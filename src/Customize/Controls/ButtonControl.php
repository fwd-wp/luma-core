<?php

namespace Luma\Core\Customize\Controls;

use WP_Customize_Manager;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Custom category control for the WordPress Customizer.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
final class ButtonControl extends BaseControl
{
    public function __construct(
        WP_Customize_Manager $manager,
        string $id,
        array $args = []
    ) {
        parent::__construct($manager, $id, $args);

        $this->type = $this->prefixed_type('font-reset-button');
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
        <button
            type="button"
            class="button button-secondary customize-control customize-control-<?php echo esc_attr($this->type); ?>"
            id="<?php echo esc_attr($this->id); ?>"
            data-type="<?php echo esc_attr($this->type); ?>"
            aria-label="<?php echo esc_attr($this->label); ?>">
            <?php echo esc_html($this->label); ?>
        </button>
<?php
    }
}
