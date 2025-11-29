<?php

namespace Luma\Core\Core;

class Config
{

    private static string $prefix_snake = 'luma_core';
    private static string $prefix_snake_core = 'luma_core';
    private static string $prefix_kebab = 'luma-core';
    private static string $prefix_kebab_core = 'luma-core';
    private static string $text_domain  = 'luma-core';
    private static string $text_domain_core  = 'luma-core';
    private static string $minimum_wp_version  = '6.8';
    private static string $minimum_php_version = '7.4';

    public static function set_prefix(string $prefix): void
    {
        self::$prefix_snake = $prefix;
    }
    public static function get_prefix(): string
    {
        return self::$prefix_snake;
    }
    public static function get_prefix_core(): string
    {
        return self::$prefix_snake_core;
    }

    public static function set_prefix_kebab(string $prefix): void
    {
        self::$prefix_kebab = $prefix;
    }
    public static function get_prefix_kebab(): string
    {
        return self::$prefix_kebab;
    }
    public static function get_prefix_kebab_core(): string
    {
        return self::$prefix_kebab_core;
    }

    public static function set_domain(string $domain): void
    {
        self::$text_domain = $domain;
    }
    public static function get_domain(): string
    {
        return self::$text_domain;
    }
    public static function get_domain_core(): string
    {
        return self::$text_domain_core;
    }

    public static function set_minimum_wp_version(string $version): void
    {
        self::$minimum_wp_version = $version;
    }
    public static function get_minimum_wp_version(): string
    {
        return self::$minimum_wp_version;
    }

    public static function set_minimum_php_version(string $version): void
    {
        self::$minimum_php_version = $version;
    }
    public static function get_minimum_php_version(): string
    {
        return self::$minimum_php_version;
    }

    public static function get_theme_version(): string
    {
        if (defined('WP_LOCAL_DEV') && WP_LOCAL_DEV) {
            return date('Ymd-His');
        }
        $path = get_template_directory() . '/package.json';
        if (! file_exists($path)) {
            return '1.0.0';
        }

        $data = json_decode(file_get_contents($path), true);
        return $data['version'] ?? '1.0.0';
    }
}
