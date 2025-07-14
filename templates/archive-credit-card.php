<?php
/**
 * The template for displaying Credit Card archive pages
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
$current_sort = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'rating';
$current_order = isset($_GET['sort_order']) ? sanitize_text_field($_GET['sort_order']) : 'desc';

// Check if we're in comparison mode
$compare_mode = isset($_GET['compare']) && !empty($_GET['compare']);
$compare_ids = $compare_mode ? explode(',', sanitize_text_field($_GET['compare'])) : [];

// Get filter data from API
$filters_response = wp_remote_get(rest_url('ccm/v1/credit-cards/filters'));
$filters = !is_wp_error($filters_response) ? json_decode(wp_remote_retrieve_body($filters_response), true) : [];

// Build query args for credit cards
$args = [
    'post_type' => 'credit-card',
    'posts_per_page' => $compare_mode ? -1 : 12,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    'meta_query' => [],
    'tax_query' => [],
];

// Add filters to query
if (!empty($current_bank)) {
    $args['tax_query'][] = [
        'taxonomy' => 'store',
        'field' => 'slug',
        'terms' => $current_bank,
    ];
}

if (!empty($current_network)) {
    $args['tax_query'][] = [
        'taxonomy' => 'network-type',
        'field' => 'slug',
        'terms' => $current_network,
    ];
}

if (!empty($current_category)) {
    $args['tax_query'][] = [
        'taxonomy' => 'category',
        'field' => 'slug',
        'terms' => $current_category,
    ];
}

if (!empty($current_min_rating)) {
    $args['meta_query'][] = [
        'key' => 'rating',
        'value' => floatval($current_min_rating),
        'compare' => '>=',
        'type' => 'DECIMAL',
    ];
}

if (!empty($current_max_fee)) {
    $args['meta_query'][] = [
        'key' => 'annual_fee_numeric',
        'value' => intval($current_max_fee),
        'compare' => '<=',
        'type' => 'NUMERIC',
    ];
}

if ($current_featured === '1') {
    $args['meta_query'][] = [
        'key' => 'featured',
        'value' => '1',
        'compare' => '=',
    ];
}

if ($current_trending === '1') {
    $args['meta_query'][] = [
        'key' => 'trending',
        'value' => '1',
        'compare' => '=',
    ];
}

// For comparison mode, get specific cards
if ($compare_mode && !empty($compare_ids)) {
    $args['post__in'] = $compare_ids;
    $args['orderby'] = 'post__in';
    unset($args['meta_query']);
    unset($args['tax_query']);
} else {
    // Sorting
    switch ($current_sort) {
        case 'rating':
            $args['meta_key'] = 'rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($current_order);
            break;
        case 'annual_fee':
            $args['meta_key'] = 'annual_fee_numeric';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($current_order);
            break;
        case 'review_count':
            $args['meta_key'] = 'review_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = strtoupper($current_order);
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = strtoupper($current_order);
    }
}

// Set relation for meta_query if multiple conditions
if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
    $args['meta_query']['relation'] = 'AND';
}

// Set relation for tax_query if multiple conditions
if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
    $args['tax_query']['relation'] = 'AND';
}

// Run the query
$credit_cards = new WP_Query($args);

// Helper function to get meta values
function get_cc_meta($post_id, $key, $default = '', $is_numeric = false, $unserialize = false) {
    $value = get_post_meta($post_id, $key, true);
    if ($unserialize) {
        return maybe_unserialize($value) ?: $default;
    }
    return $is_numeric ? (is_numeric($value) ? $value : $default) : $value;
}

// Helper function for SVG icons
function get_cc_icon($name, $classes = '') {
    $icons = [
        'star' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z"/></svg>',
        'filter' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>',
        'sort' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5h10"></path><path d="M11 9h7"></path><path d="M11 13h4"></path><path d="M3 17h18"></path><path d="M3 5l4 4"></path><path d="M7 5v12"></path></svg>',
        'check' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        'plus' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
        'minus' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
        'arrow-right' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
        'x' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        'credit-card' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
        'gift' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>',
        'compare' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line><line x1="6" y1="8" x2="6" y2="4"></line></svg>',
    ];
    return isset($icons[$name]) ? $icons[$name] : '';
}
?>

<style>
:root {
    --cc-white: #ffffff;
    --cc-gray-50: #f9fafb;
    --cc-gray-100: #f3f4f6;
    --cc-gray-200: #e5e7eb;
    --cc-gray-300: #d1d5db;
    --cc-gray-400: #9ca3af;
    --cc-gray-500: #6b7280;
    --cc-gray-600: #4b5563;
    --cc-gray-700: #374151;
    --cc-gray-800: #1f2937;
    --cc-gray-900: #111827;
    --cc-blue-50: #eff6ff;
    --cc-blue-100: #dbeafe;
    --cc-blue-500: #3b82f6;
    --cc-blue-600: #2563eb;
    --cc-blue-700: #1d4ed8;
    --cc-green-500: #22c55e;
    --cc-red-500: #ef4444;
    --cc-yellow-400: #facc15;
    --cc-purple-600: #9333ea;
    --cc-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --cc-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --cc-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --cc-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --cc-radius-sm: 0.25rem;
    --cc-radius: 0.5rem;
    --cc-radius-md: 0.75rem;
    --cc-radius-lg: 1rem;
    --cc-radius-xl: 1.5rem;
}

/* Main Container */
.cc-archive-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.cc-archive-header {
    margin-bottom: 2rem;
    text-align: center;
}

