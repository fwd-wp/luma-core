<?php

namespace Luma\Core\Helpers;

use Luma\Core\Services\ThemeSettingsSchema;

/**
 * Custom template tags for this theme.
 *
 * All functions output escaped (safe) HTML.
 * 
 * Class must be initialised with TemplateTags::init($config) before use to set prefix and domain.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
class TemplateTags
{
	protected static string $domain = 'luma-core';

	public static function init($config): void
	{
		self::$domain = $config['text_domain'] ?? self::$domain;
	}

	public static function posted_on($class = 'posted-on', $time_class = 'entry-date published updated', bool $echo = true): ?string
	{
		$time_string = '<time class="' . esc_attr($time_class) . '" datetime="'
			. esc_attr(get_the_date(DATE_W3C)) . '">'
			. esc_html(get_the_date()) . '</time>';

		$output = '<span class="' . esc_attr($class) . '">'
			. sprintf(
				/* translators: %s is the date */
				__('Published %s', self::$domain),
				$time_string
			)
			. '</span>';

		$output = apply_filters('luma_core_posted_on', $output, $time_string, $class, $time_class);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function posted_ago(string $class = 'posted-on', bool $echo = true): ?string
	{
		$published_time = get_the_time('U');
		$modified_time  = get_the_modified_time('U');
		$current_time   = current_time('timestamp');

		$time_to_show = ($modified_time > $published_time) ? $modified_time : $published_time;
		$time_diff    = human_time_diff($time_to_show, $current_time);

		$output = '<span class="' . esc_attr($class) . '">'
			. sprintf(
				/* translators: %s is human-readable time difference, e.g. "5 minutes" */
				__('%s ago', self::$domain),
				$time_diff
			)
			. '</span>';

		$output = apply_filters('luma_core_posted_ago', $output, $time_diff, $class);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function posted_by(string $class = 'posted-by', bool $echo = true): ?string
	{
		if (!get_the_author() || !post_type_supports(get_post_type(), 'author')) {
			return null;
		}

		$author_name = get_the_author();
		$author_url  = esc_url(get_author_posts_url(get_the_author_meta('ID')));

		$output = '<span class="' . esc_attr($class) . '"><a href="' . $author_url . '" rel="author">'
			. esc_html($author_name) . '</a></span>';

		$output = apply_filters('luma_core_posted_by', $output, $author_name, $author_url);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function single_posted_meta(string $class = 'posted-meta', bool $echo = true): ?string
	{
		$output = '<div class="' . esc_attr($class) . '">';
		$output .= self::posted_on(false);

		if (!ThemeSettingsSchema::get_theme_mod('display_post_author_bio')) {
			$output .= self::posted_by(false);
		}

		$output .= self::edit_post_link(false);
		$output .= '</div>';

		$output = apply_filters('luma_core_single_posted_meta', $output, $class);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function archive_posted_meta(string $class = 'posted-meta', string $separator_class = 'separator', bool $echo = true): ?string
	{
		$output = '<div class="' . esc_attr($class) . '">';
		$output .= self::posted_by(false);
		$output .= '<span class="' . esc_attr($separator_class) . '"> &middot; </span>';
		$output .= self::posted_ago(false);
		$output .= '</div>';

		$output = apply_filters('luma_core_archive_posted_meta', $output, $class, $separator_class);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function post_taxonomies(bool $echo = true): ?string
	{
		if (!has_category() && !has_tag()) {
			return null;
		}

		$output = '<div class="post-taxonomies">';

		$categories_list = get_the_category_list(wp_get_list_item_separator());
		if ($categories_list) {
			$output .= '<span class="cat-links">'
				. sprintf(
					/* translators: %s is a comma-separated list of categories */
					__('Categorized as %s', self::$domain),
					wp_kses_post($categories_list)
				)
				. '</span>';
		}

		$tags_list = get_the_tag_list('', wp_get_list_item_separator());
		if ($tags_list && !is_wp_error($tags_list)) {
			$output .= '<span class="tags-links">'
				. sprintf(
					/* translators: %s is a comma-separated list of tags */
					__('Tagged %s', self::$domain),
					wp_kses_post($tags_list)
				)
				. '</span>';
		}

		$output .= '</div>';

		$output = apply_filters('luma_core_post_taxonomies', $output, $categories_list, $tags_list);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function post_thumbnail(bool $echo = true): ?string
	{
		if (!TemplateFunctions::can_show_post_thumbnail()) {
			return null;
		}

		$post_thumbnail_id = get_post_thumbnail_id();
		$caption           = wp_get_attachment_caption($post_thumbnail_id);
		$thumbnail_args    = ['loading' => is_singular() ? false : 'lazy'];

		$output = '<figure class="post-thumbnail">';

		if (is_singular()) {
			ob_start();
			the_post_thumbnail('post-thumbnail', $thumbnail_args);
			$output .= ob_get_clean();

			if ($caption) {
				$output .= '<figcaption class="wp-caption-text">' . wp_kses_post($caption) . '</figcaption>';
			}
		} else {
			$output .= '<a class="post-thumbnail-inner" href="' . esc_url(get_permalink()) . '" aria-hidden="true" tabindex="-1">';
			ob_start();
			the_post_thumbnail('post-thumbnail', $thumbnail_args);
			$output .= ob_get_clean();
			$output .= '</a>';
		}

		$output .= '</figure>';

		$output = apply_filters('luma_core_post_thumbnail', $output, $post_thumbnail_id, $caption);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function the_posts_pagination(bool $echo = true): ?string
	{
		$prev_icon = is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_right') : TemplateFunctions::get_icon_svg('ui', 'arrow_left');
		$next_icon = is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_left') : TemplateFunctions::get_icon_svg('ui', 'arrow_right');

		$prev_text = $prev_icon
			. '<span class="nav-prev-text">'
			. sprintf(
				/* translators: "Newer posts" text in pagination */
				__('Newer <span class="nav-short">posts</span>', self::$domain)
			)
			. '</span>';
		$next_text = '<span class="nav-next-text">'
			. sprintf(
				/* translators: "Older posts" text in pagination */
				__('Older <span class="nav-short">posts</span>', self::$domain)
			)
			. '</span>' . $next_icon;

		ob_start();
		the_posts_pagination([
			'before_page_number' => __('Page ', self::$domain),
			'mid_size'           => 0,
			'prev_text'          => $prev_text,
			'next_text'          => $next_text,
		]);
		$output = ob_get_clean();

		$output = apply_filters('luma_core_the_posts_pagination', $output, $prev_text, $next_text);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function page_links(bool $echo = true): ?string
	{
		global $page;

		ob_start();
		wp_link_pages([
			'before'   => '<nav class="page-links" aria-label="' . esc_attr(__('Page navigation', self::$domain)) . '">',
			'after'    => '</nav>',
			'pagelink' => sprintf(
				/* translators: %s is the page number. Use singular/plural as appropriate in your language. In English, use "Page" for both singular and plural. */
				_n('Page %s', 'Page %s', $page, self::$domain),
				$page
			),
		]);
		$output = ob_get_clean();

		/**
		 * Filter the page links output.
		 *
		 * @param string $output The HTML output for paginated page links.
		 */
		$output = apply_filters('luma_core_page_links', $output);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function edit_post_link(bool $echo = true): ?string
	{
		$post_type_obj = get_post_type_object(get_post_type());
		$singular_name = $post_type_obj ? $post_type_obj->labels->singular_name : __('post', self::$domain);

		ob_start();
		edit_post_link(
			sprintf(
				/* translators: %s is the singular post type name */
				__('Edit this %s', self::$domain),
				$singular_name
			),
			'<span class="edit-link">',
			'</span>'
		);
		$output = ob_get_clean();

		$output = apply_filters('luma_core_edit_post_link', $output, $singular_name);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}


	public static function single_post_navigation(string $class = 'meta-nav', string $title_class = 'post-title', bool $echo = true): ?string
	{
		$next_icon = is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_left') : TemplateFunctions::get_icon_svg('ui', 'arrow_right');
		$prev_icon = is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_right') : TemplateFunctions::get_icon_svg('ui', 'arrow_left');

		$next_label = esc_html__('Next post', self::$domain);
		$prev_label = esc_html__('Previous post', self::$domain);

		ob_start();

		the_post_navigation(
			array(
				'next_text' => '<span class="' . esc_attr($class) . '">' . $next_label . $next_icon . '</span><span class="' . esc_attr($title_class) . '">%title</span>',
				'prev_text' => '<span class="' . esc_attr($class) . '">' . $prev_icon . $prev_label . '</span><span class="' . esc_attr($title_class) . '">%title</span>',
			)
		);

		$output = ob_get_clean();

		$output = apply_filters('luma_core_single_post_navigation', $output);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	public static function site_title(string $class = 'site-title', bool $echo = true): ?string
	{
		$name  = get_bloginfo('name');
		$show  = ThemeSettingsSchema::get_theme_mod('wp-core_display_title_and_tagline');
		$class = $show ? $class : 'screen-reader-text';

		if (!$name) {
			return null;
		}

		$title = is_front_page()
			? esc_html($name)
			: '<a href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html($name) . '</a>';

		$output = '<h1 class="' . esc_attr($class) . '">' . $title . '</h1>';

		$output = apply_filters('luma_core_site_title', $output, $class, $title);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	/**
	 * Outputs the attachment image.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $size Image size to display.
	 * @param bool   $echo Whether to echo or return the HTML.
	 * @return string|null
	 */
	public static function attachment_image(string $size = 'full', bool $echo = true): ?string
	{
		$size = apply_filters('luma_core_attachment_size', $size);

		$html = wp_get_attachment_image(get_the_ID(), $size);
		$html = apply_filters('luma_core_attachment_image', $html, $size);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	/**
	 * Outputs the attachment caption inside a <figcaption>.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param bool $echo Whether to echo or return the HTML.
	 * @return string|null
	 */
	public static function attachment_caption(bool $echo = true): ?string
	{
		$caption = wp_get_attachment_caption();
		if (!$caption) {
			return $echo ? null : '';
		}

		$html = sprintf(
			'<figcaption class="wp-caption-text">%s</figcaption>',
			wp_kses_post($caption)
		);

		$html = apply_filters('luma_core_attachment_caption', $html, $caption);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	/**
	 * Outputs a link back to the parent post if one exists.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param bool $echo Whether to echo or return the HTML.
	 * @return string|null
	 */
	public static function attachment_parent_link(bool $echo = true): ?string
	{
		$parent_id = wp_get_post_parent_id(get_the_ID());
		if (!$parent_id) {
			return $echo ? null : '';
		}

		$parent_title = get_the_title($parent_id);
		$parent_link  = get_the_permalink($parent_id);

		$html = sprintf(
			'<span class="posted-on">%s <a href="%s">%s</a></span>',
			esc_html__('Published in', 'luma-core'),
			esc_url($parent_link),
			esc_html($parent_title)
		);

		$html = apply_filters('luma_core_attachment_parent_link', $html, $parent_id);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	/**
	 * Outputs a "Full-size" link showing width × height.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param bool $echo Whether to echo or return the HTML.
	 * @return string|null
	 */
	public static function attachment_full_size_link(bool $echo = true): ?string
	{
		$metadata = wp_get_attachment_metadata();
		if (!$metadata || empty($metadata['width']) || empty($metadata['height'])) {
			return $echo ? null : '';
		}

		$html = sprintf(
			'<span class="full-size-link"><span class="screen-reader-text">%1$s</span><a href="%2$s">%3$d × %4$d</a></span>',
			esc_html_x('Full size', 'Used before full size attachment link.', 'luma-core'),
			esc_url(wp_get_attachment_url()),
			absint($metadata['width']),
			absint($metadata['height'])
		);

		$html = apply_filters('luma_core_attachment_full_size_link', $html, $metadata);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	/**
	 * Outputs the edit post link for attachments.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param bool $echo Whether to echo or return the HTML.
	 * @return string|null
	 */
	public static function attachment_edit_link(bool $echo = true): ?string
	{
		if (!current_user_can('edit_post', get_the_ID())) {
			return $echo ? null : '';
		}

		ob_start();
		edit_post_link(
			esc_html__('Edit', 'luma-core'),
			'<span class="edit-link">',
			'</span>'
		);
		$html = ob_get_clean();

		$html = apply_filters('luma_core_attachment_edit_link', $html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}


	public static function maybe_comments_template(bool $echo = true): ?string
	{
		if (!comments_open() && !get_comments_number()) {
			return null;
		}

		ob_start();
		comments_template();
		$html = ob_get_clean();

		$html = apply_filters('luma_core_maybe_comments_template', $html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	public static function attachment_navigation(bool $echo = true): ?string
	{
		if (!is_attachment()) {
			return null;
		}

		$html = get_the_post_navigation([
			'prev_text' => sprintf(
				__('<span class="meta-nav">Published in</span><span class="post-title">%s</span>', 'luma-core'),
				'%title'
			),
		]);

		$html = apply_filters('luma_core_attachment_navigation', $html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}
}
