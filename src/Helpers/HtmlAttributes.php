<?php

namespace Luma\Core\Helpers;

use Luma\Core\Customize\ThemeSettingsSchema;


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

        // Apply filter
        $classes = apply_filters('luma_core_html_class', $css_class, $echo);

        // remove empty string array items
        $css_class = array_filter($css_class);

        // Step 4: Build attribute
        $attr = empty($classes)
            ? ''
            : 'class="' . esc_attr(implode(' ', $classes)) . '"';

        if ($echo) {
            echo $attr;
            return null;
        }

        return $attr;
    }

    /**
     * Calculates classes for the site header container container.
     *
     * @since Luma-Core 1.0
     *
     * @param bool         $echo Whether to echo the attribute or return it.
     * @param string|array $extra_classes Optional additional classes.
     * @return string|null
     */
    public static function header_container_class(bool $echo = true, string|array $extra_classes = ''): ?string
    {
        // Step 1: Derive header "state"
        // This is semantic intent, not CSS.
        $state = [
            'is_sticky'       => ThemeSettingsSchema::get_theme_mod('header_navbar_sticky'),
            'is_transparent'  => ThemeSettingsSchema::get_theme_mod('header_navbar_transparent'),
        ];

        /**
         * Allow themes or plugins to modify header state before
         * class mapping occurs.
         *
         * @since Luma-Core 1.0
         */
        $state = apply_filters('luma_core_header_container_state', $state);

        // Step 2: Map state → default CSS classes
        $classes = ['site-header-container'];

        if ($state['is_sticky']) {
            $classes[] = 'is-sticky';
        }
        if ($state['is_transparent']) {
            $classes[] = 'is-transparent';
        }

        // Step 3: Merge extra classes + final filter
        if (is_string($extra_classes)) {
            $extra_classes = preg_split('/\s+/', $extra_classes);
        }

        $extra_classes = array_filter((array) $extra_classes);

        $classes = array_merge($classes, $extra_classes);

        /**
         * Final escape hatch to alter CSS classes directly.
         *
         * @since Luma-Core 1.0
         */
        $classes = apply_filters('luma_core_header_container_classes', $classes, $state);

        // Step 4: Build attribute
        $attr = empty($classes)
            ? ''
            : 'class="' . esc_attr(implode(' ', $classes)) . '"';

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
     * @param bool         $echo Whether to echo the attribute or return it.
     * @param string|array $extra_classes Optional additional classes.
     * @return string|null
     */
    public static function header_class(bool $echo = true, string|array $extra_classes = ''): ?string
    {
        // Step 1: Derive header "state"
        // This is semantic intent, not CSS.
        $state = [
            'has_logo'        => has_custom_logo(),
            'has_menu'        => has_nav_menu('main'),
            'has_title'       => (
                ThemeSettingsSchema::get_theme_mod('navbar_display_title')
                && get_bloginfo('name') !== ''
            ),
            'has_description' => (
                ThemeSettingsSchema::get_theme_mod('navbar_display_title')
                && get_bloginfo('description') !== ''
            ),
            'is_full'         => ThemeSettingsSchema::get_theme_mod('header_navbar_full_width'),
            'is_shrink'       => ThemeSettingsSchema::get_theme_mod('header_navbar_shrink'),
        ];

        /**
         * Allow themes or plugins to modify header state before
         * class mapping occurs.
         *
         * @since Luma-Core 1.0
         */
        $state = apply_filters('luma_core_header_state', $state);

        // Step 2: Map state → default CSS classes
        $classes = ['site-header'];

        if ($state['has_logo']) {
            $classes[] = 'has-logo';
        }
        if ($state['has_menu']) {
            $classes[] = 'has-menu';
        }
        if ($state['has_title']) {
            $classes[] = 'has-title';
        }
        if ($state['has_description']) {
            $classes[] = 'has-description';
        }
        if ($state['is_full']) {
            $classes[] = 'is-full';
        }
        if ($state['is_shrink']) {
            $classes[] = 'is-shrink-enabled';
        }

        // Step 3: Merge extra classes + final filter
        if (is_string($extra_classes)) {
            $extra_classes = preg_split('/\s+/', $extra_classes);
        }

        $extra_classes = array_filter((array) $extra_classes);

        $classes = array_merge($classes, $extra_classes);

        /**
         * Final escape hatch to alter CSS classes directly.
         *
         * @since Luma-Core 1.0
         */
        $classes = apply_filters('luma_core_header_classes', $classes, $state);

        // Step 4: Build attribute
        $attr = empty($classes)
            ? ''
            : 'class="' . esc_attr(implode(' ', $classes)) . '"';

        if ($echo) {
            echo $attr;
            return null;
        }

        return $attr;
    }
}
