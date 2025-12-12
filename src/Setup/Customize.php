<?php

namespace Luma\Core\Setup;

use Luma\Core\Helpers\Functions;
use Luma\Core\Core\Config;
use Luma\Core\Services\StaticCustomizeSettings;
use Luma\Core\Services\ThemeSettingsSchema;
use Luma\Core\Setup\CustomizeBase;
use Luma\Core\Services\ThemeJsonSettings;
use WP_Customize_Manager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customizer settings for this theme.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
class Customize extends CustomizeBase
{

    /**
     * Initialize hooks
     */
    public function __invoke(): void
    {
        add_action('customize_register', [$this, 'core_modifications']);

        add_action('after_setup_theme',  [$this, 'generate_settings']); // runs
        add_action('customize_register', [$this, 'register_customize_settings']); // registers generated settings
        add_filter('wp_theme_json_data_user', [$this, 'modify_theme_json_user']);

        // Enqueue Customizer assets
        add_action('customize_preview_init', [$this, 'enqueue_customize_preview']);
        add_action('customize_controls_enqueue_scripts', [$this, 'controls_enqueue']);
        // add_action('wp_ajax_font_reset', [$this, 'ajax_reset_font_category']);

        // Custom logo handling
        add_filter('wp_generate_attachment_metadata', [$this, 'generate_logo_sizes'], 10, 2);
        add_filter('get_custom_logo', [$this, 'custom_logo_output'], 10, 2);

        //remove_theme_mods();
    }

    /**
     * Register site identity settings
     */
    public function core_modifications(WP_Customize_Manager $wp_customize): void
    {
        // Live preview for site title & tagline
        foreach (['blogname', 'blogdescription'] as $setting) {
            if ($control = $wp_customize->get_setting($setting)) {
                $control->transport = 'postMessage';
            }
        }

        // Change labels
        if ($control = $wp_customize->get_control('blogdescription')) {
            $control->label = 'Site Description';
        }

        // change label and move to header section
        if ($control = $wp_customize->get_control('display_header_text')) {
            $control->label = 'Display Site Title in Navbar';
            $control->section = $this->namespaced('header_section');
            $control->priority = 10;
        }

        // move custom header controls into new header section
        $priority = 50;
        foreach (['header_image', 'header_video', 'external_header_video'] as $setting) {
            if ($control = $wp_customize->get_control($setting)) {
                $control->section = $this->namespaced('header_section');
                $control->priority = $priority;
                $priority += 5;
            }
        }

        // Logo description
        if ($control = $wp_customize->get_control('custom_logo')) {
            $control->description = __('Upload your logo file for the navbar. Should be at least 300px x 130px', Config::get_domain());
        }

        // // Remove the built-in Colors section
        $wp_customize->remove_section('colors');
    }

    public function generate_settings(): void
    {
        $static_settings = StaticCustomizeSettings::get();
        ThemeSettingsSchema::set($static_settings, false); // dont merge on first call
        $dynamic_settings = new ThemeJsonSettings($this->theme_json);
        ThemeSettingsSchema::set($dynamic_settings->generate());
    }

    /**
     * Register Post section options in the Customizer.
     *
     * @since Luma-Core 1.0
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     * @return void
     */
    public function register_customize_settings(WP_Customize_Manager $wp_customize): void
    {
        $this->register_settings($wp_customize, 'header');
        $this->register_settings($wp_customize, 'display');
        $this->register_settings($wp_customize, 'color');
        $this->register_settings($wp_customize, 'font');
    }

