<?php
/**
 * The template for displaying a single "Credit Card" post type.
 * SEO-optimized with rich snippets and meta tags
 *
 * @package Credit Card Manager
 */

get_header();

// --- Data Fetching & Preparation ---
$post_id = get_the_ID();
$meta = get_post_meta($post_id);
$card_name = get_the_title();

// Helper function for rupee formatting (local override)
function format_rupees($amount) {
    if (!is_numeric($amount)) {
        return $amount;
    }
    return '₹' . number_format($amount);
}

// --- Assign all variables ---
$rating = ccm_get_meta($post_id, 'rating', 0, true);
$review_count = ccm_get_meta($post_id, 'review_count', 0, true);
$annual_fee = ccm_get_meta($post_id, 'annual_fee', 0, true);
$joining_fee = ccm_get_meta($post_id, 'joining_fee', 0, true);
$welcome_bonus = ccm_get_meta($post_id, 'welcome_bonus', 'N/A');
$credit_limit = ccm_get_meta($post_id, 'credit_limit', 'N/A');
$processing_time = ccm_get_meta($post_id, 'processing_time', 'N/A');
$min_income = ccm_get_meta($post_id, 'min_income', 'N/A');
$apply_link = esc_url(ccm_get_meta($post_id, 'apply_link', '#'));
$trending = (bool) ccm_get_meta($post_id, 'trending', false);

// Design & Color
$gradient_class = ccm_get_meta($post_id, 'gradient', 'from-gray-700 to-gray-900'); // Default gradient
$theme_color = ccm_get_meta($post_id, 'theme_color', '#1e40af');

// Scores
$overall_score = ccm_get_meta($post_id, 'overall_score', 0, true);
$reward_score = ccm_get_meta($post_id, 'reward_score', 0, true);
$fees_score = ccm_get_meta($post_id, 'fees_score', 0, true);
$benefits_score = ccm_get_meta($post_id, 'benefits_score', 0, true);
$support_score = ccm_get_meta($post_id, 'support_score', 0, true);

// Array data
$pros = ccm_get_meta($post_id, 'pros', [], false, true);
$cons = ccm_get_meta($post_id, 'cons', [], false, true);
$best_for = ccm_get_meta($post_id, 'best_for', [], false, true);
$features = ccm_get_meta($post_id, 'features', [], false, true);
$rewards = ccm_get_meta($post_id, 'rewards', [], false, true);
$fees = ccm_get_meta($post_id, 'fees', [], false, true);
$eligibility = ccm_get_meta($post_id, 'eligibility', [], false, true);
$documents = ccm_get_meta($post_id, 'documents', [], false, true);

// Get Network Type taxonomy
$network_terms = get_the_terms($post_id, 'network-type');
if (!is_wp_error($network_terms) && !empty($network_terms)) {
    $network_names = wp_list_pluck($network_terms, 'name');
    $network_type = implode(', ', $network_names);
} else {
    $network_type = 'N/A';
}

// Get Bank/Store taxonomy
$bank_terms = get_the_terms($post_id, 'store');
$bank_name = (!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '';

// SEO Meta Data
$page_title = get_the_title() . ' Credit Card Review & Apply Online';
$meta_description = sprintf(
    'Complete review of %s credit card with %s rating. Compare fees (₹%s annual), rewards, benefits and apply online. Get expert insights and user reviews.',
    get_the_title(),
    $rating,
    number_format($annual_fee)
);
$canonical_url = get_permalink($post_id);
$featured_image = has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'large') : '';

// Breadcrumb structured data
$breadcrumbs = [
    ['name' => 'Home', 'url' => home_url()],
    ['name' => 'Credit Cards', 'url' => get_post_type_archive_link('credit-card')],
    ['name' => get_the_title(), 'url' => $canonical_url]
];

?>

