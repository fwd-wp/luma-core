<?php

namespace Luma\Core\Helpers;

use Luma\Core\Customize\ThemeSettingsSchema;

/**
 * Custom template tags for this theme.
 *
 * All functions output escaped (safe) HTML.
 * 
 * If method uses text domain, it must be checked and potentially initialised with self::set_domain()
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
class TemplateTags extends TemplateTagsBase
{

	/**
	 * Outputs or returns a label for posts marked as sticky.
	 *
	 * Generates a wrapped label (e.g., "Featured Post") using the post type's
	 * singular name, with optional before/after text. Output can be echoed
	 * or returned as a string.
	 *
	 * @param bool  $echo Whether to echo the output. If false, the method returns the HTML.
	 * @param array $args {
	 *     Optional. Arguments controlling the sticky label output.
	 *
	 *     @type string $before         Text displayed before the label. Default "Featured".
	 *     @type string $after          Text displayed after the label. Default empty string.
	 *     @type string $singular_name  Post-type singular label. Auto-filled from post type.
	 *     @type string $class          CSS class for the wrapper <span>. Default "sticky-label".
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The sticky label HTML when `$echo` is false, otherwise null.
	 */
	public static function sticky_label(bool $echo = true, array $args = []): ?string
	{
		if (!is_sticky()) {
			return null;
		}

		self::set_domain();

		$defaults = [
			'before'        => __('Featured', self::$domain),
			'after'         => '',
			'singular_name' => TemplateFunctions::get_post_type_label('singular'),
			'class'         => 'sticky-label',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filters the arguments used to generate the sticky label.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $args Parsed arguments with defaults applied.
		 * @param bool  $echo Whether the final output will be echoed.
		 */
		$args = apply_filters('luma_core_sticky_label_args', $args, $echo);

		$parts = [
			$args['before'],
			esc_html($args['singular_name']),
			$args['after'],
		];

		$html = self::wrap_content($args['class'], $parts);

		/**
		 * Filters the final HTML output of the sticky label.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $html The generated sticky label HTML.
		 * @param array  $args Parsed arguments used to build the HTML.
		 * @param bool   $echo Whether the output is being echoed directly.
		 */
		$html = apply_filters('luma_core_sticky_label', $html, $args, $echo);

		// Escape final output after filters.
		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}


	public static function published_on(bool $echo = true, array $args = []): ?string
	{
		$html = self::build_time_output('published', false, $args);
		if ($echo && $html !== null) echo $html;
		return $html;
	}

	public static function published_ago(bool $echo = true, array $args = []): ?string
	{
		$fallback = fn() => self::published_on(false, $args);
		$html = self::build_time_output('published', true, $args, $fallback);
		if ($echo && $html !== null) echo $html;
		return $html;
	}

	public static function updated_on(bool $echo = true, array $args = []): ?string
	{
		$html = self::build_time_output('modified', false, $args);
		if ($echo && $html !== null) echo $html;
		return $html;
	}

	public static function updated_ago(bool $echo = true, array $args = []): ?string
	{
		$fallback = fn() => self::updated_on(false, $args);
		$html = self::build_time_output('modified', true, $args, $fallback);
		if ($echo && $html !== null) echo $html;
		return $html;
	}

	/**
	 * Outputs or returns the most relevant human-readable "ago" timestamp.
	 *
	 * Compares the published and modified timestamps and chooses the newer one.
	 * If the post has been updated after publication, calls updated_ago().
	 * Otherwise, calls published_ago().
	 *
	 * Only arguments explicitly provided in `$args` are forwarded, allowing
	 * each child function's own defaults to remain intact.
	 *
	 * @param bool  $echo Whether to echo the output. If false, returns the HTML.
	 * @param array $args {
	 *     Optional. Arguments for controlling the output. All keys are optional:
	 *
	 *     @type string|null $published_class    CSS class for the published <span>.  
	 *     @type string|null $published_before   Text/HTML before the published time.  
	 *     @type string|null $published_after    Text/HTML after the published time.  
	 *     @type int|null    $published_max_days Max "ago" days before fallback.
	 *
	 *     @type string|null $updated_class      CSS class for the updated <span>.  
	 *     @type string|null $updated_before     Text/HTML before the updated time.  
	 *     @type string|null $updated_after      Text/HTML after the updated time.  
	 *     @type int|null    $updated_max_days   Max "ago" days before fallback.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The HTML if `$echo` is false, otherwise null.
	 */
	public static function most_recent_ago(bool $echo = true, array $args = []): ?string
	{
		$defaults = [
			// Published version
			'published_class'    => null,   // null = do NOT override child default
			'published_before'   => null,
			'published_after'    => null,
			'published_max_days' => null,

			// Updated version
			'updated_class'      => null,
			'updated_before'     => null,
			'updated_after'      => null,
			'updated_max_days'   => null,
		];

		$args = wp_parse_args($args, $defaults);

		$published = get_the_time('U');
		$modified  = get_the_modified_time('U');

		$use_updated = ($modified > $published);

		if ($use_updated) {

			// Build ONLY the args that were provided (preserve child defaults)
			$updated_args = array_filter([
				'class'    => $args['updated_class'],
				'before'   => $args['updated_before'],
				'after'    => $args['updated_after'],
				'max_days' => $args['updated_max_days'],
			], static fn($v) => $v !== null);

			$output = self::updated_ago(false, $updated_args);
		} else {

			// Same: only pass through provided overrides
			$published_args = array_filter([
				'class'    => $args['published_class'],
				'before'   => $args['published_before'],
				'after'    => $args['published_after'],
				'max_days' => $args['published_max_days'],
			], static fn($v) => $v !== null);

			// Must pass echo=false so we control output
			$output = self::published_ago(false, $published_args);
		}

		$output = wp_kses_post($output);

		if ($echo && $output !== null) {
			echo $output;
			return null;
		}

		return $output;
	}




	/**
	 * Outputs or returns the post author with a link to their posts.
	 *
	 * Supports optional text before and after the author name. All parameters
	 * are passed via `$args` for consistent filtering and extensibility.
	 *
	 * @param bool  $echo   Whether to echo the output or return it.
	 * @param array $args {
	 *     Optional. Arguments controlling the output.
	 *
	 *     @type string $class  CSS class for the wrapper <span>. Default 'byline'.
	 *     @type string $before Text displayed before the author link. Default 'Created by'.
	 *     @type string $after  Text displayed after the author link. Default ''.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The HTML output of the posted-by link, or null if no author.
	 */
	public static function posted_by(bool $echo = true, array $args = []): ?string
	{
		if (!get_the_author() || !post_type_supports(get_post_type(), 'author')) {
			return null;
		}

		self::set_domain();

		$defaults = [
			'class'  => 'byline',
			'before' => __('Created by', self::$domain),
			'after'  => '',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the posted-by output.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $args Parsed parameters for the posted-by output.
		 * @param bool  $echo   Whether the output will be echoed.
		 */
		$args = apply_filters('luma_core_posted_by_args', $args, $echo);

		$author_name = get_the_author();
		$author_url  = esc_url(get_author_posts_url(get_the_author_meta('ID')));

		$parts = [
			$args['before'],
			'<a href="' . $author_url . '" rel="author">' . esc_html($author_name) . '</a>',
			$args['after'],
		];

		$html = self::wrap_content($args['class'], $parts);

		/**
		 * Filters the HTML output of the posted-by link.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $html        The HTML output.
		 * @param string $author_name The display name of the post author.
		 * @param string $author_url  URL to the author's posts.
		 * @param array  $args      The parsed parameters array.
		 * @param bool   $echo        Whether the output will be echoed.
		 */
		$html = apply_filters('luma_core_posted_by', $html, $author_name, $author_url, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}



	public static function category_list(bool $echo = true, array $args = []): ?string
	{
		return self::term_list($echo, 'category', $args);
	}

	public static function tag_list(bool $echo = true, array $args = []): ?string
	{
		return self::term_list($echo, 'tag', $args);
	}

	/**
	 * Outputs or returns navigation links for multi-page posts or post lists.
	 *
	 * Provides full control over the wrapper class, link text, and supports
	 * translation and post type-specific labels.
	 *
	 * @param bool  $echo   Whether to echo the output or return it.
	 * @param array $args {
	 *     Optional. Arguments controlling output.
	 *
	 *     @type string $class      CSS class for the wrapper <nav>. Default ''.
	 *     @type string $newer_text Text for the "Newer" link. Default 'Newer posts'.
	 *     @type string $older_text Text for the "Older" link. Default 'Older posts'.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null HTML output or null if echoed.
	 */
	public static function the_posts_navigation(bool $echo = true, array $args = []): ?string
	{
		self::set_domain();

		// Get post type plural label for defaults
		$plural = TemplateFunctions::get_post_type_label('plural') ?? __('posts', self::$domain);

		$defaults = [
			'class'      => '',
			'newer_text' => sprintf(__('Newer %s', self::$domain), $plural),
			'older_text' => sprintf(__('Older %s', self::$domain), $plural),
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the posts navigation.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $args Parsed parameters controlling the posts navigation.
		 * @param bool  $echo   Whether the output will be echoed.
		 */
		$args = apply_filters('luma_core_posts_nav_links_args', $args, $echo);

		$html = get_the_posts_navigation([
			'prev_text' => $args['older_text'],
			'next_text' => $args['newer_text'],
			'class'     => $args['class'],
		]);

		/**
		 * Filters the posts navigation links output.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $html   The HTML output of the posts navigation.
		 * @param array  $args The parsed parameters array.
		 * @param string $plural Plural label for the post type.
		 * @param bool   $echo   Whether the output will be echoed.
		 */
		$html = apply_filters('luma_core_posts_nav_links', $html, $args, $plural, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}


	/**
	 * Outputs or returns the single post navigation markup.
	 *
	 * Supports customization of wrapper classes, icons, and labels, and allows
	 * filters for both arguments and final output.
	 *
	 * @param bool  $echo   Whether to echo the navigation. Default true.
	 * @param array $args {
	 *     Optional. Arguments controlling output.
	 *
	 *     @type string $class       Class for the meta navigation label wrapper. Default 'meta-nav'.
	 *     @type string $title_class Class for the linked post title wrapper. Default 'post-title'.
	 *     @type string $next_icon   SVG/icon HTML for the "next" link. Default arrow right/left based on RTL.
	 *     @type string $prev_icon   SVG/icon HTML for the "previous" link. Default arrow left/right based on RTL.
	 *     @type string $next_label  Text label for the next post link. Default 'Next [Post Type]'.
	 *     @type string $prev_label  Text label for the previous post link. Default 'Previous [Post Type]'.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null Navigation markup or null when echoing.
	 */
	public static function single_post_navigation(bool $echo = true, array $args = []): ?string
	{
		self::set_domain();

		// Get current post type singular label
		$singular_label = TemplateFunctions::get_post_type_label('singular') ?? esc_html__('Post', self::$domain);

		$defaults = [
			'class'       => 'meta-nav',
			'title_class' => 'post-title',
			'next_icon'   => is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_left') : TemplateFunctions::get_icon_svg('ui', 'arrow_right'),
			'prev_icon'   => is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_right') : TemplateFunctions::get_icon_svg('ui', 'arrow_left'),
			'next_label'  => sprintf(esc_html__('Next %s', self::$domain), $singular_label),
			'prev_label'  => sprintf(esc_html__('Previous %s', self::$domain), $singular_label),
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the single post navigation.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $args Parsed parameters controlling the navigation.
		 * @param bool  $echo   Whether the output will be echoed.
		 */
		$args = apply_filters('luma_core_single_post_navigation_args', $args, $echo);

		$output = get_the_post_navigation([
			'next_text' =>
			'<span class="' . esc_attr($args['class']) . '">' . $args['next_label'] . $args['next_icon'] . '</span>' .
				'<span class="' . esc_attr($args['title_class']) . '">%title</span>',

			'prev_text' =>
			'<span class="' . esc_attr($args['class']) . '">' . $args['prev_icon'] . $args['prev_label'] . '</span>' .
				'<span class="' . esc_attr($args['title_class']) . '">%title</span>',
		]);

		/**
		 * Filters the single post navigation markup.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $output      Complete navigation HTML.
		 * @param array  $args      Parsed parameters array.
		 * @param bool   $echo        Whether the output will be echoed.
		 */
		$output = apply_filters('luma_core_single_post_navigation', $output, $args, $echo);

		$output = wp_kses_post($output);

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}

	/**
	 * Outputs or returns the site title.
	 *
	 * Wraps the site title in an <h1> element. On non-front pages, the title
	 * is linked to the home page. Supports CSS class customization and
	 * respects the theme setting for displaying title and tagline.
	 * 
	 *
	 * @param bool  $echo   Whether to echo the output. Default true.
	 * @param array $args {
	 *     Optional. Arguments controlling output.
	 *
	 *     @type string $class CSS class for the <h1> wrapper. Default 'site-title'.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The formatted site title HTML, or null if no site name.
	 */
	public static function site_title(bool $echo = true, array $args = []): ?string
	{
		$defaults = [
			'class' => 'site-title',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the site title.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $args Parsed parameters controlling the site title.
		 * @param bool  $echo   Whether the output will be echoed.
		 */
		$args = apply_filters('luma_core_site_title_args', $args, $echo);

		$name = get_bloginfo('name');
		$show = ThemeSettingsSchema::get_theme_mod('header_navbar_display_title');
		$class = $show ? $args['class'] : 'screen-reader-text';

		if (!$name) {
			return null;
		}


		$title = ! is_front_page() && $show
			? '<a href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html($name) . '</a>'
			: esc_html($name);

		if (TemplateFunctions::is_list_view()) {
			// if page title is shown, h1 is used in the page title, not for site title
			$html = '<div class="' . esc_attr($class) . '">' . $title . '</div>';
		} else {
			$html = '<h1 class="' . esc_attr($class) . '">' . $title . '</h1>';
		}


		/**
		 * Filter the output of the site title HTML.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $html The HTML output of the site title.
		 * @param string $class  CSS class for the <h1> wrapper.
		 * @param string $title  The site title text or link.
		 * @param array  $args      Parsed parameters array.
		 * @param bool   $echo   Whether the output will be echoed.
		 */
		$html = apply_filters('luma_core_site_title', $html, $class, $title, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}



	/**
	 * Outputs or returns the archive/blog page header.
	 *
	 * Includes title and optional description with core classes.
	 *
	 * @param bool  $echo Whether to echo the output. Default true.
	 * @param array $args Optional arguments to customize classes:
	 *                    [
	 *                        'header_class'      => 'page-header',
	 *                        'title_class'       => 'page-title',
	 *                        'description_class' => 'archive-description',
	 *                    ]
	 * @return string|null The formatted page header HTML, or null if no title.
	 */
	public static function page_header(bool $echo = true, array $args = []): ?string
	{

		// Only show on archives or blog index (not front page)
		if (! TemplateFunctions::is_list_view()) {
			return null;
		}

		// Default classes
		$defaults = [
			'header_class'      => 'page-header',
			'title_class'       => 'page-title',
			'description_class' => 'archive-description',
		];
		$args = wp_parse_args($args, $defaults);

		// Get page title
		$title = '';
		if (is_archive()) {
			$title = wp_strip_all_tags(get_the_archive_title());
		} elseif (is_home() && ! is_front_page()) {
			$page_for_posts = get_option('page_for_posts');
			if ($page_for_posts) {
				$title = get_the_title($page_for_posts);
			}
		}

		if (! $title) {
			return null;
		}

		// Get page description
		$description = '';
		if (is_archive()) {
			$description = get_the_archive_description();
		}

		// Set aria-labelledby only with existing elements
		$aria_ids = ['page-header-title'];
		if ($description) {
			$aria_ids[] = 'page-header-description';
		}
		$header_attrs = sprintf(' aria-labelledby="%s"', implode(' ', $aria_ids));

		// Build header HTML
		$html  = sprintf(
			'<header class="%s"%s>',
			esc_attr($args['header_class']),
			$header_attrs
		);
		$html .= sprintf(
			'<h1 id="page-header-title" class="%s">%s</h1>',
			esc_attr($args['title_class']),
			esc_html($title)
		);

		if ($description) {
			$html .= sprintf(
				'<p id="page-header-description" class="%s">%s</p>',
				esc_attr($args['description_class']),
				wp_kses_post($description)
			);
		}

		$html .= '</header>';

		// Apply filter for customization
		$html = apply_filters('luma_core_page_header', $html, $title, $description, $args, $echo);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	/**
	 * Outputs or returns the comments template for the current post.
	 *
	 * Only outputs if comments are open or there are existing comments.
	 * Captures the output of `comments_template()` so it can be filtered
	 * and optionally returned instead of echoed.
	 *
	 * @param bool  $echo   Whether to echo the output (true) or return it (false). Default true.
	 * @param array $args Optional arguments for future flexibility.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The comments HTML if `$echo` is false, or null if echoed.
	 */
	public static function maybe_comments_template(bool $echo = true): ?string
	{
		// exit early if not a single page
		if (! is_single()) {
			return null;
		}

		if (!comments_open() && !get_comments_number()) {
			return null;
		}

		ob_start();
		comments_template();
		$html = ob_get_clean();

		/**
		 * Filter the comments template output.
		 *
		 * Allows modification of the comments markup before it is echoed or returned.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $html   The HTML output of the comments template.
		 * @param bool   $echo   Whether the output will be echoed.
		 */
		$html = apply_filters('luma_core_maybe_comments_template', $html, $echo);

		if ($echo) {
			// Output comes directly from comments_template() and is safe.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $html;
			return null;
		}

		return $html;
	}


	/**
	 * Outputs or returns a "Continue reading" link for the current post. 
	 * 
	 * You need to disable default 'continue reading' text and link with 'filter_excerpt_more'
	 *
	 * This helper builds a customizable read-more link with optional screen-reader
	 * text containing the post title. All output is safely escaped, and both the
	 * arguments and the final markup can be filtered.
	 *
	 * @param bool  $echo Whether to echo the link (true) or return it as a string (false).
	 * @param array $args {
	 *     Optional. Arguments to control the markup.
	 *
	 *     @type string $class               CSS class for the wrapper div. Default 'read-more'.
	 *     @type string $text                Link text. Default 'Continue reading'.
	 *     @type string $title               Post title used in screen-reader text. Defaults to current title.
	 *     @type string $url                 The post URL. Default get_permalink().
	 *     @type bool   $screen_reader_text  Whether to append screen-reader text. Default true.
	 * }
	 *
	 * @return string|null Markup string if `$echo` is false, otherwise null.
	 */
	public static function continue_reading_link(
		bool $echo = true,
		array $args = []
	): ?string {

		self::set_domain();

		$defaults = [
			'class'               => 'read-more',
			'text'                => __('Continue reading', self::$domain),
			'title'               => wp_strip_all_tags(get_the_title()) ?: __('this post', self::$domain),
			'url'                 => get_permalink(),
			'screen_reader_text'  => true,
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filters the arguments used to generate the continue-reading link.
		 *
		 * Allows developers to modify classes, text, URL, title, and accessibility
		 * settings before the markup is created.
		 *
		 * @param array $args Parsed and merged arguments.
		 * @param bool  $echo Whether the function is set to echo or return.
		 */
		$args = apply_filters('luma_core_continue_reading_link_args', $args, $echo);

		// Build markup
		$html  = '<div class="' . esc_attr($args['class']) . '">';
		$html .= '<a href="' . esc_url($args['url']) . '">';
		$html .= esc_html($args['text']);

		if ($args['screen_reader_text']) {
			$html .= '<span class="screen-reader-text"> ' . esc_html($args['title']) . '</span>';
		}

		$html .= '</a></div>';

		/**
		 * Filters the final continue-reading link markup.
		 *
		 * This runs after escaping individual attributes but before final kses
		 * sanitization, allowing full control over the wrapper or link structure.
		 *
		 * @param string $html The generated HTML markup.
		 * @param array  $args The arguments used to build it.
		 * @param bool   $echo Whether the function is set to echo or return.
		 */
		$html = apply_filters('luma_core_continue_reading_link', $html, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	/**
	 * Outputs or returns the post thumbnail with optional caption.
	 *
	 * Wraps the thumbnail in a <figure> with a CSS class, and optionally
	 * wraps the caption in a <figcaption> with its own class. For
	 * non-singular views, the thumbnail is wrapped in a link to the post.
	 *
	 * @param bool  $echo   Whether to echo the output or return it.
	 * @param array $args {
	 *     Optional. Arguments controlling output.
	 *
	 *     @type string $class            CSS class for the <figure> wrapper. Default 'post-thumbnail'.
	 *     @type string $figcaption_class CSS class for the <figcaption> element. Default 'post-thumbnail-inner'.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The formatted HTML or null if no thumbnail exists.
	 */
	public static function post_thumbnail(bool $echo = true, array $args = []): ?string
	{
		if (!TemplateFunctions::can_show_post_thumbnail()) {
			return null;
		}

		$defaults = [
			'class'            => 'post-thumbnail',
			'figcaption_class' => 'post-thumbnail-inner',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the post thumbnail.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $args Parsed parameters for the post thumbnail.
		 * @param bool  $echo   Whether the output will be echoed.
		 */
		$args = apply_filters('luma_core_post_thumbnail_args', $args, $echo);

		$post_thumbnail_id = get_post_thumbnail_id();
		$caption           = wp_get_attachment_caption($post_thumbnail_id);
		$thumbnail_args    = ['loading' => is_singular() ? false : 'lazy'];

		$parts = [];

		if (is_singular()) {
			$parts[] = get_the_post_thumbnail('post-thumbnail', $thumbnail_args);

			if ($caption) {
				$parts[] = '<figcaption class="' . esc_attr($args['figcaption_class']) . '">'
					. wp_kses_post($caption)
					. '</figcaption>';
			}
		} else {
			$parts[] = '<a class="' . esc_attr($args['figcaption_class']) . '" href="'
				. esc_url(get_permalink())
				. '" aria-hidden="true" tabindex="-1">'
				. get_the_post_thumbnail('post-thumbnail', $thumbnail_args)
				. '</a>';
		}

		$html = self::wrap_content($args['class'], $parts, 'figure');

		/**
		 * Filters the post thumbnail output.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $html               The HTML output of the post thumbnail.
		 * @param int    $post_thumbnail_id  Attachment ID of the post thumbnail.
		 * @param string $caption            Caption text for the thumbnail.
		 * @param array  $args             The parsed parameters array.
		 * @param bool   $echo               Whether the output will be echoed.
		 */
		$html = apply_filters('luma_core_post_thumbnail', $html, $post_thumbnail_id, $caption, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}

	/**
	 * Outputs or returns the attachment image for the current post.
	 *
	 * Supports a customizable image size and allows filtering of both the
	 * arguments and final markup.
	 *
	 * @param bool  $echo Whether to echo the HTML (true) or return it (false).
	 * @param array $args {
	 *     Optional. Arguments to control the markup.
	 *
	 *     @type string $size Image size to display. Default 'full'.
	 *     @type string $class CSS class for the <img> element. Default empty.
	 *     @type string $alt   Alternative text for the image. Defaults to attachment alt.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null HTML output if `$echo` is false, otherwise null.
	 */
	public static function attachment_image(bool $echo = true, array $args = []): ?string
	{
		$defaults = [
			'size'  => 'full',
			'class' => '',
			'alt'   => get_post_meta(get_the_ID(), '_wp_attachment_image_alt', true) ?: '',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the attachment image.
		 *
		 * @param array $args Parsed and merged arguments.
		 * @param bool  $echo Whether the function is set to echo or return.
		 */
		$args = apply_filters('luma_core_attachment_image_args', $args, $echo);

		$html = wp_get_attachment_image(
			get_the_ID(),
			$args['size'],
			false,
			['class' => esc_attr($args['class']), 'alt' => esc_attr($args['alt'])]
		);

		/**
		 * Filter the final attachment image markup.
		 *
		 * @param string $html The generated HTML markup.
		 * @param array  $args The arguments used to build it.
		 * @param bool   $echo Whether the function is set to echo or return.
		 */
		$html = apply_filters('luma_core_attachment_image', $html, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}


	/**
	 * Outputs or returns the attachment caption inside a <figcaption>.
	 *
	 * Supports optional CSS class customization and allows filtering of both
	 * the arguments and final markup.
	 *
	 * @param bool  $echo Whether to echo the HTML (true) or return it (false).
	 * @param array $args {
	 *     Optional. Arguments to control the markup.
	 *
	 *     @type string $class CSS class for the <figcaption>. Default 'wp-caption-text'.
	 *     @type string $caption Caption text. Defaults to the attachment caption.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null HTML output if `$echo` is false, otherwise null.
	 */
	public static function attachment_caption(bool $echo = true, array $args = []): ?string
	{
		$defaults = [
			'class'   => 'wp-caption-text',
			'caption' => wp_get_attachment_caption() ?: '',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the attachment caption.
		 *
		 * @param array $args Parsed and merged arguments.
		 * @param bool  $echo Whether the function is set to echo or return.
		 */
		$args = apply_filters('luma_core_attachment_caption_args', $args, $echo);

		if (empty($args['caption'])) {
			return $echo ? null : '';
		}

		$html = sprintf(
			'<figcaption class="%s">%s</figcaption>',
			esc_attr($args['class']),
			wp_kses_post($args['caption'])
		);

		/**
		 * Filter the final attachment caption markup.
		 *
		 * @param string $html    The generated HTML markup.
		 * @param array  $args    The arguments used to build it.
		 * @param bool   $echo    Whether the function is set to echo or return.
		 */
		$html = apply_filters('luma_core_attachment_caption', $html, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}


	/**
	 * Outputs a link back to the parent post if one exists.
	 *
	 * Wraps the parent post link in a <span> with optional before/after text
	 * and allows filtering of both arguments and final markup.
	 *
	 * @param bool  $echo Whether to echo or return the HTML.
	 * @param array $args {
	 *     Optional. Arguments to control the markup.
	 *
	 *     @type string $class  CSS class for the wrapper span. Default 'posted-on'.
	 *     @type string $before Text/HTML to output before the link. Default 'Published in'.
	 *     @type string $after  Text/HTML to output after the link. Default ''.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The HTML output if `$echo` is false, otherwise null.
	 */
	public static function attachment_parent_link(bool $echo = true, array $args = []): ?string
	{
		$parent_id = wp_get_post_parent_id(get_the_ID());
		if (!$parent_id) {
			return $echo ? null : '';
		}

		self::set_domain();

		$defaults = [
			'class'  => 'posted-on',
			'before' => __('Published in', self::$domain),
			'after'  => '',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the parent post link.
		 *
		 * @param array $args      Parsed and merged arguments.
		 * @param bool  $echo      Whether the function is set to echo or return.
		 * @param int   $parent_id The parent post ID.
		 */
		$args = apply_filters('luma_core_attachment_parent_link_args', $args, $echo, $parent_id);

		$parent_title = get_the_title($parent_id);
		$parent_link  = get_the_permalink($parent_id);

		$html = sprintf(
			'<span class="%s">%s <a href="%s">%s</a> %s</span>',
			esc_attr($args['class']),
			$args['before'],
			esc_url($parent_link),
			esc_html($parent_title),
			$args['after']
		);

		/**
		 * Filter the final parent post link markup.
		 *
		 * @param string $html        The generated HTML markup.
		 * @param array  $args        The arguments used to build it.
		 * @param int    $parent_id   The parent post ID.
		 * @param string $parent_title The title of the parent post.
		 * @param string $parent_link  URL of the parent post.
		 * @param bool   $echo        Whether the function is set to echo or return.
		 */
		$html = apply_filters('luma_core_attachment_parent_link', $html, $args, $parent_id, $parent_title, $parent_link, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}


	/**
	 * Outputs or returns the navigation for an attachment post.
	 *
	 * Wraps the navigation link to the parent post of the attachment, allowing
	 * customization of the link text and CSS class. Only applies when viewing
	 * an attachment page.
	 *
	 * @param bool  $echo Whether to echo the output (true) or return it (false).
	 * @param array $args {
	 *     Optional. Arguments to control the markup.
	 *
	 *     @type string $class CSS class applied to the wrapper span. Default 'meta-nav posted-in'.
	 *     @type string $text  Text displayed before the parent post title. Default 'Published in'.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The formatted HTML if `$echo` is false, otherwise null.
	 */
	public static function attachment_navigation(bool $echo = true, array $args = []): ?string
	{
		if (!is_attachment()) {
			return $echo ? null : '';
		}

		self::set_domain();

		$defaults = [
			'class' => 'meta-nav posted-in',
			'text'  => __('Published in', self::$domain),
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filters the arguments used to generate attachment navigation.
		 *
		 * @param array $args  Parsed and merged arguments.
		 * @param bool  $echo  Whether the function is set to echo or return.
		 */
		$args = apply_filters('luma_core_attachment_navigation_args', $args, $echo);

		$prev_text = self::wrap_content(esc_attr($args['class']), [esc_html($args['text']), '%title']);

		$html = get_the_post_navigation([
			'prev_text' => $prev_text,
		]);

		/**
		 * Filters the final attachment navigation markup.
		 *
		 * @param string $html The generated HTML markup.
		 * @param string $prev_text The previous link text
		 * @param array  $args The arguments used to build it.
		 * @param bool   $echo Whether the function is set to echo or return.
		 */
		$html = apply_filters('luma_core_attachment_navigation', $html, $prev_text, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}



	/**
	 * Outputs a "Full-size image attachment" link showing width × height for an attachment.
	 *
	 * Wraps the link in a <span> with a customizable CSS class. The text is built from
	 * `$before`, followed by dimensions (if available), and `$after`. Falls back gracefully
	 * if the attachment URL exists but metadata is missing.
	 *
	 * @param bool  $echo Whether to echo the HTML or return it.
	 * @param array $args {
	 *     Optional. Arguments to customize the link.
	 *
	 *     @type string $class  CSS class for the wrapper <span>. Default 'full-size-image-link'.
	 *     @type string $before Text inserted before the dimensions. Default 'View full size image'.
	 *     @type string $after  Text inserted after the dimensions. Default ''.
	 * }
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string|null The HTML output, or null if echoed.
	 */
	public static function attachment_full_size_link(bool $echo = true, array $args = []): ?string
	{
		self::set_domain();

		$defaults = [
			'class'  => 'full-size-image-link',
			'before' => __('View full size image', self::$domain),
			'after'  => '',
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filter the arguments used to generate the attachment full-size link.
		 *
		 * @param array $args  Parsed arguments with defaults merged.
		 * @param bool  $echo  Whether the function is set to echo or return.
		 */
		$args = apply_filters('luma_core_attachment_full_size_link_args', $args, $echo);

		$url = wp_get_attachment_url();
		if (!$url) {
			return $echo ? '' : null;
		}

		$metadata = wp_get_attachment_metadata() ?: [];
		$width    = ! empty($metadata['width'])  ? absint($metadata['width'])  : '';
		$height   = ! empty($metadata['height']) ? absint($metadata['height']) : '';

		// Build link text
		$link_text = $width && $height
			? sprintf('%s (%d × %d) %s', $args['before'], $width, $height, $args['after'])
			: trim($args['before'] . ' ' . $args['after']);

		$parts = [
			'<a href="' . esc_url($url) . '">',
			esc_html($link_text),
			'</a>',
		];

		$html = self::wrap_content($args['class'], $parts);

		/**
		 * Filter the full-size attachment link HTML.
		 *
		 * @param string $html       The generated HTML output.
		 * @param string $url        The attachment URL.
		 * @param array  $metadata   The attachment metadata array.
		 * @param string $link_text  The text used for the link, including dimensions.
		 * @param array  $args       The original parsed arguments.
		 * @param bool   $echo       Whether the function is set to echo the output.
		 */
		$html = apply_filters('luma_core_attachment_full_size_link', $html, $url, $metadata, $link_text, $args, $echo);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}
}
