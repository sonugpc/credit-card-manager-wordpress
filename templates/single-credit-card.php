<?php
/**
 * Professional Single Credit Card Template - Version 3.0
 * Inspired by PaisaBazaar and CardInsider designs
 * Blog-style layout with comprehensive information
 * 
 * @package Credit Card Manager
 */

get_header();

// Data preparation
$post_id = get_the_ID();
$card_name = get_the_title();

// Basic info
$rating = ccm_get_meta($post_id, 'rating', 0, true);
$review_count = ccm_get_meta($post_id, 'review_count', 0, true);
$annual_fee = ccm_get_meta($post_id, 'annual_fee', 0, true);
$joining_fee = ccm_get_meta($post_id, 'joining_fee', 0, true);
$welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', 'N/A');
$cashback_rate = ccm_get_meta($post_id, 'cashback_rate', 'N/A');
$reward_type = ccm_get_meta($post_id, 'reward_type', '');
$reward_conversion_rate = ccm_get_meta($post_id, 'reward_conversion_rate', '');
$reward_conversion_value = ccm_get_meta($post_id, 'reward_conversion_value', 0, true);
$credit_limit = ccm_get_meta($post_id, 'credit_limit', 'N/A');
$processing_time = ccm_get_meta($post_id, 'processing_time', 'N/A');
$min_income = ccm_get_meta($post_id, 'min_income', 'N/A');
$apply_link = esc_url(ccm_get_meta($post_id, 'apply_link', '#'));
$featured = (bool) ccm_get_meta($post_id, 'featured', false);
$trending = (bool) ccm_get_meta($post_id, 'trending', false);

// Advanced data
$pros = ccm_get_meta($post_id, 'pros', [], false, true);
$cons = ccm_get_meta($post_id, 'cons', [], false, true);
$best_for = ccm_get_meta($post_id, 'best_for', [], false, true);
$features = ccm_get_meta($post_id, 'features', [], false, true);
$rewards = ccm_get_meta($post_id, 'rewards', [], false, true);
$fees = ccm_get_meta($post_id, 'fees', [], false, true);
$eligibility = ccm_get_meta($post_id, 'eligibility', [], false, true);
$documents = ccm_get_meta($post_id, 'documents', [], false, true);

// Get taxonomy terms
$bank_terms = get_the_terms($post_id, 'store');
$bank_name = (!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '';

$network_terms = get_the_terms($post_id, 'network-type');
$network_type = (!is_wp_error($network_terms) && !empty($network_terms)) ? $network_terms[0]->name : 'N/A';

$category_terms = get_the_terms($post_id, 'card-category');
$category_name = (!is_wp_error($category_terms) && !empty($category_terms)) ? $category_terms[0]->name : '';

// Card image - use featured image
$card_image = has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'large') : '';

// SEO data
$page_title = $card_name . ' Credit Card Review 2025 - Fees, Benefits & Apply Online';
$meta_description = sprintf(
    '%s credit card review with %s/5 rating. ₹%s annual fee, %s rewards. Compare benefits, eligibility & apply online instantly.',
    $card_name,
    $rating,
    number_format($annual_fee),
    $cashback_rate
);

// Use existing format_currency function from helper-functions.php
?>

<?php
// SEO Meta Tags (only if no SEO plugin detected)
if (!function_exists('ccm_has_seo_plugin') || !ccm_has_seo_plugin()) {
    ccm_add_meta_tags($page_title, $meta_description, get_permalink(), $card_name . ', credit card, ' . $bank_name . ', review, apply online');
    ccm_add_og_tags($page_title, $meta_description, get_permalink(), $card_image, 'article');
    ccm_add_twitter_tags($page_title, $meta_description, get_permalink(), $card_image);
}
?>


<style>
/* Professional Credit Card Single Page Styles */
:root {
    --primary-blue: #2563eb;
    --primary-green: #059669;
    --primary-red: #dc2626;
    --primary-orange: #ea580c;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    --radius-sm: 6px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;
}

