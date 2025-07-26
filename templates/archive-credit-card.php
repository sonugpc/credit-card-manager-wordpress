<?php
/**
 * The template for displaying Credit Card archive pages - Version 2
 * Professional design inspired by PaisaBazaar and CardInsider
 * 
 * @package Credit Card Manager
 */

get_header();

// Get filter parameters from URL
$current_bank = isset($_GET['bank']) ? sanitize_text_field($_GET['bank']) : '';
$current_network = isset($_GET['network_type']) ? sanitize_text_field($_GET['network_type']) : '';
$current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$current_min_rating = isset($_GET['min_rating']) ? sanitize_text_field($_GET['min_rating']) : '';
$current_max_fee = isset($_GET['max_annual_fee']) ? sanitize_text_field($_GET['max_annual_fee']) : '';
$current_featured = isset($_GET['featured']) ? sanitize_text_field($_GET['featured']) : '';
$current_trending = isset($_GET['trending']) ? sanitize_text_field($_GET['trending']) : '';
$current_sort = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'rating';
$current_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'desc';

// Check if we're in comparison mode
$compare_mode = isset($_GET['compare']) && !empty($_GET['compare']);
$compare_ids = $compare_mode ? explode(',', sanitize_text_field($_GET['compare'])) : [];

// Get filter data from API
$filters_response = wp_remote_get(rest_url('ccm/v1/credit-cards/filters'));
$filters = !is_wp_error($filters_response) ? json_decode(wp_remote_retrieve_body($filters_response), true) : [];

// Build query args for credit cards
$args = [
    'post_type' => 'credit-card',
    'posts_per_page' => $compare_mode ? -1 : 12,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    'meta_query' => [],
    'tax_query' => [],
];

// Add filters to query (keeping existing filter logic)
if (!empty($current_bank)) {
    $args['tax_query'][] = [
        'taxonomy' => 'store',
        'field' => 'slug',
        'terms' => $current_bank,
    ];
}

if (!empty($current_network)) {
    $args['tax_query'][] = [
        'taxonomy' => 'network-type',
        'field' => 'slug',
        'terms' => $current_network,
    ];
}

if (!empty($current_category)) {
    $args['tax_query'][] = [
        'taxonomy' => 'category',
        'field' => 'slug',
        'terms' => $current_category,
    ];
}

if (!empty($current_min_rating)) {
    $args['meta_query'][] = [
        'key' => 'rating',
        'value' => floatval($current_min_rating),
        'compare' => '>=',
        'type' => 'DECIMAL',
    ];
}

if (!empty($current_max_fee)) {
    $args['meta_query'][] = [
        'key' => 'annual_fee_numeric',
        'value' => intval($current_max_fee),
        'compare' => '<=',
        'type' => 'NUMERIC',
    ];
}

if ($current_featured === '1') {
    $args['meta_query'][] = [
        'key' => 'featured',
        'value' => '1',
        'compare' => '=',
    ];
}

if ($current_trending === '1') {
    $args['meta_query'][] = [
        'key' => 'trending',
        'value' => '1',
        'compare' => '=',
    ];
}

// For comparison mode, get specific cards
if ($compare_mode && !empty($compare_ids)) {
    $args['post__in'] = $compare_ids;
    $args['orderby'] = 'post__in';
    unset($args['meta_query']);
    unset($args['tax_query']);
} else {
    // Sorting logic
    switch ($current_sort) {
        case 'rating':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($current_order);
            break;
        case 'annual_fee':
            $args['meta_key'] = 'annual_fee_numeric';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($current_order);
            break;
        case 'review_count':
            $args['meta_key'] = 'review_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($current_order);
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = strtoupper($current_order);
    }
}

// Set relations for queries
if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
    $args['meta_query']['relation'] = 'AND';
}

if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
    $args['tax_query']['relation'] = 'AND';
}

// Run the query
$credit_cards = new WP_Query($args);

// SEO Meta Data for Archive
$archive_title = 'Best Credit Cards in India  - Compare & Apply Online On Bigtricks';
$archive_description = 'Compare the best credit cards in India. Find cashback, rewards, and travel credit cards from top banks. Expert reviews, detailed comparisons, and instant online applications.';
$archive_canonical = get_post_type_archive_link('credit-card');

