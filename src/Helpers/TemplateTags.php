<?php

namespace Luma\Core\Helpers;

use Luma\Core\Services\ThemeSettingsSchema;

/**
 * Custom template tags for this theme
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

class TemplateTags
{


	/**
	 * Prints HTML with meta information for the current post-date/time.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function posted_on(): void
	{
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

		$time_string = sprintf(
			$time_string,
			esc_attr(get_the_date(DATE_W3C)),
			esc_html(get_the_date())
		);
		echo '<span class="posted-on">';
		printf(
			/* translators: %s: Publish date. */
			esc_html__('Published %s', 'luma-core'),
			$time_string // phpcs:ignore WordPress.Security.EscapeOutput
		);
		echo '</span>';
	}

	/**
	 * Prints HTML with meta information for how long ago the post was published or updated
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function posted_ago(): void
	{
		$published_time = get_the_time('U');
		$modified_time  = get_the_modified_time('U');
		$current_time   = current_time('timestamp');

		// Default to published
		$label = __('%s ago', 'luma-core');
		$time_diff = human_time_diff($published_time, $current_time);

		// If modified time is later than published time, show "Updated"
		if ($modified_time > $published_time) {
			$time_diff = human_time_diff($modified_time, $current_time);
		}

		echo '<span class="posted-on">';
		printf(
			/* translators: %s: Human-readable time difference. */
			esc_html($label),
			esc_html($time_diff)
		);
		echo '</span>';
	}


	/**
	 * Prints HTML with meta information about theme author.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function posted_by(): void
	{
		// TODO check if the if condition is correct based on the output
		if (get_the_author() && post_type_supports(get_post_type(), 'author')) {
			echo '<span class="posted-by">';
			printf(
				/* translators: %s: Author name. */
				esc_html__('%s', 'luma-core'),
				'<a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '" rel="author">' . esc_html(get_the_author()) . '</a>'
			);
			echo '</span>';
		}
	}

	/**
	 * Prints HTML of the whole posted by section
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function single_posted_meta(): void
	{ ?>
		<div class="posted-meta">
			<?php self::posted_on(); ?>
			<?php if (!ThemeSettingsSchema::theme_mod_with_default('display_post_author_bio')): ?>
				<?php self::posted_by(); ?>
			<?php endif; ?>
			<?php self::edit_post_link(); ?>
		</div>
	<?php
	}

	/**
	 * Prints HTML of the whole posted by section
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function archive_posted_meta(): void
	{ ?>
		<div class="posted-meta">
			<?php self::posted_by(); ?>
			<span class="separator"> &middot; </span>
			<?php self::posted_ago(); ?>
		</div>
	<?php
	}


	/**
	 * Displays taxonomy categories and tags for a post
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */

	public static function post_taxonomies(): void
	{
		if (has_category() || has_tag()) {

			echo '<div class="post-taxonomies">';

			$categories_list = get_the_category_list(wp_get_list_item_separator());
			if ($categories_list) {
				printf(
					/* translators: %s: List of categories. */
					'<span class="cat-links">' . esc_html__('Categorized as %s', 'luma-core') . ' </span>',
					$categories_list // phpcs:ignore WordPress.Security.EscapeOutput
				);
			}

			$tags_list = get_the_tag_list('', wp_get_list_item_separator());
			if ($tags_list && ! is_wp_error($tags_list)) {
				printf(
					/* translators: %s: List of tags. */
					'<span class="tags-links">' . esc_html__('Tagged %s', 'luma-core') . '</span>',
					$tags_list // phpcs:ignore WordPress.Security.EscapeOutput
				);
			}
			echo '</div>';
		}
	}

	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function post_thumbnail(): void
	{
		if (! TemplateFunctions::can_show_post_thumbnail()) {
			return;
		}
	?>
		<?php if (is_singular()) : ?>
			<?php // post, page, custom post types, attachment & password protected excluded above 
			?>
			<figure class="post-thumbnail">
				<?php
				// Lazy-loading attributes should be skipped for thumbnails since they are immediately in the viewport.
				the_post_thumbnail('post-thumbnail', array('loading' => false));
				?>
				<?php if (wp_get_attachment_caption(get_post_thumbnail_id())) : ?>
					<figcaption class="wp-caption-text"><?php echo wp_kses_post(wp_get_attachment_caption(get_post_thumbnail_id())); ?></figcaption>
				<?php endif; ?>
			</figure><!-- .post-thumbnail -->

		<?php else : ?>
			<?php // list pages - links the image, but no caption
			?>
			<figure class="post-thumbnail">
				<a class="post-thumbnail-inner" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<?php the_post_thumbnail('post-thumbnail'); ?>
				</a>
			</figure><!-- .post-thumbnail -->

		<?php endif; ?>
<?php
	}

	/**
	 * Print the next and previous posts navigation.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function the_posts_pagination(): void
	{
		the_posts_pagination(
			array(
				'before_page_number' => esc_html__('Page', 'luma-core') . ' ',
				'mid_size'           => 0,
				'prev_text'          => sprintf(
					'%s <span class="nav-prev-text">%s</span>',
					is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_right') : TemplateFunctions::get_icon_svg('ui', 'arrow_left'),
					wp_kses(
						__('Newer <span class="nav-short">posts</span>', 'luma-core'),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					)
				),
				'next_text'          => sprintf(
					'<span class="nav-next-text">%s</span> %s',
					wp_kses(
						__('Older <span class="nav-short">posts</span>', 'luma-core'),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_left') : TemplateFunctions::get_icon_svg('ui', 'arrow_right')
				),
			)
		);
	}

	/**
	 * Displays page links for paginated posts
	 * 
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function page_links(): void
	{
		wp_link_pages(
			array(
				'before'   => '<nav class="page-links" aria-label="' . esc_attr__('Page', 'luma-core') . '">',
				'after'    => '</nav>',
				/* translators: %: Page number. */
				'pagelink' => esc_html__('Page %', 'luma-core'),
			)
		);
	}
	/**
	 * Displays edit post link for pages and posts
	 * 
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function edit_post_link(): void
	{
		edit_post_link(
			sprintf(
				/* translators: %s: Post title. Only visible to screen readers. */
				esc_html__('Edit %s', 'luma-core'),
				'<span class="screen-reader-text">' . get_the_title() . '</span>'
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
}
