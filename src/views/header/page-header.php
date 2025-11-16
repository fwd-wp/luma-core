<?php

/**
 * Displays the page header
 *
 * @package Luma-Core
 *  
 * @since Twenty Luma-Core 1.0
 */

// page header is only used on archive pages, and index.html if its a blog page

?>
<?php if (is_archive() || (is_home() && ! is_front_page() && ! empty(single_post_title('', false)))) : ?>
    <header class="page-header">
        <?php if (is_archive()): ?>
            <?php the_archive_title('<h2 class="page-title">', '</h2>'); ?>
            <?php if ($description = get_the_archive_description()) : ?>
                <div class="archive-description"><?php echo wp_kses_post(wpautop($description)); ?></div>
            <?php endif; ?>
        <?php elseif (is_home() && ! is_front_page() && ! empty(single_post_title('', false))): ?>
            <h2 class="page-title"><?php single_post_title(); ?></h2>
        <?php endif; ?>
    </header><!-- .page-header -->
<?php endif; ?>