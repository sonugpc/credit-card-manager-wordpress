<?php
/**
 * Plugin Name: Credit Card Manager
 * Plugin URI: https://bigtricks.com
 * Description: A comprehensive plugin to manage credit cards with advanced filtering and API support
 * Version: 1.0.0
 * Author: Sonu
 * License: GPL v2 or later
 *
 * Text Domain: credit-card-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include Configuration
require_once plugin_dir_path(__FILE__) . 'includes/config.php';

// Include Helper Functions
require_once CCM_PLUGIN_DIR . 'includes/helper-functions.php';

// Include Core Classes
require_once CCM_PLUGIN_DIR . 'includes/class-plugin-core.php';

// Initialize the plugin
new CreditCardManager_Core();
