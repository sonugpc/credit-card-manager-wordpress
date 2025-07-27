<?php
/**
 * SEO Functions for Credit Card Manager Plugin
 * Works alongside RankMath and other SEO plugins
 * 
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if RankMath or other major SEO plugins are active
 */
function ccm_has_seo_plugin() {
    return (
        class_exists('RankMath') ||
        defined('WPSEO_VERSION') || // Yoast
        class_exists('AIOSEO\\Plugin\\AIOSEO') || // All in One SEO
        function_exists('seopress_activation') // SEOPress
    );
}

/**
 * Check if we should output basic meta tags (only if no SEO plugin detected)
 */
function ccm_should_output_meta_tags() {
    return !ccm_has_seo_plugin();
}

/**
 * Generate SEO-optimized title for credit card pages
 */
function ccm_generate_seo_title($post_id = null, $context = 'single') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $card_title = get_the_title($post_id);
    $bank_terms = get_the_terms($post_id, 'store');
    $bank_name = (!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '';
    
    switch ($context) {
        case 'single':
            return $card_title . ' Credit Card Review & Apply Online | ' . get_bloginfo('name');
        case 'comparison':
            return $card_title . ' vs Other Cards - Detailed Comparison';
        case 'archive':
            return $bank_name ? $bank_name . ' Credit Cards - Compare & Apply' : 'Best Credit Cards in India';
        default:
            return $card_title;
    }
}

/**
 * Generate SEO-optimized meta description
 */
function ccm_generate_meta_description($post_id = null, $context = 'single') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $card_title = get_the_title($post_id);
    $rating = ccm_get_meta($post_id, 'rating', 0, true);
    $annual_fee = ccm_get_meta($post_id, 'annual_fee', 0, true);
    $welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', '');
    
    switch ($context) {
        case 'single':
            return sprintf(
                'Complete review of %s credit card with %s/5 rating. Annual fee: ₹%s. %s Apply online with instant approval and expert guidance.',
                $card_title,
                $rating,
                number_format($annual_fee),
                $welcome_bonus ? 'Welcome bonus: ' . $welcome_bonus . '. ' : ''
            );
        case 'archive':
            return 'Compare the best credit cards in India. Find cashback, rewards, and travel credit cards from top banks. Expert reviews, detailed comparisons, and instant online applications.';
        case 'comparison':
            return 'Compare credit cards side-by-side with our detailed comparison tool. Analyze fees, rewards, benefits, and interest rates to find the perfect card for your needs.';
        default:
            return wp_strip_all_tags(get_the_excerpt($post_id));
    }
}

/**
 * Generate structured data for credit cards
 */
function ccm_generate_product_schema($post_id) {
    $card_title = get_the_title($post_id);
    $rating = ccm_get_meta($post_id, 'rating', 0, true);
    $review_count = ccm_get_meta($post_id, 'review_count', 0, true);
    $annual_fee = ccm_get_meta($post_id, 'annual_fee', 0, true);
    $welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', '');
    $apply_link = ccm_get_meta($post_id, 'apply_link', '');
    $featured_image = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'large') : '';
    
    $bank_terms = get_the_terms($post_id, 'store');
    $bank_name = (!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '';
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $card_title,
        'description' => wp_strip_all_tags(get_the_excerpt($post_id)),
        'category' => 'Credit Card',
        'url' => get_permalink($post_id)
    ];
    
    if ($featured_image) {
        $schema['image'] = $featured_image;
    }
    
    if ($bank_name) {
        $schema['brand'] = [
            '@type' => 'Brand',
            'name' => $bank_name
        ];
    }
    
    if ($rating > 0) {
        $schema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating,
            'reviewCount' => $review_count,
            'bestRating' => 5,
            'worstRating' => 1
        ];
    }
    
    if ($apply_link) {
        $schema['offers'] = [
            '@type' => 'Offer',
            'url' => $apply_link,
            'priceCurrency' => 'INR',
            'price' => $annual_fee,
            'priceValidUntil' => date('Y-12-31'),
            'availability' => 'https://schema.org/InStock'
        ];
        
        if ($bank_name) {
            $schema['offers']['seller'] = [
                '@type' => 'Organization',
                'name' => $bank_name
            ];
        }
    }
    
    $additional_properties = [];
    
    if ($annual_fee) {
        $additional_properties[] = [
            '@type' => 'PropertyValue',
            'name' => 'Annual Fee',
            'value' => '₹' . number_format($annual_fee)
        ];
    }
    
    $network_terms = get_the_terms($post_id, 'network-type');
    if (!is_wp_error($network_terms) && !empty($network_terms)) {
        $additional_properties[] = [
            '@type' => 'PropertyValue',
            'name' => 'Network Type',
            'value' => $network_terms[0]->name
        ];
    }
    
    if ($welcome_bonus) {
        $additional_properties[] = [
            '@type' => 'PropertyValue',
            'name' => 'Welcome Bonus',
            'value' => $welcome_bonus
        ];
    }
    
    if (!empty($additional_properties)) {
        $schema['additionalProperty'] = $additional_properties;
    }
    
    return $schema;
}

