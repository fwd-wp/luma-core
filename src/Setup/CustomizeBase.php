<?php

namespace Luma\Core\Setup;

// use Luma\Core\Controllers\CustomizerButtonControl;

use Luma\Core\Controllers\CustomizerButtonControl;
use Luma\Core\Controllers\CustomizerSubheadingControl;
use Luma\Core\Core\Config;
use Luma\Core\Services\ThemeJsonService;
use Luma\Core\Services\ThemeSettingsSchema;


class CustomizeBase
{
    protected string $prefix;
    protected ThemeJsonService $theme_json;

    public function __construct()
    {
        $this->prefix = Config::get_prefix();
        $this->theme_json = new ThemeJsonService();
        ThemeSettingsSchema::set_prefix($this->prefix);
    }

    protected function register_settings(\WP_Customize_Manager $wp_customize, string $group, ?string $section_id = null)
    {
        // $section_id needs to be passed in for built in sections as they are not namespaced or prefixed
        // e.g. 'title_tagline' vs 'luma_sagewood_display_page_width'
        if (!$section_id) {
            $section_id = $this->namespaced("{$group}_section");
        }

        $schema = ThemeSettingsSchema::get();

        if (!isset($schema[$group])) {
            return;
        }
        $data = $schema[$group];

        if (isset($data['title'])) {
            $this->add_section(
                $wp_customize,
                $section_id,
                $data['title'],
                $data['priority'] ?? null,
            );
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

        $wp_customize->add_setting($setting_id, $normalized['setting']);

        $control_class = $this->get_control_class($config['type']);

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

        if (isset($normalized['partial'])) {
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
                $translated_choices[$key] = __($value, Core::get_domain());
            }
        }
        $input_attrs = [];
        if ( isset($item['input_attrs']) && is_array($item['input_attrs']) ) {
            $input_attrs['min'] = $item['input_attrs']['min'] ?? null;
            $input_attrs['max'] = $item['input_attrs']['max'] ?? null;
            $input_attrs['step'] = $item['input_attrs']['step'] ?? null;
        }

        $setting = [
            'default' => $this::get_default($item['default'] ?? null, $item['type'], $item['choices'] ?? []),
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
                'container_inclusive' => $item['partial']['container_inclusive'] ?? '',
            ];
        } else
            $partial = [];


        return [
            'setting' => $setting,
            'control' => $control,
            'partial' => $partial,
        ];
    }

    private function get_sanitizer($item)
    {
        switch ($item['type']) {
            case 'checkbox':
                return 'rest_sanitize_boolean';

            case 'radio':
            case 'select':
                $valid_keys = array_keys($item['choices'] ?? []);
                return static function ($val) use ($valid_keys) {
                    return in_array($val, $valid_keys, true) ? $val : ($valid_keys[0] ?? '');
                };

            case 'color':
                return 'sanitize_hex_color';

            case 'number':
            case 'range':
                return 'absint'; // ensures integer value

            case 'url':
                return 'esc_url_raw';

            case 'email':
                return 'sanitize_email';

            case 'textarea':
                return 'sanitize_textarea_field';

            case 'image':
            case 'media':
            case 'upload':
            case 'cropped_image':
                return 'absint'; // assuming WP attachment ID; or 'esc_url_raw' if URL

            default:
                return 'sanitize_text_field';
        }
    }
    public function old_get_default(array $item)
    {
        // explicit default always wins
        if (array_key_exists('default', $item)) {
            return $item['default'];
        }

        switch ($item['type']) {
            case 'checkbox':
                // WP default is ''
                return false;

            case 'radio':
            case 'select':
                // WP default is null
                if (!empty($item['choices'])) {
                    $keys = array_keys($item['choices']);
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
            'subheading' => CustomizerSubheadingControl::class,
            'button'     => CustomizerButtonControl::class,
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
            'title'    => __($title, Core::get_domain()),
            'priority' => $priority,
        ]);
    }
}
