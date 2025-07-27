<?php
/**
 * Template part for displaying a single credit card item.
 *
 * @package Credit Card Manager
 * 
 * Available variables:
 * $post_id - The credit card post ID
 * $show_compare - Whether to show compare button (default: true)
 * $show_badges - Whether to show featured/trending badges (default: true)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Set defaults
$post_id = isset($post_id) ? $post_id : get_the_ID();
$show_compare = isset($show_compare) ? $show_compare : true;
$show_badges = isset($show_badges) ? $show_badges : true;

// Get card data using centralized functions
$card_image = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'medium') : '';
$rating = ccm_get_meta($post_id, 'rating', 0, true);
$review_count = ccm_get_meta($post_id, 'review_count', 0, true);
$annual_fee = ccm_format_currency(ccm_get_meta($post_id, 'annual_fee', 'N/A'));
$cashback_rate = ccm_get_meta($post_id, 'cashback_rate', 'N/A');
$welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', 'N/A');
$apply_link = ccm_get_meta($post_id, 'apply_link', '#');
$pros = ccm_get_meta($post_id, 'pros', [], false, true);
$featured = ccm_get_meta($post_id, 'featured', false);
$trending = ccm_get_meta($post_id, 'trending', false);

// Get bank name
$bank_name = ccm_get_card_bank($post_id);

// Use featured image if no card image URL
if (empty($card_image) && has_post_thumbnail($post_id)) {
    $card_image = get_the_post_thumbnail_url($post_id, 'medium');
}
?>

<article class="ccv2-card" data-id="<?php echo esc_attr($post_id); ?>">
    
    <!-- Card Header -->
    <header class="ccv2-card-header">
        <!-- Badges -->
        <?php if ($show_badges): ?>
            <div class="ccv2-card-badges">
                <?php if ($featured): ?>
                    <span class="ccv2-badge ccv2-badge-featured">
                        <?php echo ccm_get_icon('award', 'icon'); ?>
                        Featured
                    </span>
                <?php endif; ?>
                
                <?php if ($trending): ?>
                    <span class="ccv2-badge ccv2-badge-trending">
                        <?php echo ccm_get_icon('trending-up', 'icon'); ?>
                        Trending
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Card Image -->
        <div class="ccv2-card-image">
            <?php if (!empty($card_image)): ?>
                <img src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>">
            <?php endif; ?>
        </div>

        <!-- Card Title -->
        <h3 class="ccv2-card-title"><?php echo esc_html(get_the_title($post_id)); ?></h3>
        
        <?php if (!empty($bank_name) && $bank_name !== 'N/A'): ?>
            <div class="ccv2-card-bank"><?php echo esc_html($bank_name); ?></div>
        <?php endif; ?>
        
        <!-- Rating -->
        <?php if ($rating > 0): ?>
            <div class="ccv2-card-rating">
                <?php echo ccm_render_rating($rating, 5, true); ?>
                <?php if ($review_count > 0): ?>
                    <span class="ccv2-review-count">(<?php echo esc_html($review_count); ?> reviews)</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>
    
    <!-- Card Content -->
    <div class="ccv2-card-content">
        <!-- Key Highlights -->
        <div class="ccv2-card-highlights">
            <div class="ccv2-highlight">
                <?php echo ccm_get_icon('credit-card', 'ccv2-highlight-icon'); ?>
                <div class="ccv2-highlight-label">Annual Fee</div>
                <div class="ccv2-highlight-value"><?php echo esc_html($annual_fee); ?></div>
            </div>
            
            <div class="ccv2-highlight">
                <?php echo ccm_get_icon('percentage', 'ccv2-highlight-icon'); ?>
                <div class="ccv2-highlight-label">Reward Rate</div>
                <div class="ccv2-highlight-value"><?php echo esc_html($cashback_rate); ?></div>
            </div>
            
            <div class="ccv2-highlight" style="grid-column: 1 / -1;">
                <?php echo ccm_get_icon('gift', 'ccv2-highlight-icon'); ?>
                <div class="ccv2-highlight-label">Welcome Bonus</div>
                <div class="ccv2-highlight-value"><?php echo esc_html($welcome_bonus); ?></div>
            </div>
        </div>
        
        <!-- Key Features -->
        <?php if (!empty($pros) && is_array($pros)): ?>
            <div class="ccv2-card-features">
                <?php foreach (array_slice($pros, 0, 3) as $pro): ?>
                    <div class="ccv2-feature-item">
                        <?php echo ccm_get_icon('check', 'icon'); ?>
                        <span class="ccv2-feature-text"><?php echo esc_html($pro); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="ccv2-card-actions">
            <?php if ($show_compare): ?>
                <label class="cc-btn-compare" data-id="<?php echo esc_attr($post_id); ?>" data-title="<?php echo esc_attr(get_the_title()); ?>">
                    <input type="checkbox">
                    <span>Compare</span>
                </label>
            <?php endif; ?>
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="ccv2-btn ccv2-btn-details">
                <?php echo ccm_get_icon('info', 'icon'); ?>
                Details
            </a>
            <a href="<?php echo esc_url($apply_link); ?>" class="ccv2-btn ccv2-btn-apply" target="_blank" rel="noopener">
                Apply Now
                <?php echo ccm_get_icon('arrow-right', 'icon'); ?>
            </a>
        </div>
    </div>
</article>