* {
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: var(--gray-800);
    background-color: var(--gray-50);
    margin: 0;
    padding: 0;
}

.cc-single-wrapper {
    width: 100%;
    background: white;
    min-height: 100vh;
}

/* Hero Section */
.cc-hero {
    background: linear-gradient(135deg, var(--primary-blue) 0%, #1e40af 100%);
    color: white;
    padding: 2rem 1.5rem;
    position: relative;
    overflow: hidden;
}

.cc-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.1;
}

.cc-hero-content {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
}

.cc-hero-header {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.cc-card-image {
    width: 140px;
    height: auto;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    flex-shrink: 0;
}

.cc-hero-title {
    flex: 1;
}

.cc-hero-title h1 {
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.cc-bank-name {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0 0 1rem 0;
    font-weight: 500;
}

.cc-hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.cc-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cc-badge.featured {
    background: var(--primary-orange);
}

.cc-badge.trending {
    background: var(--primary-red);
}

.cc-badge.rating {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.cc-key-highlights {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
}

.cc-highlight-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    padding: 1rem;
    text-align: center;
}

.cc-highlight-label {
    font-size: 0.875rem;
    opacity: 0.8;
    margin-bottom: 0.5rem;
}

.cc-highlight-value {
    font-size: 1.25rem;
    font-weight: 700;
}

/* Sticky Navigation */
.cc-nav-sticky {
    position: sticky;
    top: 0;
    background: white;
    border-bottom: 1px solid var(--gray-200);
    z-index: 100;
    box-shadow: var(--shadow-sm);
}

.cc-nav-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.cc-nav-links {
    display: flex;
    gap: 2rem;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.cc-nav-links::-webkit-scrollbar {
    display: none;
}

.cc-nav-link {
    font-weight: 500;
    color: var(--gray-600);
    text-decoration: none;
    padding: 0.5rem 0;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}

.cc-nav-link:hover,
.cc-nav-link.active {
    color: var(--primary-blue);
    border-bottom-color: var(--primary-blue);
}

.cc-apply-btn {
    background: var(--primary-blue);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.cc-apply-btn:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: var(--shadow-lg);
}

/* Content Sections */
.cc-content {
    padding: 2rem 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.cc-section {
    margin-bottom: 3rem;
}

.cc-section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.cc-section-title::before {
    content: '';
    width: 4px;
    height: 2rem;
    background: var(--primary-blue);
    border-radius: 2px;
}

/* Quick Overview Grid */
.cc-overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.cc-overview-card {
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    text-align: center;
}

.cc-overview-icon {
    width: 3rem;
    height: 3rem;
    background: var(--primary-blue);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.5rem;
}

.cc-overview-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin-bottom: 0.5rem;
}

.cc-overview-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
}

/* Pros and Cons */
.cc-pros-cons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .cc-pros-cons {
        grid-template-columns: 1fr;
    }
}

.cc-pros,
.cc-cons {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
}

.cc-pros {
    border-left: 4px solid var(--primary-green);
}

.cc-cons {
    border-left: 4px solid var(--primary-red);
}

.cc-pros h3 {
    color: var(--primary-green);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
}

.cc-cons h3 {
    color: var(--primary-red);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
}

.cc-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.cc-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    padding: 0.5rem 0;
}

.cc-list li:last-child {
    margin-bottom: 0;
}

.cc-list .icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.cc-pros .icon {
    color: var(--primary-green);
}

.cc-cons .icon {
    color: var(--primary-red);
}

/* Best For Tags */
.cc-best-for {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.cc-tag {
    background: var(--primary-blue);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Features List */
.cc-features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.cc-feature-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    transition: background-color 0.2s;
}

.cc-feature-item:hover {
    background-color: var(--gray-50);
}

.cc-feature-item:last-child {
    border-bottom: none;
}

.cc-feature-icon {
    width: 3rem;
    height: 3rem;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-blue);
    font-size: 1.5rem;
    flex-shrink: 0;
}

