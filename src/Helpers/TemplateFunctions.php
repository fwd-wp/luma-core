<?php

namespace Luma\Core\Helpers;

use Luma\Core\Core\Config;
use Luma\Core\Models\SVGIconsModel;
use Luma\Core\Customize\ThemeSettingsSchema;

/**
 * Functions which enhance the theme by hooking into WordPress
 * 
 *  Class must be initialised with TemplateFunctions::init($config) before use to set domain.
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */
class TemplateFunctions
{
	// stores theme variant domain, self::set_domain() needs to be run first if domain is needed in a method
	private static string $domain;

	private static function set_domain(): void
	{
		if (!isset(self::$domain)) {
			// checks and sets the prefix
			self::$domain = Config::get_domain() ?? 'luma-core';
		}
	}

	/**
	 * Gets the SVG code for a given icon.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $group The icon group.
	 * @param string $icon  The icon.
	 * @param int    $size  The icon size in pixels.
	 * @return string
	 */
	public static function get_icon_svg($group, $icon, $size = 24)
	{
		return SVGIconsModel::get_svg($group, $icon, $size);
	}

	/**
	 * Get the first instance(s) of a block in the content.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string      $block_name The full block type name, or a partial match with '*'.
	 *                                Example: 'core/image', 'core-embed/*'.
	 * @param string|null $content    The content to search in. Defaults to get_the_content().
	 * @param int         $instances  How many instances of the block to return. Default 1.
	 * @param bool        $echo       Whether to echo the output or return it. Default true.
	 *
	 * @return string|bool Returns the HTML of the block(s) if $echo is false, otherwise true/false.
	 */
	public static function get_first_instance_of_block(string $block_name, ?string $content = null, int $instances = 1, bool $echo = false)
	{
		$instances_count = 0;
		$blocks_content  = '';

		if (! $content) {
			$content = get_the_content();
		}

		/**
		 * Recursive function to search blocks, including nested blocks.
		 */
		$search_blocks = function (array $blocks) use (&$search_blocks, $block_name, $instances, &$instances_count, &$blocks_content) {
			foreach ($blocks as $block) {
				if (! isset($block['blockName'])) {
					continue;
				}

				$is_matching_block = false;

				if ('*' === substr($block_name, -1)) {
					$is_matching_block = 0 === strpos($block['blockName'], rtrim($block_name, '*'));
				} else {
					$is_matching_block = $block_name === $block['blockName'];
				}

				if ($is_matching_block) {
					++$instances_count;
					$blocks_content .= render_block($block);

					if ($instances_count >= $instances) {
						return true; // stop search
					}
				}

				// Recursively check inner blocks
				if (! empty($block['innerBlocks'])) {
					if ($search_blocks($block['innerBlocks'])) {
						return true;
					}
				}
			}

			return false;
		};

		$blocks = parse_blocks($content);
		$search_blocks($blocks);

		if ($blocks_content) {
			$blocks_content = apply_filters('luma_core_first_instance_of_block', $blocks_content, $block_name, $instances);

			if ($echo) {
				echo $blocks_content; // phpcs:ignore WordPress.Security.EscapeOutput
				return true;
			}

			return $blocks_content;
		}

		return false;
	}

	/**
	 * Get or print the first matching block from a list of block types.
	 *
	 * @param string[]    $block_types Array of block names in priority order.
	 * @param string|null $content     Content to search. Defaults to get_the_content().
	 * @param int         $instances   How many instances to get per block type. Default 1.
	 * @param bool        $echo        Whether to echo (true) or return (false). Default true.
	 *
	 * @return string|bool HTML output if $echo is false, otherwise true if printed, false if nothing found.
	 */
	public static function get_first_available_block(array $block_types, ?string $content = null, int $instances = 1, bool $echo = false)
	{
		foreach ($block_types as $block_type) {
			$html = self::get_first_instance_of_block($block_type, $content, $instances, false);
			if ($html) {
				if ($echo) {
					echo $html;
					return true;
				}
				return $html;
			}
		}

		return $echo ? false : '';
	}

