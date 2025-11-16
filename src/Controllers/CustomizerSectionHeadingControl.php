<?php

namespace Twenty\One\Controllers;
use WP_Customize_Control;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Custom category control for the WordPress Customizer.
 *
 * Adds a simple heading label for grouping sections or controls in the Customizer.
 *
 * @package Luma-Core
 * @since Twenty Luma-Core 1.0
 */
final class CustomizerSectionHeadingControl extends WP_Customize_Control
{
    /**
     * Control type.
     *
     * This identifies the control type as "customize_category" so the Customizer
     * can handle it appropriately.
     *
     * @since Twenty Luma-Core 1.0
     * @var string
     */
    public $type = 'customize_category';

    /**
     * Render the control content in the Customizer.
     *
     * Outputs the label wrapped in an `<h4>` tag. Escapes the label for safe output.
     *
     * @since Twenty Luma-Core 1.0
     * @return void
     */
    public function render_content()
    {
?>
        <h4 class="customize_control_title customize_control_category">
            <?php echo esc_html($this->label); ?>
        </h4>
<?php
    }
}
