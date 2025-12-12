<?php
/**
 * The template for displaying Credit Card archive pages - Optimized Version
 * Performance-focused design with lowest LCP and clean CSS architecture
 *
 * @package Credit Card Manager
 */

get_header();

// Get filter parameters from URL (handle arrays for multiple selections)
$current_bank = isset($_GET['bank']) ? (array) $_GET['bank'] : [];
$current_network = isset($_GET['network_type']) ? (array) $_GET['network_type'] : [];
$current_category = isset($_GET['category']) ? (array) $_GET['category'] : [];
$current_min_rating = isset($_GET['min_rating']) ? sanitize_text_field($_GET['min_rating']) : '';
$current_max_fee = isset($_GET['max_annual_fee']) ? sanitize_text_field($_GET['max_annual_fee']) : '';
$current_featured = isset($_GET['featured']) ? sanitize_text_field($_GET['featured']) : '';
$current_trending = isset($_GET['trending']) ? sanitize_text_field($_GET['trending']) : '';
$current_sort = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'date';
$current_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'desc';

// Sanitize array values
$current_bank = array_map('sanitize_text_field', $current_bank);
$current_network = array_map('sanitize_text_field', $current_network);
$current_category = array_map('sanitize_text_field', $current_category);

// Get filter data from cached function instead of API call for better performance
$filters = ccm_get_filters_data();

// Build query args for credit cards with sorting support
$args = [
    'post_type' => 'credit-card',
    'post_status' => 'publish',
    'posts_per_page' => 8, // Show 8 initially, load more will add 8 each time
    'paged' => 1, // Always start with page 1 for AJAX loading
];

// Handle sorting
switch ($current_sort) {
    case 'rating':
        $args['meta_key'] = 'rating';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = strtoupper($current_order);
        break;
    case 'annual_fee':
        $args['meta_key'] = 'annual_fee';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = strtoupper($current_order);
        break;
    case 'review_count':
        $args['meta_key'] = 'review_count';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = strtoupper($current_order);
        break;
    case 'date':
    default:
        $args['orderby'] = 'date';
        $args['order'] = strtoupper($current_order);
        break;
}

// Handle filtering
$tax_query = [];
$meta_query = [];

// Bank filter (store taxonomy)
if (!empty($current_bank)) {
    $tax_query[] = [
        'taxonomy' => 'store',
        'field'    => 'slug',
        'terms'    => $current_bank,
        'operator' => 'IN',
    ];
}

// Category filter (card-category taxonomy)
if (!empty($current_category)) {
    $tax_query[] = [
        'taxonomy' => 'card-category',
        'field'    => 'slug',
        'terms'    => $current_category,
        'operator' => 'IN',
    ];
}

// Network type filter (network-type taxonomy)
if (!empty($current_network)) {
    $tax_query[] = [
        'taxonomy' => 'network-type',
        'field'    => 'slug',
        'terms'    => $current_network,
        'operator' => 'IN',
    ];
}

// Minimum rating filter
if (!empty($current_min_rating) && is_numeric($current_min_rating)) {
    $meta_query[] = [
        'key'     => 'rating',
        'value'   => $current_min_rating,
        'compare' => '>=',
        'type'    => 'NUMERIC',
    ];
}

// Maximum annual fee filter
if (!empty($current_max_fee) && is_numeric($current_max_fee)) {
    $meta_query[] = [
        'key'     => 'annual_fee',
        'value'   => $current_max_fee,
        'compare' => '<=',
        'type'    => 'NUMERIC',
    ];
}

// Featured filter
if (!empty($current_featured)) {
    $meta_query[] = [
        'key'     => 'featured',
        'value'   => '1',
        'compare' => '=',
    ];
}

// Trending filter
if (!empty($current_trending)) {
    $meta_query[] = [
        'key'     => 'trending',
        'value'   => '1',
        'compare' => '=',
    ];
}

// Add tax_query and meta_query to args if they have filters
if (!empty($tax_query)) {
    $args['tax_query'] = $tax_query;
    // If multiple tax queries, set relation to AND
    if (count($tax_query) > 1) {
        $args['tax_query']['relation'] = 'AND';
    }
}

if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
    // If multiple meta queries, set relation to AND
    if (count($meta_query) > 1) {
        $args['meta_query']['relation'] = 'AND';
    }
}

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
if (!empty($current_bank)) {
    // Use first bank for SEO title when multiple banks are selected
    $first_bank = is_array($current_bank) ? $current_bank[0] : $current_bank;
    $bank_term = get_term_by('slug', $first_bank, 'store');
    $bank_display_name = $bank_term ? $bank_term->name : $first_bank;
    $archive_title = $bank_display_name . ' Credit Cards - Compare & Apply Online';
    $archive_description = "Compare the best " . $bank_display_name . " credit cards in India. Find cashback, rewards, and travel cards with detailed reviews and instant applications.";
}

