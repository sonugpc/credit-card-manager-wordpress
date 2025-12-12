<?php
/**
 * Post Types Class
 * Handles custom post type registration and taxonomies
 */

if (!defined('ABSPATH')) {
    exit;
}

class CreditCardManager_PostTypes {

    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('init', array($this, 'register_meta_fields'));
        add_filter('template_include', array($this, 'load_templates'));
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('admin_notices', array($this, 'taxonomy_conflict_notice'));
    }

    /**
     * Initialize component
     */
    public function init() {
        // Component is initialized through hooks
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
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments'),
            'taxonomies'         => array('store', 'card-category', 'network-type'),
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

        // Store/Bank Taxonomy - Only register for credit-card post type since it's already registered by another plugin
        register_taxonomy_for_object_type('store', 'credit-card');

        // Card Categories Taxonomy
        $category_labels = array(
            'name'              => _x('Card Categories', 'taxonomy general name', 'credit-card-manager'),
            'singular_name'     => _x('Card Category', 'taxonomy singular name', 'credit-card-manager'),
            'search_items'      => __('Search Categories', 'credit-card-manager'),
            'all_items'         => __('All Categories', 'credit-card-manager'),
            'parent_item'       => __('Parent Category', 'credit-card-manager'),
            'parent_item_colon' => __('Parent Category:', 'credit-card-manager'),
            'edit_item'         => __('Edit Category', 'credit-card-manager'),
            'update_item'       => __('Update Category', 'credit-card-manager'),
            'add_new_item'      => __('Add New Category', 'credit-card-manager'),
            'new_item_name'     => __('New Category Name', 'credit-card-manager'),
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
        if (!term_exists('Cashback', 'card-category')) {
            wp_insert_term('Cashback', 'card-category', array('description' => 'Cards offering cashback rewards'));
            wp_insert_term('Travel', 'card-category', array('description' => 'Travel and airline credit cards'));
            wp_insert_term('Rewards', 'card-category', array('description' => 'General rewards credit cards'));
            wp_insert_term('Business', 'card-category', array('description' => 'Business and corporate credit cards'));
            wp_insert_term('Premium', 'card-category', array('description' => 'Premium and luxury credit cards'));
            wp_insert_term('Secured', 'card-category', array('description' => 'Secured credit cards'));
            wp_insert_term('Student', 'card-category', array('description' => 'Student credit cards'));
            wp_insert_term('No Annual Fee', 'card-category', array('description' => 'Cards with no annual fee'));
        }

        // Store which taxonomies had conflicts for admin notice
        $this->check_taxonomy_conflicts();
    }

    /**
     * Check for taxonomy conflicts and store information
     */
    public function check_taxonomy_conflicts() {
        $conflicted_taxonomies = array();

        $taxonomies_to_check = array(
            'network-type' => 'Network Types',
            'store' => 'Banks/Stores',
            'card-category' => 'Card Categories'
        );

        foreach ($taxonomies_to_check as $taxonomy => $label) {
            if (taxonomy_exists($taxonomy)) {
                // Check if the taxonomy was registered by another plugin
                $taxonomy_object = get_taxonomy($taxonomy);
                if ($taxonomy_object && !in_array('credit-card', $taxonomy_object->object_type)) {
                    $conflicted_taxonomies[] = array(
                        'taxonomy' => $taxonomy,
                        'label' => $label
                    );
                }
            }
        }

        if (!empty($conflicted_taxonomies)) {
            update_option('ccm_taxonomy_conflicts', $conflicted_taxonomies);
        } else {
            delete_option('ccm_taxonomy_conflicts');
        }
    }

    /**
     * Display admin notice for taxonomy conflicts
     */
    public function taxonomy_conflict_notice() {
        $conflicts = get_option('ccm_taxonomy_conflicts', array());

        if (empty($conflicts)) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, array('edit-credit-card', 'credit-card', 'plugins'))) {
            return;
        }

        ?>
        <div class="notice notice-warning">
            <p><strong>Credit Card Manager - Taxonomy Conflict Detected</strong></p>
            <p>The following taxonomies are already registered by another plugin:</p>
            <ul>
                <?php foreach ($conflicts as $conflict): ?>
                    <li><strong><?php echo esc_html($conflict['label']); ?></strong> (<?php echo esc_html($conflict['taxonomy']); ?>)</li>
                <?php endforeach; ?>
            </ul>
            <p>The plugin has automatically registered these taxonomies for the credit-card post type to avoid conflicts. Your existing functionality should work normally.</p>
            <p><em>If you experience issues, please deactivate the conflicting plugin temporarily and reactivate this plugin.</em></p>
        </div>
        <?php
    }

    /**
     * Register Meta Fields for REST API
     */
    public function register_meta_fields() {
        $meta_fields = array(
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
                'type' => 'number',
                'description' => 'Annual fee amount',
                'single' => true,
                'sanitize_callback' => 'absint',
                'show_in_rest' => true,
            ),
            'joining_fee' => array(
                'type' => 'number',
                'description' => 'Joining fee amount',
                'single' => true,
                'sanitize_callback' => 'absint',
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
            'reward_type' => array(
                'type' => 'string',
                'description' => 'Primary reward type (points, cashback, neucoins, etc.)',
                'single' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'show_in_rest' => true,
            ),
            'reward_conversion_rate' => array(
                'type' => 'string',
                'description' => 'Reward conversion rate description (e.g., 1 neucoin = 1 rupee)',
                'single' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'show_in_rest' => true,
            ),
            'reward_conversion_value' => array(
                'type' => 'number',
                'description' => 'Numerical conversion value for calculations',
                'single' => true,
                'sanitize_callback' => array($this, 'sanitize_decimal'),
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
            'custom_faqs' => array(
                'type' => 'array',
                'description' => 'Custom FAQs for this card',
                'single' => true,
                'sanitize_callback' => array($this, 'sanitize_complex_array'),
                'show_in_rest' => array(
                    'schema' => array(
                        'type' => 'array',
                        'items' => array(
                            'type' => 'object',
                            'properties' => array(
                                'question' => array('type' => 'string'),
                                'answer' => array('type' => 'string')
                            )
                        )
                    )
                ),
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
     * Load custom templates
     */
    public function load_templates($template) {
        $post_type = 'credit-card';

        // Handle comparison page
        if (get_query_var('credit_card_compare') || is_page('compare-cards') || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/compare-cards') !== false)) {
            $compare_template = CCM_PLUGIN_DIR . "templates/page-compare-cards.php";
            if (file_exists($compare_template)) {
                return $compare_template;
            }
        }

        if (is_singular($post_type)) {
            $single_template = CCM_PLUGIN_DIR . "templates/single-{$post_type}.php";
            if (file_exists($single_template)) {
                return $single_template;
            }
        }

        if (is_post_type_archive($post_type)) {
            $archive_template = CCM_PLUGIN_DIR . "templates/archive-{$post_type}.php";
            if (file_exists($archive_template)) {
                return $archive_template;
            }
        }

        return $template;
    }

    /**
     * Add rewrite rules for comparison page
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^compare-cards/?$', 'index.php?credit_card_compare=1', 'top');
        add_rewrite_rule('^compare-cards/([^/]+)/?$', 'index.php?credit_card_compare=1&cards=$matches[1]', 'top');
    }

    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'credit_card_compare';
        $vars[] = 'cards';
        return $vars;
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
                'taxonomy' => 'card-category',
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
    public function get_filters_api() {
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
            'taxonomy' => 'card-category',
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
     * Format Credit Card Data for API - Optimized to reduce database queries
     */
    public function format_credit_card_data($post_id) {
        $post = get_post($post_id);

        // Get taxonomies with error handling
        $banks = wp_get_post_terms($post_id, 'store');
        $network_types = wp_get_post_terms($post_id, 'network-type');
        $categories = wp_get_post_terms($post_id, 'card-category');

        // Handle WP_Error cases
        if (is_wp_error($banks)) $banks = array();
        if (is_wp_error($network_types)) $network_types = array();
        if (is_wp_error($categories)) $categories = array();

        // Get ALL meta data in single query to avoid N+1 problem
        $all_meta = get_post_meta($post_id);

        // Define meta fields and get values from cached array
        $meta_fields = array(
            'rating', 'review_count', 'annual_fee', 'joining_fee',
            'welcome_bonus', 'welcome_bonus_points', 'welcome_bonus_type', 'cashback_rate',
            'reward_type', 'reward_conversion_rate', 'reward_conversion_value',
            'credit_limit', 'interest_rate', 'processing_time', 'min_income',
            'min_age', 'max_age', 'pros', 'cons', 'best_for', 'features',
            'rewards', 'fees', 'eligibility', 'documents', 'apply_link',
            'featured', 'trending', 'gradient', 'bg_gradient', 'theme_color',
            'overall_score', 'reward_score', 'fees_score', 'benefits_score',
            'support_score', 'reward_rate', 'custom_faqs'
        );

        $meta_data = array();
        foreach ($meta_fields as $field) {
            $meta_data[$field] = isset($all_meta[$field][0]) ? maybe_unserialize($all_meta[$field][0]) : '';
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
            'card_image' => $featured_image,
            'rating' => floatval($meta_data['rating']),
            'review_count' => intval($meta_data['review_count']),
            'annual_fee' => $meta_data['annual_fee'],
            'joining_fee' => $meta_data['joining_fee'],
            'welcome_bonus' => $meta_data['welcome_bonus'],
            'welcome_bonus_points' => intval($meta_data['welcome_bonus_points']),
            'welcome_bonus_type' => $meta_data['welcome_bonus_type'],
            'cashback_rate' => $meta_data['cashback_rate'],
            'reward_type' => $meta_data['reward_type'],
            'reward_conversion_rate' => $meta_data['reward_conversion_rate'],
            'reward_conversion_value' => floatval($meta_data['reward_conversion_value']),
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
            'custom_faqs' => $meta_data['custom_faqs'] ?: array(),
        );
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

    public function sanitize_decimal($value) {
        return floatval($value);
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
}