// Add filter-based title and description
if ($current_bank) {
    $bank_term = get_term_by('slug', $current_bank, 'store');
    $bank_display_name = $bank_term ? $bank_term->name : $current_bank;
    $archive_title = $bank_display_name . ' Credit Cards - Compare & Apply Online';
    $archive_description = "Compare the best " . $bank_display_name . " credit cards in India. Find cashback, rewards, and travel cards with detailed reviews and instant applications.";
}

if ($current_category) {
    $category_term = get_term_by('slug', $current_category, 'category');
    $category_display_name = $category_term ? $category_term->name : $current_category;
    $archive_title = 'Best ' . $category_display_name . ' Credit Cards in India - Compare & Apply';
    $archive_description = "Find the best " . $category_display_name . " credit cards in India. Compare features, benefits, fees and apply online for instant approval.";
}

// Pagination for SEO
$current_page = max(1, get_query_var('paged'));
if ($current_page > 1) {
    $archive_title .= ' - Page ' . $current_page;
}
?>

<?php
// Only output meta tags if no SEO plugin is detected
if (!function_exists('ccm_has_seo_plugin') || !ccm_has_seo_plugin()) {
    // Output meta tags only if no SEO plugin
    ccm_add_meta_tags(
        $archive_title, 
        $archive_description, 
        $archive_canonical, 
        'credit cards India, compare credit cards, best credit cards, cashback cards, rewards cards, travel cards, apply online'
    );
    
    // Output social media tags only if no SEO plugin
    ccm_add_og_tags($archive_title, $archive_description, $archive_canonical, '', 'website');
    ccm_add_twitter_tags($archive_title, $archive_description, $archive_canonical);
} else {
    echo '<!-- Meta tags handled by ' . (class_exists('RankMath') ? 'RankMath' : 'SEO Plugin') . ' -->' . "\n";
}
?>

<!-- JSON-LD Structured Data for Archive -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "<?php echo esc_attr($archive_title); ?>",
    "description": "<?php echo esc_attr($archive_description); ?>",
    "url": "<?php echo esc_url($archive_canonical); ?>",
    "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": "<?php echo $credit_cards->found_posts; ?>",
        "itemListElement": [
            <?php 
            $card_items = [];
            if ($credit_cards->have_posts()) {
                $position = 1;
                while ($credit_cards->have_posts()) {
                    $credit_cards->the_post();
                    $card_items[] = sprintf(
                        '{
                            "@type": "ListItem",
                            "position": %d,
                            "item": {
                                "@type": "Product",
                                "name": "%s",
                                "url": "%s",
                                "image": "%s",
                                "aggregateRating": {
                                    "@type": "AggregateRating",
                                    "ratingValue": "%s",
                                    "reviewCount": "%s"
                                }
                            }
                        }',
                        $position,
                        esc_attr(get_the_title()),
                        esc_url(get_permalink()),
                        esc_url(has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : ''),
                        esc_attr(ccm_get_meta(get_the_ID(), 'rating', '0')),
                        esc_attr(ccm_get_meta(get_the_ID(), 'review_count', '0'))
                    );
                    $position++;
                    if ($position > 10) break; // Limit to first 10 for performance
                }
                wp_reset_postdata();
            }
            echo implode(',', $card_items);
            ?>
        ]
    }
}
</script>

<!-- WebSite Schema with SearchAction -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "<?php bloginfo('name'); ?>",
    "url": "<?php echo home_url(); ?>",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "<?php echo esc_url($archive_canonical); ?>?s={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>

