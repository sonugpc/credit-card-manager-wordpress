<?php
/**
 * Credit Card Manager Shortcodes
 *
 * @package Credit Card Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all shortcodes
 */
function ccm_register_shortcodes() {
    add_shortcode('credit_card', 'ccm_credit_card_shortcode');
    add_shortcode('credit_cards', 'ccm_credit_cards_shortcode');
}
add_action('init', 'ccm_register_shortcodes');

/**
 * Shortcode to display a single credit card
 * 
 * Usage: [credit_card id="123"]
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function ccm_credit_card_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'credit_card');
    
    $card_id = intval($atts['id']);
    
    if (!$card_id) {
        return '<p class="ccm-error">Error: Credit card ID is required.</p>';
    }
    
    $card = get_post($card_id);
    
    if (!$card || $card->post_type !== 'credit-card' || $card->post_status !== 'publish') {
        return '<p class="ccm-error">Error: Credit card not found or not published.</p>';
    }
    
    // Get card data
    $card_title = get_the_title($card_id);
    $card_link = get_permalink($card_id);
    $card_excerpt = get_the_excerpt($card_id);
    
    // Get meta data
    $card_image = get_post_meta($card_id, 'card_image_url', true);
    if (empty($card_image) && has_post_thumbnail($card_id)) {
        $card_image = get_the_post_thumbnail_url($card_id, 'medium');
    }
    
    $rating = get_post_meta($card_id, 'rating', true);
    $rating_percent = $rating ? ($rating / 5) * 100 : 0;
    $review_count = get_post_meta($card_id, 'review_count', true);
    $annual_fee = get_post_meta($card_id, 'annual_fee', true);
    $cashback_rate = get_post_meta($card_id, 'cashback_rate', true);
    $welcome_bonus = get_post_meta($card_id, 'welcome_bonus', true);
    $apply_link = get_post_meta($card_id, 'apply_link', true);
    $featured = get_post_meta($card_id, 'featured', true);
    $trending = get_post_meta($card_id, 'trending', true);
    
    // Get bank (store taxonomy)
    $bank_terms = get_the_terms($card_id, 'store');
    $bank_name = '';
    if (!is_wp_error($bank_terms) && !empty($bank_terms)) {
        $bank_name = $bank_terms[0]->name;
    }
    
    // Build output
    ob_start();
    ?>
    <div class="ccm-card-item ccm-card-full-width">
        <div class="ccm-card-inner">
            <div class="ccm-card-compare">
                <label class="ccm-compare-checkbox">
                    <input type="checkbox" class="ccm-compare-input" 
                           data-id="<?php echo esc_attr($card_id); ?>" 
                           data-title="<?php echo esc_attr($card_title); ?>" 
                           data-image="<?php echo esc_url($card_image); ?>">
                    Add to Compare
                </label>
            </div>
            
            <div class="ccm-card-full-content">
                <div class="ccm-card-image">
                    <img src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr($card_title); ?>">
                    <?php if ($featured) : ?>
                        <span class="ccm-badge ccm-featured">Featured</span>
                    <?php endif; ?>
                    <?php if ($trending) : ?>
                        <span class="ccm-badge ccm-trending">Trending</span>
                    <?php endif; ?>
                </div>
                
                <div class="ccm-card-content">
                    <h3 class="ccm-card-title">
                        <a href="<?php echo esc_url($card_link); ?>"><?php echo esc_html($card_title); ?></a>
                    </h3>
                    
                    <?php if (!empty($bank_name)) : ?>
                        <div class="ccm-card-bank"><?php echo esc_html($bank_name); ?></div>
                    <?php endif; ?>
                    
                    <div class="ccm-card-meta">
                        <?php if (!empty($rating)) : ?>
                            <div class="ccm-rating">
                                <div class="ccm-stars">
                                    <span class="dashicons dashicons-star-filled"></span>
                                    <span class="dashicons dashicons-star-filled"></span>
                                    <span class="dashicons dashicons-star-filled"></span>
                                    <span class="dashicons dashicons-star-filled"></span>
                                    <span class="dashicons dashicons-star-filled"></span>
                                    <div class="ccm-stars-filled" style="width: <?php echo esc_attr($rating_percent); ?>%;">
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span class="dashicons dashicons-star-filled"></span>
                                    </div>
                                </div>
                                <span class="ccm-rating-number"><?php echo esc_html($rating); ?>/5</span>
                                <?php if (!empty($review_count)) : ?>
                                    <span class="ccm-review-count">(<?php echo esc_html($review_count); ?> reviews)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="ccm-card-details">
                        <?php if (!empty($annual_fee)) : ?>
                            <div class="ccm-detail-item">
                                <span class="ccm-detail-label">Annual Fee</span>
                                <span class="ccm-detail-value"><?php echo esc_html($annual_fee); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($cashback_rate)) : ?>
                            <div class="ccm-detail-item">
                                <span class="ccm-detail-label">Reward Rate</span>
                                <span class="ccm-detail-value"><?php echo esc_html($cashback_rate); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($welcome_bonus)) : ?>
                            <div class="ccm-detail-item">
                                <span class="ccm-detail-label">Welcome Bonus</span>
                                <span class="ccm-detail-value"><?php echo esc_html($welcome_bonus); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($card_excerpt)) : ?>
                        <div class="ccm-card-excerpt"><?php echo wp_kses_post($card_excerpt); ?></div>
                    <?php endif; ?>
                    
                    <div class="ccm-card-actions">
                        <a href="<?php echo esc_url($card_link); ?>" class="ccm-btn ccm-btn-details">
                            <span class="dashicons dashicons-visibility"></span> Read More
                        </a>
                        <?php if (!empty($apply_link)) : ?>
                            <a href="<?php echo esc_url($apply_link); ?>" class="ccm-btn ccm-btn-apply" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-external"></span> Quick Apply
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode to display multiple credit cards
 * 
 * Usage: [credit_cards ids="123,456,789"]
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function ccm_credit_cards_shortcode($atts) {
    $atts = shortcode_atts(array(
        'ids' => '',
    ), $atts, 'credit_cards');
    
    if (empty($atts['ids'])) {
        return '<p class="ccm-error">Error: Credit card IDs are required.</p>';
    }
    
    $card_ids = array_map('intval', explode(',', $atts['ids']));
    
    if (empty($card_ids)) {
        return '<p class="ccm-error">Error: Invalid credit card IDs.</p>';
    }
    
    $args = array(
        'post_type' => 'credit-card',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'post__in' => $card_ids,
        'orderby' => 'post__in',
    );
    
    $cards_query = new WP_Query($args);
    
    if (!$cards_query->have_posts()) {
        return '<p class="ccm-error">Error: No credit cards found.</p>';
    }
    
    ob_start();
    ?>
    <div class="ccm-cards-container">
        <?php while ($cards_query->have_posts()) : $cards_query->the_post(); ?>
            <?php echo ccm_credit_card_shortcode(array('id' => get_the_ID())); ?>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </div>
    
    <!-- Compare Modal -->
    <div class="ccm-modal" id="ccm-compare-modal">
        <div class="ccm-modal-content">
            <div class="ccm-modal-header">
                <h2>Compare Credit Cards</h2>
                <button type="button" class="ccm-modal-close" id="ccm-modal-close">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="ccm-modal-body">
                <table class="ccm-comparison-table" id="ccm-comparison-table"></table>
            </div>
        </div>
    </div>
    
    <!-- Compare Section (Initially Hidden) -->
    <div class="ccm-compare-section" id="ccm-compare-section" style="display: none;">
        <div class="ccm-compare-header">
            <h3>Compare Credit Cards</h3>
            <div class="ccm-compare-actions">
                <span class="ccm-compare-count" id="ccm-compare-count">0 cards selected</span>
                <button type="button" class="ccm-compare-clear" id="ccm-compare-clear">
                    <span class="dashicons dashicons-trash"></span> Clear All
                </button>
                <button type="button" class="ccm-compare-button" id="ccm-compare-button" disabled>
                    <span class="dashicons dashicons-visibility"></span> Compare Cards
                </button>
            </div>
        </div>
        <div class="ccm-compare-cards" id="ccm-compare-cards"></div>
    </div>
    
    <!-- Card Template for Compare Section -->
    <script type="text/template" id="ccm-compare-card-template">
        <div class="ccm-compare-card">
            <button type="button" class="ccm-compare-remove" data-id="{{id}}">×</button>
            <img src="{{image}}" alt="{{title}}">
            <div class="ccm-compare-title">{{title}}</div>
        </div>
    </script>
    <?php
    
    // Add custom CSS for full-width cards
    $custom_css = '
    <style>
        .ccm-card-full-width {
            width: 100%;
            margin-bottom: 2rem;
        }
        
        .ccm-card-full-width .ccm-card-inner {
            display: flex;
            flex-direction: column;
        }
        
        .ccm-card-full-width .ccm-card-full-content {
            display: flex;
            flex-direction: row;
        }
        
        .ccm-card-full-width .ccm-card-image {
            flex: 0 0 200px;
            height: auto;
            padding: 1.5rem;
        }
        
        .ccm-card-full-width .ccm-card-content {
            flex: 1;
            padding: 1.5rem;
            border-left: 1px solid var(--ccm-border);
        }
        
        @media (max-width: 768px) {
            .ccm-card-full-width .ccm-card-full-content {
                flex-direction: column;
            }
            
            .ccm-card-full-width .ccm-card-image {
                flex: 0 0 auto;
                height: 180px;
                border-bottom: 1px solid var(--ccm-border);
            }
            
            .ccm-card-full-width .ccm-card-content {
                border-left: none;
            }
        }
    </style>
    ';
    
    return $custom_css . ob_get_clean();
}