    public function modify_theme_json_user(\WP_Theme_JSON_Data $wp_theme_json_data)
    {
        $user_json = [];

        // COLOR
        $colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();
        foreach ($colors as $color) {
            // check if theme_mod exists and compare against default in theme_json
            $slug = $color['slug'] ?? null;
            $theme_mod_value = ThemeSettingsSchema::get_theme_mod("color_{$slug}");
            // TODO: normalise settings so no need to specify setting specific e.g. ['color']
            $theme_json_default = $color['color'];
            if ($theme_mod_value && $theme_json_default && $theme_mod_value !== $theme_json_default) {
                $color['color'] = $theme_mod_value;
                $user_json['color']['palette'][] = $color;
            }
        }

        // FONTS
        $font_categories = StaticCustomizeSettings::get_font_categories();
        foreach ($font_categories as $category => $props) { // body, heading
            foreach ($props as $prop => $value) {
                if (empty($value) || $prop === 'label') continue;
                $settings = ThemeSettingsSchema::get_theme_mod_default_and_value("font_{$prop}_{$category}");

                if (isset($settings['value']) && isset($settings['default']) && $settings['value'] !== $settings['default']) {
                    if ($prop === 'weight' || $prop === 'line_height') {
                        $user_json['custom']['font'][$prop][$category] = $settings['value'];
                    } else if ($prop === 'family' || $prop === 'size') {
                        $value = $this->theme_json->get(['settings', 'typography', $value['choices']])->get_by_slug($settings['value'])->css_var();
                        $user_json['custom']['font'][$prop][$category] = "var({$value})";
                    }
                }
            }
        }

        // Update theme JSON (user) with merged palette
        $new_data = [
            'version'  => 3,
            'settings' => [],
        ];

        foreach (['color', 'custom'] as $key) {
            if (!empty($user_json[$key])) {
                $new_data['settings'][$key] = $user_json[$key];
            }
        }

        return $wp_theme_json_data->update_with($new_data);
    }


    /**
     * Enqueue scripts for the Customizer live preview.
     *
     * @since Luma-Core 1.0
     *
     * @return void
     */
    public function enqueue_customize_preview(): void
    {
        // customize live preview script
        wp_enqueue_script(
            'luma-core-customize-preview',
            get_template_directory_uri() . '/assets/js/customize-preview.js',
            ['customize-preview'],
            null,
            true
        );

        $typography = $this->theme_json->get(['settings', 'typography'])->raw();
        $colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();

        // add data from theme.json to script, for lookups

        if (!empty($fonts_families)) {
            wp_localize_script(
                'luma-core-customize-preview',
                'wpData',
                [
                    'prefix' => $this->prefix,
                    'fontFamilies' => $typography['font_families'] ?? [],
                    'fontSizes' => $typography['font_sizes'] ?? [],
                    'colorPalette' => $colors ?? [],
                ]
            );
        };
    }


    /**
     * Enqueues customize control (left settings pane) styles and scripts
     *
     * @since Luma-Core 1.0
     *
     * @return void
     */
    public function controls_enqueue(): void
    {
        // customize admin css enqueue
        wp_enqueue_style(
            'luma-core-customize-controls',
            get_template_directory_uri() . '/assets/css/customize-controls.css',
            [],
            wp_get_theme()->get('Version')
        );

        // customize admin js enqueue
        wp_enqueue_script(
            'luma-core-customize-controls',
            get_template_directory_uri() . '/assets/js/customize-controls.js',
            ['customize-controls'], // depend on controls API
            filemtime(get_template_directory() . '/assets/js/customize-controls.js'),
            true
        );

        // Localize only the keys (category slugs), not the full config (to keep JS light)
        wp_localize_script('luma-core-customize-controls', 'wpData', [
            'categories' => StaticCustomizeSettings::get_font_categories(),
            'prefix' => $this->prefix,
            'ajax'  => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('font_reset_nonce'),
        ]);
    }

    public function ajax_reset_font_category(): void
    {
        check_ajax_referer('font_reset_nonce', 'nonce');

        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('not_allowed');
        }

        $category = sanitize_key($_POST['category'] ?? '');
        $categories = StaticCustomizeSettings::get_font_categories();

        if (!$category || !isset($categories[$category])) {
            wp_send_json_error('invalid_category');
        }

        // Loop properties and remove theme_mod if property is not false or 'label'
        foreach ($categories[$category] as $prop => $value) {
            if ($value === false || $prop === 'label') continue;
            remove_theme_mod("font_{$prop}_{$category}");
        }

