<?php

namespace Luma\Core\Helpers;

use Luma\Core\Core\Config;

/**
 * Base class for template tag helpers.
 * 
 * need to run ::init() in functions.php after Config is initialized
 * so that domain is set correctly for translations
 * 
 * @package Luma-Core
 * @since Luma-Core 1.0
 */

class TemplateTagsBase
{
    protected static string $domain = 'luma-core';

    public static function init(): void
    {
        // uses theme variant prefix for translations
        self::$domain = Config::get_domain() ?? self::$domain;
    }



    /**
     * Internal helper Wraps an array of label parts in a <span> with a given CSS class.
     *
     * Empty parts are automatically removed, and the remaining parts
     * are joined with spaces. The CSS class is escaped for safe output.
     *
     * @param string $class CSS class for the <span> wrapper.
     * @param string[] $parts Array of strings to include inside the span.
     *
     * @return string The HTML <span> element containing the label parts.
     *
     * @since Luma-Core 1.0
     */
    protected static function wrap_content(string $class, array $parts, string $tag = 'span'): string
    {
        $label = implode(' ', array_filter($parts));
        if ($label === '') {
            return '';
        }
        $tag = esc_attr($tag);
        $class = esc_attr($class);
        return "<{$tag} class='{$class}'>{$label}</{$tag}>";
    }


    /**
     * Helper to build post time output (absolute or relative) with fallbacks.
     *
     * @param string $type       'published' or 'modified'.
     * @param bool   $relative   Whether to display "X ago" format. Default false.
     * @param array  $args       Arguments controlling output (class, before, after, time_class, max_days).
     * @param callable|null $fallback Optional fallback when max_days exceeded.
     *
     * @return string|null HTML output or null on failure.
     */
    protected static function build_time_output(
        string $type,
        bool $relative = false,
        array $args = [],
        ?callable $fallback = null
    ): ?string {

        // Determine timestamps
        switch ($type) {
            case 'published':
                $timestamp = (int) get_the_time('U');
                if ($timestamp <= 0) return null;
                break;

            case 'modified':
                $timestamp  = (int) get_the_modified_time('U');
                $published  = (int) get_the_time('U');
                if ($timestamp <= 0 || $timestamp <= $published) return null;
                break;

            default:
                return null;
        }

        // Default arguments
        $defaults = [
            'class'      => ($type === 'published') ? 'posted-on' : 'updated-on',
            'time_class' => ($type === 'published') ? 'entry-date published' : 'updated',
            'before'     => ($type === 'published') ? __('Published', self::$domain) : __('Updated', self::$domain),
            'after'      => ($relative ? __('ago', self::$domain) : ''),
            'max_days'   => 364,
        ];
        $args = wp_parse_args($args, $defaults);

        $now = (int) current_time('timestamp');
        if ($now <= 0) {
            $now = (int) time();
            if ($now <= 0) {
                // Fail safely if both timestamps are invalid
                return null;
            }
        }


        // Relative output
        if ($relative) {
            $days_old = floor(($now - $timestamp) / DAY_IN_SECONDS);
            if (!empty($args['max_days']) && $days_old > (int) $args['max_days']) {
                return $fallback ? $fallback() : null;
            }

            $display = human_time_diff($timestamp, $now);
            $parts   = [$args['before'], esc_html($display), $args['after']];
            $html    = self::wrap_content($args['class'], $parts);

            // Absolute output (<time>)
        } else {
            $datetime = ($type === 'published')
                ? get_the_date(DATE_W3C)
                : get_the_modified_date(DATE_W3C);

            if (!$datetime) return null;

            $display_value = ($type === 'published')
                ? get_the_date()
                : get_the_modified_time('U');

            $time_string = sprintf(
                '<time class="%s" datetime="%s">%s</time>',
                esc_attr($args['time_class']),
                esc_attr($datetime),
                esc_html($display_value)
            );

            $parts = [$args['before'], $time_string, $args['after']];
            $html  = self::wrap_content($args['class'], $parts);
        }

        $filter_tag = $type . ($relative ? '_ago' : '_on'); // e.g., 'modified_ago'
        $html = apply_filters($filter_tag, $html, $args, $timestamp);

        return wp_kses_post($html);
    }

    /**
     * Generate a list of terms (categories or tags) for the current post.
     *
     * @param bool   $echo   Whether to echo the output. Default true.
     * @param string $type   'category' or 'tag'. Default 'category'.
     * @param array  $args   Optional arguments:
     *                       - 'before'    Text/HTML before the list. Default 'Categories:' / 'Tags:'.
     *                       - 'after'     Text/HTML after the list. Default ''.
     *                       - 'separator' Separator between terms. Default theme list item separator.
     *                       - 'class'     CSS class for the wrapper <span>. Default 'cat-links' / 'tags-links'.
     *
     * @return string|null The final HTML if `$echo` is false, otherwise null.
     */
    protected static function term_list(bool $echo = true, string $type = 'category', array $args = []): ?string
    {
        $type = $type === 'tag' ? 'tag' : 'category';

        $has_terms = $type === 'tag' ? has_tag() : has_category();
        if (! $has_terms) {
            return null;
        }

        $defaults = [
            'before'    => $type === 'tag' ? 'Tags:' : 'Categories:',
            'after'     => '',
            'separator' => wp_get_list_item_separator() ?? ', ',
            'class'     => $type === 'tag' ? 'tags-links' : 'cat-links',
        ];

        $args = wp_parse_args($args, $defaults);

        // Allow filters to modify args BEFORE building HTML
        $args = apply_filters("luma_core_{$type}_list_args", $args, $echo);

        // Generate the term links
        $term_list = $type === 'tag'
            ? get_the_tag_list('', $args['separator'])
            : get_the_category_list($args['separator']);

        if (! $term_list) {
            return null;
        }

        $parts = [
            $args['before'],
            $term_list,
            $args['after'],
        ];

        $html = self::wrap_content($args['class'], $parts);

        // Allow filters to modify the full HTML output
        $html = apply_filters("luma_core_{$type}_list", $html, $term_list, $args, $echo);

        // Sanitize final output
        $html = wp_kses_post($html);

        if ($echo) {
            echo $html;
            return null;
        }

        return $html;
    }
}