/**
 * Generate breadcrumb schema
 */
function ccm_generate_breadcrumb_schema($breadcrumbs) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => []
    ];
    
    foreach ($breadcrumbs as $index => $breadcrumb) {
        $schema['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $breadcrumb['name'],
            'item' => $breadcrumb['url']
        ];
    }
    
    return $schema;
}

/**
 * Generate static FAQ schema for common credit card questions
 */
function ccm_generate_static_faq_schema() {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'What is the best credit card in India?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'The best credit card depends on your spending patterns, income, and financial goals. Premium cards offer extensive rewards and benefits, while entry-level cards are suitable for building credit history.'
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'How do I choose the right credit card?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Consider factors like annual fees, interest rates, reward programs, welcome bonuses, and additional benefits. Match the card features with your spending habits and financial needs.'
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'What documents are required for credit card application?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Typically required documents include identity proof (Aadhaar, PAN), address proof, income proof (salary slips, ITR), and bank statements. Requirements may vary by bank and card type.'
                ]
            ]
        ]
    ];
}

/**
 * Output JSON-LD schema
 */
function ccm_output_schema($schema) {
    if (empty($schema)) {
        return;
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    echo '</script>' . "\n";
}

/**
 * Add meta tags to head (only if no SEO plugin is active)
 */
function ccm_add_meta_tags($title, $description, $canonical_url, $keywords = '') {
    if (!ccm_should_output_meta_tags()) {
        return; // Don't output if SEO plugin is handling this
    }
    
    echo '<title>' . esc_html($title) . '</title>' . "\n";
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    
    if ($keywords) {
        echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
    }
    
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";
    echo '<meta name="robots" content="index,follow">' . "\n";
}

/**
 * Add Open Graph meta tags (only if no SEO plugin is active)
 */
function ccm_add_og_tags($title, $description, $url, $image = '', $type = 'website') {
    if (!ccm_should_output_meta_tags()) {
        return; // Don't output if SEO plugin is handling this
    }
    
    echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
}

/**
 * Add Twitter Card meta tags (only if no SEO plugin is active)
 */
function ccm_add_twitter_tags($title, $description, $url, $image = '') {
    if (!ccm_should_output_meta_tags()) {
        return; // Don't output if SEO plugin is handling this
    }
    
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }
}

/**
 * Generate and output complete SEO package for single credit card
 */
