<?php

namespace Luma\Core\Helpers;

use Luma\Core\Services\ThemeSettingsSchema;


/**
 * Custom html attribute generation.
 *
 * All functions output escaped (safe) HTML.
 * 
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
class HtmlAttributes
{
    /**
     * Generates class attribute for the main <html> element.
     *
     * Accepts additional classes as a string or array, merges them with defaults.
     *
     * @since Luma-Core 1.0
     *
     * @param string|array $css_class Optional additional classes to append.
     * @param bool $echo Whether to echo the attribute or return as string. Default true.
     * @return string|null
     */
    public static function html_class(bool $echo = true, string|array $css_class = ''): ?string
    {
        // Start with extra classes
        if (is_string($css_class)) {
            // create array from string split where spaces are
            $css_class = preg_split('/\s+/', $css_class);
        }

        // remove empty string array items
        $css_class = array_filter($css_class);

        // Apply filter
        $classes = apply_filters('luma_core_html_class', $css_class);

        // Build class attribute string
        $attr = empty($classes) ? '' : 'class="' . esc_attr(implode(' ', $classes)) . '"';

        if ($echo) {
            echo $attr;
            return null;
        }

        return $attr;
    }

    /**
     * Calculates classes for the site header container.
     *
     * @since Luma-Core 1.0
     *
     * @param string|array $css_class Optional additional classes.
     * @param bool $echo Whether to echo the attribute or return as string.
     * @return string|null The class attribute string or null if echoed.
     */
    public static function header_class(bool $echo = true, string|array $css_class = ''): ?string
    {
        // Normalize extra classes
        if (is_string($css_class)) {
            $css_class = preg_split('/\s+/', $css_class);
        }
        $css_class = array_filter($css_class);

        // Default classes
        $default = ['site-header'];
        if (ThemeSettingsSchema::get_theme_mod('header_navbar_full_width')) {
            $default[] = 'is-full';
        }
        if (ThemeSettingsSchema::get_theme_mod('header_sticky')) {
            $default[] = 'is-sticky';
        }
        if (ThemeSettingsSchema::get_theme_mod('header_navbar_transparent')) {
            $default[] = 'is-transparent';
        }
        if (ThemeSettingsSchema::get_theme_mod('wp-core_display_title_and_tagline')) {
            if (get_bloginfo('name')) {
                $default[] = 'has-title';
            }
            if (get_bloginfo('description')) {
                $default[] = 'has-description';
            }
        }
        if (has_custom_logo()) {
            $default[] = 'has-logo';
        }
        if (has_nav_menu('main')) {
            $default[] = 'has-menu';
        }

        // Merge default and extra
        $classes = array_merge($default, $css_class);

        /**
         * Filter the CSS classes applied to the site header container.
         *
         * @since Luma-Core 1.0
         *
         * @param array $classes Array of CSS classes.
         * @param bool  $echo Whether the final output will be echoed.
         */
        $classes = apply_filters('luma_core_header_class', $classes, $echo);

        // Build class attribute string
        $attr = empty($classes) ? '' : 'class="' . esc_attr(implode(' ', $classes)) . '"';

        if ($echo) {
            echo $attr;
            return null;
        }

        return $attr;
    }

    /**
     * Calculates classes for the main site navigation container.
     *
     * @since Luma-Core 1.0
     *
     * @param string|array $css_class Optional additional classes.
     * @param bool $echo Whether to echo the attribute or return as string.
     * @return string|null The class attribute string or null if echoed.
     */
    public static function nav_class(bool $echo = true, string|array $css_class = ''): ?string
    {
        // Normalize extra classes
        if (is_string($css_class)) {
            $css_class = preg_split('/\s+/', $css_class);
        }
        $css_class = array_filter($css_class);

        // Default classes
        $default = ['site-navigation'];

        if (ThemeSettingsSchema::get_theme_mod('header_navbar_shrink')) {
            $default[] = 'is-sticky';
            $default[] = 'is-shrink-enabled';
        }

        // Merge default and extra
        $classes = array_merge($default, $css_class);

        /**
         * Filter the CSS classes applied to the site navigation container.
         *
         * @since Luma-Core 1.0
         *
         * @param array $classes Array of CSS classes.
         * @param bool  $echo Whether the final output will be echoed.
         */
        $classes = apply_filters('luma_core_nav_class', $classes, $echo);

        // Build class attribute string
        $attr = empty($classes) ? '' : 'class="' . esc_attr(implode(' ', $classes)) . '"';

        if ($echo) {
            echo $attr;
            return null;
        }

        return $attr;
    }
}
