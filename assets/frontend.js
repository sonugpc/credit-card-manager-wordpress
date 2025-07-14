/**
 * Credit Card Manager Frontend JavaScript
 */

(function ($) {
  "use strict";

  // Global variables
  let compareCards = [];
  const MAX_COMPARE_CARDS = 4;

  // DOM elements
  const $filterForm = $("#ccm-filter-form");
  const $compareSection = $("#ccm-compare-section");
  const $compareCards = $("#ccm-compare-cards");
  const $compareCount = $("#ccm-compare-count");
  const $compareButton = $("#ccm-compare-button");
  const $compareClear = $("#ccm-compare-clear");
  const $compareModal = $("#ccm-compare-modal");
  const $comparisonTable = $("#ccm-comparison-table");
  const $modalClose = $("#ccm-modal-close");
  const $toggleFilters = $("#ccm-toggle-filters");
  const $filtersContainer = $("#ccm-filters-container");
  const $filterReset = $("#ccm-filter-reset");

  /**
   * Initialize the application
   */
  function init() {
    // Set up event listeners
    setupEventListeners();

    // Initialize compare section
    initializeCompareSection();
  }

  /**
   * Set up event listeners
   */
  function setupEventListeners() {
    // Reset filters
    $filterReset.on("click", function () {
      $filterForm[0].reset();
      window.location.href = window.location.pathname;
    });

    // Compare checkbox clicks
    $(document).on("change", ".ccm-compare-input", function () {
      const $this = $(this);
      const cardId = $this.data("id");
      const cardTitle = $this.data("title");
      const cardImage = $this.data("image");

      if ($this.is(":checked")) {
        // Add to compare list
        if (compareCards.length < MAX_COMPARE_CARDS) {
          compareCards.push({
            id: cardId,
            title: cardTitle,
            image: cardImage,
          });
        } else {
          // Max cards reached, uncheck
          $this.prop("checked", false);
          alert(
            "You can compare up to " + MAX_COMPARE_CARDS + " cards at a time."
          );
          return;
        }
      } else {
        // Remove from compare list
        compareCards = compareCards.filter((card) => card.id !== cardId);
      }

      updateCompareSection();

      // Store compare cards in localStorage
      localStorage.setItem("ccm_compare_cards", JSON.stringify(compareCards));
    });

    // Remove card from compare
    $compareCards.on("click", ".ccm-compare-remove", function () {
      const cardId = $(this).data("id");

      // Uncheck the checkbox
      $('.ccm-compare-input[data-id="' + cardId + '"]').prop("checked", false);

      // Remove from compare list
      compareCards = compareCards.filter((card) => card.id !== cardId);

      updateCompareSection();

      // Update localStorage
      localStorage.setItem("ccm_compare_cards", JSON.stringify(compareCards));
    });

    // Clear all compare cards
    $compareClear.on("click", function () {
      // Uncheck all checkboxes
      $(".ccm-compare-input").prop("checked", false);

      // Clear compare list
      compareCards = [];

      updateCompareSection();

      // Clear localStorage
      localStorage.removeItem("ccm_compare_cards");
    });

    // Compare button click
    $compareButton.on("click", function () {
      if (compareCards.length > 1) {
        showCompareModal();
      }
    });

    // Close modal
    $modalClose.on("click", function () {
      $compareModal.hide();
    });

    // Close modal when clicking outside
    $(window).on("click", function (e) {
      if ($(e.target).is($compareModal)) {
        $compareModal.hide();
      }
    });

    // Toggle filters
    $toggleFilters.on("click", function () {
      $filtersContainer.slideToggle(300);
    });

    // Close modal with ESC key
    $(document).on("keydown", function (e) {
      if (e.key === "Escape" && $compareModal.is(":visible")) {
        $compareModal.hide();
      }
    });
  }

  /**
   * Initialize compare section from localStorage
   */
  function initializeCompareSection() {
    // Try to load compare cards from localStorage
    const savedCards = localStorage.getItem("ccm_compare_cards");
    if (savedCards) {
      try {
        compareCards = JSON.parse(savedCards);

        // Check boxes for saved cards
        compareCards.forEach(function (card) {
          $('.ccm-compare-input[data-id="' + card.id + '"]').prop(
            "checked",
            true
          );
        });

        updateCompareSection();
      } catch (e) {
        console.error("Error loading saved compare cards:", e);
        localStorage.removeItem("ccm_compare_cards");
      }
    }
  }

  /**
   * Update compare section
   */
  function updateCompareSection() {
    $compareCards.empty();
    $compareCount.text(compareCards.length + " cards selected");

    if (compareCards.length > 0) {
      $compareSection.show();

      // Render compare cards
      compareCards.forEach(function (card) {
        let cardHtml = $("#ccm-compare-card-template").html();
        cardHtml = cardHtml.replace(/\{\{id\}\}/g, card.id);
        cardHtml = cardHtml.replace(/\{\{title\}\}/g, card.title);
        cardHtml = cardHtml.replace(/\{\{image\}\}/g, card.image);

        $compareCards.append(cardHtml);
      });

      // Enable/disable compare button
      if (compareCards.length > 1) {
        $compareButton.prop("disabled", false);
      } else {
        $compareButton.prop("disabled", true);
      }
    } else {
      $compareSection.hide();
    }
  }

  /**
   * Show compare modal
   */
  function showCompareModal() {
    if (compareCards.length < 2) {
      alert("Please select at least 2 cards to compare.");
      return;
    }

    // Fetch card data for comparison
    fetchCardDataForComparison();
  }

  /**
   * Fetch card data for comparison
   */
  function fetchCardDataForComparison() {
    // Show loading in modal
    $comparisonTable.html(
      '<tr><td colspan="' +
        (compareCards.length + 1) +
        '" style="text-align: center; padding: 30px;"><span class="dashicons dashicons-update-alt ccm-spin" style="font-size: 2rem; margin-bottom: 15px;"></span><p>Loading comparison data...</p></td></tr>'
    );
    $compareModal.show();

    // Get card IDs
    const cardIds = compareCards.map((card) => card.id);

    // Fetch data for each card
    const promises = cardIds.map((id) => {
      return $.ajax({
        url: ccm_frontend.api_url + "credit-cards/" + id,
        method: "GET",
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", ccm_frontend.nonce);
        },
      });
    });

    // When all data is fetched
    $.when
      .apply($, promises)
      .then(function () {
        // Convert arguments to array of card data
        const cards = [];

        // Handle single card case differently
        if (promises.length === 1) {
          cards.push(arguments[0]);
        } else {
          // Multiple cards
          for (let i = 0; i < arguments.length; i++) {
            cards.push(arguments[i][0]);
          }
        }

        // Build comparison table
        buildComparisonTable(cards);
      })
      .fail(function () {
        $comparisonTable.html(
          '<tr><td colspan="' +
            (compareCards.length + 1) +
            '" style="text-align: center; padding: 30px; color: #ef4444;"><span class="dashicons dashicons-warning" style="font-size: 2rem; margin-bottom: 15px;"></span><p>Error loading comparison data. Please try again.</p></td></tr>'
        );
      });
  }

  /**
   * Build comparison table
   */
  function buildComparisonTable(cards) {
    $comparisonTable.empty();

    // Table header
    let headerHtml = "<tr><th>Features</th>";
    cards.forEach(function (card) {
      headerHtml += '<th class="ccm-card-header">';
      headerHtml +=
        '<img src="' + card.card_image + '" alt="' + card.title + '">';
      headerHtml += "<h3>" + card.title + "</h3>";

      if (card.bank && card.bank.name) {
        headerHtml += '<div class="ccm-card-bank">' + card.bank.name + "</div>";
      }

      if (card.apply_link) {
        headerHtml +=
          '<a href="' +
          card.apply_link +
          '" class="ccm-btn ccm-btn-apply" target="_blank" rel="noopener noreferrer">';
        headerHtml +=
          '<span class="dashicons dashicons-external"></span> Apply Now</a>';
      }

      headerHtml += "</th>";
    });
    headerHtml += "</tr>";

    $comparisonTable.append(headerHtml);

    // Basic details section
    $comparisonTable.append(
      '<tr class="ccm-feature-row"><td colspan="' +
        (cards.length + 1) +
        '">Basic Details</td></tr>'
    );

    // Rating
    let ratingHtml = "<tr><td>Rating</td>";
    cards.forEach(function (card) {
      ratingHtml += "<td>";
      if (card.rating) {
        ratingHtml += card.rating + "/5";
      } else {
        ratingHtml += "-";
      }
      ratingHtml += "</td>";
    });
    ratingHtml += "</tr>";
    $comparisonTable.append(ratingHtml);

    // Annual Fee
    let feeHtml = "<tr><td>Annual Fee</td>";
    cards.forEach(function (card) {
      feeHtml += "<td>" + (card.annual_fee || "-") + "</td>";
    });
    feeHtml += "</tr>";
    $comparisonTable.append(feeHtml);

    // Joining Fee
    let joiningFeeHtml = "<tr><td>Joining Fee</td>";
    cards.forEach(function (card) {
      joiningFeeHtml += "<td>" + (card.joining_fee || "-") + "</td>";
    });
    joiningFeeHtml += "</tr>";
    $comparisonTable.append(joiningFeeHtml);

    // Network Type
    let networkHtml = "<tr><td>Network</td>";
    cards.forEach(function (card) {
      networkHtml += "<td>";
      if (card.network_type && card.network_type.name) {
        networkHtml += card.network_type.name;
      } else {
        networkHtml += "-";
      }
      networkHtml += "</td>";
    });
    networkHtml += "</tr>";
    $comparisonTable.append(networkHtml);

    // Rewards section
    $comparisonTable.append(
      '<tr class="ccm-feature-row"><td colspan="' +
        (cards.length + 1) +
        '">Rewards & Benefits</td></tr>'
    );

    // Cashback Rate
    let cashbackHtml = "<tr><td>Reward Rate</td>";
    cards.forEach(function (card) {
      cashbackHtml += "<td>" + (card.cashback_rate || "-") + "</td>";
    });
    cashbackHtml += "</tr>";
    $comparisonTable.append(cashbackHtml);

    // Welcome Bonus
    let bonusHtml = "<tr><td>Welcome Bonus</td>";
    cards.forEach(function (card) {
      bonusHtml += "<td>" + (card.welcome_bonus || "-") + "</td>";
    });
    bonusHtml += "</tr>";
    $comparisonTable.append(bonusHtml);

    // Eligibility section
    $comparisonTable.append(
      '<tr class="ccm-feature-row"><td colspan="' +
        (cards.length + 1) +
        '">Eligibility & Terms</td></tr>'
    );

    // Credit Limit
    let limitHtml = "<tr><td>Credit Limit</td>";
    cards.forEach(function (card) {
      limitHtml += "<td>" + (card.credit_limit || "-") + "</td>";
    });
    limitHtml += "</tr>";
    $comparisonTable.append(limitHtml);

    // Interest Rate
    let interestHtml = "<tr><td>Interest Rate</td>";
    cards.forEach(function (card) {
      interestHtml += "<td>" + (card.interest_rate || "-") + "</td>";
    });
    interestHtml += "</tr>";
    $comparisonTable.append(interestHtml);

    // Processing Time
    let processingHtml = "<tr><td>Processing Time</td>";
    cards.forEach(function (card) {
      processingHtml += "<td>" + (card.processing_time || "-") + "</td>";
    });
    processingHtml += "</tr>";
    $comparisonTable.append(processingHtml);

    // Min Income
    let incomeHtml = "<tr><td>Minimum Income</td>";
    cards.forEach(function (card) {
      incomeHtml += "<td>" + (card.min_income || "-") + "</td>";
    });
    incomeHtml += "</tr>";
    $comparisonTable.append(incomeHtml);

    // Age Requirements
    let ageHtml = "<tr><td>Age Requirements</td>";
    cards.forEach(function (card) {
      ageHtml += "<td>";
      if (card.min_age || card.max_age) {
        if (card.min_age && card.max_age) {
          ageHtml += card.min_age + " - " + card.max_age;
        } else if (card.min_age) {
          ageHtml += "Min: " + card.min_age;
        } else if (card.max_age) {
          ageHtml += "Max: " + card.max_age;
        }
      } else {
        ageHtml += "-";
      }
      ageHtml += "</td>";
    });
    ageHtml += "</tr>";
    $comparisonTable.append(ageHtml);

    // Pros & Cons section
    $comparisonTable.append(
      '<tr class="ccm-feature-row"><td colspan="' +
        (cards.length + 1) +
        '">Pros & Cons</td></tr>'
    );

    // Pros
    let prosHtml = "<tr><td>Pros</td>";
    cards.forEach(function (card) {
      prosHtml += "<td>";
      if (card.pros && card.pros.length) {
        prosHtml += "<ul>";
        card.pros.forEach(function (pro) {
          prosHtml += "<li>" + pro + "</li>";
        });
        prosHtml += "</ul>";
      } else {
        prosHtml += "-";
      }
      prosHtml += "</td>";
    });
    prosHtml += "</tr>";
    $comparisonTable.append(prosHtml);

    // Cons
    let consHtml = "<tr><td>Cons</td>";
    cards.forEach(function (card) {
      consHtml += "<td>";
      if (card.cons && card.cons.length) {
        consHtml += "<ul>";
        card.cons.forEach(function (con) {
          consHtml += "<li>" + con + "</li>";
        });
        consHtml += "</ul>";
      } else {
        consHtml += "-";
      }
      consHtml += "</td>";
    });
    consHtml += "</tr>";
    $comparisonTable.append(consHtml);

    // Best For
    let bestForHtml = "<tr><td>Best For</td>";
    cards.forEach(function (card) {
      bestForHtml += "<td>";
      if (card.best_for && card.best_for.length) {
        bestForHtml += "<ul>";
        card.best_for.forEach(function (item) {
          bestForHtml += "<li>" + item + "</li>";
        });
        bestForHtml += "</ul>";
      } else {
        bestForHtml += "-";
      }
      bestForHtml += "</td>";
    });
    bestForHtml += "</tr>";
    $comparisonTable.append(bestForHtml);
  }

  // Initialize when document is ready
  $(document).ready(init);
})(jQuery);
