<?php

/**
 * Template part for displaying post excerpts (on search, or archive if set)
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

use Twenty\One\Helpers\TemplateFunctions;
use Twenty\One\Helpers\TemplateTags;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('is-excerpt'); ?>>
    <?php TemplateTags::post_thumbnail(); ?>
    <div class="entry-body">
        <?php get_template_part('src/views/header/entry-header'); ?>
        <div class="entry-content">
            <?php get_template_part('src/views/excerpt/excerpt', get_post_format()); ?>
        </div>
        <footer class="entry-footer">
            <div class="read-more">
                <a href="<?php echo esc_url(get_permalink()); ?>">
                    <?php TemplateFunctions::continue_reading_text(); // phpcs:ignore WordPress.Security.EscapeOutput 
                    ?>
                </a>
            </div>
        </footer>
    </div>
</article><!-- #post-<?php the_ID(); ?> -->