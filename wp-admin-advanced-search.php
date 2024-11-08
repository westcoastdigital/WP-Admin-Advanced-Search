<?php
/*
Plugin Name:  WP Admin Advanced Search
Plugin URI:   https://jonmather.au
Description:  Add support to easily search for any media, product, post or page in the backend
Version:      0.1.0
Author:       Jon Mather
Author URI:   https://jonmather.au
Text Domain:  translate
Domain Path:  languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

//define plugin version
define('JM_WP_ADMIN_SEARCH_VERSION', '0.1.0');
//plugin file
define('JM_WP_ADMIN_SEARCH_AUTH_FILE', __FILE__);
//plugin folder path
define('JM_WP_ADMIN_SEARCH_AUTH_PATH', plugin_dir_path(__FILE__));
//plugin folder url
define('JM_WP_ADMIN_SEARCH_AUTH_URL', plugin_dir_url(__FILE__));

// Include files
require_once JM_WP_ADMIN_SEARCH_AUTH_PATH . 'inc/setup.php';
require_once JM_WP_ADMIN_SEARCH_AUTH_PATH . 'inc/settings.php';
require_once JM_WP_ADMIN_SEARCH_AUTH_PATH . 'inc/results.php';

/*
* Check if ACF is activated
*/
if(!function_exists('is_acf_activated')) {
    function is_acf_activated() {
        if ( class_exists( 'ACF' ) ) { return true; } else { return false; }
    }
}

/*
* Check if WooCommerce is activated
*/
if ( ! function_exists( 'is_woocommerce_activated' ) ) {
	function is_woocommerce_activated() {
		if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
	}
}

// Define default excluded post types
$excluded_post_types = [
    'acf-field',
    'acf-field-group',
    'oembed_cache',
    'search-filter-widget',
    'wp_global_styles'
];
define('JM_WP_ADMIN_SEARCH_EXCL_POST_TYPES', $excluded_post_types);