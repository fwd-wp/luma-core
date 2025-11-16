<?php
/**
 * Show the appropriate content for the Image post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

use Luma\Core\Helpers\TemplateFunctions;

// If there is no featured-image, print the first image block found.
if (
	! TemplateFunctions::can_show_post_thumbnail() &&
	has_block( 'core/image', get_the_content() )
) {

	TemplateFunctions::print_first_instance_of_block( 'core/image', get_the_content() );
}

the_excerpt();
