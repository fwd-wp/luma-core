<?php

namespace Luma\Core\Helpers;

use Luma\Core\Core\Config;
use Luma\Core\Models\SVGIconsModel;
use Luma\Core\Services\ThemeSettingsSchema;

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */
class TemplateFunctions
{

	protected string $prefix;

	public function __construct(string $prefix = 'luma_core')
	{
		$this->prefix = $prefix;
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
	 * Print the first instance of a block in the content, and then break away.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string      $block_name The full block type name, or a partial match.
	 *                                Example: `core/image`, `core-embed/*`.
	 * @param string|null $content    The content to search in. Use null for get_the_content().
	 * @param int         $instances  How many instances of the block will be printed (max). Default  1.
	 * @return bool Returns true if a block was located & printed, otherwise false.
	 */
	public static function print_first_instance_of_block($block_name, $content = null, $instances = 1)
	{
		$instances_count = 0;
		$blocks_content  = '';

		if (! $content) {
			$content = get_the_content();
		}

		// Parse blocks in the content.
		$blocks = parse_blocks($content);

		// Loop blocks.
		foreach ($blocks as $block) {

			// Confidence check.
			if (! isset($block['blockName'])) {
				continue;
			}

			// Check if this the block matches the $block_name.
			$is_matching_block = false;

			// If the block ends with *, try to match the first portion.
			if ('*' === $block_name[-1]) {
				$is_matching_block = 0 === strpos($block['blockName'], rtrim($block_name, '*'));
			} else {
				$is_matching_block = $block_name === $block['blockName'];
			}

			if ($is_matching_block) {
				// Increment count.
				++$instances_count;

				// Add the block HTML.
				$blocks_content .= render_block($block);

				// Break the loop if the $instances count was reached.
				if ($instances_count >= $instances) {
					break;
				}
			}
		}

		if ($blocks_content) {
			/** This filter is documented in wp-includes/post-template.php */
			echo apply_filters('the_content', $blocks_content); // phpcs:ignore WordPress.Security.EscapeOutput
			return true;
		}

		return false;
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
	public static function generate_css($selector, $style, $value, $prefix = '', $suffix = '', $display = true)
	{

		// Bail early if there is no $selector elements or properties and $value.
		if (! $value || ! $selector) {
			return '';
		}

		$css = sprintf('%s { %s: %s; }', $selector, $style, $prefix . $value . $suffix);

		if ($display) {
			/*
		 * Note to reviewers: $css contains auto-generated CSS.
		 * It is included inside <style> tags and can only be interpreted as CSS on the browser.
		 * Using wp_strip_all_tags() here is sufficient escaping to avoid
		 * malicious attempts to close </style> and open a <script>.
		 */
			echo wp_strip_all_tags($css); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		return $css;
	}

	/**
	 * Calculates classes for the main <html> element.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public static function html_classes(): string
	{
		/**
		 * Filters the classes for the main <html> element.
		 *
		 * @since Luma-Core 1.0
		 *
		 * @param string The list of classes. Default empty string.
		 */
		$prefix = Config::get_prefix();
		$classes = apply_filters("{$prefix}_html_classes", '');
		if (! $classes) {
			return '';
		}
		return 'class="' . esc_attr($classes) . '"';

		// USAGE:
		// add_filter('luma_core_html_classes', function () {
		// 	return 'dark-mode custom-layout';
		// });
	}

	public static function body_classes(): array
	{
		$body_classes = [];
		$body_classes[] = TemplateFunctions::is_excerpt() ? ' is-excerpt' : ' is-full';
		if (is_single()) {
			$body_classes[] = ThemeSettingsSchema::theme_mod_with_default('display_post_width') === 'wide' ? ' is-wide-single' : '';
		}
		if (is_page()) {
			$body_classes[] = ThemeSettingsSchema::theme_mod_with_default('display_page_width') === 'wide' ? ' is-wide-page' : '';
		}
		return $body_classes;
	}

	public static function grid_classes(): string
	{
		$grid_classes  = 'archive-grid';
		$grid_classes .= ' archive-grid--' . ThemeSettingsSchema::theme_mod_with_default('display_archive_excerpt_format');

		return $grid_classes;
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
			__('Continue reading %s', Core::get_domain()),
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
		$is_excerpt = (ThemeSettingsSchema::theme_mod_with_default('display_archive_view') === 'excerpt');

		if (! $is_excerpt || is_single()) {
			return false;
		}

		$post_format = get_post_format();
		return !in_array($post_format, array('aside', 'status'), true);
	}
}
