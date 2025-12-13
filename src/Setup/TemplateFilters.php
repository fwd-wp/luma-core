<?php

namespace Luma\Core\Setup;

use Luma\core\Core\Config;
use Luma\Core\Helpers\Functions;
use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Services\ThemeSettingsSchema;

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

		// Custom logo handling
		add_filter('wp_generate_attachment_metadata', [$this, 'filter_attachment_metadata'], 10, 2);
		add_filter('get_custom_logo', [$this, 'filter_custom_logo']);
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

		$heights = [
			'desktop_1x' => 65,
			'desktop_2x' => 130,
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
	 * Output a responsive <picture> logo with desktop/mobile 1x/2x.
	 *
	 * @param string $html
	 * @param int    $blog_id
	 * @return string
	 */
	public function filter_custom_logo($html): string
	{
		$logo_id = get_theme_mod('custom_logo');

		if (! $logo_id) {
			// no error log, as not required to set a custom logo
			return $html;
		}

		$meta = wp_get_attachment_metadata($logo_id);
		if (!$meta) {
			Functions::error_log("Missing metadata for logo ID {$logo_id}");
			return $html;
		}

		$fallback = wp_get_attachment_image_url($logo_id, 'full');
		$full_width  = $meta['width'] ?? '';
		$full_height = $meta['height'] ?? '';
		$breakpoint_setting = wp_get_global_settings(['custom', 'breakpoint', 'navbar']);
		$breakpoint = is_array($breakpoint_setting) ? '800px' : $breakpoint_setting;
		$bp_int = (int) $breakpoint;
		$bp_max = ($bp_int - 1) . 'px';
		// category slug => breakpoint media query
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
				$display_width = $meta['sizes']["{$cat}_1x"]['width'] ?? 100;
				$sources[] = [
					'media'  => $media_query,
					'srcset' => implode(', ', $srcset_parts),
					'sizes'  => $display_width . 'px',
				];
			}
		}

		ob_start(); ?>
		<picture class="site-logo">
			<?php foreach ($sources as $source): ?>
				<source
					<?php if ($source['media']): ?>
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
