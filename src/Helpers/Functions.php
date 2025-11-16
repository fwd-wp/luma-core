<?php

namespace Luma\Core\Helpers;


/**
 * Helper functions (Generic)
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */
class Functions
{

    /**
     * Logs an error with automatic class and method context.
     *
     * @param string $message Custom error message
     */
    public static function error_log(string $message): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller_class    = $trace[1]['class'] ?? 'global';
        $caller_function = $trace[1]['function'] ?? 'global';

        error_log("{$caller_class}::{$caller_function} - {$message}");
    }

    // --- camelCase transformations (used on JSON slugs)

    public static function camel_to_kebab(string $string): string
    {
        if (function_exists('_wp_to_kebab_case')) {
            return _wp_to_kebab_case($string);
        }

        // Fallback that matches WP behavior
        $string = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);
        $string = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $string);
        $string = preg_replace('/([a-zA-Z])([0-9])/', '$1-$2', $string);
        $string = preg_replace('/([0-9])([a-zA-Z])/', '$1-$2', $string);

        return strtolower($string);
    }

    public static function camel_to_title($string): string
    {
        // Handle boundaries:
        // 1. lower → upper  (testTitle → test Title)
        // 2. upper → upper+lower (XMLHttp → XML Http)
        // 3. letter → digit (Title3 → Title 3)
        $string = preg_replace('/(?<=\p{Ll})(\p{Lu})|(?<=\p{Lu})(\p{Lu}\p{Ll})|(?<=\p{L})(\p{Nd})/u', ' $1$2$3', $string);

        // Trim and uppercase words
        return ucwords(trim($string));
    }

    public static function camel_to_snake(string $input): string
    {
        // Add underscore before each uppercase letter (except at the start)
        $snake = preg_replace('/([a-z])([A-Z])/', '$1_$2', $input);

        // Add underscore before numbers when they follow letters
        $snake = preg_replace('/([a-zA-Z])([0-9])/', '$1_$2', $snake);

        // Convert to lowercase
        return strtolower($snake);
    }

    /**
     * Normalize array keys: camelCase → snake_case (recursively).
     *
     * @param mixed $data
     * @return mixed
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

    // --- snake_case transforamtions (used on php slugs)

    public static function snake_to_camel(string $string, bool $capitalize_first = false): string
    {
        // camel will pass through
        // Split on underscores, capitalize words, then join back
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if (! $capitalize_first) {
            // Lowercase first char for camelCase
            $str = lcfirst($str);
        }

        return $str;
    }

    public static function snake_to_kebab(string $string): string
    {
        return str_replace('_', '-', $string);
    }

    public static function snake_to_title(string $string): string
    {
        // Replace underscores with spaces
        $string = str_replace('_', ' ', $string);

        // Uppercase each word
        return ucwords($string);
    }

    // --- kebab-case transformations (used on css variable slugs)

    public static function to_kebab(string $input): string
    {
        // Convert camelCase → snake_case first
        $snake = preg_replace('/([a-z])([A-Z])/', '$1_$2', $input);

        // Normalize underscores → hyphens
        $kebab = str_replace('_', '-', $snake);

        return strtolower($kebab);
    }

    public static function kebab_to_camel(string $kebab, bool $capitalize_first = false): string
    {
        // camel can be passed through
        // Trim any leading or trailing hyphens
        $str = trim($kebab, '-');
        // Replace hyphens with spaces, capitalize words, remove spaces
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $str)));

        // If not PascalCase, lowercase the first character
        if (! $capitalize_first) {
            $str = lcfirst($str);
        }

        return $str;
    }

    public static function kebab_to_snake(string $string): string
    {
        // Lowercase the string
        $string = strtolower($string);

        // Replace one or more hyphens with a single underscore
        $string = preg_replace('/-+/', '_', $string);

        // Trim any leading or trailing underscores
        $string = trim($string, '_');

        return $string;
    }

    // --- CSS Variable manipulation

    // returns the end of a wp css var, retuns in CamelCase, or snake_case if set
    public static function get_slug_from_css_var(string $css_var, bool $snake_case = false): string
    {
        // Remove 'var(' at the start and ')' at the end if present
        if (str_starts_with($css_var, 'var(') && str_ends_with($css_var, ')')) {
            $css_var = substr($css_var, 4, -1); // removes first 4 chars and last char
        }

        // Trim any leftover dashes and split
        $parts = explode('--', trim($css_var, '-'));

        if ($snake_case) {
            // return last part as snake case
            return self::kebab_to_snake(end($parts));
        }
        return self::kebab_to_camel(end($parts));
    }

    // needs fixing, if used
    // todo: remove
    // public static function css_var_from_snake(array $parts): string
    // {
    //     foreach ($parts as $part) {
    //         if (!is_string($part)) {
    //             Functions::error_log("$part is not a string");
    //             return '';
    //         }
    //     }

    //     return '--wp--' . implode('--', $parts);
    // }
}
