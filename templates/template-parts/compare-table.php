<?php
/**
 * Template part for displaying the credit card comparison table.
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Get card data passed from the parent template or shortcode
if (!isset($compare_cards)) {
    return;
}
?>

<section class="compare-table-container">
    <div class="compare-table-header">
        <h2 class="compare-table-title">Detailed Feature Comparison</h2>
    </div>
    
    <div class="compare-table-wrapper">
        <table class="compare-table">
            <thead>
                <tr>
                    <th style="min-width: 200px;">Features</th>
                    <?php foreach ($compare_cards as $card): ?>
                        <th style="min-width: 200px; text-align: center;">
                            <?php echo esc_html($card['title']); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <!-- Card Image Section -->
                <tr>
                    <td class="compare-feature-row">Card Image</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <td class="compare-value compare-card-image-cell">
                            <?php if (has_post_thumbnail($card['id'])): ?>
                                <?php echo get_the_post_thumbnail($card['id'], 'medium'); ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>

                <!-- Basic Information Section -->
                <tr class="compare-table-section">
                    <td colspan="<?php echo count($compare_cards) + 1; ?>">
                        <?php echo ccm_get_icon('info', 'icon'); ?> Basic Information
                    </td>
                </tr>
                
                <!-- Bank/Issuer -->
                <tr>
                    <td class="compare-feature-row">Bank/Issuer</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php 
                        $bank_terms = get_the_terms($card['id'], 'store');
                        $bank_name = !is_wp_error($bank_terms) && !empty($bank_terms) ? $bank_terms[0]->name : 'N/A';
                        ?>
                        <td class="compare-value"><?php echo esc_html($bank_name); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Network Type -->
                <tr>
                    <td class="compare-feature-row">Network Type</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php 
                        $network_terms = get_the_terms($card['id'], 'network-type');
                        if (!is_wp_error($network_terms) && !empty($network_terms)) {
                            $network_names = wp_list_pluck($network_terms, 'name');
                            $network_name = implode(', ', $network_names);
                        } else {
                            $network_name = 'N/A';
                        }
                        ?>
                        <td class="compare-value"><?php echo esc_html($network_name); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Rating -->
                <tr>
                    <td class="compare-feature-row">Customer Rating</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $rating = ccm_get_meta($card['id'], 'rating', 0, true); ?>
                        <td class="compare-value">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <?php echo ccm_get_icon('star', 'icon'); ?>
                                <span><?php echo esc_html($rating); ?>/5</span>
                            </div>
                        </td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Fees Section -->
                <tr class="compare-table-section">
                    <td colspan="<?php echo count($compare_cards) + 1; ?>">
                        <?php echo ccm_get_icon('dollar-sign', 'icon'); ?> Fees & Charges
                    </td>
                </tr>
                
                <!-- Annual Fee -->
                <tr>
                    <td class="compare-feature-row">Annual Fee</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $annual_fee = ccm_get_meta($card['id'], 'annual_fee', 'N/A'); ?>
                        <td class="compare-value <?php echo ($annual_fee === 'Free' || $annual_fee === '0') ? 'positive' : 'negative'; ?>">
                            <?php echo esc_html(ccm_format_currency($annual_fee)); ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Joining Fee -->
                <tr>
                    <td class="compare-feature-row">Joining Fee</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $joining_fee = ccm_get_meta($card['id'], 'joining_fee', 'N/A'); ?>
                        <td class="compare-value <?php echo ($joining_fee === 'Free' || $joining_fee === '0') ? 'positive' : 'negative'; ?>">
                            <?php echo esc_html(ccm_format_currency($joining_fee)); ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Rewards Section -->
                <tr class="compare-table-section">
                    <td colspan="<?php echo count($compare_cards) + 1; ?>">
                        <?php echo ccm_get_icon('gift', 'icon'); ?> Rewards & Benefits
                    </td>
                </tr>
                
                <!-- Welcome Bonus -->
                <tr>
                    <td class="compare-feature-row">Welcome Bonus</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $welcome_bonus = ccm_get_meta($card['id'], 'welcome_bonus', 'N/A'); ?>
                        <td class="compare-value highlight"><?php echo esc_html($welcome_bonus); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Reward Rate -->
                <tr>
                    <td class="compare-feature-row">Reward Rate</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $cashback_rate = ccm_get_meta($card['id'], 'cashback_rate', 'N/A'); ?>
                        <td class="compare-value positive"><?php echo esc_html($cashback_rate); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Credit Details Section -->
                <tr class="compare-table-section">
                    <td colspan="<?php echo count($compare_cards) + 1; ?>">
                        <?php echo ccm_get_icon('credit-card', 'icon'); ?> Credit Details
                    </td>
                </tr>
                
                <!-- Credit Limit -->
                <tr>
                    <td class="compare-feature-row">Credit Limit</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $credit_limit = ccm_get_meta($card['id'], 'credit_limit', 'N/A'); ?>
                        <td class="compare-value"><?php echo esc_html($credit_limit); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Interest Rate -->
                <tr>
                    <td class="compare-feature-row">Interest Rate</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $interest_rate = ccm_get_meta($card['id'], 'interest_rate', 'N/A'); ?>
                        <td class="compare-value"><?php echo esc_html($interest_rate); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Processing Details Section -->
                <tr class="compare-table-section">
                    <td colspan="<?php echo count($compare_cards) + 1; ?>">
                        <?php echo ccm_get_icon('calendar', 'icon'); ?> Processing & Eligibility
                    </td>
                </tr>
                
                <!-- Processing Time -->
                <tr>
                    <td class="compare-feature-row">Processing Time</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $processing_time = ccm_get_meta($card['id'], 'processing_time', 'N/A'); ?>
                        <td class="compare-value"><?php echo esc_html($processing_time); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Minimum Income -->
                <tr>
                    <td class="compare-feature-row">Minimum Income</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $min_income = ccm_get_meta($card['id'], 'min_income', 'N/A'); ?>
                        <td class="compare-value"><?php echo esc_html($min_income); ?></td>
                    <?php endforeach; ?>
                </tr>
                
                <!-- Action Buttons -->
                <tr class="compare-table-section">
                    <td colspan="<?php echo count($compare_cards) + 1; ?>">
                        <?php echo ccm_get_icon('external-link', 'icon'); ?> Apply Now
                    </td>
                </tr>
                
                <tr>
                    <td class="compare-feature-row">Apply for Card</td>
                    <?php foreach ($compare_cards as $card): ?>
                        <?php $apply_link = ccm_get_meta($card['id'], 'apply_link', '#'); ?>
                        <td class="compare-action-cell">
                            <a href="<?php echo esc_url($apply_link); ?>" 
                               class="compare-btn-apply" 
                               target="_blank" 
                               rel="noopener sponsored">
                                Apply Now
                                <?php echo ccm_get_icon('external-link', 'icon'); ?>
                            </a>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</section>
