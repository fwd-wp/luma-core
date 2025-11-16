<?php

namespace Twenty\One\Models;

use Twenty\One\Helpers\Functions;
use \WP_Theme_JSON_Resolver;

if (! defined('ABSPATH')) {
    exit;
}

class ThemeJSONnew
{
    /**
     * Central cache for all data
     *
     * Keys:
     * 'json:{origin}' => decoded theme.json
     * 'color:{slug}:{stripHash}' => cached color values
     */
    private array $cache = [];


    /**
     * stores data for chaining
     */
    protected array|string $data = [];

    /**
     * original path - used for css var generation
     */
    protected array $original_path = [];

    /**
     * Load theme.json settings for a given path.
     *
     * @param array $path Example: ['color', 'palette']
     * @param string $origin 'theme', 'block', 'custom', or 'default'
     */
    public function load(array $path = [], string $origin = 'theme', bool $snake_case = false): self
    {
        // Map snake_case to camelCase if needed
        $path = $snake_case ? array_map(fn($p) => Functions::snake_to_camel($p), $path) : $path;

        $this->data = $this->fetch_data($path, $origin);
        $this->original_path = $path;
        return $this;
    }

    /**
     * filter output by slug for built in values (not custom)
     *
     * @param string $slug e.g. 'primary'
     * @return object (single flat array)
     */
    public function filter_by_slug(string $slug): self
    {
        if (!is_array($this->data)) return $this;

        foreach ($this->data as $item) {
            if (isset($item['slug']) && $item['slug'] === $slug) {
                $this->data = $item; // single flat array
            } else {
                break;
            }
        }

        return $this;
    }

    /**
     * Terminal method: return data in original format
     */
    public function raw(): array|string
    {
        return $this->data;
    }

    /**
     * Terminal method: only outputs a string
     */
    public function raw_string(): string
    {
        if (is_string($this->data)) {
            return $this->data;
        } else {
            return '';
        }
    }
    /**
     * Terminal method: only outputs an array
     */
    public function raw_array(): array
    {
        if (is_array($this->data)) {
            return $this->data;
        } else {
            return [];
        }
    }

    /**
     * Terminal method: return full data array in snake case format
     *
     * //TODO: not normalizing camelCase keys to snake_case
     *
     */
    public function snake_case(): array|string
    {
        return Functions::normalize_camel_keys_recursive($this->data);
    }

    /**
     * Terminal method: return choices array (slug => name)
     */
    public function choices(): array
    {
        if (!is_array($this->data)) return [];

        return $this->convert_to_choices($this->data);
    }

    /**
     * Terminal method: return with value if not present in preset, also snake_case if set
     */
    public function normalized(bool $snake_case = false): array
    {
        if (!is_array($this->data)) return [];

        $data = $this->normalize_data($this->data); // fills value keys

        if ($snake_case) {
            return Functions::normalize_camel_keys_recursive($data);
        }

        return $data; // original camelCase
    }

    /**
     * Terminal method: converts slug string to associated css var based on path passed in to load()
     * 
     * $this->data can be a string (slug) or an associative array with a slug key and value
     */
    public function css_var(): string
    {
        if (is_array($this->data) && isset($this->data['slug'])) {
            return $this->generate_css_var($this->data['slug']);
        }

        if (is_string($this->data)) {
            return $this->generate_css_var($this->data);
        }

        return '';
    }

    /**
     * Terminal method: converts css var to slug string
     * 
     * $this->data must be a css var (string) can be wrapped in var()
     */
    public function slug_from_css_var(): string
    {
        if (is_string($this->data)) {
            return Functions::get_slug_from_css_var($this->data);
        }

        return '';
    }

    /**
     * private helper to fetch theme.json settings
     *
     * $source - default (wp default), theme, custom (custom only used with FSE)
     *
     * @return array camelCase WP theme.json array
     */
    private function fetch_data(array $path, string $origin): array|string
    {
        // Build cache key
        $cache_key = implode(':', $path) . ":$origin";

        // Return cached data if available
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }

        $json_cache_key = "json:$origin";
        if (isset($this->cache[$json_cache_key])) {
            $data = $this->cache[$json_cache_key];
        } else {
            $this->cache[$json_cache_key] = $data = WP_Theme_JSON_Resolver::get_merged_data($origin)->get_data();
        }

        foreach ($path as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                // doesn't go any deeper if no match is found
                break;
            }
        }

        return $this->cache[$cache_key] = $data;
    }

    /**
     * Private helper
     * Generate CSS variable name from theme.json preset path
     * Supports all theme.json v3 preset groups.
     */
    private function generate_css_var(string $slug): string
    {
        if (!is_string($slug) || $slug === '') {
            return '';
        }

        $path = $this->original_path;
        if (!is_array($path) || empty($path)) {
            return '';
        }

        // Strip leading 'settings'
        if ($path[0] === 'settings') {
            array_shift($path);
        }

        $segments = [];

        // Custom tokens → `--wp--custom--...`
        if ($path[0] === 'custom') {
            $segments[] = 'custom';
            // Keep exact structure: custom > group > key
            if (isset($path[1])) $segments[] = Functions::to_kebab($path[1]);
            if (isset($path[2])) $segments[] = Functions::to_kebab($path[2]);
        } else {
            // All presets use `preset` prefix
            $segments[] = 'preset';

            // Map preset group → WP variable prefix
            $map = [
                'color' => [
                    'palette' => 'color',
                    'gradients' => 'gradient',
                    'duotone' => 'duotone',
                ],
                'typography' => [
                    'fontFamilies' => 'font-family',
                    'fontSizes' => 'font-size',
                ],
                'spacing' => [
                    'spacingSizes' => 'spacing',
                    'spacing_scale' => 'spacing',
                ],
                'border' => [
                    'radius' => 'border-radius',
                    'width' => 'border-width',
                ],
                'shadow' => [
                    // Shadow presets do not have subgroups
                    '*' => 'shadow',
                ],
            ];

            $group = $path[0] ?? null;
            $sub = $path[1] ?? null;

            if (isset($map[$group])) {
                if (isset($map[$group][$sub])) {
                    $segments[] = $map[$group][$sub];
                } elseif (isset($map[$group]['*'])) {
                    $segments[] = $map[$group]['*'];
                }
            }
        }

        // Append normalized slug
        $segments[] = Functions::to_kebab($slug);

        return '--wp--' . implode('--', $segments);
    }

    /**
     * Private helper: convert internal data to slug => name
     */
    private function convert_to_choices(array $path): array
    {
        // Check that this is a list of entries with slugs
        if (!empty($path) && isset($path[0]) && is_array($path[0]) && array_key_exists('slug', $path[0])) {
            $choices = [];

            foreach ($path as $entry) {
                // Must have slug AND name — otherwise skip
                if (!isset($entry['slug']) || !isset($entry['name'])) {
                    continue;
                }

                $choices[$entry['slug']] = $entry['name'];
            }

            return $choices;
        }

        return [];
    }

    /**
     * Private helper
     * normalizes data so all ararys contain 'value'
     * works on top level (array of arrays )
     */
    private function normalize_data($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $formatted = [];

        // Preset property names that map to 'value'
        $presetValueKeys = ['size', 'fontFamily', 'color', 'gradient', 'shadow', 'colors'];

        // Numeric array → multiple preset items
        if (array_values($data) === $data) {
            foreach ($data as $item) {
                if (!is_array($item)) continue;

                // Only add 'value' if not already present (some entries already have it)
                if (!isset($item['value'])) {
                    foreach ($presetValueKeys as $prop) {
                        if (isset($item[$prop])) {
                            $item['value'] = $item[$prop];
                            break; // first match wins
                        }
                    }
                }

                $formatted[] = $item;
            }
        } else {
            // Single associative array (custom entry), or too deep
            $formatted = $data;
        }

        return $formatted;
    }
}