function ccm_output_single_seo($post_id) {
    $title = ccm_generate_seo_title($post_id, 'single');
    $description = ccm_generate_meta_description($post_id, 'single');
    $canonical_url = get_permalink($post_id);
    $featured_image = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'large') : '';
    
    $card_title = get_the_title($post_id);
    $bank_terms = get_the_terms($post_id, 'store');
    $bank_name = (!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '';
    $network_terms = get_the_terms($post_id, 'network-type');
    $network_name = (!is_wp_error($network_terms) && !empty($network_terms)) ? $network_terms[0]->name : '';
    
    $keywords = $card_title . ', credit card, ' . $bank_name . ', ' . $network_name . ', rewards, cashback, apply online';
    
    // Output meta tags
    ccm_add_meta_tags($title, $description, $canonical_url, $keywords);
    
    // Output Open Graph tags
    ccm_add_og_tags($title, $description, $canonical_url, $featured_image, 'article');
    
    // Output Twitter tags
    ccm_add_twitter_tags($title, $description, $canonical_url, $featured_image);
    
    // Output Article meta tags
    echo '<meta property="article:published_time" content="' . get_the_date('c', $post_id) . '">' . "\n";
    echo '<meta property="article:modified_time" content="' . get_the_modified_date('c', $post_id) . '">' . "\n";
    echo '<meta property="article:section" content="Credit Cards">' . "\n";
    echo '<meta property="article:tag" content="' . esc_attr($card_title . ', ' . $bank_name . ', Credit Card') . '">' . "\n";
    
    // Output schemas
    ccm_output_schema(ccm_generate_product_schema($post_id));
    
    $breadcrumbs = [
        ['name' => 'Home', 'url' => home_url()],
        ['name' => 'Credit Cards', 'url' => get_post_type_archive_link('credit-card')],
        ['name' => get_the_title($post_id), 'url' => $canonical_url]
    ];
    ccm_output_schema(ccm_generate_breadcrumb_schema($breadcrumbs));
    
    // Financial Product Schema
    $financial_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FinancialProduct',
        'name' => get_the_title($post_id),
        'description' => $description,
        'provider' => [
            '@type' => 'FinancialService',
            'name' => $bank_name
        ],
        'feesAndCommissionsSpecification' => 'Annual Fee: ₹' . number_format(ccm_get_meta($post_id, 'annual_fee', 0, true)) . ', Joining Fee: ₹' . number_format(ccm_get_meta($post_id, 'joining_fee', 0, true)),
        'interestRate' => ccm_get_meta($post_id, 'interest_rate', 'Varies'),
        'amount' => [
            '@type' => 'MonetaryAmount',
            'currency' => 'INR',
            'value' => ccm_get_meta($post_id, 'annual_fee', 0, true)
        ]
    ];
    ccm_output_schema($financial_schema);
}

/**
 * RankMath Integration Functions
 * These functions provide credit card specific data to RankMath
 */

/**
 * Add credit card specific schema to RankMath
 */
function ccm_rankmath_schema_credit_card($data, $jsonld_instance = null) {
    // Get current post ID
    $post_id = get_the_ID();
    
    // If we don't have a valid post ID, try to get it from global $post
    if (!$post_id) {
        global $post;
        if ($post && isset($post->ID)) {
            $post_id = $post->ID;
        } else {
            return $data; // Can't determine post, return original data
        }
    }
    
    // Only process credit card posts
    if (get_post_type($post_id) !== 'credit-card') {
        return $data;
    }
    
    // Add our specialized credit card schema alongside RankMath's default schema
    $credit_card_schema = ccm_generate_product_schema($post_id);
    
    // If this is an array of schemas, find and enhance Product schema
    if (is_array($data)) {
        foreach ($data as $key => $schema) {
            if (isset($schema['@type']) && $schema['@type'] === 'Product') {
                if (isset($credit_card_schema['additionalProperty'])) {
                    $data[$key]['additionalProperty'] = $credit_card_schema['additionalProperty'];
                }
                if (isset($credit_card_schema['aggregateRating'])) {
                    $data[$key]['aggregateRating'] = $credit_card_schema['aggregateRating'];
                }
                break; // Only enhance the first Product schema found
            }
        }
    }
    // If this is a single schema object
    elseif (isset($data['@type']) && $data['@type'] === 'Product') {
        if (isset($credit_card_schema['additionalProperty'])) {
            $data['additionalProperty'] = $credit_card_schema['additionalProperty'];
        }
        if (isset($credit_card_schema['aggregateRating'])) {
            $data['aggregateRating'] = $credit_card_schema['aggregateRating'];
        }
    }
    
    return $data;
}

/**
 * Provide credit card specific title suggestions to RankMath
 */
function ccm_rankmath_title_credit_card($title) {
    global $post;
    
    if (!$post || get_post_type($post) !== 'credit-card') {
        return $title;
    }
    
    // If title is empty or default, suggest our optimized title
    if (empty($title) || $title === get_the_title($post)) {
        return ccm_generate_seo_title($post->ID, 'single');
    }
    
    return $title;
}

/**
 * Provide credit card specific description suggestions to RankMath
 */
