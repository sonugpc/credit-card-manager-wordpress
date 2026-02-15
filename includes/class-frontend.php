<?php
/**
 * Frontend Class
 * Handles all frontend functionality and asset loading
 */

if (!defined('ABSPATH')) {
    exit;
}

class CreditCardManager_Frontend {

    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('rest_query_vars', array($this, 'add_rest_query_vars'));
        add_filter('rest_credit-card_query', array($this, 'filter_rest_api'));
    }

    /**
     * Initialize component
     */
    public function init() {
        // Component is initialized through hooks
    }

    /**
     * Enqueue frontend scripts and styles - Optimized with conditional loading
     */
    public function enqueue_scripts() {
        // Only load assets on relevant pages to improve performance
        if ($this->should_load_assets()) {
            wp_enqueue_style('ccm-frontend', CCM_PLUGIN_URL . 'assets/frontend.css', array(), CCM_VERSION);
            wp_enqueue_style('ccm-archive-responsive', CCM_PLUGIN_URL . 'assets/archive-responsive.css', array('ccm-frontend'), CCM_VERSION);
            wp_enqueue_script('ccm-frontend', CCM_PLUGIN_URL . 'assets/frontend.js', array('jquery'), CCM_VERSION, true);

            wp_localize_script('ccm-frontend', 'ccm_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'api_url' => rest_url('ccm/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
            ));
        }
    }

    /**
     * Check if assets should be loaded on current page
     */
    private function should_load_assets() {
        // Check for plugin-specific pages
        if (is_post_type_archive('credit-card') ||
            is_singular('credit-card') ||
            is_page('compare-cards') ||
            get_query_var('credit_card_compare')) {
            return true;
        }

        // Check for shortcodes in content
        global $post;
        if (is_a($post, 'WP_Post') &&
            (has_shortcode($post->post_content, 'credit-card') ||
             has_shortcode($post->post_content, 'credit_card_grid') ||
             has_shortcode($post->post_content, 'ccm_filters'))) {
            return true;
        }

        return false;
    }

    /**
     * Add REST Query Vars
     */
    public function add_rest_query_vars($valid_vars) {
        $valid_vars = array_merge($valid_vars, array(
            'meta_query', 'tax_query', 'min_rating', 'max_annual_fee',
            'featured', 'trending', 'bank', 'network_type', 'category'
        ));
        return $valid_vars;
    }

    /**
     * Filter REST API for default WordPress endpoints
     */
    public function filter_rest_api($args, $request = null) {
        // If no request object, return args unchanged
        if (!$request) {
            return $args;
        }
        
        $params = $request->get_params();

        // Filter by bank (store taxonomy)
        if (!empty($params['bank'])) {
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = array();
            }
            $args['tax_query'][] = array(
                'taxonomy' => 'store',
                'field'    => 'slug',
                'terms'    => explode(',', $params['bank']),
            );
        }

        // Filter by network type
        if (!empty($params['network_type'])) {
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = array();
            }
            $args['tax_query'][] = array(
                'taxonomy' => 'network-type',
                'field'    => 'slug',
                'terms'    => explode(',', $params['network_type']),
            );
        }

        // Filter by category
        if (!empty($params['category'])) {
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = array();
            }
            $args['tax_query'][] = array(
                'taxonomy' => 'card-category',
                'field'    => 'slug',
                'terms'    => explode(',', $params['category']),
            );
        }

        // Meta query filters
        if (!empty($params['min_rating']) || !empty($params['max_annual_fee']) ||
            isset($params['featured']) || isset($params['trending'])) {

            if (!isset($args['meta_query'])) {
                $args['meta_query'] = array();
            }

            if (!empty($params['min_rating'])) {
                $args['meta_query'][] = array(
                    'key'     => 'rating',
                    'value'   => floatval($params['min_rating']),
                    'compare' => '>=',
                    'type'    => 'DECIMAL',
                );
            }

            if (!empty($params['max_annual_fee'])) {
                $args['meta_query'][] = array(
                    'key'     => 'annual_fee_numeric',
                    'value'   => intval($params['max_annual_fee']),
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                );
            }

            if (isset($params['featured'])) {
                $args['meta_query'][] = array(
                    'key'     => 'featured',
                    'value'   => $params['featured'] ? '1' : '0',
                    'compare' => '=',
                );
            }

            if (isset($params['trending'])) {
                $args['meta_query'][] = array(
                    'key'     => 'trending',
                    'value'   => $params['trending'] ? '1' : '0',
                    'compare' => '=',
                );
            }
        }

        // Set relations if multiple conditions
        if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }

        if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }

        return $args;
    }
}

