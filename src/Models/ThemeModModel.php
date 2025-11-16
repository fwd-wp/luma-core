<?php

namespace Luma\Core\Models;

class ThemeModModel
{

    // Array stores defaults for consistent retreival throughout theme
    protected static array $defaults = [
        'luma_core_display_title_and_tagline'    => true,

        'luma_core_post_display_author_bio'      => false,
        'luma_core_post_archive_display'         => 'excerpt',
        'luma_core_post__archive_format'         => 'list',
        'luma_core_post_width'                   => 'default',
        'luma_core_post_page_width'              => 'default',
        'luma_core_post__archive_format'         => 'list',

        'luma_core_header_nav_full'              => false,
        'luma_core_header_sticky'                => false,
        'luma_core_header_transparent'           => false,
        'luma_core_header_shrink'                => false,
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
