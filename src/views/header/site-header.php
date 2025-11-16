<?php

/**
 * Displays the site header.
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

use Luma\Core\Models\ThemeModModel;

$header_classes = 'site-header';
$header_classes .= ThemeMod::get('luma_core_header_sticky') ? ' is-sticky' : '';
$header_classes .= ThemeMod::get('luma_core_header_transparent') ? ' is-transparent' : '';
?>

<header id="masthead" class="<?php echo esc_attr($header_classes); ?>">
	<?php get_template_part('src/views/header/site-nav'); ?>
	<?php get_template_part('src/views/header/site-custom-header-image'); ?>
</header><!-- #masthead -->