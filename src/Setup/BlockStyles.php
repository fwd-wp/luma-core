<?php

namespace Twenty\One\Setup;

/**
 * Block Styles
 *
 * @link https://developer.wordpress.org/reference/functions/register_block_style/
 *
 * @package Luma-Core
 * @since Twenty Luma-Core 1.0
 */
class BlockStyles
{
	public function __invoke()
	{
		if (function_exists('register_block_style')) {
			add_action('init', [$this, 'register_block_styles']);
		}
	}

	/**
	 * Register block styles.
	 *
	 * @since Twenty Luma-Core 1.0
	 *
	 * @return void
	 */
	public function register_block_styles()
	{
		// Columns: Overlap (unique to TT1).
		register_block_style(
			'core/columns',
			[
				'name'  => 'Luma-Core-column-overlap',
				'label' => esc_html__('Overlap', 'luma-core'),
			]
		);

		// Generic Border style: register once, apply to multiple blocks.
		$border_blocks = [
			'core/cover',
			'core/group',
			'core/image',
			'core/media-text',
			'core/latest-posts',
			'core/query',
			'core/post-template',
			'core/post-featured-image',
			'core/post-terms',
			'core/navigation',
			'core/comments'
		];

		foreach ($border_blocks as $block) {
			register_block_style(
				$block,
				[
					'name'  => 'Luma-Core-border',
					'label' => esc_html__('Border', 'luma-core'),
				]
			);
		}

		// Image: Frame (still useful / unique).
		register_block_style(
			'core/image',
			[
				'name'  => 'Luma-Core-image-frame',
				'label' => esc_html__('Frame', 'luma-core'),
			]
		);

		// Latest Posts: Dividers (unique).
		register_block_style(
			'core/latest-posts',
			[
				'name'  => 'Luma-Core-latest-posts-dividers',
				'label' => esc_html__('Dividers', 'luma-core'),
			]
		);

		// Separator: Thick → Already shipped in Core, no need to re-register.
		// Social Links: Dark gray → Already shipped in Core, no need to re-register.
	}
}