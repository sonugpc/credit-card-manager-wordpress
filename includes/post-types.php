<?php
/**
 * Post Types and Taxonomies
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Credit Card Post Type
 */
function ccm_register_post_type() {
    $labels = array(
        'name'                  => _x('Credit Cards', 'Post type general name', 'credit-card-manager'),
        'singular_name'         => _x('Credit Card', 'Post type singular name', 'credit-card-manager'),
        'menu_name'             => _x('Credit Cards', 'Admin Menu text', 'credit-card-manager'),
        'name_admin_bar'        => _x('Credit Card', 'Add New on Toolbar', 'credit-card-manager'),
        'add_new'               => __('Add New', 'credit-card-manager'),
        'add_new_item'          => __('Add New Credit Card', 'credit-card-manager'),
        'new_item'              => __('New Credit Card', 'credit-card-manager'),
        'edit_item'             => __('Edit Credit Card', 'credit-card-manager'),
        'view_item'             => __('View Credit Card', 'credit-card-manager'),
        'all_items'             => __('All Credit Cards', 'credit-card-manager'),
        'search_items'          => __('Search Credit Cards', 'credit-card-manager'),
        'parent_item_colon'     => __('Parent Credit Cards:', 'credit-card-manager'),
        'not_found'             => __('No credit cards found.', 'credit-card-manager'),
        'not_found_in_trash'    => __('No credit cards found in Trash.', 'credit-card-manager'),
        'featured_image'        => _x('Credit Card Image', 'Overrides the "Featured Image" phrase', 'credit-card-manager'),
        'set_featured_image'    => _x('Set credit card image', 'Overrides the "Set featured image" phrase', 'credit-card-manager'),
        'remove_featured_image' => _x('Remove credit card image', 'Overrides the "Remove featured image" phrase', 'credit-card-manager'),
        'use_featured_image'    => _x('Use as credit card image', 'Overrides the "Use as featured image" phrase', 'credit-card-manager'),
        'archives'              => _x('Credit Card archives', 'The post type archive label', 'credit-card-manager'),
        'insert_into_item'      => _x('Insert into credit card', 'Overrides the "Insert into post" phrase', 'credit-card-manager'),
        'uploaded_to_this_item' => _x('Uploaded to this credit card', 'Overrides the "Uploaded to this post" phrase', 'credit-card-manager'),
        'filter_items_list'     => _x('Filter credit cards list', 'Screen reader text for the filter links', 'credit-card-manager'),
        'items_list_navigation' => _x('Credit cards list navigation', 'Screen reader text for the pagination', 'credit-card-manager'),
        'items_list'            => _x('Credit cards list', 'Screen reader text for the items list', 'credit-card-manager'),
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'rest_base'          => 'credit-cards',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'query_var'          => true,
        'rewrite'            => array('slug' => 'credit-cards'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-id-alt',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'taxonomies'         => array('store', 'category', 'network-type'),
    );
    
    register_post_type('credit-card', $args);
}
add_action('init', 'ccm_register_post_type');

/**
 * Register Custom Taxonomies
 */
function ccm_register_taxonomies() {
    // Network Type Taxonomy
    $network_labels = array(
        'name'              => _x('Network Types', 'taxonomy general name', 'credit-card-manager'),
        'singular_name'     => _x('Network Type', 'taxonomy singular name', 'credit-card-manager'),
        'search_items'      => __('Search Network Types', 'credit-card-manager'),
        'all_items'         => __('All Network Types', 'credit-card-manager'),
        'parent_item'       => __('Parent Network Type', 'credit-card-manager'),
        'parent_item_colon' => __('Parent Network Type:', 'credit-card-manager'),
        'edit_item'         => __('Edit Network Type', 'credit-card-manager'),
        'update_item'       => __('Update Network Type', 'credit-card-manager'),
        'add_new_item'      => __('Add New Network Type', 'credit-card-manager'),
        'new_item_name'     => __('New Network Type Name', 'credit-card-manager'),
        'menu_name'         => __('Network Types', 'credit-card-manager'),
    );
    
    $network_args = array(
        'hierarchical'      => false,
        'labels'            => $network_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rest_base'         => 'network-types',
        'query_var'         => true,
        'rewrite'           => array('slug' => 'network-type'),
    );
    
    register_taxonomy('network-type', array('credit-card'), $network_args);
    
    // Store/Bank Taxonomy
    $store_labels = array(
        'name'              => _x('Banks', 'taxonomy general name', 'credit-card-manager'),
        'singular_name'     => _x('Bank', 'taxonomy singular name', 'credit-card-manager'),
        'search_items'      => __('Search Banks', 'credit-card-manager'),
        'all_items'         => __('All Banks', 'credit-card-manager'),
        'parent_item'       => __('Parent Bank', 'credit-card-manager'),
        'parent_item_colon' => __('Parent Bank:', 'credit-card-manager'),
        'edit_item'         => __('Edit Bank', 'credit-card-manager'),
        'update_item'       => __('Update Bank', 'credit-card-manager'),
        'add_new_item'      => __('Add New Bank', 'credit-card-manager'),
        'new_item_name'     => __('New Bank Name', 'credit-card-manager'),
        'menu_name'         => __('Banks', 'credit-card-manager'),
    );
    
    $store_args = array(
        'hierarchical'      => true,
        'labels'            => $store_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rest_base'         => 'banks',
        'query_var'         => true,
        'rewrite'           => array('slug' => 'bank'),
    );
    
    register_taxonomy('store', array('credit-card'), $store_args);
}
add_action('init', 'ccm_register_taxonomies');

/**
 * Add default terms on plugin activation
 */
function ccm_add_default_terms() {
    // Add default network types
    if (!term_exists('Visa', 'network-type')) {
        wp_insert_term('Visa', 'network-type');
        wp_insert_term('Mastercard', 'network-type');
        wp_insert_term('American Express', 'network-type');
        wp_insert_term('Discover', 'network-type');
        wp_insert_term('RuPay', 'network-type');
    }
}