<?php
// Only output meta tags if no SEO plugin is detected
if (!function_exists('ccm_has_seo_plugin') || !ccm_has_seo_plugin()) {
    // Output basic meta tags only if no SEO plugin
    ccm_add_meta_tags(
        $page_title, 
        $meta_description, 
        $canonical_url, 
        get_the_title() . ', credit card, ' . $bank_name . ', ' . $network_type . ', rewards, cashback, apply online'
    );
    
    // Output social media tags only if no SEO plugin
    ccm_add_og_tags($page_title, $meta_description, $canonical_url, $featured_image, 'article');
    ccm_add_twitter_tags($page_title, $meta_description, $canonical_url, $featured_image);
    
    // Article specific meta (only if no SEO plugin)
    echo '<meta property="article:published_time" content="' . get_the_date('c') . '">' . "\n";
    echo '<meta property="article:modified_time" content="' . get_the_modified_date('c') . '">' . "\n";
    echo '<meta property="article:section" content="Credit Cards">' . "\n";
    echo '<meta property="article:tag" content="' . esc_attr(get_the_title() . ', ' . $bank_name . ', Credit Card') . '">' . "\n";
} else {
    echo '<!-- Meta tags handled by ' . (class_exists('RankMath') ? 'RankMath' : 'SEO Plugin') . ' -->' . "\n";
}
?>

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "<?php echo esc_attr(get_the_title()); ?>",
    "description": "<?php echo esc_attr(wp_strip_all_tags(get_the_excerpt())); ?>",
    "brand": {
        "@type": "Brand",
        "name": "<?php echo esc_attr($bank_name); ?>"
    },
    <?php if ($featured_image): ?>
    "image": "<?php echo esc_url($featured_image); ?>",
    <?php endif; ?>
    "category": "Credit Card",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo esc_attr($rating); ?>",
        "reviewCount": "<?php echo esc_attr($review_count); ?>",
        "bestRating": "5",
        "worstRating": "1"
    },
    "offers": {
        "@type": "Offer",
        "url": "<?php echo esc_url($apply_link); ?>",
        "priceCurrency": "INR",
        "price": "<?php echo esc_attr($annual_fee); ?>",
        "priceValidUntil": "<?php echo date('Y-12-31'); ?>",
        "availability": "https://schema.org/InStock",
        "seller": {
            "@type": "Organization",
            "name": "<?php echo esc_attr($bank_name); ?>"
        }
    },
    "additionalProperty": [
        {
            "@type": "PropertyValue",
            "name": "Annual Fee",
            "value": "₹<?php echo esc_attr(number_format($annual_fee)); ?>"
        },
        {
            "@type": "PropertyValue",
            "name": "Network Type",
            "value": "<?php echo esc_attr($network_type); ?>"
        },
        {
            "@type": "PropertyValue",
            "name": "Welcome Bonus",
            "value": "<?php echo esc_attr($welcome_bonus); ?>"
        }
    ]
}
</script>

<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
        {
            "@type": "ListItem",
            "position": <?php echo $index + 1; ?>,
            "name": "<?php echo esc_attr($breadcrumb['name']); ?>",
            "item": "<?php echo esc_url($breadcrumb['url']); ?>"
        }<?php echo ($index < count($breadcrumbs) - 1) ? ',' : ''; ?>
        <?php endforeach; ?>
    ]
}
</script>

<!-- FinancialProduct Schema -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FinancialProduct",
    "name": "<?php echo esc_attr(get_the_title()); ?>",
    "description": "<?php echo esc_attr($meta_description); ?>",
    "provider": {
        "@type": "FinancialService",
        "name": "<?php echo esc_attr($bank_name); ?>"
    },
    "feesAndCommissionsSpecification": "Annual Fee: ₹<?php echo esc_attr(number_format($annual_fee)); ?>, Joining Fee: ₹<?php echo esc_attr(number_format($joining_fee)); ?>",
    "interestRate": "<?php echo esc_attr(ccm_get_meta($post_id, 'interest_rate', 'Varies')); ?>",
    "amount": {
        "@type": "MonetaryAmount",
        "currency": "INR",
        "value": "<?php echo esc_attr($annual_fee); ?>"
    }
}
</script>

