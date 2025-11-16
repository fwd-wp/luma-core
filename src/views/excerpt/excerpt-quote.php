<?php
/**
 * Show the appropriate content for the Quote post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateFunctions;

$content = get_the_content();

// If there is no quote or pullquote print the content.
if ( has_block( 'core/quote', $content ) ) {
	TemplateFunctions::print_first_instance_of_block( 'core/quote', $content );
} elseif ( has_block( 'core/pullquote', $content ) ) {
	TemplateFunctions::print_first_instance_of_block( 'core/pullquote', $content );
} else {
	the_excerpt();
}
