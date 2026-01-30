<?php
/*
Plugin Name:  WP Admin Advanced Search
Plugin URI:   https://github.com/westcoastdigital/WP-Admin-Advanced-Search
Description:  Add support to easily search for any media, product, post or page in the backend
Version:      1.0.0
Author:       Jon Mather
Author URI:   https://jonmather.au
Text Domain:  simpliweb
Domain Path:  /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

//define plugin version
define('SIMPLI_WP_ADMIN_SEARCH_VERSION', '1.0.0');
//plugin file
define('SIMPLI_WP_ADMIN_SEARCH_AUTH_FILE', __FILE__);
//plugin folder path
define('SIMPLI_WP_ADMIN_SEARCH_AUTH_PATH', plugin_dir_path(__FILE__));
//plugin folder url
define('SIMPLI_WP_ADMIN_SEARCH_AUTH_URL', plugin_dir_url(__FILE__));

// Include the updater class
require_once plugin_dir_path(__FILE__) . 'github-updater.php';

// For private repos, uncomment and add your token:
// define('SW_GITHUB_ACCESS_TOKEN', 'your_token_here');

if (class_exists('SimpliWeb_GitHub_Updater')) {
    $updater = new SimpliWeb_GitHub_Updater(__FILE__);
    $updater->set_username('westcoastdigital'); // Update Username
    $updater->set_repository('SWP-Admin-Advanced-Search'); // Update plugin slug
    
    if (defined('GITHUB_ACCESS_TOKEN')) {
      $updater->authorize(SW_GITHUB_ACCESS_TOKEN);
    }
    
    $updater->initialize();
}

// Include files
require_once SIMPLI_WP_ADMIN_SEARCH_AUTH_PATH . 'inc/setup.php';
require_once SIMPLI_WP_ADMIN_SEARCH_AUTH_PATH . 'inc/settings.php';
require_once SIMPLI_WP_ADMIN_SEARCH_AUTH_PATH . 'inc/results.php';

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

// Allow extension via filter
$excluded_post_types = apply_filters(
    'simpli_wp_admin_search_excluded_post_types',
    $excluded_post_types
);

define('SIMPLI_WP_ADMIN_SEARCH_EXCL_POST_TYPES', $excluded_post_types);