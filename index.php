<?php

/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
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
