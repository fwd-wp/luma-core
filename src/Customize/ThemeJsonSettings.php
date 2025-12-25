<?php

namespace Luma\Core\Customize;

use Luma\Core\Core\Config;
use Luma\Core\Customize\StaticCustomizeSettings;
use Luma\Core\Customize\ThemeJsonService;
use Luma\Core\Helpers\Functions;

class ThemeJsonSettings
{
    protected ThemeJsonService $theme_json;
    protected string $prefix = 'luma_core';

    public function __construct(ThemeJsonService $theme_json)
    {
        $this->theme_json = $theme_json;
        // uses theme variant prefix as settings are stored to DB
        $this->prefix = Config::get_prefix() ?? $this->prefix;
    }
    public function generate(): array
    {
        // static data structure
        $dynamic_settings = [
            'color' => [
                'title' => 'Colors',
                'priority' => 35,
                'settings' => [
                    'semantic_heading' => [
                        'label'     => 'Semantic Colors',
                        'type'      => 'subheading',
                        'priority'  => 3,
                    ],
                    'system_heading' => [
                        'label'     => 'Neutral Scale',
                        'type'      => 'subheading',
                        'priority'  => 33,
                    ],
                    'component_heading' => [
                        'label'     => 'Component Specific',
                        'type'      => 'subheading',
                        'priority'  => 67,
                    ],
                ],
            ],
            'font' => [
                'title' => 'Fonts',
                'priority' => 50,
                'settings' => [],
            ],
            'header_image' => [
                'section'     => 'header_image',
                'settings' => [],
            ],
        ];

        // For both color types, css_var and setting added for lookup in customizer preview js script
        // Colors - from palette
        $palette_colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();
        $priority = 5;
        foreach ($palette_colors as $color) {
            $dynamic_settings['color']['settings'][$color['slug']] = [
                'default'  => $color['color'],
                'label'    => $color['name'],
                'type'     => 'color',
                'priority' => $priority,
                'source'   => 'palette',
                'css_var'  => "--wp--preset--color--{$color['slug']}",
                'setting_id'  => "color_{$color['slug']}",
                'setting_id_prefixed'  => "{$this->prefix}_color_{$color['slug']}",
            ];
            $priority += 5;
        }

        // Colors - custom
        $custom_colors = $this->theme_json->get(['settings', 'custom', 'color'])->raw();
        foreach ($custom_colors as $group => $colors) {
            foreach ($colors as $slug => $value) {
                $key = "{$group}_{$slug}";
                $dynamic_settings['color']['settings'][$key] = [
                    'default'  => $value,
                    'label'    => Functions::snake_to_title($key),
                    'type'     => 'color',
                    'priority' => $priority,
                    'source'   => 'custom',
                    'group'    => $group,
                    'slug'     => $slug,
                    'css_var'  => "--wp--custom--color--{$key}",
                    'setting_id'  => "color_{$key}",
                    'setting_id_prefixed'  => "{$this->prefix}_color_{$key}",
                ];
                $priority += 5;
            }
        }

        // Fonts
        $priority = 5;
        $type_map = $this->get_font_type_map();
        $priority = 5;
        // body         
        foreach (StaticCustomizeSettings::get_font_categories() as $category => $values) {
            //          subheading    $setting['label']
            foreach ($values as $property => $setting) {

                // check type exists in type_map, exit from loop if it doesnt
                $type = $setting['type'] ?? null;
                if (!isset($type_map[$type])) {
                    continue;
                }
                $map  = $type_map[$type] ?? null;

                $choices      = [];
                $input_attrs  = [];
                $default      = '';

                // Choices (select controls)
                if (!empty($map['needs_choices']) && !empty($setting['choices'])) {
                    $choices = $this->theme_json
                        ->get(['settings', 'typography', $setting['choices']])
                        ->choices();

                    if (empty($choices)) {
                        continue;
                    }
                }

                // Default value resolution
                // get slug from css var in theme.json
                if (($map['default'] ?? null) === 'slug') {
                    $default = $this->theme_json
                        ->get(['settings', 'custom', 'font', $property, $category])
                        ->slug_from_css_var();
                }

                // get raw string for default value from theme.json
                if (($map['default'] ?? null) === 'raw') {
                    $default = $this->theme_json
                        ->get([
                            'settings',
                            'custom',
                            'font',
                            Functions::kebab_to_camel($property),
                            $category,
                        ])
                        ->raw_string();
                }

                // Input attributes (number controls)
                if ($type === 'number') {
                    $input_attrs = [
                        'min'  => $setting['min'] ?? '',
                        'max'  => $setting['max'] ?? '',
                        'step' => $map['steps'][$property] ?? null,
                    ];
                }

                // Register setting
                $dynamic_settings['font']['settings']["{$property}_{$category}"] = [
                    'default'     => $default,
                    'label'       => $setting['label'],
                    'type'        => $type,
                    'priority'    => $priority,
                    'choices'     => $choices,
                    'input_attrs' => $input_attrs,
                    'property'    => $property,
                    'category'    => $category,
                    'css_var'  => "--wp--custom--font--{$property}--{$category}",
                    'setting_id'  => "font_{$property}_{$category}",
                    'setting_id_prefixed'  => "{$this->prefix}_font_{$property}_{$category}",
                ];

                $priority += 5;
            }
        }

        // custom header overlay opacity (1 off setting)
        // added to a core section (as set at top of this method)
        $default = $this->theme_json->get(['settings', 'custom', 'customHeader', 'overlayOpacity'])->raw();
        //print('<pre>' . print_r($default, true) . '</pre>');
        // 0.25
    //print('<pre>' . print_r(ThemeSettingsSchema::get(), true) . '</pre>');


        $dynamic_settings['header_image']['settings']["overlay-opacity"] = [
            'default'     => $default,
            'priority'    => 10,
            'type'        => 'range',
            'label'       => 'Header Image Overlay Opacity',
            'description' => 'Adjust the opacity of the overlay on the header image to improve text visibility.',
            'priority'    => 8,
            'input_attrs' => [
                'min'   => 0,
                'max'   => 0.9,
                'step'  => 0.05,
            ],
            'css_var'  => '--wp--custom--custom-header--overlay-opacity',
            'setting_id'  => 'header_image_overlay-opacity',
            'setting_id_prefixed'  => "{$this->prefix}_header_image_overlay-opacity",

        ];


        return $dynamic_settings;
    }

    protected function get_font_type_map(): array
    {
        return [
            'subheading' => [],
            'button'     => [],

            'select' => [
                'needs_choices' => true,
                'default'       => 'slug',
            ],

            'number' => [
                'default' => 'raw',
                'steps'   => [
                    'weight'      => 100,
                    'line-height' => 0.1,
                ],
            ],
        ];
    }
}
