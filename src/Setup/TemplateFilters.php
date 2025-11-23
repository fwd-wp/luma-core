<?php

namespace Luma\Core\Setup;

use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Services\I18nService;

/**
 * Filter Functions which enhance the theme by hooking into WordPress
 * originally in template-functions.php
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

class TemplateFilters
{

	public function __invoke()
	{
		add_filter('body_class', array($this, 'body_classes'));
		add_filter('comment_form_defaults', array($this, 'comment_form_defaults'));
		add_filter('excerpt_length', array($this, 'excerpt_length'));
		add_filter('excerpt_more', array($this, 'continue_reading_link_excerpt'));
		add_filter('the_content_more_link', array($this, 'continue_reading_link'), 10, 2);
		add_filter('the_title', array($this, 'post_title'));
		add_filter('get_calendar', array($this, 'change_calendar_nav_arrows'));
		add_filter('the_password_form', array($this, 'password_form'), 10, 2);
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param array $classes Classes for the body element.
	 * @return array
	 */
	public function body_classes($classes)
	{

		// Add a body class if main navigation is active.
		if (has_nav_menu('primary')) {
			$classes[] = 'has-main-navigation';
		}

		// Add a body class if there are no footer widgets.
		if (! is_active_sidebar('sidebar-1')) {
			$classes[] = 'no-widgets';
		}

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
	public function comment_form_defaults($defaults)
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
	public function excerpt_length(int $length): int
	{
		return 25;
	}

	/**
	 * Customize the excerpt "Read More" link.
	 *
	 * @param string $more Default excerpt more string '[...]'.
	 * @return string
	 *
	 * @since Luma-Core 1.0
	 */
	public function continue_reading_link_excerpt(string $more): string
	{
		// if (! is_admin()) {
		// 	return '&hellip; <a class="more-link" href="' . esc_url(get_permalink()) . '">' . TemplateFunctions::continue_reading_text(false) . '</a>';
		// } else {
		// 	return $more;
		// } 
		return '&hellip;';
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
	public function continue_reading_link(string $link, string $text): string
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
	public function post_title($title): string
	{
		return '' === $title ? esc_html_x('Untitled', 'Added to posts and pages that are missing titles', I18nService::get_domain()) : $title;
	}

	/**
	 * Changes the default navigation arrows to svg icons
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param string $calendar_output The generated HTML of the calendar.
	 * @return string
	 */
	public function change_calendar_nav_arrows($calendar_output)
	{
		$calendar_output = str_replace('&laquo; ', is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_right') : TemplateFunctions::get_icon_svg('ui', 'arrow_left'), $calendar_output);
		$calendar_output = str_replace(' &raquo;', is_rtl() ? TemplateFunctions::get_icon_svg('ui', 'arrow_left') : TemplateFunctions::get_icon_svg('ui', 'arrow_right'), $calendar_output);
		return $calendar_output;
	}

	/**
	 * Retrieve protected post password form content.
	 *
	 * @since Luma-Core 1.0
	 * @since Luma-Core 1.4 Corrected parameter name for `$output`,
	 *                              added the `$post` parameter.
	 *
	 * @param string      $output The password form HTML output.
	 * @param int|WP_Post $post   Optional. Post ID or WP_Post object. Default is global $post.
	 * @return string HTML content for password form for password protected post.
	 */
	public function password_form($output, $post = 0)
	{
		$post   = get_post($post);
		$label  = 'pwbox-' . (empty($post->ID) ? wp_rand() : $post->ID);
		$output = '<p class="post-password-message">' . esc_html__('This content is password protected. Please enter a password to view.', I18nService::get_domain()) . '</p>
	<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" class="post-password-form" method="post">
	<label class="post-password-form__label" for="' . esc_attr($label) . '">' . esc_html_x('Password', 'Post password form', I18nService::get_domain()) . '</label><input class="post-password-form__input" name="post_password" id="' . esc_attr($label) . '" type="password" spellcheck="false" size="20" /><input type="submit" class="post-password-form__submit" name="' . esc_attr_x('Submit', 'Post password form', I18nService::get_domain()) . '" value="' . esc_attr_x('Enter', 'Post password form', I18nService::get_domain()) . '" /></form>
	';
		return $output;
	}
}
