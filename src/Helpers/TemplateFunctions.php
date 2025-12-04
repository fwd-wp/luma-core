<?php

namespace Luma\Core\Helpers;

use Luma\Core\Models\SVGIconsModel;
use Luma\Core\Services\ThemeSettingsSchema;

/**
 * Functions which enhance the theme by hooking into WordPress
 * 
 *  Class must be initialised with TemplateFunctions::init($config) before use to set prefix and domain.
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */
class TemplateFunctions
{
	protected static string $domain = 'luma-core';

	public static function init($config): void
	{
		self::$domain = $config['text_domain'] ?? self::$domain;
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
	 * Get custom CSS.
	 *
	 * Return CSS for non-latin language, if available, or null
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $type Whether to return CSS for the "front-end", "block-editor", or "classic-editor".
	 * @return string
	 */
	public static function get_non_latin_css($type = 'front-end')
	{

		// Fetch site locale.
		$locale = get_bloginfo('language');

		/**
		 * Filters the fallback fonts for non-latin languages.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $font_family An array of locales and font families.
		 */
		$font_family = apply_filters(
			'get_localized_font_family_types',
			array(

				// Arabic.
				'ar'    => array('Tahoma', 'Arial', 'sans-serif'),
				'ary'   => array('Tahoma', 'Arial', 'sans-serif'),
				'azb'   => array('Tahoma', 'Arial', 'sans-serif'),
				'ckb'   => array('Tahoma', 'Arial', 'sans-serif'),
				'fa-IR' => array('Tahoma', 'Arial', 'sans-serif'),
				'haz'   => array('Tahoma', 'Arial', 'sans-serif'),
				'ps'    => array('Tahoma', 'Arial', 'sans-serif'),

				// Chinese Simplified (China) - Noto Sans SC.
				'zh-CN' => array('\'PingFang SC\'', '\'Helvetica Neue\'', '\'Microsoft YaHei New\'', '\'STHeiti Light\'', 'sans-serif'),

				// Chinese Traditional (Taiwan) - Noto Sans TC.
				'zh-TW' => array('\'PingFang TC\'', '\'Helvetica Neue\'', '\'Microsoft YaHei New\'', '\'STHeiti Light\'', 'sans-serif'),

				// Chinese (Hong Kong) - Noto Sans HK.
				'zh-HK' => array('\'PingFang HK\'', '\'Helvetica Neue\'', '\'Microsoft YaHei New\'', '\'STHeiti Light\'', 'sans-serif'),

				// Cyrillic.
				'bel'   => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'bg-BG' => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'kk'    => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'mk-MK' => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'mn'    => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'ru-RU' => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'sah'   => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'sr-RS' => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'tt-RU' => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),
				'uk'    => array('\'Helvetica Neue\'', 'Helvetica', '\'Segoe UI\'', 'Arial', 'sans-serif'),

				// Devanagari.
				'bn-BD' => array('Arial', 'sans-serif'),
				'hi-IN' => array('Arial', 'sans-serif'),
				'mr'    => array('Arial', 'sans-serif'),
				'ne-NP' => array('Arial', 'sans-serif'),

				// Greek.
				'el'    => array('\'Helvetica Neue\', Helvetica, Arial, sans-serif'),

				// Gujarati.
				'gu'    => array('Arial', 'sans-serif'),

				// Hebrew.
				'he-IL' => array('\'Arial Hebrew\'', 'Arial', 'sans-serif'),

				// Japanese.
				'ja'    => array('sans-serif'),

				// Korean.
				'ko-KR' => array('\'Apple SD Gothic Neo\'', '\'Malgun Gothic\'', '\'Nanum Gothic\'', 'Dotum', 'sans-serif'),

				// Thai.
				'th'    => array('\'Sukhumvit Set\'', '\'Helvetica Neue\'', 'Helvetica', 'Arial', 'sans-serif'),

				// Vietnamese.
				'vi'    => array('\'Libre Franklin\'', 'sans-serif'),

			)
		);

		// Return if the selected language has no fallback fonts.
		if (empty($font_family[$locale])) {
			return '';
		}

		/**
		 * Filters the elements to apply fallback fonts to.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param array $elements An array of elements for "front-end", "block-editor", or "classic-editor".
		 */
		$elements = apply_filters(
			'get_localized_font_family_elements',
			array(
				'front-end'      => array('body', 'input', 'textarea', 'button', '.button', '.faux-button', '.wp-block-button__link', '.wp-block-file__button', '.has-drop-cap:not(:focus)::first-letter', '.entry-content .wp-block-archives', '.entry-content .wp-block-categories', '.entry-content .wp-block-cover-image', '.entry-content .wp-block-latest-comments', '.entry-content .wp-block-latest-posts', '.entry-content .wp-block-pullquote', '.entry-content .wp-block-quote.is-large', '.entry-content .wp-block-quote.is-style-large', '.entry-content .wp-block-archives *', '.entry-content .wp-block-categories *', '.entry-content .wp-block-latest-posts *', '.entry-content .wp-block-latest-comments *', '.entry-content p', '.entry-content ol', '.entry-content ul', '.entry-content dl', '.entry-content dt', '.entry-content cite', '.entry-content figcaption', '.entry-content .wp-caption-text', '.comment-content p', '.comment-content ol', '.comment-content ul', '.comment-content dl', '.comment-content dt', '.comment-content cite', '.comment-content figcaption', '.comment-content .wp-caption-text', '.widget_text p', '.widget_text ol', '.widget_text ul', '.widget_text dl', '.widget_text dt', '.widget-content .rssSummary', '.widget-content cite', '.widget-content figcaption', '.widget-content .wp-caption-text'),
				'block-editor'   => array('.editor-styles-wrapper > *', '.editor-styles-wrapper p', '.editor-styles-wrapper ol', '.editor-styles-wrapper ul', '.editor-styles-wrapper dl', '.editor-styles-wrapper dt', '.editor-post-title__block .editor-post-title__input', '.editor-styles-wrapper .wp-block h1', '.editor-styles-wrapper .wp-block h2', '.editor-styles-wrapper .wp-block h3', '.editor-styles-wrapper .wp-block h4', '.editor-styles-wrapper .wp-block h5', '.editor-styles-wrapper .wp-block h6', '.editor-styles-wrapper .has-drop-cap:not(:focus)::first-letter', '.editor-styles-wrapper cite', '.editor-styles-wrapper figcaption', '.editor-styles-wrapper .wp-caption-text'),
				'classic-editor' => array('body#tinymce.wp-editor', 'body#tinymce.wp-editor p', 'body#tinymce.wp-editor ol', 'body#tinymce.wp-editor ul', 'body#tinymce.wp-editor dl', 'body#tinymce.wp-editor dt', 'body#tinymce.wp-editor figcaption', 'body#tinymce.wp-editor .wp-caption-text', 'body#tinymce.wp-editor .wp-caption-dd', 'body#tinymce.wp-editor cite', 'body#tinymce.wp-editor table'),
			)
		);

		// Return if the specified type doesn't exist.
		if (empty($elements[$type])) {
			return '';
		}

		// Include file if function doesn't exist.
		// if (! function_exists('generate_css')) {
		// 	require_once get_theme_file_path('inc/custom-css.php'); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
		// }

		// Return the specified styles.
		return self::generate_css( // @phpstan-ignore-line.
			implode(',', $elements[$type]),
			'font-family',
			implode(',', $font_family[$locale]),
			null,
			null,
			false
		);
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
	 * Generate CSS.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $selector The CSS selector.
	 * @param string $style    The CSS style.
	 * @param string $value    The CSS value.
	 * @param string $prefix   The CSS prefix.
	 * @param string $suffix   The CSS suffix.
	 * @param bool   $display  Print the styles.
	 * @return string
	 */
	public static function generate_css($selector, $style, $value, $prefix = '', $suffix = '', $echo = false)
	{

		// Bail early if there is no $selector elements or properties and $value.
		if (! $value || ! $selector) {
			return '';
		}

		$css = sprintf('%s { %s: %s; }', $selector, $style, $prefix . $value . $suffix);

		if ($echo) {
			/*
		 * Note to reviewers: $css contains auto-generated CSS.
		 * It is included inside <style> tags and can only be interpreted as CSS on the browser.
		 * Using wp_strip_all_tags() here is sufficient escaping to avoid
		 * malicious attempts to close </style> and open a <script>.
		 */
			echo wp_strip_all_tags($css); // phpcs:ignore WordPress.Security.EscapeOutput
			return null;
		}
		return $css;
	}

	/**
	 * Calculates classes for the main <html> element.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string
	 */
	public static function html_class(): string
	{
		/**
		 * Filter the classes for the main <html> element.
		 *
		 * Multiple callbacks can append to the existing class string.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $classes Current list of classes. Default empty string.
		 */

		$classes = apply_filters('luma_core_html_class', '');
		if (! $classes) {
			return '';
		}
		return 'class="' . esc_attr($classes) . '"';

		/* USAGE:
		add_filter('luma_core_html_classes', function($classes) {
    		return trim($classes . ' dark-mode custom-layout');
		});
		*/
	}

	/**
	 * Calculates classes for the archive grid container.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string CSS classes for the archive grid container.
	 */
	public static function grid_classes(string $class = ''): string
	{
		$classes = $class;
		$classes .= 'archive-grid';
		$classes .= ' archive-grid--' . ThemeSettingsSchema::get_theme_mod('display_archive_excerpt_format');

		/**
		 * Filter the archive grid container classes.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $grid_classes The CSS classes for the archive grid container.
		 */
		return apply_filters('luma_core_grid_classes', $classes);
	}

	/**
	 * Calculates classes for the site header container.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string CSS classes for the site header.
	 */
	public static function header_class(string $class = ''): string
	{
		$classes = $class;
		$classes .= 'site-header';
		$classes .= ThemeSettingsSchema::get_theme_mod('header_sticky') ? ' is-sticky' : '';
		$classes .= ThemeSettingsSchema::get_theme_mod('header_navbar_transparent') ? ' is-transparent' : '';

		/**
		 * Filter the site header CSS classes.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $classes The CSS classes for the site header.
		 */
		$classes = apply_filters('luma_core_header_classes', $classes);
		return 'class="' . esc_attr($classes) . '"';
	}

	/**
	 * Calculates classes for the main site navigation container.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return string CSS classes for the site navigation container.
	 */
	public static function nav_class(string $class = ''): string
	{
		$classes = $class;
		$classes .= 'site-navigation';
		$classes .= has_custom_logo() ? ' has-logo' : '';
		$classes .= has_nav_menu('main') ? ' has-menu' : '';

		if (ThemeSettingsSchema::get_theme_mod('wp-core_display_title_and_tagline')) {
			$classes .= get_bloginfo('name') ? ' has-title' : '';
			$classes .= get_bloginfo('description') ? ' has-description' : '';
		}

		$classes .= ThemeSettingsSchema::get_theme_mod('header_navbar_full_width') ? ' is-full-width' : '';
		$classes .= ThemeSettingsSchema::get_theme_mod('header_navbar_shrink') ? ' is-sticky is-shrink-enabled' : '';

		/**
		 * Filter the site navigation CSS classes.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string $classes The CSS classes for the site navigation container.
		 */
		$classes = apply_filters('luma_core_nav_class', $classes);
		return 'class="' . esc_attr($classes) . '"';
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
	 * Creates continue reading text.
	 *
	 * @since Luma-Core 1.0
	 */
	public static function continue_reading_text($echo = true)
	{
		$continue_reading = sprintf(
			/* translators: %s: Post title. Only visible to screen readers. */
			__('Continue reading %s', self::$domain),
			get_the_title('<span class="screen-reader-text">', '</span>')
		);

		$continue_reading = apply_filters('luma_core_continue_reading_text', $continue_reading);

		if ($echo) {
			echo esc_html($continue_reading);
		}

		return $continue_reading;
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
	public static function is_excerpt_micro_post(): bool
	{
		if (! self::is_excerpt()) {
			return false;
		}

		$post_format = get_post_format();
		return in_array($post_format, array('aside', 'status'), true);
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
		$is_excerpt = (ThemeSettingsSchema::get_theme_mod('display_archive_view') === 'excerpt');

		if (! $is_excerpt || is_single()) {
			return false;
		}

		$post_format = get_post_format();
		return !in_array($post_format, array('aside', 'status'), true);
	}
}
