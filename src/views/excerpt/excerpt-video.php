<?php
/**
 * Show the appropriate content for the Video post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

use Luma\Core\Helpers\TemplateFunctions;

$content = get_the_content();

if ( has_block( 'core/video', $content ) ) {
	TemplateFunctions::print_first_instance_of_block( 'core/video', $content );
} elseif ( has_block( 'core/embed', $content ) ) {
	TemplateFunctions::print_first_instance_of_block( 'core/embed', $content );
} else {
	TemplateFunctions::print_first_instance_of_block( 'core-embed/*', $content );
}

// Add the excerpt.
the_excerpt();
