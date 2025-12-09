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



// Use existing format_currency function from helper-functions.php
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
    background: white;
    border-bottom: 1px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
    position: sticky;
    z-index: 999;

}

/* Desktop sticky navigation */
@media (min-width: 769px) {
    .cc-nav-sticky {
        position: sticky;
        z-index: 100;
            top: 60px !important;

    }
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
    color: white !important;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    text-align:center;
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

/* Related Articles Section */
.cc-related-articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.cc-article-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
}

.cc-article-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.cc-article-image {
    width: 100%;
    height: 150px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    overflow: hidden;
}

.cc-article-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cc-article-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.cc-article-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
    line-height: 1.4;
}

.cc-article-title a {
    color: var(--gray-900);
    text-decoration: none;
    transition: color 0.2s;
}

.cc-article-title a:hover {
    color: var(--primary-blue);
}

.cc-article-excerpt {
    color: var(--gray-600);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    flex: 1;
}

.cc-article-meta {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-top: auto;
}

/* Trending Credit Cards Section */
.cc-trending-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.cc-trending-card-item {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    transition: all 0.2s;
    position: relative;
}

.cc-trending-card-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.cc-card-badges {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.cc-card-image {
    width: 100%;
    height: 120px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    overflow: hidden;
}

.cc-card-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.cc-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
}

.cc-card-title a {
    color: var(--gray-900);
    text-decoration: none;
    transition: color 0.2s;
}

.cc-card-title a:hover {
    color: var(--primary-blue);
}

.cc-card-bank {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin-bottom: 0.5rem;
}

.cc-card-rating {
    font-size: 0.875rem;
    color: var(--gray-700);
    margin-bottom: 1rem;
}

.cc-card-highlights {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.cc-highlight {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cc-label {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 500;
}

.cc-value {
    font-size: 0.875rem;
    color: var(--gray-900);
    font-weight: 600;
}

.cc-card-actions {
    display: flex;
    gap: 0.75rem;
}

.cc-btn-details,
.cc-btn-apply {
    flex: 1;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
    transition: all 0.2s;
}

.cc-btn-details {
    background: var(--gray-100);
    color: var(--gray-700);
    border: 1px solid var(--gray-300);
}

.cc-btn-details:hover {
    background: var(--gray-200);
}

.cc-btn-apply {
    background: var(--primary-blue);
    color: white !important;
    border: 1px solid var(--primary-blue);
}

.cc-btn-apply:hover {
    background: #1d4ed8;
}

/* Explore Card Categories Section */
.cc-categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.cc-category-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-200);
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.cc-category-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.cc-category-content {
    margin-bottom: 1.5rem;
}

.cc-category-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
    line-height: 1.4;
}

.cc-category-title a {
    color: var(--gray-900);
    text-decoration: none;
    transition: color 0.2s;
}

.cc-category-title a:hover {
    color: var(--primary-blue);
}

.cc-category-description {
    color: var(--gray-600);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.cc-category-count {
    font-size: 0.75rem;
    color: var(--gray-500);
    font-weight: 500;
}

.cc-category-action {
    text-align: center;
}

/* View All Container */
.cc-view-all-container {
    text-align: center;
    margin-top: 2rem;
}

/* No Content Message */
.cc-no-content {
    text-align: center;
    color: var(--gray-500);
    font-size: 1rem;
    padding: 2rem;
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    margin-bottom: 2rem;
}

/* Bottom Sticky Bar */
.cc-sticky-bottom-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid var(--gray-200);
    box-shadow: 0 -4px 6px -1px rgb(0 0 0 / 0.1);
    z-index: 1000;
    padding: 0.5rem 0;
    transition: transform 0.3s ease-in-out;
}



.cc-sticky-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.cc-sticky-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
}

.cc-sticky-actions .cc-apply-btn {
    flex: 1;
}



.cc-sticky-comment-btn,
.cc-sticky-share-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    background: var(--gray-50);
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
    color: var(--gray-700);
    min-width: 40px;
    min-height: 40px;
    flex-shrink: 0;
}

.cc-sticky-comment-btn:hover,
.cc-sticky-share-btn:hover {
    background: var(--gray-100);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.cc-sticky-label {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Share Modal */
.cc-share-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    animation: fadeIn 0.3s ease-out;
}

.cc-share-modal-content {
    background: white;
    border-radius: var(--radius-lg);
    max-width: 400px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-xl);
    animation: slideUp 0.3s ease-out;
}