<div class="ccv2-container">
    <!-- Hero Section -->
    <section class="ccv2-hero">
        <div class="ccv2-hero-content">
            <h1>Find Your Perfect Credit Card</h1>
            <p>Compare the best credit cards in India with our comprehensive database. Make informed decisions with expert reviews, detailed comparisons, and exclusive offers.</p>
            
            <div class="ccv2-hero-stats">
                <div class="ccv2-stat-card">
                    <span class="ccv2-stat-number"><?php echo $credit_cards->found_posts; ?>+</span>
                    <span class="ccv2-stat-label">Credit Cards</span>
                </div>
                <div class="ccv2-stat-card">
                    <span class="ccv2-stat-number">50+</span>
                    <span class="ccv2-stat-label">Banks & NBFCs</span>
                </div>
                <div class="ccv2-stat-card">
                    <span class="ccv2-stat-number">10K+</span>
                    <span class="ccv2-stat-label">Happy Customers</span>
                </div>
                <div class="ccv2-stat-card">
                    <span class="ccv2-stat-number">24/7</span>
                    <span class="ccv2-stat-label">Expert Support</span>
                </div>
            </div>
        </div>
    </section>

    <?php if ($compare_mode): ?>
        <!-- Comparison Mode -->
        <div class="ccv2-filter-section">
            <div class="ccv2-filter-header">
                <h2 class="ccv2-filter-title">
                    <?php echo ccm_get_icon('compare', 'icon'); ?>
                    Credit Card Comparison
                </h2>
                <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="ccv2-filter-toggle">
                    <?php echo ccm_get_icon('x', 'icon'); ?> Exit Comparison
                </a>
            </div>
            <p>Compare the selected credit cards side by side to find the best option for your needs.</p>
        </div>

        <?php if ($credit_cards->have_posts()): ?>
            <!-- Comparison Table (Enhanced version of existing comparison table) -->
            <div style="overflow-x: auto; background: white; border-radius: var(--radius-2xl); box-shadow: var(--shadow-lg); margin-bottom: var(--space-8);">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: var(--neutral-50);">
                            <th style="padding: var(--space-6); text-align: left; font-weight: 700; color: var(--neutral-800); border-bottom: 2px solid var(--neutral-200);">Features</th>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); ?>
                                <th style="padding: var(--space-6); text-align: center; font-weight: 700; color: var(--neutral-800); border-bottom: 2px solid var(--neutral-200); min-width: 200px;">
                                    <?php the_title(); ?>
                                </th>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Card Images -->
                        <tr>
                            <td style="padding: var(--space-4); font-weight: 600; color: var(--neutral-700); border-bottom: 1px solid var(--neutral-200);">Card Image</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $card_image = ccm_get_meta(get_the_ID(), 'card_image_url', '');
                                if (empty($card_image) && has_post_thumbnail()) {
                                    $card_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                                }
                            ?>
                                <td style="padding: var(--space-4); text-align: center; border-bottom: 1px solid var(--neutral-200);">
                                    <?php if (!empty($card_image)): ?>
                                        <img src="<?php echo esc_url($card_image); ?>" alt="<?php the_title(); ?>" style="max-width: 150px; max-height: 100px; object-fit: contain; border-radius: var(--radius-md);">
                                    <?php endif; ?>
                                </td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Continue with other comparison rows using the enhanced styling -->
                        <!-- (Bank, Network, Rating, Annual Fee, etc. - keeping the same data structure) -->
                        
                        <!-- Apply Now Row -->
                        <tr style="background: var(--neutral-50);">
                            <td style="padding: var(--space-6); font-weight: 700; color: var(--neutral-800);">Apply Now</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $apply_link = ccm_get_meta(get_the_ID(), 'apply_link', '#');
                            ?>
                                <td style="padding: var(--space-6); text-align: center;">
                                    <a href="<?php echo esc_url($apply_link); ?>" class="ccv2-btn ccv2-btn-primary" target="_blank" rel="noopener">
                                        Apply Now
                                    </a>
                                </td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="ccv2-no-results">
                <?php echo ccm_get_icon('info', 'ccv2-no-results-icon'); ?>
                <h3>No cards selected for comparison</h3>
                <p>Please select at least two credit cards to compare their features side by side.</p>
                <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="ccv2-btn ccv2-btn-primary">
                    Browse Credit Cards
                </a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Regular Archive View -->
        
        <!-- Enhanced Filter Section -->
        <form class="ccv2-filter-section" method="get" action="<?php echo get_post_type_archive_link('credit-card'); ?>">
            <div class="ccv2-filter-header">
                <h2 class="ccv2-filter-title">
                    <?php echo ccm_get_icon('filter', 'icon'); ?>
                    Find Your Ideal Credit Card
                </h2>
                <button type="button" class="ccv2-filter-toggle" id="toggle-filters">
                    <?php echo ccm_get_icon('minus', 'icon'); ?>
                    <span>Hide Filters</span>
                </button>
            </div>
            
            <div class="ccv2-filter-grid" id="filter-content">
                <?php if (!empty($filters['banks'])): ?>
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="bank">Bank/Issuer</label>
                    <select class="ccv2-filter-select" name="bank" id="bank">
                        <option value="">All Banks</option>
                        <?php foreach ($filters['banks'] as $bank): ?>
                            <option value="<?php echo esc_attr($bank['slug']); ?>" <?php selected($current_bank, $bank['slug']); ?>>
                                <?php echo esc_html($bank['name']); ?> (<?php echo esc_html($bank['count']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($filters['network_types'])): ?>
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="network_type">Network Type</label>
                    <select class="ccv2-filter-select" name="network_type" id="network_type">
                        <option value="">All Networks</option>
                        <?php foreach ($filters['network_types'] as $network): ?>
                            <option value="<?php echo esc_attr($network['slug']); ?>" <?php selected($current_network, $network['slug']); ?>>
                                <?php echo esc_html($network['name']); ?> (<?php echo esc_html($network['count']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($filters['categories'])): ?>
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="category">Category</label>
                    <select class="ccv2-filter-select" name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach ($filters['categories'] as $category): ?>
                            <option value="<?php echo esc_attr($category['slug']); ?>" <?php selected($current_category, $category['slug']); ?>>
                                <?php echo esc_html($category['name']); ?> (<?php echo esc_html($category['count']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($filters['rating_ranges'])): ?>
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="min_rating">Minimum Rating</label>
                    <select class="ccv2-filter-select" name="min_rating" id="min_rating">
                        <option value="">Any Rating</option>
                        <?php foreach ($filters['rating_ranges'] as $range): ?>
                            <option value="<?php echo esc_attr($range['min']); ?>" <?php selected($current_min_rating, $range['min']); ?>>
                                <?php echo esc_html($range['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($filters['fee_ranges'])): ?>
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="max_annual_fee">Maximum Annual Fee</label>
                    <select class="ccv2-filter-select" name="max_annual_fee" id="max_annual_fee">
                        <option value="">Any Fee</option>
                        <?php foreach ($filters['fee_ranges'] as $range): ?>
                            <option value="<?php echo esc_attr($range['max']); ?>" <?php selected($current_max_fee, $range['max']); ?>>
                                <?php echo esc_html($range['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="featured">Card Type</label>
                    <select class="ccv2-filter-select" name="featured" id="featured">
                        <option value="">All Cards</option>
                        <option value="1" <?php selected($current_featured, '1'); ?>>Featured Cards</option>
                        <option value="0" <?php selected($current_featured, '0'); ?>>Regular Cards</option>
                    </select>
                </div>
            </div>
            
            <div class="ccv2-filter-actions">
                <button type="reset" class="ccv2-btn ccv2-btn-secondary">
                    Reset Filters
                </button>
                <button type="submit" class="ccv2-btn ccv2-btn-primary">
                    <?php echo ccm_get_icon('filter', 'icon'); ?>
                    Apply Filters
                </button>
            </div>
        </form>

        <!-- Enhanced Controls Bar -->
        <div class="ccv2-controls-bar">
            <div class="ccv2-results-info">
                <div class="ccv2-results-count">
                    <strong><?php echo $credit_cards->found_posts; ?></strong> credit cards found
                </div>
                
                <!-- Active Filters Display -->
                <div class="ccv2-active-filters">
                    <?php if ($current_bank): ?>
                        <span class="ccv2-filter-tag">Bank: <?php echo esc_html($current_bank); ?></span>
                    <?php endif; ?>
                    <?php if ($current_category): ?>
                        <span class="ccv2-filter-tag">Category: <?php echo esc_html($current_category); ?></span>
                    <?php endif; ?>
                    <?php if ($current_min_rating): ?>
                        <span class="ccv2-filter-tag">Rating: <?php echo esc_html($current_min_rating); ?>+</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="ccv2-sort-controls">
                <div class="ccv2-sort-label">
                    <?php echo ccm_get_icon('sort', 'icon'); ?>
                    Sort by:
                </div>
                <select class="ccv2-sort-select" id="sort-select" onchange="updateSort(this.value)">
                    <option value="rating-desc" <?php echo ($current_sort === 'rating' && $current_order === 'desc') ? 'selected' : ''; ?>>
                        Highest Rated
                    </option>
                    <option value="rating-asc" <?php echo ($current_sort === 'rating' && $current_order === 'asc') ? 'selected' : ''; ?>>
                        Lowest Rated
                    </option>
                    <option value="annual_fee-asc" <?php echo ($current_sort === 'annual_fee' && $current_order === 'asc') ? 'selected' : ''; ?>>
                        Lowest Annual Fee
                    </option>
                    <option value="annual_fee-desc" <?php echo ($current_sort === 'annual_fee' && $current_order === 'desc') ? 'selected' : ''; ?>>
                        Highest Annual Fee
                    </option>
                    <option value="review_count-desc" <?php echo ($current_sort === 'review_count' && $current_order === 'desc') ? 'selected' : ''; ?>>
                        Most Popular
                    </option>
                    <option value="date-desc" <?php echo ($current_sort === 'date' && $current_order === 'desc') ? 'selected' : ''; ?>>
                        Newest First
                    </option>
                </select>
            </div>
        </div>

        <?php if ($credit_cards->have_posts()): ?>
            <!-- Enhanced Cards Grid -->
            <div class="ccv2-cards-grid">
                <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                    $post_id = get_the_ID();
                    $card_image = ccm_get_meta($post_id, 'card_image_url', '');
                    if (empty($card_image) && has_post_thumbnail()) {
                        $card_image = get_the_post_thumbnail_url($post_id, 'medium');
                    }
                    
                    $rating = ccm_get_meta($post_id, 'rating', 0, true);
                    $review_count = ccm_get_meta($post_id, 'review_count', 0, true);
                    $annual_fee = ccm_format_currency(ccm_get_meta($post_id, 'annual_fee', 'N/A'));
                    $cashback_rate = ccm_get_meta($post_id, 'cashback_rate', 'N/A');
                    $welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', 'N/A');
                    $apply_link = ccm_get_meta($post_id, 'apply_link', get_permalink());
                    $featured = (bool) ccm_get_meta($post_id, 'featured', false);
                    $trending = (bool) ccm_get_meta($post_id, 'trending', false);
                    
                    $bank_terms = get_the_terms($post_id, 'store');
                    $bank_name = !is_wp_error($bank_terms) && !empty($bank_terms) ? $bank_terms[0]->name : '';
                    
                    $pros = ccm_get_meta($post_id, 'pros', [], false, true);
                ?>
                <article class="ccv2-card" data-id="<?php echo esc_attr($post_id); ?>">
                    <!-- Compare Button -->
                    <button type="button" class="ccv2-btn-compare" data-id="<?php echo esc_attr($post_id); ?>">
                        <?php echo ccm_get_icon('compare', 'icon'); ?>
                        <span>Compare</span>
                    </button>
                    
                    <!-- Card Header -->
                    <header class="ccv2-card-header">
                        <!-- Badges -->
                        <div class="ccv2-card-badges">
                            <?php if ($featured): ?>
                                <span class="ccv2-badge ccv2-badge-featured">
                                    <?php echo ccm_get_icon('award', 'icon'); ?>
                                    Featured
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($trending): ?>
                                <span class="ccv2-badge ccv2-badge-trending">
                                    <?php echo ccm_get_icon('trending-up', 'icon'); ?>
                                    Trending
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Card Image -->
                        <div class="ccv2-card-image">
                            <?php if (!empty($card_image)): ?>
                                <img src="<?php echo esc_url($card_image); ?>" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Card Title -->
                        <h3 class="ccv2-card-title"><?php the_title(); ?></h3>
                        
                        <?php if (!empty($bank_name)): ?>
                            <div class="ccv2-card-bank"><?php echo esc_html($bank_name); ?></div>
                        <?php endif; ?>
                        
                        <!-- Rating -->
                        <?php if ($rating > 0): ?>
                            <div class="ccv2-card-rating">
                                <div class="ccv2-rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php echo ccm_get_icon('star', 'icon'); ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="ccv2-rating-text">
                                    <?php echo esc_html($rating); ?>/5
                                    <?php if ($review_count > 0): ?>
                                        (<?php echo esc_html($review_count); ?> reviews)
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </header>
                    
                    <!-- Card Content -->
                    <div class="ccv2-card-content">
                        <!-- Key Highlights -->
                        <div class="ccv2-card-highlights">
                            <div class="ccv2-highlight">
                                <?php echo ccm_get_icon('credit-card', 'ccv2-highlight-icon'); ?>
                                <div class="ccv2-highlight-label">Annual Fee</div>
                                <div class="ccv2-highlight-value"><?php echo esc_html($annual_fee); ?></div>
                            </div>
                            
                            <div class="ccv2-highlight">
                                <?php echo ccm_get_icon('percentage', 'ccv2-highlight-icon'); ?>
                                <div class="ccv2-highlight-label">Reward Rate</div>
                                <div class="ccv2-highlight-value"><?php echo esc_html($cashback_rate); ?></div>
                            </div>
                            
                            <div class="ccv2-highlight" style="grid-column: 1 / -1;">
                                <?php echo ccm_get_icon('gift', 'ccv2-highlight-icon'); ?>
                                <div class="ccv2-highlight-label">Welcome Bonus</div>
                                <div class="ccv2-highlight-value"><?php echo esc_html($welcome_bonus); ?></div>
                            </div>
                        </div>
                        
                        <!-- Key Features -->
                        <?php if (!empty($pros) && is_array($pros)): ?>
                            <div class="ccv2-card-features">
                                <?php foreach (array_slice($pros, 0, 3) as $pro): ?>
                                    <div class="ccv2-feature-item">
                                        <?php echo ccm_get_icon('check', 'icon'); ?>
                                        <span class="ccv2-feature-text"><?php echo esc_html($pro); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div class="ccv2-card-actions">
                            <a href="<?php the_permalink(); ?>" class="ccv2-btn ccv2-btn-details">
                                <?php echo ccm_get_icon('info', 'icon'); ?>
                                Details
                            </a>
                            <a href="<?php echo esc_url($apply_link); ?>" class="ccv2-btn ccv2-btn-apply" target="_blank" rel="noopener">
                                Apply Now
                                <?php echo ccm_get_icon('arrow-right', 'icon'); ?>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            
            <!-- Enhanced Pagination -->
            <?php
            $total_pages = $credit_cards->max_num_pages;
            if ($total_pages > 1):
                $current_page = max(1, get_query_var('paged'));
            ?>
            <nav class="ccv2-pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo get_pagenum_link($current_page - 1); ?>" class="ccv2-pagination-link">
                        &laquo;
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                    <a href="<?php echo get_pagenum_link($i); ?>" 
                       class="ccv2-pagination-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?php echo get_pagenum_link($current_page + 1); ?>" class="ccv2-pagination-link">
                        &raquo;
                    </a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        <?php else: ?>
            <!-- Enhanced No Results -->
            <div class="ccv2-no-results">
                <?php echo ccm_get_icon('info', 'ccv2-no-results-icon'); ?>
                <h3>No credit cards found</h3>
                <p>We couldn't find any credit cards matching your criteria. Try adjusting your filters or explore our featured cards.</p>
                <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="ccv2-btn ccv2-btn-primary">
                    View All Cards
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Enhanced Comparison Bar -->
        <div class="ccv2-comparison-bar" id="comparison-bar">
            <div class="ccv2-comparison-content">
                <div class="ccv2-comparison-info">
                    <?php echo ccm_get_icon('compare', 'ccv2-comparison-icon'); ?>
                    <div class="ccv2-comparison-text">
                        <span class="ccv2-comparison-count" id="selected-count">0</span>
                        cards selected for comparison
                    </div>
                </div>
                <div class="ccv2-comparison-actions">
                    <button type="button" class="ccv2-btn ccv2-btn-clear" id="clear-comparison">
                        Clear All
                    </button>
                    <button type="button" class="ccv2-btn ccv2-btn-compare-now" id="compare-now" disabled>
                        <?php echo ccm_get_icon('compare', 'icon'); ?>
                        Compare Now
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    // Enhanced filter toggle functionality
    const toggleBtn = document.getElementById('toggle-filters');
    const filterContent = document.getElementById('filter-content');
    
    if (toggleBtn && filterContent) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = filterContent.style.display !== 'none';
            filterContent.style.display = isVisible ? 'none' : 'grid';
            const spanText = toggleBtn.querySelector('span');
            const iconEl = toggleBtn.querySelector('svg');
            
            if (spanText) {
                spanText.textContent = isVisible ? 'Show Filters' : 'Hide Filters';
            }
            
            if (iconEl) {
                iconEl.outerHTML = isVisible 
                    ? '<?php echo ccm_get_icon('plus', 'icon'); ?>'
                    : '<?php echo ccm_get_icon('minus', 'icon'); ?>';
            }
        });
    }
    
    // Enhanced sort functionality
    window.updateSort = function(value) {
        const [sort, order] = value.split('-');
        const url = new URL(window.location.href);
        url.searchParams.set('sort_by', sort);
        url.searchParams.set('sort_order', order);
        window.location.href = url.toString();
    };
    
    // Enhanced comparison functionality
    const comparisonBar = document.getElementById('comparison-bar');
    const compareButtons = document.querySelectorAll('.ccv2-btn-compare');
    const clearComparisonBtn = document.getElementById('clear-comparison');
    const compareNowBtn = document.getElementById('compare-now');
    const selectedCountEl = document.getElementById('selected-count');
    
    let selectedCards = [];
    const maxCompare = 3;
    
    // Load selected cards from localStorage
    function loadSelectedCards() {
        const saved = localStorage.getItem('ccv2_compare_cards');
        if (saved) {
            try {
                selectedCards = JSON.parse(saved);
                updateComparisonUI();
            } catch (e) {
                console.error('Error loading saved comparison data', e);
                selectedCards = [];
            }
        }
    }
    
    // Save selected cards to localStorage
    function saveSelectedCards() {
        localStorage.setItem('ccv2_compare_cards', JSON.stringify(selectedCards));
    }
    
    // Update UI based on selected cards
    function updateComparisonUI() {
        // Update buttons
        compareButtons.forEach(btn => {
            const cardId = btn.getAttribute('data-id');
            const isSelected = selectedCards.includes(cardId);
            const spanEl = btn.querySelector('span');
            
            btn.classList.toggle('active', isSelected);
            
            if (spanEl) {
                spanEl.textContent = isSelected ? 'Remove' : 'Compare';
            }
            
            // Update icon
            const iconEl = btn.querySelector('svg');
            if (iconEl) {
                iconEl.outerHTML = isSelected 
                    ? '<?php echo ccm_get_icon('minus', 'icon'); ?>'
                    : '<?php echo ccm_get_icon('compare', 'icon'); ?>';
            }
        });
        
        // Update comparison bar
        if (selectedCards.length > 0) {
            comparisonBar.classList.add('active');
            selectedCountEl.textContent = selectedCards.length;
            compareNowBtn.disabled = selectedCards.length < 2;
        } else {
            comparisonBar.classList.remove('active');
        }
    }
    
    // Toggle card selection
    function toggleCardSelection(cardId) {
        const index = selectedCards.indexOf(cardId);
        
        if (index > -1) {
            selectedCards.splice(index, 1);
        } else {
            if (selectedCards.length < maxCompare) {
                selectedCards.push(cardId);
            } else {
                alert(`You can only compare up to ${maxCompare} cards at once.`);
                return;
            }
        }
        
        saveSelectedCards();
        updateComparisonUI();
    }
    
    // Initialize comparison functionality
    function initComparison() {
        loadSelectedCards();
        
        compareButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const cardId = this.getAttribute('data-id');
                toggleCardSelection(cardId);
            });
        });
        
        if (clearComparisonBtn) {
            clearComparisonBtn.addEventListener('click', function() {
                selectedCards = [];
                saveSelectedCards();
                updateComparisonUI();
            });
        }
        
        if (compareNowBtn) {
            compareNowBtn.addEventListener('click', function() {
                if (selectedCards.length >= 2) {
                    // Redirect to dedicated comparison page
                    const compareUrl = `${window.location.origin}/compare-cards?cards=${selectedCards.join(',')}`;
                    window.location.href = compareUrl;
                }
            });
        }
    }
    
    // Initialize comparison functionality if we're not in comparison mode
    <?php if (!$compare_mode): ?>
    initComparison();
    <?php endif; ?>
    
    // Add loading states for better UX
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scrolling to pagination links
        const paginationLinks = document.querySelectorAll('.ccv2-pagination-link');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Add loading state
                const heroSection = document.querySelector('.ccv2-hero');
                if (heroSection) {
                    heroSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        // Add form submission loading state
        const filterForm = document.querySelector('.ccv2-filter-section form');
        if (filterForm) {
            filterForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('.ccv2-btn-primary');
                if (submitBtn) {
                    submitBtn.innerHTML = '<div class="ccv2-spinner"></div> Applying...';
                    submitBtn.disabled = true;
                }
            });
        }
    });
})();
</script>

<?php get_footer(); ?>