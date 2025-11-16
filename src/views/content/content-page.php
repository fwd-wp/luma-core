<?php

/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

use Luma\Core\Helpers\TemplateTags;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php get_template_part('src/views/header/entry-header'); ?>
	<div class="entry-body">
		<?php TemplateTags::post_thumbnail(); ?>
		<div class="entry-content">
			<?php the_content(); ?>
			<?php TemplateTags::page_links(); ?>
		</div><!-- .entry-content -->
	</div><!-- .entry-body -->

	<?php if (get_edit_post_link()) : ?>
		<footer class="entry-footer">
			<?php TemplateTags::edit_post_link(); ?>
		</footer><!-- .entry-footer -->
	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->