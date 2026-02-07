/**
 * Credit Card Manager Frontend JavaScript
 */
(function ($) {
  "use strict";

  // Initialize when document is ready
  $(document).ready(function () {
    initComparisonFeature();
    initFilterToggle();
    initSortingFeature();
    initFilterReset();
  });

  /**
   * Initialize comparison feature
   */
  function initComparisonFeature() {
    const compareButtons = document.querySelectorAll(".cc-btn-compare");
    const comparisonBar = document.getElementById("comparison-bar");
    const clearComparisonBtn = document.getElementById("clear-comparison");
    const compareNowBtn = document.getElementById("compare-now");
    const selectedCountEl = document.getElementById("selected-count");

    // Skip if elements don't exist (not on archive page)
    if (!comparisonBar || !compareButtons.length) {
      return;
    }

    let selectedCards = [];
    const maxCompare = 3;

    // Load selected cards from localStorage
    function loadSelectedCards() {
      const saved = localStorage.getItem("cc_compare_cards");
      if (saved) {
        try {
          selectedCards = JSON.parse(saved);
          updateComparisonUI();
        } catch (e) {
          console.error("Error loading saved comparison data", e);
          selectedCards = [];
        }
      }
    }

    // Save selected cards to localStorage
    function saveSelectedCards() {
      localStorage.setItem("cc_compare_cards", JSON.stringify(selectedCards));
    }

    // Update UI based on selected cards
    function updateComparisonUI() {
      // Update buttons
      compareButtons.forEach((btn) => {
        const cardId = btn.getAttribute("data-id");
        const isSelected = selectedCards.includes(cardId);

        btn.classList.toggle("active", isSelected);

        // Update button text and icon (only for buttons with SVG)
        const svgElement = btn.querySelector("svg");
        if (svgElement) {
          if (isSelected) {
            btn.innerHTML = btn.innerHTML.replace("Compare", "Remove");
            svgElement.outerHTML =
              '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
          } else {
            btn.innerHTML = btn.innerHTML.replace("Remove", "Compare");
            svgElement.outerHTML =
              '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line><line x1="6" y1="8" x2="6" y2="4"></line></svg>';
          }
        }
      });

      // Update comparison bar
      if (selectedCards.length > 0) {
        comparisonBar.classList.add("active");
        selectedCountEl.textContent = selectedCards.length;
        compareNowBtn.disabled = selectedCards.length < 2;
      } else {
        comparisonBar.classList.remove("active");
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

    // Add click handlers to compare buttons
    compareButtons.forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        const cardId = this.getAttribute("data-id");
        toggleCardSelection(cardId);
      });
    });

    // Clear comparison
    if (clearComparisonBtn) {
      clearComparisonBtn.addEventListener("click", function () {
        selectedCards = [];
        saveSelectedCards();
        updateComparisonUI();
      });
    }

    // Compare now
    if (compareNowBtn) {
      compareNowBtn.addEventListener("click", function () {
        if (selectedCards.length >= 2) {
          const archiveContainer = document.querySelector(
            ".cc-archive-container",
          );
          const archiveUrl = archiveContainer
            ? archiveContainer.getAttribute("data-archive-url")
            : window.location.href.split("?")[0];
          const compareUrl = `${archiveUrl}?compare=${selectedCards.join(",")}`;
          window.location.href = compareUrl;
        }
      });
    }

    // Initialize on page load
    loadSelectedCards();
  }

  /**
   * Initialize filter toggle
   */
  function initFilterToggle() {
    const toggleBtn = document.getElementById("toggle-filters");
    const filterContent = document.getElementById("filter-content");

    if (toggleBtn && filterContent) {
      toggleBtn.addEventListener("click", function () {
        const isVisible = filterContent.style.display !== "none";
        filterContent.style.display = isVisible ? "none" : "grid";
        const spanEl = toggleBtn.querySelector("span");
        if (spanEl) {
          spanEl.textContent = isVisible ? "Show Filters" : "Hide Filters";
        }

        // Update icon (only if SVG exists)
        const svgElement = toggleBtn.querySelector("svg");
        if (svgElement) {
          if (isVisible) {
            svgElement.outerHTML =
              '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
          } else {
            svgElement.outerHTML =
              '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
          }
        }
      });
    }
  }

  /**
   * Initialize sorting feature
   */
  function initSortingFeature() {
    const sortSelect = document.getElementById("sort-select");

    if (sortSelect) {
      sortSelect.addEventListener("change", function () {
        const [sort, order] = this.value.split("-");
        const url = new URL(window.location.href);
        url.searchParams.set("sort_by", sort);
        url.searchParams.set("sort_order", order);
        window.location.href = url.toString();
      });
    }
  }

  /**
   * Initialize filter reset
   */
  function initFilterReset() {
    const resetBtn = document.querySelector(".cc-filter-reset");

    if (resetBtn) {
      resetBtn.addEventListener("click", function (e) {
        e.preventDefault();
        const url = new URL(window.location.href);

        // Clear all filter parameters
        url.searchParams.delete("bank");
        url.searchParams.delete("network_type");
        url.searchParams.delete("category");
        url.searchParams.delete("min_rating");
        url.searchParams.delete("max_annual_fee");
        url.searchParams.delete("featured");
        url.searchParams.delete("trending");

        // Keep sort parameters
        const sortBy = url.searchParams.get("sort_by");
        const sortOrder = url.searchParams.get("sort_order");

        // Reset URL
        window.location.href = url.toString();
      });
    }
  }
})(jQuery);