function ccm_rankmath_description_credit_card($description) {
    global $post;
    
    if (!$post || get_post_type($post) !== 'credit-card') {
        return $description;
    }
    
    // If description is empty, suggest our optimized description
    if (empty($description)) {
        return ccm_generate_meta_description($post->ID, 'single');
    }
    
    return $description;
}

/**
 * Initialize RankMath integration hooks
 */
function ccm_init_rankmath_integration() {
    if (!class_exists('RankMath')) {
        return;
    }
    
    // Use RankMath's specific schema hooks for better compatibility
    add_action('rank_math/head', 'ccm_add_credit_card_schema_to_head');
    
    // Alternative: Try the json_ld filter with better error handling
    add_filter('rank_math/json_ld', function($data, $jsonld = null) {
        try {
            return ccm_rankmath_schema_credit_card($data, $jsonld);
        } catch (Exception $e) {
            error_log('CCM RankMath Schema Error: ' . $e->getMessage());
            return $data;
        }
    }, 10, 2);
    
    // Hook into RankMath's title filter (disabled for now to prevent conflicts)
    /*
    add_filter('rank_math/frontend/title', function($title) {
        try {
            return ccm_rankmath_title_credit_card($title);
        } catch (Exception $e) {
            error_log('CCM RankMath Title Error: ' . $e->getMessage());
            return $title;
        }
    }, 10, 1);
    
    // Hook into RankMath's description filter (disabled for now to prevent conflicts)
    add_filter('rank_math/frontend/description', function($description) {
        try {
            return ccm_rankmath_description_credit_card($description);
        } catch (Exception $e) {
            error_log('CCM RankMath Description Error: ' . $e->getMessage());
            return $description;
        }
    }, 10, 1);
    */
}

/**
 * Add credit card schema directly to head when RankMath is active
 */
function ccm_add_credit_card_schema_to_head() {
    $post_id = get_the_ID();
    
    if (!$post_id || get_post_type($post_id) !== 'credit-card') {
        return;
    }
    
    // Output our specialized credit card schemas
    $financial_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FinancialProduct',
        'name' => get_the_title($post_id),
        'description' => ccm_generate_meta_description($post_id),
        'provider' => [
            '@type' => 'FinancialService',
            'name' => ''
        ],
        'feesAndCommissionsSpecification' => 'Annual Fee: ₹' . number_format(ccm_get_meta($post_id, 'annual_fee', 0, true)) . ', Joining Fee: ₹' . number_format(ccm_get_meta($post_id, 'joining_fee', 0, true)),
        'interestRate' => ccm_get_meta($post_id, 'interest_rate', 'Varies'),
        'amount' => [
            '@type' => 'MonetaryAmount',
            'currency' => 'INR',
            'value' => ccm_get_meta($post_id, 'annual_fee', 0, true)
        ]
    ];
    
    // Get bank name
    $bank_terms = get_the_terms($post_id, 'store');
    if (!is_wp_error($bank_terms) && !empty($bank_terms)) {
        $financial_schema['provider']['name'] = $bank_terms[0]->name;
    }
    
    ccm_output_schema($financial_schema);
}

// Initialize RankMath integration on plugins_loaded to ensure RankMath is available
add_action('plugins_loaded', 'ccm_init_rankmath_integration', 20);

/**
 * Add RankMath specific meta box data for credit cards
 */
function ccm_rankmath_add_credit_card_meta() {
    if (!class_exists('RankMath')) {
        return;
    }
    
    // Add custom focus keywords suggestions based on credit card data
    add_filter('rank_math/metabox/focus_keyword/suggestions', function($suggestions, $post_id) {
        if (get_post_type($post_id) !== 'credit-card') {
            return $suggestions;
        }
        
        $card_title = get_the_title($post_id);
        $bank_terms = get_the_terms($post_id, 'store');
        $bank_name = (!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '';
        
        $custom_suggestions = [
            $card_title . ' credit card',
            $bank_name . ' credit card',
            $card_title . ' review',
            $card_title . ' apply online',
            'best ' . strtolower($bank_name) . ' cards'
        ];
        
        return array_merge($suggestions, array_filter($custom_suggestions));
    }, 10, 2);
}

add_action('admin_init', 'ccm_rankmath_add_credit_card_meta');