<?php
/**
 * REST API Endpoints
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register REST API Routes
 */
function ccm_register_rest_routes() {
    register_rest_route('ccm/v1', '/cards', array(
        'methods' => 'GET',
        'callback' => 'ccm_get_cards',
        'permission_callback' => '__return_true',
    ));
    
    register_rest_route('ccm/v1', '/cards/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'ccm_get_card',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
    
    register_rest_route('ccm/v1', '/filters', array(
        'methods' => 'GET',
        'callback' => 'ccm_get_filters',
        'permission_callback' => '__return_true',
    ));
    
    register_rest_route('ccm/v1', '/compare', array(
        'methods' => 'GET',
        'callback' => 'ccm_compare_cards',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'ccm_register_rest_routes');

/**
 * Get Cards
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function ccm_get_cards($request) {
    $args = array(
        'post_type' => 'credit-card',
        'posts_per_page' => $request->get_param('per_page') ?: 10,
        'paged' => $request->get_param('page') ?: 1,
        'post_status' => 'publish',
    );
    
    // Filter by featured
    if ($request->get_param('featured')) {
        $args['meta_query'][] = array(
            'key' => 'featured',
            'value' => 1,
            'compare' => '=',
        );
    }
    
    // Filter by trending
    if ($request->get_param('trending')) {
        $args['meta_query'][] = array(
            'key' => 'trending',
            'value' => 1,
            'compare' => '=',
        );
    }
    
    // Filter by bank/store
    if ($request->get_param('bank')) {
        $args['tax_query'][] = array(
            'taxonomy' => 'store',
            'field' => 'slug',
            'terms' => $request->get_param('bank'),
        );
    }
    
    // Filter by network type
    if ($request->get_param('network')) {
        $args['tax_query'][] = array(
            'taxonomy' => 'network-type',
            'field' => 'slug',
            'terms' => $request->get_param('network'),
        );
    }
    
    // Filter by annual fee range
    if ($request->get_param('fee_min') || $request->get_param('fee_max')) {
        $fee_query = array('key' => 'annual_fee_numeric');
        
        if ($request->get_param('fee_min')) {
            $fee_query['value'] = intval($request->get_param('fee_min'));
            $fee_query['compare'] = '>=';
            $fee_query['type'] = 'NUMERIC';
        }
        
        if ($request->get_param('fee_max')) {
            $fee_query['value'] = intval($request->get_param('fee_max'));
            $fee_query['compare'] = '<=';
            $fee_query['type'] = 'NUMERIC';
        }
        
        $args['meta_query'][] = $fee_query;
    }
    
    // Filter by minimum income
    if ($request->get_param('income')) {
        $args['meta_query'][] = array(
            'key' => 'min_income_numeric',
            'value' => intval($request->get_param('income')),
            'compare' => '<=',
            'type' => 'NUMERIC',
        );
    }
    
    // Search by keyword
    if ($request->get_param('search')) {
        $args['s'] = sanitize_text_field($request->get_param('search'));
    }
    
    // Set up meta query relation if multiple conditions
    if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
        $args['meta_query']['relation'] = 'AND';
    }
    
    // Set up tax query relation if multiple conditions
    if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
        $args['tax_query']['relation'] = 'AND';
    }
    
    // Sort by
    $sort = $request->get_param('sort') ?: 'default';
    switch ($sort) {
        case 'rating_high':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
            
        case 'rating_low':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
            
        case 'fee_high':
            $args['meta_key'] = 'annual_fee_numeric';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
            
        case 'fee_low':
            $args['meta_key'] = 'annual_fee_numeric';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
            
        case 'title_asc':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
            
        case 'title_desc':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
            
        case 'date_new':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
            
        case 'date_old':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
            
        default:
            // Default sorting (featured first, then by rating)
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => 'featured',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key' => 'featured',
                    'compare' => 'NOT EXISTS',
                ),
            );
            $args['orderby'] = array(
                'meta_value' => 'DESC',
                'meta_value_num' => 'DESC',
            );
            $args['meta_key'] = 'featured';
            break;
    }
    
    $query = new WP_Query($args);
    $cards = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $cards[] = ccm_prepare_card_for_response(get_the_ID());
        }
        wp_reset_postdata();
    }
    
    $response = array(
        'cards' => $cards,
        'total' => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'current_page' => $request->get_param('page') ?: 1,
    );
    
    return rest_ensure_response($response);
}

/**
 * Get Single Card
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function ccm_get_card($request) {
    $card_id = $request->get_param('id');
    
    if (!$card_id || get_post_type($card_id) !== 'credit-card') {
        return new WP_Error('not_found', __('Card not found', 'credit-card-manager'), array('status' => 404));
    }
    
    $card = ccm_prepare_card_for_response($card_id, true);
    
    return rest_ensure_response($card);
}

/**
 * Get Filter Options
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function ccm_get_filters($request) {
    // Get banks
    $banks = get_terms(array(
        'taxonomy' => 'store',
        'hide_empty' => true,
    ));
    
    $bank_options = array();
    if (!is_wp_error($banks)) {
        foreach ($banks as $bank) {
            $bank_options[] = array(
                'id' => $bank->term_id,
                'slug' => $bank->slug,
                'name' => $bank->name,
                'count' => $bank->count,
            );
        }
    }
    
    // Get network types
    $networks = get_terms(array(
        'taxonomy' => 'network-type',
        'hide_empty' => true,
    ));
    
    $network_options = array();
    if (!is_wp_error($networks)) {
        foreach ($networks as $network) {
            $network_options[] = array(
                'id' => $network->term_id,
                'slug' => $network->slug,
                'name' => $network->name,
                'count' => $network->count,
            );
        }
    }
    
    // Get annual fee ranges
    global $wpdb;
    $fee_query = $wpdb->prepare(
        "SELECT MIN(meta_value) as min_fee, MAX(meta_value) as max_fee 
         FROM {$wpdb->postmeta} 
         WHERE meta_key = %s 
         AND meta_value > 0",
        'annual_fee_numeric'
    );
    
    $fee_range = $wpdb->get_row($fee_query);
    $min_fee = $fee_range ? intval($fee_range->min_fee) : 0;
    $max_fee = $fee_range ? intval($fee_range->max_fee) : 10000;
    
    // Get income ranges
    $income_query = $wpdb->prepare(
        "SELECT MIN(meta_value) as min_income, MAX(meta_value) as max_income 
         FROM {$wpdb->postmeta} 
         WHERE meta_key = %s 
         AND meta_value > 0",
        'min_income_numeric'
    );
    
    $income_range = $wpdb->get_row($income_query);
    $min_income = $income_range ? intval($income_range->min_income) : 0;
    $max_income = $income_range ? intval($income_range->max_income) : 1000000;
    
    $filters = array(
        'banks' => $bank_options,
        'networks' => $network_options,
        'fee_range' => array(
            'min' => $min_fee,
            'max' => $max_fee,
        ),
        'income_range' => array(
            'min' => $min_income,
            'max' => $max_income,
        ),
        'sort_options' => array(
            array('value' => 'default', 'label' => __('Featured', 'credit-card-manager')),
            array('value' => 'rating_high', 'label' => __('Rating: High to Low', 'credit-card-manager')),
            array('value' => 'rating_low', 'label' => __('Rating: Low to High', 'credit-card-manager')),
            array('value' => 'fee_high', 'label' => __('Annual Fee: High to Low', 'credit-card-manager')),
            array('value' => 'fee_low', 'label' => __('Annual Fee: Low to High', 'credit-card-manager')),
            array('value' => 'title_asc', 'label' => __('Name: A to Z', 'credit-card-manager')),
            array('value' => 'title_desc', 'label' => __('Name: Z to A', 'credit-card-manager')),
            array('value' => 'date_new', 'label' => __('Newest First', 'credit-card-manager')),
            array('value' => 'date_old', 'label' => __('Oldest First', 'credit-card-manager')),
        ),
    );
    
    return rest_ensure_response($filters);
}

/**
 * Compare Cards
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function ccm_compare_cards($request) {
    $card_ids = $request->get_param('ids');
    
    if (!$card_ids) {
        return new WP_Error('missing_ids', __('No card IDs provided', 'credit-card-manager'), array('status' => 400));
    }
    
    $card_ids = explode(',', $card_ids);
    $cards = array();
    
    foreach ($card_ids as $card_id) {
        if (get_post_type($card_id) === 'credit-card') {
            $cards[] = ccm_prepare_card_for_response($card_id, true);
        }
    }
    
    if (empty($cards)) {
        return new WP_Error('not_found', __('No valid cards found', 'credit-card-manager'), array('status' => 404));
    }
    
    return rest_ensure_response(array('cards' => $cards));
}

/**
 * Prepare Card Data for API Response
 *
 * @param int $card_id
 * @param bool $full_details
 * @return array
 */
function ccm_prepare_card_for_response($card_id, $full_details = false) {
    $card = get_post($card_id);
    
    if (!$card) {
        return array();
    }
    
    $data = array(
        'id' => $card->ID,
        'title' => $card->post_title,
        'slug' => $card->post_name,
        'link' => get_permalink($card->ID),
        'excerpt' => get_the_excerpt($card->ID),
        'image' => get_post_meta($card->ID, 'card_image_url', true) ?: get_the_post_thumbnail_url($card->ID, 'medium'),
        'rating' => floatval(get_post_meta($card->ID, 'rating', true)),
        'review_count' => intval(get_post_meta($card->ID, 'review_count', true)),
        'annual_fee' => get_post_meta($card->ID, 'annual_fee', true),
        'joining_fee' => get_post_meta($card->ID, 'joining_fee', true),
        'welcome_bonus' => get_post_meta($card->ID, 'welcome_bonus', true),
        'cashback_rate' => get_post_meta($card->ID, 'cashback_rate', true),
        'apply_link' => get_post_meta($card->ID, 'apply_link', true),
        'featured' => (bool) get_post_meta($card->ID, 'featured', true),
        'trending' => (bool) get_post_meta($card->ID, 'trending', true),
        'theme_color' => get_post_meta($card->ID, 'theme_color', true) ?: '#1e40af',
    );
    
    // Get bank/store
    $banks = get_the_terms($card->ID, 'store');
    if ($banks && !is_wp_error($banks)) {
        $data['bank'] = array(
            'id' => $banks[0]->term_id,
            'name' => $banks[0]->name,
            'slug' => $banks[0]->slug,
        );
    }
    
    // Get network type
    $networks = get_the_terms($card->ID, 'network-type');
    if ($networks && !is_wp_error($networks)) {
        $data['network'] = array(
            'id' => $networks[0]->term_id,
            'name' => $networks[0]->name,
            'slug' => $networks[0]->slug,
        );
    }
    
    // Add full details if requested
    if ($full_details) {
        $data['content'] = apply_filters('the_content', $card->post_content);
        $data['credit_limit'] = get_post_meta($card->ID, 'credit_limit', true);
        $data['interest_rate'] = get_post_meta($card->ID, 'interest_rate', true);
        $data['processing_time'] = get_post_meta($card->ID, 'processing_time', true);
        $data['min_income'] = get_post_meta($card->ID, 'min_income', true);
        $data['min_age'] = get_post_meta($card->ID, 'min_age', true);
        $data['max_age'] = get_post_meta($card->ID, 'max_age', true);
        $data['welcome_bonus_points'] = intval(get_post_meta($card->ID, 'welcome_bonus_points', true));
        $data['welcome_bonus_type'] = get_post_meta($card->ID, 'welcome_bonus_type', true);
        $data['pros'] = get_post_meta($card->ID, 'pros', true) ?: array();
        $data['cons'] = get_post_meta($card->ID, 'cons', true) ?: array();
        $data['best_for'] = get_post_meta($card->ID, 'best_for', true) ?: array();
        $data['documents'] = get_post_meta($card->ID, 'documents', true) ?: array();
        
        // Get all banks/stores
        $all_banks = get_the_terms($card->ID, 'store');
        if ($all_banks && !is_wp_error($all_banks)) {
            $data['banks'] = array();
            foreach ($all_banks as $bank) {
                $data['banks'][] = array(
                    'id' => $bank->term_id,
                    'name' => $bank->name,
                    'slug' => $bank->slug,
                );
            }
        }
        
        // Get all network types
        $all_networks = get_the_terms($card->ID, 'network-type');
        if ($all_networks && !is_wp_error($all_networks)) {
            $data['networks'] = array();
            foreach ($all_networks as $network) {
                $data['networks'][] = array(
                    'id' => $network->term_id,
                    'name' => $network->name,
                    'slug' => $network->slug,
                );
            }
        }
    }
    
    return $data;
}
