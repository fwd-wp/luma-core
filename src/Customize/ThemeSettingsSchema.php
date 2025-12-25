<?php

namespace Luma\Core\Customize;

use Luma\Core\Core\Config;
use Luma\Core\Helpers\Functions;
use Luma\Core\Customize\CustomizeBase;

class ThemeSettingsSchema
{
    private static array $cache = [];

    // stores theme variant prefix, self::set_prefix() needs to be run first if prefix is needed in a method
    private static string $prefix = 'luma_core';

    private static function set_prefix(): void
    {
        if (self::$prefix === '') {
            // uses theme variant prefix for settings as they are stored to DB
            self::$prefix = Config::get_prefix() ?? self::$prefix;
        }
    }

    /**
     * Get a list of all settings with their default and current value.
     * @param bool $prefix Whether to prefix the keys with the theme prefix. Only used to see whats available to custoize.
     * @return array An associative array of settings keys to their default and current value.
     * 
     */
    public static function get_settings_list($prefix = false): array
    {
        $schema = self::get();
        if (!is_array($schema)) {
            return []; // nothing to process
        }

        self::set_prefix();

        $list = [];

        foreach ($schema as $group => $values) {
            // Ensure 'settings' exists and is an array
            if (!isset($values['settings']) || !is_array($values['settings'])) {
                continue;
            }

            foreach ($values['settings'] as $id => $items) {
                // Safety: $items must be an array with 'type' and 'label'
                if (!is_array($items)) {
                    continue;
                }

                // Skip subheadings or buttons
                if (isset($items['type']) && in_array($items['type'], ['subheading', 'button'], true)) {
                    continue;
                }

                // Build key
                $key = ($prefix ? self::$prefix : '') . "{$group}_{$id}";

                $list[$key] =  self::get_theme_mod_default_and_value("{$group}_{$id}");
            }
        }

        return $list;
    }

    public static function get(): array
    {
        if (empty(self::$cache)) {
            Functions::error_log("ThemeSettingsSchema cache not set. Returning empty array.");
            return [];
        }
        return self::$cache;
    }

    /**
     * Set the ThemeSettingsSchema.
     * Merges with defaults, by default
     * ensure you are using the correct action for timing if getting 
     * data from a dynamic source
     */
    public static function set(array $settings, bool $merge = true): void
    {
        if ($merge) {
            self::$cache = Functions::array_merge_recursive_distinct(self::$cache, $settings);
        } else {
            self::$cache = $settings;
        }
    }

    /** gets a theme mod value with default from schema the customizer also uses for defaults
     * safe to use in templates
     * 
     */
    public static function get_theme_mod(string $key): mixed
    {
        self::set_prefix();

        // Extract group and key
        $parts = explode('_', $key, 2); // split into 2 parts only
        if (count($parts) < 2) {
            // fallback if key format is invalid
            return get_theme_mod($key);
        }

        [$group_name, $sub_key] = $parts;


        if ($group_name === 'wp-core') {
            $full_key = $sub_key;
        }

        $prefix = self::$prefix;

        $full_key = "{$prefix}_{$key}";

        $default = self::get_theme_mod_default($key);
        if ($default) {
            return get_theme_mod($full_key, $default);
        }

        return get_theme_mod($full_key);
    }

    public static function get_theme_mod_default(string $key): mixed
    {
        // Extract group and key
        $parts = explode('_', $key, 2); // split into 2 parts only
        if (count($parts) < 2) {
            // fallback if key format is invalid
            return get_theme_mod($key);
        }

        [$group_name, $sub_key] = $parts;

        // utlize default from settings list, if it exists
        $item = self::$cache[$group_name]['settings'][$sub_key] ?? null;
        if (isset($item)) {
            $default = $item['default'] ?? null;
            $type = $item['type'] ?? '';
            $choices = $item['choices'] ?? [];
            $default_fallback = CustomizeBase::get_default($type, $choices) ?? null;

            $default = $default ?? $default_fallback;
            return $default;
        }
        return '';
    }

    public static function get_theme_mod_default_and_value(string $key): array
    {
        $default = self::get_theme_mod_default($key);
        $value = self::get_theme_mod($key);
        return [
            'default' => $default,
            'value' => $value,
        ];
    }
}