if (!empty($current_category)) {
    // Use first category for SEO title when multiple categories are selected
    $first_category = is_array($current_category) ? $current_category[0] : $current_category;
    $category_term = get_term_by('slug', $first_category, 'card-category');
    $category_display_name = $category_term ? $category_term->name : $first_category;
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
    <!-- Simplified Hero Section with Breadcrumb -->
  

    <!-- CTA Section -->
    <section class="ccm-top-cta">
        <div class="ccm-container">
            <div class="ccm-cta-content">
                <h1 class="ccm-cta-main-title">Best Credit Cards in India - Compare & Apply Online On Bigtricks</h1>
                <p>Credit cards come with varied features and benefits tailored to different lifestyles. The key is to choose the one that aligns with your spending preferences. Paisabazaar makes it simple by bringing 70+ cards in one place for you to compare, check eligibility, apply through a completely digital process, and get instant approval.</p>

                <div class="ccm-cta-features">
                    <div class="ccm-cta-feature">
                        <div class="ccm-cta-feature-icon">
                            <span class="rbi rbi-chart-o"></span>
                        </div>
                        <h4>Compare India's best credit cards</h4>
                        <p>See cards from top Banks & issuers</p>
                    </div>
                    <div class="ccm-cta-feature">
                        <div class="ccm-cta-feature-icon">
                            <span class="rbi rbi-user"></span>
                        </div>
                        <h4>Choose card matching your lifestyle</h4>
                        <p>Wide choice of 70+ credit cards</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Area with Sidebar and Cards -->
    <div class="ccm-main-wrapper">
        <div class="ccm-container">
            <div class="ccm-layout-grid">
                <!-- Left Sidebar - Filters (Desktop) -->
                <aside class="ccm-sidebar-filters">
                    <div class="ccm-sidebar-sticky">
                        <div class="ccm-filters-header">
                            <h2 class="ccm-filters-title">Filters</h2>
                            <button type="button" class="ccm-clear-all" id="clear-all-filters">Clear All</button>
                        </div>

                        <form class="ccm-filters-form" method="get" action="<?php echo get_post_type_archive_link('credit-card'); ?>" id="sidebar-filter-form">
                            <?php if (!empty($filters['banks'])): ?>
                            <div class="ccm-filter-group">
                                <h3 class="ccm-filter-label">BANKS</h3>
                                <div class="ccm-filter-options">
                                    <?php
                                    $bank_count = 0;
                                    foreach ($filters['banks'] as $bank):
                                        $is_hidden = $bank_count >= 5 ? 'style="display: none;"' : '';
                                        $bank_count++;
                                    ?>
                                    <label class="ccm-checkbox-label" <?php echo $is_hidden; ?>>
                                        <input type="checkbox" name="bank[]" value="<?php echo esc_attr($bank['slug']); ?>" <?php checked(in_array($bank['slug'], (array)$current_bank)); ?>>
                                        <span class="ccm-checkbox-text"><?php echo esc_html($bank['name']); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                    <?php if (count($filters['banks']) > 5): ?>
                                    <button type="button" class="ccm-show-more" data-target="banks">+ Show More</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($filters['categories'])): ?>
                            <div class="ccm-filter-group">
                                <h3 class="ccm-filter-label">CATEGORIES</h3>
                                <div class="ccm-filter-options">
                                    <?php foreach ($filters['categories'] as $category): ?>
                                    <label class="ccm-checkbox-label">
                                        <input type="checkbox" name="category[]" value="<?php echo esc_attr($category['slug']); ?>" <?php checked(in_array($category['slug'], (array)$current_category)); ?>>
                                        <span class="ccm-checkbox-text"><?php echo esc_html($category['name']); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($filters['network_types'])): ?>
                            <div class="ccm-filter-group">
                                <h3 class="ccm-filter-label">NETWORK TYPE</h3>
                                <div class="ccm-filter-options">
                                    <?php foreach ($filters['network_types'] as $network): ?>
                                    <label class="ccm-checkbox-label">
                                        <input type="checkbox" name="network_type[]" value="<?php echo esc_attr($network['slug']); ?>" <?php checked(in_array($network['slug'], (array)$current_network)); ?>>
                                        <span class="ccm-checkbox-text"><?php echo esc_html($network['name']); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <button type="submit" class="ccm-btn ccm-btn-primary ccm-btn-block">Apply Filters</button>
                        </form>
                    </div>
                </aside>

                <!-- Right Content Area - Cards -->
                <main class="ccm-main-content">

                    <!-- Results Bar -->
                    <div class="ccm-results-bar">
                        <div class="ccm-results-count">
                            <strong><?php echo number_format($credit_cards->found_posts); ?></strong> credit cards found
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
                        <!-- Cards List -->
                        <div class="ccm-cards-list">
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
                                $categories = get_the_terms($post_id, 'card-category');
                            ?>
                            <article class="ccm-card-horizontal" data-id="<?php echo esc_attr($post_id); ?>">
                                <div class="ccm-card-image-wrapper">
                                    <?php if (!empty($card_image)): ?>
                                        <img src="<?php echo esc_url($card_image); ?>"
                                             alt="<?php the_title(); ?>"
                                             class="ccm-card-img"
                                             loading="lazy">
                                    <?php endif; ?>
                                </div>

                                <div class="ccm-card-body">
                                    <div class="ccm-card-header-inline">
                                        <div>
                                            <h3 class="ccm-card-title-inline"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
                                            <?php if ($rating > 0): ?>
                                                <div class="ccm-card-rating-inline">
                                                    <span class="ccm-rating-value"><?php echo esc_html($rating); ?>/5</span>
                                                    <span class="ccm-rating-stars-inline"><?php echo str_repeat('⭐', floor($rating)); ?></span>
                                                    <?php if ($review_count > 0): ?>
                                                        <span class="ccm-review-count">(<?php echo esc_html($review_count); ?> reviews)</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ccm-card-badges-inline">
                                            <?php if (!is_wp_error($categories) && !empty($categories)): ?>
                                                <?php foreach (array_slice($categories, 0, 3) as $cat): ?>
                                                    <span class="ccm-category-badge"><?php echo esc_html($cat->name); ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($pros) && is_array($pros)): ?>
                                        <ul class="ccm-card-features-list">
                                            <?php foreach (array_slice($pros, 0, 4) as $pro): ?>
                                                <li>
                                                    <span class="ccm-feature-icon-inline">✓</span>
                                                    <?php echo esc_html($pro); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <div class="ccm-card-meta-inline">
                                        <div class="ccm-meta-item">
                                            <span class="ccm-meta-label">Joining Fee:</span>
                                            <span class="ccm-meta-value"><?php echo esc_html($annual_fee); ?></span>
                                        </div>
                                        <div class="ccm-meta-item">
                                            <span class="ccm-meta-label">Annual/Renewal Fee:</span>
                                            <span class="ccm-meta-value"><?php echo esc_html($annual_fee); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="ccm-card-actions-inline">
                                    <a href="<?php the_permalink(); ?>" class="ccm-btn ccm-btn-outline">Read More</a>
                                    <a href="<?php echo esc_url($apply_link); ?>" class="ccm-btn ccm-btn-primary" target="_blank" rel="noopener">Check Eligibility ></a>
                                </div>

                                <label class="ccm-compare-label">
                                    <input type="checkbox" class="ccm-compare-checkbox" data-id="<?php echo esc_attr($post_id); ?>">
                                    <span>Compare</span>
                                </label>
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
                </main>
            </div>
        </div>
    </div>



    <!-- Mobile Filter Modal -->
    <div class="ccm-filter-modal" id="filter-modal">
        <div class="ccm-filter-modal-overlay" id="filter-modal-overlay"></div>
        <div class="ccm-filter-modal-content">
            <div class="ccm-filter-modal-header">
                <h2>Filters</h2>
                <button type="button" class="ccm-modal-close" id="close-filter-modal">✕</button>
            </div>
            <div class="ccm-filter-modal-body">
                <form class="ccm-filters-form" method="get" action="<?php echo get_post_type_archive_link('credit-card'); ?>" id="mobile-filter-form">
                    <?php if (!empty($filters['banks'])): ?>
                    <div class="ccm-filter-group">
                        <h3 class="ccm-filter-label">BANKS</h3>
                        <div class="ccm-filter-options">
                            <?php foreach ($filters['banks'] as $bank): ?>
                            <label class="ccm-checkbox-label">
                                <input type="checkbox" name="bank[]" value="<?php echo esc_attr($bank['slug']); ?>" <?php checked(in_array($bank['slug'], (array)$current_bank)); ?>>
                                <span class="ccm-checkbox-text"><?php echo esc_html($bank['name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($filters['categories'])): ?>
                    <div class="ccm-filter-group">
                        <h3 class="ccm-filter-label">CATEGORIES</h3>
                        <div class="ccm-filter-options">
                            <?php foreach ($filters['categories'] as $category): ?>
                            <label class="ccm-checkbox-label">
                                <input type="checkbox" name="category[]" value="<?php echo esc_attr($category['slug']); ?>" <?php checked(in_array($category['slug'], (array)$current_category)); ?>>
                                <span class="ccm-checkbox-text"><?php echo esc_html($category['name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($filters['network_types'])): ?>
                    <div class="ccm-filter-group">
                        <h3 class="ccm-filter-label">NETWORK TYPE</h3>
                        <div class="ccm-filter-options">
                            <?php foreach ($filters['network_types'] as $network): ?>
                            <label class="ccm-checkbox-label">
                                <input type="checkbox" name="network_type[]" value="<?php echo esc_attr($network['slug']); ?>" <?php checked(in_array($network['slug'], (array)$current_network)); ?>>
                                <span class="ccm-checkbox-text"><?php echo esc_html($network['name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="ccm-filter-modal-footer">
                <button type="button" class="ccm-btn ccm-btn-secondary" id="modal-clear-filters">Clear All</button>
                <button type="submit" form="mobile-filter-form" class="ccm-btn ccm-btn-primary">Apply Filters</button>
            </div>
        </div>
    </div>

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
                // Fetch random 6 posts from credit-card-bill-payment-offers category
                $blog_args = [
                    'post_type' => 'post',
                    'posts_per_page' => 6,
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

    <!-- Mobile Filter Button -->
    <button class="ccm-mobile-filter-btn" id="mobile-filter-btn">
        <svg class="ccm-filter-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V21C3 22.11 3.89 23 5 23H19C20.11 23 21 22.11 21 21V9M19 9H14V4H5V21H19V9Z" fill="currentColor"/>
        </svg>
        <span>Filters</span>
        <span class="ccm-filter-count" id="mobile-filter-count" style="display: none;">0</span>
    </button>

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
    // Mobile Filter Modal functionality
    const mobileFilterBtn = document.getElementById('mobile-filter-btn');
    const filterModal = document.getElementById('filter-modal');
    const filterModalOverlay = document.getElementById('filter-modal-overlay');
    const closeFilterModal = document.getElementById('close-filter-modal');
    const mobileFilterCount = document.getElementById('mobile-filter-count');

    if (mobileFilterBtn && filterModal) {
        // Open modal
        mobileFilterBtn.addEventListener('click', function() {
            filterModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close modal
        function closeModal() {
            filterModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (closeFilterModal) {
            closeFilterModal.addEventListener('click', closeModal);
        }

        if (filterModalOverlay) {
            filterModalOverlay.addEventListener('click', closeModal);
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && filterModal.classList.contains('active')) {
                closeModal();
            }
        });
    }

    // Clear all filters functionality
    const clearAllButtons = document.querySelectorAll('#clear-all-filters, #modal-clear-filters');
    clearAllButtons.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                // Uncheck all checkboxes
                document.querySelectorAll('.ccm-filters-form input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                // Update filter count
                updateFilterCount();
            });
        }
    });

    // Update filter count badge - count unique applied filters
    function updateFilterCount() {
        // Count unique applied filters by checking URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        let appliedFilters = 0;

        // Count bank filters
        const bankFilters = urlParams.getAll('bank[]');
        if (bankFilters.length > 0) appliedFilters += bankFilters.length;

        // Count category filters
        const categoryFilters = urlParams.getAll('category[]');
        if (categoryFilters.length > 0) appliedFilters += categoryFilters.length;

        // Count network filters
        const networkFilters = urlParams.getAll('network_type[]');
        if (networkFilters.length > 0) appliedFilters += networkFilters.length;

        // Count other filters
        if (urlParams.get('min_rating')) appliedFilters++;
        if (urlParams.get('max_annual_fee')) appliedFilters++;
        if (urlParams.get('featured')) appliedFilters++;
        if (urlParams.get('trending')) appliedFilters++;

        if (mobileFilterCount) {
            if (appliedFilters > 0) {
                mobileFilterCount.textContent = appliedFilters;
                mobileFilterCount.style.display = 'inline-block';
            } else {
                mobileFilterCount.style.display = 'none';
            }
        }
    }

    // Listen for filter changes
    document.querySelectorAll('.ccm-filters-form input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateFilterCount);
    });

    // Initialize filter count on page load
    updateFilterCount();

    // Show more functionality for filter groups
    document.querySelectorAll('.ccm-show-more').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const filterGroup = this.closest('.ccm-filter-group');
            const allOptions = filterGroup.querySelectorAll('.ccm-filter-options label');

            // Show all options
            allOptions.forEach(option => {
                option.style.display = 'block';
            });

            // Hide the show more button
            this.style.display = 'none';
        });
    });

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
    const cardsList = document.querySelector('.ccm-cards-list');

    if (loadMoreBtn && cardsList) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.getAttribute('data-page'));
            const isLoading = this.getAttribute('data-loading') === 'true';

            if (isLoading) return;

            // Set loading state
            this.setAttribute('data-loading', 'true');
            this.innerHTML = 'Loading... <span class="ccm-loading-spinner">⟳</span>';
            this.disabled = true;

            // Get current URL parameters (includes sort and filter parameters)
            const urlParams = new URLSearchParams(window.location.search);
            const sortBy = urlParams.get('sort_by') || 'date';
            const sortOrder = urlParams.get('sort_order') || 'desc';

            // Build query string with all current parameters
            const queryParams = new URLSearchParams();
            queryParams.set('page', currentPage);
            queryParams.set('per_page', '8');
            queryParams.set('sort_by', sortBy);
            queryParams.set('sort_order', sortOrder);

            // Add filter parameters if they exist
            urlParams.forEach((value, key) => {
                if (key !== 'page' && key !== 'per_page' && key !== 'sort_by' && key !== 'sort_order') {
                    // Handle array parameters (multiple values)
                    const values = urlParams.getAll(key);
                    values.forEach(val => queryParams.append(key, val));
                }
            });

            // Make AJAX request
            fetch(`${window.location.origin}/wp-json/ccm/v1/credit-cards?${queryParams.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.data && data.data.length > 0) {
                        // Append new cards to list
                        data.data.forEach(card => {
                            const cardHTML = createCardHTML(card);
                            cardsList.insertAdjacentHTML('beforeend', cardHTML);
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
        const ratingStars = card.rating > 0 ? '⭐'.repeat(Math.floor(card.rating)) : '';
        const ratingText = card.rating > 0 ? `${card.rating}/5${card.review_count > 0 ? ` (${card.review_count} reviews)` : ''}` : '';

        const pros = card.pros && card.pros.length > 0 ?
            card.pros.slice(0, 4).map(pro => `<li><span class="ccm-feature-icon-inline">✓</span>${pro}</li>`).join('') : '';

        return `
            <article class="ccm-card-horizontal" data-id="${card.id}">
                <div class="ccm-card-image-wrapper">
                    ${card.card_image ? `<img src="${card.card_image}" alt="${card.title}" class="ccm-card-img" loading="lazy">` : ''}
                </div>

                <div class="ccm-card-body">
                    <div class="ccm-card-header-inline">
                        <div>
                            <h3 class="ccm-card-title-inline">${card.title}</h3>
                            ${card.rating > 0 ? `
                                <div class="ccm-card-rating-inline">
                                    <span class="ccm-rating-value">${card.rating}/5</span>
                                    <span class="ccm-rating-stars-inline">${ratingStars}</span>
                                    ${card.review_count > 0 ? `<span class="ccm-review-count">(${card.review_count} reviews)</span>` : ''}
                                </div>
                            ` : ''}
                        </div>
                        <div class="ccm-card-badges-inline">
                            ${card.featured ? '<span class="ccm-category-badge">Featured</span>' : ''}
                            ${card.trending ? '<span class="ccm-category-badge">Trending</span>' : ''}
                        </div>
                    </div>

                    ${pros ? `<ul class="ccm-card-features-list">${pros}</ul>` : ''}

                    <div class="ccm-card-meta-inline">
                        <div class="ccm-meta-item">
                            <span class="ccm-meta-label">Joining Fee:</span>
                            <span class="ccm-meta-value">${card.annual_fee || 'N/A'}</span>
                        </div>
                        <div class="ccm-meta-item">
                            <span class="ccm-meta-label">Annual/Renewal Fee:</span>
                            <span class="ccm-meta-value">${card.annual_fee || 'N/A'}</span>
                        </div>
                    </div>
                </div>

                <div class="ccm-card-actions-inline">
                    <a href="${card.link}" class="ccm-btn ccm-btn-outline">Read More</a>
                    <a href="${card.apply_link || card.link}" class="ccm-btn ccm-btn-primary" target="_blank" rel="noopener">Check Eligibility ></a>
                </div>

                <label class="ccm-compare-label">
                    <input type="checkbox" class="ccm-compare-checkbox" data-id="${card.id}">
                    <span>Compare</span>
                </label>
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
