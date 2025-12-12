<?php

namespace Luma\Core\Setup;

use Luma\Core\Core\Config;

/**
 * Class ThemeSetup
 *
 * Bootstraps theme functionality and integration with WordPress core.
 * Registers theme supports, navigation menus, widget areas, and editor styles.
 *
 * This class is invoked as a callable and attaches all setup-related
 * actions to the appropriate WordPress hooks.
 */
class ThemeSetup
{
    /**
     * Magic invoke method.
     *
     * Attaches all theme initialization routines to WordPress hooks:
     * - after_setup_theme → theme_support()
     * - after_setup_theme → core_editor_styles()
     * - after_setup_theme → woo_commerce()
     * - after_setup_theme → register_nav_menus()
     * - widgets_init      → register_sidebars()
     *
     * @return void
     */
    public function __invoke(): void
    {
        add_action('after_setup_theme', [$this, 'theme_support']);
        add_action('after_setup_theme', [$this, 'core_editor_styles']);
        add_action('after_setup_theme', [$this, 'woo_commerce']);
        add_action('after_setup_theme', [$this, 'register_nav_menus']);
        add_action('widgets_init',      [$this, 'register_sidebars']);
    }

    /**
     * Registers core WordPress theme supports.
     *
     * Enables:
     * - automatic-feed-links
     * - title-tag
     * - post formats
     * - post thumbnails
     * - HTML5 markup support
     * - custom logo
     * - custom header
     * - block styles
     * - responsive embeds
     * - wide alignment support
     * - selective refresh for widgets
     *
     * Also removes the feed icon from the legacy RSS widget.
     *
     * Hook: after_setup_theme
     *
     * @return void
     */
    public function theme_support(): void
    {
        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');

        add_theme_support('post-formats', [
            'link',
            'aside',
            'gallery',
            'image',
            'quote',
            'status',
            'video',
            'audio',
            'chat',
        ]);

        add_theme_support('post-thumbnails');

        add_theme_support('html5', [
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'search-form',
            'style',
            'script',
            'navigation-widgets',
        ]);

        add_theme_support('custom-logo', [
            'height'               => 130,
            'width'                => 300,
            'flex-width'           => true,
            'flex-height'          => true,
            'unlink-homepage-logo' => true,
        ]);

        add_theme_support('custom-header', [
            'width'       => 1600,
            'height'      => 400,
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => true,
            'video'       => true,
        ]);

        add_theme_support('wp-block-styles');
        add_theme_support('responsive-embeds');
        add_theme_support('align-wide');
        add_theme_support('customize-selective-refresh-widgets');

        add_filter('rss_widget_feed_link', '__return_empty_string');
    }

    /**
     * Registers editor styles for the block editor.
     *
     * Enables editor-styles support and loads the compiled editor stylesheet.
     *
     * Hook: after_setup_theme
     *
     * @return void
     */
    public function core_editor_styles(): void
    {
        add_theme_support('editor-styles');
        add_editor_style('vendor/luma/core/build/css/editor.css');
    }

    /**
     * Enables WooCommerce theme support features.
     *
     * Adds support for:
     * - WooCommerce core features
     * - product gallery zoom
     * - product gallery lightbox
     * - product gallery slider
     *
     * Hook: after_setup_theme
     *
     * @return void
     */
    public function woo_commerce(): void
    {
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
    }

    /**
     * Registers navigation menu locations.
     *
     * Provides:
     * - "main"   → primary site navigation
     * - "footer" → footer navigation menu
     *
     * Hook: after_setup_theme
     *
     * @return void
     */
    public function register_nav_menus(): void
    {
        register_nav_menus([
            'main'   => esc_html__('Main menu', Config::get_domain()),
            'footer' => esc_html__('Footer menu', Config::get_domain()),
        ]);
    }

    /**
     * Registers theme widget areas (sidebars).
     *
     * Registers four footer widget regions:
     * - Footer 1
     * - Footer 2
     * - Footer 3
     * - Footer 4
     *
     * Hook: widgets_init
     *
     * @return void
     */
    public function register_sidebars(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            register_sidebar([
                'name'          => sprintf(__('Footer %d', Config::get_domain()), $i),
                'id'            => 'footer-' . $i,
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            ]);
        }
    }
}
