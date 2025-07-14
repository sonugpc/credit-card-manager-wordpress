<?php
/**
 * Assets Management
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Admin Scripts
 */
function ccm_admin_scripts($hook) {
    global $post_type;
    
    if ($post_type === 'credit-card' && ($hook === 'post.php' || $hook === 'post-new.php')) {
        wp_enqueue_media();
        wp_enqueue_script(
            'credit-card-admin',
            CCM_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            CCM_VERSION,
            true
        );
        
        wp_localize_script('credit-card-admin', 'ccm_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ccm_admin_nonce'),
        ));
    }
}
add_action('admin_enqueue_scripts', 'ccm_admin_scripts');

/**
 * Frontend Scripts
 */
function ccm_frontend_scripts() {
    if (is_singular('credit-card') || is_post_type_archive('credit-card')) {
        wp_enqueue_script(
            'credit-card-frontend',
            CCM_PLUGIN_URL . 'assets/frontend.js',
            array('jquery'),
            CCM_VERSION,
            true
        );
        
        wp_enqueue_style(
            'credit-card-frontend',
            CCM_PLUGIN_URL . 'assets/frontend.css',
            array(),
            CCM_VERSION
        );
        
        wp_localize_script('credit-card-frontend', 'ccm_frontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'api_url' => rest_url('ccm/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'ccm_frontend_scripts');
