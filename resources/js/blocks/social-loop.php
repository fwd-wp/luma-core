<?php
$social = get_option( 'wpseo_social', [] );

if ( ! empty( $social ) && is_array( $social ) ) {
    foreach ( $social as $key => $value ) {
        if ( empty( $value ) ) {
            continue;
        }

        // Special case for Twitter
        if ( $key === 'twitter_site' ) {
            $url  = 'https://twitter.com/' . ltrim( $value, '@' );
            $icon = 'twitter';
        } else {
            $url  = esc_url( $value );
            $icon = str_replace( [ '_url', '_site' ], '', $key );
        }

        // Check if TT1 has this icon
        $svg = luma_core_get_social_link_svg( $icon );
        if ( empty( $svg ) ) {
            continue; // Skip if no matching icon
        }

        echo '<a href="' . esc_url( $url ) . '" class="social-link ' . esc_attr( $icon ) . '" target="_blank" rel="noopener">';
        echo $svg; // Safe: comes from TT1’s icon system
        echo '</a>';
    }
}
