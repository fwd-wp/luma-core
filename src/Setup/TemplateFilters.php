<?php

namespace Luma\Core\Setup;

use Luma\core\Core\Config;
use Luma\Core\Helpers\Functions;
use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Customize\ThemeSettingsSchema;

/**
 * Filter WP core Functions which enhance the theme by hooking into WordPress 
 * originally in template-functions.php
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

class TemplateFilters
{
	protected string $domain = 'luma-core';

	public function __invoke()
	{
		add_filter('body_class', array($this, 'filter_body_class'));
		add_filter('comment_form_defaults', array($this, 'filter_comment_form_defaults'));
		add_filter('excerpt_length', array($this, 'filter_excerpt_length'));
		add_filter('excerpt_more', array($this, 'filter_excerpt_more'));
		add_filter('the_content_more_link', array($this, 'filter_the_content_more_link'), 10, 2);
		add_filter('the_title', array($this, 'filter_the_title'));
		add_filter('get_calendar', array($this, 'filter_get_calendar'));
		add_filter('the_password_form', array($this, 'filter_password_form'), 10, 2);
		add_filter('wp_link_pages_args', [$this, 'filter_wp_link_pages_args']);
		add_filter('edit_post_link', [$this, 'filter_edit_post_link'], 10, 3);
		add_filter('get_header_image_tag', [$this, 'filter_header_image_tag'], 10, 3);


		// Custom logo handling
		add_filter('wp_generate_attachment_metadata', [$this, 'filter_attachment_metadata'], 10, 2);
		add_filter('get_custom_logo', [$this, 'filter_custom_logo'], 10, 2);
	}

	public function __construct()
	{
		$this->domain = Config::get_domain() ?? $this->domain;
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param array $classes Classes for the body element.
	 * @return array
	 */
	public function filter_body_class(array $classes): array
	{
		// Core logic
		if (is_archive() || is_home() || is_post_type_archive()) {
			if (TemplateFunctions::is_excerpt()) {
				$classes[] = 'is-excerpt';
				$classes[] = ThemeSettingsSchema::get_theme_mod('display_archive_excerpt_format') === 'masonry' ? 'is-masonry' : '';
				$classes[] = ThemeSettingsSchema::get_theme_mod('display_archive_excerpt_format') === 'grid' ? 'is-grid' : '';
			} else {
				$classes[] = 'is-full';
			}
		}

		if (is_search()) {
			$classes[] = 'is-archive';
		}

		if (is_page()) {
			$classes[] = ThemeSettingsSchema::get_theme_mod('display_page_width') === 'wide' ? 'is-wide' : '';
		}
		if (is_single()) {
			$classes[] = ThemeSettingsSchema::get_theme_mod('display_post_width') === 'wide' ? 'is-wide' : '';
		}

		// Theme-specific classes
		if (has_nav_menu('primary')) {
			$classes[] = 'has-main-navigation';
		}

		// Check all registered sidebars
		$sidebar_widgets = wp_get_sidebars_widgets();
		$has_widgets = false;
		foreach ($sidebar_widgets as $widget) {
			if (!empty($widget)) {
				$has_widgets = true;
				break;
			}
		}
		if (!$has_widgets) {
			$classes[] = 'no-widgets';
		} else {
			$classes[] = 'has-widgets';
		}

		// Clean up empty values
		$classes = array_filter($classes);

		return $classes;
	}


	/**
	 * Changes comment form default fields.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param array $defaults The form defaults.
	 * @return array
	 */
	public function filter_comment_form_defaults($defaults)
	{
		// Adjust height of comment form.
		$defaults['comment_field'] = str_replace(
			'rows="8"',
			'rows="5"',
			$defaults['comment_field']
		);

		return $defaults;
	}

	/**
	 * Customize the excerpt length
	 *
	 * @param int $length
	 * @return int
	 *
	 * @since Luma-Core 1.0
	 */
	public function filter_excerpt_length(int $length): int
	{
		return ThemeSettingsSchema::get_theme_mod('display_archive_excerpt_length');
	}

	/**
	 * Customize the excerpt "Read More" link.
	 *
	 * @param string $more Default excerpt more string '[...]'.
	 * @return string
	 *
	 * @since Luma-Core 1.0
	 */
	public function filter_excerpt_more(string $more): string
	{
		return '&hellip; <a class="more-link" href="' . esc_url(get_permalink()) . '">' . TemplateFunctions::continue_reading_text(false) . '</a>';
	}

	/**
	 * Customize the content "More" link for <!--more--> tags.
	 *
	 * @param string $link The HTML link generated.
	 * @param string $text Default link text.
	 * @return string
	 *
	 * @since Luma-Core 1.0
	 */
	public function filter_the_content_more_link(string $link, string $text): string
	{
		if (! is_admin()) {
			return str_replace($text, '<span class="more-link-container">' . TemplateFunctions::continue_reading_text(false) . '</span>', $link);
		} else {
			return $text;
		}
	}

	/**
	 * Adds a title to posts and pages that are missing titles.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $title The title.
	 * @return string
	 */
	public function filter_the_title($title): string
	{
		return '' === $title ? esc_html_x('Untitled', 'Added to posts and pages that are missing titles', $this->domain) : $title;
	}

	/**
	 * Changes the default navigation arrows to svg icons
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $calendar_output The generated HTML of the calendar.
	 * @return string
	 */
	public function filter_get_calendar($calendar_output)
	{
		$calendar_output = str_replace('&laquo; ', is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_right') : TemplateFunctions::get_icon_svg('ui', 'arrow_left'), $calendar_output);
		$calendar_output = str_replace(' &raquo;', is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_left') : TemplateFunctions::get_icon_svg('ui', 'arrow_right'), $calendar_output);
		return $calendar_output;
	}

	/**
	 * Retrieve protected post password form content.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string      $output The password form HTML output.
	 * @param int|WP_Post $post   Optional. Post ID or WP_Post object. Default is global $post.
	 * @return string HTML content for password form for password protected post.
	 */
	public function filter_password_form($output, $post = 0)
	{
		$post   = get_post($post);
		$label  = 'pwbox-' . (empty($post->ID) ? wp_rand() : $post->ID);
		$output = '<p class="post-password-message">' . esc_html__('This content is password protected. Please enter a password to view.', $this->domain) . '</p>
	<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" class="post-password-form" method="post">
	<label class="post-password-form__label" for="' . esc_attr($label) . '">' . esc_html_x('Password', 'Post password form', $this->domain) . '</label><input class="post-password-form__input" name="post_password" id="' . esc_attr($label) . '" type="password" spellcheck="false" size="20" /><input type="submit" class="post-password-form__submit" name="' . esc_attr_x('Submit', 'Post password form', $this->domain) . '" value="' . esc_attr_x('Enter', 'Post password form', $this->domain) . '" /></form>
	';
		return $output;
	}

	/**
	 * Modify the args for wp_link_pages().
	 */
	public function filter_wp_link_pages_args(array $args): array
	{
		$args['before']   = '<nav class="page-links" aria-label="' . esc_attr__('Page', $this->domain) . '">';
		$args['after']    = '</nav>';
		/* translators: %: Page number. */
		$args['pagelink'] = esc_html__('Page %', $this->domain);

		return $args;
	}

	/**
	 * Filter the full HTML of edit_post_link().
	 *
	 * @param string $link   The existing HTML.
	 * @param int    $post_id
	 * @param array  $args
	 *
	 * @return string
	 */
	public function filter_edit_post_link(string $link, int $post_id, string $text): string
	{

		$post = get_post($post_id);
		if (! $post) {
			return $link;
		}

		// Get singular label for current post type or default to 'post'
		$singular = TemplateFunctions::get_post_type_label('singular') ?? __('post');

		if ($post->post_type === 'attachment') {
			$singular = sprintf(__('attachment', $this->domain));
		}

		// Build custom link text: "Edit this Page"
		$new_text = sprintf(__('Edit this %s'), $singular);

		return str_replace(esc_html($text), esc_html($new_text), $link);
	}


	public function filter_header_image_tag($html, $header, $attr)
	{
		$site_name    = get_bloginfo('name');
		$description  = get_bloginfo('description', 'display');

		$text_color = get_header_textcolor();
		$text_style = '';
		if (!empty($text_color) && $text_color !== 'blank') {
			$text_style = ' style="color:' . esc_attr( '#' . $text_color) . '"';
		}

		// Only add if user wants to display title/tagline
		if (get_theme_mod('display_title_and_tagline', true) || is_customize_preview()) {

			// Build IDs for aria-labelledby
			$aria_ids = [];
			$aria_ids[] = 'wp-custom-header-title';
			if ($description) {
				$aria_ids[] = 'wp-custom-header-description';
			}

			// Add wrapper with aria-labelledby
			$html .= sprintf(
				'<div class="wp-custom-header-inner" aria-labelledby="%s">',
				esc_attr(implode(' ', $aria_ids))
			);

			// Site title
			$html .= sprintf(
				'<div id="wp-custom-header-title" class="wp-custom-header-title"%s>%s</div>',
				$text_style,
				esc_html($site_name)
			);

			// Site description (tagline)
			if ($description || is_customize_preview()) {
				$html .= sprintf(
					'<div id="wp-custom-header-description" class="wp-custom-header-description"%s>%s</div>',
					$text_style,
					esc_html($description)
				);
			}

			$html .= '</div>'; // close wp-custom-header-inner
		}

		return $html;
	}


	/**
	 * Generate custom logo sizes for desktop and mobile 1x/2x.
	 *
	 * @param array $metadata
	 * @param int   $attachment_id
	 * @return array
	 */
	public function filter_attachment_metadata($metadata, $attachment_id): array
	{
		$theme = wp_get_theme();
		$theme_mods = get_option("theme_mods_{$theme->get('TextDomain')}", []);
		$custom_logo_id = $theme_mods['custom_logo'] ?? 0;

		// Only generate sizes for the logo attachment
		if ($custom_logo_id && $attachment_id !== $custom_logo_id) {
			return $metadata;
		}

		$file = get_attached_file($attachment_id);
		$editor = wp_get_image_editor($file);

		if (is_wp_error($editor)) {
			Functions::error_log("Cannot load image editor for attachment ID {$attachment_id}");
			return $metadata;
		}

		$orig_size   = $editor->get_size();
		$orig_height = $orig_size['height'] ?? 0;

		$custom_logo_args = get_theme_support('custom-logo');

		if ($custom_logo_args) {
			$args = $custom_logo_args[0]; // WordPress wraps it in a nested array
			$logo_height = (int) $args['height'] ?? 130;
		}

		$heights = [
			// get desktop heights from the theme support args
			'desktop_1x' => (int) ($logo_height / 2),
			'desktop_2x' => $logo_height,
			// mobile is fixed
			'mobile_1x'  => 45,
			'mobile_2x'  => 90,
		];

		foreach ($heights as $key => $height) {
			$resize_height = min($height, $orig_height);

			$resized = wp_get_image_editor($file);
			if (is_wp_error($resized)) {
				Functions::error_log("Failed to instantiate editor for {$key}");
				continue;
			}

			$resized->resize(null, $resize_height);
			$dest  = $resized->generate_filename($key);
			$saved = $resized->save($dest);

			if (is_wp_error($saved)) {
				Functions::error_log("Failed to save resized image for {$key}");
				continue;
			}

			$metadata['sizes'][$key] = [
				'file'      => wp_basename($saved['path']),
				'width'     => $saved['width'],
				'height'    => $saved['height'],
				'mime-type' => $saved['mime-type'],
			];
		}

		return $metadata;
	}

	/**
	 * Replace the <img> in the original custom logo HTML with a responsive <picture>.
	 *
	 * @param string $html
	 * @param int    $blog_id
	 * @return string
	 */
	public static function filter_custom_logo(string $html, int $blog_id = 0): string
	{
		$logo_id = get_theme_mod('custom_logo', false, $blog_id);

		if (! $logo_id) {
			return $html; // no logo, keep original HTML
		}

		$meta = wp_get_attachment_metadata($logo_id);
		if (!$meta) {
			return $html; // metadata missing
		}

		$picture = self::generate_picture_tag($logo_id, $meta);

		// replace first <img> with <picture>
		$html = preg_replace('#<img.*?>#i', $picture, $html, 1);

		return $html;
	}

	/**
	 * Generate a responsive <picture> tag with retina sources.
	 *
	 * @param int   $logo_id
	 * @param array $meta Attachment metadata
	 * @return string
	 */
	public static function generate_picture_tag(int $logo_id, array $meta): string
	{
		$fallback = wp_get_attachment_image_url($logo_id, 'full');
		$full_width  = (int) ($meta['width'] ?? 0);
		$full_height = (int) ($meta['height'] ?? 0);

		$breakpoint = wp_get_global_settings(['custom', 'breakpoint', 'navbar']) ?? '800px';
		$bp_int     = (int) $breakpoint;
		$bp_max     = ($bp_int - 1) . 'px';

		$categories = [
			'mobile'  => "(max-width: {$bp_max})",
			'desktop' => "(min-width: {$breakpoint})",
		];

		$retina_factors = ['1x', '2x'];
		$sources = [];

		foreach ($categories as $cat => $media_query) {
			$srcset_parts = [];

			foreach ($retina_factors as $factor) {
				$size_key = "{$cat}_{$factor}";
				if (!empty($meta['sizes'][$size_key])) {
					$size_data = $meta['sizes'][$size_key];
					$url = wp_get_attachment_image_url($logo_id, $size_key);
					if ($url) {
						$srcset_parts[] = $url . ' ' . $size_data['width'] . 'w';
					}
				}
			}

			if ($srcset_parts) {
				$display_width = (int) ($meta['sizes']["{$cat}_1x"]['width'] ?? 100);
				$sources[] = [
					'media'  => $media_query,
					'srcset' => implode(', ', $srcset_parts),
					'sizes'  => $display_width . 'px',
				];
			}
		}

		ob_start(); ?>
		<picture class="custom-logo-picture">
			<?php foreach ($sources as $source): ?>
				<source
					<?php if (!empty($source['media'])): ?>
					media="<?php echo esc_attr($source['media']); ?>"
					<?php endif; ?>
					srcset="<?php echo esc_attr($source['srcset']); ?>"
					sizes="<?php echo esc_attr($source['sizes']); ?>">
			<?php endforeach; ?>

			<img
				src="<?php echo esc_url($fallback); ?>"
				width="<?php echo esc_attr($full_width); ?>"
				height="<?php echo esc_attr($full_height); ?>"
				alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
				class="custom-logo">
		</picture>
<?php
		return ob_get_clean();
	}
}