/**
 * Template Functions for Theme Integration
 */

/**
 * Get Credit Card Data
 */
function ccm_get_credit_card($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $manager = new CreditCardManager_PostTypes();
    return $manager->format_credit_card_data($post_id);
}

/**
 * Display Credit Card Info
 */
function ccm_display_card_info($post_id = null, $fields = array()) {
    $card = ccm_get_credit_card($post_id);

    if (empty($fields)) {
        $fields = array('rating', 'annual_fee', 'network_type', 'bank');
    }

    echo '<div class="ccm-card-info">';

    foreach ($fields as $field) {
        if (isset($card[$field]) && !empty($card[$field])) {
            echo '<div class="ccm-info-item ccm-' . esc_attr($field) . '">';
            echo '<label>' . esc_html(ucwords(str_replace('_', ' ', $field))) . ':</label>';

            if ($field === 'rating') {
                echo '<span class="rating">' . esc_html($card[$field]) . '/5 ⭐</span>';
            } elseif ($field === 'bank' && is_array($card[$field])) {
                echo '<span>' . esc_html($card[$field]['name']) . '</span>';
            } elseif ($field === 'network_type' && is_array($card[$field])) {
                echo '<span>' . esc_html($card[$field]['name']) . '</span>';
            } else {
                echo '<span>' . esc_html($card[$field]) . '</span>';
            }

            echo '</div>';
        }
    }

    echo '</div>';
}

/**
 * Display Credit Card Filters
 */
