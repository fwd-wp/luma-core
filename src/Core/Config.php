<?php

namespace Luma\Core\Core;

class Config
{

    /*

    example config structure:
    Config::init([
        'prefix_snake'       => 'luma_sagewood',
        'prefix_kebab'       => 'luma-sagewood',
        'prefix_camel'       => 'lumaSagewood',
        'text_domain'        => 'luma-sagewood',
        'minimum_wp_version' => '6.8',
        'minimum_php_version' => '7.4',
    ]);

    */
    private static array $config = [
        'prefix_snake'       => 'luma_core',
        'prefix_snake_core'  => 'luma_core',
        'prefix_kebab'       => 'luma-core',
        'prefix_kebab_core'  => 'luma-core',
        'prefix_camel'       => 'lumaCore',
        'prefix_camel_core'  => 'lumaCore',
        'text_domain'        => 'luma-core',
        'text_domain_core'   => 'luma-core',
        'minimum_wp_version' => '6.8',
        'minimum_php_version' => '7.4',
    ];

    /**
     * Initialize config with an array of key => value pairs.
     *
     * @param array $config
     */
    public static function init(array $config): void
    {
        self::$config = wp_parse_args($config, self::$config);
    }

    // --- Specific getters for convenience and backward compatibility ---

    public static function get_prefix(): string
    {
        return self::$config['prefix_snake'];
    }

    public static function get_prefix_core(): string
    {
        return self::$config['prefix_snake_core'];
    }

    public static function get_prefix_kebab(): string
    {
        return self::$config['prefix_kebab'];
    }

    public static function get_prefix_kebab_core(): string
    {
        return self::$config['prefix_kebab_core'];
    }
    public static function get_prefix_camel(): string
    {
        return self::$config['prefix_camel'];
    }

    public static function get_prefix_camel_core(): string
    {
        return self::$config['prefix_camel_core'];
    }

    public static function get_domain(): string
    {
        return self::$config['text_domain'];
    }

    public static function get_domain_core(): string
    {
        return self::$config['text_domain_core'];
    }

    public static function get_minimum_wp_version(): string
    {
        return self::$config['minimum_wp_version'];
    }

    public static function get_minimum_php_version(): string
    {
        return self::$config['minimum_php_version'];
    }

    public static function get_theme_version(): string
    {
        if (defined('WP_ENVIRONMENT_TYPE') && (WP_ENVIRONMENT_TYPE === 'local' || WP_ENVIRONMENT_TYPE === 'development')) {
            return date('Ymd-His');
        }

        $path = get_template_directory() . '/package.json';
        if (!file_exists($path)) {
            return '1.0.0';
        }

        $data = json_decode(file_get_contents($path), true);
        return $data['version'] ?? '1.0.0';
    }
}
