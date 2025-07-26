<?php
/**
 * Template part for displaying pagination.
 *
 * @package Credit Card Manager
 * 
 * Available variables:
 * $total_pages - Total number of pages
 * $current_page - Current page number
 * $base_url - Base URL for pagination links (optional)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Set defaults if variables not provided
$total_pages = isset($total_pages) ? $total_pages : 1;
$current_page = isset($current_page) ? $current_page : 1;
$base_url = isset($base_url) ? $base_url : '';

// Don't show pagination if there's only one page
if ($total_pages <= 1) {
    return;
}

// Calculate page range to show
$range = 2; // Show 2 pages before and after current page
$start_page = max(1, $current_page - $range);
$end_page = min($total_pages, $current_page + $range);

// Helper function to build page URL
function build_page_url($page, $base_url = '') {
    if (!empty($base_url)) {
        return add_query_arg('paged', $page, $base_url);
    }
    
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return add_query_arg('paged', $page, $current_url);
}
?>

<nav class="ccv2-pagination" role="navigation" aria-label="Credit cards pagination">
    <!-- Previous Page -->
    <?php if ($current_page > 1): ?>
        <a href="<?php echo esc_url(build_page_url($current_page - 1, $base_url)); ?>" 
           class="ccv2-pagination-link ccv2-pagination-prev"
           aria-label="Previous page">
            <?php echo ccm_get_icon('arrow-left', 'icon'); ?>
        </a>
    <?php else: ?>
        <span class="ccv2-pagination-link ccv2-pagination-prev disabled" aria-hidden="true">
            <?php echo ccm_get_icon('arrow-left', 'icon'); ?>
        </span>
    <?php endif; ?>
    
    <!-- First Page -->
    <?php if ($start_page > 1): ?>
        <a href="<?php echo esc_url(build_page_url(1, $base_url)); ?>" 
           class="ccv2-pagination-link"
           aria-label="Page 1">1</a>
        
        <?php if ($start_page > 2): ?>
            <span class="ccv2-pagination-ellipsis" aria-hidden="true">...</span>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Page Numbers -->
    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
        <?php if ($i == $current_page): ?>
            <span class="ccv2-pagination-link active" aria-current="page" aria-label="Current page, page <?php echo $i; ?>">
                <?php echo $i; ?>
            </span>
        <?php else: ?>
            <a href="<?php echo esc_url(build_page_url($i, $base_url)); ?>" 
               class="ccv2-pagination-link"
               aria-label="Page <?php echo $i; ?>">
                <?php echo $i; ?>
            </a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <!-- Last Page -->
    <?php if ($end_page < $total_pages): ?>
        <?php if ($end_page < $total_pages - 1): ?>
            <span class="ccv2-pagination-ellipsis" aria-hidden="true">...</span>
        <?php endif; ?>
        
        <a href="<?php echo esc_url(build_page_url($total_pages, $base_url)); ?>" 
           class="ccv2-pagination-link"
           aria-label="Last page, page <?php echo $total_pages; ?>">
            <?php echo $total_pages; ?>
        </a>
    <?php endif; ?>
    
    <!-- Next Page -->
    <?php if ($current_page < $total_pages): ?>
        <a href="<?php echo esc_url(build_page_url($current_page + 1, $base_url)); ?>" 
           class="ccv2-pagination-link ccv2-pagination-next"
           aria-label="Next page">
            <?php echo ccm_get_icon('arrow-right', 'icon'); ?>
        </a>
    <?php else: ?>
        <span class="ccv2-pagination-link ccv2-pagination-next disabled" aria-hidden="true">
            <?php echo ccm_get_icon('arrow-right', 'icon'); ?>
        </span>
    <?php endif; ?>
</nav>

<!-- Pagination Info -->
<div class="ccv2-pagination-info" aria-live="polite">
    Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
</div>