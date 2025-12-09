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

    // Build query args
    $args = [
        'post_type' => 'credit-card',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'orderby' => $orderby,
        'order' => $order,
    ];

    // Add sorting logic
    switch ($orderby) {
        case 'rating':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            break;
        case 'annual_fee':
            $args['meta_key'] = 'annual_fee_numeric';
            $args['orderby'] = 'meta_value_num';
            break;
        case 'review_count':
            $args['meta_key'] = 'review_count';
            $args['orderby'] = 'meta_value_num';
            break;
        default:
            $args['orderby'] = 'date';
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
