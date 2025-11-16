<?php

namespace Luma\Core\Controllers;

use \WP_Customize_Control;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Custom category control for the WordPress Customizer.
 *
 * Adds a simple heading label for grouping sections or controls in the Customizer.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
final class CustomizerButtonControl extends WP_Customize_Control
{
    /**
     * Control type.
     *
     * This identifies the control type as "customize_category" so the Customizer
     * can handle it appropriately.
     *
     * @since Luma-Core 1.0
     * @var string
     */
    public $type = 'font_reset_button';

    /**
     * Render the control content in the Customizer.
     *
     * Outputs the label wrapped in an `<h4>` tag. Escapes the label for safe output.
     *
     * @since Luma-Core 1.0
     * @return void
     */
    public function render_content()
    {
?>
        <button
            type="button"
            class="button button-secondary font-reset-button"
            id="<?php echo esc_attr($this->id); ?>"
            data-category="<?php echo esc_attr($this->id); ?>"
            aria-label="<?php echo esc_attr($this->label); ?>">
            <?php echo esc_html($this->label); ?>
        </button>
<?php
    }
}
