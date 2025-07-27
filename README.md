# Credit Card Manager Plugin

A comprehensive WordPress plugin for managing and displaying credit cards with advanced filtering, comparison tools, and SEO optimization. Perfect for financial comparison websites and credit card affiliate platforms.

## 🚀 Features

- **Complete Card Management** - Custom post type with rich metadata
- **Advanced Filtering & Search** - Filter by bank, network, category, rating, fees
- **Card Comparison Tool** - Side-by-side comparison of multiple cards
- **Professional Templates** - Responsive, SEO-optimized templates
- **REST API** - Complete API for external integrations
- **Schema Markup** - Rich snippets for better search visibility
- **Mobile Responsive** - Optimized for all devices
- **SEO Friendly** - Built-in SEO optimization with RankMath compatibility

## 📋 Table of Contents

- [Installation](#installation)
- [Shortcodes](#shortcodes)
- [REST API](#rest-api)
- [Template Structure](#template-structure)
- [Taxonomies](#taxonomies)
- [Meta Fields](#meta-fields)
- [Customization](#customization)
- [SEO Features](#seo-features)
- [Developer Guide](#developer-guide)

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Credit Cards** in your WordPress admin to start adding cards
4. Configure taxonomies (Banks, Network Types, Categories) as needed

## 📝 Shortcodes

### [credit-card] - Single Card Display

Display a single credit card with customizable options.

```php
[credit-card id="123" mode="full" show_image="yes" show_rating="yes"]
```

**Parameters:**
- `id` (required) - Credit card post ID
- `mode` - Display mode: "mini" or "full" (default: "mini")
- `show_image` - Show card image: "yes"/"no" (default: "yes")
- `show_rating` - Show rating: "yes"/"no" (default: "yes")
- `show_fees` - Show fees: "yes"/"no" (default: "yes")
- `show_benefits` - Show benefits: "yes"/"no" (default: "yes")

**Examples:**
```php
// Basic card display
[credit-card id="123"]

// Full card with all details
[credit-card id="123" mode="full"]

// Mini card without rating
[credit-card id="123" mode="mini" show_rating="no"]
```

### [credit_card_grid] - Card Grid Display

Display a filterable grid of credit cards.

```php
[credit_card_grid count="6" bank="hdfc" category="cashback" show_filters="yes"]
```

**Parameters:**
- `count` - Number of cards to display (default: "6")
- `bank` - Filter by bank/store slug
- `category` - Filter by card category slug  
- `network_type` - Filter by network type slug
- `featured` - Show only featured cards: "1"
- `trending` - Show only trending cards: "1"
- `min_rating` - Minimum rating filter (0-5)
- `max_annual_fee` - Maximum annual fee filter
- `sort_by` - Sort field: "rating", "annual_fee", "review_count" (default: "rating")
- `sort_order` - Sort order: "asc", "desc" (default: "desc")
- `show_filters` - Show filter interface: "yes"/"no" (default: "yes")

**Examples:**
```php
// Basic grid
[credit_card_grid count="8"]

// Featured cashback cards
[credit_card_grid category="cashback" featured="1" count="4"]

// HDFC cards sorted by rating
[credit_card_grid bank="hdfc" sort_by="rating" sort_order="desc"]

// Cards with no annual fee under specific limit
[credit_card_grid max_annual_fee="500" min_rating="4"]

// Grid without filters
[credit_card_grid count="12" show_filters="no"]
```

### [compare-card] - Card Comparison

Display side-by-side comparison of multiple cards.

```php
[compare-card ids="123,456,789"]
```

**Parameters:**
- `ids` (required) - Comma-separated list of card IDs to compare

**Examples:**
```php
// Compare three cards
[compare-card ids="123,456,789"]

// Compare two cards
[compare-card ids="123,456"]
```

## 🌐 REST API

### Base URL
```
/wp-json/ccm/v1/
```

### Endpoints

#### GET /credit-cards
List credit cards with filtering options.

**Parameters:**
- `bank` - Filter by bank slug
- `network_type` - Filter by network type slug
- `category` - Filter by category slug
- `featured` - Filter featured cards (1/0)
- `trending` - Filter trending cards (1/0)
- `min_rating` - Minimum rating (0-5)
- `max_annual_fee` - Maximum annual fee
- `sort_by` - Sort field (rating, annual_fee, review_count)
- `sort_order` - Sort order (asc, desc)
- `per_page` - Items per page (default: 12)
- `page` - Page number

**Example:**
```bash
GET /wp-json/ccm/v1/credit-cards?bank=hdfc&category=cashback&min_rating=4
```

#### GET /credit-cards/{id}
Get single credit card details.

**Example:**
```bash
GET /wp-json/ccm/v1/credit-cards/123
```

#### GET /credit-cards/filters
Get available filter options.

**Response includes:**
- Available banks
- Network types
- Categories
- Rating ranges
- Fee ranges

**Example:**
```bash
GET /wp-json/ccm/v1/credit-cards/filters
```

### WordPress Native Endpoints

```bash
# Credit cards
GET /wp-json/wp/v2/credit-cards

# Banks/Stores
GET /wp-json/wp/v2/stores

# Network types
GET /wp-json/wp/v2/network-types

# Card categories
GET /wp-json/wp/v2/card-categories
```

## 📁 Template Structure

### Main Templates

- **archive-credit-card.php** - Card archive/listing page
- **single-credit-card.php** - Individual card detail page
- **page-compare-cards.php** - Card comparison page

### Template Parts

- **template-parts/card-item.php** - Reusable card component
- **template-parts/compare-table.php** - Comparison table
- **template-parts/filter-section.php** - Filter interface
- **template-parts/pagination.php** - Pagination controls

### Template Override

Copy templates to your theme directory:
```
your-theme/
└── ccm-templates/
    ├── archive-credit-card.php
    ├── single-credit-card.php
    └── template-parts/
        └── card-item.php
```

## 🏷️ Taxonomies

### Banks/Stores (store)
- **Purpose:** Credit card issuers (HDFC, ICICI, SBI, etc.)
- **Hierarchical:** No
- **Slug:** `/store/hdfc-bank/`

### Network Types (network-type)
- **Purpose:** Card networks (Visa, Mastercard, American Express)
- **Hierarchical:** No
- **Default Terms:** Visa, Mastercard, American Express, Discover, RuPay
- **Slug:** `/network-type/visa/`

### Card Categories (card-category)
- **Purpose:** Card types and purposes
- **Hierarchical:** Yes (supports parent/child relationships)
- **Default Terms:** 
  - Cashback
  - Travel
  - Rewards
  - Business
  - Premium
  - Secured
  - Student
  - No Annual Fee
- **Slug:** `/card-category/cashback/`

## 📊 Meta Fields

### Basic Information
```php
'rating'           // Card rating (0-5)
'review_count'     // Number of reviews
'annual_fee'       // Annual fee amount
'joining_fee'      // Joining fee amount
'apply_link'       // Application URL
```

### Financial Details
```php
'welcome_bonus'         // Welcome bonus description
'welcome_bonus_points'  // Welcome bonus amount
'welcome_bonus_type'    // Type: points, money, cashback
'cashback_rate'         // Cashback/reward rate
'credit_limit'          // Credit limit range
'interest_rate'         // Interest rate
'min_income'           // Minimum income requirement
'processing_time'      // Application processing time
'min_age'             // Minimum age requirement
'max_age'             // Maximum age requirement
```

### Features and Benefits (Arrays)
```php
'pros'         // Array of advantages
'cons'         // Array of disadvantages
'best_for'     // Array of ideal use cases
'features'     // Complex array: title, description, icon
'rewards'      // Complex array: category, rate, description
'fees'         // Complex array: type, amount, description
'eligibility'  // Complex array: criteria, value
'documents'    // Array of required documents
```

### Display Options
```php
'featured'     // Featured card flag
'trending'     // Trending card flag
'theme_color'  // Card theme color
'gradient'     // Background gradient
```

### Scoring System
```php
'overall_score'   // Overall rating (0-5)
'reward_score'    // Rewards rating (0-5)
'fees_score'      // Fees rating (0-5)
'benefits_score'  // Benefits rating (0-5)
'support_score'   // Support rating (0-5)
```

## 🎨 Customization

### CSS Classes

**Archive Page:**
- `.ccv2-container` - Main container
- `.ccv2-cards-grid` - Cards grid
- `.ccv2-filter-section` - Filter area
- `.ccv2-comparison-bar` - Comparison toolbar

**Single Page:**
- `.cc-single-wrapper` - Page wrapper
- `.cc-hero` - Hero section
- `.cc-nav-sticky` - Sticky navigation
- `.cc-content` - Main content area
- `.cc-section` - Content sections

**Card Components:**
- `.ccv2-card` - Individual card
- `.cc-overview-grid` - Overview grid
- `.cc-pros-cons` - Pros/cons section
- `.cc-data-table` - Data tables

### Hooks and Filters

```php
// Modify card data before display
add_filter('ccm_card_data', 'your_custom_function');

// Customize template paths
add_filter('ccm_template_path', 'your_template_function');

// Modify API response
add_filter('ccm_api_response', 'your_api_function');

// Custom card meta
add_action('ccm_after_card_meta', 'your_meta_function');
```

### Template Functions

```php
// Get card meta data
ccm_get_meta($post_id, $key, $default, $is_numeric);

// Format currency
ccm_format_currency($amount);

// Get card rating
ccm_get_rating($post_id);

// Get card features
ccm_get_features($post_id);

// Check if card is featured
ccm_is_featured($post_id);
```

## 🔍 SEO Features

### Built-in SEO
- **Meta tags** - Title, description, keywords
- **Open Graph** - Social media optimization
- **Twitter Cards** - Twitter-specific metadata
- **Schema Markup** - Rich snippets for search engines
- **Canonical URLs** - Prevent duplicate content
- **Breadcrumbs** - Navigation structure

### Schema Types
- `FinancialProduct` - Credit card products
- `Review` - Card reviews and ratings
- `BreadcrumbList` - Navigation breadcrumbs
- `Organization` - Publisher information

### SEO Functions
```php
// Add meta tags
ccm_add_meta_tags($title, $description, $canonical, $keywords);

// Add Open Graph tags
ccm_add_og_tags($title, $description, $url, $image, $type);

// Add schema markup
ccm_add_schema_markup($type, $data);
```

## 💻 Developer Guide

### Plugin Architecture

```
Plugin Structure:
├── plugin.php              // Main plugin file
├── includes/
│   ├── config.php          // Configuration
│   ├── api.php             // REST API
│   ├── shortcodes.php      // Shortcode handlers
│   ├── helper-functions.php // Utility functions
│   └── seo-functions.php   // SEO utilities
├── templates/              // Template files
├── assets/                 // CSS/JS assets
└── README.md              // This file
```

### Custom Post Type Registration
```php
register_post_type('credit-card', [
    'public' => true,
    'has_archive' => true,
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
    'taxonomies' => ['store', 'card-category', 'network-type'],
    'show_in_rest' => true,
]);
```

### Adding Custom Meta Fields
```php
add_action('init', function() {
    register_meta('post', 'custom_field', [
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
});
```

### Custom Template Loading
```php
add_filter('template_include', function($template) {
    if (is_singular('credit-card')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-credit-card.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
});
```

## 📋 Usage Examples

### Basic Implementation
```php
// Display featured cards
echo do_shortcode('[credit_card_grid featured="1" count="4"]');

// Show specific bank cards
echo do_shortcode('[credit_card_grid bank="hdfc" category="cashback"]');

// Card comparison
echo do_shortcode('[compare-card ids="123,456"]');
```

### Advanced Filtering
```php
// AJAX card loading
$cards = get_posts([
    'post_type' => 'credit-card',
    'meta_query' => [
        [
            'key' => 'annual_fee',
            'value' => 1000,
            'compare' => '<='
        ]
    ],
    'tax_query' => [
        [
            'taxonomy' => 'card-category',
            'field' => 'slug',
            'terms' => 'cashback'
        ]
    ]
]);
```

### REST API Usage
```javascript
// Fetch cards with JavaScript
fetch('/wp-json/ccm/v1/credit-cards?category=travel&min_rating=4')
    .then(response => response.json())
    .then(data => console.log(data));

// Get filter options
fetch('/wp-json/ccm/v1/credit-cards/filters')
    .then(response => response.json())
    .then(filters => {
        // Populate filter dropdowns
        console.log(filters.banks);
        console.log(filters.categories);
    });
```

## 🛠️ Configuration

### Plugin Constants
```php
CCM_VERSION              // Plugin version
CCM_POST_TYPE           // 'credit-card'
CCM_API_NAMESPACE       // 'ccm/v1'
CCM_MAX_COMPARE_CARDS   // 4
CCM_CARDS_PER_PAGE      // 12
CCM_ENABLE_CACHE        // true
```

### Cache Settings
```php
define('CCM_CACHE_EXPIRY', 12 * HOUR_IN_SECONDS); // 12 hours
define('CCM_ENABLE_CACHE', true);
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 📞 Support

For support and documentation, please visit the plugin documentation or create an issue in the repository.

---

**Version:** 1.0.0  
**Requires:** WordPress 5.0+  
**Tested up to:** WordPress 6.4  
**PHP Version:** 7.4+