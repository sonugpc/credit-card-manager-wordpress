<?php
/**
 * Plugin Name: Credit Card Manager
 * Plugin URI: https://yoursite.com
 * Description: A comprehensive plugin to manage credit cards with advanced filtering and API support
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: credit-card-manager
 */

add_filter('template_include', 'load_credit_card_templates_from_plugin');

function load_credit_card_templates_from_plugin($template) {
    $post_type = 'credit-card';

    if (is_singular($post_type)) {
        $single_template = plugin_dir_path(__FILE__) . "templates/single-{$post_type}.php";
        if (file_exists($single_template)) {
            return $single_template;
        }
    }

    if (is_post_type_archive($post_type)) {
        $archive_template = plugin_dir_path(__FILE__) . "templates/archive-credit-card.php";
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }

    // Check for template by name (for custom pages using our template)
    $template_name = basename($template);
    if ($template_name == 'archive-credit-card.php') {
        $custom_template = plugin_dir_path(__FILE__) . "templates/archive-credit-card.php";
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    return $template;
}

// Add a template for the archive page
add_filter('theme_page_templates', 'ccm_add_archive_template');
function ccm_add_archive_template($templates) {
    $templates['templates/archive-credit-card.php'] = __('Credit Card Archive', 'credit-card-manager');
    return $templates;
}


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include API functions
require_once plugin_dir_path(__FILE__) . 'includes/api.php';

class CreditCardManager {
    
    private $version = '1.0.0';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        
        // Add meta box
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'));
        
        // Add admin columns
        add_filter('manage_credit-card_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_credit-card_posts_custom_column', array($this, 'admin_column_content'), 10, 2);
        add_filter('manage_edit-credit-card_sortable_columns', array($this, 'sortable_columns'));
        
        // REST API filters
        add_filter('rest_credit-card_query', array($this, 'filter_rest_api'), 10, 2);
        add_filter('rest_query_vars', array($this, 'add_rest_query_vars'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        $this->register_post_type();
        $this->register_taxonomies();
        $this->register_meta_fields();
    }
    
    /**
     * Register Credit Card Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Credit Cards', 'Post type general name', 'credit-card-manager'),
            'singular_name'         => _x('Credit Card', 'Post type singular name', 'credit-card-manager'),
            'menu_name'             => _x('Credit Cards', 'Admin Menu text', 'credit-card-manager'),
            'name_admin_bar'        => _x('Credit Card', 'Add New on Toolbar', 'credit-card-manager'),
            'add_new'               => __('Add New', 'credit-card-manager'),
            'add_new_item'          => __('Add New Credit Card', 'credit-card-manager'),
            'new_item'              => __('New Credit Card', 'credit-card-manager'),
            'edit_item'             => __('Edit Credit Card', 'credit-card-manager'),
            'view_item'             => __('View Credit Card', 'credit-card-manager'),
            'all_items'             => __('All Credit Cards', 'credit-card-manager'),
            'search_items'          => __('Search Credit Cards', 'credit-card-manager'),
            'parent_item_colon'     => __('Parent Credit Cards:', 'credit-card-manager'),
            'not_found'             => __('No credit cards found.', 'credit-card-manager'),
            'not_found_in_trash'    => __('No credit cards found in Trash.', 'credit-card-manager'),
            'featured_image'        => _x('Credit Card Image', 'Overrides the "Featured Image" phrase', 'credit-card-manager'),
            'set_featured_image'    => _x('Set credit card image', 'Overrides the "Set featured image" phrase', 'credit-card-manager'),
            'remove_featured_image' => _x('Remove credit card image', 'Overrides the "Remove featured image" phrase', 'credit-card-manager'),
            'use_featured_image'    => _x('Use as credit card image', 'Overrides the "Use as featured image" phrase', 'credit-card-manager'),
            'archives'              => _x('Credit Card archives', 'The post type archive label', 'credit-card-manager'),
            'insert_into_item'      => _x('Insert into credit card', 'Overrides the "Insert into post" phrase', 'credit-card-manager'),
            'uploaded_to_this_item' => _x('Uploaded to this credit card', 'Overrides the "Uploaded to this post" phrase', 'credit-card-manager'),
            'filter_items_list'     => _x('Filter credit cards list', 'Screen reader text for the filter links', 'credit-card-manager'),
            'items_list_navigation' => _x('Credit cards list navigation', 'Screen reader text for the pagination', 'credit-card-manager'),
            'items_list'            => _x('Credit cards list', 'Screen reader text for the items list', 'credit-card-manager'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'rest_base'          => 'credit-cards',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'query_var'          => true,
            'rewrite'            => array('slug' => 'credit-cards'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-id-alt',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'taxonomies'         => array('store', 'category', 'network-type'),
        );
        
        register_post_type('credit-card', $args);
    }
    
    /**
     * Register Custom Taxonomies
     */
    public function register_taxonomies() {
        // Network Type Taxonomy
        $network_labels = array(
            'name'              => _x('Network Types', 'taxonomy general name', 'credit-card-manager'),
            'singular_name'     => _x('Network Type', 'taxonomy singular name', 'credit-card-manager'),
            'search_items'      => __('Search Network Types', 'credit-card-manager'),
            'all_items'         => __('All Network Types', 'credit-card-manager'),
            'parent_item'       => __('Parent Network Type', 'credit-card-manager'),
            'parent_item_colon' => __('Parent Network Type:', 'credit-card-manager'),
            'edit_item'         => __('Edit Network Type', 'credit-card-manager'),
            'update_item'       => __('Update Network Type', 'credit-card-manager'),
            'add_new_item'      => __('Add New Network Type', 'credit-card-manager'),
            'new_item_name'     => __('New Network Type Name', 'credit-card-manager'),
            'menu_name'         => __('Network Types', 'credit-card-manager'),
        );
        
        $network_args = array(
            'hierarchical'      => false,
            'labels'            => $network_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'network-types',
            'query_var'         => true,
            'rewrite'           => array('slug' => 'network-type'),
        );
        
        register_taxonomy('network-type', array('credit-card'), $network_args);
        
        // Add default network types
        if (!term_exists('Visa', 'network-type')) {
            wp_insert_term('Visa', 'network-type');
            wp_insert_term('Mastercard', 'network-type');
            wp_insert_term('American Express', 'network-type');
            wp_insert_term('Discover', 'network-type');
            wp_insert_term('RuPay', 'network-type');
        }
        
        // Store/Bank Taxonomy
        $store_labels = array(
            'name'              => _x('Banks/Stores', 'taxonomy general name', 'credit-card-manager'),
            'singular_name'     => _x('Bank/Store', 'taxonomy singular name', 'credit-card-manager'),
            'search_items'      => __('Search Banks/Stores', 'credit-card-manager'),
            'all_items'         => __('All Banks/Stores', 'credit-card-manager'),
            'parent_item'       => __('Parent Bank/Store', 'credit-card-manager'),
            'parent_item_colon' => __('Parent Bank/Store:', 'credit-card-manager'),
            'edit_item'         => __('Edit Bank/Store', 'credit-card-manager'),
            'update_item'       => __('Update Bank/Store', 'credit-card-manager'),
            'add_new_item'      => __('Add New Bank/Store', 'credit-card-manager'),
            'new_item_name'     => __('New Bank/Store Name', 'credit-card-manager'),
            'menu_name'         => __('Banks/Stores', 'credit-card-manager'),
        );
        
        $store_args = array(
            'hierarchical'      => true,
            'labels'            => $store_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'stores',
            'query_var'         => true,
            'rewrite'           => array('slug' => 'store'),
        );
        
        register_taxonomy('store', array('credit-card'), $store_args);
        
        // Card Category Taxonomy with Icon Support
        $category_labels = array(
            'name'              => _x('Card Categories', 'taxonomy general name', 'credit-card-manager'),
            'singular_name'     => _x('Card Category', 'taxonomy singular name', 'credit-card-manager'),
            'search_items'      => __('Search Card Categories', 'credit-card-manager'),
            'all_items'         => __('All Card Categories', 'credit-card-manager'),
            'parent_item'       => __('Parent Card Category', 'credit-card-manager'),
            'parent_item_colon' => __('Parent Card Category:', 'credit-card-manager'),
            'edit_item'         => __('Edit Card Category', 'credit-card-manager'),
            'update_item'       => __('Update Card Category', 'credit-card-manager'),
            'add_new_item'      => __('Add New Card Category', 'credit-card-manager'),
            'new_item_name'     => __('New Card Category Name', 'credit-card-manager'),
            'menu_name'         => __('Card Categories', 'credit-card-manager'),
        );
        
        $category_args = array(
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'card-categories',
            'query_var'         => true,
            'rewrite'           => array('slug' => 'card-category'),
        );
        
        register_taxonomy('card-category', array('credit-card'), $category_args);
        
        // Add default card categories
        if (!term_exists('Rewards', 'card-category')) {
            wp_insert_term('Rewards', 'card-category');
            wp_insert_term('Cashback', 'card-category');
            wp_insert_term('Travel', 'card-category');
            wp_insert_term('Fuel', 'card-category');
            wp_insert_term('Lifestyle', 'card-category');
            wp_insert_term('Shopping', 'card-category');
            wp_insert_term('Business', 'card-category');
            wp_insert_term('Student', 'card-category');
            wp_insert_term('Secured', 'card-category');
            wp_insert_term('Premium', 'card-category');
        }
        
        // Add icon field to card-category taxonomy
        add_action('card-category_add_form_fields', array($this, 'add_category_icon_field'));
        add_action('card-category_edit_form_fields', array($this, 'edit_category_icon_field'), 10, 2);
        add_action('created_card-category', array($this, 'save_category_icon_field'));
        add_action('edited_card-category', array($this, 'save_category_icon_field'));
    }
    
    /**
     * Add Category Icon Field
     */
    public function add_category_icon_field() {
        ?>
        <div class="form-field">
            <label for="category_icon"><?php _e('Category Icon', 'credit-card-manager'); ?></label>
            <textarea name="category_icon" id="category_icon" rows="5" placeholder="<?php _e('Enter SVG code, base64 encoded image, or icon class', 'credit-card-manager'); ?>"></textarea>
            <p class="description"><?php _e('Add an icon for this category. You can use SVG code, base64 encoded image, or a CSS class name.', 'credit-card-manager'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit Category Icon Field
     */
    public function edit_category_icon_field($term, $taxonomy) {
        $icon = get_term_meta($term->term_id, 'category_icon', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="category_icon"><?php _e('Category Icon', 'credit-card-manager'); ?></label>
            </th>
            <td>
                <textarea name="category_icon" id="category_icon" rows="5"><?php echo esc_textarea($icon); ?></textarea>
                <p class="description"><?php _e('Add an icon for this category. You can use SVG code, base64 encoded image, or a CSS class name.', 'credit-card-manager'); ?></p>
                <?php if (!empty($icon)): ?>
                <div class="icon-preview" style="margin-top: 10px; padding: 10px; border: 1px solid #ddd; display: inline-block;">
                    <strong><?php _e('Icon Preview:', 'credit-card-manager'); ?></strong><br>
                    <?php echo $icon; ?>
                </div>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save Category Icon Field
     */
    public function save_category_icon_field($term_id) {
        if (isset($_POST['category_icon'])) {
            update_term_meta($term_id, 'category_icon', $_POST['category_icon']);
        }
    }
    
 /**
 * Register Meta Fields for REST API
 */
public function register_meta_fields() {
    $meta_fields = array(
        'card_image_url' => array(
            'type' => 'string',
            'description' => 'Credit card image URL',
            'single' => true,
            'sanitize_callback' => 'esc_url_raw',
            'show_in_rest' => true,
        ),
        'rating' => array(
            'type' => 'number',
            'description' => 'Card rating (0-5)',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_rating'),
            'show_in_rest' => true,
        ),
        'review_count' => array(
            'type' => 'integer',
            'description' => 'Number of reviews',
            'single' => true,
            'sanitize_callback' => 'absint',
            'show_in_rest' => true,
        ),
        'annual_fee' => array(
            'type' => 'string',
            'description' => 'Annual fee amount',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'joining_fee' => array(
            'type' => 'string',
            'description' => 'Joining fee amount',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'welcome_bonus' => array(
            'type' => 'string',
            'description' => 'Welcome bonus description',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'welcome_bonus_points' => array(
            'type' => 'integer',
            'description' => 'Welcome bonus points',
            'single' => true,
            'sanitize_callback' => 'absint',
            'show_in_rest' => true,
        ),
        'welcome_bonus_type' => array(
            'type' => 'string',
            'description' => 'Type of welcome bonus (points/money)',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'cashback_rate' => array(
            'type' => 'string',
            'description' => 'Cashback/reward rate',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'credit_limit' => array(
            'type' => 'string',
            'description' => 'Credit limit',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'interest_rate' => array(
            'type' => 'string',
            'description' => 'Interest rate',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'processing_time' => array(
            'type' => 'string',
            'description' => 'Processing time',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'min_income' => array(
            'type' => 'string',
            'description' => 'Minimum income requirement',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'min_age' => array(
            'type' => 'string',
            'description' => 'Minimum age requirement',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'max_age' => array(
            'type' => 'string',
            'description' => 'Maximum age requirement',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'pros' => array(
            'type' => 'array',
            'description' => 'Pros of the credit card',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_array_field'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string'
                    )
                )
            ),
        ),
        'cons' => array(
            'type' => 'array',
            'description' => 'Cons of the credit card',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_array_field'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string'
                    )
                )
            ),
        ),
        'best_for' => array(
            'type' => 'array',
            'description' => 'Best suited for',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_array_field'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string'
                    )
                )
            ),
        ),
        'features' => array(
            'type' => 'array',
            'description' => 'Card features',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_complex_array'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'title' => array('type' => 'string'),
                            'description' => array('type' => 'string'),
                            'icon' => array('type' => 'string')
                        )
                    )
                )
            ),
        ),
        'rewards' => array(
            'type' => 'array',
            'description' => 'Reward structure',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_complex_array'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'category' => array('type' => 'string'),
                            'rate' => array('type' => 'string'),
                            'description' => array('type' => 'string')
                        )
                    )
                )
            ),
        ),
        'fees' => array(
            'type' => 'array',
            'description' => 'Fee structure',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_complex_array'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'type' => array('type' => 'string'),
                            'amount' => array('type' => 'string'),
                            'description' => array('type' => 'string')
                        )
                    )
                )
            ),
        ),
        'eligibility' => array(
            'type' => 'array',
            'description' => 'Eligibility criteria',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_complex_array'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'criteria' => array('type' => 'string'),
                            'value' => array('type' => 'string')
                        )
                    )
                )
            ),
        ),
        'documents' => array(
            'type' => 'array',
            'description' => 'Required documents',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_array_field'),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string'
                    )
                )
            ),
        ),
        'apply_link' => array(
            'type' => 'string',
            'description' => 'Application link',
            'single' => true,
            'sanitize_callback' => 'esc_url_raw',
            'show_in_rest' => true,
        ),
        'featured' => array(
            'type' => 'boolean',
            'description' => 'Is featured card',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'show_in_rest' => true,
        ),
        'trending' => array(
            'type' => 'boolean',
            'description' => 'Is trending card',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'show_in_rest' => true,
        ),
        'gradient' => array(
            'type' => 'string',
            'description' => 'CSS gradient classes',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'bg_gradient' => array(
            'type' => 'string',
            'description' => 'Background gradient classes',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'show_in_rest' => true,
        ),
        'theme_color' => array(
            'type' => 'string',
            'description' => 'Theme color',
            'single' => true,
            'sanitize_callback' => 'sanitize_hex_color',
            'show_in_rest' => true,
        ),
        'overall_score' => array(
            'type' => 'number',
            'description' => 'Overall score',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_rating'),
            'show_in_rest' => true,
        ),
        'reward_score' => array(
            'type' => 'number',
            'description' => 'Reward score',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_rating'),
            'show_in_rest' => true,
        ),
        'fees_score' => array(
            'type' => 'number',
            'description' => 'Fees score',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_rating'),
            'show_in_rest' => true,
        ),
        'benefits_score' => array(
            'type' => 'number',
            'description' => 'Benefits score',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_rating'),
            'show_in_rest' => true,
        ),
        'support_score' => array(
            'type' => 'number',
            'description' => 'Support score',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_rating'),
            'show_in_rest' => true,
        ),
        'reward_rate' => array(
            'type' => 'number',
            'description' => 'Reward rate percentage',
            'single' => true,
            'sanitize_callback' => array($this, 'sanitize_percentage'),
            'show_in_rest' => true,
        ),
    );
    
    foreach ($meta_fields as $key => $args) {
        register_post_meta('credit-card', $key, array_merge($args, array(
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        )));
    }
}
    
    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'credit-card-details',
            __('Credit Card Details', 'credit-card-manager'),
            array($this, 'meta_box_callback'),
            'credit-card',
            'normal',
            'high'
        );
    }
    
    /**
     * Meta Box Callback
     */
    public function meta_box_callback($post) {
        wp_nonce_field('credit_card_meta_box', 'credit_card_meta_box_nonce');
        
        // Get current values
        $card_image_url = get_post_meta($post->ID, 'card_image_url', true);
        $rating = get_post_meta($post->ID, 'rating', true);
        $review_count = get_post_meta($post->ID, 'review_count', true);
        $annual_fee = get_post_meta($post->ID, 'annual_fee', true);
        $joining_fee = get_post_meta($post->ID, 'joining_fee', true);
        $welcome_bonus = get_post_meta($post->ID, 'welcome_bonus', true);
        $welcome_bonus_points = get_post_meta($post->ID, 'welcome_bonus_points', true);
        $welcome_bonus_type = get_post_meta($post->ID, 'welcome_bonus_type', true);
        $cashback_rate = get_post_meta($post->ID, 'cashback_rate', true);
        $credit_limit = get_post_meta($post->ID, 'credit_limit', true);
        $interest_rate = get_post_meta($post->ID, 'interest_rate', true);
        $processing_time = get_post_meta($post->ID, 'processing_time', true);
        $min_income = get_post_meta($post->ID, 'min_income', true);
        $min_age = get_post_meta($post->ID, 'min_age', true);
        $max_age = get_post_meta($post->ID, 'max_age', true);
        $apply_link = get_post_meta($post->ID, 'apply_link', true);
        $featured = get_post_meta($post->ID, 'featured', true);
        $trending = get_post_meta($post->ID, 'trending', true);
        $theme_color = get_post_meta($post->ID, 'theme_color', true);
        
        // Get array fields
        $pros = get_post_meta($post->ID, 'pros', true) ?: array();
        $cons = get_post_meta($post->ID, 'cons', true) ?: array();
        $best_for = get_post_meta($post->ID, 'best_for', true) ?: array();
        $documents = get_post_meta($post->ID, 'documents', true) ?: array();
        
        ?>
        <div class="credit-card-meta-container">
            <style>
                .credit-card-meta-container { max-width: 100%; }
                .ccm-field-group { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
                .ccm-field-group h3 { margin-top: 0; color: #333; }
                .ccm-field { margin-bottom: 15px; }
                .ccm-field label { display: block; font-weight: bold; margin-bottom: 5px; }
                .ccm-field input, .ccm-field select, .ccm-field textarea { width: 100%; max-width: 400px; }
                .ccm-array-field { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; }
                .ccm-array-item { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
                .ccm-array-item input { flex: 1; }
                .ccm-remove-item { background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; }
                .ccm-add-item { background: #28a745; color: white; border: none; padding: 5px 15px; cursor: pointer; }
                .ccm-checkbox-group { display: flex; align-items: center; gap: 10px; }
                .ccm-image-upload { border: 1px solid #ddd; padding: 15px; background: white; }
            </style>
            
            <div class="ccm-field-group">
                <h3><?php _e('Basic Information', 'credit-card-manager'); ?></h3>
                
                <div class="ccm-field">
                    <label><?php _e('Card Image', 'credit-card-manager'); ?></label>
                    <div class="ccm-image-upload">
                        <input type="hidden" id="card_image_id" name="card_image_id" value="<?php echo esc_attr(get_post_meta($post->ID, 'card_image_id', true)); ?>" />
                        <input type="text" id="card_image_url" name="card_image_url" value="<?php echo esc_url($card_image_url); ?>" placeholder="<?php _e('Enter image URL or upload image', 'credit-card-manager'); ?>" />
                        <button type="button" class="button" id="upload_image_button"><?php _e('Upload Image', 'credit-card-manager'); ?></button>
                        <div id="image_preview" style="margin-top: 10px;">
                            <?php if ($card_image_url): ?>
                                <img src="<?php echo esc_url($card_image_url); ?>" style="max-width: 200px; height: auto;" />
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ccm-field">
                    <label for="rating"><?php _e('Rating (0-5)', 'credit-card-manager'); ?></label>
                    <input type="number" id="rating" name="rating" value="<?php echo esc_attr($rating); ?>" min="0" max="5" step="0.1" />
                </div>
                
                <div class="ccm-field">
                    <label for="review_count"><?php _e('Review Count', 'credit-card-manager'); ?></label>
                    <input type="number" id="review_count" name="review_count" value="<?php echo esc_attr($review_count); ?>" min="0" />
                </div>
                
                <div class="ccm-checkbox-group">
                    <input type="checkbox" id="featured" name="featured" value="1" <?php checked($featured, 1); ?> />
                    <label for="featured"><?php _e('Featured Card', 'credit-card-manager'); ?></label>
                </div>
                
                <div class="ccm-checkbox-group">
                    <input type="checkbox" id="trending" name="trending" value="1" <?php checked($trending, 1); ?> />
                    <label for="trending"><?php _e('Trending Card', 'credit-card-manager'); ?></label>
                </div>
                
                <div class="ccm-field">
                    <label for="theme_color"><?php _e('Theme Color', 'credit-card-manager'); ?></label>
                    <input type="color" id="theme_color" name="theme_color" value="<?php echo esc_attr($theme_color ?: '#1e40af'); ?>" />
                </div>
            </div>
            
            <div class="ccm-field-group">
                <h3><?php _e('Fees & Benefits', 'credit-card-manager'); ?></h3>
                
                <div class="ccm-field">
                    <label for="annual_fee"><?php _e('Annual Fee', 'credit-card-manager'); ?></label>
                    <input type="text" id="annual_fee" name="annual_fee" value="<?php echo esc_attr($annual_fee); ?>" placeholder="₹2,500" />
                </div>
                
                <div class="ccm-field">
                    <label for="joining_fee"><?php _e('Joining Fee', 'credit-card-manager'); ?></label>
                    <input type="text" id="joining_fee" name="joining_fee" value="<?php echo esc_attr($joining_fee); ?>" placeholder="₹2,500" />
                </div>
                
                <div class="ccm-field">
                    <label for="welcome_bonus"><?php _e('Welcome Bonus Description', 'credit-card-manager'); ?></label>
                    <input type="text" id="welcome_bonus" name="welcome_bonus" value="<?php echo esc_attr($welcome_bonus); ?>" placeholder="10,000 reward points worth ₹2,500" />
                </div>
                
                <div class="ccm-field">
                    <label for="welcome_bonus_points"><?php _e('Welcome Bonus Points/Amount', 'credit-card-manager'); ?></label>
                    <input type="number" id="welcome_bonus_points" name="welcome_bonus_points" value="<?php echo esc_attr($welcome_bonus_points); ?>" min="0" />
                </div>
                
                <div class="ccm-field">
                    <label for="welcome_bonus_type"><?php _e('Welcome Bonus Type', 'credit-card-manager'); ?></label>
                    <select id="welcome_bonus_type" name="welcome_bonus_type">
                        <option value="points" <?php selected($welcome_bonus_type, 'points'); ?>><?php _e('Points', 'credit-card-manager'); ?></option>
                        <option value="money" <?php selected($welcome_bonus_type, 'money'); ?>><?php _e('Money', 'credit-card-manager'); ?></option>
                        <option value="cashback" <?php selected($welcome_bonus_type, 'cashback'); ?>><?php _e('Cashback', 'credit-card-manager'); ?></option>
                    </select>
                </div>
                
                <div class="ccm-field">
                    <label for="cashback_rate"><?php _e('Cashback/Reward Rate', 'credit-card-manager'); ?></label>
                    <input type="text" id="cashback_rate" name="cashback_rate" value="<?php echo esc_attr($cashback_rate); ?>" placeholder="Up to 4% reward rate" />
                </div>
            </div>
            
            <div class="ccm-field-group">
                <h3><?php _e('Eligibility & Terms', 'credit-card-manager'); ?></h3>
                
                <div class="ccm-field">
                    <label for="credit_limit"><?php _e('Credit Limit', 'credit-card-manager'); ?></label>
                    <input type="text" id="credit_limit" name="credit_limit" value="<?php echo esc_attr($credit_limit); ?>" placeholder="Up to ₹10,00,000" />
                </div>
                
                <div class="ccm-field">
                    <label for="interest_rate"><?php _e('Interest Rate', 'credit-card-manager'); ?></label>
                    <input type="text" id="interest_rate" name="interest_rate" value="<?php echo esc_attr($interest_rate); ?>" placeholder="3.49% per month" />
                </div>
                
                <div class="ccm-field">
                    <label for="processing_time"><?php _e('Processing Time', 'credit-card-manager'); ?></label>
                    <input type="text" id="processing_time" name="processing_time" value="<?php echo esc_attr($processing_time); ?>" placeholder="7-10 working days" />
                </div>
                
                <div class="ccm-field">
                    <label for="min_income"><?php _e('Minimum Income', 'credit-card-manager'); ?></label>
                    <input type="text" id="min_income" name="min_income" value="<?php echo esc_attr($min_income); ?>" placeholder="₹6,00,000 annually" />
                </div>
                
                <div class="ccm-field">
                    <label for="min_age"><?php _e('Minimum Age', 'credit-card-manager'); ?></label>
                    <input type="text" id="min_age" name="min_age" value="<?php echo esc_attr($min_age); ?>" placeholder="21 years" />
                </div>
                
                <div class="ccm-field">
                    <label for="max_age"><?php _e('Maximum Age', 'credit-card-manager'); ?></label>
                    <input type="text" id="max_age" name="max_age" value="<?php echo esc_attr($max_age); ?>" placeholder="65 years" />
                </div>
                
                <div class="ccm-field">
                    <label for="apply_link"><?php _e('Application Link', 'credit-card-manager'); ?></label>
                    <input type="url" id="apply_link" name="apply_link" value="<?php echo esc_url($apply_link); ?>" placeholder="https://www.bank.com/apply" />
                </div>
            </div>
            
            <div class="ccm-field-group">
                <h3><?php _e('Pros', 'credit-card-manager'); ?></h3>
                    <div class="ccm-array-field" id="pros-field">
                   <?php if (!empty($pros)): ?>
                       <?php foreach ($pros as $index => $pro): ?>
                           <div class="ccm-array-item">
                               <input type="text" name="pros[]" value="<?php echo esc_attr($pro); ?>" placeholder="Enter a pro" />
                               <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                           </div>
                       <?php endforeach; ?>
                   <?php else: ?>
                       <div class="ccm-array-item">
                           <input type="text" name="pros[]" value="" placeholder="Enter a pro" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endif; ?>
                   <button type="button" class="ccm-add-item" onclick="addArrayItem('pros-field', 'pros[]', 'Enter a pro')"><?php _e('Add Pro', 'credit-card-manager'); ?></button>
               </div>
           </div>
           
           <div class="ccm-field-group">
               <h3><?php _e('Cons', 'credit-card-manager'); ?></h3>
               <div class="ccm-array-field" id="cons-field">
                   <?php if (!empty($cons)): ?>
                       <?php foreach ($cons as $index => $con): ?>
                           <div class="ccm-array-item">
                               <input type="text" name="cons[]" value="<?php echo esc_attr($con); ?>" placeholder="Enter a con" />
                               <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                           </div>
                       <?php endforeach; ?>
                   <?php else: ?>
                       <div class="ccm-array-item">
                           <input type="text" name="cons[]" value="" placeholder="Enter a con" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endif; ?>
                   <button type="button" class="ccm-add-item" onclick="addArrayItem('cons-field', 'cons[]', 'Enter a con')"><?php _e('Add Con', 'credit-card-manager'); ?></button>
               </div>
           </div>
           
           <div class="ccm-field-group">
               <h3><?php _e('Best For', 'credit-card-manager'); ?></h3>
               <div class="ccm-array-field" id="best-for-field">
                   <?php if (!empty($best_for)): ?>
                       <?php foreach ($best_for as $index => $item): ?>
                           <div class="ccm-array-item">
                               <input type="text" name="best_for[]" value="<?php echo esc_attr($item); ?>" placeholder="Enter target audience" />
                               <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                           </div>
                       <?php endforeach; ?>
                   <?php else: ?>
                       <div class="ccm-array-item">
                           <input type="text" name="best_for[]" value="" placeholder="Enter target audience" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endif; ?>
                   <button type="button" class="ccm-add-item" onclick="addArrayItem('best-for-field', 'best_for[]', 'Enter target audience')"><?php _e('Add Item', 'credit-card-manager'); ?></button>
               </div>
           </div>
           
           <div class="ccm-field-group">
               <h3><?php _e('Required Documents', 'credit-card-manager'); ?></h3>
               <div class="ccm-array-field" id="documents-field">
                   <?php if (!empty($documents)): ?>
                       <?php foreach ($documents as $index => $document): ?>
                           <div class="ccm-array-item">
                               <input type="text" name="documents[]" value="<?php echo esc_attr($document); ?>" placeholder="Enter required document" />
                               <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                           </div>
                       <?php endforeach; ?>
                   <?php else: ?>
                       <div class="ccm-array-item">
                           <input type="text" name="documents[]" value="" placeholder="Enter required document" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endif; ?>
                   <button type="button" class="ccm-add-item" onclick="addArrayItem('documents-field', 'documents[]', 'Enter required document')"><?php _e('Add Document', 'credit-card-manager'); ?></button>
               </div>
           </div>
       </div>
       
       <script>
       function addArrayItem(containerId, inputName, placeholder) {
           const container = document.getElementById(containerId);
           const newItem = document.createElement('div');
           newItem.className = 'ccm-array-item';
           newItem.innerHTML = `
               <input type="text" name="${inputName}" value="" placeholder="${placeholder}" />
               <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
           `;
           container.insertBefore(newItem, container.lastElementChild);
       }
       
       function removeArrayItem(button) {
           const item = button.parentElement;
           const container = item.parentElement;
           if (container.querySelectorAll('.ccm-array-item').length > 1) {
               item.remove();
           }
       }
       </script>
       <?php
   }
   
   /**
    * Save Meta Data
    */
   public function save_meta_data($post_id) {
       // Verify nonce
       if (!isset($_POST['credit_card_meta_box_nonce']) || 
           !wp_verify_nonce($_POST['credit_card_meta_box_nonce'], 'credit_card_meta_box')) {
           return;
       }
       
       // Check if user has permission
       if (!current_user_can('edit_post', $post_id)) {
           return;
       }
       
       // Don't save on autosave
       if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
           return;
       }
       
       // Only save for credit-card post type
       if (get_post_type($post_id) !== 'credit-card') {
           return;
       }
       
       // Save simple fields
       $simple_fields = array(
           'card_image_url', 'rating', 'review_count', 'annual_fee', 'joining_fee',
           'welcome_bonus', 'welcome_bonus_points', 'welcome_bonus_type', 'cashback_rate',
           'credit_limit', 'interest_rate', 'processing_time', 'min_income',
           'min_age', 'max_age', 'apply_link', 'theme_color'
       );
       
       foreach ($simple_fields as $field) {
           if (isset($_POST[$field])) {
               update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
           }
       }
       
       // Save boolean fields
       update_post_meta($post_id, 'featured', isset($_POST['featured']) ? 1 : 0);
       update_post_meta($post_id, 'trending', isset($_POST['trending']) ? 1 : 0);
       
       // Save array fields
       $array_fields = array('pros', 'cons', 'best_for', 'documents');
       foreach ($array_fields as $field) {
           if (isset($_POST[$field]) && is_array($_POST[$field])) {
               $clean_array = array_filter(array_map('sanitize_text_field', $_POST[$field]));
               update_post_meta($post_id, $field, $clean_array);
           }
       }
   }
   
   /**
    * Add Admin Columns
    */
   public function add_admin_columns($columns) {
       $new_columns = array();
       foreach ($columns as $key => $value) {
           $new_columns[$key] = $value;
           if ($key === 'title') {
               $new_columns['card_image'] = __('Image', 'credit-card-manager');
               $new_columns['rating'] = __('Rating', 'credit-card-manager');
               $new_columns['annual_fee'] = __('Annual Fee', 'credit-card-manager');
               $new_columns['network_type'] = __('Network', 'credit-card-manager');
               $new_columns['bank'] = __('Bank', 'credit-card-manager');
               $new_columns['featured'] = __('Featured', 'credit-card-manager');
           }
       }
       return $new_columns;
   }
   
   /**
    * Admin Column Content
    */
   public function admin_column_content($column, $post_id) {
       switch ($column) {
           case 'card_image':
               $image_url = get_post_meta($post_id, 'card_image_url', true);
               if ($image_url) {
                   echo '<img src="' . esc_url($image_url) . '" style="width: 50px; height: auto;" />';
               } elseif (has_post_thumbnail($post_id)) {
                   echo get_the_post_thumbnail($post_id, array(50, 50));
               } else {
                   echo '-';
               }
               break;
               
           case 'rating':
               $rating = get_post_meta($post_id, 'rating', true);
               if ($rating) {
                   echo esc_html($rating) . '/5 ⭐';
               } else {
                   echo '-';
               }
               break;
               
           case 'annual_fee':
               $fee = get_post_meta($post_id, 'annual_fee', true);
               echo $fee ? esc_html($fee) : '-';
               break;
               
           case 'network_type':
               $terms = get_the_terms($post_id, 'network-type');
               if ($terms && !is_wp_error($terms)) {
                   $names = wp_list_pluck($terms, 'name');
                   echo esc_html(implode(', ', $names));
               } else {
                   echo '-';
               }
               break;
               
           case 'bank':
               $terms = get_the_terms($post_id, 'store');
               if ($terms && !is_wp_error($terms)) {
                   $names = wp_list_pluck($terms, 'name');
                   echo esc_html(implode(', ', $names));
               } else {
                   echo '-';
               }
               break;
               
           case 'featured':
               $featured = get_post_meta($post_id, 'featured', true);
               echo $featured ? '✅' : '-';
               break;
       }
   }
   
   /**
    * Sortable Columns
    */
   public function sortable_columns($columns) {
       $columns['rating'] = 'rating';
       $columns['annual_fee'] = 'annual_fee';
       return $columns;
   }
   
   /**
    * Register REST API Routes
    */
   public function register_rest_routes() {
       // Custom endpoint for advanced filtering
       register_rest_route('ccm/v1', '/credit-cards', array(
           'methods' => 'GET',
           'callback' => array($this, 'get_credit_cards_api'),
           'permission_callback' => '__return_true',
           'args' => array(
               'bank' => array(
                   'description' => 'Filter by bank (store taxonomy)',
                   'type' => 'string',
               ),
               'network_type' => array(
                   'description' => 'Filter by network type',
                   'type' => 'string',
               ),
               'category' => array(
                   'description' => 'Filter by category',
                   'type' => 'string',
               ),
               'min_rating' => array(
                   'description' => 'Minimum rating filter',
                   'type' => 'number',
               ),
               'max_annual_fee' => array(
                   'description' => 'Maximum annual fee (numeric value)',
                   'type' => 'number',
               ),
               'featured' => array(
                   'description' => 'Filter featured cards',
                   'type' => 'boolean',
               ),
               'trending' => array(
                   'description' => 'Filter trending cards',
                   'type' => 'boolean',
               ),
               'min_income_range' => array(
                   'description' => 'Filter by income requirement range',
                   'type' => 'string',
               ),
               'sort_by' => array(
                   'description' => 'Sort by field (rating, annual_fee, review_count)',
                   'type' => 'string',
                   'default' => 'rating',
               ),
               'sort_order' => array(
                   'description' => 'Sort order (asc, desc)',
                   'type' => 'string',
                   'default' => 'desc',
               ),
               'per_page' => array(
                   'description' => 'Items per page',
                   'type' => 'integer',
                   'default' => 10,
               ),
               'page' => array(
                   'description' => 'Page number',
                   'type' => 'integer',
                   'default' => 1,
               ),
               's' => array(
                   'description' => 'Search term',
                   'type' => 'string',
               ),
           ),
       ));
       
       // Endpoint for single credit card
       register_rest_route('ccm/v1', '/credit-cards/(?P<id>\d+)', array(
           'methods' => 'GET',
           'callback' => array($this, 'get_single_credit_card_api'),
           'permission_callback' => '__return_true',
       ));
       
       // Endpoint for filters/facets
       register_rest_route('ccm/v1', '/credit-cards/filters', array(
           'methods' => 'GET',
           'callback' => array($this, 'get_filters_api'),
           'permission_callback' => '__return_true',
       ));
   }
   
   /**
    * GET Credit Cards API
    */
   public function get_credit_cards_api($request) {
       $params = $request->get_params();
       
       // Base query args
       $args = array(
           'post_type' => 'credit-card',
           'post_status' => 'publish',
           'posts_per_page' => isset($params['per_page']) ? intval($params['per_page']) : 10,
           'paged' => isset($params['page']) ? intval($params['page']) : 1,
           'meta_query' => array(),
           'tax_query' => array(),
       );

       // Search parameter
       if (!empty($params['s'])) {
           $args['s'] = sanitize_text_field($params['s']);
       }
       
       // Taxonomy filters
       if (!empty($params['bank'])) {
           $args['tax_query'][] = array(
               'taxonomy' => 'store',
               'field'    => 'slug',
               'terms'    => explode(',', $params['bank']),
           );
       }
       
       if (!empty($params['network_type'])) {
           $args['tax_query'][] = array(
               'taxonomy' => 'network-type',
               'field'    => 'slug',
               'terms'    => explode(',', $params['network_type']),
           );
       }
       
       if (!empty($params['category'])) {
           $args['tax_query'][] = array(
               'taxonomy' => 'category',
               'field'    => 'slug',
               'terms'    => explode(',', $params['category']),
           );
       }
       
       // Meta filters
       if (!empty($params['min_rating'])) {
           $args['meta_query'][] = array(
               'key'     => 'rating',
               'value'   => floatval($params['min_rating']),
               'compare' => '>=',
               'type'    => 'DECIMAL',
           );
       }
       
       if (!empty($params['max_annual_fee'])) {
           $args['meta_query'][] = array(
               'key'     => 'annual_fee_numeric',
               'value'   => intval($params['max_annual_fee']),
               'compare' => '<=',
               'type'    => 'NUMERIC',
           );
       }
       
       if (isset($params['featured']) && $params['featured'] !== '') {
           $args['meta_query'][] = array(
               'key'     => 'featured',
               'value'   => $params['featured'] ? '1' : '0',
               'compare' => '=',
           );
       }
       
       if (isset($params['trending']) && $params['trending'] !== '') {
           $args['meta_query'][] = array(
               'key'     => 'trending',
               'value'   => $params['trending'] ? '1' : '0',
               'compare' => '=',
           );
       }
       
       // Income range filter
       if (!empty($params['min_income_range'])) {
           $income_ranges = array(
               'low' => array(0, 300000),
               'medium' => array(300000, 1000000),
               'high' => array(1000000, 9999999),
           );
           
           if (isset($income_ranges[$params['min_income_range']])) {
               $range = $income_ranges[$params['min_income_range']];
               $args['meta_query'][] = array(
                   'key'     => 'min_income_numeric',
                   'value'   => $range,
                   'compare' => 'BETWEEN',
                   'type'    => 'NUMERIC',
               );
           }
       }
       
       // Sorting
       $sort_by = isset($params['sort_by']) ? $params['sort_by'] : 'rating';
       $sort_order = isset($params['sort_order']) ? strtoupper($params['sort_order']) : 'DESC';
       
       switch ($sort_by) {
           case 'rating':
               $args['meta_key'] = 'rating';
               $args['orderby'] = 'meta_value_num';
               $args['order'] = $sort_order;
               break;
           case 'annual_fee':
               $args['meta_key'] = 'annual_fee_numeric';
               $args['orderby'] = 'meta_value_num';
               $args['order'] = $sort_order;
               break;
           case 'review_count':
               $args['meta_key'] = 'review_count';
               $args['orderby'] = 'meta_value_num';
               $args['order'] = $sort_order;
               break;
           default:
               $args['orderby'] = 'date';
               $args['order'] = $sort_order;
       }
       
       // Set relation for meta_query if multiple conditions
       if (count($args['meta_query']) > 1) {
           $args['meta_query']['relation'] = 'AND';
       }
       
       // Set relation for tax_query if multiple conditions
       if (count($args['tax_query']) > 1) {
           $args['tax_query']['relation'] = 'AND';
       }
       
       $query = new WP_Query($args);
       $cards = array();
       
       if ($query->have_posts()) {
           while ($query->have_posts()) {
               $query->the_post();
               $cards[] = $this->format_credit_card_data(get_the_ID());
           }
           wp_reset_postdata();
       }
       
       return new WP_REST_Response(array(
           'data' => $cards,
           'pagination' => array(
               'total' => $query->found_posts,
               'pages' => $query->max_num_pages,
               'current_page' => intval($params['page']),
               'per_page' => intval($params['per_page']),
           ),
           'filters_applied' => array_filter($params, function($key) {
               return !in_array($key, array('page', 'per_page', 'sort_by', 'sort_order'));
           }, ARRAY_FILTER_USE_KEY),
       ), 200);
   }
   
   /**
    * GET Single Credit Card API
    */
   public function get_single_credit_card_api($request) {
       $id = (int) $request['id'];
       $post = get_post($id);
       
       if (!$post || $post->post_type !== 'credit-card') {
           return new WP_Error('card_not_found', 'Credit card not found', array('status' => 404));
       }
       
       return new WP_REST_Response($this->format_credit_card_data($id), 200);
   }
   
   /**
    * GET Filters API
    */
   public function get_filters_api($request) {
       $filters = array();
       
       // Get banks (store taxonomy)
       $banks = get_terms(array(
           'taxonomy' => 'store',
           'hide_empty' => true,
       ));
       
       $filters['banks'] = array();
       if (!is_wp_error($banks)) {
           foreach ($banks as $bank) {
               $filters['banks'][] = array(
                   'id' => $bank->term_id,
                   'name' => $bank->name,
                   'slug' => $bank->slug,
                   'count' => $bank->count,
               );
           }
       }
       
       // Get network types
       $network_types = get_terms(array(
           'taxonomy' => 'network-type',
           'hide_empty' => true,
       ));
       
       $filters['network_types'] = array();
       if (!is_wp_error($network_types)) {
           foreach ($network_types as $network) {
               $filters['network_types'][] = array(
                   'id' => $network->term_id,
                   'name' => $network->name,
                   'slug' => $network->slug,
                   'count' => $network->count,
               );
           }
       }
       
       // Get categories
       $categories = get_terms(array(
           'taxonomy' => 'category',
           'hide_empty' => true,
       ));
       
       $filters['categories'] = array();
       if (!is_wp_error($categories)) {
           foreach ($categories as $category) {
               $filters['categories'][] = array(
                   'id' => $category->term_id,
                   'name' => $category->name,
                   'slug' => $category->slug,
                   'count' => $category->count,
               );
           }
       }
       
       // Get rating ranges
       $filters['rating_ranges'] = array(
           array('label' => '4+ Stars', 'value' => '4', 'min' => 4),
           array('label' => '3+ Stars', 'value' => '3', 'min' => 3),
           array('label' => '2+ Stars', 'value' => '2', 'min' => 2),
           array('label' => '1+ Stars', 'value' => '1', 'min' => 1),
       );
       
       // Get fee ranges
       $filters['fee_ranges'] = array(
           array('label' => 'Free', 'value' => '0', 'max' => 0),
           array('label' => 'Under ₹1,000', 'value' => '1000', 'max' => 1000),
           array('label' => 'Under ₹2,500', 'value' => '2500', 'max' => 2500),
           array('label' => 'Under ₹5,000', 'value' => '5000', 'max' => 5000),
       );
       
       // Get income ranges
       $filters['income_ranges'] = array(
           array('label' => 'Up to ₹3 Lakh', 'value' => 'low', 'max' => 300000),
           array('label' => '₹3-10 Lakh', 'value' => 'medium', 'min' => 300000, 'max' => 1000000),
           array('label' => '₹10+ Lakh', 'value' => 'high', 'min' => 1000000),
       );
       
       return new WP_REST_Response($filters, 200);
   }
   
   /**
    * Format Credit Card Data for API
    */
   public function format_credit_card_data($post_id) {
       $post = get_post($post_id);
       
       // Get taxonomies
   $banks = wp_get_post_terms($post_id, 'store');
$network_types = wp_get_post_terms($post_id, 'network-type');
$categories = wp_get_post_terms($post_id, 'category');

// Handle WP_Error cases
if (is_wp_error($banks)) $banks = array();
if (is_wp_error($network_types)) $network_types = array();
if (is_wp_error($categories)) $categories = array();
       
       // Get meta fields
       $meta_fields = array(
           'card_image_url', 'rating', 'review_count', 'annual_fee', 'joining_fee',
           'welcome_bonus', 'welcome_bonus_points', 'welcome_bonus_type', 'cashback_rate',
           'credit_limit', 'interest_rate', 'processing_time', 'min_income',
           'min_age', 'max_age', 'pros', 'cons', 'best_for', 'features',
           'rewards', 'fees', 'eligibility', 'documents', 'apply_link',
           'featured', 'trending', 'gradient', 'bg_gradient', 'theme_color',
           'overall_score', 'reward_score', 'fees_score', 'benefits_score',
           'support_score', 'reward_rate'
       );
       
       $meta_data = array();
       foreach ($meta_fields as $field) {
           $meta_data[$field] = get_post_meta($post_id, $field, true);
       }
       
       // Get featured image
       $featured_image = '';
       if (has_post_thumbnail($post_id)) {
           $featured_image = get_the_post_thumbnail_url($post_id, 'large');
       }
       
       return array(
           'id' => $post_id,
           'title' => $post->post_title,
           'slug' => $post->post_name,
           'content' => $post->post_content,
           'excerpt' => $post->post_excerpt,
           'status' => $post->post_status,
           'date' => $post->post_date,
           'modified' => $post->post_modified,
           'featured_image' => $featured_image,
           'link' => get_permalink($post_id),
           
           // Taxonomies
          'bank' => (!empty($banks) && !is_wp_error($banks)) ? array(
    'id' => $banks[0]->term_id,
    'name' => $banks[0]->name,
    'slug' => $banks[0]->slug,
) : null,
'network_type' => (!empty($network_types) && !is_wp_error($network_types)) ? array(
    'id' => $network_types[0]->term_id,
    'name' => $network_types[0]->name,
    'slug' => $network_types[0]->slug,
) : null,
'categories' => (!empty($categories) && !is_wp_error($categories)) ? array_map(function($cat) {
    return array(
        'id' => $cat->term_id,
        'name' => $cat->name,
        'slug' => $cat->slug,
    );
}, $categories) : array(),
           
           // Meta data
           'card_image' => $meta_data['card_image_url'] ?: $featured_image,
           'rating' => floatval($meta_data['rating']),
           'review_count' => intval($meta_data['review_count']),
           'annual_fee' => $meta_data['annual_fee'],
           'joining_fee' => $meta_data['joining_fee'],
           'welcome_bonus' => $meta_data['welcome_bonus'],
           'welcome_bonus_points' => intval($meta_data['welcome_bonus_points']),
           'welcome_bonus_type' => $meta_data['welcome_bonus_type'],
           'cashback_rate' => $meta_data['cashback_rate'],
           'credit_limit' => $meta_data['credit_limit'],
           'interest_rate' => $meta_data['interest_rate'],
           'processing_time' => $meta_data['processing_time'],
           'min_income' => $meta_data['min_income'],
           'min_age' => $meta_data['min_age'],
           'max_age' => $meta_data['max_age'],
           'pros' => $meta_data['pros'] ?: array(),
           'cons' => $meta_data['cons'] ?: array(),
           'best_for' => $meta_data['best_for'] ?: array(),
           'features' => $meta_data['features'] ?: array(),
           'rewards' => $meta_data['rewards'] ?: array(),
           'fees' => $meta_data['fees'] ?: array(),
           'eligibility' => $meta_data['eligibility'] ?: array(),
           'documents' => $meta_data['documents'] ?: array(),
           'apply_link' => $meta_data['apply_link'],
           'featured' => (bool) $meta_data['featured'],
           'trending' => (bool) $meta_data['trending'],
           'gradient' => $meta_data['gradient'],
           'bg_gradient' => $meta_data['bg_gradient'],
           'theme_color' => $meta_data['theme_color'],
           'overall_score' => floatval($meta_data['overall_score']),
           'reward_score' => floatval($meta_data['reward_score']),
           'fees_score' => floatval($meta_data['fees_score']),
           'benefits_score' => floatval($meta_data['benefits_score']),
           'support_score' => floatval($meta_data['support_score']),
           'reward_rate' => floatval($meta_data['reward_rate']),
       );
   }
   
   /**
    * Add REST Query Vars
    */
   public function add_rest_query_vars($valid_vars) {
       $valid_vars = array_merge($valid_vars, array(
           'meta_query', 'tax_query', 'min_rating', 'max_annual_fee',
           'featured', 'trending', 'bank', 'network_type', 'category'
       ));
           return $valid_vars;
   }
   
   /**
    * Filter REST API for default WordPress endpoints
    */
   public function filter_rest_api($args, $request) {
       $params = $request->get_params();
       
       // Filter by bank (store taxonomy)
       if (!empty($params['bank'])) {
           if (!isset($args['tax_query'])) {
               $args['tax_query'] = array();
           }
           $args['tax_query'][] = array(
               'taxonomy' => 'store',
               'field'    => 'slug',
               'terms'    => explode(',', $params['bank']),
           );
       }
       
       // Filter by network type
       if (!empty($params['network_type'])) {
           if (!isset($args['tax_query'])) {
               $args['tax_query'] = array();
           }
           $args['tax_query'][] = array(
               'taxonomy' => 'network-type',
               'field'    => 'slug',
               'terms'    => explode(',', $params['network_type']),
           );
       }
       
       // Filter by category
       if (!empty($params['category'])) {
           if (!isset($args['tax_query'])) {
               $args['tax_query'] = array();
           }
           $args['tax_query'][] = array(
               'taxonomy' => 'category',
               'field'    => 'slug',
               'terms'    => explode(',', $params['category']),
           );
       }
       
       // Meta query filters
       if (!empty($params['min_rating']) || !empty($params['max_annual_fee']) || 
           isset($params['featured']) || isset($params['trending'])) {
           
           if (!isset($args['meta_query'])) {
               $args['meta_query'] = array();
           }
           
           if (!empty($params['min_rating'])) {
               $args['meta_query'][] = array(
                   'key'     => 'rating',
                   'value'   => floatval($params['min_rating']),
                   'compare' => '>=',
                   'type'    => 'DECIMAL',
               );
           }
           
           if (!empty($params['max_annual_fee'])) {
               $args['meta_query'][] = array(
                   'key'     => 'annual_fee_numeric',
                   'value'   => intval($params['max_annual_fee']),
                   'compare' => '<=',
                   'type'    => 'NUMERIC',
               );
           }
           
           if (isset($params['featured'])) {
               $args['meta_query'][] = array(
                   'key'     => 'featured',
                   'value'   => $params['featured'] ? '1' : '0',
                   'compare' => '=',
               );
           }
           
           if (isset($params['trending'])) {
               $args['meta_query'][] = array(
                   'key'     => 'trending',
                   'value'   => $params['trending'] ? '1' : '0',
                   'compare' => '=',
               );
           }
       }
       
       // Set relations if multiple conditions
       if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
           $args['meta_query']['relation'] = 'AND';
       }
       
       if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
           $args['tax_query']['relation'] = 'AND';
       }
       
       return $args;
   }
   
   /**
    * Admin Scripts
    */
   public function admin_scripts($hook) {
       global $post_type;
       
       if ($post_type === 'credit-card' && ($hook === 'post.php' || $hook === 'post-new.php')) {
           wp_enqueue_media();
           wp_enqueue_script(
               'credit-card-admin',
               plugin_dir_url(__FILE__) . 'assets/admin.js',
               array('jquery'),
               $this->version,
               true
           );
           
           wp_localize_script('credit-card-admin', 'ccm_admin', array(
               'ajax_url' => admin_url('admin-ajax.php'),
               'nonce' => wp_create_nonce('ccm_admin_nonce'),
           ));
       }
   }
   
    /**
     * Frontend Scripts
     */
    public function frontend_scripts() {
        if (is_singular('credit-card') || is_post_type_archive('credit-card') || is_page_template('templates/archive-credit-card.php')) {
            // Enqueue dashicons for star ratings and icons
            wp_enqueue_style('dashicons');
            
            wp_enqueue_script(
                'credit-card-frontend',
                plugin_dir_url(__FILE__) . 'assets/frontend.js',
                array('jquery'),
                $this->version,
                true
            );
            
            wp_enqueue_style(
                'credit-card-frontend',
                plugin_dir_url(__FILE__) . 'assets/frontend.css',
                array(),
                $this->version
            );
            
            wp_localize_script('credit-card-frontend', 'ccm_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'api_url' => rest_url('ccm/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
            ));
        }
    }
   
   /**
    * Sanitization Functions
    */
   public function sanitize_rating($value) {
       $value = floatval($value);
       return max(0, min(5, $value));
   }
   
   public function sanitize_percentage($value) {
       $value = floatval($value);
       return max(0, min(100, $value));
   }
   
   public function sanitize_boolean($value) {
       return $value ? 1 : 0;
   }
   
   public function sanitize_array_field($value) {
       if (!is_array($value)) {
           return array();
       }
       return array_map('sanitize_text_field', $value);
   }
   
   public function sanitize_complex_array($value) {
       if (!is_array($value)) {
           return array();
       }
       
       $sanitized = array();
       foreach ($value as $item) {
           if (is_array($item)) {
               $sanitized_item = array();
               foreach ($item as $key => $val) {
                   $sanitized_item[sanitize_key($key)] = sanitize_text_field($val);
               }
               $sanitized[] = $sanitized_item;
           } else {
               $sanitized[] = sanitize_text_field($item);
           }
       }
       return $sanitized;
   }
   
   /**
    * Plugin Activation
    */
   public function activate() {
       $this->init();
       flush_rewrite_rules();
       
       // Create tables for better performance (optional)
       $this->create_performance_tables();
   }
   
   /**
    * Plugin Deactivation
    */
   public function deactivate() {
       flush_rewrite_rules();
   }
   
   /**
    * Create Performance Tables (Optional)
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
    * Update Meta Cache
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
           array(
               '%d', '%d', '%d', '%f', '%d', '%d', '%d'
           )
       );
       
       // Also update individual meta fields for backward compatibility
       update_post_meta($post_id, 'annual_fee_numeric', $annual_fee_numeric);
       update_post_meta($post_id, 'min_income_numeric', $min_income_numeric);
   }
   
   /**
    * Extract Numeric Value from String
    */
   private function extract_numeric_value($string) {
       // Remove currency symbols and commas
       $cleaned = preg_replace('/[^\d.]/', '', $string);
       return intval($cleaned);
   }
}

