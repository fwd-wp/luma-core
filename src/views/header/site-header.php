<?php

/**
 * Displays the site header.
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Models\ThemeMod;

$header_classes = 'site-header';
$header_classes .= ThemeMod::get('twenty_one_header_sticky') ? ' is-sticky' : '';
$header_classes .= ThemeMod::get('twenty_one_header_transparent') ? ' is-transparent' : '';
?>

<header id="masthead" class="<?php echo esc_attr($header_classes); ?>">
	<?php get_template_part('src/views/header/site-nav'); ?>
	<?php get_template_part('src/views/header/site-custom-header-image'); ?>
</header><!-- #masthead -->