<?php

/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Models\ThemeJSON;
use Twenty\One\Setup\{
	BackCompat,
	BlockPatterns,
	BlockStyles,
	Customize,
	Enqueue,
	Gutenburg,
	Setup,
	TemplateFilters,
};

use Twenty\One\Models\ThemeJSONnew;

require get_template_directory() . '/vendor/autoload.php';

// ---- Setup ----

(new BackCompat(6.8, 7.4))();

(new BlockPatterns())();

(new BlockStyles())();

// // Customizer additions.
(new Customize())();
// remove_theme_mods();

// // Enqueue scripts
(new Enqueue())();

// // Gutenburg editor settings
(new Gutenburg())();

// // Theme Setup (Theme supports and widgets)
(new Setup())();

// // Enhance the theme by hooking into WordPress.
(new TemplateFilters())();



add_action('after_setup_theme', function () {

// 	$themeJSON = new ThemeJSONnew;

// 	// $data = $themeJSON->load([])->raw();
// 	$data = $themeJSON->load(['settings', 'typography', 'fontFamilies'])->filter_by_slug('inter')->css_var();
// 	echo '<pre>';
// 	print_r($data);
// 	echo '</pre>';
});
