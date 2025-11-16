<?php

/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateTags;

get_header();

if (have_posts()):
?>
	<header class="page-header alignwide">
		<h1 class="page-title">
			<?php
			printf(
				/* translators: %s: Search term. */
				esc_html__('Results for "%s"', 'twentyone'),
				'<span class="page-description search-term">' . esc_html(get_search_query()) . '</span>'
			);
			?>
		</h1>
	</header><!-- .page-header -->

	<div class="search-result-count">
		<?php
		printf(
			esc_html(
				/* translators: %d: The number of search results. */
				_n(
					'We found %d result for your search.',
					'We found %d results for your search.',
					(int) $wp_query->found_posts,
					'twentyone'
				)
			),
			(int) $wp_query->found_posts
		);
		?>
	</div><!-- .search-result-count -->
<?php

	while (have_posts()): the_post();
		get_template_part('src/views/content/content-archive');
	endwhile;

	TemplateTags::the_posts_navigation();
else:
	get_template_part('src/views/content/content-none');
endif;

get_footer();
