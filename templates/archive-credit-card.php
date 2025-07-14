<?php
/**
 * Template for displaying Credit Card Archive
 *
 * @package Credit Card Manager
 */

get_header();

// Get filter parameters from URL
$bank_filter = isset($_GET['bank']) ? sanitize_text_field($_GET['bank']) : '';
$network_filter = isset($_GET['network_type']) ? sanitize_text_field($_GET['network_type']) : '';
$category_filter = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$min_rating = isset($_GET['min_rating']) ? floatval($_GET['min_rating']) : '';
$max_annual_fee = isset($_GET['max_annual_fee']) ? intval($_GET['max_annual_fee']) : '';
$featured_filter = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : '';
$trending_filter = isset($_GET['trending']) ? filter_var($_GET['trending'], FILTER_VALIDATE_BOOLEAN) : '';
$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'rating';
$sort_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'desc';

// Set up the query arguments
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type' => 'credit-card',
    'post_status' => 'publish',
    'posts_per_page' => 12,
    'paged' => $paged,
    'meta_query' => array(),
    'tax_query' => array(),
);

// Add taxonomy filters
if (!empty($bank_filter)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'store',
        'field' => 'slug',
        'terms' => explode(',', $bank_filter),
    );
}

if (!empty($network_filter)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'network-type',
        'field' => 'slug',
        'terms' => explode(',', $network_filter),
    );
}

if (!empty($category_filter)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'card-category',
        'field' => 'slug',
        'terms' => explode(',', $category_filter),
    );
}

// Add meta filters
if (!empty($min_rating)) {
    $args['meta_query'][] = array(
        'key' => 'rating',
        'value' => $min_rating,
        'compare' => '>=',
        'type' => 'DECIMAL',
    );
}

if (!empty($max_annual_fee)) {
    $args['meta_query'][] = array(
        'key' => 'annual_fee_numeric',
        'value' => $max_annual_fee,
        'compare' => '<=',
        'type' => 'NUMERIC',
    );
}

if ($featured_filter !== '') {
    $args['meta_query'][] = array(
        'key' => 'featured',
        'value' => $featured_filter ? '1' : '0',
        'compare' => '=',
    );
}

if ($trending_filter !== '') {
    $args['meta_query'][] = array(
        'key' => 'trending',
        'value' => $trending_filter ? '1' : '0',
        'compare' => '=',
    );
}

// Set up sorting
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

// Execute the query
$credit_cards_query = new WP_Query($args);

// Get filter data for dropdowns
$banks = get_terms(array(
    'taxonomy' => 'store',
    'hide_empty' => true,
));

$network_types = get_terms(array(
    'taxonomy' => 'network-type',
    'hide_empty' => true,
));

$categories = get_terms(array(
    'taxonomy' => 'card-category',
    'hide_empty' => true,
));

// Rating ranges
$rating_ranges = array(
    array('label' => '4+ Stars', 'value' => '4', 'min' => 4),
    array('label' => '3+ Stars', 'value' => '3', 'min' => 3),
    array('label' => '2+ Stars', 'value' => '2', 'min' => 2),
    array('label' => '1+ Stars', 'value' => '1', 'min' => 1),
);

// Fee ranges
$fee_ranges = array(
    array('label' => 'Free', 'value' => '0', 'max' => 0),
    array('label' => 'Under ₹1,000', 'value' => '1000', 'max' => 1000),
    array('label' => 'Under ₹2,500', 'value' => '2500', 'max' => 2500),
    array('label' => 'Under ₹5,000', 'value' => '5000', 'max' => 5000),
);
?>

