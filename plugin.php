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

// Include shortcodes
require_once plugin_dir_path(__FILE__) . 'shortcodes.php';

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
        $credit_limit = get_post_meta($
