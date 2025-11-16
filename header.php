<?php

/**
 * The header.
 *
 * This is the template that displays all of the <head> section and everything up until main.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateFunctions;
use Twenty\One\Models\ThemeMod;

$is_excerpt = (ThemeMod::get('twenty_one_post_archive_display') === 'excerpt') || is_search();

$body_class = '';
$body_class .= $is_excerpt ? ' is-excerpt' : ' is-full';
if (is_single()) {
	$body_class .= ThemeMod::get('twenty_one_post_width') === 'wide' ? ' is-wide-single' : '';
}
if (is_page()) {
	$body_class .= ThemeMod::get('twenty_one_post_page_width') === 'wide' ? ' is-wide-page' : '';
}

?>
<!doctype html>
<html <?php language_attributes(); ?> <?php TemplateFunctions::the_html_classes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); ?>
</head>

<body <?php body_class($body_class); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">
			<?php
			/* translators: Hidden accessibility text. */
			esc_html_e('Skip to content', 'twentyone');
			?>
		</a>

		<?php get_template_part('src/views/header/site-header'); ?>

		<div id="content" class="site-content">
			<div id="primary" class="content-area">
				<main id="main" class="site-main">