.cc-archive-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--cc-gray-900);
    margin-bottom: 0.5rem;
}

.cc-archive-description {
    color: var(--cc-gray-600);
    max-width: 700px;
    margin: 0 auto;
}

/* Filter Section */
.cc-filter-section {
    background-color: var(--cc-white);
    border-radius: var(--cc-radius-xl);
    box-shadow: var(--cc-shadow-md);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--cc-gray-100);
}

.cc-filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--cc-gray-200);
}

.cc-filter-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--cc-gray-800);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cc-filter-title .icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--cc-blue-600);
}

.cc-filter-toggle {
    background: none;
    border: none;
    color: var(--cc-blue-600);
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
}

.cc-filter-toggle .icon {
    width: 1rem;
    height: 1rem;
}

.cc-filter-content {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.cc-filter-group {
    margin-bottom: 1rem;
}

.cc-filter-label {
    display: block;
    font-weight: 500;
    color: var(--cc-gray-700);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.cc-filter-select {
    width: 100%;
    padding: 0.625rem 0.75rem;
    border: 1px solid var(--cc-gray-300);
    border-radius: var(--cc-radius);
    background-color: var(--cc-white);
    color: var(--cc-gray-800);
    font-size: 0.875rem;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 1rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.cc-filter-select:focus {
    border-color: var(--cc-blue-500);
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}

.cc-filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--cc-gray-200);
}

.cc-btn {
    padding: 0.625rem 1rem;
    border-radius: var(--cc-radius);
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
}

.cc-btn-primary {
    background-color: var(--cc-blue-600);
    color: var(--cc-white);
    border: none;
}

.cc-btn-primary:hover {
    background-color: var(--cc-blue-700);
}

.cc-btn-secondary {
    background-color: var(--cc-white);
    color: var(--cc-gray-700);
    border: 1px solid var(--cc-gray-300);
}

.cc-btn-secondary:hover {
    background-color: var(--cc-gray-50);
}

.cc-btn .icon {
    width: 1rem;
    height: 1rem;
}

/* Sort Bar */
.cc-sort-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.cc-results-count {
    font-size: 0.875rem;
    color: var(--cc-gray-600);
}

.cc-sort-options {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.cc-sort-label {
    font-size: 0.875rem;
    color: var(--cc-gray-600);
}

.cc-sort-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--cc-gray-300);
    border-radius: var(--cc-radius);
    background-color: var(--cc-white);
    color: var(--cc-gray-800);
    font-size: 0.875rem;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 1rem;
}

.cc-sort-select:focus {
    border-color: var(--cc-blue-500);
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}