<style>
    /* Tailwind-inspired CSS for the template */
    :root {
        --cc-theme-color: <?php echo esc_attr($theme_color); ?>;
        --cc-gray-50: #f9fafb; --cc-gray-100: #f3f4f6; --cc-gray-200: #e5e7eb; --cc-gray-600: #4b5563; --cc-gray-700: #374151; --cc-gray-800: #1f2937;
        --cc-blue-50: #eff6ff; --cc-blue-200: #bfdbfe; --cc-blue-500: #3b82f6; --cc-blue-600: #2563eb; --cc-blue-700: #1d4ed8;
        --cc-green-500: #22c55e; --cc-red-500: #ef4444; --cc-red-600: #dc2626; --cc-red-700: #b91c1c;
        --cc-purple-600: #9333ea;
        --cc-yellow-400: #facc15;
    }
    .theme-background {
        background: var(--cc-theme-color);
    }
    .cc-body { background-color: var(--cc-gray-50); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    .cc-hero { color: white; position: relative; overflow: hidden; }
    .cc-hero.gradient-1 { background-image: linear-gradient(to bottom right, #1e40af, #7c3aed); }
    .cc-hero.gradient-2 { background-image: linear-gradient(to bottom right, #be185d, #f472b6); }
    .cc-hero.gradient-3 { background-image: linear-gradient(to bottom right, #047857, #34d399); }
    .cc-hero.gradient-default { background-image: linear-gradient(to bottom right, #4b5563, #1f2937); }
    .cc-hero-content { position: relative; z-index: 10; padding: 2rem 1.5rem; }
    .cc-hero-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .cc-hero-img-wrap { width: 100px; height: 63px; }
    .cc-hero-img-wrap img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .cc-hero-title h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 4px; }
    .cc-hero-title p { opacity: 0.9; font-size: 0.875rem; margin: 0; }
    .cc-rating-trending { display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem; }
    .cc-rating { display: flex; align-items: center; gap: 0.25rem; }
    .cc-rating .icon { width: 1rem; height: 1rem; color: var(--cc-yellow-400); }
    .cc-rating span { font-size: 0.875rem; font-weight: 500; }
    .cc-trending-badge { background-color: var(--cc-red-500); font-size: 0.75rem; padding: 4px 8px; border-radius: 99px; font-weight: 500; display: flex; align-items: center; gap: 4px; }
    .cc-trending-badge .icon { width: 0.75rem; height: 0.75rem; }
    .cc-hero-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
    .cc-stat-box { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(4px); border-radius: 12px; padding: 1rem; border: 1px solid rgba(255,255,255,0.2); }
    .cc-stat-box .label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; opacity: 0.8; margin-bottom: 0.5rem; }
    .cc-stat-box .label .icon { width: 1rem; height: 1rem; }
    .cc-stat-box .value { font-size: 1.125rem; font-weight: 700; }
    .cc-score-breakdown { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(4px); border-radius: 12px; padding: 1rem; border: 1px solid rgba(255,255,255,0.2); }
    .cc-score-breakdown .overall { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
    .cc-score-breakdown .overall .label { font-size: 0.875rem; opacity: 0.8; }
    .cc-score-breakdown .overall .value { font-size: 1.5rem; font-weight: 700; }
    .cc-score-breakdown .details { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem; }
    .cc-score-breakdown .details > div { display: flex; justify-content: space-between; }
    .cc-score-breakdown .details .label { opacity: 0.7; }
    .cc-score-breakdown .details .value { font-weight: 500; }
    .cc-actions-bar { padding: 1rem 1.5rem; margin-top: -1rem; position: relative; z-index: 10; }
    .cc-actions-bar-inner { background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid var(--cc-gray-100); padding: 1rem; display: flex; gap: 0.75rem; }
    .cc-btn { flex: 1; padding: 0.75rem; border-radius: 12px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; }
    .cc-btn .icon { width: 1.25rem; height: 1.25rem; }
    .cc-btn-primary { background-image: linear-gradient(to right, var(--cc-blue-600), var(--cc-purple-600)); color: white; }
    .cc-btn-primary:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .cc-btn-secondary { background-color: var(--cc-gray-100); color: var(--cc-gray-700); }
    .cc-btn-secondary:hover { background-color: var(--cc-gray-200); }
    .cc-tabs-nav { padding: 1rem 1.5rem; }
    .cc-tabs-nav-inner { background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--cc-gray-100); display: flex; overflow-x: auto; }
    .cc-tab-link { flex: 1; min-width: 0; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.25rem; border: none; background: transparent; cursor: pointer; color: var(--cc-gray-600); border-bottom: 2px solid transparent; }
    .cc-tab-link .icon { width: 1rem; height: 1rem; }
    .cc-tab-link:hover { background-color: var(--cc-gray-50); }
    .cc-tab-link.active { color: var(--cc-blue-600); border-color: var(--cc-blue-600); background-color: var(--cc-blue-50); }
    .cc-content-wrap { padding: 0 1.5rem 1.5rem; }
    .cc-tab-content { display: none; animation: fadeIn 0.5s; }
    .cc-tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .cc-section { background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--cc-gray-100); padding: 1.5rem; margin-bottom: 1.5rem; }
    .cc-section-title { font-size: 1.125rem; font-weight: 700; color: var(--cc-gray-800); margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .cc-section-title .icon { width: 1.25rem; height: 1.25rem; }
    .cc-overview-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .cc-overview-item { text-align: center; padding: 1rem; background-color: var(--cc-gray-50); border-radius: 12px; }
    .cc-overview-item .icon { width: 2rem; height: 2rem; margin: 0 auto 0.5rem; color: var(--cc-blue-500); }
    .cc-overview-item .label { font-size: 0.875rem; color: var(--cc-gray-600); }
    .cc-overview-item .value { font-weight: 500; color: var(--cc-gray-800); }
    .cc-pros-cons-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
    @media (min-width: 768px) { .cc-pros-cons-grid { grid-template-columns: 1fr 1fr; } }
    .cc-list ul { list-style: none; padding: 0; margin: 0; }
    .cc-list li { display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem; }
    .cc-list .icon { width: 1rem; height: 1rem; flex-shrink: 0; margin-top: 2px; }
    .cc-list .icon.pro { color: var(--cc-green-500); }
    .cc-list .icon.con { color: var(--cc-red-600); }
    .cc-list span { font-size: 0.875rem; color: var(--cc-gray-700); }
    .cc-best-for-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .cc-best-for-tags span { background-color: var(--cc-blue-50); color: var(--cc-blue-700); font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 99px; border: 1px solid var(--cc-blue-200); }
    .cc-feature-item { display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; border-radius: 12px; transition: background-color 0.2s; }
    .cc-feature-item:not(:last-child) { border-bottom: 1px solid var(--cc-gray-100); }
    .cc-feature-item:hover { background-color: var(--cc-gray-50); }
    .cc-feature-item .icon { width: 2rem; height: 2rem; color: var(--cc-blue-500); }
    .cc-feature-item .title { font-weight: 700; margin-bottom: 0.25rem; color: var(--cc-gray-800); }
    .cc-feature-item .desc { font-size: 0.875rem; color: var(--cc-gray-600); }
    .cc-data-table .item { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--cc-gray-100); }
    .cc-data-table .item:last-child { border-bottom: none; }
    .cc-data-table .label { color: var(--cc-gray-700); }
    .cc-data-table .value { font-weight: 500; color: var(--cc-gray-800); }
    .cc-data-table .value.positive { color: var(--cc-green-500); }
    .cc-data-table .value.negative { color: var(--cc-red-600); }
    .cc-bottom-cta { background-image: linear-gradient(to right, var(--cc-blue-600), var(--cc-purple-600)); border-radius: 16px; padding: 1.5rem; text-align: center; color: white; }
    .cc-bottom-cta h3 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.5rem; }
    .cc-bottom-cta p { opacity: 0.8; font-size: 0.875rem; margin: 0 0 1rem; }
    .cc-bottom-cta .cc-btn { background: white; color: var(--cc-blue-600); display: inline-flex; width: auto; padding: 0.75rem 2rem; }
</style>

<div class="cc-body">
    <!-- Breadcrumb Navigation -->
    <nav class="cc-breadcrumb" style="background: white; padding: 1rem 1.5rem; border-bottom: 1px solid var(--cc-gray-200);">
        <ol style="display: flex; align-items: center; gap: 0.5rem; margin: 0; padding: 0; list-style: none; font-size: 0.875rem; color: var(--cc-gray-600);">
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                <li style="display: flex; align-items: center; gap: 0.5rem;">
                    <?php if ($index > 0): ?>
                        <span style="color: var(--cc-gray-400);">›</span>
                    <?php endif; ?>
                    <?php if ($index < count($breadcrumbs) - 1): ?>
                        <a href="<?php echo esc_url($breadcrumb['url']); ?>" style="color: var(--cc-blue-600); text-decoration: none; hover: text-decoration: underline;">
                            <?php echo esc_html($breadcrumb['name']); ?>
                        </a>
                    <?php else: ?>
                        <span style="color: var(--cc-gray-800); font-weight: 500;">
                            <?php echo esc_html($breadcrumb['name']); ?>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <main id="primary" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
            <!-- HERO SECTION -->
            <div class="cc-hero theme-background <?php echo esc_attr($gradient_class); ?>">
                <div class="cc-hero-content">
                    <div class="cc-hero-header">
                        <div class="cc-hero-img-wrap">
                            <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url($post_id, 'medium'); ?>" alt="<?php echo esc_attr($card_name); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="cc-hero-title">
                            <h1><?php echo esc_html(get_bloginfo('name')); // Bank Name or Site Name ?></h1>
                            <p><?php echo esc_html($card_name); ?></p>
                            <div class="cc-rating-trending">
                                <div class="cc-rating">
                                    <?php echo ccm_get_icon('star', 'icon'); ?>
                                    <span><?php echo esc_html($rating); ?> (<?php echo esc_html($review_count); ?> reviews)</span>
                                </div>
                                <?php if ($trending) : ?>
                                <div class="cc-trending-badge">
                                    <?php echo ccm_get_icon('zap', 'icon'); ?>
                                    <span>TRENDING</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="cc-hero-stats">
                        <div class="cc-stat-box">
                            <div class="label"><?php echo ccm_get_icon('credit-card', 'icon'); ?><span>Annual Fee</span></div>
                            <p class="value"><?php echo esc_html(format_rupees($annual_fee)); ?></p>
                        </div>
                        <div class="cc-stat-box">
                            <div class="label"><?php echo ccm_get_icon('gift', 'icon'); ?><span>Welcome Bonus</span></div>
                            <p class="value"><?php echo esc_html($welcome_bonus); ?></p>
                        </div>
                    </div>

                    <div class="cc-score-breakdown">
                        <div class="overall">
                            <span class="label">Overall Score</span>
                            <span class="value"><?php echo esc_html($overall_score); ?>/5</span>
                        </div>
                        <div class="details">
                            <div><span class="label">Rewards</span><span class="value"><?php echo esc_html($reward_score); ?>/5</span></div>
                            <div><span class="label">Benefits</span><span class="value"><?php echo esc_html($benefits_score); ?>/5</span></div>
                            <div><span class="label">Fees</span><span class="value"><?php echo esc_html($fees_score); ?>/5</span></div>
                            <div><span class="label">Support</span><span class="value"><?php echo esc_html($support_score); ?>/5</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS BAR -->
            <div class="cc-actions-bar">
                <div class="cc-actions-bar-inner">
                    <a href="<?php echo $apply_link; ?>" target="_blank" rel="noopener sponsored" class="cc-btn cc-btn-primary">
                        <?php echo ccm_get_icon('zap', 'icon'); ?>
                        <span>Apply Now</span>
                    </a>
                    <button class="cc-btn cc-btn-secondary" onclick="alert('Calculator feature coming soon!');">
                        <?php echo ccm_get_icon('calculator', 'icon'); ?>
                        <span>Calculator</span>
                    </button>
                </div>
            </div>

            <!-- TABS NAVIGATION -->
            <div class="cc-tabs-nav">
                <div class="cc-tabs-nav-inner">
                    <button class="cc-tab-link active" data-tab="overview"><?php echo ccm_get_icon('overview', 'icon'); ?><span>Overview</span></button>
                    <button class="cc-tab-link" data-tab="features"><?php echo ccm_get_icon('features', 'icon'); ?><span>Features</span></button>
                    <button class="cc-tab-link" data-tab="rewards"><?php echo ccm_get_icon('rewards', 'icon'); ?><span>Rewards</span></button>
                    <button class="cc-tab-link" data-tab="fees"><?php echo ccm_get_icon('fees', 'icon'); ?><span>Fees</span></button>
                    <button class="cc-tab-link" data-tab="eligibility"><?php echo ccm_get_icon('eligibility', 'icon'); ?><span>Eligibility</span></button>
                </div>
            </div>

            <!-- TABS CONTENT -->
            <div class="cc-content-wrap">
                <!-- Overview Tab -->
                <div id="overview" class="cc-tab-content active">
                    <div class="cc-section">
                        <h3 class="cc-section-title">Quick Overview</h3>
                        <div class="cc-overview-grid">
                            <div class="cc-overview-item"><?php echo ccm_get_icon('credit-card', 'icon'); ?><p class="label">Network</p><p class="value"><?php echo esc_html($network_type); ?></p></div>
                            <div class="cc-overview-item"><?php echo ccm_get_icon('wallet', 'icon'); ?><p class="label">Credit Limit</p><p class="value"><?php echo esc_html($credit_limit); ?></p></div>
                            <div class="cc-overview-item"><?php echo ccm_get_icon('clock', 'icon'); ?><p class="label">Processing Time</p><p class="value"><?php echo esc_html($processing_time); ?></p></div>
                            <div class="cc-overview-item"><?php echo ccm_get_icon('trending-up', 'icon'); ?><p class="label">Min Income</p><p class="value"><?php echo esc_html($min_income); ?></p></div>
                        </div>
                    </div>
                    <div class="cc-pros-cons-grid">
                        <?php if (!empty($pros)): ?>
                        <div class="cc-section cc-list">
                            <h4 class="cc-section-title" style="color: var(--cc-green-500);"><?php echo ccm_get_icon('check-circle', 'icon'); ?><span>Pros</span></h4>
                            <ul><?php foreach ($pros as $pro) echo '<li><span class="icon pro">' . ccm_get_icon('check-circle') . '</span><span>' . esc_html($pro) . '</span></li>'; ?></ul>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($cons)): ?>
                        <div class="cc-section cc-list">
                            <h4 class="cc-section-title" style="color: var(--cc-red-700);"><?php echo ccm_get_icon('x-circle', 'icon'); ?><span>Cons</span></h4>
                            <ul><?php foreach ($cons as $con) echo '<li><span class="icon con">' . ccm_get_icon('x-circle') . '</span><span>' . esc_html($con) . '</span></li>'; ?></ul>
                        </div>
                        <?php endif; ?>
                    </div>
                     <?php if (!empty($best_for)): ?>
                    <div class="cc-section">
                        <h3 class="cc-section-title" style="color: var(--cc-blue-700);"><?php echo ccm_get_icon('target', 'icon'); ?><span>Best For</span></h3>
                        <div class="cc-best-for-tags"><?php foreach ($best_for as $item) echo '<span>' . esc_html($item) . '</span>'; ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Features Tab -->
                <div id="features" class="cc-tab-content">
                    <div class="cc-section">
                        <h3 class="cc-section-title">Key Features</h3>
                        <?php if (!empty($features)): foreach ($features as $feature): ?>
                        <div class="cc-feature-item">
                            <?php echo ccm_get_icon('gift', 'icon'); // Generic icon, can be dynamic ?>
                            <div>
                                <p class="title"><?php echo esc_html($feature['title']); ?></p>
                                <p class="desc"><?php echo esc_html($feature['description']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; else: echo '<p>No specific features listed.</p>'; endif; ?>
                    </div>
                </div>

                <!-- Rewards Tab -->
                <div id="rewards" class="cc-tab-content">
                    <div class="cc-section cc-data-table">
                        <h3 class="cc-section-title">Reward Program</h3>
                        <?php if (!empty($rewards)): foreach ($rewards as $reward): ?>
                        <div class="item">
                            <div>
                                <p class="label"><?php echo esc_html($reward['category']); ?></p>
                                <p style="font-size: 0.8rem; color: var(--cc-gray-600);"><?php echo esc_html($reward['description']); ?></p>
                            </div>
                            <span class="value positive"><?php echo esc_html($reward['rate']); ?></span>
                        </div>
                        <?php endforeach; else: echo '<p>No reward details available.</p>'; endif; ?>
                    </div>
                </div>

                <!-- Fees Tab -->
                <div id="fees" class="cc-tab-content">
                     <div class="cc-section cc-data-table">
                        <h3 class="cc-section-title">Fees & Charges</h3>
                        <div class="item"><span class="label">Joining Fee</span><span class="value negative"><?php echo esc_html(format_rupees($joining_fee)); ?></span></div>
                        <div class="item"><span class="label">Annual Fee</span><span class="value negative"><?php echo esc_html(format_rupees($annual_fee)); ?></span></div>
                        <?php if (!empty($fees)): foreach ($fees as $fee): ?>
                        <div class="item">
                            <span class="label"><?php echo esc_html($fee['type']); ?></span>
                            <span class="value negative"><?php echo esc_html($fee['amount']); ?></span>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Eligibility Tab -->
                <div id="eligibility" class="cc-tab-content">
                    <div class="cc-section cc-data-table">
                        <h3 class="cc-section-title">Eligibility Criteria</h3>
                         <?php if (!empty($eligibility)): foreach ($eligibility as $criterion): ?>
                         <div class="item">
                             <span class="label"><?php echo esc_html($criterion['criteria']); ?></span>
                             <span class="value"><?php echo esc_html($criterion['value']); ?></span>
                         </div>
                         <?php endforeach; else: echo '<p>No eligibility criteria listed.</p>'; endif; ?>
                    </div>
                    <?php if (!empty($documents)): ?>
                    <div class="cc-section cc-list">
                        <h3 class="cc-section-title"><?php echo ccm_get_icon('file-text', 'icon'); ?><span>Required Documents</span></h3>
                        <ul><?php foreach ($documents as $doc) echo '<li><span class="icon">' . ccm_get_icon('file-text') . '</span><span>' . esc_html($doc) . '</span></li>'; ?></ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Bottom CTA -->
                <div class="cc-bottom-cta">
                    <h3>Ready to Apply?</h3>
                    <p>Join thousands of satisfied customers and start earning rewards today!</p>
                     <a href="<?php echo $apply_link; ?>" target="_blank" rel="noopener sponsored" class="cc-btn">
                        <?php echo ccm_get_icon('zap', 'icon'); ?>
                        <span>Apply Now</span>
                        <?php echo ccm_get_icon('arrow-right', 'icon'); ?>
                    </a>
                </div>
                <div class="cc-section">
                    <?php
                    error_log('Comments open: ' . (comments_open() ? 'yes' : 'no'));
                    error_log('Number of comments: ' . get_comments_number());
                    // If comments are open or we have at least one comment, load up the comment template.
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;
                    ?>
                </div>
            </div>
        </article>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.cc-tab-link');
    const tabContents = document.querySelectorAll('.cc-tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tabId = link.getAttribute('data-tab');

            tabLinks.forEach(item => item.classList.remove('active'));
            link.classList.add('active');

            tabContents.forEach(content => {
                content.classList.toggle('active', content.id === tabId);
            });
        });
    });
});
</script>

<?php
get_footer();
