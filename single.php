<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateFunctions;

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post();

	get_template_part( 'src/views/content/content-single' );

	if ( is_attachment() ) {
		// Parent post navigation.
		the_post_navigation(
			array(
				/* translators: %s: Parent post link. */
				'prev_text' => sprintf( __( '<span class="meta-nav">Published in</span><span class="post-title">%s</span>', 'twentyone' ), '%title' ),
			)
		);
	}

	// If comments are open or there is at least one comment, load up the comment template.
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

	// Previous/next post navigation.
	$twenty_one_next = is_rtl() ? TemplateFunctions::get_icon_svg( 'ui', 'arrow_left' ) : TemplateFunctions::get_icon_svg( 'ui', 'arrow_right' );
	$twenty_one_prev = is_rtl() ? TemplateFunctions::get_icon_svg( 'ui', 'arrow_right' ) : TemplateFunctions::get_icon_svg( 'ui', 'arrow_left' );

	$twenty_one_next_label     = esc_html__( 'Next post', 'twentyone' );
	$twenty_one_previous_label = esc_html__( 'Previous post', 'twentyone' );

	the_post_navigation(
		array(
			'next_text' => '<p class="meta-nav">' . $twenty_one_next_label . $twenty_one_next . '</p><p class="post-title">%title</p>',
			'prev_text' => '<p class="meta-nav">' . $twenty_one_prev . $twenty_one_previous_label . '</p><p class="post-title">%title</p>',
		)
	);
endwhile; // End of the loop.

get_footer();
