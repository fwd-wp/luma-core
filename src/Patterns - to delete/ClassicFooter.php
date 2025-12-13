<?php

namespace Luma\Core\Patterns;

use Luma\Core\Core\Config;

class ClassicFooter
{

    public static function register(): void
    {
        register_block_pattern(
            'luma-core/classic-footer',
            [
                'title'      => __('Classic Footer php', Config::get_domain()),
                'categories' => ['footer', 'luma'],
                'content'    => self::content(),
            ]
        );
    }

    protected static function content(): string
    {
        return <<<HTML
<!-- wp:group {"align":"full","className":"site-footer"} -->
<div class="wp-block-group alignfull site-footer">

    <!-- wp:paragraph -->
    <p>This is a classic footer pattern.</p>
    <!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
HTML;
    }
}
