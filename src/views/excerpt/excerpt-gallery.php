<?php
/**
 * Show the appropriate content for the Gallery post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateFunctions;

// Print the 1st gallery found.
if ( has_block( 'core/gallery', get_the_content() ) ) {

	TemplateFunctions::print_first_instance_of_block( 'core/gallery', get_the_content() );
}

the_excerpt();
