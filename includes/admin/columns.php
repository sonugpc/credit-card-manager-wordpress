<?php
/**
 * Admin Columns for Credit Card Post Type
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add Admin Columns
 */
function ccm_add_admin_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['card_image'] = __('Image', 'credit-card-manager');
            $new_columns['rating'] = __('Rating', 'credit-card-manager');
            $new_columns['annual_fee'] = __('Annual Fee', 'credit-card-manager');
            $new_columns['network_type'] = __('Network', 'credit-card-manager');
            $new_columns['bank'] = __('Bank', 'credit-card-manager');
            $new_columns['featured'] = __('Featured', 'credit-card-manager');
        }
    }
    return $new_columns;
}
add_filter('manage_credit-card_posts_columns', 'ccm_add_admin_columns');

/**
 * Admin Column Content
 */
function ccm_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'card_image':
            $image_url = get_post_meta($post_id, 'card_image_url', true);
            if ($image_url) {
                echo '<img src="' . esc_url($image_url) . '" style="width: 50px; height: auto;" />';
            } elseif (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, array(50, 50));
            } else {
                echo '-';
            }
            break;
            
        case 'rating':
            $rating = get_post_meta($post_id, 'rating', true);
            if ($rating) {
                echo esc_html($rating) . '/5 ⭐';
            } else {
                echo '-';
            }
            break;
            
        case 'annual_fee':
            $fee = get_post_meta($post_id, 'annual_fee', true);
            echo $fee ? esc_html($fee) : '-';
            break;
            
        case 'network_type':
            $terms = get_the_terms($post_id, 'network-type');
            if ($terms && !is_wp_error($terms)) {
                $names = wp_list_pluck($terms, 'name');
                echo esc_html(implode(', ', $names));
            } else {
                echo '-';
            }
            break;
            
        case 'bank':
            $terms = get_the_terms($post_id, 'store');
            if ($terms && !is_wp_error($terms)) {
                $names = wp_list_pluck($terms, 'name');
                echo esc_html(implode(', ', $names));
            } else {
                echo '-';
            }
            break;
            
        case 'featured':
            $featured = get_post_meta($post_id, 'featured', true);
            echo $featured ? '✅' : '-';
            break;
    }
}
add_action('manage_credit-card_posts_custom_column', 'ccm_admin_column_content', 10, 2);

/**
 * Sortable Columns
 */
function ccm_sortable_columns($columns) {
    $columns['rating'] = 'rating';
    $columns['annual_fee'] = 'annual_fee';
    return $columns;
}
add_filter('manage_edit-credit-card_sortable_columns', 'ccm_sortable_columns');

/**
 * Sort columns by meta value
 */
function ccm_sort_custom_columns($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('rating' === $orderby) {
        $query->set('meta_key', 'rating');
        $query->set('orderby', 'meta_value_num');
    }

    if ('annual_fee' === $orderby) {
        $query->set('meta_key', 'annual_fee_numeric');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'ccm_sort_custom_columns');
