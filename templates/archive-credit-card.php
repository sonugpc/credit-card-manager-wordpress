<?php
/**
 * Template Name: Credit Card Archive
 * Template for displaying credit card archive
 */

get_header();
?>

<div class="ccm-archive-container">
    <div class="ccm-archive-header">
        <div class="ccm-container">
            <h1 class="ccm-archive-title"><?php _e('Compare Credit Cards', 'credit-card-manager'); ?></h1>
            <p class="ccm-archive-description">
                <?php _e('Find and compare the best credit cards for your needs. Use filters to narrow down your options.', 'credit-card-manager'); ?>
            </p>
        </div>
    </div>

    <div class="ccm-container">
        <div class="ccm-archive-content">
            <!-- Filters Section -->
            <div class="ccm-filters-section">
                <div class="ccm-filters-header">
                    <h2><?php _e('Filter Credit Cards', 'credit-card-manager'); ?></h2>
                    <button id="ccm-toggle-filters" class="ccm-toggle-filters">
                        <span class="dashicons dashicons-filter"></span> <?php _e('Toggle Filters', 'credit-card-manager'); ?>
                    </button>
                </div>
                
                <div id="ccm-filters-container" class="ccm-filters-container">
                    <form id="ccm-filter-form" class="ccm-filter-form">
                        <div class="ccm-filter-row">
                            <div class="ccm-filter-group">
                                <label for="ccm-bank-filter"><?php _e('Bank', 'credit-card-manager'); ?></label>
                                <select id="ccm-bank-filter" name="bank" class="ccm-filter-select">
                                    <option value=""><?php _e('All Banks', 'credit-card-manager'); ?></option>
                                    <!-- Banks will be populated via JS -->
                                </select>
                            </div>
                            
                            <div class="ccm-filter-group">
                                <label for="ccm-network-filter"><?php _e('Network Type', 'credit-card-manager'); ?></label>
                                <select id="ccm-network-filter" name="network_type" class="ccm-filter-select">
                                    <option value=""><?php _e('All Networks', 'credit-card-manager'); ?></option>
                                    <!-- Networks will be populated via JS -->
                                </select>
                            </div>
                            
                            <div class="ccm-filter-group">
                                <label for="ccm-category-filter"><?php _e('Category', 'credit-card-manager'); ?></label>
                                <select id="ccm-category-filter" name="category" class="ccm-filter-select">
                                    <option value=""><?php _e('All Categories', 'credit-card-manager'); ?></option>
                                    <!-- Categories will be populated via JS -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="ccm-filter-row">
                            <div class="ccm-filter-group">
                                <label for="ccm-rating-filter"><?php _e('Minimum Rating', 'credit-card-manager'); ?></label>
                                <select id="ccm-rating-filter" name="min_rating" class="ccm-filter-select">
                                    <option value=""><?php _e('Any Rating', 'credit-card-manager'); ?></option>
                                    <option value="4"><?php _e('4+ Stars', 'credit-card-manager'); ?></option>
                                    <option value="3"><?php _e('3+ Stars', 'credit-card-manager'); ?></option>
                                    <option value="2"><?php _e('2+ Stars', 'credit-card-manager'); ?></option>
                                    <option value="1"><?php _e('1+ Stars', 'credit-card-manager'); ?></option>
                                </select>
                            </div>
                            
                            <div class="ccm-filter-group">
                                <label for="ccm-fee-filter"><?php _e('Annual Fee', 'credit-card-manager'); ?></label>
                                <select id="ccm-fee-filter" name="max_annual_fee" class="ccm-filter-select">
                                    <option value=""><?php _e('Any Fee', 'credit-card-manager'); ?></option>
                                    <option value="0"><?php _e('No Annual Fee', 'credit-card-manager'); ?></option>
                                    <option value="1000"><?php _e('Under ₹1,000', 'credit-card-manager'); ?></option>
                                    <option value="2500"><?php _e('Under ₹2,500', 'credit-card-manager'); ?></option>
                                    <option value="5000"><?php _e('Under ₹5,000', 'credit-card-manager'); ?></option>
                                </select>
                            </div>
                            
                            <div class="ccm-filter-group">
                                <label for="ccm-sort-filter"><?php _e('Sort By', 'credit-card-manager'); ?></label>
                                <select id="ccm-sort-filter" name="sort_by" class="ccm-filter-select">
                                    <option value="rating"><?php _e('Rating (High to Low)', 'credit-card-manager'); ?></option>
                                    <option value="annual_fee"><?php _e('Annual Fee (Low to High)', 'credit-card-manager'); ?></option>
                                    <option value="review_count"><?php _e('Popularity', 'credit-card-manager'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="ccm-filter-row ccm-filter-actions">
                            <div class="ccm-filter-group ccm-filter-checkboxes">
                                <div class="ccm-checkbox-wrapper">
                                    <input type="checkbox" id="ccm-featured-filter" name="featured" value="1">
                                    <label for="ccm-featured-filter"><?php _e('Featured Cards Only', 'credit-card-manager'); ?></label>
                                </div>
                                
                                <div class="ccm-checkbox-wrapper">
                                    <input type="checkbox" id="ccm-trending-filter" name="trending" value="1">
                                    <label for="ccm-trending-filter"><?php _e('Trending Cards Only', 'credit-card-manager'); ?></label>
                                </div>
                            </div>
                            
                            <div class="ccm-filter-buttons">
                                <button type="submit" class="ccm-filter-submit">
                                    <span class="dashicons dashicons-search"></span> <?php _e('Apply Filters', 'credit-card-manager'); ?>
                                </button>
                                <button type="button" id="ccm-filter-reset" class="ccm-filter-reset">
                                    <span class="dashicons dashicons-dismiss"></span> <?php _e('Reset', 'credit-card-manager'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Compare Section -->
            <div id="ccm-compare-section" class="ccm-compare-section" style="display: none;">
                <div class="ccm-compare-header">
                    <h3><?php _e('Compare Selected Cards', 'credit-card-manager'); ?></h3>
                    <div class="ccm-compare-actions">
                        <span id="ccm-compare-count">0 cards selected</span>
                        <button id="ccm-compare-clear" class="ccm-compare-clear">
                            <span class="dashicons dashicons-no-alt"></span> <?php _e('Clear All', 'credit-card-manager'); ?>
                        </button>
                        <button id="ccm-compare-button" class="ccm-compare-button" disabled>
                            <span class="dashicons dashicons-visibility"></span> <?php _e('Compare Cards', 'credit-card-manager'); ?>
                        </button>
                    </div>
                </div>
                <div id="ccm-compare-cards" class="ccm-compare-cards"></div>
            </div>
            
            <!-- Cards Grid Section -->
            <div class="ccm-cards-section">
                <div class="ccm-cards-header">
                    <h2><?php _e('Credit Cards', 'credit-card-manager'); ?></h2>
                    <div class="ccm-cards-count">
                        <span id="ccm-total-cards">0</span> <?php _e('cards found', 'credit-card-manager'); ?>
                    </div>
                </div>
                
                <div id="ccm-cards-grid" class="ccm-cards-grid">
                    <!-- Cards will be loaded via JS -->
                    <div class="ccm-loading">
                        <span class="dashicons dashicons-update-alt ccm-spin"></span>
                        <p><?php _e('Loading credit cards...', 'credit-card-manager'); ?></p>
                    </div>
                </div>
                
                <div id="ccm-pagination" class="ccm-pagination"></div>
            </div>
        </div>
    </div>
</div>

<!-- Compare Modal -->
<div id="ccm-compare-modal" class="ccm-modal">
    <div class="ccm-modal-content">
        <div class="ccm-modal-header">
            <h2><?php _e('Credit Card Comparison', 'credit-card-manager'); ?></h2>
            <button id="ccm-modal-close" class="ccm-modal-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="ccm-modal-body">
            <div id="ccm-comparison-table" class="ccm-comparison-table"></div>
        </div>
    </div>
</div>

<script type="text/template" id="ccm-card-template">
    <div class="ccm-card-item" data-id="{{id}}">
        <div class="ccm-card-inner">
            <div class="ccm-card-compare">
                <label class="ccm-compare-checkbox">
                    <input type="checkbox" class="ccm-compare-input" data-id="{{id}}" data-title="{{title}}" data-image="{{card_image}}">
                    <span class="ccm-compare-checkmark"></span>
                    <?php _e('Compare', 'credit-card-manager'); ?>
                </label>
            </div>
            
            <div class="ccm-card-image">
                <img src="{{card_image}}" alt="{{title}}">
                {{#featured}}
                <span class="ccm-badge ccm-featured"><?php _e('Featured', 'credit-card-manager'); ?></span>
                {{/featured}}
                {{#trending}}
                <span class="ccm-badge ccm-trending"><?php _e('Trending', 'credit-card-manager'); ?></span>
                {{/trending}}
            </div>
            
            <div class="ccm-card-content">
                <h3 class="ccm-card-title">{{title}}</h3>
                
                {{#bank}}
                <div class="ccm-card-bank">{{bank.name}}</div>
                {{/bank}}
                
                <div class="ccm-card-meta">
                    <div class="ccm-rating">
                        <div class="ccm-stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <div class="ccm-stars-filled" style="width: {{rating_percent}}%"></div>
                        </div>
                        <span class="ccm-rating-number">{{rating}}/5</span>
                        {{#review_count}}
                        <span class="ccm-review-count">({{review_count}} reviews)</span>
                        {{/review_count}}
                    </div>
                    
                    <div class="ccm-card-details">
                        {{#annual_fee}}
                        <div class="ccm-detail-item">
                            <span class="ccm-detail-label"><?php _e('Annual Fee:', 'credit-card-manager'); ?></span>
                            <span class="ccm-detail-value">{{annual_fee}}</span>
                        </div>
                        {{/annual_fee}}
                        
                        {{#cashback_rate}}
                        <div class="ccm-detail-item">
                            <span class="ccm-detail-label"><?php _e('Reward Rate:', 'credit-card-manager'); ?></span>
                            <span class="ccm-detail-value">{{cashback_rate}}</span>
                        </div>
                        {{/cashback_rate}}
                        
                        {{#welcome_bonus}}
                        <div class="ccm-detail-item">
                            <span class="ccm-detail-label"><?php _e('Welcome Bonus:', 'credit-card-manager'); ?></span>
                            <span class="ccm-detail-value">{{welcome_bonus}}</span>
                        </div>
                        {{/welcome_bonus}}
                    </div>
                </div>
                
                {{#excerpt}}
                <div class="ccm-card-excerpt">{{excerpt}}</div>
                {{/excerpt}}
                
                <div class="ccm-card-actions">
                    <a href="{{link}}" class="ccm-btn ccm-btn-details">
                        <span class="dashicons dashicons-visibility"></span> <?php _e('Read More', 'credit-card-manager'); ?>
                    </a>
                    
                    {{#apply_link}}
                    <a href="{{apply_link}}" class="ccm-btn ccm-btn-apply" target="_blank" rel="noopener noreferrer">
                        <span class="dashicons dashicons-external"></span> <?php _e('Quick Apply', 'credit-card-manager'); ?>
                    </a>
                    {{/apply_link}}
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="ccm-compare-card-template">
    <div class="ccm-compare-card" data-id="{{id}}">
        <button class="ccm-compare-remove" data-id="{{id}}">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
        <img src="{{image}}" alt="{{title}}">
        <span class="ccm-compare-title">{{title}}</span>
    </div>
</script>

<?php get_footer(); ?>
