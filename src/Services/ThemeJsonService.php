<?php

namespace Luma\Core\Services;

use Luma\Core\Helpers\Functions;
use \WP_Theme_JSON_Resolver;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class for accessing data from theme.json
 * 
 */
class ThemeJsonService
{
    private static array $cache = [];
    private string $origin = 'theme';
    protected array|string $data = [];
    protected array $original_path = [];

    public function __construct(string $origin = 'theme', array|null $override_data = null)
    {
        $this->origin = $origin;
        $this->data = [];

        // For testing: use override data instead of WP_Theme_JSON_Resolver
        if ($override_data !== null) {
            self::$cache["json:{$this->origin}"] = $override_data;
        }
    }

    public static function reset_cache(): void
    {
        self::$cache = [];
    }

    public static function alter_css_generation(): void
    {
        
    }

    /**
     * Explicitly load and get data (lazy load)
     * earlist safe point to call is 'after_setup_theme'
     */
    public function get(array $path = [], bool $snake_case = false): self
    {
        // Lazy-load theme.json only on first get
        if (!isset(self::$cache["json:{$this->origin}"])) {
            self::$cache["json:{$this->origin}"] = WP_Theme_JSON_Resolver::get_merged_data($this->origin)->get_data();
        }

        // Apply snake_case normalization to full dataset first
        $dataToUse = self::$cache["json:{$this->origin}"];
        if ($snake_case && is_array($dataToUse)) {
            $dataToUse = Functions::normalize_camel_keys_recursive($dataToUse);
        }

        // Drill down into path if provided
        $this->data = $this->drill_down($path, $dataToUse);
        $this->original_path = $path;

        // Ensure $this->data is never null
        if ($this->data === null) {
            $this->data = [];
        }

        return $this;
    }

    /**
     * Optional: get a single item by slug
     */
    public function get_by_slug(string $slug): self
    {
        if (!is_array($this->data)) return $this;

        foreach ($this->data as $item) {
            if (isset($item['slug']) && $item['slug'] === $slug) {
                $this->data = $item;
                break;
            }
        }

        if (!is_array($this->data)) {
            $this->data = [];
        }

        return $this;
    }

    /** Raw accessors */
    public function raw(): array|string
    {
        if (is_array($this->data) || is_string($this->data)) {
            return $this->data;
        }
        return [];
    }

    public function raw_array(): array
    {
        return is_array($this->data) ? $this->data : [];
    }

    public function raw_string(): string
    {
        return is_string($this->data) ? $this->data : '';
    }

    /** Helpers */
    public function snake_case(): array|string
    {
        // changes camelCase keys to snake_case
        if (is_array($this->data)) {
            return Functions::normalize_camel_keys_recursive($this->data);
        }
        return $this->data;
    }

    public function choices(): array
    {
        return is_array($this->data) ? $this->convert_to_choices($this->data) : [];
    }

    public function normalized(bool $snake_case = false): array
    {
        if (!is_array($this->data)) return [];

        $data = $this->normalize_data($this->data);
        return $snake_case ? Functions::normalize_camel_keys_recursive($data) : $data;
    }

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

    public function slug_from_css_var(): string
    {
        return is_string($this->data) ? Functions::get_slug_from_css_var($this->data) : '';
    }

    /** --- Internal helpers --- */

    private function drill_down(array $path, array|string|null $data = null): array|string|null
    {
        $data = $data ?? self::$cache["json:{$this->origin}"];

        if (empty($path)) {
            return $data;
        }

        $cache_key = 'json:' . $this->origin . ':' . implode(':', $path);

        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        foreach ($path as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $data = [];
                break;
            }
        }

        return self::$cache[$cache_key] = $data;
    }

    private function generate_css_var(string $slug): string
    {
        if (!is_string($slug) || $slug === '') return '';
        $path = $this->original_path;
        if (!is_array($path) || empty($path)) return '';

        if ($path[0] === 'settings') array_shift($path);

        $segments = [];
        if ($path[0] === 'custom') {
            $segments[] = 'custom';
            if (isset($path[1])) $segments[] = Functions::to_kebab($path[1]);
            if (isset($path[2])) $segments[] = Functions::to_kebab($path[2]);
        } else {
            $segments[] = 'preset';
            $map = [
                'color' => ['palette' => 'color', 'gradients' => 'gradient', 'duotone' => 'duotone'],
                'typography' => ['fontFamilies' => 'font-family', 'fontSizes' => 'font-size'],
                'spacing' => ['spacingSizes' => 'spacing', 'spacing_scale' => 'spacing'],
                'border' => ['radius' => 'border-radius', 'width' => 'border-width'],
                'shadow' => ['*' => 'shadow'],
            ];

            $group = $path[0] ?? null;
            $sub = $path[1] ?? null;
            $segments[] = $map[$group][$sub] ?? $map[$group]['*'] ?? '';
        }

        $segments[] = Functions::to_kebab($slug);
        return '--wp--' . implode('--', $segments);
    }

    private function convert_to_choices(array $path): array
    {
        $choices = [];
        if (!empty($path) && isset($path[0]) && is_array($path[0]) && isset($path[0]['slug'])) {
            foreach ($path as $entry) {
                if (isset($entry['slug'], $entry['name'])) {
                    $choices[$entry['slug']] = $entry['name'];
                }
            }
        }
        return $choices;
    }

    private function normalize_data($data): array
    {
        if (!is_array($data)) return [];
        $formatted = [];
        $presetValueKeys = ['size', 'fontFamily', 'color', 'gradient', 'shadow', 'colors'];

        if (array_values($data) === $data) {
            foreach ($data as $item) {
                if (!is_array($item)) continue;
                if (!isset($item['value'])) {
                    foreach ($presetValueKeys as $prop) {
                        if (isset($item[$prop])) {
                            $item['value'] = $item[$prop];
                            break;
                        }
                    }
                }
                $formatted[] = $item;
            }
        } else {
            $formatted = $data;
        }

        return $formatted;
    }
}
