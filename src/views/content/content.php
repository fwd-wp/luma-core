<?php

/**
 * Template part for displaying posts (full view, not excerpt)
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateTags;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('is-full'); ?>>
	<?php get_template_part('src/views/header/entry-header'); ?>
	<div class="entry-body">
		<?php TemplateTags::post_thumbnail(); ?>
		<div class="entry-content">
			<?php the_content(); ?>
			<?php TemplateTags::page_links(); ?>
		</div><!-- .entry-content -->
	</div><!-- .entry-body -->

	<footer class="entry-footer">
		<?php get_template_part('src/views/post/entry-meta-footer'); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->