/* Cards Grid */
.cc-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.cc-card {
    background-color: var(--cc-white);
    border-radius: var(--cc-radius-xl);
    box-shadow: var(--cc-shadow);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid var(--cc-gray-100);
    position: relative;
}

.cc-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--cc-shadow-lg);
}

.cc-card-image {
    height: 180px;
    position: relative;
    overflow: hidden;
    background-color: var(--cc-gray-100);
}

.cc-card-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 1rem;
}

.cc-card-badges {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.cc-badge {
    padding: 0.25rem 0.5rem;
    border-radius: var(--cc-radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--cc-white);
}

.cc-badge-featured {
    background-color: var(--cc-blue-600);
}

.cc-badge-trending {
    background-color: var(--cc-red-500);
}

.cc-card-content {
    padding: 1.25rem;
}

.cc-card-header {
    margin-bottom: 1rem;
}

.cc-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--cc-gray-900);
    margin: 0 0 0.25rem;
}

.cc-card-bank {
    font-size: 0.875rem;
    color: var(--cc-gray-600);
}

.cc-card-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.5rem;
}

.cc-card-rating .icon {
    width: 1rem;
    height: 1rem;
    color: var(--cc-yellow-400);
}

.cc-card-rating-text {
    font-size: 0.875rem;
    color: var(--cc-gray-700);
    font-weight: 500;
}

.cc-card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.cc-card-detail {
    display: flex;
    flex-direction: column;
}

.cc-card-detail-label {
    font-size: 0.75rem;
    color: var(--cc-gray-500);
    margin-bottom: 0.25rem;
}

.cc-card-detail-value {
    font-size: 0.875rem;
    color: var(--cc-gray-800);
    font-weight: 500;
}

.cc-card-features {
    margin-bottom: 1rem;
}

.cc-card-feature {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.cc-card-feature .icon {
    width: 1rem;
    height: 1rem;
    color: var(--cc-green-500);
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.cc-card-feature-text {
    font-size: 0.875rem;
    color: var(--cc-gray-700);
}

.cc-card-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.25rem;
}

.cc-btn-apply {
    background-color: var(--cc-blue-600);
    color: var(--cc-white);
    border: none;
    flex: 2;
}

.cc-btn-apply:hover {
    background-color: var(--cc-blue-700);
}

.cc-btn-details {
    background-color: var(--cc-white);
    color: var(--cc-gray-700);
    border: 1px solid var(--cc-gray-300);
    flex: 1;
}

.cc-btn-details:hover {
    background-color: var(--cc-gray-50);
}

.cc-btn-compare {
    background-color: var(--cc-white);
    color: var(--cc-gray-700);
    border: 1px solid var(--cc-gray-300);
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: var(--cc-radius-sm);
    display: flex;
    align-items: center;
    gap: 0.25rem;
    z-index: 10;
}

.cc-btn-compare .icon {
    width: 0.875rem;
    height: 0.875rem;
}

.cc-btn-compare.active {
    background-color: var(--cc-blue-600);
    color: var(--cc-white);
    border-color: var(--cc-blue-600);
}

/* Pagination */
.cc-pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.cc-pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--cc-radius);
    border: 1px solid var(--cc-gray-300);
    color: var(--cc-gray-700);
    font-weight: 500;
    transition: all 0.2s;
    text-decoration: none;
}

.cc-pagination-link:hover {
    background-color: var(--cc-gray-50);
}

.cc-pagination-link.active {
    background-color: var(--cc-blue-600);
    color: var(--cc-white);
    border-color: var(--cc-blue-600);
}

.cc-pagination-link.disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* Comparison Bar */
.cc-comparison-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: var(--cc-gray-800);
    color: var(--cc-white);
    padding: 1rem;
    z-index: 100;
    box-shadow: var(--cc-shadow-lg);
    transform: translateY(100%);
    transition: transform 0.3s ease-in-out;
}

.cc-comparison-bar.active {
    transform: translateY(0);
}

.cc-comparison-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cc-comparison-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.cc-comparison-count {
    font-weight: 500;
}