        wp_send_json_success(true);
    }


    /**
     * Generate custom logo sizes for desktop and mobile 1x/2x.
     *
     * @param array $metadata
     * @param int   $attachment_id
     * @return array
     */
    public function generate_logo_sizes($metadata, $attachment_id): array
    {
        $theme = wp_get_theme();
        $theme_mods = get_option("theme_mods_{$theme->get('TextDomain')}", []);
        $custom_logo_id = $theme_mods['custom_logo'] ?? 0;

        // Only generate sizes for the logo attachment
        if ($custom_logo_id && $attachment_id !== $custom_logo_id) {
            return $metadata;
        }

        $file = get_attached_file($attachment_id);
        $editor = wp_get_image_editor($file);

        if (is_wp_error($editor)) {
            Functions::error_log("Cannot load image editor for attachment ID {$attachment_id}");
            return $metadata;
        }

        $orig_size   = $editor->get_size();
        $orig_height = $orig_size['height'] ?? 0;

        $heights = [
            'desktop_1x' => 65,
            'desktop_2x' => 130,
            'mobile_1x'  => 45,
            'mobile_2x'  => 90,
        ];

        foreach ($heights as $key => $height) {
            $resize_height = min($height, $orig_height);

            $resized = wp_get_image_editor($file);
            if (is_wp_error($resized)) {
                Functions::error_log("Failed to instantiate editor for {$key}");
                continue;
            }

            $resized->resize(null, $resize_height);
            $dest  = $resized->generate_filename($key);
            $saved = $resized->save($dest);

            if (is_wp_error($saved)) {
                Functions::error_log("Failed to save resized image for {$key}");
                continue;
            }

            $metadata['sizes'][$key] = [
                'file'      => wp_basename($saved['path']),
                'width'     => $saved['width'],
                'height'    => $saved['height'],
                'mime-type' => $saved['mime-type'],
            ];
        }

        return $metadata;
    }

    /**
     * Output a responsive <picture> logo with desktop/mobile 1x/2x.
     *
     * @param string $html
     * @param int    $blog_id
     * @return string
     */
    public function custom_logo_output($html, $blog_id): string
    {
        $logo_id = get_theme_mod('custom_logo');

        if (! $logo_id) {
            return $html;
        }

        $meta = wp_get_attachment_metadata($logo_id);
        if (!$meta) {
            Functions::error_log("Missing metadata for logo ID {$logo_id}");
            return $html;
        }

        $fallback = wp_get_attachment_image_url($logo_id, 'full');
        $full_width  = $meta['width'] ?? '';
        $full_height = $meta['height'] ?? '';
        $breakpoint_setting = wp_get_global_settings(['custom', 'breakpoint', 'navbar']);
        $breakpoint = is_array($breakpoint_setting) ? '800px' : $breakpoint_setting;
        $bp_int = (int) $breakpoint;
        $bp_max = ($bp_int - 1) . 'px';
        // category slug => breakpoint media query
        $categories = [
            'mobile'  => "(max-width: {$bp_max})",
            'desktop' => "(min-width: {$breakpoint})",
        ];

        $retina_factors = ['1x', '2x'];
        $sources = [];

        foreach ($categories as $cat => $media_query) {
            $srcset_parts = [];

            foreach ($retina_factors as $factor) {
                $size_key = "{$cat}_{$factor}";
                if (!empty($meta['sizes'][$size_key])) {
                    $size_data = $meta['sizes'][$size_key];
                    $url = wp_get_attachment_image_url($logo_id, $size_key);
                    if ($url) {
                        $srcset_parts[] = $url . ' ' . $size_data['width'] . 'w';
                    }
                }
            }

            if ($srcset_parts) {
                $display_width = $meta['sizes']["{$cat}_1x"]['width'] ?? 100;
                $sources[] = [
                    'media'  => $media_query,
                    'srcset' => implode(', ', $srcset_parts),
                    'sizes'  => $display_width . 'px',
                ];
            }
        }

        ob_start(); ?>
        <picture class="site-logo">
            <?php foreach ($sources as $source): ?>
                <source
                    <?php if ($source['media']): ?>
                    media="<?php echo esc_attr($source['media']); ?>"
                    <?php endif; ?>
                    srcset="<?php echo esc_attr($source['srcset']); ?>"
                    sizes="<?php echo esc_attr($source['sizes']); ?>">
            <?php endforeach; ?>

            <img
                src="<?php echo esc_url($fallback); ?>"
                width="<?php echo esc_attr($full_width); ?>"
                height="<?php echo esc_attr($full_height); ?>"
                alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                class="custom-logo">
        </picture>
<?php

        return ob_get_clean();
    }
}
