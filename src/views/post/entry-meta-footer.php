<?php

/**
 * Prints HTML with meta information for the categories, tags and comments.
 * Footer entry meta is displayed differently in archives and single posts.
 * excerpt_micro (aside or status) - show read more text
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Helpers\TemplateTags;

// Early exit if not a post.
if ('post' !== get_post_type()) {
    return;
}

if (! is_single()) {

    // Sticky post label.
    if (is_sticky()) {
        echo '<p>' . esc_html_x(
            'Featured post',
            'Label for sticky posts',
            'luma-core'
        ) . '</p>';
    }

    // Micro excerpt posts – show "continue reading".
    if (TemplateFunctions::is_excerpt_micro_post()) {
        printf(
            '<p><a href="%s">%s</a></p>',
            esc_url(get_permalink()),
            TemplateFunctions::continue_reading_text(false)
        );
    }
}

// Output meta + taxonomies.
TemplateTags::single_posted_meta();
TemplateTags::post_taxonomies();
