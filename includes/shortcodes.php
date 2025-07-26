<?php
/**
 * Shortcodes for Credit Card Manager
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register the [compare_card] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode output.
 */
function ccm_register_compare_card_shortcode($atts) {
    $atts = shortcode_atts(
        [
            'ids' => '',
        ],
        $atts,
        'compare-card'
    );

    if (empty($atts['ids'])) {
        return '<p>Please provide at least two card IDs.</p>';
    }

    $card_ids = array_map('intval', explode(',', $atts['ids']));

    if (count($card_ids) < 2) {
        return '<p>Please provide at least two card IDs for comparison.</p>';
    }

    $args = [
        'post_type' => 'credit-card',
        'post__in' => $card_ids,
        'orderby' => 'post__in',
        'posts_per_page' => -1,
    ];
    
    $query = new WP_Query($args);
    $compare_cards = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $compare_cards[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'data' => get_post_meta(get_the_ID())
            ];
        }
        wp_reset_postdata();
    }

    wp_enqueue_style(
        'credit-card-compare',
        plugin_dir_url(__FILE__) . '../assets/compare.css',
        [],
        '1.0.0'
    );

    ob_start();
    include(plugin_dir_path(__FILE__) . '../templates/template-parts/compare-table.php');
    return ob_get_clean();
}
add_shortcode('compare-card', 'ccm_register_compare_card_shortcode');
