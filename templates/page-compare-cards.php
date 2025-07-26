<?php
/**
 * Template for Credit Card Comparison Page
 * Professional design for detailed card comparisons
 * 
 * @package Credit Card Manager
 */

get_header();

// Get card IDs from URL parameter
$card_ids = isset($_GET['cards']) ? explode(',', sanitize_text_field($_GET['cards'])) : [];
$card_ids = array_filter($card_ids, 'is_numeric'); // Ensure all IDs are numeric

// Query for the cards to compare
$compare_cards = [];
if (!empty($card_ids)) {
    $args = [
        'post_type' => 'credit-card',
        'post__in' => $card_ids,
        'orderby' => 'post__in',
        'posts_per_page' => -1,
    ];
    
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $compare_cards[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'data' => get_post_meta(get_the_ID())
            ];
        }
        wp_reset_postdata();
    }
}

// SEO and Dynamic Content
$card_titles = wp_list_pluck($compare_cards, 'title');
$page_title = 'Credit Card Comparison';
if (count($card_titles) > 1) {
    $page_title = implode(' vs ', $card_titles);
}

$meta_description = 'Compare ' . $page_title . ' on key features like annual fees, rewards, interest rates, and more. Make an informed decision with our detailed side-by-side comparison.';
$canonical_url = get_permalink();

$dynamic_description = 'Choosing the right credit card can be overwhelming. Our comparison tool helps you analyze the key benefits and features of the most popular credit cards, so you can find the one that best fits your spending habits and financial goals. Compare rewards, fees, and interest rates to make a confident decision.';
if (count($card_titles) > 1) {
    $dynamic_description = 'Comparing ' . $page_title . '? This page provides a detailed side-by-side analysis of their features, benefits, and fees. Discover which card offers the best rewards, lowest fees, and most valuable perks to help you make an informed financial decision.';
}
?>

<!-- SEO Meta Tags -->
<title><?php echo esc_html($page_title); ?></title>
<meta name="description" content="<?php echo esc_attr($meta_description); ?>">
<link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo esc_url($canonical_url); ?>">
<meta property="og:title" content="<?php echo esc_attr($page_title); ?>">
<meta property="og:description" content="<?php echo esc_attr($meta_description); ?>">
<?php if (!empty($compare_cards) && has_post_thumbnail($compare_cards[0]['id'])): ?>
    <meta property="og:image" content="<?php echo esc_url(get_the_post_thumbnail_url($compare_cards[0]['id'], 'large')); ?>">
<?php endif; ?>

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?php echo esc_url($canonical_url); ?>">
<meta property="twitter:title" content="<?php echo esc_attr($page_title); ?>">
<meta property="twitter:description" content="<?php echo esc_attr($meta_description); ?>">
<?php if (!empty($compare_cards) && has_post_thumbnail($compare_cards[0]['id'])): ?>
    <meta property="twitter:image" content="<?php echo esc_url(get_the_post_thumbnail_url($compare_cards[0]['id'], 'large')); ?>">
<?php endif; ?>

