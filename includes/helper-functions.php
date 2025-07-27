<?php
/**
 * Credit Card Manager - Helper Functions
 * Centralized utility functions for the plugin
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Get meta value for a credit card with enhanced error handling
 */
function ccm_get_meta($post_id, $key, $default = '', $is_numeric = false, $unserialize = false) {
    if (!$post_id || !is_numeric($post_id)) {
        return $default;
    }
    
    $value = get_post_meta($post_id, $key, true);
    
    if ($unserialize) {
        $unserialized = maybe_unserialize($value);
        return !empty($unserialized) ? $unserialized : $default;
    }
    
    if ($is_numeric) {
        return is_numeric($value) ? floatval($value) : (is_numeric($default) ? floatval($default) : 0);
    }
    
    return !empty($value) ? $value : $default;
}

/**
 * Legacy function for backward compatibility
 */
function get_cc_meta($post_id, $key, $default = '', $is_numeric = false, $unserialize = false) {
    return ccm_get_meta($post_id, $key, $default, $is_numeric, $unserialize);
}

/**
 * Legacy function for backward compatibility
 */
function get_compare_meta($post_id, $key, $default = '', $is_numeric = false, $unserialize = false) {
    return ccm_get_meta($post_id, $key, $default, $is_numeric, $unserialize);
}

/**
 * Format numeric value as Indian currency
 */
function ccm_format_currency($value, $show_free = true) {
    if (!is_numeric($value)) {
        // Handle text values like "Free", "N/A", etc.
        return $value;
    }
    
    $num_value = floatval($value);
    
    if ($num_value == 0 && $show_free) {
        return 'Free';
    }
    
    if ($num_value >= 10000000) { // 1 crore
        return '₹' . number_format($num_value / 10000000, 1) . ' Cr';
    } elseif ($num_value >= 100000) { // 1 lakh
        return '₹' . number_format($num_value / 100000, 1) . ' L';
    } else {
        return '₹' . number_format($num_value, 0, '.', ',');
    }
}

/**
 * Legacy function for backward compatibility
 */
function format_currency($value) {
    return ccm_format_currency($value);
}

/**
 * Enhanced SVG icon library with consistent sizing and caching
 */