.cc-share-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.cc-share-modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
}

.cc-share-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-500);
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.cc-share-modal-close:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.cc-share-modal-body {
    padding: 1.5rem;
}

.cc-share-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.cc-share-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--gray-700);
    font-weight: 500;
    transition: all 0.2s;
    background: var(--gray-50);
}

.cc-share-option:hover {
    background: var(--gray-100);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.cc-share-option.facebook:hover {
    background: #1877f2;
    color: white;
    border-color: #1877f2;
}

.cc-share-option.twitter:hover {
    background: #1da1f2;
    color: white;
    border-color: #1da1f2;
}

.cc-share-option.whatsapp:hover {
    background: #25d366;
    color: white;
    border-color: #25d366;
}

.cc-share-option.copy-link:hover {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

    .cc-related-articles-grid,
    .cc-trending-cards-grid,
    .cc-categories-grid {
        grid-template-columns: 1fr;
    }

    .cc-card-actions {
        flex-direction: column;
    }

    .cc-highlight {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .cc-sticky-actions {
        flex-direction: row;
        gap: 0.75rem;
        justify-content: center;
    }

    .cc-sticky-comment-btn,
    .cc-sticky-share-btn {
        flex-shrink: 0;
    }

    .cc-share-options {
        grid-template-columns: 1fr;
    }
}
</style>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('cc-single-wrapper'); ?>>
                <!-- Hero Section -->
                <header class=" cc-hero">
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

        <!-- Related Articles Section -->
        <section id="related-articles" class="cc-section">
            <h2 class="cc-section-title">Related Articles</h2>

            <?php
            $related_articles = get_posts(array(
                'category_name' => 'credit-card-bill-payment-offers',
                'posts_per_page' => 3,
                'post_status' => 'publish',
                'post__not_in' => array(get_the_ID()), // Exclude current post if it's in the same category
            ));

            if (!empty($related_articles)): ?>
                <div class="cc-related-articles-grid">
                    <?php foreach ($related_articles as $article): ?>
                        <article class="cc-article-card">
                            <div class="cc-article-image">
                                <?php if (has_post_thumbnail($article->ID)): ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($article->ID, 'medium')); ?>"
                                         alt="<?php echo esc_attr(get_the_title($article->ID)); ?>">
                                <?php else: ?>
                                    <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/no-post.jpg'); ?>"
                                         alt="Default article image">
                                <?php endif; ?>
                            </div>
                            <div class="cc-article-content">
                                <h3 class="cc-article-title">
                                    <a href="<?php echo esc_url(get_permalink($article->ID)); ?>">
                                        <?php echo esc_html(get_the_title($article->ID)); ?>
                                    </a>
                                </h3>
                                <div class="cc-article-excerpt">
                                    <?php echo wp_kses_post(wp_trim_words(get_the_excerpt($article->ID), 20)); ?>
                                </div>
                                <div class="cc-article-meta">
                                    <time datetime="<?php echo esc_attr(get_the_date('c', $article->ID)); ?>">
                                        <?php echo esc_html(get_the_date('', $article->ID)); ?>
                                    </time>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="cc-view-all-container">
                    <a href="<?php echo esc_url(get_category_link(get_cat_ID('credit-card-bill-payment-offers'))); ?>" class="cc-apply-btn">
                        View All Articles
                    </a>
                </div>
            <?php else: ?>
                <p class="cc-no-content">No related articles found.</p>
            <?php endif; ?>
        </section>

        <!-- Trending Credit Cards Section -->
        <section id="trending-cards" class="cc-section">
            <h2 class="cc-section-title">Trending Credit Cards</h2>

            <?php
            $trending_cards = get_posts(array(
                'post_type' => 'credit-card',
                'posts_per_page' => 3,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'post__not_in' => array(get_the_ID()), // Exclude current card
            ));

            if (!empty($trending_cards)): ?>
                <div class="cc-trending-cards-grid">
                    <?php foreach ($trending_cards as $card): ?>
                        <?php
                        $card_id = $card->ID;
                        $card_rating = ccm_get_meta($card_id, 'rating', 0, true);
                        $card_fee = ccm_format_currency(ccm_get_meta($card_id, 'annual_fee', 'N/A'));
                        $card_reward = ccm_get_meta($card_id, 'cashback_rate', 'N/A');
                        $card_apply_link = ccm_get_meta($card_id, 'apply_link', '#');
                        $card_featured = ccm_get_meta($card_id, 'featured', false);
                        $card_trending = ccm_get_meta($card_id, 'trending', false);
                        $card_bank = ccm_get_card_bank($card_id);
                        ?>
                        <div class="cc-trending-card-item">
                            <?php if ($card_featured || $card_trending): ?>
                                <div class="cc-card-badges">
                                    <?php if ($card_featured): ?>
                                        <span class="cc-badge featured">🏆 Featured</span>
                                    <?php endif; ?>
                                    <?php if ($card_trending): ?>
                                        <span class="cc-badge trending">🔥 Trending</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="cc-card-image">
                                <?php if (has_post_thumbnail($card_id)): ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($card_id, 'medium')); ?>"
                                         alt="<?php echo esc_attr(get_the_title($card_id)); ?>">
                                <?php endif; ?>
                            </div>

                            <div class="cc-card-info">
                                <h3 class="cc-card-title">
                                    <a href="<?php echo esc_url(get_permalink($card_id)); ?>">
                                        <?php echo esc_html(get_the_title($card_id)); ?>
                                    </a>
                                </h3>

                                <?php if (!empty($card_bank) && $card_bank !== 'N/A'): ?>
                                    <div class="cc-card-bank"><?php echo esc_html($card_bank); ?></div>
                                <?php endif; ?>

                                <?php if ($card_rating > 0): ?>
                                    <div class="cc-card-rating">
                                        ⭐ <?php echo esc_html($card_rating); ?>/5
                                    </div>
                                <?php endif; ?>

                                <div class="cc-card-highlights">
                                    <div class="cc-highlight">
                                        <span class="cc-label">Annual Fee:</span>
                                        <span class="cc-value"><?php echo esc_html($card_fee); ?></span>
                                    </div>
                                    <div class="cc-highlight">
                                        <span class="cc-label">Reward Rate:</span>
                                        <span class="cc-value"><?php echo esc_html($card_reward); ?></span>
                                    </div>
                                </div>

                                <div class="cc-card-actions">
                                    <a href="<?php echo esc_url(get_permalink($card_id)); ?>" class="cc-btn-details">
                                        Details
                                    </a>
                                    <a href="<?php echo esc_url($card_apply_link); ?>" class="cc-btn-apply" target="_blank" rel="noopener">
                                        Apply Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cc-view-all-container">
                    <a href="<?php echo esc_url(get_post_type_archive_link('credit-card')); ?>" class="cc-apply-btn">
                        View All Credit Cards
                    </a>
                </div>
            <?php else: ?>
                <p class="cc-no-content">No trending credit cards found.</p>
            <?php endif; ?>
        </section>

        <!-- Explore Card Categories Section -->
        <section id="explore-categories" class="cc-section">
            <h2 class="cc-section-title">Explore Other Card Categories</h2>

            <?php
            $card_categories = get_terms(array(
                'taxonomy' => 'card-category',
                'hide_empty' => true,
                'number' => 6, // Show up to 6 categories
            ));

            if (!empty($card_categories) && !is_wp_error($card_categories)): ?>
                <div class="cc-categories-grid">
                    <?php foreach ($card_categories as $category): ?>
                        <div class="cc-category-card">
                            <div class="cc-category-content">
                                <h3 class="cc-category-title">
                                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                </h3>
                                <div class="cc-category-description">
                                    <?php echo esc_html($category->description ?: 'Explore ' . $category->name . ' credit cards'); ?>
                                </div>
                                <div class="cc-category-count">
                                    <?php echo esc_html($category->count); ?> cards available
                                </div>
                            </div>
                            <div class="cc-category-action">
                                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="cc-apply-btn">
                                    Explore
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="cc-no-content">No card categories found.</p>
            <?php endif; ?>
        </section>
    </div>
    <footer class="entry-footer">
        <?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
    </footer>

    <!-- Comments Section -->
    <?php if (comments_open() || get_comments_number()) : ?>
        <section id="comments" class="cc-section">
            <h2 class="cc-section-title">Comments</h2>
            <?php comments_template(); ?>
        </section>
    <?php endif; ?>