<div class="compare-container">
    <!-- Header Section -->
    <header class="compare-header">
        <div class="compare-header-content">
            <h1><?php echo esc_html($page_title); ?></h1>
            <p><?php echo esc_html($dynamic_description); ?></p>
            
            <div class="compare-header-actions">
                <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="compare-btn compare-btn-primary">
                    <?php echo ccm_get_icon('arrow-left', 'icon'); ?>
                    Back to All Cards
                </a>
                <button onclick="window.print()" class="compare-btn compare-btn-secondary">
                    <?php echo ccm_get_icon('external-link', 'icon'); ?>
                    Print Comparison
                </button>
            </div>
        </div>
    </header>

    <?php if (!empty($compare_cards)): ?>
        <!-- Cards Overview -->
        <section class="compare-overview">
            <?php foreach ($compare_cards as $card): ?>
                <?php 
                $card_image = ccm_get_meta($card['id'], 'card_image_url', '');
                if (empty($card_image) && has_post_thumbnail($card['id'])) {
                    $card_image = get_the_post_thumbnail_url($card['id'], 'medium');
                }
                
                $rating = ccm_get_meta($card['id'], 'rating', 0, true);
                $review_count = ccm_get_meta($card['id'], 'review_count', 0, true);
                $annual_fee = ccm_get_meta($card['id'], 'annual_fee', 'N/A');
                $cashback_rate = ccm_get_meta($card['id'], 'cashback_rate', 'N/A');
                
                $bank_terms = get_the_terms($card['id'], 'store');
                $bank_name = !is_wp_error($bank_terms) && !empty($bank_terms) ? $bank_terms[0]->name : '';
                ?>
                <div class="compare-card-preview">
                    <div class="compare-card-image">
                        <?php if (!empty($card_image)): ?>
                            <img src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr($card['title']); ?>">
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="compare-card-title"><?php echo esc_html($card['title']); ?></h3>
                    
                    <?php if (!empty($bank_name)): ?>
                        <div class="compare-card-bank"><?php echo esc_html($bank_name); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($rating > 0): ?>
                        <div class="compare-card-rating">
                            <div class="compare-rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php echo ccm_get_icon('star', 'icon'); ?>
                                <?php endfor; ?>
                            </div>
                            <span class="compare-rating-text">
                                <?php echo esc_html($rating); ?>/5
                                <?php if ($review_count > 0): ?>
                                    (<?php echo esc_html($review_count); ?>)
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="compare-quick-stats">
                        <div class="compare-stat">
                            <div class="compare-stat-label">Annual Fee</div>
                            <div class="compare-stat-value"><?php echo esc_html($annual_fee); ?></div>
                        </div>
                        <div class="compare-stat">
                            <div class="compare-stat-label">Reward Rate</div>
                            <div class="compare-stat-value"><?php echo esc_html($cashback_rate); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Detailed Comparison Table -->
        <?php include(plugin_dir_path(__FILE__) . 'template-parts/compare-table.php'); ?>
        
    <?php else: ?>
        <!-- No Cards Message -->
        <section class="compare-no-cards">
            <?php echo ccm_get_icon('info', 'compare-no-cards-icon'); ?>
            <h2>No Cards Selected for Comparison</h2>
            <p>It looks like you haven't selected any cards to compare. Please select at least two cards from our catalog to see a detailed comparison.</p>
            <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="compare-btn compare-btn-primary">
                Browse Credit Cards
            </a>
        </section>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading states to apply buttons
    document.querySelectorAll('.compare-btn-apply').forEach(btn => {
        btn.addEventListener('click', function() {
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';
            setTimeout(() => {
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            }, 2000);
        });
    });
    
    // Add table responsive behavior
    const tableWrapper = document.querySelector('.compare-table-wrapper');
    if (tableWrapper) {
        let isScrolling = false;
        tableWrapper.addEventListener('scroll', function() {
            if (!isScrolling) {
                tableWrapper.style.cursor = 'grabbing';
                isScrolling = true;
                setTimeout(() => {
                    tableWrapper.style.cursor = 'grab';
                    isScrolling = false;
                }, 150);
            }
        });
    }
});
</script>

<!-- JSON-LD Schema Markup -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ComparisonPage",
    "name": "<?php echo esc_attr($page_title); ?>",
    "description": "<?php echo esc_attr($meta_description); ?>",
    "mainEntity": [
        <?php foreach ($compare_cards as $index => $card): ?>
        {
            "@type": "Product",
            "name": "<?php echo esc_attr($card['title']); ?>",
            "image": "<?php echo esc_url(get_the_post_thumbnail_url($card['id'], 'large')); ?>",
            "description": "<?php echo esc_attr(get_the_excerpt($card['id'])); ?>",
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "<?php echo esc_attr(ccm_get_meta($card['id'], 'rating', '0', true)); ?>",
                "reviewCount": "<?php echo esc_attr(ccm_get_meta($card['id'], 'review_count', '0', true)); ?>"
            },
            "offers": {
                "@type": "Offer",
                "url": "<?php echo esc_url(ccm_get_meta($card['id'], 'apply_link', '#')); ?>",
                "priceCurrency": "INR",
                "price": "<?php echo esc_attr(preg_replace('/[^0-9.]/', '', ccm_get_meta($card['id'], 'annual_fee', '0'))); ?>"
            }
        }<?php echo ($index < count($compare_cards) - 1) ? ',' : ''; ?>
        <?php endforeach; ?>
    ]
}
</script>

<?php get_footer(); ?>
