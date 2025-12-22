<?php

namespace Luma\Core\Setup;

use Luma\Core\Core\Config;
use Luma\Core\Customize\ThemeSettingsSchema;

/**
 * Class ThemeSetup
 *
 * Registers all theme supports, editor integration,
 * navigation menus, widget areas, and plugin compatibility.
 *
 * Hooked via __invoke() during theme bootstrap.
 *
 * @package Luma-Core
 * @since 1.0.0
 */
class ThemeSetup
{

    protected string $domain = 'luma-core';

    public function __construct()
    {
        $this->domain = $this->domain ?? $this->domain;
    }
    /**
     * Register all setup hooks.
     *
     * @return void
     */
    public function __invoke(): void
    {
        add_action('after_setup_theme', [$this, 'core_theme_support']);
        add_action('after_setup_theme', [$this, 'branding_support']);
        add_action('after_setup_theme', [$this, 'block_support']);
        add_action('after_setup_theme', [$this, 'editor_support']);
        add_action('after_setup_theme', [$this, 'woocommerce_support']);

        add_action('after_setup_theme', [$this, 'register_menus']);
        add_action('widgets_init',      [$this, 'register_sidebars']);
    }

    /**
     * Core WordPress theme supports.
     *
     * @return void
     */
    public function core_theme_support(): void
    {
        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');

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

        add_theme_support('customize-selective-refresh-widgets');
    }

    /**
     * Branding and identity supports.
     *
     * @return void
     */
    public function branding_support(): void
    {
        // height setting flows to logo image srcset generation
        add_theme_support('custom-logo', [
            'height'               => 130,
            'width'                => 300,
            'flex-width'           => true,
            'flex-height'          => true,
            'unlink-homepage-logo' => true,
        ]);


        $header_enabled = ThemeSettingsSchema::get_theme_mod('header_custom_header_enabled') ?? false;
        if ($header_enabled) {
        }
        add_theme_support('custom-header', [
            'width'       => 1600,
            'height'      => 400,
            'flex-width'  => true,
            'flex-height' => true,
            'header-text' => true,
            'video'       => true,
        ]);
    }

    /**
     * Block editor and hybrid theme support.
     *
     * Conservative defaults suitable for ThemeForest.
     *
     * @return void
     */
    public function block_support(): void
    {
        add_theme_support('responsive-embeds');
        add_theme_support('align-wide');
        add_theme_support('wp-block-styles');
        add_theme_support('block-patterns');

        register_block_pattern_category(
            'luma-footers',
            [
                'label' => __('Footer', $this->domain),
            ]
        );
    }

    /**
     * Editor styles integration.
     *
     * @return void
     */
    public function editor_support(): void
    {
        add_theme_support('editor-styles');
        add_editor_style('vendor/luma/core/build/css/editor.css');
    }

    /**
     * WooCommerce compatibility.
     *
     * Safe to include even if WooCommerce is inactive.
     *
     * @return void
     */
    public function woocommerce_support(): void
    {
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
    }

    /**
     * Register navigation menu locations.
     *
     * @return void
     */
    public function register_menus(): void
    {
        register_nav_menus([
            'primary' => esc_html__('Primary Menu', $this->domain),
            'footer'  => esc_html__('Footer Menu', $this->domain),
        ]);
    }

    /**
     * Register widget areas.
     *
     * @return void
     */
    public function register_sidebars(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            register_sidebar([
                'name'          => sprintf(__('Footer %d', $this->domain), $i),
                'id'            => "footer-{$i}",
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            ]);
        }
    }
}