</article>
<?php endwhile; ?>
</main>
</div>

<!-- Bottom Sticky Bar -->
<div class="cc-sticky-bottom-bar">
    <div class="cc-sticky-container">
        <div class="cc-sticky-actions">
            <a href="<?php echo esc_url($apply_link); ?>" target="_blank" rel="noopener" class="cc-apply-btn">
                Apply Now
            </a>

            <button type="button" class="cc-sticky-comment-btn" onclick="scrollToComments()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <button type="button" class="cc-sticky-share-btn" onclick="openShareModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 12V20C4 20.5304 4.21071 20.9609 4.58579 21.3359C4.96086 21.711 5.46957 21.9217 6 21.9217H18C18.5304 21.9217 19.0391 21.711 19.4142 21.3359C19.7893 20.9609 20 20.5304 20 20V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 6L12 2L8 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 2V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div id="cc-share-modal" class="cc-share-modal" style="display: none;">
    <div class="cc-share-modal-content">
        <div class="cc-share-modal-header">
            <h3>Share this Credit Card</h3>
            <button type="button" class="cc-share-modal-close" onclick="closeShareModal()">&times;</button>
        </div>
        <div class="cc-share-modal-body">
            <div class="cc-share-options">
                <a href="#" onclick="shareOnFacebook()" class="cc-share-option facebook">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </a>
                <a href="#" onclick="shareOnTwitter()" class="cc-share-option twitter">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                    Twitter
                </a>
                <a href="#" onclick="shareOnWhatsApp()" class="cc-share-option whatsapp">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                    WhatsApp
                </a>
                <button type="button" onclick="copyLink()" class="cc-share-option copy-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 4H4C2.89543 4 2 4.89543 2 6V20C2 21.1046 2.89543 22 4 22H18C19.1046 22 20 21.1046 20 20V8L16 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 4V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 12H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 16H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Copy Link
                </button>
            </div>
        </div>
    </div>
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
        sections.forEach(section => {
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
 

    // Sticky bottom bar visibility
    // const stickyBar = document.querySelector('.cc-sticky-bottom-bar');
    // const bottomCTA = document.querySelector('.cc-bottom-cta');

    // if (stickyBar && bottomCTA) {
    //     const observer = new IntersectionObserver((entries) => {
    //         entries.forEach(entry => {
    //             if (entry.isIntersecting) {
    //                 stickyBar.classList.remove('visible');
    //             } else {
    //                 stickyBar.classList.add('visible');
    //             }
    //         });
    //     }, {
    //         threshold: 0.1,
    //         rootMargin: '0px 0px -100px 0px'
    //     });

    //     observer.observe(bottomCTA);
    // }

    // Share modal functionality
    window.openShareModal = function() {
        const modal = document.getElementById('cc-share-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeShareModal = function() {
        const modal = document.getElementById('cc-share-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('cc-share-modal');
        if (modal && e.target === modal) {
            closeShareModal();
        }
    });

    // Share functions
    window.shareOnFacebook = function() {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent('<?php echo esc_js($card_name); ?> Credit Card Review');
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${title}`, '_blank', 'width=600,height=400');
        closeShareModal();
    };

    window.shareOnTwitter = function() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('Check out this <?php echo esc_js($card_name); ?> credit card review! #CreditCard #<?php echo esc_js(str_replace(' ', '', $card_name)); ?>');
        window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
        closeShareModal();
    };

    window.shareOnWhatsApp = function() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('Check out this <?php echo esc_js($card_name); ?> credit card review: ' + window.location.href);
        window.open(`https://wa.me/?text=${text}`, '_blank');
        closeShareModal();
    };

    window.copyLink = function() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            // Show success feedback
            const copyBtn = document.querySelector('.copy-link');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Copied!';
            copyBtn.style.background = 'var(--primary-green)';
            copyBtn.style.color = 'white';

            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.style.background = '';
                copyBtn.style.color = '';
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
        });
    };

    // Scroll to comments function
    window.scrollToComments = function() {
        const commentsSection = document.getElementById('comments');
        if (commentsSection) {
            const offsetTop = commentsSection.offsetTop - 100;
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    };
});
</script>


<?php get_footer(); ?>
