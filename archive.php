<?php

/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateTags;

get_header();

if (have_posts()) :
	get_template_part('src/views/header/page-header');
	get_template_part('src/views/content/content-archive');
	TemplateTags::the_posts_navigation();
else :
	get_template_part('src/views/content/content-none');
endif;

get_footer();
