<?php
$header_image = get_random_header_image();
if (! $header_image) {
    $header_image = get_header_image();
}
$name    = get_bloginfo('name');
$description  = get_bloginfo('description', 'display');
$show_title   = display_header_text();
$header_class = $show_title ? 'site-title site-title--custom-header' : 'screen-reader-text';
?>

<?php if ($header_image) : ?>
    <header class="custom-header-image" style="background-image: url('<?php echo esc_url($header_image); ?>');">
        <div class="custom-header-image-inner">
            <?php if ($name) : ?>
                <?php if (is_front_page()) : ?>
                    <h1 class="<?php echo esc_attr($header_class); ?>"><?php echo esc_html($name); ?></h1>
                <?php else: ?>
                    <p class="<?php echo esc_attr($header_class); ?>"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php echo esc_html($name); ?></a></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($description && $show_title) : ?>
                <p class="site-description site-description--custom-header">
                    <?php echo wp_kses_post($description); ?>
                </p>
            <?php endif; ?>
        </div><!-- .custom-header-image-inner -->
    </header><!-- .custom-header-image -->
<?php endif; ?>