.cc-comparison-actions {
    display: flex;
    gap: 0.75rem;
}

/* Comparison Table */
.cc-comparison-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
    background-color: var(--cc-white);
    border-radius: var(--cc-radius-lg);
    overflow: hidden;
    box-shadow: var(--cc-shadow-md);
}

.cc-comparison-table th,
.cc-comparison-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--cc-gray-200);
}

.cc-comparison-table th {
    background-color: var(--cc-gray-50);
    font-weight: 600;
    color: var(--cc-gray-800);
    position: sticky;
    top: 0;
    z-index: 10;
}

.cc-comparison-table tr:last-child td {
    border-bottom: none;
}

.cc-comparison-table .cc-feature-row {
    background-color: var(--cc-gray-50);
    font-weight: 600;
}

.cc-comparison-table .cc-card-image-cell {
    width: 150px;
    height: 100px;
    padding: 0.5rem;
}

.cc-comparison-table .cc-card-image-cell img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.cc-comparison-table .cc-rating-cell {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.cc-comparison-table .cc-rating-cell .icon {
    width: 1rem;
    height: 1rem;
    color: var(--cc-yellow-400);
}

.cc-comparison-table .cc-check-cell {
    text-align: center;
}

.cc-comparison-table .cc-check-cell .icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--cc-green-500);
}

.cc-comparison-table .cc-action-cell {
    text-align: center;
}

/* No Results */
.cc-no-results {
    text-align: center;
    padding: 3rem 1rem;
    background-color: var(--cc-white);
    border-radius: var(--cc-radius-lg);
    box-shadow: var(--cc-shadow);
}

.cc-no-results h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--cc-gray-800);
    margin-bottom: 0.5rem;
}

