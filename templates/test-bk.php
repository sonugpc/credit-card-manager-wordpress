<?php
/**
 * Archive Template for Credit Cards - v4 (Server-Side Render + AJAX)
 *
 * This template uses a hybrid approach:
 * 1. Initial page load is server-rendered with WP_Query for SEO and speed.
 * 2. All subsequent filtering, searching, and pagination are handled via AJAX
 *    for a seamless user experience.
 * 3. A large list layout is used for displaying cards.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// --- Data Fetching for Filters ---
$banks = get_terms(['taxonomy' => 'store', 'hide_empty' => true]);
$network_types = get_terms(['taxonomy' => 'network-type', 'hide_empty' => true]);
$categories = get_terms(['taxonomy' => 'category', 'hide_empty' => true]);
$rating_ranges = [['label' => '4+ Stars', 'value' => 4], ['label' => '3+ Stars', 'value' => 3], ['label' => '2+ Stars', 'value' => 2]];
$fee_ranges = [['label' => 'Any Fee', 'value' => ''], ['label' => 'Free', 'value' => 0], ['label' => 'Under ₹1,000', 'value' => 1000], ['label' => 'Under ₹2,500', 'value' => 2500]];

// --- Initial Query for Server-Side Rendering ---
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$initial_args = [
    'post_type' => 'credit-card',
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'paged' => $paged,
];
$cards_query = new WP_Query($initial_args);

// --- SEO Meta Tags ---
$archive_title = __('Credit Cards - Compare and Find the Best', 'credit-card-manager');
$archive_description = __('Explore our curated list of credit cards. Use our advanced filters to find the perfect card based on your needs, whether for rewards, travel, or cashback.', 'credit-card-manager');
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($archive_title); ?></title>
    <meta name="description" content="<?php echo esc_attr($archive_description); ?>">
    <?php wp_head(); ?>
</head>

<body <?php body_class('ccm-archive-page-v4'); ?>>

<div id="ccm-archive-wrapper">
    <header class="ccm-header">
        <div class="ccm-container">
            <h1 class="ccm-main-title"><?php echo esc_html__('Find Your Perfect Credit Card', 'credit-card-manager'); ?></h1>
            <p class="ccm-subtitle"><?php echo esc_html__('Search, filter, and compare to find the best card for you.', 'credit-card-manager'); ?></p>
            <div class="ccm-toolbar">
                <div class="ccm-search-bar">
                    <input type="search" id="ccm-search-input" name="s" placeholder="<?php esc_attr_e('Search by card name or bank...', 'credit-card-manager'); ?>">
                </div>
                <button id="ccm-filter-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                    <span><?php _e('Filters', 'credit-card-manager'); ?></span>
                </button>
            </div>
        </div>
    </header>

    <main id="ccm-main-content">
        <div class="ccm-container">
            <div id="ccm-cards-list" class="ccm-cards-list">
                <?php
                if ($cards_query->have_posts()) :
                    while ($cards_query->have_posts()) : $cards_query->the_post();
                        // Use a template part to keep the loop clean
                        get_template_part('template-parts/content', 'credit-card-list-item');
                    endwhile;
                else :
                    echo '<div class="ccm-no-results"><p>' . __('No credit cards found.', 'credit-card-manager') . '</p></div>';
                endif;
                ?>
            </div>
            <div id="ccm-loader" class="ccm-loader" style="display: none;"></div>
            <?php if ($cards_query->max_num_pages > 1) : ?>
                <div class="ccm-load-more-container">
                    <button id="ccm-load-more" data-page="2" data-max-pages="<?php echo $cards_query->max_num_pages; ?>">
                        <?php _e('Load More Cards', 'credit-card-manager'); ?>
                    </button>
                </div>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </main>
</div>

<!-- Filter Panel -->
<div id="ccm-filter-panel">
    <div class="ccm-filter-panel-header">
        <h3><?php _e('Filter Options', 'credit-card-manager'); ?></h3>
        <button id="ccm-filter-close">&times;</button>
    </div>
    <div class="ccm-filter-panel-body">
        <form id="ccm-filter-form">
            <!-- Render filter fields from PHP variables -->
            <?php if (!is_wp_error($banks) && !empty($banks)): ?>
            <div class="ccm-filter-group">
                <label for="filter-bank"><?php _e('Bank', 'credit-card-manager'); ?></label>
                <select name="bank" id="filter-bank"><option value=""><?php _e('All Banks', 'credit-card-manager'); ?></option><?php foreach ($banks as $bank) echo '<option value="' . esc_attr($bank->slug) . '">' . esc_html($bank->name) . '</option>'; ?></select>
            </div>
            <?php endif; ?>
            <?php if (!is_wp_error($network_types) && !empty($network_types)): ?>
            <div class="ccm-filter-group">
                <label for="filter-network"><?php _e('Network', 'credit-card-manager'); ?></label>
                <select name="network_type" id="filter-network"><option value=""><?php _e('All Networks', 'credit-card-manager'); ?></option><?php foreach ($network_types as $network) echo '<option value="' . esc_attr($network->slug) . '">' . esc_html($network->name) . '</option>'; ?></select>
            </div>
            <?php endif; ?>
            <div class="ccm-filter-group">
                <label for="filter-rating"><?php _e('Minimum Rating', 'credit-card-manager'); ?></label>
                <select name="min_rating" id="filter-rating"><option value=""><?php _e('Any Rating', 'credit-card-manager'); ?></option><?php foreach ($rating_ranges as $range) echo '<option value="' . esc_attr($range['value']) . '">' . esc_html($range['label']) . '</option>'; ?></select>
            </div>
            <div class="ccm-filter-group">
                <label for="filter-fee"><?php _e('Annual Fee', 'credit-card-manager'); ?></label>
                <select name="max_annual_fee" id="filter-fee"><?php foreach ($fee_ranges as $range) echo '<option value="' . esc_attr($range['value']) . '">' . esc_html($range['label']) . '</option>'; ?></select>
            </div>
            <div class="ccm-filter-actions">
                <button type="submit" class="ccm-btn-apply"><?php _e('Apply Filters', 'credit-card-manager'); ?></button>
                <button type="reset" class="ccm-btn-reset"><?php _e('Reset', 'credit-card-manager'); ?></button>
            </div>
        </form>
    </div>
</div>
<div id="ccm-filter-overlay"></div>

<style>
    :root { --ccm-primary-color: #007bff; --ccm-bg-color: #f7f8fa; --ccm-card-bg: #ffffff; --ccm-border-color: #dee2e6; --ccm-border-radius: 0.75rem; }
    body.ccm-archive-page-v4 { background-color: var(--ccm-bg-color); font-family: 'Inter', sans-serif; }
    .ccm-container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
    .ccm-header { padding: 40px 0; text-align: center; }
    .ccm-main-title { font-size: 2.5rem; font-weight: 700; }
    .ccm-subtitle { color: #6c757d; margin-bottom: 2rem; }
    .ccm-toolbar { display: flex; justify-content: center; gap: 1rem; }
    .ccm-search-bar { display: flex; flex-grow: 1; max-width: 450px; border: 1px solid var(--ccm-border-color); border-radius: 50px; background: var(--ccm-card-bg); }
    #ccm-search-input { border: none; background: transparent; padding: 12px 20px; width: 100%; font-size: 1rem; }
    #ccm-filter-toggle { border: 1px solid var(--ccm-border-color); background: var(--ccm-card-bg); border-radius: 50px; padding: 10px 20px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 500; }
    #ccm-main-content { padding: 20px 0 50px; }
    .ccm-cards-list { display: flex; flex-direction: column; gap: 1.5rem; }
    .ccm-card-list-item { display: flex; background: var(--ccm-card-bg); border-radius: var(--ccm-border-radius); box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; transition: all 0.2s ease; }
    .ccm-card-list-item:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
    .ccm-card-list-image { flex-shrink: 0; width: 150px; background-color: #eee; }
    .ccm-card-list-image img { width: 100%; height: 100%; object-fit: contain; padding: 10px; }
    .ccm-card-list-content { padding: 20px; flex-grow: 1; }
    .ccm-card-list-title { font-size: 1.3rem; font-weight: 600; margin: 0 0 10px; }
    .ccm-card-list-title a { text-decoration: none; color: #212529; }
    .ccm-card-list-meta { display: flex; gap: 20px; font-size: 0.9rem; color: #6c757d; margin-bottom: 15px; }
    .ccm-card-list-actions { margin-top: 20px; display: flex; gap: 10px; }
    .ccm-btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; }
    .ccm-btn-details { background: #f1f1f1; color: #333; }
    .ccm-btn-apply { background: var(--ccm-primary-color); color: white; }
    .ccm-load-more-container { text-align: center; margin-top: 30px; }
    #ccm-load-more { background: var(--ccm-primary-color); color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; cursor: pointer; }
    #ccm-filter-panel { position: fixed; top: 0; right: -380px; width: 360px; height: 100%; background: var(--ccm-card-bg); z-index: 1001; transition: right 0.3s ease; }
    #ccm-filter-panel.is-open { right: 0; }
    .ccm-filter-panel-header { display: flex; justify-content: space-between; padding: 20px; border-bottom: 1px solid var(--ccm-border-color); }
    #ccm-filter-close { background: none; border: none; font-size: 2rem; cursor: pointer; }
    .ccm-filter-panel-body { padding: 20px; overflow-y: auto; }
    .ccm-filter-group { margin-bottom: 1.5rem; }
    .ccm-filter-group select { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--ccm-border-color); }
    .ccm-filter-actions { display: flex; gap: 10px; }
    #ccm-filter-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 1000; opacity: 0; visibility: hidden; transition: opacity 0.3s; }
    #ccm-filter-overlay.is-visible { opacity: 1; visibility: visible; }
    .ccm-loader { margin: 40px auto; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--ccm-primary-color); border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('ccm-filter-form');
    const searchInput = document.getElementById('ccm-search-input');
    const list = document.getElementById('ccm-cards-list');
    const loader = document.getElementById('ccm-loader');
    const loadMoreBtn = document.getElementById('ccm-load-more');
    let currentPage = 1;
    let isAjaxLoading = false;

    const fetchCards = (reset = false) => {
        if (isAjaxLoading) return;
        isAjaxLoading = true;
        loader.style.display = 'block';
        if (loadMoreBtn) loadMoreBtn.style.display = 'none';
        if (reset) {
            list.innerHTML = '';
            currentPage = 1;
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        if (searchInput.value) params.set('s', searchInput.value);
        params.set('page', currentPage);
        params.set('per_page', 5);

        const apiUrl = `<?php echo rest_url('ccm/v1/credit-cards'); ?>?${params.toString()}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    data.data.forEach(card => list.insertAdjacentHTML('beforeend', renderCard(card)));
                    currentPage++;
                    if (loadMoreBtn) {
                        loadMoreBtn.dataset.page = currentPage;
                        if (currentPage > data.pagination.pages) {
                            loadMoreBtn.style.display = 'none';
                        } else {
                            loadMoreBtn.style.display = 'block';
                        }
                    }
                } else if (reset) {
                    list.innerHTML = '<div class="ccm-no-results"><p><?php _e('No cards match your criteria.', 'credit-card-manager'); ?></p></div>';
                    if(loadMoreBtn) loadMoreBtn.style.display = 'none';
                } else {
                     if(loadMoreBtn) loadMoreBtn.style.display = 'none';
                }
            })
            .finally(() => {
                loader.style.display = 'none';
                isAjaxLoading = false;
            });
    };

    const renderCard = (card) => {
        return `
            <div class="ccm-card-list-item">
                <div class="ccm-card-list-image">
                    <a href="${card.link}"><img src="${card.card_image || ''}" alt="${card.title}"></a>
                </div>
                <div class="ccm-card-list-content">
                    <h3 class="ccm-card-list-title"><a href="${card.link}">${card.title}</a></h3>
                    <div class="ccm-card-list-meta">
                        <span><strong>Bank:</strong> ${card.bank ? card.bank.name : 'N/A'}</span>
                        <span><strong>Rating:</strong> ⭐ ${card.rating || 'N/A'}</span>
                    </div>
                    <p><strong>Annual Fee:</strong> ${card.annual_fee || 'N/A'}</p>
                    <div class="ccm-card-list-actions">
                        <a href="${card.link}" class="ccm-btn ccm-btn-details">View Details</a>
                        <a href="${card.apply_link}" target="_blank" rel="noopener noreferrer" class="ccm-btn ccm-btn-apply">Apply Now</a>
                    </div>
                </div>
            </div>
        `;
    };

    // --- Event Listeners ---
    let searchTimeout;
    searchInput.addEventListener('keyup', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchCards(true), 500);
    });

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchCards(true);
        closePanel();
    });

    filterForm.addEventListener('reset', () => {
        searchInput.value = '';
        setTimeout(() => fetchCards(true), 0);
    });

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => fetchCards(false));
    }

    // Panel UI
    const filterToggle = document.getElementById('ccm-filter-toggle');
    const filterPanel = document.getElementById('ccm-filter-panel');
    const filterClose = document.getElementById('ccm-filter-close');
    const filterOverlay = document.getElementById('ccm-filter-overlay');
    const openPanel = () => { filterPanel.classList.add('is-open'); filterOverlay.classList.add('is-visible'); };
    const closePanel = () => { filterPanel.classList.remove('is-open'); filterOverlay.classList.remove('is-visible'); };
    filterToggle.addEventListener('click', openPanel);
    filterClose.addEventListener('click', closePanel);
    filterOverlay.addEventListener('click', closePanel);
});
</script>

<?php get_footer(); ?>
</body>
</html>
