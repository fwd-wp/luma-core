<?php

namespace Luma\Core\Setup;

use Luma\Core\Services\I18nService;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Handles backward compatibility for the Luma-Core theme.
 *
 * Prevents the theme from running on WordPress and PHP versions lower
 * than the required ones, and displays upgrade messages in the admin
 * and preview areas.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
final class BackCompat
{
    /**
     * Minimum required WordPress version.
     *
     * @var float
     */
    private float $required_wp_version;

    /**
     * Minimum required PHP version.
     *
     * @var float
     */
    private float $required_php_version;

    /**
     * Constructor.
     *
     * @param string $wp_ver  Minimum WordPress version required.
     * @param string $php_ver Minimum PHP version required.
     */
    public function __construct(string $wp_ver, string $php_ver)
    {
        $this->required_wp_version  = $wp_ver;
        $this->required_php_version = $php_ver;
    }

    /**
     * Attach hooks if requirements are not met.
     *
     * @return void
     */
    public function __invoke(): void
    {
        if (
            version_compare($GLOBALS['wp_version'], $this->required_wp_version, '<') ||
            version_compare(PHP_VERSION, $this->required_php_version, '<')
        ) {
            add_action('after_switch_theme', [$this, 'switch_theme']);
            add_action('load-customize.php', [$this, 'customize']);
            add_action('template_redirect', [$this, 'preview']);
        }
    }

    /**
     * Get the upgrade message.
     *
     * @return string Upgrade message for WordPress/PHP version requirements.
     */
    private function get_upgrade_message(): string
    {
        $messages = [];

        if (version_compare($GLOBALS['wp_version'], $this->required_wp_version, '<')) {
            $messages[] = sprintf(
                __('This theme requires WordPress %s or newer. You are running version %s.', I18nService::getDomain()),
                $this->required_wp_version,
                $GLOBALS['wp_version']
            );
        }

        if (version_compare(PHP_VERSION, $this->required_php_version, '<')) {
            $messages[] = sprintf(
                __('This theme requires PHP %s or newer. You are running version %s.', I18nService::getDomain()),
                $this->required_php_version,
                PHP_VERSION
            );
        }
               
        return implode(' ', $messages . ' ' . __(' Please upgrade.', I18nService::getDomain()));
         
    }

    /**
     * Display upgrade notice in admin.
     *
     * @return void
     */
    public function upgrade_notice(): void
    {
        echo '<div class="error"><p>';
        echo esc_html($this->get_upgrade_message());
        echo '</p></div>';
    }

    /**
     * Display wp_die() message with back link.
     *
     * @return void
     */
    private function die_with_upgrade_message(): void
    {
        wp_die(
            esc_html($this->get_upgrade_message()),
            esc_html__('Theme Incompatible', I18nService::getDomain()),
            [
                'back_link' => true,
            ]
        );
    }

    /**
     * Hooked to after_switch_theme.
     *
     * @return void
     */
    public function switch_theme(): void
    {
        add_action('admin_notices', [$this, 'upgrade_notice']);
    }

    /**
     * Hooked to load-customize.php.
     *
     * @return void
     */
    public function customize(): void
    {
        $this->die_with_upgrade_message();
    }

    /**
     * Hooked to template_redirect for preview pages.
     *
     * @return void
     */
    public function preview(): void
    {
        if (is_preview()) {
            $this->die_with_upgrade_message();
        }
    }
}
