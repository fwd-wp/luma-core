<?php

namespace Luma\Core\Setup;

use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Models\ThemeModModel;

/**
 * Handles the enqueueing of theme scripts and styles.
 *
 * Hooks into WordPress actions to enqueue frontend scripts, styles, block editor assets,
 * and non-latin language styles.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
class Enqueue
{
    /**
     * Invoke method to hook enqueue actions.
     *
     * Hooks all necessary scripts and styles to WordPress actions.
     *
     * @since Luma-Core 1.0
     *
     * @return void
     */
    public function __invoke()
    {
        add_action('wp_enqueue_scripts', [$this, 'scripts']);
        // delay to override plugin styles
        add_action('wp_enqueue_scripts', [$this, 'styles'], 20);
        add_action('enqueue_block_editor_assets', [$this, 'block_editor_script']);
        add_action('wp_enqueue_scripts', [$this, 'non_latin_languages']);
    }

    /**
     * Enqueues theme scripts for the frontend.
     *
     * Includes comment reply, main navigation scripts, header shrink scripts,
     * and archive masonry scripts based on Customizer settings.
     *
     * @since Luma-Core 1.0
     *
     * @global bool       $is_IE
     * @global WP_Scripts $wp_scripts
     *
     * @return void
     */
    public function scripts(): void
    {
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }

        if (has_nav_menu('main')) {
            wp_enqueue_script(
                'luma-core-menu-main-script',
                get_template_directory_uri() . '/assets/js/menu-main.js',
                array(),
                wp_get_theme()->get('Version'),
                array(
                    'in_footer' => true,
                    'strategy'  => 'defer',
                )
            );
        }

        if (ThemeMod::get('luma_core_post__archive_format') === 'masonry') {
            wp_enqueue_script(
                'luma-core-archive-masonry',
                get_template_directory_uri() . '/assets/js/archive-masonry.js',
                ['masonry'],
                null,
                true
            );
        }
    }

    /**
     * Enqueues theme styles for the frontend.
     *
     * Includes font faces, main CSS, RTL styles, and print styles.
     *
     * @since Luma-Core 1.0
     *
     * @return void
     */
    public function styles(): void
    {
        wp_enqueue_style(
            'luma-core-fonts',
            get_template_directory_uri() . '/assets/fonts/font-face.css',
            array(),
            wp_get_theme()->get('Version')
        );

        wp_enqueue_style(
            'luma-core-style',
            get_template_directory_uri() . '/build/css/main.css',
            array(),
            wp_get_theme()->get('Version')
        );

        wp_style_add_data('luma-core-style', 'rtl', 'replace');

        wp_enqueue_style(
            'luma-core-print-style',
            get_template_directory_uri() . '/build/css/print.css',
            array(),
            wp_get_theme()->get('Version'),
            'print'
        );
    }

    /**
     * Enqueues scripts for the block editor (Gutenberg).
     *
     * Adds editor-specific JavaScript.
     *
     * @since Luma-Core 1.0
     *
     * @return void
     */
    public function block_editor_script(): void
    {
        wp_enqueue_script(
            'luma-core-editor',
            get_theme_file_uri('/assets/js/editor.js'),
            array('wp-blocks', 'wp-dom'),
            wp_get_theme()->get('Version'),
            array('in_footer' => true)
        );
    }

    /**
     * Adds non-latin language CSS inline for the front-end.
     *
     * Pulls CSS from TemplateFunctions::get_non_latin_css and appends it
     * to the main theme style.
     *
     * @since Luma-Core 1.0
     *
     * @return void
     */
    public function non_latin_languages(): void
    {
        $custom_css = TemplateFunctions::get_non_latin_css('front-end');

        if ($custom_css) {
            wp_add_inline_style('luma-core-style', $custom_css);
        }
    }
}