// Initialize the plugin
new CreditCardManager();

/**
* Template Functions for Theme Integration
*/

/**
* Get Credit Card Data
*/
function ccm_get_credit_card($post_id = null) {
   if (!$post_id) {
       $post_id = get_the_ID();
   }
   
   $manager = new CreditCardManager();
   return $manager->format_credit_card_data($post_id);
}

/**
* Display Credit Card Info
*/
function ccm_display_card_info($post_id = null, $fields = array()) {
   $card = ccm_get_credit_card($post_id);
   
   if (empty($fields)) {
       $fields = array('rating', 'annual_fee', 'network_type', 'bank');
   }
   
   echo '<div class="ccm-card-info">';
   
   foreach ($fields as $field) {
       if (isset($card[$field]) && !empty($card[$field])) {
           echo '<div class="ccm-info-item ccm-' . esc_attr($field) . '">';
           echo '<label>' . esc_html(ucwords(str_replace('_', ' ', $field))) . ':</label>';
           
           if ($field === 'rating') {
               echo '<span class="rating">' . esc_html($card[$field]) . '/5 ⭐</span>';
           } elseif ($field === 'bank' && is_array($card[$field])) {
               echo '<span>' . esc_html($card[$field]['name']) . '</span>';
           } elseif ($field === 'network_type' && is_array($card[$field])) {
               echo '<span>' . esc_html($card[$field]['name']) . '</span>';
           } else {
               echo '<span>' . esc_html($card[$field]) . '</span>';
           }
           
           echo '</div>';
       }
   }
   
   echo '</div>';
}