.cc-feature-content h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: var(--gray-900);
}

.cc-feature-content p {
    color: var(--gray-600);
    margin: 0;
    line-height: 1.5;
}

/* Data Tables */
.cc-data-table {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.cc-table-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.cc-table-row:last-child {
    border-bottom: none;
}

.cc-table-row:nth-child(even) {
    background-color: var(--gray-50);
}

.cc-table-label {
    font-weight: 500;
    color: var(--gray-700);
}

.cc-table-value {
    font-weight: 600;
    color: var(--gray-900);
}

.cc-table-value.positive {
    color: var(--primary-green);
}

.cc-table-value.negative {
    color: var(--primary-red);
}

/* FAQ Section */
.cc-faq-item {
    background: white;
    border-radius: var(--radius-lg);
    margin-bottom: 1rem;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.cc-faq-question {
    padding: 1.5rem;
    font-weight: 600;
    color: var(--gray-900);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.2s;
}

.cc-faq-question:hover {
    background-color: var(--gray-50);
}

.cc-faq-answer {
    padding: 0 1.5rem 1.5rem;
    color: var(--gray-700);
    line-height: 1.6;
    display: none;
}

.cc-faq-item.active .cc-faq-answer {
    display: block;
}

/* Bottom CTA */
.cc-bottom-cta {
    background: linear-gradient(135deg, var(--primary-blue) 0%, #1e40af 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: var(--radius-xl);
    text-align: center;
    margin: 3rem 0;
}

.cc-bottom-cta h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
}

.cc-bottom-cta p {
    font-size: 1.125rem;
    opacity: 0.9;
    margin: 0 0 2rem 0;
}

.cc-cta-button {
    background: white;
    color: var(--primary-blue);
    padding: 1rem 2rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 700;
    font-size: 1.125rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    box-shadow: var(--shadow-lg);
}

.cc-cta-button:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
}

/* Content Body Styling */
.cc-content-body {
    line-height: 1.7;
    color: var(--gray-700);
}

.cc-content-body p {
    margin-bottom: 1.5rem;
}

.cc-content-body h1,
.cc-content-body h2,
.cc-content-body h3,
.cc-content-body h4,
.cc-content-body h5,
.cc-content-body h6 {
    color: var(--gray-900);
    font-weight: 600;
    margin: 2rem 0 1rem 0;
    line-height: 1.3;
}

.cc-content-body h2 {
    font-size: 1.5rem;
    border-bottom: 2px solid var(--gray-200);
    padding-bottom: 0.5rem;
}

.cc-content-body h3 {
    font-size: 1.25rem;
}

.cc-content-body ul,
.cc-content-body ol {
    margin: 1rem 0;
    padding-left: 2rem;
}

.cc-content-body li {
    margin-bottom: 0.5rem;
}

.cc-content-body blockquote {
    border-left: 4px solid var(--primary-blue);
    background: var(--gray-50);
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
}

.cc-content-body img {
    max-width: 100%;
    height: auto;
    border-radius: var(--radius-md);
    margin: 1rem 0;
}

.cc-content-body table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
    background: white;
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.cc-content-body th,
.cc-content-body td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
}

.cc-content-body th {
    background: var(--gray-50);
    font-weight: 600;
    color: var(--gray-900);
}

.cc-content-body strong {
    color: var(--gray-900);
    font-weight: 600;
}

.cc-content-body code {
    background: var(--gray-100);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
}

.cc-content-body pre {
    background: var(--gray-900);
    color: white;
    padding: 1rem;
    border-radius: var(--radius-md);
    overflow-x: auto;
    margin: 1rem 0;
}

.cc-content-body pre code {
    background: transparent;
    padding: 0;
}

.page-links {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-200);
    text-align: center;
}

