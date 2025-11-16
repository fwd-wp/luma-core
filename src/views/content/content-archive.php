<?php

use Luma\Core\Models\ThemeModModel;

// search is always excerpt
$is_excerpt = (ThemeMod::get('luma_core_post_archive_display') === 'excerpt') || is_search();

$grid_class  = 'archive-grid';
$grid_class .= ' archive-grid--' . ThemeMod::get('luma_core_post__archive_format');
?>

<section class="<?php echo esc_attr($grid_class); ?>">
    <?php while (have_posts()) :  the_post();
        if ($is_excerpt):
            get_template_part('src/views/content/content-excerpt');
        else:
            get_template_part('src/views/content/content');
        endif;
    endwhile; ?>
</section>