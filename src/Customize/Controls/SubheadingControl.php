<?php

namespace Luma\Core\Customize\Controls;

use WP_Customize_Manager;

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
final class SubheadingControl extends BaseControl
{

    public function __construct(
        WP_Customize_Manager $manager,
        string $id,
        array $args = []
    ) {
        parent::__construct($manager, $id, $args);

        $this->type = $this->prefixed_type('subheading');
    }

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
        <h4
            class="customize-control customize-control-<?php echo esc_attr($this->type); ?>"
            data-type="<?php echo esc_attr($this->type); ?>"
            id="<?php echo esc_attr($this->id); ?>">
            <?php echo esc_html($this->label); ?>
        </h4>
<?php
    }
}
