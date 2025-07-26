<?php
/**
 * Template part for displaying the credit card filter section.
 *
 * @package Credit Card Manager
 * 
 * Available variables:
 * $filters - Array of filter data from API
 * $current_* - Current filter values
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Set defaults if variables not provided
$filters = isset($filters) ? $filters : [];
$current_bank = isset($current_bank) ? $current_bank : '';
$current_network = isset($current_network) ? $current_network : '';
$current_category = isset($current_category) ? $current_category : '';
$current_min_rating = isset($current_min_rating) ? $current_min_rating : '';
$current_max_fee = isset($current_max_fee) ? $current_max_fee : '';
$current_featured = isset($current_featured) ? $current_featured : '';
$current_trending = isset($current_trending) ? $current_trending : '';
?>

<section class="ccv2-filter-section">
    <div class="ccv2-filter-header">
        <h2 class="ccv2-filter-title">
            <?php echo ccm_get_icon('filter', 'icon'); ?>
            Filter Credit Cards
        </h2>
        <button type="button" class="ccv2-filter-toggle" onclick="toggleFilters()">
            <?php echo ccm_get_icon('chevron-down', 'icon'); ?>
            <span>Toggle Filters</span>
        </button>
    </div>
    
    <form id="ccv2-filter-form" class="ccv2-filter-form" style="display: block;">
        <div class="ccv2-filter-grid">
            <!-- Bank Filter -->
            <div class="ccv2-filter-group">
                <label for="bank" class="ccv2-filter-label">Bank/Issuer</label>
                <select name="bank" id="bank" class="ccv2-filter-select">
                    <option value="">All Banks</option>
                    <?php if (!empty($filters['banks'])): ?>
                        <?php foreach ($filters['banks'] as $bank): ?>
                            <option value="<?php echo esc_attr($bank['slug']); ?>" <?php selected($current_bank, $bank['slug']); ?>>
                                <?php echo esc_html($bank['name']); ?> (<?php echo esc_html($bank['count']); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Network Type Filter -->
            <div class="ccv2-filter-group">
                <label for="network_type" class="ccv2-filter-label">Network Type</label>
                <select name="network_type" id="network_type" class="ccv2-filter-select">
                    <option value="">All Networks</option>
                    <?php if (!empty($filters['networks'])): ?>
                        <?php foreach ($filters['networks'] as $network): ?>
                            <option value="<?php echo esc_attr($network['slug']); ?>" <?php selected($current_network, $network['slug']); ?>>
                                <?php echo esc_html($network['name']); ?> (<?php echo esc_html($network['count']); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Category Filter -->
            <div class="ccv2-filter-group">
                <label for="category" class="ccv2-filter-label">Category</label>
                <select name="category" id="category" class="ccv2-filter-select">
                    <option value="">All Categories</option>
                    <?php if (!empty($filters['categories'])): ?>
                        <?php foreach ($filters['categories'] as $category): ?>
                            <option value="<?php echo esc_attr($category['slug']); ?>" <?php selected($current_category, $category['slug']); ?>>
                                <?php echo esc_html($category['name']); ?> (<?php echo esc_html($category['count']); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Minimum Rating Filter -->
            <div class="ccv2-filter-group">
                <label for="min_rating" class="ccv2-filter-label">Minimum Rating</label>
                <select name="min_rating" id="min_rating" class="ccv2-filter-select">
                    <option value="">Any Rating</option>
                    <option value="4" <?php selected($current_min_rating, '4'); ?>>4+ Stars</option>
                    <option value="3" <?php selected($current_min_rating, '3'); ?>>3+ Stars</option>
                    <option value="2" <?php selected($current_min_rating, '2'); ?>>2+ Stars</option>
                </select>
            </div>
            
            <!-- Maximum Annual Fee Filter -->
            <div class="ccv2-filter-group">
                <label for="max_annual_fee" class="ccv2-filter-label">Maximum Annual Fee</label>
                <select name="max_annual_fee" id="max_annual_fee" class="ccv2-filter-select">
                    <option value="">Any Fee</option>
                    <option value="0" <?php selected($current_max_fee, '0'); ?>>Free Cards</option>
                    <option value="1000" <?php selected($current_max_fee, '1000'); ?>>Up to ₹1,000</option>
                    <option value="5000" <?php selected($current_max_fee, '5000'); ?>>Up to ₹5,000</option>
                    <option value="10000" <?php selected($current_max_fee, '10000'); ?>>Up to ₹10,000</option>
                </select>
            </div>
            
            <!-- Featured Cards Filter -->
            <div class="ccv2-filter-group">
                <label for="featured" class="ccv2-filter-label">Card Type</label>
                <select name="featured" id="featured" class="ccv2-filter-select">
                    <option value="">All Cards</option>
                    <option value="1" <?php selected($current_featured, '1'); ?>>Featured Cards</option>
                    <option value="trending" <?php selected($current_trending, '1'); ?>>Trending Cards</option>
                </select>
            </div>
        </div>
        
        <!-- Filter Actions -->
        <div class="ccv2-filter-actions">
            <button type="button" class="ccv2-btn ccv2-btn-secondary" onclick="clearFilters()">
                <?php echo ccm_get_icon('x', 'icon'); ?>
                Clear Filters
            </button>
            <button type="submit" class="ccv2-btn ccv2-btn-primary">
                <?php echo ccm_get_icon('filter', 'icon'); ?>
                Apply Filters
            </button>
        </div>
    </form>
</section>

<script>
function toggleFilters() {
    const form = document.getElementById('ccv2-filter-form');
    const toggle = document.querySelector('.ccv2-filter-toggle');
    const icon = toggle.querySelector('.icon');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        icon.style.transform = 'rotate(0deg)';
    } else {
        form.style.display = 'none';
        icon.style.transform = 'rotate(-90deg)';
    }
}

function clearFilters() {
    const form = document.getElementById('ccv2-filter-form');
    const selects = form.querySelectorAll('select');
    selects.forEach(select => select.value = '');
    form.submit();
}

// Auto-submit form on change
document.getElementById('ccv2-filter-form').addEventListener('change', function() {
    this.submit();
});
</script>