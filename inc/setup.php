<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('JM_WP_ADMIN_SEARCH_SETUP')) {
    class JM_WP_ADMIN_SEARCH_SETUP
    {
        public function __construct()
        {
            // Add custom search box to the WordPress admin bar
            $search_location = get_option('admin_search_location', []);
            if(empty($search_location) || in_array('admin_menu', $search_location)) {
                add_action('admin_menu', [$this, 'search_field']);
            } else {
                add_action('admin_bar_menu', [$this, 'add_admin_bar_search'], 11);
            }
            add_action('wp_ajax_search_posts', [$this, 'ajax_search_posts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        }

        // Method to add the search form
        public function search_field()
        {
            global $menu;
            $position = 1;

            // Create form HTML with search input
            $form_html = sprintf(
                '<div class="wp-menu-name">
                    <form id="menu-search-form" class="menu-form-container">
                        <input type="text"
                               id="menu-search-input"
                               placeholder="Search..."
                               autocomplete="off">
                               <img class="loading" src="' . JM_WP_ADMIN_SEARCH_AUTH_URL . '/assets/image/loading.gif" />
                        <div id="search-results" class="search-results"></div>
                    </form>
                </div>'
            );

            // Insert the form into menu array
            $menu[$position] = array(
                0 => $form_html,
                1 => 'manage_options',
                2 => '#',
                3 => '',
                4 => 'menu-form menu-top no-link',
                5 => 'menu-form',
                6 => '',
            );

            // Add AJAX handler
            add_action('wp_ajax_search_posts', [$this, 'ajax_search_posts']);
        }

        public function add_admin_bar_search($wp_admin_bar) {
            // Create custom node for search
            $args = array(
                'id'    => 'admin-search',
                'title' => sprintf(
                    '<form id="admin-bar-search-form" class="admin-bar-search-container">
                        <input type="text"
                               id="admin-bar-search-input"
                               placeholder="Search..."
                               autocomplete="off">
                               <img class="loading" src="' . JM_WP_ADMIN_SEARCH_AUTH_URL . '/assets/image/loading.gif" />
                        <div id="admin-bar-search-results" class="search-results"></div>
                    </form>'
                ),
                'href'  => '#',
                'meta'  => array(
                    'class' => 'admin-bar-search'
                )
            );

            // Add the search node after the WordPress logo
            $wp_admin_bar->add_node($args);
            add_action('wp_ajax_search_posts', [$this, 'ajax_search_posts']);
        }


        public function ajax_search_posts() {
            // Verify nonce for security
            check_ajax_referer('search_posts_nonce', 'nonce');

            global $wpdb;
            // Sanitize the search term to prevent XSS
            $search_term = sanitize_text_field($_POST['search_term']);

            // Get the selected ACF fields from the options table if acf is active
            if(is_acf_activated()) {
                $acf_fields_to_search = get_option('acf_search_fields', []);
            } else {
                $acf_fields_to_search = [];
            }

            // Post types to exclude
            $exclude_post_types = get_option('excluded_post_types', []);

            // Start building the query
            $base_query = "SELECT DISTINCT p.ID, p.post_title, p.post_name, p.post_type, p.post_status, p.post_date
                FROM {$wpdb->posts} p";

            // Only add the JOIN if we have ACF fields to search or WooCommerce is active
            if (!empty($acf_fields_to_search) || is_woocommerce_activated()) {
                $base_query .= " LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id";
            }


            // Start the WHERE clause
            $where_clauses = [];
            $where_clauses[] = $wpdb->prepare("(p.post_title LIKE %s", '%' . $wpdb->esc_like($search_term) . '%');
            $where_clauses[] = $wpdb->prepare("OR p.post_name LIKE %s)", '%' . $wpdb->esc_like($search_term) . '%');

            // Add WooCommerce SKU search if active
            if (is_woocommerce_activated()) {
                $where_clauses[] = $wpdb->prepare(
                    "OR (pm.meta_key = '_sku' AND pm.meta_value LIKE %s)",
                    '%' . $wpdb->esc_like($search_term) . '%'
                );
            }

            // Add meta query conditions if we have ACF fields
            if (!empty($acf_fields_to_search)) {
                $meta_conditions = [];
                foreach ($acf_fields_to_search as $field) {
                    $meta_conditions[] = $wpdb->prepare(
                        "(pm.meta_key = %s AND pm.meta_value LIKE %s)",
                        $field,
                        '%' . $wpdb->esc_like($search_term) . '%'
                    );
                }
                if (!empty($meta_conditions)) {
                    $where_clauses[] = "OR (" . implode(" OR ", $meta_conditions) . ")";
                }
            }

            // Combine all conditions
            $where_clause = "WHERE (" . implode(" ", $where_clauses) . ")";

            // Add post status condition
            $where_clause .= " AND p.post_status IN ('publish', 'draft', 'private', 'inherit')";

            // Exlude post types
            $where_clause .= " AND p.post_type NOT IN ('" . implode("', '", $exclude_post_types) . "')";

            // Order by post date descending
            $sort_order = get_option('admin_search_order', []);
            if(!$sort_order) {
                $direction = 'DESC';
                $order_by = 'p,post_date';
            } else {
                $direction = $sort_order['direction'];
                $order_by = 'p.' . $sort_order['order_by'];
            }
            $order_by_clause = "ORDER BY " . $order_by . " " . $direction;

            // Complete the query
            $search = $base_query . " " . $where_clause . " " . $order_by_clause;

            // Get the search results
            $results = $wpdb->get_results($search);

            // Group the results by post type
            $grouped_results = [];

            // Get the desired date format from WordPress settings
            $date_format = get_option('date_format');
            foreach ($results as $post) {
                // Get post type object
                $post_type = get_post_type_object($post->post_type);
                $label = $post_type->label;

                // Format the post date
                $post_date = $post->post_date;
                $timestamp = strtotime($post_date);
                $formatted_date = date($date_format, $timestamp);

                // Get matching meta values for context
                $matching_meta = [];

                // Check for matching SKU if WooCommerce is active
                if (is_woocommerce_activated()) {
                    $sku = get_post_meta($post->ID, '_sku', true);
                    if ($sku && stripos($sku, $search_term) !== false) {
                        $matching_meta['sku'] = esc_html($sku);
                    }
                }

                 // Check ACF fields
                if (!empty($acf_fields_to_search)) {
                    foreach ($acf_fields_to_search as $field) {
                        $meta_value = get_post_meta($post->ID, $field, true);
                        if ($meta_value && stripos($meta_value, $search_term) !== false) {
                            $matching_meta[$field] = esc_html($meta_value);
                        }
                    }
                }

                // Format the post data
                $formatted_post = [
                    'id' => $post->ID,
                    'post_type' => $label,
                    'post_type_slug' => $post->post_type,
                    'title' => esc_html($post->post_title),
                    'slug' => esc_html($post->post_name),
                    'edit_url' => esc_url(get_edit_post_link($post->ID, 'raw')),
                    'status' => $post->post_status,
                    'date' => $formatted_date,
                    'matching_fields' => $matching_meta  // Include which ACF fields matched
                ];

                // Group posts by their post type
                if (!isset($grouped_results[$label])) {
                    $grouped_results[$label] = [];
                }
                $grouped_results[$label][] = $formatted_post;
            }

            // Return the results grouped by post type
            wp_send_json_success($grouped_results);
        }

        public function enqueue_assets()
        {
            wp_enqueue_style(
                'jm-admin-search',
                JM_WP_ADMIN_SEARCH_AUTH_URL . 'assets/css/search.css',
                [],
                filemtime(JM_WP_ADMIN_SEARCH_AUTH_PATH . 'assets/css/search.css')
            );
            wp_enqueue_style(
                'jm-admin-search-setup',
                JM_WP_ADMIN_SEARCH_AUTH_URL . 'assets/css/settings.css',
                [],
                filemtime(JM_WP_ADMIN_SEARCH_AUTH_PATH . 'assets/css/settings.css')
            );

            wp_enqueue_script(
                'jm-admin-search',
                JM_WP_ADMIN_SEARCH_AUTH_URL . 'assets/js/search.js',
                ['jquery'],
                filemtime(JM_WP_ADMIN_SEARCH_AUTH_PATH . 'assets/js/search.js'),
                true
            );

            $search_qty = get_option('admin_search_qty', []);
            if(!$search_qty) {
                $qty = 'all';
            } else {
                $qty = $search_qty[0];
            }
            // Localize the script with new data
            wp_localize_script('jm-admin-search', 'jmAdminSearch', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'searchqty' => $qty,
                'nonce'   => wp_create_nonce('search_posts_nonce'),
            ]);
        }

    }

    // Instantiate the class
    new JM_WP_ADMIN_SEARCH_SETUP;
}
