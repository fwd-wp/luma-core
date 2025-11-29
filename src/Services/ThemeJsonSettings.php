<?php

namespace Luma\Core\Services;

use Luma\Core\Services\ThemeJsonService;
use Luma\Core\Setup\Customize;

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
                        'label'     => 'Typography',
                        'type'      => 'subheading',
                        'priority'  => 3,
                    ],
                    'system_heading' => [
                        'label'     => 'Color System',
                        'type'      => 'subheading',
                        'priority'  => 33,
                    ],
                    'background_heading' => [
                        'label'     => 'Background',
                        'type'      => 'subheading',
                        'priority'  => 73,
                    ],
                ],
            ],
            'font' => [
                'title' => 'Fonts',
                'priority' => 50,
                'settings' => [],
            ],
        ];

        // Colors
        $colors = $this->theme_json->get(['settings', 'color', 'palette'])->raw();
        $priority = 5;
        foreach ($colors as $color) {
            $settings['color']['settings'][$color['slug']] = [
                'default'  => $color['color'],
                'label'    => $color['name'] . ' Color',
                'type'     => 'color',
                'priority' => $priority,
            ];
            $priority += 5;
        }

        // Fonts
        $priority = 5;
        foreach (StaticCustomizeSettings::get_font_categories() as $category => $props) {

            // Subheading for the current category
            $settings['font']['settings']["heading_{$category}"] = [
                'label'     => $props['label'],
                'type'      => 'subheading',
                'priority'  => $priority,
            ];
            $priority += 5;

            // Font family
            if ($props['family'] ?? false) {
                $choices = $this->theme_json->get(['settings', 'typography', 'fontFamilies'])->choices();

                if (!empty($choices)) {
                    $default = $this->theme_json->get(['settings', 'custom', 'font', 'family', $category])->slug_from_css_var();

                    $settings['font']['settings']["family_{$category}"] = [
                        'default'   => $default,
                        'label'     => 'Font',
                        'type'      => 'select',
                        'priority'  => $priority,
                        'choices'   =>  $choices
                    ];
                    $priority += 5;
                }
            }

            // Font weight
            if ($props['weight'] ?? false) {
                $default = $this->theme_json->get(['settings', 'custom', 'font', 'weight', $category])->raw_string();

                $settings['font']['settings']["weight_{$category}"] = [
                    'default'   => $default,
                    'label'     => 'Font Weight',
                    'type'      => 'number',
                    'priority'  => $priority,
                    'choices'   =>  $choices,
                    'input_attrs' => [
                        'min'  => $props['weight']['min'],
                        'max'  => $props['weight']['max'],
                        'step' => 100,
                    ],
                ];
                $priority += 5;
            }

            // Line height
            if ($props['line_height'] ?? false) {
                $default = $this->theme_json->get(['settings', 'custom', 'font', 'lineHeight', $category])->raw_string();

                $settings['font']['settings']["line_height_{$category}"] = [
                    'default'   => $default,
                    'label'     => 'Line Height',
                    'type'      => 'number',
                    'priority'  => $priority,
                    'choices'   =>  $choices,
                    'input_attrs' => [
                        'min'  => $props['line_height']['min'],
                        'max'  => $props['line_height']['max'],
                        'step' => 0.5,
                    ],
                ];
                $priority += 5;
            }

            // Font size (if applicable)
            if (!empty($props['size'])) {
                $choices = $this->theme_json->get(['settings', 'typography', 'fontSizes'])->choices();
                if (!empty($choices)) {
                    $default = $this->theme_json->get(['settings', 'custom', 'font', 'size', $category])->slug_from_css_var();

                    $settings['font']['settings']["size_{$category}"] = [
                        'default'   => $default,
                        'label'     => 'Line Height',
                        'type'      => 'select',
                        'priority'  => $priority,
                        'choices'   =>  $choices,
                    ];
                    $priority += 5;
                }
            }
        }

        return $settings;
    }
}
