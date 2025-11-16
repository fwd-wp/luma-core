<?php

/**
 * Displays the site navigation.
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Models\ThemeModModel;
use Luma\Core\Setup\AccessibleNavWalker;

$nav_classes  = 'site-navigation';
$nav_classes .= has_custom_logo() ? ' has-logo' : '';
$nav_classes .= has_nav_menu('main') ? ' has-menu' : '';
if (ThemeMod::get('luma_core_display_title_and_tagline')) {
	$nav_classes .= get_bloginfo('name') ? 'has-title' : '';
	$nav_classes .= get_bloginfo('description') ? ' has-description' : '';
}

// $nav_classes .= ThemeMod::get('luma_core_display_title_and_tagline') ? ' has-title-and-tagline' : '';
$nav_classes .= ThemeMod::get('luma_core_header_nav_full') ? ' is-full-width' : '';
$nav_classes .= ThemeMod::get('luma_core_header_shrink') ? ' is sticky is-shrink-enabled' : '';
?>

<nav id="site-navigation" class="<?php echo esc_attr($nav_classes); ?>" aria-label="<?php esc_attr_e('Main menu', 'luma-core'); ?>">
	<?php get_template_part('src/views/header/site-branding'); ?>
	<?php if (has_nav_menu('main')) : ?>
		<button id="btn-menu-toggle" class="menu-toggle" aria-expanded="false" aria-controls="menu-main">
			<?php echo TemplateFunctions::get_icon_svg('ui', 'menu', 35); ?>
			<span class="screen-reader-text"><?php esc_html_e('Open/close Main Menu', 'luma-core'); ?></span>
		</button>
		<?php
		wp_nav_menu(
			array(
				'theme_location'  => 'main',
				'container'		  => false,
				'menu_class'      => 'menu',
				'menu_id'			=> 'menu-main',
				'fallback_cb'     => false,
				'walker'         => new AccessibleNavWalker(),
			)
		);
		?>
	<?php endif; ?>
	<?php get_template_part('src/views/header/site-search'); ?>
</nav><!-- #site-navigation -->