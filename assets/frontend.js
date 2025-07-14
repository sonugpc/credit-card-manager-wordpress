jQuery(document).ready(function($) {
    
    // AJAX filtering
    $('.ccm-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        if ($(this).closest('.ccm-filters').data('ajax') === true) {
            loadCards();
        }
    });
    
    // Filter reset
    $('.ccm-filter-reset').on('click', function(e) {
        e.preventDefault();
        $('.ccm-filter-form')[0].reset();
        
        if ($('.ccm-filters').data('ajax') === true) {
            loadCards();
        }
    });
    
    // Pagination
    $(document).on('click', '.ccm-page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadCards(page);
    });
    
    // Filter change for real-time filtering
    $('.ccm-filter-select').on('change', function() {
        if ($('.ccm-filters').data('ajax') === true) {
            loadCards();
        }
    });
    
    function loadCards(page = 1) {
        const $container = $('#ccm-cards-results');
        const $form = $('.ccm-filter-form');
        
        // Show loading
        $container.html('<div class="ccm-loading">Loading...</div>');
        
        // Collect form data
        const formData = new FormData($form[0]);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }
        
        params.append('page', page);
        params.append('per_page', 12);
        
        // Make API request
        fetch(ccm_frontend.api_url + 'credit-cards?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.data) {
                    renderCards(data.data);
                    renderPagination(data.pagination);
                } else {
                    $container.html('<div class="ccm-no-results"><p>No credit cards found.</p></div>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                $container.html('<div class="ccm-error"><p>Error loading credit cards. Please try again.</p></div>');
            });
    }
    
    function renderCards(cards) {
        const $container = $('#ccm-cards-results');
        let html = '';
        
        if (cards.length === 0) {
            html = '<div class="ccm-no-results"><p>No credit cards found matching your criteria.</p></div>';
        } else {
            cards.forEach(card => {
                html += renderCard(card);
            });
        }
        
        $container.html(html);
        
        // Trigger custom event
        $(document).trigger('ccm:cards-loaded', [cards]);
    }
    
    function renderCard(card) {
        let badgesHtml = '';
        if (card.featured) {
            badgesHtml += '<span class="ccm-badge ccm-featured">Featured</span>';
        }
        if (card.trending) {
            badgesHtml += '<span class="ccm-badge ccm-trending">Trending</span>';
        }
        
        let imageHtml = '';
        if (card.card_image) {
            imageHtml = `
                <div class="ccm-card-image">
                    <img src="${card.card_image}" alt="${card.title}" />
                    ${badgesHtml}
                </div>
            `;
        }
        
        let ratingHtml = '';
        if (card.rating) {
            const stars = '⭐'.repeat(Math.floor(card.rating));
            ratingHtml = `
                <div class="ccm-rating">
                    <span class="ccm-stars">${stars}</span>
                    <span class="ccm-rating-number">${card.rating}/5</span>
                    ${card.review_count ? `<span class="ccm-review-count">(${card.review_count} reviews)</span>` : ''}
                </div>
            `;
        }
        
        let bankHtml = '';
        if (card.bank) {
            bankHtml = `<div class="ccm-card-bank">${card.bank.name}</div>`;
        }
        
        let applyButtonHtml = '';
        if (card.apply_link) {
            applyButtonHtml = `
                <a href="${card.apply_link}" class="ccm-btn ccm-btn-apply" target="_blank" rel="noopener noreferrer">
                    Apply Now
                </a>
            `;
        }
        
        return `
            <div class="ccm-card-item" data-id="${card.id}">
                <div class="ccm-card-inner">
                    ${imageHtml}
                    <div class="ccm-card-content">
                        <h3 class="ccm-card-title">
                            <a href="${card.link}">${card.title}</a>
                        </h3>
                        ${bankHtml}
                        <div class="ccm-card-meta">
                            ${ratingHtml}
                            ${card.annual_fee ? `<div class="ccm-annual-fee"><strong>Annual Fee:</strong> ${card.annual_fee}</div>` : ''}
                            ${card.cashback_rate ? `<div class="ccm-cashback-rate"><strong>Reward Rate:</strong> ${card.cashback_rate}</div>` : ''}
                        </div>
                        ${card.excerpt ? `<div class="ccm-card-excerpt">${card.excerpt}</div>` : ''}
                        <div class="ccm-card-actions">
                            <a href="${card.link}" class="ccm-btn ccm-btn-details">View Details</a>
                            ${applyButtonHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function renderPagination(pagination) {
        if (!pagination || pagination.pages <= 1) {
            $('.ccm-pagination').html('');
            return;
        }
        
        let html = '<div class="ccm-pagination">';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += `<a href="#" class="ccm-page-link" data-page="${pagination.current_page - 1}">&laquo; Previous</a>`;
        }
        
        // Page numbers
        const start = Math.max(1, pagination.current_page - 2);
        const end = Math.min(pagination.pages, pagination.current_page + 2);
        
        for (let i = start; i <= end; i++) {
            const current = i === pagination.current_page ? ' ccm-current' : '';
            html += `<a href="#" class="ccm-page-link${current}" data-page="${i}">${i}</a>`;
        }
        
        // Next button
        if (pagination.current_page < pagination.pages) {
            html += `<a href="#" class="ccm-page-link" data-page="${pagination.current_page + 1}">Next &raquo;</a>`;
        }
        
        html += '</div>';
        
        // Update or create pagination
        if ($('.ccm-pagination').length) {
            $('.ccm-pagination').replaceWith(html);
        } else {
            $('.ccm-cards-container').append(html);
        }
    }
    
    // Card comparison functionality
    let comparedCards = [];
    const maxCompare = 3;
    
    // Add compare buttons to cards
    $(document).on('click', '.ccm-card-item', function(e) {
        if (e.target.classList.contains('ccm-compare-btn')) {
            e.preventDefault();
            const cardId = $(this).data('id');
            toggleComparison(cardId);
        }
    });
    
    function toggleComparison(cardId) {
        const index = comparedCards.indexOf(cardId);
        
        if (index > -1) {
            // Remove from comparison
            comparedCards.splice(index, 1);
            $(`.ccm-card-item[data-id="${cardId}"] .ccm-compare-btn`).removeClass('active').text('Compare');
        } else {
            // Add to comparison
            if (comparedCards.length >= maxCompare) {
                alert(`You can only compare up to ${maxCompare} cards at once.`);
                return;
            }
            
            comparedCards.push(cardId);
            $(`.ccm-card-item[data-id="${cardId}"] .ccm-compare-btn`).addClass('active').text('Remove');
        }
        
        updateComparisonBar();
    }
    
    function updateComparisonBar() {
        if (comparedCards.length === 0) {
            $('.ccm-comparison-bar').remove();
            return;
        }
        
        if (!$('.ccm-comparison-bar').length) {
            $('body').append(`
                <div class="ccm-comparison-bar">
                    <div class="ccm-comparison-content">
                        <span class="ccm-comparison-count"></span>
                        <button class="ccm-compare-now-btn">Compare Now</button>
                        <button class="ccm-clear-comparison">Clear All</button>
                    </div>
                </div>
            `);
        }
        
        $('.ccm-comparison-count').text(`${comparedCards.length} card(s) selected for comparison`);
        $('.ccm-compare-now-btn').prop('disabled', comparedCards.length < 2);
    }
    
    // Clear comparison
    $(document).on('click', '.ccm-clear-comparison', function() {
        comparedCards = [];
        $('.ccm-compare-btn').removeClass('active').text('Compare');
        $('.ccm-comparison-bar').remove();
    });
    
    // Compare now
    $(document).on('click', '.ccm-compare-now-btn', function() {
        if (comparedCards.length >= 2) {
            const compareUrl = window.location.origin + window.location.pathname + '?compare=' + comparedCards.join(',');
            window.open(compareUrl, '_blank');
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        $(document).on('ccm:cards-loaded', function() {
            $('.ccm-card-image img[data-src]').each(function() {
                imageObserver.observe(this);
            });
        });
    }
});
