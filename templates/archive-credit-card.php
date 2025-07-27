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


// Get filter data from API
$filters_response = wp_remote_get(rest_url('ccm/v1/credit-cards/filters'));
$filters = !is_wp_error($filters_response) ? json_decode(wp_remote_retrieve_body($filters_response), true) : [];

// Build query args for credit cards
$args = [
    'post_type' => 'credit-card',
    'posts_per_page' => 12,
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

    <!-- Regular Archive View -->
        
        <!-- Simplified Filter Section -->
        <form class="ccv2-filter-section" method="get" action="<?php echo get_post_type_archive_link('credit-card'); ?>" style="background: white; margin: 1rem 1.5rem; padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div class="ccv2-filter-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 class="ccv2-filter-title" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1f2937;">
                    🔍 Find Your Ideal Credit Card
                </h2>
                <button type="button" class="ccv2-filter-toggle" id="toggle-filters" style="background: #f3f4f6; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.875rem;">
                    <span id="toggle-text">Hide Filters</span>
                </button>
            </div>
            
            <div class="ccv2-filter-grid" id="filter-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <?php if (!empty($filters['banks'])): ?>
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="bank" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Bank/Issuer</label>
                    <select class="ccv2-filter-select" name="bank" id="bank" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; background: white;">
                        <option value="">All Banks</option>
                        <?php foreach ($filters['banks'] as $bank): ?>
                            <option value="<?php echo esc_attr($bank['slug']); ?>" <?php selected($current_bank, $bank['slug']); ?>>
                                <?php echo esc_html($bank['name']); ?> (<?php echo esc_html($bank['count']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <?php 
                $select_style = 'width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; background: white;';
                $label_style = 'display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;';
                ?>
                
                <?php if (!empty($filters['network_types'])): ?>
                <div class="ccv2-filter-group">
                    <label class="ccv2-filter-label" for="network_type" style="<?php echo $label_style; ?>">Network Type</label>
                    <select class="ccv2-filter-select" name="network_type" id="network_type" style="<?php echo $select_style; ?>">
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
                    <label class="ccv2-filter-label" for="category" style="<?php echo $label_style; ?>">Category</label>
                    <select class="ccv2-filter-select" name="category" id="category" style="<?php echo $select_style; ?>">
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
                    <label class="ccv2-filter-label" for="min_rating" style="<?php echo $label_style; ?>">Minimum Rating</label>
                    <select class="ccv2-filter-select" name="min_rating" id="min_rating" style="<?php echo $select_style; ?>">
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
                    <label class="ccv2-filter-label" for="max_annual_fee" style="<?php echo $label_style; ?>">Maximum Annual Fee</label>
                    <select class="ccv2-filter-select" name="max_annual_fee" id="max_annual_fee" style="<?php echo $select_style; ?>">
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
                    <label class="ccv2-filter-label" for="featured" style="<?php echo $label_style; ?>">Card Type</label>
                    <select class="ccv2-filter-select" name="featured" id="featured" style="<?php echo $select_style; ?>">
                        <option value="">All Cards</option>
                        <option value="1" <?php selected($current_featured, '1'); ?>>Featured Cards</option>
                        <option value="0" <?php selected($current_featured, '0'); ?>>Regular Cards</option>
                    </select>
                </div>
            </div>
            
            <div class="ccv2-filter-actions" style="display: flex; gap: 1rem; justify-content: center;">
                <button type="reset" style="padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                    Reset Filters
                </button>
                <button type="submit" style="padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    🔍 Apply Filters
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
            <!-- Compact Cards Grid -->
            <div class="ccv2-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; margin: 1rem 1.5rem;">
                <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                    $post_id = get_the_ID();
                    $card_image = has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'medium') : '';
                    
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
                <article class="ccv2-card" data-id="<?php echo esc_attr($post_id); ?>" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; position: relative; height: fit-content;">
                    <?php if ($featured): ?>
                        <span style="position: absolute; top: 0.5rem; left: 0.5rem; background: #f59e0b; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; z-index: 2;">
                            ⭐ Featured
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($trending): ?>
                        <span style="position: absolute; top: 0.5rem; right: 0.5rem; background: #ef4444; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; z-index: 2;">
                            🔥 Trending
                        </span>
                    <?php endif; ?>
                    
                    <!-- Card Header -->
                    <header style="text-align: center; margin-bottom: 1rem;">
                        
                        <!-- Card Image -->
                        <div style="margin-bottom: 0.75rem;">
                            <?php if (!empty($card_image)): ?>
                                <img src="<?php echo esc_url($card_image); ?>" alt="<?php the_title(); ?>" style="width: 120px; height: auto; object-fit: contain; border-radius: 6px;">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Card Title -->
                        <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; font-weight: 600; color: #1f2937;"><?php the_title(); ?></h3>
                        
                        <?php if (!empty($bank_name)): ?>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo esc_html($bank_name); ?></div>
                        <?php endif; ?>
                        
                        <!-- Rating -->
                        <?php if ($rating > 0): ?>
                            <div style="margin-bottom: 0.75rem;">
                                <div style="color: #f59e0b; margin-bottom: 0.25rem;">
                                    <?php echo str_repeat('⭐', floor($rating)); ?>
                                </div>
                                <span style="font-size: 0.75rem; color: #6b7280;">
                                    <?php echo esc_html($rating); ?>/5
                                    <?php if ($review_count > 0): ?>
                                        (<?php echo esc_html($review_count); ?> reviews)
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </header>
                    
                    <!-- Card Content -->
                    <div style="margin-bottom: 1rem;">
                        <!-- Compact Key Highlights -->
                        <div style="margin-bottom: 1rem; font-size: 0.8rem; line-height: 1.4;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <span style="color: #6b7280;">Annual Fee:</span>
                                <span style="font-weight: 600; color: #1f2937;"><?php echo esc_html($annual_fee); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <span style="color: #6b7280;">Reward Rate:</span>
                                <span style="font-weight: 600; color: #1f2937;"><?php echo esc_html($cashback_rate); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: #6b7280;">Welcome Bonus:</span>
                                <span style="font-weight: 600; color: #1f2937;"><?php echo esc_html($welcome_bonus); ?></span>
                            </div>
                        </div>
                        
                        <!-- Key Features -->
                        <?php if (!empty($pros) && is_array($pros)): ?>
                            <div style="margin-bottom: 1rem;">
                                <?php foreach (array_slice($pros, 0, 2) as $pro): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; font-size: 0.8rem; color: #6b7280;">
                                        <span style="color: #10b981;">✓</span>
                                        <span><?php echo esc_html($pro); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <a href="<?php the_permalink(); ?>" style="padding: 0.5rem; text-align: center; background: #f3f4f6; color: #374151; border-radius: 6px; text-decoration: none; font-size: 0.875rem;">
                                Details
                            </a>
                            <a href="<?php echo esc_url($apply_link); ?>" target="_blank" rel="noopener" style="padding: 0.5rem; text-align: center; background: #3b82f6; color: white; border-radius: 6px; text-decoration: none; font-size: 0.875rem;">
                                Apply Now
                            </a>
                        </div>
                        
                        <!-- Compare Checkbox -->
                        <div style="text-align: center;">
                            <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.8rem; color: #6b7280;">
                                <input type="checkbox" class="ccv2-compare-checkbox" data-id="<?php echo esc_attr($post_id); ?>" style="margin: 0;">
                                <span>Compare this card</span>
                            </label>
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
        
        <!-- Compact Comparison Bar -->
        <div class="ccv2-comparison-bar" id="comparison-bar" style="position: fixed; bottom: 0; left: 0; right: 0; background: #1f2937; color: white; padding: 0.75rem; z-index: 1000; transform: translateY(100%); transition: transform 0.3s ease;">
            <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span>⚖️</span>
                    <div style="font-size: 0.875rem;">
                        <span id="selected-count">0</span> cards selected
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" id="clear-comparison" style="padding: 0.5rem 1rem; background: transparent; color: white; border: 1px solid #374151; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">
                        Clear
                    </button>
                    <button type="button" id="compare-now" disabled style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">
                        Compare
                    </button>
                </div>
            </div>
        </div>
</div>

<script>
(function() {
    // Filter toggle functionality
    const toggleBtn = document.getElementById('toggle-filters');
    const filterContent = document.getElementById('filter-content');
    const toggleText = document.getElementById('toggle-text');
    
    if (toggleBtn && filterContent && toggleText) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = filterContent.style.display !== 'none';
            filterContent.style.display = isVisible ? 'none' : 'grid';
            toggleText.textContent = isVisible ? 'Show Filters' : 'Hide Filters';
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
    const compareCheckboxes = document.querySelectorAll('.ccv2-compare-checkbox');
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
        // Update checkboxes
        compareCheckboxes.forEach(checkbox => {
            const cardId = checkbox.getAttribute('data-id');
            const isSelected = selectedCards.includes(cardId);
            checkbox.checked = isSelected;
        });
        
        // Update comparison bar
        if (selectedCards.length > 0) {
            comparisonBar.style.transform = 'translateY(0)';
            selectedCountEl.textContent = selectedCards.length;
            compareNowBtn.disabled = selectedCards.length < 2;
            compareNowBtn.style.opacity = selectedCards.length < 2 ? '0.5' : '1';
        } else {
            comparisonBar.style.transform = 'translateY(100%)';
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
        
        compareCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
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
                    // Navigate to dedicated compare-card page
                    const compareUrl = `${window.location.origin}/compare-card/?cards=${selectedCards.join(',')}`;
                    window.location.href = compareUrl;
                }
            });
        }
    }
    
    // Initialize comparison functionality
    initComparison();
    
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