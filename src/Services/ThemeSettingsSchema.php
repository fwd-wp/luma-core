<?php

namespace Luma\Core\Services;

use Luma\Core\Core\Config;
use Luma\Core\Helpers\Functions;
use Luma\Core\Setup\CustomizeBase;

class ThemeSettingsSchema
{
    private static bool $cache_set = false;
    private static array $cache = [];

    // for debugging and listing all settings
    public static function get_settings_list($prefix = false, $default_and_value = false): array
    {
        // prefixed with sagewood if true
        $schema =  self::get();
        $list = [];
        $theme_prefix = Config::get_prefix();
        foreach ($schema as $group => $values) {
            foreach ($values['settings'] as $id => $items) {
                if ($items['type'] !== 'subheading' || $items['type'] !== 'button') {
                    if ($default_and_value) {
                        $value = self::theme_mod_with_default("{$group}_{$id}",true);
                    } else {
                        $value = $items['label'];
                    }

                    if ($prefix) {
                        $list["{$theme_prefix}{$group}_{$id}"] = $value;
                    } else {
                        $list["{$group}_{$id}"] = $value;
                    }
                }
            }
        }
        return $list;
    }
    private static function set_cache(): void
    {
        self::$cache_set = true;
    }

    public static function get(): array
    {
        if (!self::$cache_set) {
            self::set_cache();
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
        if (!self::$cache_set) {
            self::set_cache();
        }
        if ($merge) {
            // self::$cache = array_merge(self::$cache, $settings);
            self::$cache = Functions::array_merge_recursive_distinct(self::$cache, $settings);
        } else {
            self::$cache = $settings;
        }
    }

    public static function get_theme_mod(string $key): mixed
    {
        return self::theme_mod_with_default($key);
    }
    public static function get_theme_mod_with_default_and_value(string $key): mixed
    {
        return self::theme_mod_with_default($key, true);
    }

    /**
     * dont pass in theme prefix only 'group_setting_name'
     * must be called later e.g. from within template to ensure settings are set up
     */
    public static function theme_mod_with_default(string $full_key, ?bool $default_and_value = false): mixed
    {
        if (!self::$cache_set) {
            self::set_cache();
        }

        // Extract group and key
        $parts = explode('_', $full_key, 2); // split into 2 parts only
        if (count($parts) < 2) {
            // fallback if key format is invalid
            return get_theme_mod($full_key);
        }

        [$group_name, $sub_key] = $parts;

        $theme_prefix = Config::get_prefix();
        $prefixed_key = "{$theme_prefix}_{$full_key}";

        // utlize default from settings list, if it exists
        $item = self::$cache[$group_name]['settings'][$sub_key] ?? null;
        if (isset($item)) {
            $default = $item['default'] ?? null;
            $type = $item['type'] ?? null;
            $choices = $item['choices'] ?? [];
            $default_fallback = CustomizeBase::get_default($default, $type, $choices) ?? null;

            $default = $default ?? $default_fallback;
            if ($default_and_value) {
                return [
                    'default' => $default ?? null,
                    'value' => get_theme_mod($prefixed_key, $default),
                ];
            }

            if ($default) {
                return get_theme_mod($prefixed_key, $default);
            }
        }


        return get_theme_mod($prefixed_key);
    }
}
