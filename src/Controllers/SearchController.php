<?php

namespace Luma\Core\Controllers;

use Luma\Core\Services\SearchService;

class SearchController
{

    public function __invoke()
    {
        add_action('wp_ajax_live_search', [$this, 'live']);
        add_action('wp_ajax_nopriv_live_search', [$this, 'live']);

        add_action('wp_ajax_search_results', [$this, 'results']);
        add_action('wp_ajax_nopriv_search_results', [$this, 'results']);
    }

    public function live()
    {

        check_ajax_referer('live-search', 'nonce');

        $term = sanitize_text_field($_GET['term'] ?? '');

        if (strlen($term) < 2) {
            wp_send_json([]);
        }

        $service = new SearchService();
        wp_send_json($service->query($term));
    }

    public function results()
    {
        set_query_var('s', sanitize_text_field($_GET['s'] ?? ''));
        get_template_part('template-parts/search-loop');
        wp_die();
    }
}
