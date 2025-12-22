<?php

namespace Luma\Core\Customize;

use Luma\Core\Core\Config;
use Luma\Core\Helpers\Functions;
use Luma\Core\Customize\StaticCustomizeSettings;
use Luma\Core\Customize\ThemeSettingsSchema;
use Luma\Core\Customize\CustomizeBase;
use Luma\Core\Customize\ThemeJsonSettings;

use WP_Customize_Manager;
use WP_Theme_JSON_Data;

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
        // modify core customizer settings
        add_action('customize_register', [$this, 'core_modifications']);
        // generates settings from static and dynamic sources into ThemeSettingsSchema
        // defaults can be retrieved from there in templates etc.
        add_action('after_setup_theme',  [$this, 'generate_settings']);
        // registers generated settings into the customizer
        add_action('customize_register', [$this, 'register_customize_settings']);
        // modify theme json user data based on customizer settings
        add_filter('wp_theme_json_data_user', [$this, 'modify_theme_json_user']);
        // Enqueue Customizer assets for live prieview iframe
        add_action('customize_preview_init', [$this, 'enqueue_customize_preview']);
        // Enqueue Customizer assets for controls pane
        add_action('customize_controls_enqueue_scripts', [$this, 'controls_enqueue']);
        // for dev only - reset all theme mods
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

        // check for custom header theme support before moving controls
        if (current_theme_supports('custom-header')) {
            // change label and move to header section
            if ($control = $wp_customize->get_control('display_header_text')) {
                $control->label = 'Display Site Title and Tagline over Custom Image Header';
                $control->section = 'header_image';
                $control->priority = 4;
                $control->transport = 'postMessage';
            }

            if ($control = $wp_customize->get_control('header_textcolor')) {
                $control->section = 'header_image';
                $control->priority = 6;
                $control->transport = 'postMessage';
            }
        } else {
            // remove display text control if no custom header support
            // as its not used for displaying title in navbar
            if ($wp_customize->get_control('display_header_text')) {
                $wp_customize->remove_control('display_header_text');
            }
        }

        // Logo description
        if ($control = $wp_customize->get_control('custom_logo')) {
            $control->description = __('Upload your logo file for the navbar. Should be at least 300px x 130px', Config::get_domain());
        }
    }

    // gets settings and registers them in ThemeSettingsSchema
    public function generate_settings(): void
    {
        // Get and set static settings from file
        $static_settings = StaticCustomizeSettings::get();
        ThemeSettingsSchema::set($static_settings, false); // dont merge on first call

        // get and set dynamic settings from theme.json
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
        $this->register_all_settings($wp_customize);
    }

    public function modify_theme_json_user(WP_Theme_JSON_Data $wp_theme_json_data): WP_Theme_JSON_Data
    {
        $user_json = [];
        $theme_settings = ThemeSettingsSchema::get();
        $all_colors = $theme_settings['color']['settings'] ?? [];

        foreach ($all_colors as $key => $setting) {
            $source = $setting['source'] ?? null;

            // skip if value not changed
            $theme_mod_value = ThemeSettingsSchema::get_theme_mod($setting['setting_id'] ?? '');
            if (!$theme_mod_value || !$setting['default'] || $theme_mod_value === $setting['default']) {
                continue;
            }

            if ($source === 'palette') {
                $user_json['color']['palette'][] = [
                    'slug' => $key,
                    'color' => $theme_mod_value,
                    'name' => $setting['label'],
                ];
            } elseif ($source === 'custom') {
                $user_json['custom']['color'][$setting['group']][$setting['slug']] = $theme_mod_value;
            }
        }

        // FONTS
        $all_fonts = $theme_settings['font']['settings'] ?? [];

        foreach ($all_fonts as $key => $setting) {
            if ($setting['type'] === 'subheading') {
                continue; // skip headings
            }

            $theme_mod_value = ThemeSettingsSchema::get_theme_mod($setting['setting_id']);
            // skip if value not changed
            if (!$theme_mod_value || !$setting['default'] || $theme_mod_value === $setting['default']) {
                continue;
            }


            // Normalize property key
            $setting['property'] = Functions::kebab_to_camel($setting['property']); // line-height ->lineHeight

            if (in_array($setting['property'], ['weight', 'lineHeight'], true)) {
                $user_json['custom']['font'][$setting['property']][$setting['category']] = $theme_mod_value;
            } elseif (in_array($setting['property'], ['family', 'size'], true)) {
                // normalise camel case theme mod values, to kebab case for css var generation
                $value = Functions::camel_to_kebab($theme_mod_value);
                $user_json['custom']['font'][$setting['property']][$setting['category']] = "var(--wp--preset--font--{$value})";
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
            "{$this->core_kebab_prefix}-customize-preview",
            get_template_directory_uri() . '/vendor/luma/core/assets/js/customize-preview.js',
            ['customize-preview'],
            $this->version,
            true
        );

        // these values are sourced from theme.json as they are lookup tables, rather than settings
        // css vars are added as thats whats being looked up in js in the preview screen
        $font_families = $this->theme_json->get(['settings', 'typography', 'fontFamilies'])->with_css_vars() ?? [];
        $font_sizes = $this->theme_json->get(['settings', 'typography', 'fontSizes'])->with_css_vars() ?? [];

        // these values are extracted from theme settings schema
        // css variables were created at the time the settings were generated
        // have to get theme settings here, so its up to date with any changes made in an earlier hook
        $theme_settings = ThemeSettingsSchema::get();
        $color_settings = $theme_settings['color']['settings'] ?? [];
        $font_settings = $theme_settings['font']['settings'] ?? [];
        //print('<pre>' . print_r($font_settings, true) . '</pre>');
        // add localised data to script
        wp_localize_script(
            "{$this->core_kebab_prefix}-customize-preview",
            'wpData',
            [
                'prefix' => $this->prefix,
                'fontFamilies' => $font_families,
                'fontSizes' => $font_sizes,
                'colorSettings' => $color_settings,
                'fontSettings' => $font_settings,
            ]
        );


        // Enqueue masonry script in case its needed
        wp_enqueue_script(
            "{$this->core_kebab_prefix}-archive-masonry-customize-preview",
            get_template_directory_uri() . '/vendor/luma/core/assets/js/archive-masonry.js',
            ['masonry'],
            Config::get_theme_version(),
            true
        );
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
            "{$this->core_kebab_prefix}-customize-controls",
            get_template_directory_uri() . '/vendor/luma/core/assets/css/customize-controls.css',
            [],
            $this->version
        );

        // customize admin js enqueue
        wp_enqueue_script(
            "{$this->core_kebab_prefix}-customize-controls",
            get_template_directory_uri() . '/vendor/luma/core/assets/js/customize-controls.js',
            ['customize-controls'], // depend on controls API
            $this->version,
            true
        );

        // Localize only the keys (category slugs), not the full config (to keep JS light)
        wp_localize_script("{$this->core_kebab_prefix}-customize-controls", 'wpData', [
            'categories' => StaticCustomizeSettings::get_font_categories(),
            'prefix' => $this->prefix,
            'ajax'  => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('font_reset_nonce'),
        ]);
    }
}