function ccm_get_icon($name, $classes = '', $size = '24') {
    static $icon_cache = [];
    
    $cache_key = $name . '_' . $classes . '_' . $size;
    
    if (isset($icon_cache[$cache_key])) {
        return $icon_cache[$cache_key];
    }
    
    $base_classes = $classes;
    $viewbox = $size === '16' ? '0 0 16 16' : '0 0 24 24';
    
    $icons = [
        // Basic icons
        'star' => '<path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z"/>',
        'star-outline' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="none" stroke="currentColor" stroke-width="2"/>',
        'check' => '<polyline points="20 6 9 17 4 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
        'x' => '<line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        'minus' => '<line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
        
        // Finance icons
        'credit-card' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2"/><line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2"/>',
        'dollar-sign' => '<line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="2"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" fill="none" stroke="currentColor" stroke-width="2"/>',
        'percentage' => '<line x1="19" y1="5" x2="5" y2="19" stroke="currentColor" stroke-width="2"/><circle cx="6.5" cy="6.5" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="17.5" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/>',
        'wallet' => '<path d="M20 12V8H4a2 2 0 0 1 0-4h12v4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-8" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="20" cy="16" r="2"/>',
        
        // Action icons  
        'gift' => '<polyline points="20 12 20 22 4 22 4 12" fill="none" stroke="currentColor" stroke-width="2"/><rect x="2" y="7" width="20" height="5" fill="none" stroke="currentColor" stroke-width="2"/><line x1="12" y1="22" x2="12" y2="7" stroke="currentColor" stroke-width="2"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" fill="none" stroke="currentColor" stroke-width="2"/>',
        'award' => '<circle cx="12" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="8.21 13.89 7 23 12 17 17 23 15.79 13.88" fill="none" stroke="currentColor" stroke-width="2"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2"/>',
        'compare' => '<line x1="18" y1="20" x2="18" y2="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="20" x2="12" y2="4" stroke="currentColor" stroke-width="2"/><line x1="6" y1="20" x2="6" y2="14" stroke="currentColor" stroke-width="2"/>',
        
        // Navigation icons
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2"/><polyline points="12 5 19 12 12 19" fill="none" stroke="currentColor" stroke-width="2"/>',
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12" stroke="currentColor" stroke-width="2"/><polyline points="12 19 5 12 12 5" fill="none" stroke="currentColor" stroke-width="2"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="15 3 21 3 21 9" fill="none" stroke="currentColor" stroke-width="2"/><line x1="10" y1="14" x2="21" y2="3" stroke="currentColor" stroke-width="2"/>',
        
        // UI icons
        'filter' => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" fill="none" stroke="currentColor" stroke-width="2"/>',
        'sort' => '<path d="M11 5h10M11 9h7M11 13h4M3 17h18M3 5l4 4M7 5v12" fill="none" stroke="currentColor" stroke-width="2"/>',
        'info' => '<circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12" y2="12" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12.01" y2="8" stroke="currentColor" stroke-width="2"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/><line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>',
        'trending-up' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="17 6 23 6 23 12" fill="none" stroke="currentColor" stroke-width="2"/>',
        'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="none" stroke="currentColor" stroke-width="2"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" fill="none" stroke="currentColor" stroke-width="2"/>',
        'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" fill="none" stroke="currentColor" stroke-width="2"/>',
    ];
    
    $icon_path = isset($icons[$name]) ? $icons[$name] : $icons['info']; // fallback to info icon
    
    $svg = sprintf(
        '<svg class="%s" xmlns="http://www.w3.org/2000/svg" viewBox="%s" fill="none">%s</svg>',
        esc_attr($base_classes),
        esc_attr($viewbox),
        $icon_path
    );
    
    $icon_cache[$cache_key] = $svg;
    
    return $svg;
}

/**
 * Legacy functions for backward compatibility
 */
function get_cc_icon($name, $classes = '') {
    return ccm_get_icon($name, $classes);
}

function get_compare_icon($name, $classes = '') {
    return ccm_get_icon($name, $classes);
}

/**
 * Get card taxonomy terms as array
 */
function ccm_get_card_terms($post_id, $taxonomy, $field = 'name') {
    $terms = get_the_terms($post_id, $taxonomy);
    
    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }
    
    return wp_list_pluck($terms, $field);
}

/**
 * Get card bank/issuer name
 */
function ccm_get_card_bank($post_id) {
    $banks = ccm_get_card_terms($post_id, 'store');
    return !empty($banks) ? $banks[0] : 'N/A';
}

/**
 * Get card network type
 */
function ccm_get_card_network($post_id) {
    $networks = ccm_get_card_terms($post_id, 'network-type');
    return !empty($networks) ? implode(', ', $networks) : 'N/A';
}

/**
 * Get card categories
 */
function ccm_get_card_categories($post_id) {
    $categories = ccm_get_card_terms($post_id, 'category');
    return !empty($categories) ? $categories : [];
}

/**
 * Generate star rating HTML
 */
