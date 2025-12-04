<?php

namespace Luma\Core\Helpers;

/**
 * Helper functions (Generic)
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
class Functions
{
    /**
     * Logs an error with automatic class and method context.
     *
     * @since Luma-Core 1.0
     *
     * @param string $message Custom error message.
     * @return void
     */
    public static function error_log(string $message): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller_class    = $trace[1]['class'] ?? 'global';
        $caller_function = $trace[1]['function'] ?? 'global';

        error_log("{$caller_class}::{$caller_function} - {$message}");
    }


    // --------------------------
    // CamelCase transformations
    // --------------------------

    /**
     * Convert camelCase or PascalCase to kebab-case.
     *
     * @since Luma-Core 1.0
     *
     * @param string $string Input string in camelCase.
     * @return string Kebab-case string.
     */
    public static function camel_to_kebab(string $string): string
    {
        if (function_exists('_wp_to_kebab_case')) {
            return _wp_to_kebab_case($string);
        }

        $string = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);
        $string = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $string);
        $string = preg_replace('/([a-zA-Z])([0-9])/', '$1-$2', $string);
        $string = preg_replace('/([0-9])([a-zA-Z])/', '$1-$2', $string);

        return strtolower($string);
    }

    /**
     * Convert camelCase or PascalCase to a title case string.
     *
     * @since Luma-Core 1.0
     *
     * @param string $string Input string in camelCase.
     * @return string Human-readable title case.
     */
    public static function camel_to_title(string $string): string
    {
        $string = preg_replace('/(?<=\p{Ll})(\p{Lu})|(?<=\p{Lu})(\p{Lu}\p{Ll})|(?<=\p{L})(\p{Nd})/u', ' $1$2$3', $string);
        return ucwords(trim($string));
    }

    /**
     * Convert camelCase to snake_case.
     *
     * @since Luma-Core 1.0
     *
     * @param string $input Input camelCase string.
     * @return string Snake_case string.
     */
    public static function camel_to_snake(string $input): string
    {
        $snake = preg_replace('/([a-z])([A-Z])/', '$1_$2', $input);
        $snake = preg_replace('/([a-zA-Z])([0-9])/', '$1_$2', $snake);
        return strtolower($snake);
    }

    /**
     * Recursively normalize array keys from camelCase to snake_case.
     *
     * @since Luma-Core 1.0
     *
     * @param mixed $data Array or value to normalize.
     * @return mixed Normalized array or value.
     */
    public static function normalize_camel_keys_recursive($data)
    {
        if (is_array($data)) {
            $normalized = [];
            foreach ($data as $key => $value) {
                $newKey = is_string($key) ? self::camel_to_snake($key) : $key;
                $normalized[$newKey] = self::normalize_camel_keys_recursive($value);
            }
            return $normalized;
        }
        return $data;
    }

    // --------------------------
    // snake_case transformations
    // --------------------------

    /**
     * Convert snake_case to camelCase.
     *
     * @since Luma-Core 1.0
     *
     * @param string $string Input snake_case string.
     * @param bool $capitalize_first Optional. Capitalize first character (PascalCase). Default false.
     * @return string camelCase or PascalCase string.
     */
    public static function snake_to_camel(string $string, bool $capitalize_first = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if (!$capitalize_first) {
            $str = lcfirst($str);
        }
        return $str;
    }

    /**
     * Convert snake_case to kebab-case.
     *
     * @since Luma-Core 1.0
     *
     * @param string $string Input snake_case string.
     * @return string Kebab-case string.
     */
    public static function snake_to_kebab(string $string): string
    {
        return str_replace('_', '-', $string);
    }

    /**
     * Convert snake_case to Title Case.
     *
     * @since Luma-Core 1.0
     *
     * @param string $string Input snake_case string.
     * @return string Title Case string.
     */
    public static function snake_to_title(string $string): string
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    // --- kebab-case transformations

    /**
     * Convert camelCase or PascalCase to kebab-case.
     *
     * @since Luma-Core 1.0
     *
     * @param string $input Input string.
     * @return string Kebab-case string.
     */
    public static function to_kebab(string $input): string
    {
        $snake = preg_replace('/([a-z])([A-Z])/', '$1_$2', $input);
        return strtolower(str_replace('_', '-', $snake));
    }

    /**
     * Convert kebab-case to camelCase or PascalCase.
     *
     * @since Luma-Core 1.0
     *
     * @param string $kebab Input kebab-case string.
     * @param bool $capitalize_first Optional. Capitalize first character (PascalCase). Default false.
     * @return string camelCase or PascalCase string.
     */
    public static function kebab_to_camel(string $kebab, bool $capitalize_first = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', trim($kebab, '-'))));
        if (!$capitalize_first) {
            $str = lcfirst($str);
        }
        return $str;
    }

    /**
     * Convert kebab-case to snake_case.
     *
     * @since Luma-Core 1.0
     *
     * @param string $string Input kebab-case string.
     * @return string Snake_case string.
     */
    public static function kebab_to_snake(string $string): string
    {
        $string = strtolower($string);
        $string = preg_replace('/-+/', '_', $string);
        return trim($string, '_');
    }

    // --------------------------
    // CSS variable utilities
    // --------------------------

    /**
     * Get the slug from a CSS variable.
     *
     * @since Luma-Core 1.0
     *
     * @param string $css_var CSS variable (e.g. var(--wp--custom--color)).
     * @param bool $snake_case Optional. Convert to snake_case. Default false (camelCase returned).
     * @return string Slug of the variable.
     */
    public static function get_slug_from_css_var(string $css_var, bool $snake_case = false): string
    {
        if (str_starts_with($css_var, 'var(') && str_ends_with($css_var, ')')) {
            $css_var = substr($css_var, 4, -1);
        }
        $parts = explode('--', trim($css_var, '-'));
        return $snake_case ? self::kebab_to_snake(end($parts)) : self::kebab_to_camel(end($parts));
    }

    // --------------------------
    // array utilities
    // --------------------------

    /**
     * Recursively merge two arrays, overriding scalar values.
     *
     * @since Luma-Core 1.0
     *
     * @param array $array1 Base array.
     * @param array $array2 Array with values to merge.
     * @return array Merged array.
     */
    public static function array_merge_recursive_distinct(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
