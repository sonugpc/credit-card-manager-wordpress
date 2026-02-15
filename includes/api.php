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
 * 
 * Note: The callback functions ccm_get_filters_data() and ccm_get_credit_cards()
 * are defined in helper-functions.php and class-post-types.php respectively.
 */
function ccm_register_rest_routes() {
    // This endpoint uses the function from helper-functions.php
    register_rest_route('ccm/v1', '/credit-cards/filters', array(
        'methods' => 'GET',
        'callback' => 'ccm_get_filters_data_api',
        'permission_callback' => '__return_true',
    ));

    // This endpoint uses the function from this file
    register_rest_route('ccm/v1', '/credit-cards', array(
        'methods' => 'GET',
        'callback' => 'ccm_get_credit_cards_api',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'ccm_register_rest_routes');

/**
 * API wrapper for filters data
 * Wraps the helper function to return proper REST response
 */
function ccm_get_filters_data_api() {
    // Get filters from class-post-types.php method
    $post_types = new CreditCardManager_PostTypes();
    return $post_types->get_filters_api();
}

/**
 * Get credit cards for API endpoint
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function ccm_get_credit_cards_api($request) {
    // Delegate to class-post-types.php method
    $post_types = new CreditCardManager_PostTypes();
    return $post_types->get_credit_cards_api($request);
}
