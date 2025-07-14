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
}
add_action('rest_api_init', 'ccm_register_rest_routes');

/**
 * Get filter data for credit cards
 * 
 * @return array Filter data
 */
function ccm_get_filters_data() {
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
        'taxonomy' => 'category',
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
    
    return $filters;
}