/**
* Display Credit Card Filters
*/
function ccm_display_filters($atts = array()) {
   $defaults = array(
       'show_banks' => true,
       'show_networks' => true,
       'show_categories' => true,
       'show_rating' => true,
       'show_fees' => true,
       'ajax' => true,
   );
   
   $args = wp_parse_args($atts, $defaults);
   
   // Get filter data from API
   $response = wp_remote_get(rest_url('ccm/v1/credit-cards/filters'));
   
   if (is_wp_error($response)) {
       return '';
   }
   
   $filters = json_decode(wp_remote_retrieve_body($response), true);
   
   ob_start();
   ?>
   <div class="ccm-filters" data-ajax="<?php echo $args['ajax'] ? 'true' : 'false'; ?>">
       <form class="ccm-filter-form" method="get">
           
           <?php if ($args['show_banks'] && !empty($filters['banks'])): ?>
           <div class="ccm-filter-group">
               <label><?php _e('Bank', 'credit-card-manager'); ?></label>
               <select name="bank" class="ccm-filter-select">
                   <option value=""><?php _e('All Banks', 'credit-card-manager'); ?></option>
                   <?php foreach ($filters['banks'] as $bank): ?>
                       <option value="<?php echo esc_attr($bank['slug']); ?>" 
                               <?php selected(isset($_GET['bank']) ? $_GET['bank'] : '', $bank['slug']); ?>>
                           <?php echo esc_html($bank['name']) . ' (' . $bank['count'] . ')'; ?>
                       </option>
                   <?php endforeach; ?>
               </select>
           </div>
           <?php endif; ?>
           
           <?php if ($args['show_networks'] && !empty($filters['network_types'])): ?>
           <div class="ccm-filter-group">
               <label><?php _e('Network Type', 'credit-card-manager'); ?></label>
               <select name="network_type" class="ccm-filter-select">
                   <option value=""><?php _e('All Networks', 'credit-card-manager'); ?></option>
                   <?php foreach ($filters['network_types'] as $network): ?>
                       <option value="<?php echo esc_attr($network['slug']); ?>"
                               <?php selected(isset($_GET['network_type']) ? $_GET['network_type'] : '', $network['slug']); ?>>
                           <?php echo esc_html($network['name']) . ' (' . $network['count'] . ')'; ?>
                       </option>
                   <?php endforeach; ?>
               </select>
           </div>
           <?php endif; ?>
           
           <?php if ($args['show_categories'] && !empty($filters['categories'])): ?>
           <div class="ccm-filter-group">
               <label><?php _e('Category', 'credit-card-manager'); ?></label>
               <select name="category" class="ccm-filter-select">
                   <option value=""><?php _e('All Categories', 'credit-card-manager'); ?></option>
                   <?php foreach ($filters['categories'] as $category): ?>
                       <option value="<?php echo esc_attr($category['slug']); ?>"
                               <?php selected(isset($_GET['category']) ? $_GET['category'] : '', $category['slug']); ?>>
                           <?php echo esc_html($category['name']) . ' (' . $category['count'] . ')'; ?>
                       </option>
                   <?php endforeach; ?>
               </select>
           </div>
           <?php endif; ?>
           
           <?php if ($args['show_rating']): ?>
           <div class="ccm-filter-group">
               <label><?php _e('Minimum Rating', 'credit-card-manager'); ?></label>
               <select name="min_rating" class="ccm-filter-select">
                   <option value=""><?php _e('Any Rating', 'credit-card-manager'); ?></option>
                   <?php foreach ($filters['rating_ranges'] as $range): ?>
                       <option value="<?php echo esc_attr($range['min']); ?>"
                               <?php selected(isset($_GET['min_rating']) ? $_GET['min_rating'] : '', $range['min']); ?>>
                           <?php echo esc_html($range['label']); ?>
                       </option>
                   <?php endforeach; ?>
               </select>
           </div>
           <?php endif; ?>
           
           <?php if ($args['show_fees']): ?>
           <div class="ccm-filter-group">
               <label><?php _e('Maximum Annual Fee', 'credit-card-manager'); ?></label>
               <select name="max_annual_fee" class="ccm-filter-select">
                   <option value=""><?php _e('Any Fee', 'credit-card-manager'); ?></option>
                   <?php foreach ($filters['fee_ranges'] as $range): ?>
                       <option value="<?php echo esc_attr($range['max']); ?>"
                               <?php selected(isset($_GET['max_annual_fee']) ? $_GET['max_annual_fee'] : '', $range['max']); ?>>
                           <?php echo esc_html($range['label']); ?>
                       </option>
                   <?php endforeach; ?>
               </select>
           </div>
           <?php endif; ?>
           
           <div class="ccm-filter-group">
               <button type="submit" class="ccm-filter-submit">
                   <?php _e('Filter Cards', 'credit-card-manager'); ?>
               </button>
               <button type="button" class="ccm-filter-reset">
                   <?php _e('Reset', 'credit-card-manager'); ?>
               </button>
           </div>
           
       </form>
   </div>
   
   <style>
   .ccm-filters {
       margin-bottom: 30px;
       padding: 20px;
       background: #f8f9fa;
       border: 1px solid #e9ecef;
       border-radius: 8px;
   }
   
   .ccm-filter-form {
       display: flex;
       flex-wrap: wrap;
       gap: 15px;
       align-items: end;
   }
   
   .ccm-filter-group {
       display: flex;
       flex-direction: column;
       min-width: 150px;
   }
   
   .ccm-filter-group label {
       font-weight: 600;
       margin-bottom: 5px;
       color: #333;
   }
   
   .ccm-filter-select {
       padding: 8px 12px;
       border: 1px solid #ddd;
       border-radius: 4px;
       background: white;
   }
   
   .ccm-filter-submit, .ccm-filter-reset {
       padding: 8px 16px;
       border: none;
       border-radius: 4px;
       cursor: pointer;
       font-weight: 600;
   }
   
   .ccm-filter-submit {
       background: #007cba;
       color: white;
   }
   
   .ccm-filter-reset {
       background: #6c757d;
       color: white;
   }
   
   .ccm-filter-submit:hover {
       background: #005a87;
   }
   
   .ccm-filter-reset:hover {
       background: #545b62;
   }
   
   @media (max-width: 768px) {
       .ccm-filter-form {
           flex-direction: column;
       }
       
       .ccm-filter-group {
           width: 100%;
       }
   }
   </style>
   <?php
   
   return ob_get_clean();
}