.cc-no-results p {
    color: var(--cc-gray-600);
    margin-bottom: 1.5rem;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .cc-filter-content {
        grid-template-columns: 1fr;
    }
    
    .cc-sort-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .cc-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .cc-comparison-content {
        flex-direction: column;
        gap: 1rem;
    }
    
    .cc-comparison-actions {
        width: 100%;
    }
    
    .cc-comparison-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="cc-archive-container">
    <header class="cc-archive-header">
        <h1 class="cc-archive-title">Compare Credit Cards</h1>
        <p class="cc-archive-description">Find the perfect credit card for your needs. Compare features, rewards, fees, and more to make an informed decision.</p>
    </header>

    <?php if ($compare_mode): ?>
        <!-- Comparison View -->
        <div class="cc-filter-section">
            <div class="cc-filter-header">
                <h2 class="cc-filter-title"><?php echo get_cc_icon('compare', 'icon'); ?> Credit Card Comparison</h2>
                <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="cc-filter-toggle">
                    <?php echo get_cc_icon('x', 'icon'); ?> Exit Comparison
                </a>
            </div>
        </div>

        <?php if ($credit_cards->have_posts()): ?>
            <div class="cc-comparison-table-wrapper">
                <table class="cc-comparison-table">
                    <thead>
                        <tr>
                            <th>Features</th>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); ?>
                                <th><?php the_title(); ?></th>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Card Images -->
                        <tr>
                            <td>Card Image</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $card_image = get_cc_meta(get_the_ID(), 'card_image_url', '');
                                if (empty($card_image) && has_post_thumbnail()) {
                                    $card_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                                }
                            ?>
                                <td class="cc-card-image-cell">
                                    <?php if (!empty($card_image)): ?>
                                        <img src="<?php echo esc_url($card_image); ?>" alt="<?php the_title(); ?>">
                                    <?php endif; ?>
                                </td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Bank/Issuer -->
                        <tr>
                            <td>Bank/Issuer</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $bank_terms = get_the_terms(get_the_ID(), 'store');
                                $bank_name = !is_wp_error($bank_terms) && !empty($bank_terms) ? $bank_terms[0]->name : 'N/A';
                            ?>
                                <td><?php echo esc_html($bank_name); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Network Type -->
                        <tr>
                            <td>Network</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $network_terms = get_the_terms(get_the_ID(), 'network-type');
                                $network_name = !is_wp_error($network_terms) && !empty($network_terms) ? $network_terms[0]->name : 'N/A';
                            ?>
                                <td><?php echo esc_html($network_name); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Rating -->
                        <tr>
                            <td>Rating</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $rating = get_cc_meta(get_the_ID(), 'rating', 0, true);
                            ?>
                                <td class="cc-rating-cell">
                                    <?php echo get_cc_icon('star', 'icon'); ?>
                                    <span><?php echo esc_html($rating); ?>/5</span>
                                </td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Annual Fee -->
                        <tr>
                            <td>Annual Fee</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $annual_fee = get_cc_meta(get_the_ID(), 'annual_fee', 'N/A');
                            ?>
                                <td><?php echo esc_html($annual_fee); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Welcome Bonus -->
                        <tr>
                            <td>Welcome Bonus</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $welcome_bonus = get_cc_meta(get_the_ID(), 'welcome_bonus', 'N/A');
                            ?>
                                <td><?php echo esc_html($welcome_bonus); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Reward Rate -->
                        <tr>
                            <td>Reward Rate</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $cashback_rate = get_cc_meta(get_the_ID(), 'cashback_rate', 'N/A');
                            ?>
                                <td><?php echo esc_html($cashback_rate); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Interest Rate -->
                        <tr>
                            <td>Interest Rate</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $interest_rate = get_cc_meta(get_the_ID(), 'interest_rate', 'N/A');
                            ?>
                                <td><?php echo esc_html($interest_rate); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Credit Limit -->
                        <tr>
                            <td>Credit Limit</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $credit_limit = get_cc_meta(get_the_ID(), 'credit_limit', 'N/A');
                            ?>
                                <td><?php echo esc_html($credit_limit); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Processing Time -->
                        <tr>
                            <td>Processing Time</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $processing_time = get_cc_meta(get_the_ID(), 'processing_time', 'N/A');
                            ?>
                                <td><?php echo esc_html($processing_time); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Min Income -->
                        <tr>
                            <td>Min Income</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $min_income = get_cc_meta(get_the_ID(), 'min_income', 'N/A');
                            ?>
                                <td><?php echo esc_html($min_income); ?></td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                        
                        <!-- Action -->
                        <tr>
                            <td>Apply</td>
                            <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                                $apply_link = get_cc_meta(get_the_ID(), 'apply_link', '#');
                            ?>
                                <td class="cc-action-cell">
                                    <a href="<?php echo esc_url($apply_link); ?>" class="cc-btn cc-btn-primary" target="_blank" rel="noopener">Apply Now</a>
                                </td>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="cc-no-results">
                <h3>No cards selected for comparison</h3>
                <p>Please select at least two credit cards to compare.</p>
                <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="cc-btn cc-btn-primary">Browse Credit Cards</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Regular Archive View -->
        <form class="cc-filter-section" method="get" action="<?php echo get_post_type_archive_link('credit-card'); ?>">
            <div class="cc-filter-header">
                <h2 class="cc-filter-title"><?php echo get_cc_icon('filter', 'icon'); ?> Filter Credit Cards</h2>
                <button type="button" class="cc-filter-toggle" id="toggle-filters">
                    <?php echo get_cc_icon('minus', 'icon'); ?> <span>Hide Filters</span>
                </button>
            </div>
            
            <div class="cc-filter-content" id="filter-content">
                <?php if (!empty($filters['banks'])): ?>
                <div class="cc-filter-group">
                    <label class="cc-filter-label" for="bank">Bank/Issuer</label>
                    <select class="cc-filter-select" name="bank" id="bank">
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
                <div class="cc-filter-group">
                    <label class="cc-filter-label" for="network_type">Network Type</label>
                    <select class="cc-filter-select" name="network_type" id="network_type">
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
                <div class="cc-filter-group">
                    <label class="cc-filter-label" for="category">Category</label>
                    <select class="cc-filter-select" name="category" id="category">
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
                <div class="cc-filter-group">
                    <label class="cc-filter-label" for="min_rating">Minimum Rating</label>
                    <select class="cc-filter-select" name="min_rating" id="min_rating">
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
                <div class="cc-filter-group">
                    <label class="cc-filter-label" for="max_annual_fee">Maximum Annual Fee</label>
                    <select class="cc-filter-select" name="max_annual_fee" id="max_annual_fee">
                        <option value="">Any Fee</option>
                        <?php foreach ($filters['fee_ranges'] as $range): ?>
                            <option value="<?php echo esc_attr($range['max']); ?>" <?php selected($current_max_fee, $range['max']); ?>>
                                <?php echo esc_html($range['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="cc-filter-group">
                    <label class="cc-filter-label" for="featured">Card Type</label>
                    <select class="cc-filter-select" name="featured" id="featured">
                        <option value="">All Cards</option>
                        <option value="1" <?php selected($current_featured, '1'); ?>>Featured Cards</option>
                        <option value="0" <?php selected($current_featured, '0'); ?>>Regular Cards</option>
                    </select>
                </div>
            </div>
            
            <div class="cc-filter-actions">
                <button type="reset" class="cc-btn cc-btn-secondary">Reset Filters</button>
                <button type="submit" class="cc-btn cc-btn-primary"><?php echo get_cc_icon('filter', 'icon'); ?> Apply Filters</button>
            </div>
        </form>

        <div class="cc-sort-bar">
            <div class="cc-results-count">
                <?php echo $credit_cards->found_posts; ?> credit cards found
            </div>
            
            <div class="cc-sort-options">
                <span class="cc-sort-label"><?php echo get_cc_icon('sort', 'icon'); ?> Sort by:</span>
                <select class="cc-sort-select" id="sort-select" onchange="updateSort(this.value)">
                    <option value="rating-desc" <?php echo ($current_sort === 'rating' && $current_order === 'desc') ? 'selected' : ''; ?>>Rating (High to Low)</option>
                    <option value="rating-asc" <?php echo ($current_sort === 'rating' && $current_order === 'asc') ? 'selected' : ''; ?>>Rating (Low to High)</option>
                    <option value="annual_fee-asc" <?php echo ($current_sort === 'annual_fee' && $current_order === 'asc') ? 'selected' : ''; ?>>Annual Fee (Low to High)</option>
                    <option value="annual_fee-desc" <?php echo ($current_sort === 'annual_fee' && $current_order === 'desc') ? 'selected' : ''; ?>>Annual Fee (High to Low)</option>
                    <option value="review_count-desc" <?php echo ($current_sort === 'review_count' && $current_order === 'desc') ? 'selected' : ''; ?>>Popularity</option>
                    <option value="date-desc" <?php echo ($current_sort === 'date' && $current_order === 'desc') ? 'selected' : ''; ?>>Newest First</option>
                </select>
            </div>
        </div>

        <?php if ($credit_cards->have_posts()): ?>
            <div class="cc-cards-grid">
                <?php while ($credit_cards->have_posts()): $credit_cards->the_post(); 
                    $post_id = get_the_ID();
                    $card_image = get_cc_meta($post_id, 'card_image_url', '');
                    if (empty($card_image) && has_post_thumbnail()) {
                        $card_image = get_the_post_thumbnail_url($post_id, 'medium');
                    }
                    
                    $rating = get_cc_meta($post_id, 'rating', 0, true);
                    $review_count = get_cc_meta($post_id, 'review_count', 0, true);
                    $annual_fee = get_cc_meta($post_id, 'annual_fee', 'N/A');
                    $cashback_rate = get_cc_meta($post_id, 'cashback_rate', 'N/A');
                    $welcome_bonus = get_cc_meta($post_id, 'welcome_bonus', 'N/A');
                    $apply_link = get_cc_meta($post_id, 'apply_link', get_permalink());
                    $featured = (bool) get_cc_meta($post_id, 'featured', false);
                    $trending = (bool) get_cc_meta($post_id, 'trending', false);
                    
                    $bank_terms = get_the_terms($post_id, 'store');
                    $bank_name = !is_wp_error($bank_terms) && !empty($bank_terms) ? $bank_terms[0]->name : '';
                    
                    $pros = get_cc_meta($post_id, 'pros', [], false, true);
                ?>
                <div class="cc-card" data-id="<?php echo esc_attr($post_id); ?>">
                    <button type="button" class="cc-btn-compare" data-id="<?php echo esc_attr($post_id); ?>">
                        <?php echo get_cc_icon('compare', 'icon'); ?> Compare
                    </button>
                    
                    <div class="cc-card-image">
                        <?php if (!empty($card_image)): ?>
                            <img src="<?php echo esc_url($card_image); ?>" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        
                        <div class="cc-card-badges">
                            <?php if ($featured): ?>
                                <span class="cc-badge cc-badge-featured">Featured</span>
                            <?php endif; ?>
                            
                            <?php if ($trending): ?>
                                <span class="cc-badge cc-badge-trending">Trending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="cc-card-content">
                        <div class="cc-card-header">
                            <h3 class="cc-card-title"><?php the_title(); ?></h3>
                            <?php if (!empty($bank_name)): ?>
                                <div class="cc-card-bank"><?php echo esc_html($bank_name); ?></div>
                            <?php endif; ?>
                            
                            <?php if ($rating > 0): ?>
                                <div class="cc-card-rating">
                                    <?php echo get_cc_icon('star', 'icon'); ?>
                                    <span class="cc-card-rating-text"><?php echo esc_html($rating); ?>/5</span>
                                    <?php if ($review_count > 0): ?>
                                        <span class="cc-card-rating-text">(<?php echo esc_html($review_count); ?> reviews)</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cc-card-details">
                            <div class="cc-card-detail">
                                <span class="cc-card-detail-label">Annual Fee</span>
                                <span class="cc-card-detail-value"><?php echo esc_html($annual_fee); ?></span>
                            </div>
                            
                            <div class="cc-card-detail">
                                <span class="cc-card-detail-label">Reward Rate</span>
                                <span class="cc-card-detail-value"><?php echo esc_html($cashback_rate); ?></span>
                            </div>
                            
                            <div class="cc-card-detail" colspan="2">
                                <span class="cc-card-detail-label">Welcome Bonus</span>
                                <span class="cc-card-detail-value"><?php echo esc_html($welcome_bonus); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($pros) && is_array($pros)): ?>
                            <div class="cc-card-features">
                                <?php foreach (array_slice($pros, 0, 3) as $pro): ?>
                                    <div class="cc-card-feature">
                                        <?php echo get_cc_icon('check', 'icon'); ?>
                                        <span class="cc-card-feature-text"><?php echo esc_html($pro); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="cc-card-actions">
                            <a href="<?php the_permalink(); ?>" class="cc-btn cc-btn-details">Details</a>
                            <a href="<?php echo esc_url($apply_link); ?>" class="cc-btn cc-btn-apply" target="_blank" rel="noopener">Quick Apply</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            
            <?php
            // Pagination
            $total_pages = $credit_cards->max_num_pages;
            if ($total_pages > 1):
                $current_page = max(1, get_query_var('paged'));
            ?>
            <div class="cc-pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo get_pagenum_link($current_page - 1); ?>" class="cc-pagination-link">&laquo;</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                    <a href="<?php echo get_pagenum_link($i); ?>" class="cc-pagination-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?php echo get_pagenum_link($current_page + 1); ?>" class="cc-pagination-link">&raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="cc-no-results">
                <h3>No credit cards found</h3>
                <p>Try adjusting your filters or search criteria.</p>
                <a href="<?php echo get_post_type_archive_link('credit-card'); ?>" class="cc-btn cc-btn-primary">Reset Filters</a>
            </div>
        <?php endif; ?>
        
        <!-- Comparison Bar -->
        <div class="cc-comparison-bar" id="comparison-bar">
            <div class="cc-comparison-content">
                <div class="cc-comparison-info">
                    <span class="cc-comparison-count"><span id="selected-count">0</span> cards selected for comparison</span>
                </div>
                <div class="cc-comparison-actions">
                    <button type="button" class="cc-btn cc-btn-secondary" id="clear-comparison">Clear All</button>
                    <button type="button" class="cc-btn cc-btn-primary" id="compare-now" disabled>Compare Cards</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    // Toggle filters
    const toggleBtn = document.getElementById('toggle-filters');
    const filterContent = document.getElementById('filter-content');
    
    if (toggleBtn && filterContent) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = filterContent.style.display !== 'none';
            filterContent.style.display = isVisible ? 'none' : 'grid';
            toggleBtn.querySelector('span').textContent = isVisible ? 'Show Filters' : 'Hide Filters';
            toggleBtn.querySelector('svg').outerHTML = isVisible 
                ? '<?php echo get_cc_icon('plus', 'icon'); ?>'
                : '<?php echo get_cc_icon('minus', 'icon'); ?>';
        });
    }
    
    // Sort functionality
    window.updateSort = function(value) {
        const [sort, order] = value.split('-');
        const url = new URL(window.location.href);
        url.searchParams.set('sort_by', sort);
        url.searchParams.set('sort_order', order);
        window.location.href = url.toString();
    };
    
    // Comparison functionality
    const comparisonBar = document.getElementById('comparison-bar');
    const compareButtons = document.querySelectorAll('.cc-btn-compare');
    const clearComparisonBtn = document.getElementById('clear-comparison');
    const compareNowBtn = document.getElementById('compare-now');
    const selectedCountEl = document.getElementById('selected-count');
    
    let selectedCards = [];
    const maxCompare = 3;
    
    // Load selected cards from localStorage
    function loadSelectedCards() {
        const saved = localStorage.getItem('cc_compare_cards');
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
        localStorage.setItem('cc_compare_cards', JSON.stringify(selectedCards));
    }
    
    // Update UI based on selected cards
    function updateComparisonUI() {
        // Update buttons
        compareButtons.forEach(btn => {
            const cardId = btn.getAttribute('data-id');
            const isSelected = selectedCards.includes(cardId);
            
            btn.classList.toggle('active', isSelected);
            btn.textContent = isSelected ? 'Remove' : 'Compare';
            btn.innerHTML = (isSelected ? '<?php echo get_cc_icon('minus', 'icon'); ?>' : '<?php echo get_cc_icon('compare', 'icon'); ?>') + 
                            (isSelected ? ' Remove' : ' Compare');
        });
        
        // Update comparison bar
        if (selectedCards.length > 0) {
            comparisonBar.classList.add('active');
            selectedCountEl.textContent = selectedCards.length;
            compareNowBtn.disabled = selectedCards.length < 2;
        } else {
            comparisonBar.classList.remove('active');
        }
    }
    
    // Toggle card selection
    function toggleCardSelection(cardId) {
        const index = selectedCards.indexOf(cardId);
        
        if (index > -1) {
            // Remove from selection
            selectedCards.splice(index, 1);
        } else {
            // Add to selection if not at max
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
        // Load saved selections
        loadSelectedCards();
        
        // Add click handlers to compare buttons
        compareButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const cardId = this.getAttribute('data-id');
                toggleCardSelection(cardId);
            });
        });
        
        // Clear comparison
        if (clearComparisonBtn) {
            clearComparisonBtn.addEventListener('click', function() {
                selectedCards = [];
                saveSelectedCards();
                updateComparisonUI();
            });
        }
        
        // Compare now
        if (compareNowBtn) {
            compareNowBtn.addEventListener('click', function() {
                if (selectedCards.length >= 2) {
                    const compareUrl = `<?php echo get_post_type_archive_link('credit-card'); ?>?compare=${selectedCards.join(',')}`;
                    window.location.href = compareUrl;
                }
            });
        }
    }
    
    // Initialize comparison functionality if we're not in comparison mode
    <?php if (!$compare_mode): ?>
    initComparison();
    <?php endif; ?>
})();
</script>

<?php get_footer(); ?>