<div class="ccm-container ccm-archive-container">
    <!-- Hero Section -->
    <div class="ccm-hero-section">
        <div class="ccm-hero-content">
            <h1 class="ccm-hero-title">Find The Right Credit Card</h1>
            <p class="ccm-hero-description">
                Explore the leading credit card categories in India to begin your credit journey. 
                Discover top-rated cards and even lifetime free options to get started.
            </p>
        </div>
        <div class="ccm-hero-image">
            <img src="<?php echo plugin_dir_url(dirname(__FILE__)); ?>assets/images/credit-cards.png" alt="Credit Cards">
        </div>
    </div>

    <!-- Category Icons Section -->
    <div class="ccm-category-section">
        <h2 class="ccm-section-title">Top Credit Card Categories</h2>
        <div class="ccm-category-icons">
            <?php
            // Get card categories
            $categories = get_terms(array(
                'taxonomy' => 'card-category',
                'hide_empty' => true,
                'number' => 6, // Limit to 6 categories
            ));

            if (!is_wp_error($categories) && !empty($categories)) :
                foreach ($categories as $category) :
                    $icon = get_term_meta($category->term_id, 'category_icon', true);
                    $category_link = get_term_link($category);
            ?>
                <a href="<?php echo esc_url($category_link); ?>" class="ccm-category-icon-item">
                    <div class="ccm-category-icon">
                        <?php if (!empty($icon)) : ?>
                            <?php echo $icon; ?>
                        <?php else : ?>
                            <span class="dashicons dashicons-credit-card"></span>
                        <?php endif; ?>
                    </div>
                    <div class="ccm-category-name"><?php echo esc_html($category->name); ?></div>
                </a>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>

    <!-- Banks Section -->
    <div class="ccm-banks-section">
        <h2 class="ccm-section-title">Credit Card Issuers</h2>
        <div class="ccm-banks-grid">
            <?php
            // Get banks (store taxonomy where type is bank)
            $banks = get_terms(array(
                'taxonomy' => 'store',
                'hide_empty' => true,
            ));

            if (!is_wp_error($banks) && !empty($banks)) :
                foreach ($banks as $bank) :
                    $bank_link = get_term_link($bank);
                    // Count cards for this bank
                    $card_count = $bank->count;
            ?>
                <a href="<?php echo esc_url($bank_link); ?>" class="ccm-bank-item">
                    <div class="ccm-bank-name"><?php echo esc_html($bank->name); ?></div>
                    <div class="ccm-bank-count"><?php echo esc_html($card_count); ?> cards</div>
                </a>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>

    <div class="ccm-archive-header">
        <h1 class="ccm-archive-title"><?php echo get_the_archive_title(); ?></h1>
        <div class="ccm-archive-description">
            <?php echo get_the_archive_description(); ?>
        </div>
    </div>

    <div class="ccm-archive-content">
        <!-- Filters Section -->
        <div class="ccm-filters-section">
            <div class="ccm-filters-header">
                <h2>Filter Credit Cards</h2>
                <button type="button" class="ccm-toggle-filters" id="ccm-toggle-filters">
                    <span class="dashicons dashicons-filter"></span> Toggle Filters
                </button>
            </div>
            <div class="ccm-filters-container" id="ccm-filters-container">
                <form id="ccm-filter-form" class="ccm-filter-form" method="get">
                    <div class="ccm-filter-row">
                        <!-- Bank Filter -->
                        <div class="ccm-filter-group">
                            <label for="ccm-bank-filter">Bank</label>
                            <select id="ccm-bank-filter" name="bank" class="ccm-filter-select">
                                <option value="">All Banks</option>
                                <?php if (!is_wp_error($banks) && !empty($banks)) : ?>
                                    <?php foreach ($banks as $bank) : ?>
                                        <option value="<?php echo esc_attr($bank->slug); ?>" <?php selected($bank_filter, $bank->slug); ?>>
                                            <?php echo esc_html($bank->name); ?> (<?php echo esc_html($bank->count); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Network Type Filter -->
                        <div class="ccm-filter-group">
                            <label for="ccm-network-filter">Network Type</label>
                            <select id="ccm-network-filter" name="network_type" class="ccm-filter-select">
                                <option value="">All Networks</option>
                                <?php if (!is_wp_error($network_types) && !empty($network_types)) : ?>
                                    <?php foreach ($network_types as $network) : ?>
                                        <option value="<?php echo esc_attr($network->slug); ?>" <?php selected($network_filter, $network->slug); ?>>
                                            <?php echo esc_html($network->name); ?> (<?php echo esc_html($network->count); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div class="ccm-filter-group">
                            <label for="ccm-category-filter">Category</label>
                            <select id="ccm-category-filter" name="category" class="ccm-filter-select">
                                <option value="">All Categories</option>
                                <?php if (!is_wp_error($categories) && !empty($categories)) : ?>
                                    <?php foreach ($categories as $category) : ?>
                                        <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($category_filter, $category->slug); ?>>
                                            <?php echo esc_html($category->name); ?> (<?php echo esc_html($category->count); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="ccm-filter-row">
                        <!-- Rating Filter -->
                        <div class="ccm-filter-group">
                            <label for="ccm-rating-filter">Minimum Rating</label>
                            <select id="ccm-rating-filter" name="min_rating" class="ccm-filter-select">
                                <option value="">Any Rating</option>
                                <?php foreach ($rating_ranges as $range) : ?>
                                    <option value="<?php echo esc_attr($range['min']); ?>" <?php selected($min_rating, $range['min']); ?>>
                                        <?php echo esc_html($range['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Annual Fee Filter -->
                        <div class="ccm-filter-group">
                            <label for="ccm-fee-filter">Maximum Annual Fee</label>
                            <select id="ccm-fee-filter" name="max_annual_fee" class="ccm-filter-select">
                                <option value="">Any Fee</option>
                                <?php foreach ($fee_ranges as $range) : ?>
                                    <option value="<?php echo esc_attr($range['max']); ?>" <?php selected($max_annual_fee, $range['max']); ?>>
                                        <?php echo esc_html($range['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sort By Filter -->
                        <div class="ccm-filter-group">
                            <label for="ccm-sort-filter">Sort By</label>
                            <select id="ccm-sort-filter" name="sort_by" class="ccm-filter-select">
                                <option value="rating" <?php selected($sort_by, 'rating'); ?>>Rating</option>
                                <option value="annual_fee" <?php selected($sort_by, 'annual_fee'); ?>>Annual Fee</option>
                                <option value="review_count" <?php selected($sort_by, 'review_count'); ?>>Popularity</option>
                                <option value="date" <?php selected($sort_by, 'date'); ?>>Newest</option>
                            </select>
                        </div>
                    </div>

                    <div class="ccm-filter-actions">
                        <div class="ccm-filter-checkboxes">
                            <div class="ccm-checkbox-wrapper">
                                <input type="checkbox" id="ccm-featured-filter" name="featured" value="1" <?php checked($featured_filter, true); ?>>
                                <label for="ccm-featured-filter">Featured Cards Only</label>
                            </div>
                            <div class="ccm-checkbox-wrapper">
                                <input type="checkbox" id="ccm-trending-filter" name="trending" value="1" <?php checked($trending_filter, true); ?>>
                                <label for="ccm-trending-filter">Trending Cards Only</label>
                            </div>
                        </div>
                        <div class="ccm-filter-buttons">
                            <button type="submit" class="ccm-filter-submit">
                                <span class="dashicons dashicons-search"></span> Apply Filters
                            </button>
                            <button type="button" id="ccm-filter-reset" class="ccm-filter-reset">
                                <span class="dashicons dashicons-dismiss"></span> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Compare Section (Initially Hidden) -->
        <div class="ccm-compare-section" id="ccm-compare-section" style="display: none;">
            <div class="ccm-compare-header">
                <h3>Compare Credit Cards</h3>
                <div class="ccm-compare-actions">
                    <span class="ccm-compare-count" id="ccm-compare-count">0 cards selected</span>
                    <button type="button" class="ccm-compare-clear" id="ccm-compare-clear">
                        <span class="dashicons dashicons-trash"></span> Clear All
                    </button>
                    <button type="button" class="ccm-compare-button" id="ccm-compare-button" disabled>
                        <span class="dashicons dashicons-visibility"></span> Compare Cards
                    </button>
                </div>
            </div>
            <div class="ccm-compare-cards" id="ccm-compare-cards"></div>
        </div>

        <!-- Cards Section -->
        <div class="ccm-cards-section">
            <div class="ccm-cards-header">
                <h2>Credit Cards</h2>
                <span class="ccm-cards-count">
                    <span id="ccm-total-cards"><?php echo esc_html($credit_cards_query->found_posts); ?></span> cards found
                </span>
            </div>

            <div class="ccm-cards-grid" id="ccm-cards-grid">
                <?php if ($credit_cards_query->have_posts()) : ?>
                    <?php while ($credit_cards_query->have_posts()) : $credit_cards_query->the_post(); ?>
                        <?php
                        // Get card data
                        $card_id = get_the_ID();
                        $card_title = get_the_title();
                        $card_link = get_permalink();
                        $card_excerpt = get_the_excerpt();
                        
                        // Get meta data
                        $card_image = get_post_meta($card_id, 'card_image_url', true);
                        if (empty($card_image) && has_post_thumbnail()) {
                            $card_image = get_the_post_thumbnail_url($card_id, 'medium');
                        }
                        
                        $rating = get_post_meta($card_id, 'rating', true);
                        $rating_percent = $rating ? ($rating / 5) * 100 : 0;
                        $review_count = get_post_meta($card_id, 'review_count', true);
                        $annual_fee = get_post_meta($card_id, 'annual_fee', true);
                        $cashback_rate = get_post_meta($card_id, 'cashback_rate', true);
                        $welcome_bonus = get_post_meta($card_id, 'welcome_bonus', true);
                        $apply_link = get_post_meta($card_id, 'apply_link', true);
                        $featured = get_post_meta($card_id, 'featured', true);
                        $trending = get_post_meta($card_id, 'trending', true);
                        
                        // Get bank (store taxonomy)
                        $bank_terms = get_the_terms($card_id, 'store');
                        $bank_name = '';
                        if (!is_wp_error($bank_terms) && !empty($bank_terms)) {
                            $bank_name = $bank_terms[0]->name;
                        }
                        ?>
                        <div class="ccm-card-item">
                            <div class="ccm-card-inner">
                                <div class="ccm-card-compare">
                                    <label class="ccm-compare-checkbox">
                                        <input type="checkbox" class="ccm-compare-input" 
                                               data-id="<?php echo esc_attr($card_id); ?>" 
                                               data-title="<?php echo esc_attr($card_title); ?>" 
                                               data-image="<?php echo esc_url($card_image); ?>">
                                        Add to Compare
                                    </label>
                                </div>
                                
                                <div class="ccm-card-image">
                                    <img src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr($card_title); ?>">
                                    <?php if ($featured) : ?>
                                        <span class="ccm-badge ccm-featured">Featured</span>
                                    <?php endif; ?>
                                    <?php if ($trending) : ?>
                                        <span class="ccm-badge ccm-trending">Trending</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="ccm-card-content">
                                    <h3 class="ccm-card-title">
                                        <a href="<?php echo esc_url($card_link); ?>"><?php echo esc_html($card_title); ?></a>
                                    </h3>
                                    
                                    <?php if (!empty($bank_name)) : ?>
                                        <div class="ccm-card-bank"><?php echo esc_html($bank_name); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="ccm-card-meta">
                                        <?php if (!empty($rating)) : ?>
                                            <div class="ccm-rating">
                                                <div class="ccm-stars">
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                    <div class="ccm-stars-filled" style="width: <?php echo esc_attr($rating_percent); ?>%;">
                                                        <span class="dashicons dashicons-star-filled"></span>
                                                        <span class="dashicons dashicons-star-filled"></span>
                                                        <span class="dashicons dashicons-star-filled"></span>
                                                        <span class="dashicons dashicons-star-filled"></span>
                                                        <span class="dashicons dashicons-star-filled"></span>
                                                    </div>
                                                </div>
                                                <span class="ccm-rating-number"><?php echo esc_html($rating); ?>/5</span>
                                                <?php if (!empty($review_count)) : ?>
                                                    <span class="ccm-review-count">(<?php echo esc_html($review_count); ?> reviews)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="ccm-card-details">
                                        <?php if (!empty($annual_fee)) : ?>
                                            <div class="ccm-detail-item">
                                                <span class="ccm-detail-label">Annual Fee</span>
                                                <span class="ccm-detail-value"><?php echo esc_html($annual_fee); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($cashback_rate)) : ?>
                                            <div class="ccm-detail-item">
                                                <span class="ccm-detail-label">Reward Rate</span>
                                                <span class="ccm-detail-value"><?php echo esc_html($cashback_rate); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($welcome_bonus)) : ?>
                                            <div class="ccm-detail-item">
                                                <span class="ccm-detail-label">Welcome Bonus</span>
                                                <span class="ccm-detail-value"><?php echo esc_html($welcome_bonus); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($card_excerpt)) : ?>
                                        <div class="ccm-card-excerpt"><?php echo wp_kses_post($card_excerpt); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="ccm-card-actions">
                                        <a href="<?php echo esc_url($card_link); ?>" class="ccm-btn ccm-btn-details">
                                            <span class="dashicons dashicons-visibility"></span> Read More
                                        </a>
                                        <?php if (!empty($apply_link)) : ?>
                                            <a href="<?php echo esc_url($apply_link); ?>" class="ccm-btn ccm-btn-apply" target="_blank" rel="noopener noreferrer">
                                                <span class="dashicons dashicons-external"></span> Quick Apply
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="ccm-no-results">
                        <p>No credit cards found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($credit_cards_query->max_num_pages > 1) : ?>
                <div class="ccm-pagination" id="ccm-pagination">
                    <?php
                    $big = 999999999; // need an unlikely integer
                    echo paginate_links(array(
                        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => $credit_cards_query->max_num_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'type' => 'list',
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Compare Modal -->
<div class="ccm-modal" id="ccm-compare-modal">
    <div class="ccm-modal-content">
        <div class="ccm-modal-header">
            <h2>Compare Credit Cards</h2>
            <button type="button" class="ccm-modal-close" id="ccm-modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="ccm-modal-body">
            <table class="ccm-comparison-table" id="ccm-comparison-table"></table>
        </div>
    </div>
</div>

<!-- Card Template for Compare Section -->
<script type="text/template" id="ccm-compare-card-template">
    <div class="ccm-compare-card">
        <button type="button" class="ccm-compare-remove" data-id="{{id}}">×</button>
        <img src="{{image}}" alt="{{title}}">
        <div class="ccm-compare-title">{{title}}</div>
    </div>
</script>

<style>
/* Container */
.ccm-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Hero Section */
.ccm-hero-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.75rem;
    padding: 40px;
    margin-bottom: 40px;
    overflow: hidden;
}

.ccm-hero-content {
    flex: 1;
    padding-right: 40px;
}

.ccm-hero-title {
    font-size: 2.5rem;
    color: #1e40af;
    margin-bottom: 20px;
    line-height: 1.2;
}

.ccm-hero-description {
    font-size: 1.1rem;
    color: #4b5563;
    line-height: 1.6;
}

.ccm-hero-image {
    flex: 1;
    text-align: center;
}

.ccm-hero-image img {
    max-width: 100%;
    height: auto;
    max-height: 300px;
}

/* Category Icons Section */
.ccm-category-section {
    margin-bottom: 40px;
}

.ccm-section-title {
    font-size: 1.8rem;
    color: #1e293b;
    margin-bottom: 20px;
    text-align: center;
}

.ccm-category-icons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}

.ccm-category-icon-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 150px;
    text-decoration: none;
    color: #1e293b;
    transition: transform 0.2s;
}

.ccm-category-icon-item:hover {
    transform: translateY(-5px);
}

.ccm-category-icon {
    width: 80px;
    height: 80px;
    background-color: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.ccm-category-icon svg {
    width: 40px;
    height: 40px;
}

.ccm-category-icon .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    color: #1e40af;
}

.ccm-category
