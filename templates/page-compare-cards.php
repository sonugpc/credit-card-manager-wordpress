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
$page_title = 'Credit Card Comparison Tool - Compare Best Cards';
if (count($card_titles) > 1) {
    $page_title = implode(' vs ', $card_titles) . ' - Credit Card Comparison';
}

$meta_description = 'Compare credit cards side-by-side with our detailed comparison tool. Analyze fees, rewards, benefits, and interest rates to find the perfect card for your needs.';
if (count($card_titles) > 1) {
    $meta_description = 'Compare ' . implode(' vs ', $card_titles) . ' credit cards. Detailed side-by-side comparison of fees, rewards, benefits, and features to help you choose the best card.';
}

$canonical_url = get_permalink();
if (!empty($card_ids)) {
    $canonical_url = add_query_arg('cards', implode(',', $card_ids), get_permalink());
}

$dynamic_description = 'Choosing the right credit card can be overwhelming. Our comparison tool helps you analyze the key benefits and features of the most popular credit cards, so you can find the one that best fits your spending habits and financial goals. Compare rewards, fees, and interest rates to make a confident decision.';
if (count($card_titles) > 1) {
    $dynamic_description = 'Comparing ' . implode(' vs ', $card_titles) . '? This page provides a detailed side-by-side analysis of their features, benefits, and fees. Discover which card offers the best rewards, lowest fees, and most valuable perks to help you make an informed financial decision.';
}

// Additional SEO data
$keywords = 'credit card comparison, compare credit cards, ' . implode(', ', $card_titles) . ', best credit cards, fees comparison, rewards comparison';
$breadcrumbs = [
    ['name' => 'Home', 'url' => home_url()],
    ['name' => 'Credit Cards', 'url' => get_post_type_archive_link('credit-card')],
    ['name' => 'Compare Cards', 'url' => $canonical_url]
];
?>


<!-- Breadcrumb Navigation -->
<nav style="background: white; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
    <ol style="display: flex; align-items: center; gap: 0.5rem; margin: 0; padding: 0; list-style: none; font-size: 0.875rem; color: #6b7280;">
        <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
            <li style="display: flex; align-items: center; gap: 0.5rem;">
                <?php if ($index > 0): ?>
                    <span style="color: #9ca3af;">›</span>
                <?php endif; ?>
                <?php if ($index < count($breadcrumbs) - 1): ?>
                    <a href="<?php echo esc_url($breadcrumb['url']); ?>" style="color: #2563eb; text-decoration: none;">
                        <?php echo esc_html($breadcrumb['name']); ?>
                    </a>
                <?php else: ?>
                    <span style="color: #1f2937; font-weight: 500;">
                        <?php echo esc_html($breadcrumb['name']); ?>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>

<!-- Social media tags handled by SEO plugin or output conditionally -->

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
                $card_image = has_post_thumbnail($card['id']) ? get_the_post_thumbnail_url($card['id'], 'medium') : '';
                
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
    "@type": "WebPage",
    "name": "<?php echo esc_attr($page_title); ?>",
    "description": "<?php echo esc_attr($meta_description); ?>",
    "url": "<?php echo esc_url($canonical_url); ?>",
    "breadcrumb": {
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
    },
    "mainEntity": {
        "@type": "ItemList",
        "name": "Credit Card Comparison",
        "description": "Compare multiple credit cards side by side",
        "numberOfItems": "<?php echo count($compare_cards); ?>",
        "itemListElement": [
            <?php foreach ($compare_cards as $index => $card): ?>
            {
                "@type": "ListItem",
                "position": <?php echo $index + 1; ?>,
                "item": {
                    "@type": "FinancialProduct",
                    "name": "<?php echo esc_attr($card['title']); ?>",
                    "url": "<?php echo esc_url(get_permalink($card['id'])); ?>",
                    "image": "<?php echo esc_url(get_the_post_thumbnail_url($card['id'], 'large')); ?>",
                    "description": "<?php echo esc_attr(wp_strip_all_tags(get_the_excerpt($card['id']))); ?>",
                    "category": "Credit Card",
                    "brand": {
                        "@type": "Brand",
                        "name": "<?php 
                        $bank_terms = get_the_terms($card['id'], 'store');
                        echo esc_attr((!is_wp_error($bank_terms) && !empty($bank_terms)) ? $bank_terms[0]->name : '');
                        ?>"
                    },
                    "aggregateRating": {
                        "@type": "AggregateRating",
                        "ratingValue": "<?php echo esc_attr(ccm_get_meta($card['id'], 'rating', '0', true)); ?>",
                        "reviewCount": "<?php echo esc_attr(ccm_get_meta($card['id'], 'review_count', '0', true)); ?>",
                        "bestRating": "5",
                        "worstRating": "1"
                    },
                    "offers": {
                        "@type": "Offer",
                        "url": "<?php echo esc_url(ccm_get_meta($card['id'], 'apply_link', '#')); ?>",
                        "priceCurrency": "INR",
                        "price": "<?php echo esc_attr(preg_replace('/[^0-9.]/', '', ccm_get_meta($card['id'], 'annual_fee', '0'))); ?>",
                        "priceValidUntil": "<?php echo date('Y-12-31'); ?>",
                        "availability": "https://schema.org/InStock"
                    },
                    "additionalProperty": [
                        {
                            "@type": "PropertyValue",
                            "name": "Annual Fee",
                            "value": "₹<?php echo esc_attr(number_format(ccm_get_meta($card['id'], 'annual_fee', 0, true))); ?>"
                        },
                        {
                            "@type": "PropertyValue",
                            "name": "Welcome Bonus",
                            "value": "<?php echo esc_attr(ccm_get_meta($card['id'], 'welcome_bonus', 'N/A')); ?>"
                        }
                    ]
                }
            }<?php echo ($index < count($compare_cards) - 1) ? ',' : ''; ?>
            <?php endforeach; ?>
        ]
    }
}
</script>

<?php
// Generate dynamic FAQ schema based on compared cards
if (!empty($compare_cards)) {
    $all_comparison_faqs = array();
    
    // Add generic comparison FAQs
    $all_comparison_faqs[] = array(
        'question' => 'How do I compare credit cards effectively?',
        'answer' => 'To compare credit cards effectively, focus on key factors like annual fees, interest rates, reward programs, welcome bonuses, and additional benefits. Consider your spending patterns and financial goals to choose the card that offers the most value for your lifestyle.'
    );
    
    // Generate FAQs specific to the cards being compared
    foreach ($compare_cards as $card) {
        $card_faqs = ccm_get_card_faqs($card['id']);
        if (!empty($card_faqs)) {
            // Take only the first 2 FAQs from each card to avoid too many
            $all_comparison_faqs = array_merge($all_comparison_faqs, array_slice($card_faqs, 0, 2));
        }
    }
    
    // Add comparison-specific FAQs
    if (count($compare_cards) > 1) {
        $card_names = wp_list_pluck($compare_cards, 'title');
        $all_comparison_faqs[] = array(
            'question' => 'What are the key differences between ' . implode(' and ', $card_names) . '?',
            'answer' => 'The main differences lie in annual fees, reward structures, welcome bonuses, and target customer segments. Compare the fee structures, earning rates, and benefits that align with your spending patterns to make the best choice.'
        );
    }
    
    // Generate and output FAQ schema
    $faq_schema = ccm_generate_faq_schema($all_comparison_faqs);
    if ($faq_schema) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        echo '</script>' . "\n";
    }
}
?>

<?php get_footer(); ?>
