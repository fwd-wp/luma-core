<?php

namespace Luma\Core\Setup;

use Luma\Core\Core\Config;
use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Services\ThemeSettingsSchema;

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
    protected string $prefix;
    protected string $prefix_core;

    public function __construct()
    {
        $this->prefix = Config::get_prefix_kebab() ?? $this->prefix;
        $this->prefix_core = Config::get_prefix_kebab_core() ?? $this->prefix_core;
    }


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
        add_action('wp_enqueue_scripts', [$this, 'core_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'core_styles'], 5);
        add_action('enqueue_block_editor_assets', [$this, 'core_block_editor_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'core_non_latin_languages']);
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
    public function core_scripts(): void
    {
        /**
         * Loading priorities for styles
         * 
         * Core - 5
         * Plugin - 7
         * Theme - 10
         * Potential overrides 15
         */


        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }

        if (has_nav_menu('main')) {
            wp_enqueue_script(
                "{$this->prefix_core}-menu-main-script",
                get_template_directory_uri() . '/vendor/luma/core/assets/js/menu-main.js',
                [],
                Config::get_theme_version(),
                array(
                    'in_footer' => true,
                    'strategy'  => 'defer',
                )
            );
        }

        if (ThemeSettingsSchema::get_theme_mod('display_archive_excerpt_format') === 'masonry') {
            wp_enqueue_script(
                "{$this->prefix_core}-archive-masonry",
                get_template_directory_uri() . '/vendor/luma/core/assets/js/archive-masonry.js',
                ['masonry'],
                Config::get_theme_version(),
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
    public function core_styles(): void
    {
        // main core stylesheet
        wp_enqueue_style(
            "{$this->prefix_core}-main",
            get_template_directory_uri() . '/vendor/luma/core/build/css/main.css',
            [],
            wp_get_theme()->get('Version')
        );

        // Tells WP to look for main-rtl.css in same dir.
        wp_style_add_data("{$this->prefix_core}-main", 'rtl', 'replace');

        // loads twentytwentyone print stylesheet
        wp_enqueue_style(
            "{$this->prefix_core}-print",
            get_template_directory_uri() . '/vendor/luma/core/build/css/print.css',
            [],
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
    public function core_block_editor_scripts(): void
    {
        wp_enqueue_script(
            "{$this->prefix_core}-editor",
            get_theme_file_uri('/vendor/luma/core/assets/js/editor.js'),
            array('wp-blocks', 'wp-dom'),
            Config::get_theme_version(),
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
    public function core_non_latin_languages(): void
    {
        $custom_css = TemplateFunctions::get_non_latin_css('front-end');

        if ($custom_css) {
            wp_add_inline_style("{$this->prefix_core}-non-latin-css", $custom_css);
        }
    }
}
