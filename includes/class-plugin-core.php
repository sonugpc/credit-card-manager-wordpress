<?php
/**
 * Plugin Core Class
 * Handles main plugin initialization and coordination
 */

if (!defined('ABSPATH')) {
    exit;
}

class CreditCardManager_Core {

    private $version = CCM_VERSION;
    private $post_types;
    private $admin;
    private $frontend;

    public function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Initialize core hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        register_activation_hook(CCM_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(CCM_PLUGIN_FILE, array($this, 'deactivate'));
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load classes
        $this->load_class('CreditCardManager_PostTypes');
        $this->load_class('CreditCardManager_Admin');
        $this->load_class('CreditCardManager_Frontend');

        // Initialize classes
        $this->post_types = new CreditCardManager_PostTypes();
        $this->admin = new CreditCardManager_Admin();
        $this->frontend = new CreditCardManager_Frontend();
    }

    /**
     * Load class file
     */
    private function load_class($class_name) {
        // Map class names to actual file names
        $class_map = array(
            'CreditCardManager_PostTypes' => 'class-post-types.php',
            'CreditCardManager_Admin' => 'class-admin.php',
            'CreditCardManager_Frontend' => 'class-frontend.php',
        );

        if (isset($class_map[$class_name])) {
            $file = $class_map[$class_name];
            $path = CCM_PLUGIN_DIR . 'includes/' . $file;

            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize components
        if ($this->post_types) {
            $this->post_types->init();
        }
        if ($this->admin) {
            $this->admin->init();
        }
        if ($this->frontend) {
            $this->frontend->init();
        }
    }

    /**
     * Register REST routes
     */
    public function register_rest_routes() {
        // Register API routes through components
        if ($this->post_types) {
            $this->post_types->register_rest_routes();
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        $this->init();
        flush_rewrite_rules();

        // Create performance tables if needed
        $this->create_performance_tables();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Create performance optimization tables
     */
    private function create_performance_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'credit_card_meta_cache';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            annual_fee_numeric int(11) DEFAULT NULL,
            min_income_numeric int(11) DEFAULT NULL,
            rating_numeric decimal(3,2) DEFAULT NULL,
            review_count_numeric int(11) DEFAULT NULL,
            featured tinyint(1) DEFAULT 0,
            trending tinyint(1) DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_id (post_id),
            KEY annual_fee_idx (annual_fee_numeric),
            KEY min_income_idx (min_income_numeric),
            KEY rating_idx (rating_numeric),
            KEY featured_idx (featured),
            KEY trending_idx (trending)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Hook to update cache when posts are saved
        add_action('save_post_credit-card', array($this, 'update_meta_cache'));
    }

    /**
     * Update meta cache for performance
     */
    public function update_meta_cache($post_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'credit_card_meta_cache';

        // Extract numeric values from meta fields
        $annual_fee = get_post_meta($post_id, 'annual_fee', true);
        $annual_fee_numeric = $this->extract_numeric_value($annual_fee);

        $min_income = get_post_meta($post_id, 'min_income', true);
        $min_income_numeric = $this->extract_numeric_value($min_income);

        $rating = get_post_meta($post_id, 'rating', true);
        $rating_numeric = floatval($rating);

        $review_count = get_post_meta($post_id, 'review_count', true);
        $review_count_numeric = intval($review_count);

        $featured = get_post_meta($post_id, 'featured', true) ? 1 : 0;
        $trending = get_post_meta($post_id, 'trending', true) ? 1 : 0;

        // Insert or update cache
        $wpdb->replace(
            $table_name,
            array(
                'post_id' => $post_id,
                'annual_fee_numeric' => $annual_fee_numeric,
                'min_income_numeric' => $min_income_numeric,
                'rating_numeric' => $rating_numeric,
                'review_count_numeric' => $review_count_numeric,
                'featured' => $featured,
                'trending' => $trending,
            ),
            array('%d', '%d', '%d', '%f', '%d', '%d', '%d')
        );

        // Also update individual meta fields for backward compatibility
        update_post_meta($post_id, 'annual_fee_numeric', $annual_fee_numeric);
        update_post_meta($post_id, 'min_income_numeric', $min_income_numeric);
    }

    /**
     * Extract numeric value from string
     */
    private function extract_numeric_value($string) {
        // Remove currency symbols and commas
        $cleaned = preg_replace('/[^\d.]/', '', $string);
        return intval($cleaned);
    }
}
