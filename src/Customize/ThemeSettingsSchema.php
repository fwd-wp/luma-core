<?php

namespace Luma\Core\Customize;

use Luma\Core\Core\Config;
use Luma\Core\Helpers\Functions;
use Luma\Core\Customize\CustomizeBase;

class ThemeSettingsSchema
{
    private static array $cache = [];

    // stores theme variant prefix, self::set_prefix() needs to be run first if prefix is needed in a method
    private static string $prefix;

    private static function set_prefix(): void
    {
        if(!isset(self::$prefix)) {
        // uses theme variant prefix for settings as they are stored to DB
        self::$prefix = Config::get_prefix() ?? 'luma_core';
        }
    }

    /**
     * Get a list of all settings with their default and current value.
     * @param bool $show_prefix Whether to prefix the keys with the theme prefix. Only used to see whats available to custoize.
     * @return array An associative array of settings keys to their default and current value.
     * 
     */
    public static function get_settings_list($show_prefix = false): array
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
                $key = "{$group}_{$id}";
                // Build key for non core settings
                if ($group == 'wp-core') {
                    $list[$key] =  self::get_theme_mod_default_and_value($key, true);
                } else {
                    $setting = ($show_prefix ? self::$prefix . '_' : '') . $key;
                    $list[$setting] =  self::get_theme_mod_default_and_value($key);
                }
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
    public static function get_theme_mod(string $key, bool $core = false): mixed
    {
        if ($core === false) {
            self::set_prefix();
            $prefix = self::$prefix;
            $default = self::get_theme_mod_default($key);
            // need to prefix with theme variant prefix for get_theme_mod()
            $key = "{$prefix}_{$key}";
            //print('<pre>' . print_r($key, true) . '</pre> ');
        } else {
            $default = self::get_theme_mod_default($key, true);
            // no prefixing of key for get_theme_mod
        }

        return get_theme_mod($key, $default);
    }

    public static function get_theme_mod_default(string $key, bool $core = false): mixed
    {
        if ($core === false) {
            // Extract group and key
            $parts = explode('_', $key, 2); // split into 2 parts only
            if (count($parts) < 2) {
                // fallback if key format is invalid
                return get_theme_mod($key);
            }

            [$group_name, $sub_key] = $parts;

            // utlize default from settings list, if it exists
            $item = self::$cache[$group_name]['settings'][$sub_key] ?? null;
        } else {
            $item = self::$cache['wp-core']['settings'][$key] ?? null;
        }

        if (!isset($item)) {
            return null;
        }

        $default = $item['default'] ?? null;
        $type = $item['type'] ?? '';
        $choices = $item['choices'] ?? [];
        $default_fallback = CustomizeBase::get_default($type, $choices) ?? null;
        $default = $default ?? $default_fallback;
        return $default;
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
