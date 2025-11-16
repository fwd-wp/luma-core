<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

?>
</main><!-- #main -->
</div><!-- #primary -->
</div><!-- #content -->


<div class="site-footer-container">
	<footer id="colophon" class="site-footer">
		<?php get_template_part('src/views/footer/footer-widgets'); ?>
	</footer><!-- #colophon -->
</div><!-- .site-footer-container -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>