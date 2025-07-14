<?php
/**
 * Utility Functions
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Sanitize rating value (0-5)
 *
 * @param float $value The rating value to sanitize
 * @return float Sanitized rating between 0 and 5
 */
function ccm_sanitize_rating($value) {
    $value = floatval($value);
    return max(0, min(5, $value));
}

/**
 * Sanitize percentage value (0-100)
 *
 * @param float $value The percentage value to sanitize
 * @return float Sanitized percentage between 0 and 100
 */
function ccm_sanitize_percentage($value) {
    $value = floatval($value);
    return max(0, min(100, $value));
}

/**
 * Sanitize boolean value
 *
 * @param mixed $value The value to sanitize as boolean
 * @return int 1 for true, 0 for false
 */
function ccm_sanitize_boolean($value) {
    return $value ? 1 : 0;
}

/**
 * Sanitize array field
 *
 * @param array $value The array to sanitize
 * @return array Sanitized array
 */
function ccm_sanitize_array_field($value) {
    if (!is_array($value)) {
        return array();
    }
    return array_map('sanitize_text_field', $value);
}

/**
 * Sanitize complex array with nested objects
 *
 * @param array $value The complex array to sanitize
 * @return array Sanitized complex array
 */
function ccm_sanitize_complex_array($value) {
    if (!is_array($value)) {
        return array();
    }
    
    $sanitized = array();
    foreach ($value as $item) {
        if (is_array($item)) {
            $sanitized_item = array();
            foreach ($item as $key => $val) {
                $sanitized_item[sanitize_key($key)] = sanitize_text_field($val);
            }
            $sanitized[] = $sanitized_item;
        } else {
            $sanitized[] = sanitize_text_field($item);
        }
    }
    return $sanitized;
}

/**
 * Extract numeric value from string
 * 
 * @param string $string The string to extract numeric value from
 * @return int Extracted numeric value
 */
function ccm_extract_numeric_value($string) {
    // Remove currency symbols and commas
    $cleaned = preg_replace('/[^\d.]/', '', $string);
    return intval($cleaned);
}

/**
 * Load a template file from the plugin
 *
 * @param string $template_name Template name
 * @param array $args Arguments to pass to the template
 * @param string $template_path Path to the template
 * @param string $default_path Default path
 * @return string
 */
function ccm_get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
    if (!empty($args) && is_array($args)) {
        extract($args);
    }
    
    $located = ccm_locate_template($template_name, $template_path, $default_path);
    
    if (!file_exists($located)) {
        return '';
    }
    
    ob_start();
    include($located);
    return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion
 *
 * @param string $template_name Template name
 * @param string $template_path Path to the template
 * @param string $default_path Default path
 * @return string
 */
function ccm_locate_template($template_name, $template_path = '', $default_path = '') {
    if (!$template_path) {
        $template_path = 'credit-card-manager/';
    }
    
    if (!$default_path) {
        $default_path = CCM_PLUGIN_DIR . 'templates/';
    }
    
    // Look within passed path within the theme - this is priority
    $template = locate_template(array(
        trailingslashit($template_path) . $template_name,
        $template_name,
    ));
    
    // Get default template
    if (!$template) {
        $template = $default_path . $template_name;
    }
    
    return $template;
}
