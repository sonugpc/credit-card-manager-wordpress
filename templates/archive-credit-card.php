<?php
/**
 * The template for displaying Credit Card archive pages - Optimized Version
 * Performance-focused design with lowest LCP and clean CSS architecture
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
$current_sort = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'date';
$current_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'desc';

// Get filter data from cached function instead of API call for better performance
$filters = ccm_get_filters_data();

// Build simple query args for credit cards - just sort by date, no filters initially
$args = [
    'post_type' => 'credit-card',
    'post_status' => 'publish',
    'posts_per_page' => 8, // Show 8 initially, load more will add 8 each time
    'paged' => 1, // Always start with page 1 for AJAX loading
    'orderby' => 'date',
    'order' => 'DESC',
];

// Run the query
$credit_cards = new WP_Query($args);

// Get credit card categories for CTA section
$credit_card_categories = [
    [
        'icon' => 'rbi-bag-add',
        'title' => 'Cashback Cards',
        'description' => 'Earn cashback on everyday spending',
        'link' => '/card-category/cashback/'
    ],
    [
        'icon' => 'rbi-tripadvisor',
        'title' => 'Travel Cards',
        'description' => 'Miles, lounges, and travel benefits',
        'link' => '/card-category/travel/'
    ],
    [
        'icon' => 'rbi-shopping-bag',
        'title' => 'Shopping Cards',
        'description' => 'Rewards on online and offline shopping',
        'link' => '/card-category/shopping/'
    ],
    [
        'icon' => 'rbi-star',
        'title' => 'Premium Cards',
        'description' => 'Exclusive benefits and luxury perks',
        'link' => '/card-category/premium/'
    ],
    [
        'icon' => 'rbi-flame',
        'title' => 'Fuel Cards',
        'description' => 'Save on fuel and transportation',
        'link' => '/card-category/fuel/'
    ],
    [
        'icon' => 'rbi-myspace',
        'title' => 'Business Cards',
        'description' => 'Designed for business expenses',
        'link' => '/card-category/business/'
    ]
];

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
    $category_term = get_term_by('slug', $current_category, 'card-category');
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

<div class="ccm-archive">
    <!-- Hero Section - Critical for LCP -->
    <section class="ccm-hero">
        <div class="ccm-hero-content">
            <h1><?php echo esc_html($archive_title); ?></h1>
            <p><?php echo esc_html($archive_description); ?></p>
            <div class="ccm-hero-stats">
                <div class="ccm-stat">
                    <span class="ccm-stat-number"><?php echo number_format($credit_cards->found_posts); ?>+</span>
                    <span class="ccm-stat-label">Credit Cards</span>
                </div>
                <div class="ccm-stat">
                    <span class="ccm-stat-number">50+</span>
                    <span class="ccm-stat-label">Banks & NBFCs</span>
                </div>
                <div class="ccm-stat">
                    <span class="ccm-stat-number">10K+</span>
                    <span class="ccm-stat-label">Happy Customers</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="ccm-filters-section">
        <div class="ccm-container">
            <form class="ccm-filters-form" method="get" action="<?php echo get_post_type_archive_link('credit-card'); ?>">
                <div class="ccm-filters-header">
                    <h2 class="ccm-filters-title">🔍 Find Your Perfect Credit Card</h2>
                    <button type="button" class="ccm-filters-toggle" id="toggle-filters">
                        <span id="toggle-text">Hide Filters</span>
                    </button>
                </div>

                <div class="ccm-filters-grid" id="filter-content">
                    <?php if (!empty($filters['banks'])): ?>
                    <div class="ccm-filter-group">
                        <label class="ccm-filter-label" for="bank">Bank/Issuer</label>
                        <select class="ccm-filter-select" name="bank" id="bank">
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
                    <div class="ccm-filter-group">
                        <label class="ccm-filter-label" for="network_type">Network Type</label>
                        <select class="ccm-filter-select" name="network_type" id="network_type">
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
                    <div class="ccm-filter-group">
                        <label class="ccm-filter-label" for="category">Category</label>
                        <select class="ccm-filter-select" name="category" id="category">
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
                    <div class="ccm-filter-group">
                        <label class="ccm-filter-label" for="min_rating">Minimum Rating</label>
                        <select class="ccm-filter-select" name="min_rating" id="min_rating">
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
                    <div class="ccm-filter-group">
                        <label class="ccm-filter-label" for="max_annual_fee">Max Annual Fee</label>
                        <select class="ccm-filter-select" name="max_annual_fee" id="max_annual_fee">
                            <option value="">Any Fee</option>
                            <?php foreach ($filters['fee_ranges'] as $range): ?>
                                <option value="<?php echo esc_attr($range['max']); ?>" <?php selected($current_max_fee, $range['max']); ?>>
                                    <?php echo esc_html($range['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="ccm-filter-group">
                        <label class="ccm-filter-label" for="featured">Card Type</label>
                        <select class="ccm-filter-select" name="featured" id="featured">
                            <option value="">All Cards</option>
                            <option value="1" <?php selected($current_featured, '1'); ?>>Featured Cards</option>
                            <option value="0" <?php selected($current_featured, '0'); ?>>Regular Cards</option>
                        </select>
                    </div>
                </div>

                <div class="ccm-filter-actions">
                    <button type="reset" class="ccm-btn ccm-btn-secondary">Reset Filters</button>
                    <button type="submit" class="ccm-btn ccm-btn-primary">🔍 Apply Filters</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Results Section -->
    <section class="ccm-results-section">
        <div class="ccm-container">
            <!-- Results Bar -->
            <div class="ccm-results-bar">
                <div class="ccm-results-info">
                    <div class="ccm-results-count">
                        <strong><?php echo number_format($credit_cards->found_posts); ?></strong> credit cards found
                    </div>
                    <div class="ccm-active-filters">
                        <?php if ($current_bank): ?>
                            <span class="ccm-filter-tag">Bank: <?php echo esc_html($current_bank); ?></span>
                        <?php endif; ?>
                        <?php if ($current_category): ?>
                            <span class="ccm-filter-tag">Category: <?php echo esc_html($current_category); ?></span>
                        <?php endif; ?>
                        <?php if ($current_min_rating): ?>
                            <span class="ccm-filter-tag">Rating: <?php echo esc_html($current_min_rating); ?>+</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ccm-sort-controls">
                    <span class="ccm-sort-label">Sort by:</span>
                    <select class="ccm-sort-select" id="sort-select" onchange="ccmUpdateSort(this.value)">
                        <option value="date-desc" <?php echo ($current_sort === 'date' && $current_order === 'desc') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="rating-desc" <?php echo ($current_sort === 'rating' && $current_order === 'desc') ? 'selected' : ''; ?>>Highest Rated</option>
                        <option value="rating-asc" <?php echo ($current_sort === 'rating' && $current_order === 'asc') ? 'selected' : ''; ?>>Lowest Rated</option>
                        <option value="annual_fee-asc" <?php echo ($current_sort === 'annual_fee' && $current_order === 'asc') ? 'selected' : ''; ?>>Lowest Fee</option>
                        <option value="annual_fee-desc" <?php echo ($current_sort === 'annual_fee' && $current_order === 'desc') ? 'selected' : ''; ?>>Highest Fee</option>
                        <option value="review_count-desc" <?php echo ($current_sort === 'review_count' && $current_order === 'desc') ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                </div>
            </div>

            <?php if ($credit_cards->have_posts()): ?>
                <!-- Cards Grid - Optimized for LCP -->
                <div class="ccm-cards-grid">
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
                    <article class="ccm-card" data-id="<?php echo esc_attr($post_id); ?>">
                        <?php if ($featured): ?>
                            <span class="ccm-badge ccm-badge-featured">⭐ Featured</span>
                        <?php endif; ?>

                        <?php if ($trending): ?>
                            <span class="ccm-badge ccm-badge-trending">🔥 Trending</span>
                        <?php endif; ?>

                        <header class="ccm-card-header">
                            <?php if (!empty($card_image)): ?>
                                <img src="<?php echo esc_url($card_image); ?>"
                                     alt="<?php the_title(); ?>"
                                     class="ccm-card-image"
                                     loading="lazy"
                                     width="120"
                                     height="auto">
                            <?php endif; ?>

                            <h3 class="ccm-card-title"><?php the_title(); ?></h3>

                            <?php if (!empty($bank_name)): ?>
                                <div class="ccm-card-bank"><?php echo esc_html($bank_name); ?></div>
                            <?php endif; ?>

                            <?php if ($rating > 0): ?>
                                <div class="ccm-card-rating">
                                    <div class="ccm-rating-stars">
                                        <?php echo str_repeat('⭐', floor($rating)); ?>
                                    </div>
                                    <span class="ccm-rating-text">
                                        <?php echo esc_html($rating); ?>/5
                                        <?php if ($review_count > 0): ?>
                                            (<?php echo esc_html($review_count); ?> reviews)
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </header>

                        <div class="ccm-card-content">
                            <div class="ccm-card-highlights">
                                <div class="ccm-highlight">
                                    <span class="ccm-highlight-label">Annual Fee</span>
                                    <span class="ccm-highlight-value"><?php echo esc_html($annual_fee); ?></span>
                                </div>
                                <div class="ccm-highlight">
                                    <span class="ccm-highlight-label">Rewards</span>
                                    <span class="ccm-highlight-value"><?php echo esc_html($cashback_rate); ?></span>
                                </div>
                            </div>

                            <?php if (!empty($pros) && is_array($pros)): ?>
                                <div class="ccm-card-features">
                                    <?php foreach (array_slice($pros, 0, 2) as $pro): ?>
                                        <div class="ccm-feature-item">
                                            <span class="ccm-feature-icon">✓</span>
                                            <span class="ccm-feature-text"><?php echo esc_html($pro); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="ccm-card-actions">
                                <a href="<?php the_permalink(); ?>" class="ccm-btn ccm-btn-secondary">Details</a>
                                <a href="<?php echo esc_url($apply_link); ?>" class="ccm-btn ccm-btn-primary" target="_blank" rel="noopener">Apply Now</a>
                            </div>

                            <label class="ccm-compare-label">
                                <input type="checkbox" class="ccm-compare-checkbox" data-id="<?php echo esc_attr($post_id); ?>">
                                <span>Compare this card</span>
                            </label>
                        </div>
                    </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>

                <!-- Load More Button -->
                <?php if ($credit_cards->found_posts > $credit_cards->post_count): ?>
                <div class="ccm-load-more-container">
                    <button id="ccm-load-more-btn" class="ccm-btn ccm-btn-primary ccm-btn-large" data-page="2" data-loading="false">
                        Load More Cards
                        <span class="ccm-loading-spinner" style="display: none;">⟳</span>
                    </button>
                    <div id="ccm-load-more-message" style="display: none; margin-top: 1rem; text-align: center; color: #666;">
                        No more cards to load
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- No Results -->
                <div class="ccm-no-results">
                    <div class="ccm-no-results-icon">🔍</div>
                    <h3>No credit cards found</h3>
                    <p>Try adjusting your filters or browse all available cards.</p>
                    <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="ccm-btn ccm-btn-primary">View All Cards</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- SEO Content Section -->
    <!-- <section class="ccm-seo-section">
        <div class="ccm-container">
            <div class="ccm-seo-content">
                <h2>Find the Best Credit Card for Your Needs</h2>
                <p>Choosing the right credit card can save you thousands of rupees annually through rewards, cashback, and exclusive benefits. Our comprehensive comparison helps you make informed decisions based on expert reviews and real user experiences.</p>

                <div class="ccm-seo-grid">
                    <div class="ccm-seo-block">
                        <h3>🏦 Top Banks & Their Best Cards</h3>
                        <p>Compare credit cards from India's leading banks including HDFC, ICICI, SBI, Axis, and Kotak Mahindra. Each bank offers unique rewards programs and benefits tailored to different spending habits.</p>
                        <div class="ccm-seo-links">
                            <a href="/credit-card/hdfc-bank-credit-cards/">HDFC Bank Cards</a>
                            <a href="/credit-card/icici-bank-credit-cards/">ICICI Bank Cards</a>
                            <a href="/credit-card/sbi-credit-cards/">SBI Credit Cards</a>
                            <a href="/credit-card/axis-bank-credit-cards/">Axis Bank Cards</a>
                        </div>
                    </div>

                    <div class="ccm-seo-block">
                        <h3>💰 Reward Types Explained</h3>
                        <p>Understanding different reward structures is key to maximizing your credit card benefits. From cashback to reward points, travel miles to fuel rewards - find the perfect match for your lifestyle.</p>
                        <div class="ccm-seo-links">
                            <a href="/credit-card/cashback-credit-cards/">Cashback Cards</a>
                            <a href="/credit-card/reward-points-credit-cards/">Reward Points Cards</a>
                            <a href="/credit-card/travel-credit-cards/">Travel Cards</a>
                            <a href="/credit-card/fuel-credit-cards/">Fuel Cards</a>
                        </div>
                    </div>

                    <div class="ccm-seo-block">
                        <h3>📊 Credit Card Fees & Charges</h3>
                        <p>Make informed decisions by understanding all costs associated with credit cards. Compare annual fees, foreign transaction charges, and other expenses to find the most cost-effective option.</p>
                        <div class="ccm-seo-links">
                            <a href="/credit-card/low-annual-fee-cards/">Low Fee Cards</a>
                            <a href="/credit-card/zero-annual-fee-cards/">Zero Annual Fee Cards</a>
                            <a href="/credit-card/premium-credit-cards/">Premium Cards</a>
                        </div>
                    </div>
                </div>

                <div class="ccm-seo-cta">
                    <h3>Ready to Apply for Your Perfect Credit Card?</h3>
                    <p>Our expert reviews and detailed comparisons ensure you choose the right card. Apply online with instant approval and start earning rewards today.</p>
                    <a href="#ccm-filters-section" class="ccm-btn ccm-btn-primary ccm-btn-large">Find Your Card Now</a>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Card Categories CTA -->
    <section class="ccm-categories-section">
        <div class="ccm-container">
            <div class="bt-card-categories">
                <h3 class="bt-categories-title">Browse by Card Type</h3>
                <div class="bt-card-types-grid">
                    <?php foreach ($credit_card_categories as $category) : ?>
                        <div class="bt-card-type" <?php if (!empty($category['link'])) : ?>onclick="window.location.href='<?php echo esc_url($category['link']); ?>'"<?php endif; ?>>
                            <div class="bt-card-type-icon">
                                <span class="rbi <?php echo esc_attr($category['icon']); ?>"></span>
                            </div>
                            <h4><?php echo esc_html($category['title']); ?></h4>
                            <p><?php echo esc_html($category['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Blogs Section -->
    <section class="ccm-blogs-section">
        <div class="ccm-container">
            <div class="ccm-blogs-header">
                <h2>Latest Credit Card News & Guides</h2>
                <p>Stay informed with expert insights, tips, and the latest updates from the credit card industry.</p>
            </div>

            <div class="ccm-blogs-grid">
                <?php
                // Fetch random 3 posts from credit-card-bill-payment-offers category
                $blog_args = [
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                    'orderby' => 'rand',
                    'category_name' => 'credit-card-bill-payment-offers' // Fetch from specific category
                ];

                $blog_query = new WP_Query($blog_args);

                if ($blog_query->have_posts()) {
                    while ($blog_query->have_posts()) {
                        $blog_query->the_post();
                        $blog_icon = '📈'; // Default icon

                        // Set different icons based on content
                        if (stripos(get_the_title(), 'reward') !== false || stripos(get_the_content(), 'reward') !== false) {
                            $blog_icon = '💰';
                        } elseif (stripos(get_the_title(), 'security') !== false || stripos(get_the_content(), 'security') !== false) {
                            $blog_icon = '🛡️';
                        } elseif (stripos(get_the_title(), 'trend') !== false || stripos(get_the_content(), 'trend') !== false) {
                            $blog_icon = '📈';
                        } elseif (stripos(get_the_title(), 'guide') !== false || stripos(get_the_content(), 'guide') !== false) {
                            $blog_icon = '📚';
                        } elseif (stripos(get_the_title(), 'tip') !== false || stripos(get_the_content(), 'tip') !== false) {
                            $blog_icon = '💡';
                        }
                        ?>
                        <article class="ccm-blog-card">
                            <div class="ccm-blog-image">
                                <?php if (has_post_thumbnail()): ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium')); ?>"
                                         alt="<?php the_title(); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <span class="ccm-blog-icon"><?php echo esc_html($blog_icon); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="ccm-blog-content">
                                <h3><?php the_title(); ?></h3>
                                <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15, '...')); ?></p>
                                <a href="<?php the_permalink(); ?>" class="ccm-blog-link">Read More →</a>
                            </div>
                        </article>
                        <?php
                    }
                    wp_reset_postdata();
                } else {
                    // Fallback static content if no posts found
                    ?>
                    <article class="ccm-blog-card">
                        <div class="ccm-blog-image">
                            <span class="ccm-blog-icon">📈</span>
                        </div>
                        <div class="ccm-blog-content">
                            <h3>Credit Card Trends 2024</h3>
                            <p>Discover the latest trends shaping India's credit card industry this year.</p>
                            <a href="/blog/credit-card-trends-2024/" class="ccm-blog-link">Read More →</a>
                        </div>
                    </article>

                    <article class="ccm-blog-card">
                        <div class="ccm-blog-image">
                            <span class="ccm-blog-icon">💰</span>
                        </div>
                        <div class="ccm-blog-content">
                            <h3>Maximize Your Credit Card Rewards</h3>
                            <p>Expert tips to get the most out of your credit card rewards program.</p>
                            <a href="/blog/maximize-credit-card-rewards/" class="ccm-blog-link">Read More →</a>
                        </div>
                    </article>

                    <article class="ccm-blog-card">
                        <div class="ccm-blog-image">
                            <span class="ccm-blog-icon">🛡️</span>
                        </div>
                        <div class="ccm-blog-content">
                            <h3>Credit Card Security Guide</h3>
                            <p>Essential tips to protect your credit card from fraud and unauthorized transactions.</p>
                            <a href="/blog/credit-card-security-guide/" class="ccm-blog-link">Read More →</a>
                        </div>
                    </article>
                    <?php
                }
                ?>
            </div>

            <div class="ccm-blogs-cta">
                <a href="/credit-card-bill-payment-offers/" class="ccm-btn ccm-btn-secondary">View All Articles</a>
            </div>
        </div>
    </section>

    <!-- Comparison Bar -->
    <div class="ccm-comparison-bar" id="comparison-bar">
        <div class="ccm-comparison-content">
            <div class="ccm-comparison-info">
                <span class="ccm-comparison-icon">⚖️</span>
                <span class="ccm-comparison-text"><span id="selected-count">0</span> cards selected</span>
            </div>
            <div class="ccm-comparison-actions">
                <button type="button" id="clear-comparison" class="ccm-btn ccm-btn-secondary">Clear</button>
                <button type="button" id="compare-now" class="ccm-btn ccm-btn-primary" disabled>Compare</button>
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

    // Sort functionality
    window.ccmUpdateSort = function(value) {
        const [sort, order] = value.split('-');
        const url = new URL(window.location.href);
        url.searchParams.set('sort_by', sort);
        url.searchParams.set('sort_order', order);
        window.location.href = url.toString();
    };

    // Comparison functionality
    const comparisonBar = document.getElementById('comparison-bar');
    const compareCheckboxes = document.querySelectorAll('.ccm-compare-checkbox');
    const clearComparisonBtn = document.getElementById('clear-comparison');
    const compareNowBtn = document.getElementById('compare-now');
    const selectedCountEl = document.getElementById('selected-count');

    let selectedCards = [];
    const maxCompare = 3;

    // Load selected cards from localStorage
    function loadSelectedCards() {
        const saved = localStorage.getItem('ccm_compare_cards');
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
        localStorage.setItem('ccm_compare_cards', JSON.stringify(selectedCards));
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
                    const compareUrl = `${window.location.origin}/compare-cards?cards=${selectedCards.join(',')}`;
                    window.location.href = compareUrl;
                }
            });
        }
    }

    // Initialize functionality
    initComparison();

    // Load More functionality
    const loadMoreBtn = document.getElementById('ccm-load-more-btn');
    const loadMoreMessage = document.getElementById('ccm-load-more-message');
    const cardsGrid = document.querySelector('.ccm-cards-grid');

    if (loadMoreBtn && cardsGrid) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.getAttribute('data-page'));
            const isLoading = this.getAttribute('data-loading') === 'true';

            if (isLoading) return;

            // Set loading state
            this.setAttribute('data-loading', 'true');
            this.innerHTML = 'Loading... <span class="ccm-loading-spinner">⟳</span>';
            this.disabled = true;

            // Make AJAX request
            fetch(`${window.location.origin}/wp-json/ccm/v1/credit-cards?page=${currentPage}&per_page=8&orderby=date&order=desc`)
                .then(response => response.json())
                .then(data => {
                    if (data.data && data.data.length > 0) {
                        // Append new cards to grid
                        data.data.forEach(card => {
                            const cardHTML = createCardHTML(card);
                            cardsGrid.insertAdjacentHTML('beforeend', cardHTML);
                        });

                        // Update button for next page
                        const nextPage = currentPage + 1;
                        loadMoreBtn.setAttribute('data-page', nextPage);
                        loadMoreBtn.innerHTML = 'Load More Cards <span class="ccm-loading-spinner" style="display: none;">⟳</span>';
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.setAttribute('data-loading', 'false');

                        // Hide button if no more cards
                        if (data.data.length < 8) {
                            loadMoreBtn.style.display = 'none';
                            loadMoreMessage.style.display = 'block';
                        }
                    } else {
                        // No more cards
                        loadMoreBtn.style.display = 'none';
                        loadMoreMessage.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error loading more cards:', error);
                    loadMoreBtn.innerHTML = 'Load More Cards <span class="ccm-loading-spinner" style="display: none;">⟳</span>';
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.setAttribute('data-loading', 'false');
                });
        });
    }

    // Helper function to create card HTML
    function createCardHTML(card) {
        const featuredBadge = card.featured ? '<span class="ccm-badge ccm-badge-featured">⭐ Featured</span>' : '';
        const trendingBadge = card.trending ? '<span class="ccm-badge ccm-badge-trending">🔥 Trending</span>' : '';

        const ratingStars = card.rating > 0 ? '⭐'.repeat(Math.floor(card.rating)) : '';
        const ratingText = card.rating > 0 ? `${card.rating}/5${card.review_count > 0 ? ` (${card.review_count} reviews)` : ''}` : '';

        const pros = card.pros && card.pros.length > 0 ?
            card.pros.slice(0, 2).map(pro => `<div class="ccm-feature-item"><span class="ccm-feature-icon">✓</span><span class="ccm-feature-text">${pro}</span></div>`).join('') : '';

        return `
            <article class="ccm-card" data-id="${card.id}">
                ${featuredBadge}
                ${trendingBadge}
                <header class="ccm-card-header">
                    ${card.card_image ? `<img src="${card.card_image}" alt="${card.title}" class="ccm-card-image" loading="lazy" width="120" height="auto">` : ''}
                    <h3 class="ccm-card-title">${card.title}</h3>
                    ${card.bank ? `<div class="ccm-card-bank">${card.bank.name}</div>` : ''}
                    ${card.rating > 0 ? `
                        <div class="ccm-card-rating">
                            <div class="ccm-rating-stars">${ratingStars}</div>
                            <span class="ccm-rating-text">${ratingText}</span>
                        </div>
                    ` : ''}
                </header>
                <div class="ccm-card-content">
                    <div class="ccm-card-highlights">
                        <div class="ccm-highlight">
                            <span class="ccm-highlight-label">Annual Fee</span>
                            <span class="ccm-highlight-value">${card.annual_fee || 'N/A'}</span>
                        </div>
                        <div class="ccm-highlight">
                            <span class="ccm-highlight-label">Rewards</span>
                            <span class="ccm-highlight-value">${card.cashback_rate || 'N/A'}</span>
                        </div>
                    </div>
                    ${pros ? `<div class="ccm-card-features">${pros}</div>` : ''}
                    <div class="ccm-card-actions">
                        <a href="${card.link}" class="ccm-btn ccm-btn-secondary">Details</a>
                        <a href="${card.apply_link || card.link}" class="ccm-btn ccm-btn-primary" target="_blank" rel="noopener">Apply Now</a>
                    </div>
                    <label class="ccm-compare-label">
                        <input type="checkbox" class="ccm-compare-checkbox" data-id="${card.id}">
                        <span>Compare this card</span>
                    </label>
                </div>
            </article>
        `;
    }

    // Form submission loading state
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('.ccm-filters-form');
        if (filterForm) {
            filterForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('.ccm-btn-primary');
                if (submitBtn) {
                    submitBtn.innerHTML = '🔄 Applying...';
                    submitBtn.disabled = true;
                }
            });
        }
    });
})();
</script>

<?php get_footer(); ?>
