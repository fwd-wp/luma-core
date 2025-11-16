<?php
/**
 * Show the appropriate content for the Audio post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateFunctions;

$content = get_the_content();

if ( has_block( 'core/audio', $content ) ) {
	TemplateFunctions::print_first_instance_of_block( 'core/audio', $content );
} elseif ( has_block( 'core/embed', $content ) ) {
	TemplateFunctions::print_first_instance_of_block( 'core/embed', $content );
} else {
	TemplateFunctions::print_first_instance_of_block( 'core-embed/*', $content );
}

// Add the excerpt.
the_excerpt();
