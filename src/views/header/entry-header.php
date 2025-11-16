<?php

/**
 * Displays the post header
 * excerpt_micro (aside or status) - doesnt display
 *
 * @package Luma-Core
 *  
 * @since Luma-Core 1.0
 */

// dont show header on static front page
if (is_front_page() && !is_home()) return;

use Luma\Core\Helpers\TemplateFunctions;
use Luma\Core\Helpers\TemplateTags;
use Luma\Core\Models\ThemeModModel;

$is_excerpt = (ThemeMod::get('luma_core_post_archive_display') === 'excerpt');

if ($is_excerpt && TemplateFunctions::is_excerpt_micro_post()) {
    return;
}

$h = 2;
if (is_archive() || is_search()) {
    $h = 3;
}
?>

<header class="entry-header">
    <?php if ((is_archive() || is_home() || is_search()) && $is_excerpt): ?>
        <?php TemplateTags::archive_posted_meta(); ?>
    <?php endif; ?>
    <h<?php echo esc_html($h); ?> class="entry-title">
        <?php if ($is_excerpt && ! is_singular()) : ?>
            <a href="<?php echo esc_url(get_permalink()); ?>" rel="bookmark">
                <?php the_title(); ?>
            </a>
        <?php else : ?>
            <?php the_title(); ?>
        <?php endif; ?>
    </h<?php echo esc_html($h); ?>>

</header><!-- .entry-header -->