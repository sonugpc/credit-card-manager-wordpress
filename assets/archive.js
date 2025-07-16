/**
 * Archive Template JavaScript
 */

(function ($) {
    "use strict";

    // DOM elements
    const $toggleFilters = $("#ccm-toggle-filters");
    const $filtersContainer = $("#ccm-filters-container");
    const $filterForm = $("#ccm-filter-form");
    const $filterReset = $("#ccm-filter-reset");
    const $cardsGrid = $("#ccm-cards-grid");
    const $totalCards = $("#ccm-total-cards");
    const $pagination = $("#ccm-pagination");

    /**
     * Initialize archive functionality
     */
    function init() {
        setupEventListeners();
        initializeFilters();
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Toggle filters
        $toggleFilters.on("click", function () {
            $filtersContainer.slideToggle(300);
            $(this).find(".dashicons").toggleClass("dashicons-arrow-up");
        });

        // Reset filters
        $filterReset.on("click", function () {
            $filterForm[0].reset();
            window.location.href = window.location.pathname;
        });

        // Filter form submission with AJAX
        $filterForm.on("submit", function (e) {
            e.preventDefault();
            applyFilters();
        });

        // Real-time filter updates
        $filterForm.find("select, input").on("change", function () {
            // Debounce the filter application
            clearTimeout(window.filterTimeout);
            window.filterTimeout = setTimeout(applyFilters, 500);
        });

        // Pagination links
        $(document).on("click", ".ccm-pagination a", function (e) {
            e.preventDefault();
            const page = $(this).data("page") || 1;
            applyFilters(page);
        });
    }

    /**
     * Initialize filters from URL parameters
     */
    function initializeFilters() {
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        
        // Set form values from URL parameters
        urlParams.forEach((value, key) => {
            const $input = $filterForm.find(`[name="${key}"]`);
            if ($input.length) {
                if ($input.is(':checkbox')) {
                    $input.prop('checked', value === '1');
                } else {
                    $input.val(value);
                }
            }
        });

        // Check if filters container should be open
        if (urlParams.toString()) {
            $filtersContainer.show();
        }
    }

    /**
     * Apply filters with AJAX
     */
    function applyFilters(page = 1) {
        // Show loading state
        showLoading();

        // Get form data
        const formData = new FormData($filterForm[0]);
        formData.append('page', page);
        formData.append('action', 'ccm_filter_cards');
        formData.append('nonce', ccm_frontend.nonce);

        // Convert FormData to URLSearchParams for API call
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value && key !== 'action' && key !== 'nonce') {
                params.append(key, value);
            }
        }

        // Build API URL
        const apiUrl = ccm_frontend.api_url + 'credit-cards?' + params.toString();

        // Make API request
        $.ajax({
            url: apiUrl,
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', ccm_frontend.nonce);
            },
            success: function (response) {
                updateResults(response);
                updateURL(params);
            },
            error: function () {
                showError();
            }
        });
    }

    /**
     * Update results display
     */
    function updateResults(response) {
        const cards = response.data || [];
        const pagination = response.pagination || {};

        // Update cards grid
        $cardsGrid.empty();

        if (cards.length > 0) {
            cards.forEach(function (card) {
                const cardHtml = buildCardHtml(card);
                $cardsGrid.append(cardHtml);
            });
        } else {
            $cardsGrid.append('<div class="ccm-no-results"><p>No credit cards found matching your criteria.</p></div>');
        }

        // Update total count
        $totalCards.text(pagination.total || 0);

        // Update pagination
        updatePagination(pagination);

        // Scroll to results
        $('html, body').animate({
            scrollTop: $cardsGrid.offset().top - 100
        }, 500);
    }

    /**
     * Build card HTML
     */
    function buildCardHtml(card) {
        let html = '<div class="ccm-card-item">';
        html += '<div class="ccm-card-inner">';
        
        // Compare checkbox
        html += '<div class="ccm-card-compare">';
        html += '<label class="ccm-compare-checkbox">';
        html += `<input type="checkbox" class="ccm-compare-input" data-id="${card.id}" data-title="${card.title}" data-image="${card.card_image}">`;
        html += 'Add to Compare';
        html += '</label>';
        html += '</div>';

        // Card image
        html += '<div class="ccm-card-image">';
        if (card.card_image) {
            html += `<img src="${card.card_image}" alt="${card.title}">`;
        }
        
        // Badges
        if (card.featured) {
            html += '<span class="ccm-badge ccm-featured">Featured</span>';
        }
        if (card.trending) {
            html += '<span class="ccm-badge ccm-trending">Trending</span>';
        }
        html += '</div>';

        // Card content
        html += '<div class="ccm-card-content">';
        html += `<h3 class="ccm-card-title"><a href="${card.link}">${card.title}</a></h3>`;
        
        if (card.bank && card.bank.name) {
            html += `<div class="ccm-card-bank">${card.bank.name}</div>`;
        }

        // Rating
        if (card.rating) {
            const ratingPercent = (card.rating / 5) * 100;
            html += '<div class="ccm-card-meta">';
            html += '<div class="ccm-rating">';
            html += '<div class="ccm-stars">';
            html += '<span class="dashicons dashicons-star-filled"></span>'.repeat(5);
            html += `<div class="ccm-stars-filled" style="width: ${ratingPercent}%;">`;
            html += '<span class="dashicons dashicons-star-filled"></span>'.repeat(5);
            html += '</div>';
            html += '</div>';
            html += `<span class="ccm-rating-number">${card.rating}/5</span>`;
            if (card.review_count) {
                html += `<span class="ccm-review-count">(${card.review_count} reviews)</span>`;
            }
            html += '</div>';
            html += '</div>';
        }

        // Card details
        html += '<div class="ccm-card-details">';
        if (card.annual_fee) {
            html += '<div class="ccm-detail-item">';
            html += '<span class="ccm-detail-label">Annual Fee</span>';
            html += `<span class="ccm-detail-value">${card.annual_fee}</span>`;
            html += '</div>';
        }
        if (card.cashback_rate) {
            html += '<div class="ccm-detail-item">';
            html += '<span class="ccm-detail-label">Reward Rate</span>';
            html += `<span class="ccm-detail-value">${card.cashback_rate}</span>`;
            html += '</div>';
        }
        if (card.welcome_bonus) {
            html += '<div class="ccm-detail-item">';
            html += '<span class="ccm-detail-label">Welcome Bonus</span>';
            html += `<span class="ccm-detail-value">${card.welcome_bonus}</span>`;
            html += '</div>';
        }
        html += '</div>';

        // Card excerpt
        if (card.excerpt) {
            html += `<div class="ccm-card-excerpt">${card.excerpt}</div>`;
        }

        // Card actions
        html += '<div class="ccm-card-actions">';
        html += `<a href="${card.link}" class="ccm-btn ccm-btn-details">`;
        html += '<span class="dashicons dashicons-visibility"></span> Read More';
        html += '</a>';
        
        if (card.apply_link) {
            html += `<a href="${card.apply_link}" class="ccm-btn ccm-btn-apply" target="_blank" rel="noopener noreferrer">`;
            html += '<span class="dashicons dashicons-external"></span> Quick Apply';
            html += '</a>';
        }
        html += '</div>';

        html += '</div>'; // card-content
        html += '</div>'; // card-inner
        html += '</div>'; // card-item

        return html;
    }

    /**
     * Update pagination
     */
    function updatePagination(pagination) {
        $pagination.empty();

        if (pagination.pages > 1) {
            let paginationHtml = '<ul class="page-numbers">';
            
            // Previous page
            if (pagination.current_page > 1) {
                paginationHtml += `<li><a href="#" class="ccm-page-link" data-page="${pagination.current_page - 1}">&laquo;</a></li>`;
            }

            // Page numbers
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.pages, pagination.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const className = i === pagination.current_page ? 'ccm-page-link ccm-current' : 'ccm-page-link';
                paginationHtml += `<li><a href="#" class="${className}" data-page="${i}">${i}</a></li>`;
            }

            // Next page
            if (pagination.current_page < pagination.pages) {
                paginationHtml += `<li><a href="#" class="ccm-page-link" data-page="${pagination.current_page + 1}">&raquo;</a></li>`;
            }

            paginationHtml += '</ul>';
            $pagination.html(paginationHtml);
        }
    }

    /**
     * Update URL with current filter parameters
     */
    function updateURL(params) {
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState(null, null, newUrl);
    }

    /**
     * Show loading state
     */
    function showLoading() {
        $cardsGrid.html('<div class="ccm-loading"><span class="dashicons dashicons-update-alt ccm-spin"></span><p>Loading credit cards...</p></div>');
    }

    /**
     * Show error state
     */
    function showError() {
        $cardsGrid.html('<div class="ccm-error"><span class="dashicons dashicons-warning"></span><p>Error loading credit cards. Please try again.</p></div>');
    }

    // Initialize when document is ready
    $(document).ready(init);

})(jQuery);
