<?php

namespace Luma\Core\Customize;

use Luma\Core\Core\Config;
use Luma\Core\Customize\Controls\ButtonControl;
use Luma\Core\Customize\Controls\SubheadingControl;
use Luma\Core\Helpers\Functions;

class CustomizeBase
{
    // settings use theme variant prefix as they are stored to DB
    protected string $prefix = 'luma_core';
    // core kebab prefix used for core asset handles
    protected string $core_kebab_prefix = 'luma-core';
    // not used
    // protected string $core_camel_prefix;
    protected string $version = '1.0.0';
    protected ThemeJsonService $theme_json;
    protected array $theme_settings;

    public function __construct()
    {
        $this->prefix = Config::get_prefix() ?? $this->prefix;
        $this->core_kebab_prefix = Config::get_prefix_kebab_core() ?? $this->core_kebab_prefix;
        // $this->core_camel_prefix = Config::get_prefix_camel_core();
        $this->version = Config::get_theme_version() ?? $this->version;
        $this->theme_json = new ThemeJsonService();
    }

    protected function register_all_settings(\WP_Customize_Manager $wp_customize): void
    {
        foreach (StaticCustomizeSettings::get() as $group => $data) {
            $this->register_group($wp_customize, $group, $data);
        }
    }

    private function register_group(\WP_Customize_Manager $wp_customize, string $group, array $data): void
    {   
        if ($data['default_only'] ?? false) {
            // skip registering settings that are default only
            return;
        }   

        if (isset($data['section'])) {
            // section is provided for built in core 

            // check if exists else skip
            if($wp_customize->get_section($data['section']) === null) {
                return;
            }
            // they will not  be namespaced, and section will not be created
            $section_id = $data['section'];
        } else {
            // if section is not specified, its built from the data structure and namespaced
            $section_id = $this->namespaced("{$group}_section");

            if (isset($data['title'])) {
                $this->add_section(
                    $wp_customize,
                    $section_id,
                    $data['title'],
                    $data['priority'] ?? null,
                );
            }
        }

        foreach ($data['settings'] as $key => $config) {
            $setting_id = $this->namespaced("{$group}_{$key}");
            $this->add_setting_and_control(
                $wp_customize,
                $setting_id,
                $section_id,
                $config,
            );
        }
    }

    private function add_setting_and_control(\WP_Customize_Manager $wp_customize, $setting_id, $section_id, $config)
    {
        $normalized = $this->normalize_config($config, $section_id);

        // add setting
        $wp_customize->add_setting($setting_id, $normalized['setting']);

        // --- Attach default for JS ---
        // $setting = $wp_customize->get_setting($setting_id);
        // if ($setting) {
        //     // ensure the JS sees the default
        //     $setting->params['default'] = $setting->default;
        // }

        $control_class = $this->get_control_class($config['type'] ?? '');

        // add control
        if ($control_class) {
            // special control type (color, image, media, etc.)
            $wp_customize->add_control(new $control_class(
                $wp_customize,
                $setting_id,
                $normalized['control']
            ));
        } else {
            // default core control (text, checkbox, radio, number...)
            $wp_customize->add_control($setting_id, $normalized['control']);
        }

        // add selective refresh partial if defined
        if (!empty($normalized['partial'])) {
            $wp_customize->selective_refresh->add_partial(
                $setting_id,
                $normalized['partial']
            );
        }
    }

