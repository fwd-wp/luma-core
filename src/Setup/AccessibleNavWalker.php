<?php

namespace Luma\Core\Setup;

/**
 * Accessible navigation walker for WordPress menus.
 *
 * Adds accessible toggle buttons for submenus and outputs descriptions for menu items.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */
class AccessibleNavWalker extends \Walker_Nav_Menu
{

    protected $submenu_ids = [];

    /**
     * Starts the list before the elements are added.
     *
     * @param string $output Used to append additional content (passed by reference).
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   Additional arguments.
     */
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $submenu_id = isset($this->submenu_ids[$depth]) ? $this->submenu_ids[$depth] : '';
        $output .= $indent . "\t<ul class='sub-menu' id='" . esc_attr($submenu_id) . "'>\n";
    }

    /**
     * Starts the element output.
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param \WP_Post $item   Menu item object.
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param array    $args   Additional arguments.
     * @param int      $id     Menu item ID.
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $class_names = implode(' ', array_map('sanitize_html_class', $classes));
        $indent = str_repeat("\t", $depth);
        $output .= $indent . '<li id="menu-item-' . $item->ID . '" class="' . esc_attr($class_names) . "\">\n";

        $has_children = in_array('menu-item-has-children', $classes, true);

        if ($has_children) {
            // Unique ID for submenu
            $submenu_id = 'submenu-' . $item->ID;
            // store ID per depth
            $this->submenu_ids[$depth] = $submenu_id;

            // Button wraps menu text
            $output .= $indent . "\t" . '<button class="submenu-toggle" aria-expanded="false" aria-controls="' . esc_attr($submenu_id) . '">';
            $output .= esc_html($item->title);
            $output .= '<span class="screen-reader-text">Toggle submenu</span>';
            $output .= "</button>\n";
        } else {

            // Conditionally add attributes and items
            $is_current   = in_array('current-menu-item', $classes, true) ? ' aria-current="page"' : '';
            $title_attr = ! empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
            $target_attr = ! empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
            $rel_attr = ! empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
            $description = ! empty($item->description) ? '<span class="menu-item-description">' . esc_html($item->description) . '</span>' : '';

            // Normal link
            $output .= $indent . '<a href="' . esc_url($item->url) . '"' . $title_attr . $target_attr . $rel_attr . $is_current . '>';
            $output .= esc_html($item->title) . $description;
            $output .= "</a>\n";
        }
    }



    /**
     * Ends the element output, if needed.
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param \WP_Post $item   Page data object. Not used.
     * @param int      $depth  Depth of menu item. Not used.
     * @param array    $args   Additional arguments.
     */
    public function end_el(&$output, $item, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= $indent . "</li>\n";
    }

    /**
     * Ends the list of after the elements are added.
     *
     * @param string $output Used to append additional content (passed by reference).
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   Additional arguments.
     */
    public function end_lvl(&$output, $depth = 0, $args = array())
    {
        $indent = str_repeat("\t", $depth);
        $output .= $indent . "</ul>\n";
    }
}
