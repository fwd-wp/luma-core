<?php

/**
 * The template for displaying image attachments
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateTags;

get_header();

// Start the loop.
while (have_posts()) {
	the_post();
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php get_template_part('src/views/header/entry-header'); ?>

		<div class="entry-content">
			<figure class="wp-block-image">
				<?php
				/**
				 * Filter the default image attachment size.
				 *
				 * @since Twenty Luma-Core 1.0
				 *
				 * @param string $image_size Image size. Default 'full'.
				 */

				$image_size = apply_filters('twenty_twenty_one_attachment_size', 'full');
				echo wp_get_attachment_image(get_the_ID(), $image_size);
				?>

				<?php if (wp_get_attachment_caption()) : ?>
					<figcaption class="wp-caption-text"><?php echo wp_kses_post(wp_get_attachment_caption()); ?></figcaption>
				<?php endif; ?>
			</figure><!-- .wp-block-image -->

			<?php
			the_content();
			TemplateTags::page_links();
			?>
		</div><!-- .entry-content -->

		<footer class="entry-footer">
			<?php
			// Check if there is a parent, then add the published in link.
			if (wp_get_post_parent_id($post)) {
				echo '<span class="posted-on">';
				printf(
					/* translators: %s: Parent post. */
					esc_html__('Published in %s', 'twentyone'),
					'<a href="' . esc_url(get_the_permalink(wp_get_post_parent_id($post))) . '">' . esc_html(get_the_title(wp_get_post_parent_id($post))) . '</a>'
				);
				echo '</span>';
			} else {
				// Edit post link.
				TemplateTags::edit_post_link();
			}

			// Retrieve attachment metadata.
			$metadata = wp_get_attachment_metadata();
			if ($metadata) {
				printf(
					'<span class="full-size-link"><span class="screen-reader-text">%1$s</span><a href="%2$s">%3$s &times; %4$s</a></span>',
					/* translators: Hidden accessibility text. */
					esc_html_x('Full size', 'Used before full size attachment link.', 'twentyone'), // phpcs:ignore WordPress.Security.EscapeOutput
					esc_url(wp_get_attachment_url()),
					absint($metadata['width']),
					absint($metadata['height'])
				);
			}

			if (wp_get_post_parent_id($post)) {
				// Edit post link.
				TemplateTags::edit_post_link();
			}
			?>
		</footer><!-- .entry-footer -->
	</article><!-- #post-<?php the_ID(); ?> -->
<?php
	// If comments are open or there is at least one comment, load up the comment template.
	if (comments_open() || get_comments_number()) {
		comments_template();
	}
} // End the loop.

get_footer();