.page-links a {
    display: inline-block;
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    background: var(--primary-blue);
    color: white;
    text-decoration: none;
    border-radius: var(--radius-md);
    transition: background-color 0.2s;
}

.page-links a:hover {
    background: #1d4ed8;
}

/* Responsive Design */
@media (max-width: 768px) {
    .cc-hero {
        padding: 1.5rem 1rem;
    }
    
    .cc-hero-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .cc-hero-title h1 {
        font-size: 1.5rem;
    }
    
    .cc-nav-container {
        padding: 0.75rem 1rem;
    }
    
    .cc-content {
        padding: 1.5rem 1rem;
    }
    
    .cc-section-title {
        font-size: 1.5rem;
    }
    
    .cc-key-highlights {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .cc-overview-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .cc-pros-cons {
        gap: 1.5rem;
    }
}
</style>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('cc-single-wrapper'); ?>>
                <!-- Hero Section -->
                <header class="entry-header cc-hero">
                    <div class="cc-hero-content">
                        <div class="cc-hero-header">
                            <?php if ($card_image): ?>
                                <img src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr($card_name); ?>" class="cc-card-image">
                            <?php endif; ?>
                            
                            <div class="cc-hero-title">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                                <?php if ($bank_name): ?>
                                    <p class="cc-bank-name">by <?php echo esc_html($bank_name); ?></p>
                                <?php endif; ?>
                                
                                <div class="cc-hero-badges">
                                    <?php if ($rating > 0): ?>
                                        <div class="cc-badge rating">
                                            ⭐ <?php echo esc_html($rating); ?>/5 (<?php echo esc_html($review_count); ?> reviews)
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($featured): ?>
                                        <div class="cc-badge featured">
                                            🏆 Featured
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($trending): ?>
                                        <div class="cc-badge trending">
                                            🔥 Trending
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($category_name): ?>
                                        <div class="cc-badge">
                                            <?php echo esc_html($category_name); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="cc-key-highlights">
                            <div class="cc-highlight-card">
                                <div class="cc-highlight-label">Annual Fee</div>
                                <div class="cc-highlight-value"><?php echo esc_html(ccm_format_currency($annual_fee)); ?></div>
                            </div>
                            
                            <div class="cc-highlight-card">
                                <div class="cc-highlight-label">Reward Rate</div>
                                <div class="cc-highlight-value"><?php echo esc_html($cashback_rate); ?></div>
                            </div>
                            
                            <div class="cc-highlight-card">
                                <div class="cc-highlight-label">Welcome Bonus</div>
                                <div class="cc-highlight-value"><?php echo esc_html($welcome_bonus); ?></div>
                            </div>
                            
                            <div class="cc-highlight-card">
                                <div class="cc-highlight-label">Credit Limit</div>
                                <div class="cc-highlight-value"><?php echo esc_html($credit_limit); ?></div>
                            </div>
                        </div>
                    </div>
                </header>
                
                <!-- Sticky Navigation -->
                <nav class="cc-nav-sticky">
                    <div class="cc-nav-container">
                        <div class="cc-nav-links">
                            <a href="#overview" class="cc-nav-link active">Overview</a>
                            <?php if (get_the_content()): ?>
                            <a href="#content" class="cc-nav-link">About</a>
                            <?php endif; ?>
                            <a href="#features" class="cc-nav-link">Features</a>
                            <a href="#rewards" class="cc-nav-link">Rewards</a>
                            <a href="#fees" class="cc-nav-link">Fees</a>
                            <a href="#eligibility" class="cc-nav-link">Eligibility</a>
                            <a href="#faq" class="cc-nav-link">FAQ</a>
                        </div>
                        <a href="<?php echo $apply_link; ?>" target="_blank" rel="noopener" class="cc-apply-btn">
                            Apply Now
                        </a>
                    </div>
                </nav>
                
                <!-- Main Content -->
                <div class="entry-content cc-content">
        <!-- Overview Section -->
        <section id="overview" class="cc-section">
            <h2 class="cc-section-title">Quick Overview</h2>
            
            <div class="cc-overview-grid">
                <div class="cc-overview-card">
                    <div class="cc-overview-icon">💳</div>
                    <div class="cc-overview-label">Network</div>
                    <div class="cc-overview-value"><?php echo esc_html($network_type); ?></div>
                </div>
                
                <div class="cc-overview-card">
                    <div class="cc-overview-icon">💰</div>
                    <div class="cc-overview-label">Joining Fee</div>
                    <div class="cc-overview-value"><?php echo esc_html(ccm_format_currency($joining_fee)); ?></div>
                </div>
                
                <div class="cc-overview-card">
                    <div class="cc-overview-icon">⏱️</div>
                    <div class="cc-overview-label">Processing Time</div>
                    <div class="cc-overview-value"><?php echo esc_html($processing_time); ?></div>
                </div>
                
                <div class="cc-overview-card">
                    <div class="cc-overview-icon">📈</div>
                    <div class="cc-overview-label">Min Income</div>
                    <div class="cc-overview-value"><?php echo esc_html($min_income); ?></div>
                </div>
                
                <?php if (!empty($reward_type)): ?>
                <div class="cc-overview-card">
                    <div class="cc-overview-icon">🎁</div>
                    <div class="cc-overview-label">Reward Type</div>
                    <div class="cc-overview-value"><?php echo esc_html($reward_type); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($reward_conversion_rate)): ?>
                <div class="cc-overview-card">
                    <div class="cc-overview-icon">💱</div>
                    <div class="cc-overview-label">Conversion Rate</div>
                    <div class="cc-overview-value"><?php echo esc_html($reward_conversion_rate); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Pros and Cons -->
            <?php if (!empty($pros) || !empty($cons)): ?>
            <section id="pros-cons" class="cc-section">
                <h2 class="cc-section-title">Pros & Cons</h2>
                <div class="cc-pros-cons">
                    <?php if (!empty($pros)): ?>
                    <div class="cc-pros">
                        <h3>✅ Pros</h3>
                        <ul class="cc-list">
                            <?php foreach ($pros as $pro): ?>
                                <li>
                                    <span class="icon">✓</span>
                                    <span><?php echo esc_html($pro); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($cons)): ?>
                    <div class="cc-cons">
                        <h3>❌ Cons</h3>
                        <ul class="cc-list">
                            <?php foreach ($cons as $con): ?>
                                <li>
                                    <span class="icon">✗</span>
                                    <span><?php echo esc_html($con); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- Best For -->
            <?php if (!empty($best_for)): ?>
            <div>
                <h3>🎯 Best For</h3>
                <div class="cc-best-for">
                    <?php foreach ($best_for as $item): ?>
                        <span class="cc-tag"><?php echo esc_html($item); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
        
        <!-- Content Section -->
        <?php if (get_the_content()): ?>
        <section id="content" class="cc-section">
            <h2 class="cc-section-title">About This Card</h2>
            <div class="cc-content-body">
                <?php 
                the_content();
                wp_link_pages(array(
                    'before' => '<div class="page-links">',
                    'after'  => '</div>',
                ));
                ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Features Section -->
        <?php if (!empty($features)): ?>
        <section id="features" class="cc-section">
            <h2 class="cc-section-title">Key Features</h2>
            
            <ul class="cc-features-list">
                <?php foreach ($features as $feature): ?>
                    <li class="cc-feature-item">
                        <div class="cc-feature-icon">🎁</div>
                        <div class="cc-feature-content">
                            <h4><?php echo esc_html($feature['title'] ?? $feature); ?></h4>
                            <?php if (isset($feature['description'])): ?>
                                <p><?php echo esc_html($feature['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
        
        <!-- Rewards Section -->
        <?php if (!empty($rewards) || !empty($cashback_rate) || !empty($welcome_bonus)): ?>
        <section id="rewards" class="cc-section">
            <h2 class="cc-section-title">Reward Program</h2>
            
            <!-- Reward Type and Conversion Info -->
            <?php if (!empty($reward_type) || !empty($reward_conversion_rate)): ?>
            <div style="background: var(--gray-50); padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 1.5rem; border-left: 4px solid var(--primary-blue);">
                <h3 style="margin: 0 0 1rem 0; color: var(--primary-blue); font-size: 1.125rem;">
                    <?php echo !empty($reward_type) ? esc_html($reward_type) . ' ' : ''; ?>Rewards
                </h3>
                <?php if (!empty($reward_conversion_rate)): ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="color: var(--gray-700); font-weight: 500;">Conversion Rate:</span>
                        <span style="color: var(--gray-900); font-weight: 600;"><?php echo esc_html($reward_conversion_rate); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($reward_conversion_value > 0): ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: var(--gray-700); font-weight: 500;">Value:</span>
                        <span style="color: var(--primary-green); font-weight: 600;">
                            <?php 
                            if ($reward_conversion_value == 1) {
                                echo '1:1 (Full Value)';
                            } else {
                                echo esc_html(number_format($reward_conversion_value, 2)) . ' Rupees per unit';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($rewards)): ?>
                <div class="cc-data-table">
                    <?php foreach ($rewards as $reward): ?>
                        <div class="cc-table-row">
                            <div>
                                <div class="cc-table-label"><?php echo esc_html($reward['category'] ?? 'Reward Category'); ?></div>
                                <?php if (isset($reward['description'])): ?>
                                    <div style="font-size: 0.875rem; color: var(--gray-600); margin-top: 0.25rem;">
                                        <?php echo esc_html($reward['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="cc-table-value positive">
                                <?php echo esc_html($reward['rate'] ?? $reward); ?>
                                <?php if (!empty($reward_type) && strtolower($reward_type) !== 'cashback'): ?>
                                    <small style="display: block; font-weight: normal; opacity: 0.8;">
                                        <?php echo esc_html($reward_type); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="cc-data-table">
                    <div class="cc-table-row">
                        <div class="cc-table-label">Default Reward Rate</div>
                        <div class="cc-table-value positive">
                            <?php echo esc_html($cashback_rate); ?>
                            <?php if (!empty($reward_type)): ?>
                                <small style="display: block; font-weight: normal; opacity: 0.8;">
                                    in <?php echo esc_html($reward_type); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="cc-table-row">
                        <div class="cc-table-label">Welcome Bonus</div>
                        <div class="cc-table-value positive"><?php echo esc_html($welcome_bonus); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
        
        <!-- Fees Section -->
        <?php if (!empty($fees) || $annual_fee > 0 || $joining_fee > 0): ?>
        <section id="fees" class="cc-section">
            <h2 class="cc-section-title">Fees & Charges</h2>
            
            <div class="cc-data-table">
                <?php if (!empty($fees)): ?>
                    <?php foreach ($fees as $fee): ?>
                        <div class="cc-table-row">
                            <div class="cc-table-label"><?php echo esc_html($fee['type'] ?? 'Fee'); ?></div>
                            <div class="cc-table-value negative"><?php echo esc_html($fee['amount'] ?? $fee); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cc-table-row">
                        <div class="cc-table-label">Joining Fee</div>
                        <div class="cc-table-value <?php echo $joining_fee > 0 ? 'negative' : 'positive'; ?>">
                            <?php echo esc_html(ccm_format_currency($joining_fee)); ?>
                        </div>
                    </div>
                    
                    <div class="cc-table-row">
                        <div class="cc-table-label">Annual Fee</div>
                        <div class="cc-table-value <?php echo $annual_fee > 0 ? 'negative' : 'positive'; ?>">
                            <?php echo esc_html(ccm_format_currency($annual_fee)); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Eligibility Section -->
        <?php if (!empty($eligibility) || !empty($documents)): ?>
        <section id="eligibility" class="cc-section">
            <h2 class="cc-section-title">Eligibility Criteria</h2>
            
            <div class="cc-data-table">
                <?php if (!empty($eligibility)): ?>
                    <?php foreach ($eligibility as $criterion): ?>
                        <div class="cc-table-row">
                            <div class="cc-table-label"><?php echo esc_html($criterion['criteria'] ?? 'Criteria'); ?></div>
                            <div class="cc-table-value"><?php echo esc_html($criterion['value'] ?? $criterion); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cc-table-row">
                        <div class="cc-table-label">Minimum Income</div>
                        <div class="cc-table-value"><?php echo esc_html($min_income); ?></div>
                    </div>
                    <div class="cc-table-row">
                        <div class="cc-table-label">Age Requirement</div>
                        <div class="cc-table-value">21-65 years</div>
                    </div>
                    <div class="cc-table-row">
                        <div class="cc-table-label">Employment</div>
                        <div class="cc-table-value">Salaried/Self-employed</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($documents)): ?>
            <div style="margin-top: 2rem;">
                <h3>📄 Required Documents</h3>
                <ul class="cc-list">
                    <?php foreach ($documents as $document): ?>
                        <li>
                            <span class="icon">📄</span>
                            <span><?php echo esc_html($document); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
        
        <!-- FAQ Section -->
        <?php 
        $card_faqs = ccm_get_card_faqs(get_the_ID());
        if (!empty($card_faqs)): ?>
        <section id="faq" class="cc-section">
            <h2 class="cc-section-title">Frequently Asked Questions</h2>
            
            <?php foreach ($card_faqs as $index => $faq): ?>
                <div class="cc-faq-item">
                    <div class="cc-faq-question">
                        <?php echo esc_html($faq['question']); ?>
                        <span>+</span>
                    </div>
                    <div class="cc-faq-answer">
                        <?php echo wp_kses_post($faq['answer']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
        
        <!-- Bottom CTA -->
        <section class="cc-bottom-cta">
            <h3>Ready to Apply for <?php echo esc_html($card_name); ?>?</h3>
            <p>Join thousands of satisfied customers and start earning rewards today. Apply online in just 5 minutes!</p>
            <a href="<?php echo $apply_link; ?>" target="_blank" rel="noopener" class="cc-cta-button">
                Apply Now - Get Instant Approval ⚡
            </a>
        </section>
    </div>
    <footer class="entry-footer">
        <?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
    </footer>
</article>
<?php endwhile; ?>
</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.cc-nav-link');
    const sections = document.querySelectorAll('.cc-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 100;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Update active navigation on scroll
    window.addEventListener('scroll', function() {
        let current = '';
        const stickyNavHeight = document.querySelector('.cc-nav-sticky').offsetHeight;
        sections.forEach(section => {
            const sectionTop = section.offsetTop - stickyNavHeight - 20;
            if (scrollY >= sectionTop) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').substring(1) === current) {
                link.classList.add('active');
            }
        });
    });
    
    // FAQ toggle functionality
    const faqItems = document.querySelectorAll('.cc-faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.cc-faq-question');
        const answer = item.querySelector('.cc-faq-answer');
        const icon = question.querySelector('span');
        
        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');
            
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
                otherItem.querySelector('.cc-faq-question span').textContent = '+';
            });
            
            // Toggle current item
            if (!isActive) {
                item.classList.add('active');
                icon.textContent = '−';
            }
        });
    });
    
    // Apply button click tracking
    const applyButtons = document.querySelectorAll('.cc-apply-btn, .cc-cta-button');
    applyButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Track apply button clicks (you can integrate with analytics here)
            console.log('Apply button clicked for:', '<?php echo esc_js($card_name); ?>');
        });
    });
});
</script>


<?php get_footer(); ?>
