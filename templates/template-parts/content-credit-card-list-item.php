<?php
/**
 * Template part for displaying a credit card in a list format.
 *
 * @package CreditCardManager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get formatted card data using the plugin's helper function
$card = ccm_get_credit_card(get_the_ID());
?>

<div class="ccm-card-list-item" data-id="<?php echo esc_attr($card['id']); ?>">
    <div class="ccm-card-list-image">
        <a href="<?php echo esc_url($card['link']); ?>">
            <img src="<?php echo esc_url($card['card_image']); ?>" alt="<?php echo esc_attr($card['title']); ?>" onerror="this.style.display='none'">
        </a>
    </div>
    <div class="ccm-card-list-content">
        <h3 class="ccm-card-list-title">
            <a href="<?php echo esc_url($card['link']); ?>"><?php echo esc_html($card['title']); ?></a>
        </h3>
        <div class="ccm-card-list-meta">
            <span>
                <strong><?php _e('Bank:', 'credit-card-manager'); ?></strong> 
                <?php echo $card['bank'] ? esc_html($card['bank']['name']) : __('N/A', 'credit-card-manager'); ?>
            </span>
            <span>
                <strong><?php _e('Rating:', 'credit-card-manager'); ?></strong> 
                ⭐ <?php echo $card['rating'] ? esc_html($card['rating']) : __('N/A', 'credit-card-manager'); ?>
            </span>
        </div>
        <p>
            <strong><?php _e('Annual Fee:', 'credit-card-manager'); ?></strong> 
            <?php echo $card['annual_fee'] ? esc_html($card['annual_fee']) : __('N/A', 'credit-card-manager'); ?>
        </p>
        <div class="ccm-card-list-actions">
            <a href="<?php echo esc_url($card['link']); ?>" class="ccm-btn ccm-btn-details"><?php _e('View Details', 'credit-card-manager'); ?></a>
            <?php if (!empty($card['apply_link'])): ?>
                <a href="<?php echo esc_url($card['apply_link']); ?>" target="_blank" rel="noopener noreferrer" class="ccm-btn ccm-btn-apply"><?php _e('Apply Now', 'credit-card-manager'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>