    private function normalize_config(array $item, string $section_id): array
    {
        $translated_choices = [];
        if (isset($item['choices']) && is_array($item['choices'])) {
            foreach ($item['choices'] as $key => $value) {
                $translated_choices[$key] = __($value, Config::get_domain());
            }
        }
        $input_attrs = [];
        if (isset($item['input_attrs']) && is_array($item['input_attrs'])) {
            $input_attrs['min'] = $item['input_attrs']['min'] ?? null;
            $input_attrs['max'] = $item['input_attrs']['max'] ?? null;
            $input_attrs['step'] = $item['input_attrs']['step'] ?? null;
        }

        $setting = [
            'default' => $this::get_default($item['default'] ?? null, $item['type'] ?? '', $item['choices'] ?? []),
            'sanitize_callback' => $this->get_sanitizer($item),
            'transport' => $item['transport'] ?? 'postMessage',

        ];

        $control = [
            'label' => $item['label'] ?? '',
            'description' => $item['description'] ?? '',
            'section' => $section_id,
            'priority' => $item['priority'] ?? 10,
            'type' => $item['type'] ?? 'text',
            'choices' => $translated_choices,
            'input_attrs' => $input_attrs,
        ];

        if (isset($item['partial']) && is_array($item['partial'])) {
            $partial = [
                'selector' => $item['partial']['selector'] ?? '',
                'render_callback' => $item['partial']['render_callback'] ?? '',
                'container_inclusive' => $item['partial']['container_inclusive'] ?? false,
            ];
        } else
            $partial = [];


        return [
            'setting' => $setting,
            'control' => $control,
            'partial' => $partial,
        ];
    }

    private function get_sanitizer(array $item)
    {   
        switch ($item['type'] ?? '') {

            // Boolean checkbox
            case 'checkbox':
                return 'rest_sanitize_boolean';

                // Single-choice inputs
            case 'radio':
            case 'select':
                $valid_keys = array_keys($item['choices'] ?? []);
                return static function ($val) use ($valid_keys) {
                    return in_array($val, $valid_keys, true) ? $val : ($valid_keys[0] ?? '');
                };

                // Color input
            case 'color':
                return 'sanitize_hex_color';

                // Numeric input
            case 'number':
            case 'range':
                $min = $item['min'] ?? null;
                $max = $item['max'] ?? null;
                return static function ($val) use ($min, $max) {
                    $val = absint($val);
                    if ($min !== null) $val = max($val, $min);
                    if ($max !== null) $val = min($val, $max);
                    return $val;
                };

                // URL
            case 'url':
                return 'esc_url_raw';

                // Email
            case 'email':
                return 'sanitize_email';

                // Textarea
            case 'textarea':
                return 'sanitize_textarea_field';

                // Media / image uploads
            case 'image':
            case 'media':
            case 'upload':
            case 'cropped_image':
                return static function ($val) {
                    $val = absint($val);
                    return ($val && wp_attachment_is_image($val)) ? $val : 0;
                };

                // Default: generic text input
            default:
                return 'sanitize_text_field';
        }
    }


    public static function get_default(mixed $default = null, string $type = '', array $choices = [])
    {
        // explicit default always wins
        if ($default) {
            return $default;
        }

        switch ($type) {
            case 'checkbox':
                // WP default is ''
                return false;

            case 'radio':
            case 'select':
                // WP default is null
                if (!empty($choices)) {
                    $keys = array_keys($choices);
                    return $keys[0];
                }
                return null;

            case 'number':
            case 'range':
                return 0;

            case 'color':
                return '';

            case 'image':
            case 'media':
            case 'upload':
            case 'cropped_image':
                return '';

                // everything else defaults to empty string
            default:
                return '';
        }
    }

    private function get_control_class(string $type)
    {
        return [
            'color'  => \WP_Customize_Color_Control::class,
            'image'  => \WP_Customize_Image_Control::class,
            'media'  => \WP_Customize_Media_Control::class,
            // custom
            'subheading' => SubheadingControl::class,
            'button'     => ButtonControl::class,
        ][$type] ?? null;
    }

    /**
     * Returns a namespaced setting key.
     */
    protected function namespaced(string $setting,): string
    {
        return "{$this->prefix}_{$setting}";
    }

    /** --------------------------
     *  HELPERS
     * --------------------------- */
    protected function add_section(
        \WP_Customize_Manager $wp_customize,
        string $id,
        string $title,
        ?int $priority = null,
    ) {
        $wp_customize->add_section($id, [
            'title'    => __($title, Config::get_domain()),
            'priority' => $priority,
        ]);
    }
}
