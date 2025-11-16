<?php

/**
 * Displays header site branding
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Models\ThemeMod;

$name    = get_bloginfo('name');
$description  = get_bloginfo('description', 'display');
$show_title   = ThemeMod::get('twenty_one_display_title_and_tagline');
$header_class = $show_title ? 'site-title' : 'screen-reader-text';
?>

<div class="site-branding">
	<?php if (has_custom_logo()): ?>
		<?php the_custom_logo(); ?>
	<?php endif; ?>
	<?php if ($name) : ?>
		<?php if (is_front_page()) : ?>
			<h1 class="<?php echo esc_attr($header_class); ?>"><?php echo esc_html($name); ?></h1>
		<?php else: ?>
			<h1 class="<?php echo esc_attr($header_class); ?>"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php echo esc_html($name); ?></a></h1>
		<?php endif; ?>
	<?php endif; ?>
</div><!-- .site-branding -->