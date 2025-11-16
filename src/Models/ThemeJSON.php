<?php

namespace Twenty\One\Models;

use Twenty\One\Helpers\Functions;

if (! defined('ABSPATH')) {
    exit;
}

class ThemeJSON
{
    /**
     * Central cache for all data
     *
     * Keys:
     *   'theme' => decoded theme.json
     *   'color:{slug}:{stripHash}' => cached color values
     */
    private static array $cache = [];


    /**
     * Convert theme.json array to Customizer choice array
     *
     * @param array $data
     * @return array 
     */
    public static function data_to_choices(array $data): array
    {
        $choices = [];
        foreach ($data as $entry) {
            $choices[$entry['slug']] = $entry['name'];
        }
        return $choices;
    }

    public static function get_all_settings(): array
    {
        return wp_get_global_settings();
    }

    /**
     * Get default (as snake_case) from css var in theme.json
     *
     * @param array  $path        Path inside theme.json (snake_case accepted).
     * @param string $search_term Term to match against (snake_case accepted).
     * @param string $search_key  Key in theme.json array to search against (snake_case accepted).
     * @param string $output_key  Key in theme.json array to output (snake_case accepted).
     *
     * @return string Snake_case default setting slug
     */
    public static function get_default(
        array $path,
        string $search_term = '',
        string $search_key = '',
        string $output_key = ''
    ): string {
        // Convert path items
        $camel_path = array_map([Functions::class, 'snake_to_camel'], $path);

        if ($search_key !== '') {
            $search_key = Functions::snake_to_camel($search_key);
        }
        if ($output_key === '' && $search_key !== '') {
            $output_key = $search_key;
        } elseif ($output_key !== '') {
            $output_key = Functions::snake_to_camel($output_key);
        }

        $setting = wp_get_global_settings($camel_path);

        if ($search_term !== '' && $search_key !== '' && is_array($setting)) {
            $search_term = Functions::snake_to_camel($search_term);
            foreach ($setting as $item) {
                if (isset($item[$search_key]) && $item[$search_key] === $search_term) {
                    if (isset($item[$output_key])) {
                        $setting = $item[$output_key];
                    }
                    break;
                }
            }
        }

        if (is_string($setting)) {
            $snake_slug = Functions::get_slug_from_css_var($setting, true);
            return $snake_slug;
        }

        return '';
    }

    public static function get_settings(array $path, ?string $slug = null): array
    {
        if (empty($path)) {
            Functions::error_log('ThemeJSON::get_settings() called with empty path.');
            return [];
        }



        // Append 'theme' for presets 
        // for presets, last item in array can be default, theme, or custom (from editor)
        $type = $path[0]; // 'custom' otherwise its 'color' etc 
        if ($type !== 'custom') {
            $path[] = 'theme';
        }

        $cache_key = implode('_', $path) . ($slug ? "_$slug" : '');
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $camel_path = array_map(fn($p) => Functions::snake_to_camel($p), $path);
        $data = wp_get_global_settings($camel_path);
        if ($data === null) {
            return [];
        }

        $data = Functions::normalize_camel_keys_recursive($data);

        // --- Helper: build CSS var like WP does ---
        $build_css_var = function (string $slug) use ($type, $path, $camel_path): string {
            $segments = [];

            if ($type === 'custom') {
                // ✅ Custom: path is already aligned with the desired output
                $segments = $path;
            } else {
                // ✅ Preset: handle mappings
                $segments[] = 'preset';

                switch ($camel_path[0]) {
                    case 'color':
                        if (isset($camel_path[1])) {
                            if ($camel_path[1] === 'palette') {
                                $segments[] = 'color';
                            } elseif ($camel_path[1] === 'gradients') {
                                // gradient presets
                                $segments[] = 'gradient';
                            } elseif ($camel_path[1] === 'duotone') {
                                // duotone presets — maybe map differently
                                $segments[] = 'duotone';
                            }
                        }
                        break;

                    case 'typography':
                        if (isset($camel_path[1])) {
                            if ($camel_path[1] === 'fontFamilies') {
                                $segments[] = 'font-family';
                            } elseif ($camel_path[1] === 'fontSizes') {
                                $segments[] = 'font-size';
                            }
                        }
                        break;

                    case 'spacing':
                        if (isset($camel_path[1])) {
                            if ($camel_path[1] === 'spacingSizes' || $camel_path[1] === 'spacing_scale') {
                                $segments[] = 'spacing';
                            }
                        }
                        break;

                    case 'border':
                        // maybe border presets: border.radius, border.width etc
                        if (isset($camel_path[1])) {
                            if ($camel_path[1] === 'radius') {
                                $segments[] = 'border-radius';
                            } elseif ($camel_path[1] === 'width') {
                                $segments[] = 'border-width';
                            }
                            // etc
                        }
                        break;

                    case 'shadow':
                        // shadow presets if present
                        $segments[] = 'shadow';
                        break;

                        // other spec keys as needed
                }
            }

            // Always normalize slug → kebab-case
            $slug = Functions::to_kebab($slug);
            $segments[] = $slug;

            return '--wp--' . implode('--', $segments);
        };



        $formatted = [];

        // If numeric array of items (like color palettes, font sizes, font families)
        if (is_array($data) && array_values($data) === $data) {
            foreach ($data as $item) {
                if (isset($item['slug'])) {
                    $item['css_var'] = $build_css_var($item['slug']);

                    // Ensure consistent `value` is output
                    if (isset($item['size'])) {
                        $item['value'] = $item['size'];
                    } elseif (isset($item['font_family'])) {
                        $item['value'] = $item['font_family'];
                    } elseif (isset($item['color'])) {
                        $item['value'] = $item['color'];
                    }
                    $formatted[] = $item;
                }
            }
        }
        // Associative arrays (like custom values or non-preset items)
        elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $formatted[] = [
                        'slug'    => $key,
                        'value'   => $value,
                        'name'    => Functions::snake_to_title($key),
                        'css_var' => $build_css_var($key),
                    ];
                } else {
                    if (isset($value['slug'])) {
                        $value['css_var'] = $build_css_var($value['slug']);
                        $formatted[] = $value;
                    } else {
                        foreach ($value as $sub_key => $sub_value) {
                            if (!is_array($sub_value)) {
                                $formatted[] = [
                                    'slug'    => $sub_key,
                                    'value'   => $sub_value,
                                    'name'    => Functions::snake_to_title($sub_key),
                                    'css_var' => $build_css_var($sub_key),
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Filter by slug if requested
        if ($slug !== null) {
            foreach ($formatted as $item) {
                if (isset($item['slug']) && $item['slug'] === $slug) {
                    return self::$cache[$cache_key] = $item; // single flat array
                }
            }
            return self::$cache[$cache_key] = [];
        }

        return self::$cache[$cache_key] = $formatted;
    }

    /**
     * Apply modifications to theme.json output.
     *
     * @param array $mods  Example:
     * [
     *   'styles' => [
     *     'color' => ['text' => '#222'],
     *     'typography' => ['fontFamily' => 'Georgia, serif']
     *   ],
     * ]
     */
    public function update(array $mods)
    {

        // Add filter once
        add_filter('wp_theme_json_data', function ($theme_json) use ($mods) {

            // Get the existing combined theme.json data (core + theme + user)
            $data = $theme_json->get_data();

            // Merge your modifications into the existing data
            $data = array_replace_recursive($data, $mods);

            // Write modified data back into the object
            $theme_json->update_data($data);

            return $theme_json;
        }, 20); // priority after core/theme merge
    }
}
