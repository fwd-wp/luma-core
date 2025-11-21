<?php

namespace Luma\Core\Setup;

use Luma\Core\Services\I18nService;

/**s
 * Class Setup
 *
 * Handles the initialization of theme features and widget areas.
 * Hooks into WordPress actions to set up theme defaults and register sidebars.
 *
 * @package Twenty-One\Setup
 * @since Twenty Twenty-One 1.0
 */
class ThemeSetup
{
    /**
     * Invoke method to initialize theme setup.
     *
     * Registers hooks for:
     * - after_setup_theme → theme_support()
     * - widgets_init → widgets_init()
     *
     * @since Twenty Twenty-One 1.0
     *
     * @return void
     */
    public function __invoke(): void
    {
        add_action('after_setup_theme', [$this, 'theme_support']);
        add_action('after_setup_theme', [$this, 'woo_commerce']);
        add_action('after_setup_theme', [$this, 'register_nav_menus']);
        add_action('widgets_init',      [$this, 'register_sidebars']);
    }


    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Hooked into after_setup_theme, which runs before init.
     * This ensures features like thumbnails and title-tag are properly registered.
     *
     * @since Twenty Twenty-One 1.0
     *
     * @return void
     */
    public function theme_support(): void
    {
        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * This theme does not use a hard-coded <title> tag in the document head,
         * WordPress will provide it for us.
         */
        add_theme_support('title-tag');

        /**
         * Add post-formats support.
         *
         * Supports all formats as of WP 6.8 (unchanged since 3.6).
         * Post formats are mainly used for styling post excerpts.
         */
        add_theme_support(
            'post-formats',
            array(
                'link',
                'aside',
                'gallery',
                'image',
                'quote',
                'status',
                'video',
                'audio',
                'chat',
            )
        );

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');
        // set_post_thumbnail_size(1568, 9999);

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         *
         * Added: search-form
         * TODO: Check CSS styles are still ok.
         */
        add_theme_support(
            'html5',
            array(
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'search-form',
                'style',
                'script',
                'navigation-widgets',
            )
        );

        /*
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        $logo_width  = 300;
        $logo_height = 130;

        add_theme_support(
            'custom-logo',
            array(
                'height'               => $logo_height,
                'width'                => $logo_width,
                'flex-width'           => true,
                'flex-height'          => true,
                'unlink-homepage-logo' => true,
            )
        );

        // Add custom header support.
        add_theme_support(
            'custom-header',
            array(
                'width'       => 1600,
                'height'      => 400,
                'flex-height' => true,
                'flex-width'  => true,
                'header-text' => true,
                // TODO: Set up default header image so it works with imported content.
                // 'default-image' => get_template_directory_uri() . '/images/sunset.jpg',
            )
        );

        // Add support for editor styles.
        add_theme_support('editor-styles');
        add_editor_style('./build/css/editor.css');

        // Add support for block styles.
        add_theme_support('wp-block-styles');

        // responsive embed functionality
        add_theme_support('responsive-embeds');

        // also in theme.json, but here for theme review
        add_theme_support('align-wide');

        // Add theme support for selective refresh for widgets.
        add_theme_support('customize-selective-refresh-widgets');

        // Remove feed icon link from legacy RSS widget (WP 6.5+).
        add_filter('rss_widget_feed_link', '__return_empty_string');
    }

    public function woo_commerce()
    {
        // WooCommerce support.
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
    }

    public function register_nav_menus()
    {
        /**
         * Register nav menu locations.
         */
        register_nav_menus(
            array(
                'main'   => esc_html__('Main menu', I18nService::get_domain()),
                'footer' => esc_html__('Footer menu', I18nService::get_domain()),
            )
        );
    }

    /**
     * Registers widget areas (sidebars).
     *
     * Four footer widget areas are registered by default.
     *
     * @since Twenty Twenty-One 1.0
     *
     * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
     *
     * @return void
     */
    public function register_sidebars(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            register_sidebar(array(
                'name'          => sprintf(__('Footer %d', I18nService::get_domain()), $i),
                'id'            => 'footer-' . $i,
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            ));
        }
    }
}
