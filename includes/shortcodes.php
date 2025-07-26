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
        'image_url' => get_post_meta($card_id, 'card_image_url', true) ?: (has_post_thumbnail($card_id) ? get_the_post_thumbnail_url($card_id, 'large') : ''),
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

    // Enqueue existing CSS and JS
    wp_enqueue_style('credit-card-frontend', plugin_dir_url(__FILE__) . '../assets/frontend.css', [], CCM_VERSION);
    wp_enqueue_script('credit-card-frontend', plugin_dir_url(__FILE__) . '../assets/frontend.js', ['jquery'], CCM_VERSION, true);

    ob_start();
    
    if ($atts['mode'] === 'mini') {
        // Mini mode using existing CSS classes
        ?>
        <div class="cc-card cc-card-shortcode-mini" data-card-id="<?php echo esc_attr($card_data['id']); ?>">
            <!-- Full width header -->
            <div class="cc-card-header" style="background: var(--cc-gray-50); border-bottom: 1px solid var(--cc-gray-200); padding: 1.25rem;">
                <h3 class="cc-card-title" style="margin: 0 0 0.5rem 0; font-size: 1.25rem;"><?php echo esc_html($card_data['title']); ?></h3>
                <?php if ($atts['show_rating'] === 'yes' && $card_data['rating']): ?>
                <div class="cc-card-rating">
                    <span style="color: var(--cc-yellow-400);"><?php echo str_repeat('⭐', floor($card_data['rating'])); ?></span>
                    <span class="cc-card-rating-text">(<?php echo esc_html($card_data['rating']); ?>/5)</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Content body -->
            <div class="cc-card-content" style="display: flex; gap: 1.5rem; align-items: flex-start; position: relative;">
                <div class="cc-card-image" style="flex-shrink: 0; width: 140px; height: auto; background: none; padding: 0;">
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
                </div>
                
                <div style="flex: 1; min-width: 0;">
                    <div style="display: grid; gap: 0.75rem;">
                        <?php if ($card_data['joining_fee']): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--cc-blue-600); font-weight: 500; font-size: 0.95em;">Joining Fee</span>
                            <span style="color: var(--cc-gray-800); font-weight: 600; font-size: 0.95em;">₹<?php echo esc_html(number_format($card_data['joining_fee'])); ?> + GST</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['annual_fee']): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--cc-blue-600); font-weight: 500; font-size: 0.95em;">Renewal Fee</span>
                            <span style="color: var(--cc-gray-800); font-weight: 600; font-size: 0.95em;">₹<?php echo esc_html(number_format($card_data['annual_fee'])); ?> + GST</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($card_data['best_for'])): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--cc-blue-600); font-weight: 500; font-size: 0.95em;">Best Suited For</span>
                            <span style="color: var(--cc-gray-800); font-weight: 600; font-size: 0.95em;"><?php echo esc_html(implode(' | ', array_slice($card_data['best_for'], 0, 2))); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['cashback_rate'] || $card_data['welcome_bonus_type']): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--cc-blue-600); font-weight: 500; font-size: 0.95em;">Reward Type</span>
                            <span style="background: var(--cc-blue-600); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8em; font-weight: 500;"><?php echo esc_html($card_data['welcome_bonus_type'] ?: 'NeuCoins'); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($card_data['welcome_bonus_points'] || $card_data['welcome_bonus']): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--cc-blue-600); font-weight: 500; font-size: 0.95em;">Welcome Benefits</span>
                            <span style="color: var(--cc-gray-800); font-weight: 600; font-size: 0.95em;"><?php echo esc_html($card_data['welcome_bonus_points'] ? $card_data['welcome_bonus_points'] . ' ' . ($card_data['welcome_bonus_type'] ?: 'NeuCoins') : $card_data['welcome_bonus']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($card_data['apply_link']): ?>
                <div style="position: absolute; top: 0; right: 0;">
                    <a href="<?php echo esc_url($card_data['apply_link']); ?>" 
                       class="cc-btn cc-btn-apply" 
                       target="_blank" 
                       rel="noopener"
                       style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        Apply Now <span style="font-size: 0.8em;">✈</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    } else {
        // Full mode using existing CSS classes with collapsible sections
        ?>
        <div class="cc-card cc-card-shortcode-full" data-card-id="<?php echo esc_attr($card_data['id']); ?>">
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
                <div class="cc-card-image" style="width: 140px; height: auto; background: none; padding: 0;">
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

                <?php if ($card_data['apply_link']): ?>
                <div>
                    <a href="<?php echo esc_url($card_data['apply_link']); ?>" 
                       class="cc-btn cc-btn-apply" 
                       target="_blank" 
                       rel="noopener"
                       style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        Apply Now <span style="font-size: 0.8em;">✈</span>
                    </a>
                </div>
                <?php endif; ?>
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
        }
        .cc-card-shortcode-full .cc-card-content {
            grid-template-columns: 1fr !important;
            text-align: center;
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
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('credit-card', 'ccm_register_credit_card_shortcode');
