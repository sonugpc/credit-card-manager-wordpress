<?php
/**
 * Meta Fields Registration
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Meta Fields for REST API
 */
function ccm_register_meta_fields() {
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
            'sanitize_callback' => 'ccm_sanitize_rating',
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
            'sanitize_callback' => 'ccm_sanitize_array_field',
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
            'sanitize_callback' => 'ccm_sanitize_array_field',
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
            'sanitize_callback' => 'ccm_sanitize_array_field',
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
            'sanitize_callback' => 'ccm_sanitize_complex_array',
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
            'sanitize_callback' => 'ccm_sanitize_complex_array',
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
            'sanitize_callback' => 'ccm_sanitize_complex_array',
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
            'sanitize_callback' => 'ccm_sanitize_complex_array',
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
            'sanitize_callback' => 'ccm_sanitize_array_field',
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
            'sanitize_callback' => 'ccm_sanitize_boolean',
            'show_in_rest' => true,
        ),
        'trending' => array(
            'type' => 'boolean',
            'description' => 'Is trending card',
            'single' => true,
            'sanitize_callback' => 'ccm_sanitize_boolean',
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
            'sanitize_callback' => 'ccm_sanitize_rating',
            'show_in_rest' => true,
        ),
        'reward_score' => array(
            'type' => 'number',
            'description' => 'Reward score',
            'single' => true,
            'sanitize_callback' => 'ccm_sanitize_rating',
            'show_in_rest' => true,
        ),
        'fees_score' => array(
            'type' => 'number',
            'description' => 'Fees score',
            'single' => true,
            'sanitize_callback' => 'ccm_sanitize_rating',
            'show_in_rest' => true,
        ),
        'benefits_score' => array(
            'type' => 'number',
            'description' => 'Benefits score',
            'single' => true,
            'sanitize_callback' => 'ccm_sanitize_rating',
            'show_in_rest' => true,
        ),
        'support_score' => array(
            'type' => 'number',
            'description' => 'Support score',
            'single' => true,
            'sanitize_callback' => 'ccm_sanitize_rating',
            'show_in_rest' => true,
        ),
        'reward_rate' => array(
            'type' => 'number',
            'description' => 'Reward rate percentage',
            'single' => true,
            'sanitize_callback' => 'ccm_sanitize_percentage',
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
add_action('init', 'ccm_register_meta_fields');
