<?php
/**
 * Credit Card Manager API Functions
 * 
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register REST API endpoints
 */
function ccm_register_rest_routes() {
    register_rest_route('ccm/v1', '/credit-cards/filters', array(
        'methods' => 'GET',
        'callback' => 'ccm_get_filters_data',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('ccm/v1', '/credit-cards', array(
        'methods' => 'GET',
        'callback' => 'ccm_get_credit_cards',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'ccm_register_rest_routes');

/**
 * Get filter data for credit cards - Cached version for performance
 *
 * @return array Filter data
 */
function ccm_get_filters_data() {
    // Cache filters for 6 hours since taxonomy data doesn't change frequently
    $cache_key = 'ccm_filters_data_' . md5(get_locale());
    $filters = wp_cache_get($cache_key, 'ccm');

    if ($filters === false) {
        $filters = array(
            'banks' => array(),
            'network_types' => array(),
            'categories' => array(),
            'rating_ranges' => array(
                array('min' => '4.5', 'label' => '4.5+ Stars'),
                array('min' => '4', 'label' => '4+ Stars'),
                array('min' => '3.5', 'label' => '3.5+ Stars'),
                array('min' => '3', 'label' => '3+ Stars'),
            ),
            'fee_ranges' => array(
                array('max' => '0', 'label' => 'No Annual Fee'),
                array('max' => '1000', 'label' => 'Under ₹1,000'),
                array('max' => '2000', 'label' => 'Under ₹2,000'),
                array('max' => '5000', 'label' => 'Under ₹5,000'),
            ),
        );

        // Get banks (store taxonomy)
        $banks = get_terms(array(
            'taxonomy' => 'store',
            'hide_empty' => true,
        ));

        if (!is_wp_error($banks) && !empty($banks)) {
            foreach ($banks as $bank) {
                $filters['banks'][] = array(
                    'name' => $bank->name,
                    'slug' => $bank->slug,
                    'count' => $bank->count,
                );
            }
        }

        // Get network types
        $networks = get_terms(array(
            'taxonomy' => 'network-type',
            'hide_empty' => true,
        ));

        if (!is_wp_error($networks) && !empty($networks)) {
            foreach ($networks as $network) {
                $filters['network_types'][] = array(
                    'name' => $network->name,
                    'slug' => $network->slug,
                    'count' => $network->count,
                );
            }
        }

        // Get categories
        $categories = get_terms(array(
            'taxonomy' => 'card-category',
            'hide_empty' => true,
        ));

        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $filters['categories'][] = array(
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->count,
                );
            }
        }

        // Cache the results
        wp_cache_set($cache_key, $filters, 'ccm', 6 * HOUR_IN_SECONDS);
    }

    return $filters;
}

/**
 * Get credit cards for AJAX load more
 *
 * @param WP_REST_Request $request
 * @return array Credit cards data
 */
function ccm_get_credit_cards($request) {
    $page = $request->get_param('page') ? intval($request->get_param('page')) : 1;
    $per_page = $request->get_param('per_page') ? intval($request->get_param('per_page')) : 8;
    $orderby = $request->get_param('orderby') ? sanitize_text_field($request->get_param('orderby')) : 'date';
    $order = $request->get_param('order') ? sanitize_text_field($request->get_param('order')) : 'DESC';

    // Support sort_by and sort_order parameters for consistency with archive page
    $sort_by = $request->get_param('sort_by') ? sanitize_text_field($request->get_param('sort_by')) : $orderby;
    $sort_order = $request->get_param('sort_order') ? sanitize_text_field($request->get_param('sort_order')) : $order;

    // Get filter parameters (handle arrays for multiple selections)
    $bank = $request->get_param('bank') ? (array) $request->get_param('bank') : [];
    $network = $request->get_param('network_type') ? (array) $request->get_param('network_type') : [];
    $category = $request->get_param('category') ? (array) $request->get_param('category') : [];
    $min_rating = $request->get_param('min_rating') ? sanitize_text_field($request->get_param('min_rating')) : '';
    $max_fee = $request->get_param('max_annual_fee') ? sanitize_text_field($request->get_param('max_annual_fee')) : '';
    $featured = $request->get_param('featured') ? sanitize_text_field($request->get_param('featured')) : '';
    $trending = $request->get_param('trending') ? sanitize_text_field($request->get_param('trending')) : '';

    // Sanitize array values
    $bank = array_map('sanitize_text_field', $bank);
    $network = array_map('sanitize_text_field', $network);
    $category = array_map('sanitize_text_field', $category);

    // Build query args
    $args = [
        'post_type' => 'credit-card',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
    ];

    // Add sorting logic
    switch ($sort_by) {
        case 'rating':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($sort_order);
            break;
        case 'annual_fee':
            $args['meta_key'] = 'annual_fee';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($sort_order);
            break;
        case 'review_count':
            $args['meta_key'] = 'review_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($sort_order);
            break;
        case 'date':
        default:
            $args['orderby'] = 'date';
            $args['order'] = strtoupper($sort_order);
            break;
    }

    // Handle filtering
    $tax_query = [];
    $meta_query = [];

    // Bank filter (store taxonomy)
    if (!empty($bank)) {
        $tax_query[] = [
            'taxonomy' => 'store',
            'field'    => 'slug',
            'terms'    => $bank,
            'operator' => 'IN',
        ];
    }

    // Category filter (card-category taxonomy)
    if (!empty($category)) {
        $tax_query[] = [
            'taxonomy' => 'card-category',
            'field'    => 'slug',
            'terms'    => $category,
            'operator' => 'IN',
        ];
    }

    // Network type filter (network-type taxonomy)
    if (!empty($network)) {
        $tax_query[] = [
            'taxonomy' => 'network-type',
            'field'    => 'slug',
            'terms'    => $network,
            'operator' => 'IN',
        ];
    }

    // Minimum rating filter
    if (!empty($min_rating) && is_numeric($min_rating)) {
        $meta_query[] = [
            'key'     => 'rating',
            'value'   => $min_rating,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ];
    }

    // Maximum annual fee filter
    if (!empty($max_fee) && is_numeric($max_fee)) {
        $meta_query[] = [
            'key'     => 'annual_fee',
            'value'   => $max_fee,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        ];
    }

    // Featured filter
    if (!empty($featured)) {
        $meta_query[] = [
            'key'     => 'featured',
            'value'   => '1',
            'compare' => '=',
        ];
    }

    // Trending filter
    if (!empty($trending)) {
        $meta_query[] = [
            'key'     => 'trending',
            'value'   => '1',
            'compare' => '=',
        ];
    }

    // Add tax_query and meta_query to args if they have filters
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
        // If multiple tax queries, set relation to AND
        if (count($tax_query) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
    }

    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
        // If multiple meta queries, set relation to AND
        if (count($meta_query) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }
    }

    $query = new WP_Query($args);
    $cards = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Get card data
            $bank_terms = get_the_terms($post_id, 'store');
            $bank_name = !is_wp_error($bank_terms) && !empty($bank_terms) ? $bank_terms[0]->name : '';

            $cards[] = [
                'id' => $post_id,
                'title' => get_the_title(),
                'link' => get_permalink(),
                'card_image' => has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'medium') : '',
                'bank' => $bank_name ? ['name' => $bank_name] : null,
                'rating' => ccm_get_meta($post_id, 'rating', 0, true),
                'review_count' => ccm_get_meta($post_id, 'review_count', 0, true),
                'annual_fee' => ccm_format_currency(ccm_get_meta($post_id, 'annual_fee', 'N/A')),
                'cashback_rate' => ccm_get_meta($post_id, 'cashback_rate', 'N/A'),
                'apply_link' => ccm_get_meta($post_id, 'apply_link', get_permalink()),
                'featured' => (bool) ccm_get_meta($post_id, 'featured', false),
                'trending' => (bool) ccm_get_meta($post_id, 'trending', false),
                'pros' => ccm_get_meta($post_id, 'pros', [], false, true),
            ];
        }
        wp_reset_postdata();
    }

    return [
        'data' => $cards,
        'total' => $query->found_posts,
        'pages' => $query->max_num_pages,
        'current_page' => $page,
    ];
}
