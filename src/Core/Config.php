<?php

namespace Luma\Core\Core;

class Config
{

    /*

    example config structure:
    Config::init([
        'prefix_snake'       => 'luma_core',
        'prefix_kebab'       => 'luma-core',
        'text_domain'        => 'luma-core',
        'minimum_wp_version' => '6.8',
        'minimum_php_version' => '7.4',
    ]);

    */
    private static array $config = [
        'prefix_snake'       => 'luma_core',
        'prefix_snake_core'  => 'luma_core',
        'prefix_kebab'       => 'luma-core',
        'prefix_kebab_core'  => 'luma-core',
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

    // /**
    //  * Generic setter for a single config key.
    //  *
    //  * @param string $key
    //  * @param mixed  $value
    //  */
    // public static function set(string $key, mixed $value): void
    // {
    //     if (array_key_exists($key, self::$config)) {
    //         self::$config[$key] = $value;
    //     } else {
    //         \Luma\Core\Helpers\Functions::error_log(sprintf(
    //             'Config key "%s" does not exist. Cannot set value.',
    //             $key
    //         ));
    //     }
    // }

    // /**
    //  * Generic getter for a single config key.
    //  *
    //  * @param string $key
    //  * @return mixed|null
    //  */
    // public static function get(string $key): mixed
    // {
    //     if (!array_key_exists($key, self::$config)) {
    //         \Luma\Core\Helpers\Functions::error_log(sprintf(
    //             'Config key "%s" does not exist. Returning null.',
    //             $key
    //         ));
    //     }

    //     return self::$config[$key] ?? null;
    // }

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
        if (defined('WP_LOCAL_DEV') && constant('WP_LOCAL_DEV')) {
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
