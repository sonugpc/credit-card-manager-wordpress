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

    ob_start();
    include(plugin_dir_path(__FILE__) . '../templates/template-parts/compare-table.php');
    return ob_get_clean();
}
add_shortcode('compare-card', 'ccm_register_compare_card_shortcode');

/**
 * Register the [credit_card] shortcode to display a single credit card.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode output.
 */
function ccm_register_credit_card_shortcode($atts) {
    $atts = shortcode_atts(
        [
            'id' => '',
            'mode' => 'mini', // 'mini' or 'full'
            'show_image' => 'yes',
            'show_rating' => 'yes',
            'show_fees' => 'yes',
            'show_benefits' => 'yes',
        ],
        $atts,
        'credit-card'
    );

    if (empty($atts['id'])) {
        return '<p>Please provide a card ID.</p>';
    }

    $card_id = intval($atts['id']);
    $post = get_post($card_id);

    if (!$post || $post->post_type !== 'credit-card') {
        return '<p>Credit card not found.</p>';
    }

    // Get card data
    $card_data = [
        'id' => $card_id,
        'title' => get_the_title($card_id),
        'permalink' => get_permalink($card_id),
        'excerpt' => get_the_excerpt($card_id),
        'image_url' => has_post_thumbnail($card_id) ? get_the_post_thumbnail_url($card_id, 'large') : '',
        'rating' => get_post_meta($card_id, 'rating', true),
        'review_count' => get_post_meta($card_id, 'review_count', true),
        'annual_fee' => get_post_meta($card_id, 'annual_fee', true),
        'joining_fee' => get_post_meta($card_id, 'joining_fee', true),
        'welcome_bonus' => get_post_meta($card_id, 'welcome_bonus', true),
        'welcome_bonus_points' => get_post_meta($card_id, 'welcome_bonus_points', true),
        'welcome_bonus_type' => get_post_meta($card_id, 'welcome_bonus_type', true),
        'cashback_rate' => get_post_meta($card_id, 'cashback_rate', true),
        'credit_limit' => get_post_meta($card_id, 'credit_limit', true),
        'interest_rate' => get_post_meta($card_id, 'interest_rate', true),
        'processing_time' => get_post_meta($card_id, 'processing_time', true),
        'min_income' => get_post_meta($card_id, 'min_income', true),
        'apply_link' => get_post_meta($card_id, 'apply_link', true),
        'featured' => get_post_meta($card_id, 'featured', true),
        'pros' => get_post_meta($card_id, 'pros', true) ?: [],
        'cons' => get_post_meta($card_id, 'cons', true) ?: [],
        'best_for' => get_post_meta($card_id, 'best_for', true) ?: [],
    ];

    // Get bank name
    $banks = wp_get_post_terms($card_id, 'store', array('fields' => 'names'));
    $card_data['bank'] = !empty($banks) ? $banks[0] : '';

    // Get network type
    $networks = wp_get_post_terms($card_id, 'network-type', array('fields' => 'names'));
    $card_data['network'] = !empty($networks) ? $networks[0] : '';

    // Note: CSS is included inline in the shortcode output
    
    ob_start();
    
    if ($atts['mode'] === 'mini') {
        // Mini mode using optimized CSS classes
        ?>
        <article class="ccm-card ccm-card-mini" data-id="<?php echo esc_attr($card_data['id']); ?>">
            <header class="ccm-card-header">
                <h3 class="ccm-card-title"><?php echo esc_html($card_data['title']); ?></h3>
                <?php if ($atts['show_rating'] === 'yes' && $card_data['rating']): ?>
                <div class="ccm-card-rating">
                    <div class="ccm-rating-stars">
                        <?php echo str_repeat('⭐', floor($card_data['rating'])); ?>
                    </div>
                    <span class="ccm-rating-text">(<?php echo esc_html($card_data['rating']); ?>/5)</span>
                </div>
                <?php endif; ?>
            </header>

            <div class="ccm-card-content ccm-card-content-mini">
                <div class="ccm-card-image-container">
                    <?php if ($card_data['image_url']): ?>
                        <img src="<?php echo esc_url($card_data['image_url']); ?>"
                             alt="<?php echo esc_attr($card_data['title']); ?>"
                             class="ccm-card-image"
                             loading="lazy">
                    <?php else: ?>
                        <div class="ccm-card-image-placeholder">
                            <span class="ccm-card-image-icon">💳</span>
                            <small>No Image</small>
                        </div>
                    <?php endif; ?>

                    <label class="ccm-compare-label">
                        <input type="checkbox" class="ccm-compare-checkbox" data-id="<?php echo esc_attr($card_data['id']); ?>">
                        <span>Compare</span>
                    </label>
                </div>

                <div class="ccm-card-details">
                    <?php if ($card_data['joining_fee']): ?>
                    <div class="ccm-highlight">
                        <span class="ccm-highlight-label">Joining Fee</span>
                        <span class="ccm-highlight-value">₹<?php echo esc_html(number_format($card_data['joining_fee'])); ?> + GST</span>
                    </div>
                    <?php endif; ?>

                    <?php if ($card_data['annual_fee']): ?>
                    <div class="ccm-highlight">
                        <span class="ccm-highlight-label">Renewal Fee</span>
                        <span class="ccm-highlight-value">₹<?php echo esc_html(number_format($card_data['annual_fee'])); ?> + GST</span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($card_data['best_for'])): ?>
                    <div class="ccm-highlight">
                        <span class="ccm-highlight-label">Best For</span>
                        <span class="ccm-highlight-value"><?php echo esc_html(implode(' | ', array_slice($card_data['best_for'], 0, 2))); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($card_data['welcome_bonus_points'] || $card_data['welcome_bonus']): ?>
                    <div class="ccm-highlight">
                        <span class="ccm-highlight-label">Welcome Bonus</span>
                        <span class="ccm-highlight-value"><?php echo esc_html($card_data['welcome_bonus_points'] ? $card_data['welcome_bonus_points'] . ' ' . ($card_data['welcome_bonus_type'] ?: 'NeuCoins') : $card_data['welcome_bonus']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($card_data['apply_link']): ?>
                <div class="ccm-card-actions">
                    <a href="<?php echo esc_url($card_data['apply_link']); ?>"
                       class="ccm-btn ccm-btn-primary"
                       target="_blank"
                       rel="noopener">Apply Now</a>
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php
    } else {
        // Full mode using existing CSS classes with collapsible sections
        ?>
        <div class="cc-card cc-card-shortcode-full" data-id="<?php echo esc_attr($card_data['id']); ?>">
            <!-- Full width header -->
            <div class="cc-card-header" style="background: var(--cc-gray-50); border-bottom: 1px solid var(--cc-gray-200); padding: 1.25rem;">
                <h3 class="cc-card-title" style="margin: 0 0 0.5rem 0; font-size: 1.5rem;"><?php echo esc_html($card_data['title']); ?></h3>
                <?php if ($atts['show_rating'] === 'yes' && $card_data['rating']): ?>
                <div class="cc-card-rating">
                    <span style="color: var(--cc-yellow-400);"><?php echo str_repeat('⭐', floor($card_data['rating'])); ?></span>
                    <span class="cc-card-rating-text">(<?php echo esc_html($card_data['rating']); ?>/5)</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Content body with image and details -->
            <div class="cc-card-content" style="display: grid; grid-template-columns: auto 1fr auto; gap: 1.5rem; align-items: start;">
                <div class="cc-card-image" style="width: 140px; height: auto; background: none; padding: 0; text-align: center;">
                    <?php if ($card_data['image_url']): ?>
                        <img src="<?php echo esc_url($card_data['image_url']); ?>" 
                             alt="<?php echo esc_attr($card_data['title']); ?>" 
                             style="width: 100%; height: auto; border-radius: var(--cc-radius); object-fit: cover;" />
                    <?php else: ?>
                        <div style="width: 100%; height: 100px; border: 2px dashed var(--cc-gray-300); border-radius: var(--cc-radius); display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--cc-gray-50); color: var(--cc-gray-400);">
                            <span style="font-size: 2em; margin-bottom: 4px;">💳</span>
                            <small style="font-size: 0.8em;">No Image</small>
                        </div>
                    <?php endif; ?>
                    <label class="cc-btn-compare" 
                           data-id="<?php echo esc_attr($card_data['id']); ?>"
                           data-title="<?php echo esc_attr($card_data['title']); ?>"
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: var(--cc-gray-50); border: 1px solid var(--cc-gray-200); border-radius: var(--cc-radius); cursor: pointer; font-size: 0.875rem; margin-top: 0.5rem;">
                        <input type="checkbox" style="margin: 0; width: 16px; height: 16px;">
                        <span>Compare</span>
                    </label>
                </div>

                <div style="display: grid; gap: 1rem; min-width: 300px;">
                    <?php if ($card_data['joining_fee']): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--cc-blue-600); font-weight: 500;">Joining Fee</span>
                        <span style="color: var(--cc-gray-800); font-weight: 600;">₹<?php echo esc_html(number_format($card_data['joining_fee'])); ?> + GST</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($card_data['annual_fee']): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--cc-blue-600); font-weight: 500;">Renewal Fee</span>
                        <span style="color: var(--cc-gray-800); font-weight: 600;">₹<?php echo esc_html(number_format($card_data['annual_fee'])); ?> + GST</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($card_data['best_for'])): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--cc-blue-600); font-weight: 500;">Best Suited For</span>
                        <span style="color: var(--cc-gray-800); font-weight: 600;"><?php echo esc_html(implode(' | ', array_slice($card_data['best_for'], 0, 2))); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($card_data['cashback_rate'] || $card_data['welcome_bonus_type']): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--cc-blue-600); font-weight: 500;">Reward Type</span>
                        <span style="background: var(--cc-blue-600); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8em; font-weight: 500;"><?php echo esc_html($card_data['welcome_bonus_type'] ?: 'NeuCoins'); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($card_data['welcome_bonus_points'] || $card_data['welcome_bonus']): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--cc-blue-600); font-weight: 500;">Welcome Benefits</span>
                        <span style="color: var(--cc-gray-800); font-weight: 600;"><?php echo esc_html($card_data['welcome_bonus_points'] ? $card_data['welcome_bonus_points'] . ' ' . ($card_data['welcome_bonus_type'] ?: 'NeuCoins') : $card_data['welcome_bonus']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <?php if ($card_data['apply_link']): ?>
                    <a href="<?php echo esc_url($card_data['apply_link']); ?>" 
                       class="cc-btn cc-btn-apply" 
                       target="_blank" 
                       rel="noopener"
                       style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        Apply Now <span style="font-size: 0.8em;">✈</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Collapsible Sections -->
            <div style="border-top: 1px solid var(--cc-gray-200);">
                <?php if (!empty($card_data['pros']) || !empty($card_data['cons']) || $card_data['cashback_rate'] || $card_data['welcome_bonus']): ?>
                <div style="border-bottom: 1px solid var(--cc-gray-200);">
                    <div class="cc-section-header" onclick="ccToggleSection(this)" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; cursor: pointer; background: var(--cc-gray-50); transition: background-color 0.2s;">
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--cc-gray-800);">Rewards and Benefits</h4>
                        <span class="cc-toggle-icon" style="font-size: 0.8rem; color: var(--cc-gray-500); transition: transform 0.2s;">▼</span>
                    </div>
                    <div class="cc-section-content" style="padding: 1.5rem; display: none; background: white;">
                        <?php if ($card_data['cashback_rate']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Reward Rate:</strong> <?php echo esc_html($card_data['cashback_rate']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['welcome_bonus']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Welcome Bonus:</strong> <?php echo esc_html($card_data['welcome_bonus']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($card_data['pros'])): ?>
                        <div style="margin-bottom: 1.25rem;">
                            <strong>Key Benefits:</strong>
                            <ul style="margin: 0.5rem 0; padding-left: 0; list-style: none;">
                                <?php foreach ($card_data['pros'] as $pro): ?>
                                <li style="padding: 0.25rem 0; font-size: 0.9rem; color: var(--cc-green-500);">✓ <?php echo esc_html($pro); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="border-bottom: 1px solid var(--cc-gray-200);">
                    <div class="cc-section-header" onclick="ccToggleSection(this)" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; cursor: pointer; background: var(--cc-gray-50); transition: background-color 0.2s;">
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--cc-gray-800);">Fees & Charges</h4>
                        <span class="cc-toggle-icon" style="font-size: 0.8rem; color: var(--cc-gray-500); transition: transform 0.2s;">▼</span>
                    </div>
                    <div class="cc-section-content" style="padding: 1.5rem; display: none; background: white;">
                        <?php if ($card_data['joining_fee']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Joining Fee:</strong> ₹<?php echo esc_html(number_format($card_data['joining_fee'])); ?> + GST
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['annual_fee']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Annual Fee:</strong> ₹<?php echo esc_html(number_format($card_data['annual_fee'])); ?> + GST
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['interest_rate']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Interest Rate:</strong> <?php echo esc_html($card_data['interest_rate']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($card_data['credit_limit'] || $card_data['min_income'] || $card_data['processing_time']): ?>
                <div style="border-bottom: 1px solid var(--cc-gray-200);">
                    <div class="cc-section-header" onclick="ccToggleSection(this)" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; cursor: pointer; background: var(--cc-gray-50); transition: background-color 0.2s;">
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--cc-gray-800);">Product Details</h4>
                        <span class="cc-toggle-icon" style="font-size: 0.8rem; color: var(--cc-gray-500); transition: transform 0.2s;">▼</span>
                    </div>
                    <div class="cc-section-content" style="padding: 1.5rem; display: none; background: white;">
                        <?php if ($card_data['credit_limit']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Credit Limit:</strong> <?php echo esc_html($card_data['credit_limit']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['min_income']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Minimum Income:</strong> <?php echo esc_html($card_data['min_income']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['processing_time']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Processing Time:</strong> <?php echo esc_html($card_data['processing_time']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['network']): ?>
                        <div style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--cc-gray-700);">
                            <strong>Network:</strong> <?php echo esc_html($card_data['network']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($card_data['pros']) || !empty($card_data['cons'])): ?>
                <div>
                    <div class="cc-section-header" onclick="ccToggleSection(this)" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; cursor: pointer; background: var(--cc-gray-50); transition: background-color 0.2s;">
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--cc-gray-800);">Pros/Cons</h4>
                        <span class="cc-toggle-icon" style="font-size: 0.8rem; color: var(--cc-gray-500); transition: transform 0.2s;">▼</span>
                    </div>
                    <div class="cc-section-content" style="padding: 1.5rem; display: none; background: white;">
                        <?php if (!empty($card_data['pros'])): ?>
                        <div style="margin-bottom: 1.25rem;">
                            <strong>Pros:</strong>
                            <ul style="margin: 0.5rem 0; padding-left: 0; list-style: none;">
                                <?php foreach ($card_data['pros'] as $pro): ?>
                                <li style="padding: 0.25rem 0; font-size: 0.9rem; color: var(--cc-green-500);">✓ <?php echo esc_html($pro); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($card_data['cons'])): ?>
                        <div style="margin-bottom: 1.25rem;">
                            <strong>Cons:</strong>
                            <ul style="margin: 0.5rem 0; padding-left: 0; list-style: none;">
                                <?php foreach ($card_data['cons'] as $con): ?>
                                <li style="padding: 0.25rem 0; font-size: 0.9rem; color: var(--cc-red-500);">✗ <?php echo esc_html($con); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    // Add comparison bar if it doesn't exist
    if (!wp_script_is('comparison-bar-added')) {
        echo '<div class="cc-comparison-bar" id="comparison-bar" style="position: fixed; bottom: 0; left: 0; right: 0; background-color: var(--cc-gray-800); color: var(--cc-white); padding: 1rem; z-index: 100; box-shadow: var(--cc-shadow-lg); transform: translateY(100%); transition: transform 0.3s ease;">
            <div class="cc-comparison-content" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
                <div class="cc-comparison-info" style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 1.2em;">⚖️</span>
                    <div>
                        <span id="selected-count">0</span> cards selected for comparison
                    </div>
                </div>
                <div class="cc-comparison-actions" style="display: flex; gap: 0.75rem;">
                    <button type="button" class="cc-btn cc-btn-secondary" id="clear-comparison" style="padding: 0.5rem 1rem; background: transparent; color: var(--cc-white); border: 1px solid var(--cc-gray-300);" onclick="ccClearComparison()">
                        Clear All
                    </button>
                    <button type="button" class="cc-btn cc-btn-primary" id="compare-now" disabled style="padding: 0.5rem 1rem; background: var(--cc-blue-600); color: var(--cc-white); border: none;" onclick="ccHandleCompareNow()">
                        Compare Now
                    </button>
                </div>
            </div>
        </div>';
        wp_add_inline_script('credit-card-frontend', 'window.comparisonBarAdded = true;', 'before');
    }

    // Add simple CSS for mobile responsiveness
    ?>
    <style>
    .cc-card-shortcode-mini .cc-card-content,
    .cc-card-shortcode-full .cc-card-content {
        margin: 20px 0;
    }
    
    @media (max-width: 768px) {
        .cc-card-shortcode-mini .cc-card-content {
            flex-direction: column;
            text-align: center;
        }
        .cc-card-shortcode-mini .cc-card-content > div:last-child {
            position: static !important;
            margin-top: 1rem;
            justify-content: center;
        }
        .cc-card-shortcode-mini .cc-card-content > div:last-child > button,
        .cc-card-shortcode-mini .cc-card-content > div:last-child > a {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        .cc-card-shortcode-full .cc-card-content {
            grid-template-columns: 1fr !important;
            text-align: center;
        }
        .cc-card-shortcode-full .cc-card-content > div:last-child {
            justify-content: center;
        }
        .cc-card-shortcode-full .cc-card-content > div:last-child > button,
        .cc-card-shortcode-full .cc-card-content > div:last-child > a {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    }
    </style>

    <script>
    function ccToggleSection(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('.cc-toggle-icon');
        
        if (content.style.display === 'block') {
            content.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
            header.style.background = 'var(--cc-gray-50)';
        } else {
            content.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
            header.style.background = 'var(--cc-gray-100)';
        }
    }

    // Initialize comparison for shortcode buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Handle shortcode compare buttons with checkboxes
        const shortcodeCompareButtons = document.querySelectorAll('.cc-card-shortcode-mini .cc-btn-compare, .cc-card-shortcode-full .cc-btn-compare');
        
        shortcodeCompareButtons.forEach(function(label) {
            const checkbox = label.querySelector('input[type="checkbox"]');
            const cardId = label.getAttribute('data-id');
            
            if (checkbox && cardId) {
                // Check if card is already selected
                const savedCards = localStorage.getItem('cc_compare_cards');
                if (savedCards) {
                    try {
                        const selectedCards = JSON.parse(savedCards);
                        if (selectedCards.includes(cardId)) {
                            checkbox.checked = true;
                        }
                    } catch (e) {
                        console.error('Error loading saved comparison data', e);
                    }
                }
                
                // Add click handler to label
                label.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Toggle checkbox
                    checkbox.checked = !checkbox.checked;
                    
                    // Trigger comparison update
                    const event = new CustomEvent('ccCompareToggle', {
                        detail: { cardId: cardId, selected: checkbox.checked }
                    });
                    document.dispatchEvent(event);
                });
            }
        });
        
        // Update comparison bar when shortcode buttons are toggled
        document.addEventListener('ccCompareToggle', function(e) {
            const { cardId, selected } = e.detail;
            let selectedCards = [];
            
            // Load existing selections
            const saved = localStorage.getItem('cc_compare_cards');
            if (saved) {
                try {
                    selectedCards = JSON.parse(saved);
                } catch (err) {
                    selectedCards = [];
                }
            }
            
            if (selected && !selectedCards.includes(cardId)) {
                if (selectedCards.length < 3) {
                    selectedCards.push(cardId);
                } else {
                    alert('You can only compare up to 3 cards at once.');
                    // Uncheck the checkbox
                    const label = document.querySelector(`.cc-btn-compare[data-id="${cardId}"]`);
                    if (label) {
                        const checkbox = label.querySelector('input[type="checkbox"]');
                        if (checkbox) checkbox.checked = false;
                    }
                    return;
                }
            } else if (!selected && selectedCards.includes(cardId)) {
                selectedCards = selectedCards.filter(id => id !== cardId);
            }
            
            // Save updated selections
            localStorage.setItem('cc_compare_cards', JSON.stringify(selectedCards));
            
            // Update comparison bar
            const comparisonBar = document.getElementById('comparison-bar');
            const selectedCountEl = document.getElementById('selected-count');
            const compareNowBtn = document.getElementById('compare-now');
            
            if (comparisonBar && selectedCountEl) {
                if (selectedCards.length > 0) {
                    comparisonBar.style.transform = 'translateY(0)';
                    selectedCountEl.textContent = selectedCards.length;
                    if (compareNowBtn) {
                        compareNowBtn.disabled = selectedCards.length < 2;
                    }
                } else {
                    comparisonBar.style.transform = 'translateY(100%)';
                }
            }
        });
    });

    // Handle clear comparison
    function ccClearComparison() {
        localStorage.removeItem('cc_compare_cards');
        
        // Uncheck all checkboxes
        const checkboxes = document.querySelectorAll('.cc-btn-compare input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        
        // Hide comparison bar
        const comparisonBar = document.getElementById('comparison-bar');
        if (comparisonBar) {
            comparisonBar.style.transform = 'translateY(100%)';
        }
    }

    // Handle compare now
    function ccHandleCompareNow() {
        const savedCards = localStorage.getItem('cc_compare_cards');
        if (savedCards) {
            try {
                const selectedCards = JSON.parse(savedCards);
                if (selectedCards.length >= 2) {
                    // Try to get the current page URL or archive URL
                    let baseUrl = window.location.origin + window.location.pathname;
                    
                    // If we're on a post with shortcode, try to find the credit card archive URL
                    const archiveContainer = document.querySelector('.cc-archive-container');
                    if (archiveContainer && archiveContainer.getAttribute('data-archive-url')) {
                        baseUrl = archiveContainer.getAttribute('data-archive-url');
                    } else {
                        // Use WordPress site URL with credit card post type archive
                        baseUrl = window.location.origin + '/credit-card/';
                    }
                    
                    const compareUrl = baseUrl + '?compare=' + selectedCards.join(',');
                    window.location.href = compareUrl;
                }
            } catch (e) {
                console.error('Error loading comparison data', e);
            }
        }
    }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('credit-card', 'ccm_register_credit_card_shortcode');

/**
 * Register the [credit_card_grid] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode output.
 */
function ccm_register_credit_card_grid_shortcode($atts) {
    $atts = shortcode_atts(
        [
            'count' => '6',
            'bank' => '',
            'category' => '',
            'network_type' => '',
            'featured' => '',
            'min_rating' => '',
            'max_annual_fee' => '',
            'sort_by' => 'rating',
            'sort_order' => 'desc',
            'show_filters' => 'yes',
        ],
        $atts,
        'credit_card_grid'
    );

    // Build query args
    $args = [
        'post_type' => 'credit-card',
        'posts_per_page' => intval($atts['count']),
        'meta_query' => [],
        'tax_query' => [],
    ];

    // Add filters based on shortcode attributes
    if (!empty($atts['bank'])) {
        $args['tax_query'][] = [
            'taxonomy' => 'store',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['bank']),
        ];
    }

    if (!empty($atts['category'])) {
        $args['tax_query'][] = [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['category']),
        ];
    }

    if (!empty($atts['network_type'])) {
        $args['tax_query'][] = [
            'taxonomy' => 'network-type',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['network_type']),
        ];
    }

    if (!empty($atts['min_rating'])) {
        $args['meta_query'][] = [
            'key' => 'rating',
            'value' => floatval($atts['min_rating']),
            'compare' => '>=',
            'type' => 'DECIMAL',
        ];
    }

    if (!empty($atts['max_annual_fee'])) {
        $args['meta_query'][] = [
            'key' => 'annual_fee_numeric',
            'value' => intval($atts['max_annual_fee']),
            'compare' => '<=',
            'type' => 'NUMERIC',
        ];
    }

    if ($atts['featured'] === '1') {
        $args['meta_query'][] = [
            'key' => 'featured',
            'value' => '1',
            'compare' => '=',
        ];
    }

    // Set relations for queries
    if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
        $args['meta_query']['relation'] = 'AND';
    }

    if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
        $args['tax_query']['relation'] = 'AND';
    }

    // Sorting
    switch ($atts['sort_by']) {
        case 'rating':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($atts['sort_order']);
            break;
        case 'annual_fee':
            $args['meta_key'] = 'annual_fee_numeric';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($atts['sort_order']);
            break;
        case 'review_count':
            $args['meta_key'] = 'review_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($atts['sort_order']);
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = strtoupper($atts['sort_order']);
    }

    // Query the cards
    $credit_cards = new WP_Query($args);

    // Generate unique ID for this shortcode instance
    $shortcode_id = 'ccm-grid-' . uniqid();

    ob_start();
    ?>
    <div class="ccm-shortcode-grid" id="<?php echo esc_attr($shortcode_id); ?>">
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <!-- Simple Filters -->
        <div class="ccm-filters" style="background: white; padding: 1rem; margin-bottom: 1rem; border: 1px solid #e5e7eb; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
                <select class="ccm-filter" data-filter="bank" style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Banks</option>
                    <!-- Populated via JavaScript -->
                </select>
                <select class="ccm-filter" data-filter="category" style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Categories</option>
                    <!-- Populated via JavaScript -->
                </select>
                <select class="ccm-filter" data-filter="sort" style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="rating-desc">Highest Rated</option>
                    <option value="rating-asc">Lowest Rated</option>
                    <option value="annual_fee-asc">Lowest Fee</option>
                    <option value="annual_fee-desc">Highest Fee</option>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Grid -->
        <div class="ccm-cards-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
            <?php if ($credit_cards->have_posts()): ?>
                <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                    $post_id = get_the_ID();
                    $card_image = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'medium') : '';
                    
                    $rating = ccm_get_meta($post_id, 'rating', 0, true);
                    $review_count = ccm_get_meta($post_id, 'review_count', 0, true);
                    $annual_fee = ccm_format_currency(ccm_get_meta($post_id, 'annual_fee', 'N/A'));
                    $cashback_rate = ccm_get_meta($post_id, 'cashback_rate', 'N/A');
                    $welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', 'N/A');
                    $apply_link = ccm_get_meta($post_id, 'apply_link', get_permalink());
                    $featured = (bool) ccm_get_meta($post_id, 'featured', false);
                    
                    $bank_terms = get_the_terms($post_id, 'store');
                    $bank_name = !is_wp_error($bank_terms) && !empty($bank_terms) ? $bank_terms[0]->name : '';
                    
                    $pros = ccm_get_meta($post_id, 'pros', [], false, true);
                ?>
                <div class="ccm-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; text-align: center; position: relative;">
                    <!-- Compare Checkbox -->
                    
                    <?php if ($featured): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <span style="background: #f59e0b; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem;">
                                ⭐ Featured
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($card_image)): ?>
                        <div style="margin-bottom: 0.75rem;">
                            <img src="<?php echo esc_url($card_image); ?>" alt="<?php the_title(); ?>" style="width: 100px; height: auto; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                    
                    <label class="ccm-btn-compare" data-id="<?php echo esc_attr($post_id); ?>" data-title="<?php echo esc_attr(get_the_title()); ?>" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; cursor: pointer; font-size: 0.75rem; background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px; border: 1px solid #d1d5db; margin-bottom: 0.75rem;">
                        <input type="checkbox" style="margin: 0; width: 14px; height: 14px;">
                        <span>Compare</span>
                    </label>
                    
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600;"><?php the_title(); ?></h3>
                    
                    <?php if (!empty($bank_name)): ?>
                        <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo esc_html($bank_name); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($rating > 0): ?>
                        <div style="margin-bottom: 0.75rem; color: #f59e0b;">
                            <?php echo str_repeat('⭐', floor($rating)); ?>
                            <span style="font-size: 0.75rem; color: #6b7280; margin-left: 0.25rem;">
                                <?php echo esc_html($rating); ?>/5
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem; font-size: 0.8rem;">
                        <div style="padding: 0.5rem; background: #f9fafb; border-radius: 6px;">
                            <div style="color: #6b7280; margin-bottom: 0.25rem;">Annual Fee</div>
                            <div style="font-weight: 600;"><?php echo esc_html($annual_fee); ?></div>
                        </div>
                        <div style="padding: 0.5rem; background: #f9fafb; border-radius: 6px;">
                            <div style="color: #6b7280; margin-bottom: 0.25rem;">Rewards</div>
                            <div style="font-weight: 600;"><?php echo esc_html($cashback_rate); ?></div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <a href="<?php the_permalink(); ?>" style="padding: 0.5rem; text-align: center; background: #f3f4f6; color: #374151; border-radius: 6px; text-decoration: none; font-size: 0.875rem;">
                            Details
                        </a>
                        <a href="<?php echo esc_url($apply_link); ?>" target="_blank" rel="noopener" style="padding: 0.5rem; text-align: center; background: #3b82f6; color: white; border-radius: 6px; text-decoration: none; font-size: 0.875rem;">
                            Apply
                        </a>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #6b7280;">
                    <p>No credit cards found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Comparison Bar for Shortcode -->
        <div class="ccm-comparison-bar" id="<?php echo esc_attr($shortcode_id); ?>-comparison-bar" style="position: fixed; bottom: 0; left: 0; right: 0; background: #1f2937; color: white; padding: 0.75rem; z-index: 1000; transform: translateY(100%); transition: transform 0.3s ease;">
            <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span>⚖️</span>
                    <div style="font-size: 0.875rem;">
                        <span class="selected-count">0</span> cards selected
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" class="clear-comparison" style="padding: 0.5rem 1rem; background: transparent; color: white; border: 1px solid #374151; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">
                        Clear
                    </button>
                    <button type="button" class="compare-now" disabled style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">
                        Compare
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    #<?php echo esc_attr($shortcode_id); ?> .ccm-card:hover {
        border-color: #d1d5db;
    }
    #<?php echo esc_attr($shortcode_id); ?> .ccm-btn-compare.active {
        background: #3b82f6 !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }
    </style>
    <?php

    // Add JavaScript for comparison functionality
    ?>
    <script>
    (function() {
        const shortcodeId = '<?php echo esc_js($shortcode_id); ?>';
        const container = document.getElementById(shortcodeId);
        if (!container) return;
        
        const comparisonBar = document.getElementById(shortcodeId + '-comparison-bar');
        const compareButtons = container.querySelectorAll('.ccm-btn-compare');
        const clearBtn = comparisonBar.querySelector('.clear-comparison');
        const compareNowBtn = comparisonBar.querySelector('.compare-now');
        const selectedCountEl = comparisonBar.querySelector('.selected-count');
        
        let selectedCards = [];
        const maxCompare = 3;
        const storageKey = 'ccm_shortcode_compare_' + shortcodeId;
        
        // Load saved selections
        function loadSelectedCards() {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                try {
                    selectedCards = JSON.parse(saved);
                    updateUI();
                } catch (e) {
                    selectedCards = [];
                }
            }
        }
        
        // Save selections
        function saveSelectedCards() {
            localStorage.setItem(storageKey, JSON.stringify(selectedCards));
        }
        
        // Update UI
        function updateUI() {
            compareButtons.forEach(btn => {
                const cardId = btn.getAttribute('data-id');
                const checkbox = btn.querySelector('input[type="checkbox"]');
                const isSelected = selectedCards.includes(cardId);
                
                checkbox.checked = isSelected;
                btn.classList.toggle('active', isSelected);
            });
            
            // Update comparison bar
            if (selectedCards.length > 0) {
                comparisonBar.style.transform = 'translateY(0)';
                selectedCountEl.textContent = selectedCards.length;
                compareNowBtn.disabled = selectedCards.length < 2;
            } else {
                comparisonBar.style.transform = 'translateY(100%)';
            }
        }
        
        // Toggle card selection
        function toggleCardSelection(cardId) {
            const index = selectedCards.indexOf(cardId);
            
            if (index > -1) {
                selectedCards.splice(index, 1);
            } else {
                if (selectedCards.length < maxCompare) {
                    selectedCards.push(cardId);
                } else {
                    alert(`You can only compare up to ${maxCompare} cards at once.`);
                    return;
                }
            }
            
            saveSelectedCards();
            updateUI();
        }
        
        // Initialize
        loadSelectedCards();
        
        // Add event listeners
        compareButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const cardId = this.getAttribute('data-id');
                toggleCardSelection(cardId);
            });
        });
        
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                selectedCards = [];
                saveSelectedCards();
                updateUI();
            });
        }
        
        if (compareNowBtn) {
            compareNowBtn.addEventListener('click', function() {
                if (selectedCards.length >= 2) {
                    // Redirect to comparison page
                    const compareUrl = window.location.origin + '/compare-cards?cards=' + selectedCards.join(',');
                    window.location.href = compareUrl;
                }
            });
        }
    })();
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('credit_card_grid', 'ccm_register_credit_card_grid_shortcode');
