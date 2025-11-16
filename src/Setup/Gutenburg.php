<?php

/**
 * Gutenberg editor settings for this theme.
 *
 * Restricts heading levels and can be extended for other editor customizations.
 *
 * @package Luma-Core
 * @since Luma-Core 1.0
 */

namespace Luma\Core\Setup;

/**
 * Class Gutenburg
 *
 * Handles Gutenberg block editor customizations for the theme.
 *
 * @package Luma-Core\Setup
 * @since Luma-Core 1.0
 */
class Gutenburg
{
	/**
	 * Invoke method to hook Gutenberg customizations.
	 *
	 * Hooks filters for Gutenberg blocks.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @return void
	 */
	public function __invoke(): void
	{
		add_filter('register_block_type_args', [$this, 'restrict_heading_levels'], 5, 2); // WP 5.8+
	}

	/**
	 * Restrict Gutenberg Heading block to H3–H6.
	 *
	 * This ensures that H1 and H2 are not selectable, 
	 * so headings in archive/blog views can maintain proper hierarchy.
	 *
	 * @since Luma-Core 1.0
	 *
	 * @param array  $args       Array of arguments for registering a block type.
	 * @param string $block_type The block type name including namespace (e.g., 'core/heading').
	 * @return array Modified array of block type arguments.
	 */
	public function restrict_heading_levels(array $args, string $block_type): array
	{
		if ('core/heading' !== $block_type) {
			return $args;
		}

		// Restrict H1 & H2, keep H3–H6, set default to H3
		$args['attributes']['levelOptions']['default'] = [3, 4, 5, 6];
		$args['attributes']['level']['default'] = 3;

		return $args;
	}
}
