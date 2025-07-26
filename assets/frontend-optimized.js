/**
 * Credit Card Manager - Optimized Frontend JavaScript
 * Consolidated and minified functionality
 */
(function ($) {
    'use strict';

    // Configuration from localized script
    const config = {
        maxCompare: 4,
        storageKey: 'ccm_compare_cards',
        selectors: {
            compareBtn: '.ccv2-btn-compare',
            comparisonBar: '.ccv2-comparison-bar',
            clearBtn: '.ccv2-btn-clear',
            compareNowBtn: '.ccv2-btn-compare-now',
            countEl: '.ccv2-comparison-count',
            filterForm: '#ccv2-filter-form',
            filterToggle: '.ccv2-filter-toggle'
        }
    };

    // Initialize on DOM ready
    $(document).ready(init);

    function init() {
        if (typeof ccm_frontend !== 'undefined') {
            if (ccm_frontend.is_archive) initArchiveFeatures();
            if (ccm_frontend.is_compare) initCompareFeatures();
            if (ccm_frontend.is_single) initSingleFeatures();
        }
        initCommonFeatures();
    }

    // Archive page features
    function initArchiveFeatures() {
        initComparison();
        initFilters();
    }

    // Compare page features  
    function initCompareFeatures() {
        initTableScroll();
        initPrintMode();
    }

    // Single page features
    function initSingleFeatures() {
        initTabNavigation();
        initScrollSpy();
    }

    // Common features for all pages
    function initCommonFeatures() {
        initSmoothScroll();
        initLoadingStates();
    }

    // Comparison functionality
    function initComparison() {
        let selectedCards = getStoredCards();
        const $compareBar = $(config.selectors.comparisonBar);
        
        if (!$compareBar.length) return;

        updateComparisonUI();

        // Compare button click handler
        $(document).on('click', config.selectors.compareBtn, function(e) {
            e.preventDefault();
            const cardId = $(this).data('id');
            toggleCardSelection(cardId);
        });

        // Clear comparison
        $(config.selectors.clearBtn).on('click', function() {
            selectedCards = [];
            saveCards();
            updateComparisonUI();
        });

        // Compare now button
        $(config.selectors.compareNowBtn).on('click', function() {
            if (selectedCards.length >= 2) {
                window.location.href = addQueryParam(window.location.origin + '/compare-cards/', 'cards', selectedCards.join(','));
            }
        });

        function toggleCardSelection(cardId) {
            const index = selectedCards.indexOf(cardId);
            
            if (index > -1) {
                selectedCards.splice(index, 1);
            } else if (selectedCards.length < config.maxCompare) {
                selectedCards.push(cardId);
            } else {
                showToast('Maximum ' + config.maxCompare + ' cards can be compared', 'warning');
                return;
            }
            
            saveCards();
            updateComparisonUI();
        }

        function updateComparisonUI() {
            // Update button states
            $(config.selectors.compareBtn).each(function() {
                const cardId = $(this).data('id');
                $(this).toggleClass('active', selectedCards.includes(cardId));
            });

            // Update comparison bar
            const count = selectedCards.length;
            $(config.selectors.countEl).text(count);
            $compareBar.toggleClass('active', count > 0);
            $(config.selectors.compareNowBtn).prop('disabled', count < 2);
        }

        function getStoredCards() {
            try {
                return JSON.parse(localStorage.getItem(config.storageKey) || '[]');
            } catch (e) {
                return [];
            }
        }

        function saveCards() {
            localStorage.setItem(config.storageKey, JSON.stringify(selectedCards));
        }
    }

    // Filter functionality
    function initFilters() {
        const $form = $(config.selectors.filterForm);
        if (!$form.length) return;

        // Auto-submit on filter change
        $form.on('change', 'select', debounce(function() {
            $form.submit();
        }, 300));

        // Filter toggle
        $(config.selectors.filterToggle).on('click', function() {
            const $form = $(config.selectors.filterForm);
            const $icon = $(this).find('.icon');
            
            $form.slideToggle(300);
            $icon.css('transform', $form.is(':visible') ? 'rotate(0deg)' : 'rotate(-90deg)');
        });

        // Clear filters
        window.clearFilters = function() {
            $form.find('select').val('');
            $form.submit();
        };
    }

    // Table scroll indicator
    function initTableScroll() {
        const $wrapper = $('.compare-table-wrapper');
        if (!$wrapper.length) return;

        $wrapper.on('scroll', function() {
            $(this).css('cursor', 'grabbing');
            clearTimeout(this.scrollTimeout);
            this.scrollTimeout = setTimeout(() => {
                $(this).css('cursor', 'grab');
            }, 150);
        });
    }

    // Print mode
    function initPrintMode() {
        $('[onclick="window.print()"]').on('click', function(e) {
            e.preventDefault();
            window.print();
        });
    }

    // Tab navigation for single pages
    function initTabNavigation() {
        $('[data-tab-target]').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('tab-target');
            
            // Update active states
            $('[data-tab-target]').removeClass('active');
            $(this).addClass('active');
            
            $('.tab-content').removeClass('active');
            $(target).addClass('active');
        });
    }

    // Scroll spy for navigation
    function initScrollSpy() {
        const $sections = $('[data-section]');
        const $navLinks = $('[data-scroll-to]');
        
        if (!$sections.length || !$navLinks.length) return;

        $(window).on('scroll', throttle(function() {
            const scrollTop = $(window).scrollTop() + 100;
            
            $sections.each(function() {
                const $section = $(this);
                const sectionTop = $section.offset().top;
                const sectionBottom = sectionTop + $section.outerHeight();
                
                if (scrollTop >= sectionTop && scrollTop < sectionBottom) {
                    const sectionId = $section.data('section');
                    $navLinks.removeClass('active');
                    $(`[data-scroll-to="${sectionId}"]`).addClass('active');
                }
            });
        }, 100));
    }

    // Smooth scrolling
    function initSmoothScroll() {
        $(document).on('click', 'a[href^="#"]', function(e) {
            const href = $(this).attr('href');
            if (href === '#') return;
            
            const $target = $(href);
            if (!$target.length) return;
            
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $target.offset().top - 80
            }, 500);
        });
    }

    // Loading states for buttons
    function initLoadingStates() {
        $(document).on('click', '.ccv2-btn-apply, .compare-btn-apply', function() {
            const $btn = $(this);
            const originalText = $btn.text();
            
            $btn.css('opacity', '0.7').css('pointer-events', 'none');
            
            setTimeout(() => {
                $btn.css('opacity', '1').css('pointer-events', 'auto');
            }, 2000);
        });
    }

    // Utility functions
    function addQueryParam(url, param, value) {
        const separator = url.includes('?') ? '&' : '?';
        return url + separator + param + '=' + encodeURIComponent(value);
    }

    function showToast(message, type = 'info') {
        // Simple toast notification
        const $toast = $(`
            <div class="ccm-toast ccm-toast-${type}" style="
                position: fixed; 
                top: 20px; 
                right: 20px; 
                background: ${type === 'warning' ? 'var(--warning)' : 'var(--primary)'}; 
                color: white; 
                padding: var(--space-4) var(--space-6); 
                border-radius: var(--radius-lg); 
                box-shadow: var(--shadow-lg);
                z-index: 10000;
                font-weight: 600;
                animation: slideInRight 0.3s ease;
            ">${message}</div>
        `);
        
        $('body').append($toast);
        
        setTimeout(() => {
            $toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // Add slide-in animation CSS
    if (!document.getElementById('ccm-toast-styles')) {
        $('<style id="ccm-toast-styles">@keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }</style>').appendTo('head');
    }

})(jQuery);