/**
* Shortcode for displaying filters
*/
function ccm_filters_shortcode($atts) {
   return ccm_display_filters($atts);
}
add_shortcode('ccm_filters', 'ccm_filters_shortcode');

/**
* Shortcode for displaying credit card grid
*/
function ccm_cards_grid_shortcode($atts) {
   $defaults = array(
       'limit' => 12,
       'bank' => '',
       'network_type' => '',
       'category' => '',
       'featured' => '',
       'trending' => '',
       'min_rating' => '',
       'sort_by' => 'rating',
       'sort_order' => 'desc',
       'show_filters' => true,
   );
   
   $args = wp_parse_args($atts, $defaults);
   
   // Build API URL
   $api_url = rest_url('ccm/v1/credit-cards');
   $query_params = array();
   
   foreach ($args as $key => $value) {
       if (!empty($value) && $key !== 'show_filters') {
           $query_params[$key] = $value;
       }
   }
   
   if (!empty($query_params)) {
       $api_url .= '?' . http_build_query($query_params);
   }
   
   // Fetch data
   $response = wp_remote_get($api_url);
   
   if (is_wp_error($response)) {
       return '<p>' . __('Error loading credit cards.', 'credit-card-manager') . '</p>';
   }
   
   $data = json_decode(wp_remote_retrieve_body($response), true);
   $cards = isset($data['data']) ? $data['data'] : array();
   
   ob_start();
   ?>
   <div class="ccm-cards-container">
       <?php if ($args['show_filters']): ?>
           <?php echo ccm_display_filters(); ?>
       <?php endif; ?>
       
       <div class="ccm-cards-grid" id="ccm-cards-results">
           <?php if (!empty($cards)): ?>
               <?php foreach ($cards as $card): ?>
                   <div class="ccm-card-item" data-id="<?php echo esc_attr($card['id']); ?>">
                       <div class="ccm-card-inner">
                           
                           <?php if (!empty($card['card_image'])): ?>
                           <div class="ccm-card-image">
                               <img src="<?php echo esc_url($card['card_image']); ?>" 
                                    alt="<?php echo esc_attr($card['title']); ?>" />
                                    
                               <?php if ($card['featured']): ?>
                                   <span class="ccm-badge ccm-featured"><?php _e('Featured', 'credit-card-manager'); ?></span>
                               <?php endif; ?>
                               
                               <?php if ($card['trending']): ?>
                                   <span class="ccm-badge ccm-trending"><?php _e('Trending', 'credit-card-manager'); ?></span>
                               <?php endif; ?>
                           </div>
                           <?php endif; ?>
                           
                           <div class="ccm-card-content">
                               <h3 class="ccm-card-title">
                                   <a href="<?php echo esc_url($card['link']); ?>">
                                       <?php echo esc_html($card['title']); ?>
                                   </a>
                               </h3>
                               
                               <?php if ($card['bank']): ?>
                               <div class="ccm-card-bank">
                                   <?php echo esc_html($card['bank']['name']); ?>
                               </div>
                               <?php endif; ?>
                               
                               <div class="ccm-card-meta">
                                   <?php if ($card['rating']): ?>
                                   <div class="ccm-rating">
                                       <span class="ccm-stars"><?php echo str_repeat('⭐', floor($card['rating'])); ?></span>
                                       <span class="ccm-rating-number"><?php echo esc_html($card['rating']); ?>/5</span>
                                       <?php if ($card['review_count']): ?>
                                           <span class="ccm-review-count">(<?php echo esc_html($card['review_count']); ?> reviews)</span>
                                       <?php endif; ?>
                                   </div>
                                   <?php endif; ?>
                                   
                                   <?php if ($card['annual_fee']): ?>
                                   <div class="ccm-annual-fee">
                                       <strong><?php _e('Annual Fee:', 'credit-card-manager'); ?></strong>
                                       <?php echo esc_html($card['annual_fee']); ?>
                                   </div>
                                   <?php endif; ?>
                                   
                                   <?php if ($card['cashback_rate']): ?>
                                   <div class="ccm-cashback-rate">
                                       <strong><?php _e('Reward Rate:', 'credit-card-manager'); ?></strong>
                                       <?php echo esc_html($card['cashback_rate']); ?>
                                   </div>
                                   <?php endif; ?>
                               </div>
                               
                               <?php if (!empty($card['excerpt'])): ?>
                               <div class="ccm-card-excerpt">
                                   <?php echo wp_kses_post($card['excerpt']); ?>
                               </div>
                               <?php endif; ?>
                               
                               <div class="ccm-card-actions">
                                   <a href="<?php echo esc_url($card['link']); ?>" class="ccm-btn ccm-btn-details">
                                       <?php _e('View Details', 'credit-card-manager'); ?>
                                   </a>
                                   
                                   <?php if (!empty($card['apply_link'])): ?>
                                   <a href="<?php echo esc_url($card['apply_link']); ?>" 
                                      class="ccm-btn ccm-btn-apply" 
                                      target="_blank" 
                                      rel="noopener noreferrer">
                                       <?php _e('Apply Now', 'credit-card-manager'); ?>
                                   </a>
                                   <?php endif; ?>
                               </div>
                           </div>
                       </div>
                   </div>
               <?php endforeach; ?>
           <?php else: ?>
               <div class="ccm-no-results">
                   <p><?php _e('No credit cards found matching your criteria.', 'credit-card-manager'); ?></p>
               </div>
           <?php endif; ?>
       </div>
       
       <?php if (isset($data['pagination']) && $data['pagination']['pages'] > 1): ?>
       <div class="ccm-pagination">
           <?php
           $pagination = $data['pagination'];
           $current_page = $pagination['current_page'];
           $total_pages = $pagination['pages'];
           
           // Simple pagination
           if ($current_page > 1) {
               echo '<a href="#" class="ccm-page-link" data-page="' . ($current_page - 1) . '">&laquo; Previous</a>';
           }
           
           for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
               $class = $i === $current_page ? 'ccm-page-link ccm-current' : 'ccm-page-link';
               echo '<a href="#" class="' . $class . '" data-page="' . $i . '">' . $i . '</a>';
           }
           
           if ($current_page < $total_pages) {
               echo '<a href="#" class="ccm-page-link" data-page="' . ($current_page + 1) . '">Next &raquo;</a>';
           }
           ?>
       </div>
       <?php endif; ?>
   </div>
   
   <style>
   .ccm-cards-grid {
       display: grid;
       grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
       gap: 20px;
       margin-bottom: 30px;
   }
   
   .ccm-card-item {
       border: 1px solid #e9ecef;
       border-radius: 8px;
       overflow: hidden;
       background: white;
       transition: transform 0.2s, box-shadow 0.2s;
   }
   
   .ccm-card-item:hover {
       transform: translateY(-2px);
       box-shadow: 0 4px 12px rgba(0,0,0,0.1);
   }
   
   .ccm-card-image {
       position: relative;
       height: 200px;
       overflow: hidden;
   }
   
   .ccm-card-image img {
       width: 100%;
       height: 100%;
       object-fit: cover;
   }
   
   .ccm-badge {
       position: absolute;
       top: 10px;
       right: 10px;
       padding: 4px 8px;
       border-radius: 4px;
       font-size: 12px;
       font-weight: 600;
       color: white;
   }
   
   .ccm-featured {
       background: #28a745;
   }
   
   .ccm-trending {
       background: #ffc107;
       color: #333;
       top: 35px;
   }
   
   .ccm-card-content {
       padding: 20px;
   }
   
   .ccm-card-title {
       margin: 0 0 10px 0;
       font-size: 18px;
   }
   
   .ccm-card-title a {
       text-decoration: none;
       color: #333;
   }
   
   .ccm-card-title a:hover {
       color: #007cba;
   }
   
   .ccm-card-bank {
       color: #666;
       font-size: 14px;
       margin-bottom: 15px;
   }
   
   .ccm-card-meta {
       margin-bottom: 15px;
   }
   
   .ccm-rating {
       margin-bottom: 8px;
   }
   
   .ccm-rating-number {
       font-weight: 600;
       margin-left: 5px;
   }
   
   .ccm-review-count {
       color: #666;
       font-size: 12px;
       margin-left: 5px;
   }
   
   .ccm-annual-fee, .ccm-cashback-rate {
       font-size: 14px;
       margin-bottom: 5px;
   }
   
   .ccm-card-excerpt {
       color: #666;
       font-size: 14px;
       line-height: 1.5;
       margin-bottom: 15px;
   }
   
   .ccm-card-actions {
       display: flex;
       gap: 10px;
   }
   
   .ccm-btn {
       padding: 8px 16px;
       border: none;
       border-radius: 4px;
       text-decoration: none;
       font-weight: 600;
       font-size: 14px;
       text-align: center;
       cursor: pointer;
       transition: background-color 0.2s;
       flex: 1;
   }
   
   .ccm-btn-details {
       background: #f8f9fa;
       color: #333;
       border: 1px solid #dee2e6;
   }
   
   .ccm-btn-details:hover {
       background: #e9ecef;
   }
   
   .ccm-btn-apply {
       background: #007cba;
       color: white;
   }
   
   .ccm-btn-apply:hover {
       background: #005a87;
   }
   
   .ccm-no-results {
       grid-column: 1 / -1;
       text-align: center;
       padding: 40px;
       color: #666;
   }
   
   .ccm-pagination {
       display: flex;
       justify-content: center;
       gap: 5px;
       margin-top: 30px;
   }
   
   .ccm-page-link {
       padding: 8px 12px;
       border: 1px solid #dee2e6;
       background: white;
       color: #007cba;
       text-decoration: none;
       border-radius: 4px;
       transition: background-color 0.2s;
   }
   
   .ccm-page-link:hover {
       background: #f8f9fa;
   }
   
   .ccm-page-link.ccm-current {
       background: #007cba;
       color: white;
       border-color: #007cba;
   }
   
   @media (max-width: 768px) {
       .ccm-cards-grid {
           grid-template-columns: 1fr;
       }
       
       .ccm-card-actions {
           flex-direction: column;
       }
   }
   </style>
   <?php
   
   return ob_get_clean();
}
add_shortcode('ccm_cards_grid', 'ccm_cards_grid_shortcode');
?>