	/**
	 * Detects the social network from a URL and returns the SVG code for its icon.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $uri  Social link.
	 * @param int    $size The icon size in pixels.
	 * @return string
	 */
	public static function get_social_link_svg($uri, $size = 24)
	{
		return SVGIconsModel::get_social_link_svg($uri, $size);
	}

	/**
	 * Determines if post thumbnail can be displayed.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return bool
	 */
	public static function can_show_post_thumbnail()
	{
		/**
		 * Filters whether post thumbnail can be displayed.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param bool $show_post_thumbnail Whether to show post thumbnail.
		 */
		return apply_filters(
			'can_show_post_thumbnail',
			! post_password_required() && ! is_attachment() && has_post_thumbnail()
		);
	}

	/**
	 * Generates the "Continue reading" text used in excerpts and teaser content.
	 *
	 * Includes the post title inside a screen-reader-only <span> for accessibility.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param bool  $echo Whether to echo the output. If false, the HTML is returned.
	 * @param array $args {
	 *     Optional. Arguments controlling the output.
	 *
	 *     @type string $label The visible text before the screen reader title.
	 *                         Default 'Continue reading'.
	 * }
	 *
	 * @return string|null The formatted HTML when `$echo` is false, otherwise null.
	 */
	public static function continue_reading_text($echo = true, array $args = []): ?string
	{
		self::set_domain();

		$defaults = [
			'label' => __('Continue reading', self::$domain),
		];

		$args = wp_parse_args($args, $defaults);

		/**
		 * Filters the arguments used to generate the continue reading text.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $args Parsed arguments after merging defaults.
		 * @param bool  $echo Whether the output will be echoed.
		 */
		$args = apply_filters('luma_core_continue_reading_text_args', $args, $echo);

		$title = get_the_title();
		if (empty($title)) {
			$title = __('this post', self::$domain);
		}

		$screen_reader_title = '<span class="screen-reader-text">' . esc_html($title) . '</span>';

		$html = $args['label'] . $screen_reader_title;

		/**
		 * Filters the full HTML output of the continue reading text.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $html                 Generated HTML output.
		 * @param string $screen_reader_title  Screen-reader-only post title.
		 * @param array  $args                 Arguments used to generate the output.
		 */
		$html = apply_filters('luma_core_continue_reading_text', $html, $screen_reader_title, $args);

		$html = wp_kses_post($html);

		if ($echo) {
			echo $html;
			return null;
		}

		return $html;
	}


	/**
	 * Determine if current context is excerpt view for micro post formats.
	 * Micro formats: aside, status.
	 * Only true on non-single views when theme is set to display excerpts.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return bool
	 */
	public static function is_micro_post(): bool
	{
		if (! self::is_excerpt()) {
			return false;
		}

		return in_array(get_post_format(), array('aside', 'status'), true);
	}

	/**
	 * Determine if current context is excerpt view 
	 * Only true on non-single views when theme is set to display excerpts.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return bool
	 */
	public static function is_excerpt(): bool
	{
		// Check if theme mod is set to 'excerpt'
		if (ThemeSettingsSchema::get_theme_mod('display_archive_view') !== 'excerpt') {
			return false;
		}

		if (is_404()) {
			return false; // explicitly exclude 404
		}

		// Return true only for archive-like views
		if (is_archive() || is_home() || is_search() || is_post_type_archive()) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the singular or plural label of the current post type.
	 *
	 * @param string $type   'singular' or 'plural'. Default 'plural'.
	 * @param string $default Optional fallback if labels are unavailable otherwise returns ''.
	 *
	 * @return string Content is not escape must be escape at output
	 */
	public static function get_post_type_label(string $type = 'plural', string $default = ''): string
	{
		$post_type_object = get_post_type_object(get_post_type());

		if (!$post_type_object) {
			if ($default !== '') {
				return $default;
			}
			return '';
		}

		return $type === 'singular'
			? ($post_type_object->labels->singular_name ?? '')
			: ($post_type_object->labels->name ?? '');
	}

	public static function is_list_view(): bool
	{
		if ((is_archive() || (is_home() && ! is_front_page()))) {
			return true;
		}
		return false;
	}

	public static function can_display_post_author_bio(): bool
	{
		return ThemeSettingsSchema::get_theme_mod('display_post_author_bio');
	}
}
