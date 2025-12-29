<?php
namespace Luma\Core\Services;

class SearchService
{
	public function query( string $term ): array
	{
		$key = 'live_search_' . md5( $term );
		$cached = get_transient( $key );

		if ( $cached !== false ) {
			return $cached;
		}

		$post_types = [ 'post', 'page' ];
		if ( class_exists( 'WooCommerce' ) ) {
			$post_types[] = 'product';
		}

		$query = new \WP_Query([
			's'              => $term,
			'post_type'      => $post_types,
			'posts_per_page' => 5,
			'post_status'    => 'publish',
		]);

		$results = [];

		while ( $query->have_posts() ) {
			$query->the_post();

			$item = [
				'title' => get_the_title(),
				'url'   => get_permalink(),
				'type'  => get_post_type(),
			];

			if ( get_post_type() === 'product' ) {
				$product = wc_get_product( get_the_ID() );
				$item['price'] = $product?->get_price_html();
			}

			$results[] = $item;
		}

		wp_reset_postdata();
		set_transient( $key, $results, MINUTE_IN_SECONDS * 5 );

		return $results;
	}
}
