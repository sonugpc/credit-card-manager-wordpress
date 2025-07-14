/**
 * Credit Card Manager Frontend JavaScript
 */

(function ($) {
  "use strict";

  // Global variables
  let cardsData = [];
  let filtersData = {};
  let currentPage = 1;
  let totalPages = 1;
  let compareCards = [];
  const MAX_COMPARE_CARDS = 4;

  // DOM elements
  const $filterForm = $("#ccm-filter-form");
  const $cardsGrid = $("#ccm-cards-grid");
  const $pagination = $("#ccm-pagination");
  const $totalCards = $("#ccm-total-cards");
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
    // Load filters data
    loadFilters();

    // Load initial cards
    loadCards();

    // Set up event listeners
    setupEventListeners();
  }

  /**
   * Set up event listeners
   */
  function setupEventListeners() {
    // Filter form submission
    $filterForm.on("submit", function (e) {
      e.preventDefault();
      currentPage = 1;
      loadCards();
    });

    // Reset filters
    $filterReset.on("click", function () {
      $filterForm[0].reset();
      currentPage = 1;
      loadCards();
    });

    // Pagination clicks
    $pagination.on("click", ".ccm-page-link", function (e) {
      e.preventDefault();
      currentPage = parseInt($(this).data("page"));
      loadCards();

      // Scroll to top of cards section
      $("html, body").animate(
        {
          scrollTop: $(".ccm-cards-section").offset().top - 100,
        },
        500
      );
    });

    // Compare checkbox clicks
    $cardsGrid.on("change", ".ccm-compare-input", function () {
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
    });

    // Remove card from compare
    $compareCards.on("click", ".ccm-compare-remove", function () {
      const cardId = $(this).data("id");

      // Uncheck the checkbox
      $('.ccm-compare-input[data-id="' + cardId + '"]').prop("checked", false);

      // Remove from compare list
      compareCards = compareCards.filter((card) => card.id !== cardId);

      updateCompareSection();
    });

    // Clear all compare cards
    $compareClear.on("click", function () {
      // Uncheck all checkboxes
      $(".ccm-compare-input").prop("checked", false);

      // Clear compare list
      compareCards = [];

      updateCompareSection();
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
  }

  /**
   * Load filters data from API
   */
  function loadFilters() {
    $.ajax({
      url: ccm_frontend.api_url + "credit-cards/filters",
      method: "GET",
      beforeSend: function (xhr) {
        xhr.setRequestHeader("X-WP-Nonce", ccm_frontend.nonce);
      },
      success: function (response) {
        filtersData = response;
        populateFilters();
      },
      error: function (xhr) {
        console.error("Error loading filters:", xhr);
      },
    });
  }

  /**
   * Populate filter dropdowns
   */
  function populateFilters() {
    // Banks
    if (filtersData.banks && filtersData.banks.length) {
      const $bankFilter = $("#ccm-bank-filter");
      filtersData.banks.forEach(function (bank) {
        $bankFilter.append(
          $("<option></option>")
            .val(bank.slug)
            .text(bank.name + " (" + bank.count + ")")
        );
      });
    }

    // Network types
    if (filtersData.network_types && filtersData.network_types.length) {
      const $networkFilter = $("#ccm-network-filter");
      filtersData.network_types.forEach(function (network) {
        $networkFilter.append(
          $("<option></option>")
            .val(network.slug)
            .text(network.name + " (" + network.count + ")")
        );
      });
    }

    // Categories
    if (filtersData.categories && filtersData.categories.length) {
      const $categoryFilter = $("#ccm-category-filter");
      filtersData.categories.forEach(function (category) {
        $categoryFilter.append(
          $("<option></option>")
            .val(category.slug)
            .text(category.name + " (" + category.count + ")")
        );
      });
    }
  }

  /**
   * Load cards from API
   */
  function loadCards() {
    // Show loading
    $cardsGrid.html(
      '<div class="ccm-loading"><span class="dashicons dashicons-update-alt ccm-spin"></span><p>Loading credit cards...</p></div>'
    );

    // Get filter values
    const filterData = $filterForm.serialize() + "&page=" + currentPage;

    $.ajax({
      url: ccm_frontend.api_url + "credit-cards",
      method: "GET",
      data: filterData,
      beforeSend: function (xhr) {
        xhr.setRequestHeader("X-WP-Nonce", ccm_frontend.nonce);
      },
      success: function (response) {
        cardsData = response.data;
        totalPages = response.pagination.pages;
        currentPage = response.pagination.current_page;

        renderCards();
        renderPagination();

        // Update total count
        $totalCards.text(response.pagination.total);
      },
      error: function (xhr) {
        console.error("Error loading cards:", xhr);
        $cardsGrid.html(
          '<div class="ccm-no-results"><p>Error loading credit cards. Please try again.</p></div>'
        );
      },
    });
  }

  /**
   * Render cards to the grid
   */
  function renderCards() {
    if (!cardsData.length) {
      $cardsGrid.html(
        '<div class="ccm-no-results"><p>No credit cards found matching your criteria.</p></div>'
      );
      return;
    }

    $cardsGrid.empty();

    cardsData.forEach(function (card) {
      // Calculate rating percentage for stars
      const ratingPercent = (card.rating / 5) * 100;
      card.rating_percent = ratingPercent;

      // Use Mustache-like template rendering
      let cardHtml = $("#ccm-card-template").html();

      // Replace simple variables
      cardHtml = cardHtml.replace(/\{\{id\}\}/g, card.id);
      cardHtml = cardHtml.replace(/\{\{title\}\}/g, card.title);
      cardHtml = cardHtml.replace(/\{\{link\}\}/g, card.link);
      cardHtml = cardHtml.replace(/\{\{card_image\}\}/g, card.card_image);
      cardHtml = cardHtml.replace(/\{\{rating\}\}/g, card.rating);
      cardHtml = cardHtml.replace(
        /\{\{rating_percent\}\}/g,
        card.rating_percent
      );

      // Handle conditional sections
      if (card.featured) {
        cardHtml = cardHtml.replace(
          /\{\{#featured\}\}([\s\S]*?)\{\{\/featured\}\}/g,
          "$1"
        );
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#featured\}\}([\s\S]*?)\{\{\/featured\}\}/g,
          ""
        );
      }

      if (card.trending) {
        cardHtml = cardHtml.replace(
          /\{\{#trending\}\}([\s\S]*?)\{\{\/trending\}\}/g,
          "$1"
        );
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#trending\}\}([\s\S]*?)\{\{\/trending\}\}/g,
          ""
        );
      }

      if (card.bank) {
        cardHtml = cardHtml.replace(
          /\{\{#bank\}\}([\s\S]*?)\{\{\/bank\}\}/g,
          "$1"
        );
        cardHtml = cardHtml.replace(/\{\{bank\.name\}\}/g, card.bank.name);
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#bank\}\}([\s\S]*?)\{\{\/bank\}\}/g,
          ""
        );
      }

      if (card.review_count) {
        cardHtml = cardHtml.replace(
          /\{\{#review_count\}\}([\s\S]*?)\{\{\/review_count\}\}/g,
          "$1"
        );
        cardHtml = cardHtml.replace(/\{\{review_count\}\}/g, card.review_count);
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#review_count\}\}([\s\S]*?)\{\{\/review_count\}\}/g,
          ""
        );
      }

      if (card.annual_fee) {
        cardHtml = cardHtml.replace(
          /\{\{#annual_fee\}\}([\s\S]*?)\{\{\/annual_fee\}\}/g,
          "$1"
        );
        cardHtml = cardHtml.replace(/\{\{annual_fee\}\}/g, card.annual_fee);
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#annual_fee\}\}([\s\S]*?)\{\{\/annual_fee\}\}/g,
          ""
        );
      }

      if (card.cashback_rate) {
        cardHtml = cardHtml.replace(
          /\{\{#cashback_rate\}\}([\s\S]*?)\{\{\/cashback_rate\}\}/g,
          "$1"
        );
        cardHtml = cardHtml.replace(
          /\{\{cashback_rate\}\}/g,
          card.cashback_rate
        );
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#cashback_rate\}\}([\s\S]*?)\{\{\/cashback_rate\}\}/g,
          ""
        );
      }

      if (card.welcome_bonus) {
        cardHtml = cardHtml.replace(
          /\{\{#welcome_bonus\}\}([\s\S]*?)\{\{\/welcome_bonus\}\}/g,
          "$1"
        );
        cardHtml = cardHtml.replace(
          /\{\{welcome_bonus\}\}/g,
          card.welcome_bonus
        );
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#welcome_bonus\}\}([\s\S]*?)\{\{\/welcome_bonus\}\}/g,
          ""
        );
      }

      if (card.excerpt) {
        cardHtml = cardHtml.replace(
          /\{\{#excerpt\}\}([\s\S]*?)\{\{\/excerpt\}\}/g,
          "$1"
        );
        cardHtml = cardHtml.replace(/\{\{excerpt\}\}/g, card.excerpt);
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#excerpt\}\}([\s\S]*?)\{\{\/excerpt\}\}/g,
          ""
        );
      }

      if (card.apply_link) {
        cardHtml = cardHtml.replace(
          /\{\{#apply_link\}\}([\s\S]*?)\{\{\/apply_link\}\}/g,
          "$1"
        );
        cardHtml = cardHtml.replace(/\{\{apply_link\}\}/g, card.apply_link);
      } else {
        cardHtml = cardHtml.replace(
          /\{\{#apply_link\}\}([\s\S]*?)\{\{\/apply_link\}\}/g,
          ""
        );
      }

      $cardsGrid.append(cardHtml);

      // Check if card is in compare list and check the checkbox
      if (compareCards.some((compareCard) => compareCard.id === card.id)) {
        $('.ccm-compare-input[data-id="' + card.id + '"]').prop(
          "checked",
          true
        );
      }
    });
  }

  /**
   * Render pagination
   */
  function renderPagination() {
    $pagination.empty();

    if (totalPages <= 1) {
      return;
    }

    // Previous button
    if (currentPage > 1) {
      $pagination.append(
        '<a href="#" class="ccm-page-link" data-page="' +
          (currentPage - 1) +
          '">&laquo;</a>'
      );
    }

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    for (let i = startPage; i <= endPage; i++) {
      const activeClass = i === currentPage ? " ccm-current" : "";
      $pagination.append(
        '<a href="#" class="ccm-page-link' +
          activeClass +
          '" data-page="' +
          i +
          '">' +
          i +
          "</a>"
      );
    }

    // Next button
    if (currentPage < totalPages) {
      $pagination.append(
        '<a href="#" class="ccm-page-link" data-page="' +
          (currentPage + 1) +
          '">&raquo;</a>'
      );
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
    // Get full data for selected cards
    const cardIds = compareCards.map((card) => card.id);
    const selectedCards = [];

    // Find cards in cardsData
    cardIds.forEach(function (id) {
      const card = cardsData.find((card) => card.id === id);
      if (card) {
        selectedCards.push(card);
      }
    });

    if (selectedCards.length < 2) {
      alert("Please select at least 2 cards to compare.");
      return;
    }

    // Build comparison table
    buildComparisonTable(selectedCards);

    // Show modal
    $compareModal.show();
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
  }

  // Initialize when document is ready
  $(document).ready(init);
})(jQuery);
