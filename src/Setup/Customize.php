<?php

namespace Luma\Core\Setup;

use Luma\Core\Setup\CustomizeBase;
use Luma\Core\Services\I18nService;
use Luma\Core\Helpers\Functions;

use Luma\Core\Controllers\{
    CustomizerSectionHeadingControl,
    CustomizerButtonControl
};
use Luma\Core\Services\ThemeSettingsSchema;
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
        // Register sections & settings
        add_action('customize_register', [$this, 'site_identity']); // 20
        add_action('customize_register', [$this, 'header']);        // 30
        add_action('customize_register', [$this, 'post']);          // 35
        add_action('customize_register', [$this, 'colors']);        // 40
        add_action('customize_register', [$this, 'register_fonts']); // 50

        // Modify theme.json data before CSS vars generation
        add_filter('wp_theme_json_data_user', [$this, 'modify_theme_json_user']);

        // Enqueue Customizer assets
        add_action('customize_preview_init', [$this, 'enqueue_customize_preview']);
        add_action('customize_controls_enqueue_scripts', [$this, 'controls_enqueue']);
        add_action('wp_ajax_font_reset', [$this, 'ajax_reset_font_category']);

        // Custom logo handling
        add_filter('wp_generate_attachment_metadata', [$this, 'generate_logo_sizes'], 10, 2);
        add_filter('get_custom_logo', [$this, 'custom_logo_output'], 10, 2);
    }


    /**
     * Register site identity settings
     */
    public function site_identity(WP_Customize_Manager $wp_customize): void
    {
        // $group = 'title_tagline';
        // $section = $group; // core so no namespacing or _section suffix
        //$this->register_settings($wp_customize, $group, $section);

        // Live preview for site title & tagline
        foreach (['blogname', 'blogdescription'] as $setting) {
            if ($control = $wp_customize->get_setting($setting)) {
                $control->transport = 'postMessage';
            }
        }

        // TODO: rename Display Site Title and Tagline to Display Site Title in Navbar, also move to different section

        // Logo description
        if ($control = $wp_customize->get_control('custom_logo')) {
            $control->description = __('Upload your logo file for the navbar. Should be at least 300px x 130px', I18nService::get_domain());
        }

        // print('<pre>');
        // print_r(ThemeSettingsSchema::get_settings_list());
        // print('</pre>');
    }

    /**
     * Register header section
     */
    public function header(WP_Customize_Manager $wp_customize): void
    {
        $this->register_settings($wp_customize, 'header');
    }

    /**
     * Register Post section options in the Customizer.
     *
     * @since Luma-Core 1.0
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     * @return void
     */
    public function post(WP_Customize_Manager $wp_customize): void
    {
        $this->register_settings($wp_customize, 'display');

    
    }

    /**
     * Register color-related Customizer settings and controls.
     *	 
     * @since Luma-Core 1.0
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     * @return void
     */
    public function colors(WP_Customize_Manager $wp_customize): void
    {
        $colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();
        $priority = 5;

        if (! empty($colors)) {
            foreach ($colors as $color) {
                $slug = $color['slug'];
                $id = "color_{$slug}";
                $wp_customize->add_setting($id, [
                    'default'           => $color['color'],
                    'transport'         => 'postMessage',
                    'sanitize_callback' => 'sanitize_hex_color',
                ]);
                $wp_customize->add_control(new \WP_Customize_Color_Control(
                    $wp_customize,
                    $id,
                    [
                        'label'    => $color['name'] . ' Color',
                        'section'  => 'colors',
                        'settings' => $id,
                        'priority' => $priority,
                    ]
                ));
                $priority += 5;
            }
        }

        // Subheading - Typography
        $wp_customize->add_setting('luma_core_colors_typography_heading', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control(new CustomizerSectionHeadingControl(
            $wp_customize,
            'luma_core_colors_typography_heading',
            [
                'label'    => 'Typography',
                'section'  => 'colors', // core section no name spacing
                'priority' => 1,
            ]
        ));

        // Subheading - Typography
        $wp_customize->add_setting('luma_core_colors_general_heading', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control(new CustomizerSectionHeadingControl(
            $wp_customize,
            'luma_core_colors_general_heading',
            [
                'label'    => 'General',
                'section'  => 'colors', // core section no name spacing
                'priority' => 31,
            ]
        ));

        // Subheading - Backgrounds
        $wp_customize->add_setting('luma_core_colors_background_heading', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control(new CustomizerSectionHeadingControl(
            $wp_customize,
            'luma_core_colors_background_heading',
            [
                'label'    => 'Background',
                'section'  => 'colors', // core section no name spacing
                'priority' => 71,
            ]
        ));

        // disable wp core header text color control
        if ($wp_customize->get_control('header_textcolor')) {
            $wp_customize->remove_control('header_textcolor');
        }
    }

    /**
     * Get all font categories and their properties.
     *
     * @since Luma-Core 1.0
     *
     * @return array<string, array<string, mixed>> Array of font categories and their properties.
     */
    public static function get_font_categories(): array
    {
        $categories = [
            'body' => [
                'label'       => 'Body',
                'family' => ['choices' => 'fontFamilies'],
                'weight' => ['min' => 300, 'max' => 600,],
                'line_height' => ['min' => 1.2, 'max' => 2.0,],
                'size'   => ['choices' => 'fontSizes'],
            ],
            'heading' => [
                'label'       => 'Heading',
                'family' => ['choices' => 'fontFamilies'],
                'weight' => ['min' => 400, 'max' => 900,],
                'line_height' => ['min' => 1.0, 'max' => 1.5,],
                'size'   => false,
            ],
        ];

        // Optionally add custom header if supported and enabled
        if (current_theme_supports('custom-header') && get_header_image()) {
            $categories['custom_header'] = [
                'label'       => 'Image Header',
                'family' => false,
                'weight' => ['min' => 400, 'max' => 700,],
                'line_height' => ['min' => 1.0, 'max' => 1.5,],
                'size'   => false,
            ];
        }

        return $categories;
    }

    /**
     * Register all font-related Customizer settings and controls.
     *
     * @since Luma-Core 1.0
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     * @return void
     */
    public function register_fonts(WP_Customize_Manager $wp_customize): void
    {
        $wp_customize->add_section('fonts_section', [
            'title'    => __('Fonts', I18nService::get_domain()),
            'priority' => 50,
        ]);

        $categories = self::get_font_categories();
        $priority = 5;

        foreach ($categories as $category => $props) {
            // Sub-heading
            $wp_customize->add_setting("font_heading_{$category}", [
                'sanitize_callback' => 'sanitize_text_field',
            ]);
            $wp_customize->add_control(new CustomizerSectionHeadingControl(
                $wp_customize,
                "font_heading_{$category}_control",
                [
                    'label'    => "{$props['label']} Typography",
                    'section'  => 'fonts_section',
                    'priority' => $priority,
                    'settings' => "font_heading_{$category}",
                ]
            ));
            $priority += 5;

            // Font family
            if ($props['family'] ?? false) {
                $choices = $this->theme_json->get(['settings', 'typography', 'fontFamilies'])->choices();

                if (!empty($choices)) {
                    $default = $this->theme_json->get(['settings', 'custom', 'font', 'family', $category])->slug_from_css_var();
                    $wp_customize->add_setting("font_family_{$category}", [
                        'default'           => $default,
                        'sanitize_callback' => static function ($value) use ($choices) {
                            return array_key_exists($value, $choices) ? $value : 'disabled';
                        },
                        'transport'         => 'postMessage',
                    ]);
                    $wp_customize->add_control("font_family_{$category}_control", [
                        'label'    => __('Font', I18nService::get_domain()),
                        'section'  => 'fonts_section',
                        'settings' => "font_family_{$category}",
                        'type'     => 'select',
                        'choices'  => $choices,
                        'priority' => $priority,
                    ]);
                    $priority += 5;
                }
            }

            // Font weight
            if ($props['weight'] ?? false) {
                $default = $this->theme_json->get(['settings', 'custom', 'font', 'weight', $category])->raw_string();
                $wp_customize->add_setting("font_weight_{$category}", [
                    'default'           => $default,
                    'sanitize_callback' => function ($value) {
                        return absint($value);
                    },
                    'transport'         => 'postMessage',
                ]);
                $wp_customize->add_control("font_weight_{$category}_control", [
                    'label'       => __('Font Weight', I18nService::get_domain()),
                    'section'     => 'fonts_section',
                    'settings'    => "font_weight_{$category}",
                    'type'        => 'number',
                    'priority'    => $priority,
                    'input_attrs' => [
                        'min'  => $props['weight']['min'],
                        'max'  => $props['weight']['max'],
                        'step' => 100,
                    ],
                ]);
                $priority += 5;
            }

            // Line height
            if ($props['line_height'] ?? false) {
                $default = $this->theme_json->get(['settings', 'custom', 'font', 'lineHeight', $category])->raw_string();
                $wp_customize->add_setting("font_line_height_{$category}", [
                    'default'           => $default,
                    'sanitize_callback' => function ($value) {
                        return floatval($value);
                    },
                    'transport'         => 'postMessage',
                ]);
                $wp_customize->add_control("font_line_height_{$category}_control", [
                    'label'       => __('Line Height', I18nService::get_domain()),
                    'section'     => 'fonts_section',
                    'settings'    => "font_line_height_{$category}",
                    'type'        => 'number',
                    'priority'    => $priority,
                    'input_attrs' => [
                        'min'  => $props['line_height']['min'],
                        'max'  => $props['line_height']['max'],
                        'step' => 0.05,
                    ],
                ]);
                $priority += 5;
            }

            // Font size (if applicable)
            if (!empty($props['size'])) {
                $choices = $this->theme_json->get(['settings', 'typography', 'fontSizes'])->choices();

                if (!empty($choices)) {
                    $default = $this->theme_json->get(['settings', 'custom', 'font', 'size', $category])->slug_from_css_var();
                    $wp_customize->add_setting("font_size_{$category}", [
                        'default'           => $default,
                        'sanitize_callback' => 'sanitize_text_field',
                        'transport'         => 'postMessage',
                    ]);
                    $wp_customize->add_control("font_size_{$category}_control", [
                        'label'    => __('Font Size', I18nService::get_domain()),
                        'section'  => 'fonts_section',
                        'settings' => "font_size_{$category}",
                        'type'     => 'select',
                        'choices'  => $choices,
                        'priority' => $priority,
                    ]);
                    $priority += 5;
                }
            }

            // Reset button
            $wp_customize->add_setting("font_reset_{$category}", [
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control(new CustomizerButtonControl(
                $wp_customize,
                "font_reset_{$category}_control",
                [
                    'label'    => "Reset to defaults",
                    'section'  => 'fonts_section',
                    'priority' => $priority,
                    'settings' => "font_reset_{$category}",
                ]
            ));
            $priority += 5;
        }
    }

    public function modify_theme_json_user(\WP_Theme_JSON_Data $wp_theme_json_data)
    {
        $user_json = [];

        // COLOR
        $colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();
        foreach ($colors as $color) {
            // check if theme_mod exists and compare against defaut
            $slug = $color['slug'] ?? null;
            $theme_mod = get_theme_mod("color_{$slug}", null);
            // TODO: normalise settings so no need to specify setting specific e.g. ['color']
            $default = $color['color'];
            if ($theme_mod && $default && $theme_mod !== $default) {
                $color['color'] = $theme_mod;
                $user_json['color']['palette'][] = $color;
            }
        }

        // FONTS
        $font_categories = self::get_font_categories();
        foreach ($font_categories as $category => $props) { // body, heading
            foreach ($props as $prop => $entries) {
                if (empty($entries)) break;

                $default = $this->theme_json->get(['settings', 'custom', 'font', $prop, $category])->slug_from_css_var();
                $theme_mod = get_theme_mod("font_{$prop}_{$category}", null);

                if (isset($theme_mod) && isset($default) && $theme_mod !== $default) {
                    if ($prop === 'weight' || $prop === 'line_height') {
                        $user_json['custom']['font'][$prop][$category] = $theme_mod;
                    } else if ($prop === 'family' || $prop === 'size') {
                        $value = $this->theme_json->get(['settings', 'typography', $entries['choices']])->get_by_slug($theme_mod)->css_var();
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

        // add data from theme.json to script, for lookups
        $fonts_families = $typography['font_families'] ?? [];
        if (!empty($fonts_families)) {
            wp_localize_script('luma-core-customize-preview', 'fontFamilies', $fonts_families);
        }

        $font_sizes = $typography['font_sizes'] ?? [];
        if (!empty($font_sizes)) {
            wp_localize_script('luma-core-customize-preview', 'fontSizes', $font_sizes);
        }

        $colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();
        if (! empty($colors)) {
            wp_localize_script('luma-core-customize-preview', 'colorPalette', $colors);
        }
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
            'luma-core-style',
            get_template_directory_uri() . '/assets/css/customize-controls.css',
            array(),
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
        wp_localize_script('luma-core-customize-controls', 'fontReset', [
            'categories' => self::get_font_categories(),
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
        $categories = self::get_font_categories();

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
