<?php

namespace Luma\Core\Services;

use Luma\Core\Helpers\Functions;
use Luma\Core\Services\ThemeJsonService;

class ThemeJsonSettings
{
    protected ThemeJsonService $theme_json;

    public function __construct(ThemeJsonService $theme_json)
    {
        $this->theme_json = $theme_json;
    }
    public function generate(): array
    {
        // static data structure
        $settings = [
            'color' => [
                'title' => 'Colors',
                'priority' => 35,
                'settings' => [
                    'typography_heading' => [
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
        ];

        // Colors - from palette
        $colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();
        $priority = 5;
        foreach ($colors as $color) {
            $settings['color']['settings'][$color['slug']] = [
                'default'  => $color['color'],
                'label'    => $color['name'],
                'type'     => 'color',
                'priority' => $priority,
            ];
            $priority += 5;
        }

        // Colors - custom
        $custom_colors = $this->theme_json->get(['settings', 'custom', 'color'])->raw();
        //print('<pre>' . print_r($custom_colors, true) . '</pre>');
        foreach ($custom_colors as $prefix => $colors) {
            foreach ($colors as $slug => $value) {
                $key = "{$prefix}_{$slug}";
                $settings['color']['settings'][$key] = [
                    'default'  => $value,
                    'label'    => Functions::snake_to_title($key),
                    'type'     => 'color',
                    'priority' => $priority,
                ];
                $priority += 5;
            }
        }

        // Fonts
        $priority = 5;
        foreach (StaticCustomizeSettings::get_font_categories() as $category => $props) {
            foreach ($props as $key => $value) {
                // skip if property not defined
                if (!isset($value)) {
                    continue;
                }

                // Subheading for the current category
                if ($key === 'label') {
                    $settings['font']['settings']["heading_{$category}"] = [
                        'label'     => $value,
                        'type'      => 'subheading',
                        'priority'  => $priority,
                    ];
                    $priority += 5;
                }

                // Font family and size
                if ($key === 'family' || $key === 'size') {
                    if (!isset($value['choices']) || !isset($value['label']) || !$value) {
                        continue;
                    }

                    $choices = $this->theme_json->get(['settings', 'typography', $value['choices']])->choices();

                    if (!empty($choices)) {
                        $default = $this->theme_json->get(['settings', 'custom', 'font', $key, $category])->slug_from_css_var();

                        $settings['font']['settings']["{$key}_{$category}"] = [
                            'default'   => $default,
                            'label'     => $value['label'],
                            'type'      => 'select',
                            'priority'  => $priority,
                            'choices'   =>  $choices
                        ];
                        $priority += 5;
                    }
                }

                // Font weight and line height
                if ($key === 'weight' || $key === 'line-height') {
                    if (!isset($value['label'])) {
                        continue;
                    }

                    $default = $this->theme_json->get(['settings', 'custom', 'font', Functions::kebab_to_camel($key), $category])->raw_string();
                    $settings['font']['settings']["{$key}_{$category}"] = [
                        'default'   => $default,
                        'label'     => $value['label'],
                        'type'      => 'number',
                        'priority'  => $priority,
                        'input_attrs' => [
                            'min'  => $props['weight']['min'] ?? '',
                            'max'  => $props['weight']['max'] ?? '',
                            'step' => $props['weight']['step'] ?? '',
                        ],
                    ];
                    $priority += 5;
                }
            }
        }

        return $settings;
    }
}
