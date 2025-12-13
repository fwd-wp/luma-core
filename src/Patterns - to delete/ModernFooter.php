<?php

namespace Luma\Core\Patterns;

use Luma\Core\Core\Config;

class ModernFooter
{

    public static function register(): void
    {
        register_block_pattern(
            'luma-core/modern-footer',
            [
                'title'      => __('Modern php', Config::get_domain()),
                'description'   => __('A custom pattern registered with PHP.', Config::get_domain()),
                'categories' => ['footer', 'luma'],
                'content'    => self::content(),
            ]
        );
    }

    protected static function content(): string
    {
        return <<<HTML
<!-- wp:group {"tagName":"footer","style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"},":hover":{"color":{"text":"var:preset|color|contrast"}}}}},"backgroundColor":"footerBackground","textColor":"heading","layout":{"type":"constrained"}} -->
<footer id="colophon" class="wp-block-group has-heading-color has-footer-background-background-color has-text-color has-background has-link-color"><!-- wp:columns {"verticalAlignment":"top","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-top"><!-- wp:column {"verticalAlignment":"top"} -->
<div class="wp-block-column is-vertically-aligned-top"><!-- wp:heading {"level":5} -->
<h5 class="wp-block-heading">Business Name</h5>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Street name</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Suburb</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top"} -->
<div class="wp-block-column is-vertically-aligned-top"><!-- wp:heading {"level":5} -->
<h5 class="wp-block-heading">Column heading</h5>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Column paragraph</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top"} -->
<div class="wp-block-column is-vertically-aligned-top"><!-- wp:heading {"level":5} -->
<h5 class="wp-block-heading">Column heading</h5>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Column paragraph</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top"} -->
<div class="wp-block-column is-vertically-aligned-top"><!-- wp:heading {"level":5} -->
<h5 class="wp-block-heading">Column heading</h5>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Column paragraph</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></footer>
<!-- /wp:group -->
HTML;
    }
}
