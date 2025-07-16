<?php
/**
 * Assets Management
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CCM_Assets {
    
    private $version;
    private $plugin_url;
    
    public function __construct() {
        $this->version = defined('CCM_VERSION') ? CCM_VERSION : '1.0.0';
        $this->plugin_url = plugin_dir_url(dirname(__FILE__));
        
        // Hook into WordPress
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add dashicons to frontend for star ratings
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashicons'));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Always enqueue on credit card related pages
        if ($this->should_load_frontend_assets()) {
            // Enqueue main frontend CSS
            wp_enqueue_style(
                'ccm-frontend-css',
                $this->plugin_url . 'assets/frontend.css',
                array(),
                $this->version
            );
            
            // Enqueue main frontend JS
            wp_enqueue_script(
                'ccm-frontend-js',
                $this->plugin_url . 'assets/frontend.js',
                array('jquery'),
                $this->version,
                true
            );
            
            // Enqueue archive-specific assets on archive pages
            if (is_post_type_archive('credit-card') || is_page_template('templates/archive-credit-card.php')) {
                wp_enqueue_style(
                    'ccm-archive-css',
                    $this->plugin_url . 'assets/archive.css',
                    array('ccm-frontend-css'),
                    $this->version
                );
                
                wp_enqueue_script(
                    'ccm-archive-js',
                    $this->plugin_url . 'assets/archive.js',
                    array('jquery', 'ccm-frontend-js'),
                    $this->version,
                    true
                );
            }
            
            // Localize script with data
            wp_localize_script('ccm-frontend-js', 'ccm_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'api_url' => rest_url('ccm/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'max_compare_cards' => 4,
                'strings' => array(
                    'max_compare_error' => __('You can compare up to 4 cards at a time.', 'credit-card-manager'),
                    'min_compare_error' => __('Please select at least 2 cards to compare.', 'credit-card-manager'),
                    'loading' => __('Loading...', 'credit-card-manager'),
                    'error' => __('Error loading data. Please try again.', 'credit-card-manager'),
                )
            ));
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        if ($post_type === 'credit-card' && ($hook === 'post.php' || $hook === 'post-new.php')) {
            // Enqueue media uploader
            wp_enqueue_media();
            
            // Enqueue admin CSS
            wp_enqueue_style(
                'ccm-admin-css',
                $this->plugin_url . 'assets/admin.css',
                array(),
                $this->version
            );
            
            // Enqueue admin JS
            wp_enqueue_script(
                'ccm-admin-js',
                $this->plugin_url . 'assets/admin.js',
                array('jquery'),
                $this->version,
                true
            );
            
            // Localize admin script
            wp_localize_script('ccm-admin-js', 'ccm_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ccm_admin_nonce'),
                'strings' => array(
                    'upload_image' => __('Upload Image', 'credit-card-manager'),
                    'select_image' => __('Select Image', 'credit-card-manager'),
                    'remove' => __('Remove', 'credit-card-manager'),
                )
            ));
        }
    }
    
    /**
     * Enqueue dashicons on frontend
     */
    public function enqueue_dashicons() {
        if ($this->should_load_frontend_assets()) {
            wp_enqueue_style('dashicons');
        }
    }
    
    /**
     * Check if frontend assets should be loaded
     */
    private function should_load_frontend_assets() {
        // Load on credit card post type pages
        if (is_singular('credit-card') || is_post_type_archive('credit-card')) {
            return true;
        }
        
        // Load on pages using credit card shortcodes
        global $post;
        if (is_a($post, 'WP_Post') && (
            has_shortcode($post->post_content, 'ccm_filters') ||
            has_shortcode($post->post_content, 'ccm_cards_grid') ||
            has_shortcode($post->post_content, 'ccm_single_card')
        )) {
            return true;
        }
        
        // Load on pages using credit card template
        if (is_page_template('templates/archive-credit-card.php')) {
            return true;
        }
        
        // Load on taxonomy pages for credit card taxonomies
        if (is_tax('store') || is_tax('network-type') || is_tax('card-category')) {
            return true;
        }
        
        return false;
    }
}

// Initialize the assets class
new CCM_Assets();