function ccm_render_rating($rating, $max_rating = 5, $show_text = true) {
    $rating = floatval($rating);
    $html = '<div class="ccm-rating-display" data-rating="' . esc_attr($rating) . '">';
    
    for ($i = 1; $i <= $max_rating; $i++) {
        $filled = $i <= $rating ? 'filled' : 'empty';
        $html .= ccm_get_icon('star', "ccm-star ccm-star-{$filled}");
    }
    
    if ($show_text) {
        $html .= '<span class="ccm-rating-text">' . number_format($rating, 1) . '/' . $max_rating . '</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Sanitize and validate card comparison IDs
 */
function ccm_sanitize_compare_ids($ids_string) {
    if (empty($ids_string)) {
        return [];
    }
    
    $ids = explode(',', sanitize_text_field($ids_string));
    $clean_ids = [];
    
    foreach ($ids as $id) {
        $id = intval(trim($id));
        if ($id > 0 && get_post_type($id) === 'credit-card' && get_post_status($id) === 'publish') {
            $clean_ids[] = $id;
        }
    }
    
    return array_unique($clean_ids);
}

/**
 * Check if value represents "free" or zero cost
 */
function ccm_is_free_value($value) {
    if (is_numeric($value)) {
        return floatval($value) == 0;
    }
    
    $value = strtolower(trim($value));
    return in_array($value, ['free', 'nil', 'waived', 'complimentary', '0', '₹0']);
}

/**
 * Get plugin asset URL
 */
function ccm_asset_url($path = '') {
    return plugin_dir_url(dirname(__FILE__)) . 'assets/' . ltrim($path, '/');
}

/**
 * Get plugin template path
 */
function ccm_template_path($template = '') {
    return plugin_dir_path(dirname(__FILE__)) . 'templates/' . ltrim($template, '/');
}

/**
 * Load template with data
 */
function ccm_load_template($template, $data = []) {
    $template_path = ccm_template_path($template);
    
    if (!file_exists($template_path)) {
        return false;
    }
    
    // Extract data to variables
    if (!empty($data)) {
        extract($data, EXTR_SKIP);
    }
    
    ob_start();
    include $template_path;
    return ob_get_clean();
}

/**
 * Debug logging for development
 */
function ccm_log($message, $data = null) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $log_message = '[CCM] ' . $message;
    
    if ($data !== null) {
        $log_message .= ' | Data: ' . print_r($data, true);
    }
    
    error_log($log_message);
}

/**
 * Generate dynamic FAQs based on card data
 */
function ccm_generate_dynamic_faqs($post_id) {
    $card_title = get_the_title($post_id);
    $bank_terms = get_the_terms($post_id, 'store');
    $bank_name = (!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '';
    
    $annual_fee = ccm_get_meta($post_id, 'annual_fee', 0, true);
    $joining_fee = ccm_get_meta($post_id, 'joining_fee', 0, true);
    $welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', '');
    $reward_type = ccm_get_meta($post_id, 'reward_type', '');
    $reward_conversion_rate = ccm_get_meta($post_id, 'reward_conversion_rate', '');
    $min_income = ccm_get_meta($post_id, 'min_income', '');
    $processing_time = ccm_get_meta($post_id, 'processing_time', '');
    $cashback_rate = ccm_get_meta($post_id, 'cashback_rate', '');
    $apply_link = ccm_get_meta($post_id, 'apply_link', '');
    
    $dynamic_faqs = array();
    
    // Fee-related FAQ
    if ($annual_fee > 0) {
        $dynamic_faqs[] = array(
            'question' => "What is the annual fee for {$card_title}?",
            'answer' => "The annual fee for {$card_title} is ₹" . number_format($annual_fee) . ($joining_fee > 0 ? " with a joining fee of ₹" . number_format($joining_fee) : "") . ". Some cards may waive the annual fee based on spending criteria or for the first year."
        );
    } else {
        $dynamic_faqs[] = array(
            'question' => "Does {$card_title} have any annual fee?",
            'answer' => "No, {$card_title} has no annual fee" . ($joining_fee > 0 ? ", but there is a joining fee of ₹" . number_format($joining_fee) : "") . ". This makes it a cost-effective option for cardholders."
        );
    }
    
    // Welcome bonus FAQ
    if (!empty($welcome_bonus)) {
        $dynamic_faqs[] = array(
            'question' => "What welcome bonus do I get with {$card_title}?",
            'answer' => "New {$card_title} cardholders can earn {$welcome_bonus}. This welcome offer is typically available for a limited time and subject to meeting minimum spending requirements."
        );
    }
    
    // Reward system FAQ
    if (!empty($reward_type) && !empty($reward_conversion_rate)) {
        $dynamic_faqs[] = array(
            'question' => "How does the {$reward_type} system work with {$card_title}?",
            'answer' => "{$card_title} offers {$reward_type} as rewards. The conversion rate is {$reward_conversion_rate}. " . (!empty($cashback_rate) ? "You can earn {$cashback_rate} on various categories." : "Earn rewards on every purchase and redeem them for maximum value.")
        );
    } elseif (!empty($cashback_rate)) {
        $dynamic_faqs[] = array(
            'question' => "What cashback rates does {$card_title} offer?",
            'answer' => "{$card_title} offers {$cashback_rate}. The cashback rates may vary by category and spending patterns, providing excellent value for everyday purchases."
        );
    }
    
    // Eligibility FAQ
    if (!empty($min_income)) {
        $dynamic_faqs[] = array(
            'question' => "What are the eligibility criteria for {$card_title}?",
            'answer' => "To be eligible for {$card_title}, you need a minimum income of {$min_income}. Additional criteria may include age requirements, credit score, and employment status. Contact {$bank_name} for complete eligibility details."
        );
    }
    
    // Processing time FAQ
    if (!empty($processing_time)) {
        $dynamic_faqs[] = array(
            'question' => "How long does it take to get {$card_title} approved?",
            'answer' => "The typical processing time for {$card_title} is {$processing_time}. Processing times may vary based on document verification and credit assessment. You may receive instant approval for pre-approved applications."
        );
    }
    
    // Application FAQ
    if (!empty($apply_link)) {
        $dynamic_faqs[] = array(
            'question' => "How can I apply for {$card_title}?",
            'answer' => "You can apply for {$card_title} online through our secure application process. The application is quick and convenient, requiring basic personal and financial information. " . (!empty($bank_name) ? "You can also visit any {$bank_name} branch to apply in person." : "")
        );
    }
    
    // Bank-specific FAQ
    if (!empty($bank_name)) {
        $dynamic_faqs[] = array(
            'question' => "Is {$card_title} from {$bank_name} a good choice?",
            'answer' => "{$card_title} from {$bank_name} offers excellent features including " . (!empty($reward_type) ? "{$reward_type} rewards" : "cashback benefits") . ($annual_fee > 0 ? "" : ", no annual fee") . ", and comprehensive benefits. It's suitable for customers looking for " . (!empty($cashback_rate) ? "high cashback rates" : "reliable banking services") . " with {$bank_name}'s trusted service."
        );
    }
    
    return $dynamic_faqs;
}

/**
 * Get combined FAQs (custom + dynamic) for a credit card
 */
function ccm_get_card_faqs($post_id) {
    // Get custom FAQs from meta
    $custom_faqs = ccm_get_meta($post_id, 'custom_faqs', array(), false, true);
    
    // Generate dynamic FAQs
    $dynamic_faqs = ccm_generate_dynamic_faqs($post_id);
    
    // Combine custom and dynamic FAQs
    $all_faqs = array();
    
    // Add custom FAQs first (they take priority)
    if (!empty($custom_faqs) && is_array($custom_faqs)) {
        foreach ($custom_faqs as $faq) {
            if (!empty($faq['question']) && !empty($faq['answer'])) {
                $all_faqs[] = $faq;
            }
        }
    }
    
    // Add dynamic FAQs
    if (!empty($dynamic_faqs)) {
        $all_faqs = array_merge($all_faqs, $dynamic_faqs);
    }
    
    return $all_faqs;
}

/**
 * Generate FAQ schema markup for SEO
 */
function ccm_generate_faq_schema($faqs) {
    if (empty($faqs)) {
        return null;
    }
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array()
    );
    
    foreach ($faqs as $faq) {
        if (!empty($faq['question']) && !empty($faq['answer'])) {
            $schema['mainEntity'][] = array(
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                )
            );
        }
    }
    
    return !empty($schema['mainEntity']) ? $schema : null;
}
