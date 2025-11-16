<?php

/**
 * The searchform.php template.
 *
 * Used any time that get_search_form() is called.
 *
 * @link https://developer.wordpress.org/reference/functions/wp_unique_id/
 * @link https://developer.wordpress.org/reference/functions/get_search_form/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

/*
 * Generate a unique ID for each form and a string containing an aria-label
 * if one was passed to get_search_form() in the args array.
 */
$twenty_one_unique_id = wp_unique_id('search-form-');

/**
 * Construct aria-label attribute safely for the form.
 * Use esc_attr() because it’s an HTML attribute, not content.
 */
// $twenty_one_aria_label = ! empty($args['aria_label'])
// 	? 'aria-label="' . esc_attr($args['aria_label']) . '"'
// 	: '';
?>

<form role="search"
	<?php if (! empty($args['aria_label'])) : ?>
	aria-label="<?php echo esc_attr($args['aria_label']); ?>"
	<?php endif; ?>
	method="get"
	class="search-form"
	action="<?php echo esc_url(home_url('/')); ?>">
	<label for="<?php echo esc_attr($twenty_one_unique_id); ?>">
		<span class="screen-reader-text"><?php esc_html_e('Search for:', 'twentyone'); ?></span>
	</label>
	<input type="search" id="<?php echo esc_attr($twenty_one_unique_id); ?>" class="search-field" placeholder="<?php esc_attr_e('Search …', 'twentyone'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	<button type="submit" class="search-submit"><?php esc_html_e('Search', 'twentyone'); ?></button>
</form>