function ccm_display_filters($atts = array()) {
    $defaults = array(
        'show_banks' => true,
        'show_networks' => true,
        'show_categories' => true,
        'show_rating' => true,
        'show_fees' => true,
        'ajax' => true,
    );

    $args = wp_parse_args($atts, $defaults);

    // Get filter data from API
    $response = wp_remote_get(rest_url('ccm/v1/credit-cards/filters'));

    if (is_wp_error($response)) {
        return '';
    }

    $filters = json_decode(wp_remote_retrieve_body($response), true);

    ob_start();
    ?>
    <div class="ccm-filters" data-ajax="<?php echo $args['ajax'] ? 'true' : 'false'; ?>">
        <form class="ccm-filter-form" method="get">

            <?php if ($args['show_banks'] && !empty($filters['banks'])): ?>
            <div class="ccm-filter-group">
                <label><?php _e('Bank', 'credit-card-manager'); ?></label>
                <select name="bank" class="ccm-filter-select">
                    <option value=""><?php _e('All Banks', 'credit-card-manager'); ?></option>
                    <?php foreach ($filters['banks'] as $bank): ?>
                        <option value="<?php echo esc_attr($bank['slug']); ?>"
                                <?php selected(isset($_GET['bank']) ? $_GET['bank'] : '', $bank['slug']); ?>>
                            <?php echo esc_html($bank['name']) . ' (' . $bank['count'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($args['show_networks'] && !empty($filters['network_types'])): ?>
            <div class="ccm-filter-group">
                <label><?php _e('Network Type', 'credit-card-manager'); ?></label>
                <select name="network_type" class="ccm-filter-select">
                    <option value=""><?php _e('All Networks', 'credit-card-manager'); ?></option>
                    <?php foreach ($filters['network_types'] as $network): ?>
                        <option value="<?php echo esc_attr($network['slug']); ?>"
                                <?php selected(isset($_GET['network_type']) ? $_GET['network_type'] : '', $network['slug']); ?>>
                            <?php echo esc_html($network['name']) . ' (' . $network['count'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($args['show_categories'] && !empty($filters['categories'])): ?>
            <div class="ccm-filter-group">
                <label><?php _e('Category', 'credit-card-manager'); ?></label>
                <select name="category" class="ccm-filter-select">
                    <option value=""><?php _e('All Categories', 'credit-card-manager'); ?></option>
                    <?php foreach ($filters['categories'] as $category): ?>
                        <option value="<?php echo esc_attr($category['slug']); ?>"
                                <?php selected(isset($_GET['category']) ? $_GET['category'] : '', $category['slug']); ?>>
                            <?php echo esc_html($category['name']) . ' (' . $category['count'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($args['show_rating']): ?>
            <div class="ccm-filter-group">
                <label><?php _e('Minimum Rating', 'credit-card-manager'); ?></label>
                <select name="min_rating" class="ccm-filter-select">
                    <option value=""><?php _e('Any Rating', 'credit-card-manager'); ?></option>
                    <?php foreach ($filters['rating_ranges'] as $range): ?>
                        <option value="<?php echo esc_attr($range['min']); ?>"
                                <?php selected(isset($_GET['min_rating']) ? $_GET['min_rating'] : '', $range['min']); ?>>
                            <?php echo esc_html($range['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($args['show_fees']): ?>
            <div class="ccm-filter-group">
                <label><?php _e('Maximum Annual Fee', 'credit-card-manager'); ?></label>
                <select name="max_annual_fee" class="ccm-filter-select">
                    <option value=""><?php _e('Any Fee', 'credit-card-manager'); ?></option>
                    <?php foreach ($filters['fee_ranges'] as $range): ?>
                        <option value="<?php echo esc_attr($range['max']); ?>"
                                <?php selected(isset($_GET['max_annual_fee']) ? $_GET['max_annual_fee'] : '', $range['max']); ?>>
                            <?php echo esc_html($range['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="ccm-filter-group">
                <button type="submit" class="ccm-filter-submit">
                    <?php _e('Filter Cards', 'credit-card-manager'); ?>
                </button>
                <button type="button" class="ccm-filter-reset">
                    <?php _e('Reset', 'credit-card-manager'); ?>
                </button>
            </div>

        </form>
    </div>

    <?php

    return ob_get_clean();
}

/**
 * Shortcode for displaying filters
 */
function ccm_filters_shortcode($atts) {
    return ccm_display_filters($atts);
}
add_shortcode('ccm_filters', 'ccm_filters_shortcode');

/**
 * Shortcode for displaying credit card grid
 */
function ccm_cards_grid_shortcode($atts) {
    $defaults = array(
        'limit' => 12,
        'bank' => '',
        'network_type' => '',
        'category' => '',
        'featured' => '',
        'trending' => '',
        'min_rating' => '',
        'sort_by' => 'rating',
        'sort_order' => 'desc',
        'show_filters' => true,
    );

    $args = wp_parse_args($atts, $defaults);

    // Build API URL
    $api_url = rest_url('ccm/v1/credit-cards');
    $query_params = array();

    foreach ($args as $key => $value) {
        if (!empty($value) && $key !== 'show_filters') {
            $query_params[$key] = $value;
        }
    }

    if (!empty($query_params)) {
        $api_url .= '?' . http_build_query($query_params);
    }

    // Fetch data
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return '<p>' . __('Error loading credit cards.', 'credit-card-manager') . '</p>';
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $cards = isset($data['data']) ? $data['data'] : array();

    ob_start();
    ?>
    <div class="ccm-cards-container">
        <?php if ($args['show_filters']): ?>
            <?php echo ccm_display_filters(); ?>
        <?php endif; ?>

        <div class="ccm-cards-grid" id="ccm-cards-results">
            <?php if (!empty($cards)): ?>
                <?php foreach ($cards as $card): ?>
                    <div class="ccm-card-item" data-id="<?php echo esc_attr($card['id']); ?>">
                        <div class="ccm-card-inner">

                            <?php if (!empty($card['card_image'])): ?>
                            <div class="ccm-card-image">
                                <img src="<?php echo esc_url($card['card_image']); ?>"
                                     alt="<?php echo esc_attr($card['title']); ?>" />

                                <?php if ($card['featured']): ?>
                                    <span class="ccm-badge ccm-featured"><?php _e('Featured', 'credit-card-manager'); ?></span>
                                <?php endif; ?>

                                <?php if ($card['trending']): ?>
                                    <span class="ccm-badge ccm-trending"><?php _e('Trending', 'credit-card-manager'); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <div class="ccm-card-content">
                                <h3 class="ccm-card-title">
                                    <a href="<?php echo esc_url($card['link']); ?>">
                                        <?php echo esc_html($card['title']); ?>
                                    </a>
                                </h3>

                                <?php if ($card['bank']): ?>
                                <div class="ccm-card-bank">
                                    <?php echo esc_html($card['bank']['name']); ?>
                                </div>
                                <?php endif; ?>

                                <div class="ccm-card-meta">
                                    <?php if ($card['rating']): ?>
                                    <div class="ccm-rating">
                                        <span class="ccm-stars"><?php echo str_repeat('⭐', floor($card['rating'])); ?></span>
                                        <span class="ccm-rating-number"><?php echo esc_html($card['rating']); ?>/5</span>
                                        <?php if ($card['review_count']): ?>
                                            <span class="ccm-review-count">(<?php echo esc_html($card['review_count']); ?> reviews)</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($card['annual_fee']): ?>
                                    <div class="ccm-annual-fee">
                                        <strong><?php _e('Annual Fee:', 'credit-card-manager'); ?></strong>
                                        <?php echo esc_html($card['annual_fee']); ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($card['cashback_rate']): ?>
                                    <div class="ccm-cashback-rate">
                                        <strong><?php _e('Reward Rate:', 'credit-card-manager'); ?></strong>
                                        <?php echo esc_html($card['cashback_rate']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($card['excerpt'])): ?>
                                <div class="ccm-card-excerpt">
                                    <?php echo wp_kses_post($card['excerpt']); ?>
                                </div>
                                <?php endif; ?>

                                <div class="ccm-card-actions">
                                    <a href="<?php echo esc_url($card['link']); ?>" class="ccm-btn ccm-btn-details">
                                        <?php _e('View Details', 'credit-card-manager'); ?>
                                    </a>

                                    <?php if (!empty($card['apply_link'])): ?>
                                    <a href="<?php echo esc_url($card['apply_link']); ?>"
                                       class="ccm-btn ccm-btn-apply"
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        <?php _e('Apply Now', 'credit-card-manager'); ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="ccm-no-results">
                    <p><?php _e('No credit cards found matching your criteria.', 'credit-card-manager'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($data['pagination']) && $data['pagination']['pages'] > 1): ?>
        <div class="ccm-pagination">
            <?php
            $pagination = $data['pagination'];
            $current_page = $pagination['current_page'];
            $total_pages = $pagination['pages'];

            // Simple pagination
            if ($current_page > 1) {
                echo '<a href="#" class="ccm-page-link" data-page="' . ($current_page - 1) . '">&laquo; Previous</a>';
            }

            for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
                $class = $i === $current_page ? 'ccm-page-link ccm-current' : 'ccm-page-link';
                echo '<a href="#" class="' . $class . '" data-page="' . $i . '">' . $i . '</a>';
            }

            if ($current_page < $total_pages) {
                echo '<a href="#" class="ccm-page-link" data-page="' . ($current_page + 1) . '">Next &raquo;</a>';
            }
            ?>
        </div>
        <?php endif; ?>
    </div>

    <?php

    return ob_get_clean();
}
add_shortcode('ccm_cards_grid', 'ccm_cards_grid_shortcode');
