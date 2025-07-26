<?php
/**
 * Credit Card Manager Configuration File
 * Central configuration and constants for the plugin
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Plugin information
define('CCM_VERSION', '1.0.0');
define('CCM_PLUGIN_NAME', 'Credit Card Manager');
define('CCM_TEXT_DOMAIN', 'credit-card-manager');

// Plugin paths and URLs
define('CCM_PLUGIN_FILE', dirname(dirname(__FILE__)) . '/plugin.php');
define('CCM_PLUGIN_DIR', plugin_dir_path(CCM_PLUGIN_FILE));
define('CCM_PLUGIN_URL', plugin_dir_url(CCM_PLUGIN_FILE));
define('CCM_ASSETS_URL', CCM_PLUGIN_URL . 'assets/');
define('CCM_TEMPLATES_DIR', CCM_PLUGIN_DIR . 'templates/');
define('CCM_INCLUDES_DIR', CCM_PLUGIN_DIR . 'includes/');

// Database and API configuration
define('CCM_POST_TYPE', 'credit-card');
define('CCM_API_NAMESPACE', 'ccm/v1');
define('CCM_CACHE_GROUP', 'ccm');
define('CCM_CACHE_EXPIRY', 12 * HOUR_IN_SECONDS); // 12 hours

// Taxonomies
define('CCM_TAXONOMY_STORE', 'store');           // Banks/Issuers
define('CCM_TAXONOMY_NETWORK', 'network-type');  // Visa, Mastercard, etc.
define('CCM_TAXONOMY_CATEGORY', 'category');     // Rewards, Travel, etc.

// Feature flags and limits
define('CCM_MAX_COMPARE_CARDS', 4);              // Maximum cards in comparison
define('CCM_CARDS_PER_PAGE', 12);                // Default cards per page
define('CCM_ENABLE_CACHE', true);                // Enable caching
define('CCM_ENABLE_DEBUG', defined('WP_DEBUG') && WP_DEBUG);

// Meta field keys
define('CCM_META_PREFIX', '_ccm_');

// Core meta fields
$ccm_meta_fields = [
    // Basic Information
    'annual_fee' => CCM_META_PREFIX . 'annual_fee',
    'joining_fee' => CCM_META_PREFIX . 'joining_fee',
    'rating' => CCM_META_PREFIX . 'rating',
    'review_count' => CCM_META_PREFIX . 'review_count',
    'card_image_url' => CCM_META_PREFIX . 'card_image_url',
    'apply_link' => CCM_META_PREFIX . 'apply_link',
    
    // Financial Details
    'cashback_rate' => CCM_META_PREFIX . 'cashback_rate',
    'welcome_bonus' => CCM_META_PREFIX . 'welcome_bonus',
    'credit_limit' => CCM_META_PREFIX . 'credit_limit',
    'interest_rate' => CCM_META_PREFIX . 'interest_rate',
    'min_income' => CCM_META_PREFIX . 'min_income',
    'processing_time' => CCM_META_PREFIX . 'processing_time',
    
    // Features and Benefits
    'pros' => CCM_META_PREFIX . 'pros',
    'cons' => CCM_META_PREFIX . 'cons',
    'key_benefits' => CCM_META_PREFIX . 'key_benefits',
    'reward_categories' => CCM_META_PREFIX . 'reward_categories',
    
    // Flags
    'featured' => CCM_META_PREFIX . 'featured',
    'trending' => CCM_META_PREFIX . 'trending',
    'recommended' => CCM_META_PREFIX . 'recommended',
    
    // Additional Details
    'eligibility_criteria' => CCM_META_PREFIX . 'eligibility_criteria',
    'documents_required' => CCM_META_PREFIX . 'documents_required',
    'fees_charges' => CCM_META_PREFIX . 'fees_charges',
];

// Make meta field keys available globally
define('CCM_META_FIELDS', $ccm_meta_fields);

// Default values for meta fields
$ccm_default_values = [
    'annual_fee' => 'N/A',
    'joining_fee' => 'N/A',
    'rating' => 0,
    'review_count' => 0,
    'cashback_rate' => 'N/A',
    'welcome_bonus' => 'N/A',
    'credit_limit' => 'N/A',
    'interest_rate' => 'N/A',
    'min_income' => 'N/A',
    'processing_time' => 'N/A',
    'featured' => false,
    'trending' => false,
    'recommended' => false,
];

define('CCM_DEFAULT_VALUES', $ccm_default_values);

// Styling and UI configuration
$ccm_ui_config = [
    'colors' => [
        'primary' => '#1e40af',
        'secondary' => '#f59e0b',
        'success' => '#16a34a',
        'warning' => '#eab308',
        'error' => '#dc2626',
        'info' => '#0ea5e9',
    ],
    'card_layouts' => [
        'grid' => 'Grid Layout',
        'list' => 'List Layout',
        'compact' => 'Compact Layout',
    ],
    'sort_options' => [
        'rating' => 'Customer Rating',
        'annual_fee' => 'Annual Fee',
        'cashback_rate' => 'Reward Rate',
        'title' => 'Card Name',
        'date' => 'Latest Added',
    ],
];

define('CCM_UI_CONFIG', $ccm_ui_config);

// Currency formatting configuration
$ccm_currency_config = [
    'symbol' => '₹',
    'position' => 'before', // 'before' or 'after'
    'thousands_separator' => ',',
    'decimal_separator' => '.',
    'decimal_places' => 0,
    'use_indian_numbering' => true, // Use lakh/crore notation
];

define('CCM_CURRENCY_CONFIG', $ccm_currency_config);

// Security and validation
define('CCM_NONCE_ACTION', 'ccm_action');
define('CCM_MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB for image uploads
define('CCM_ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Performance settings
define('CCM_ENABLE_LAZY_LOADING', true);
define('CCM_ENABLE_MINIFICATION', !CCM_ENABLE_DEBUG);
define('CCM_CACHE_BUST_VERSION', CCM_VERSION);

// Analytics and tracking (if needed)
define('CCM_ENABLE_ANALYTICS', false);
define('CCM_TRACK_APPLY_CLICKS', true);
define('CCM_TRACK_COMPARISON_USAGE', true);

/**
 * Helper function to get plugin configuration values
 *
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function ccm_get_config($key, $default = null) {
    $config_map = [
        'version' => CCM_VERSION,
        'plugin_name' => CCM_PLUGIN_NAME,
        'text_domain' => CCM_TEXT_DOMAIN,
        'post_type' => CCM_POST_TYPE,
        'api_namespace' => CCM_API_NAMESPACE,
        'max_compare_cards' => CCM_MAX_COMPARE_CARDS,
        'cards_per_page' => CCM_CARDS_PER_PAGE,
        'meta_fields' => CCM_META_FIELDS,
        'default_values' => CCM_DEFAULT_VALUES,
        'ui_config' => CCM_UI_CONFIG,
        'currency_config' => CCM_CURRENCY_CONFIG,
    ];
    
    return isset($config_map[$key]) ? $config_map[$key] : $default;
}

/**
 * Helper function to check if a feature is enabled
 *
 * @param string $feature Feature name
 * @return bool
 */
function ccm_is_feature_enabled($feature) {
    $features = [
        'cache' => CCM_ENABLE_CACHE,
        'debug' => CCM_ENABLE_DEBUG,
        'lazy_loading' => CCM_ENABLE_LAZY_LOADING,
        'minification' => CCM_ENABLE_MINIFICATION,
        'analytics' => CCM_ENABLE_ANALYTICS,
        'track_apply_clicks' => CCM_TRACK_APPLY_CLICKS,
        'track_comparison_usage' => CCM_TRACK_COMPARISON_USAGE,
    ];
    
    return isset($features[$feature]) ? $features[$feature] : false;
}