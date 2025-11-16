<?php

namespace Twenty\One\Models;

class ThemeMod
{

    // Array stores defaults for consistent retreival throughout theme
    protected static array $defaults = [
        'twenty_one_display_title_and_tagline'    => true,

        'twenty_one_post_display_author_bio'      => false,
        'twenty_one_post_archive_display' => 'excerpt',
        'twenty_one_post__archive_format'                  => 'list',
        'twenty_one_post_width'                   => 'default',
        'twenty_one_post_page_width'              => 'default',
        'twenty_one_post__archive_format'                  => 'list',

        'twenty_one_header_nav_full'              => false,
        'twenty_one_header_sticky'                => false,
        'twenty_one_header_transparent'           => false,
        'twenty_one_header_shrink'                => false,
    ];

    /**
     * Get a theme setting with a default fallback.
     *
     * @param string $key The theme mod key.
     * @return mixed|null Returns the theme mod value, or null if no default is defined.
     */
    public static function get(string $key): mixed
    {
        if (array_key_exists($key, self::$defaults)) {
            $default = self::$defaults[$key];
            return get_theme_mod($key, $default);
        }

        error_log(sprintf('Class ThemeMod: No default found for key: "%s"', $key));
        return null;
    }
}
