<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('JM_WP_ADMIN_SEARCH_SETTINGS')) {
    class JM_WP_ADMIN_SEARCH_SETTINGS {

        public function __construct() {
            // Register the settings page and the settings fields
            add_action('admin_menu', [$this, 'register_search_settings_page']);
            add_action('admin_init', [$this, 'admin_search_register_settings']);
             // Add filter for plugin action links
             add_filter('plugin_action_links_' . plugin_basename(JM_WP_ADMIN_SEARCH_AUTH_FILE), [$this, 'add_settings_link']);
            // Register activation hook to set default values
            register_activation_hook(__FILE__, [$this, 'set_default_excluded_post_types']);
        }

        // Settings link
        public function add_settings_link($links) {
            $settings_link = '<a href="' . admin_url('options-general.php?page=admin_search_settings') . '">' . __('Settings', 'your-text-domain') . '</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        // Set default excluded post types on plugin activation
        public function set_default_excluded_post_types() {
            // Check if the option already exists
            if (!get_option('excluded_post_types')) {
                // Set the default excluded post types if the option doesn't exist
                update_option('excluded_post_types', JM_WP_ADMIN_SEARCH_EXCL_POST_TYPES);
            }
        }

        // Register the settings page in the Settings menu
        public function register_search_settings_page() {
            add_options_page(
                'Admin Search Settings',          // Page title
                'Admin Search Settings',          // Menu title
                'manage_options',                 // Capability required to access this page
                'admin_search_settings',            // Menu slug
                [$this, 'admin_search_settings_page'] // Callback function to render the settings page
            );
        }

        // Render the settings page HTML with tabs
        public function admin_search_settings_page() {
            ?>
            <div class="wrap">
                <h1>Admin Search Settings</h1>

                <!-- Tab Navigation -->
                <h2 class="nav-tab-wrapper">
                    <?php if (is_acf_activated()) : ?>
                        <a href="#acf-settings" class="nav-tab nav-tab-active" id="acf-settings-tab">ACF Settings</a>
                    <?php endif; ?>
                    <a href="#post-types-settings" class="nav-tab" id="post-types-tab">Post Types</a>
                    <a href="#design-settings" class="nav-tab" id="design-settings-tab">Design Settings</a>
                </h2>

                <form method="post" action="options.php">
                    <?php settings_fields('acf_search_options_group'); ?>

                    <!-- ACF Settings Tab Content -->
                    <?php if (is_acf_activated()) : ?>
                        <div id="acf-settings" class="tab-content">
                            <h3>Searchable ACF Fields</h3>
                            <?php do_settings_sections('admin_search_settings'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Post Types Settings Tab Content -->
                    <div id="post-types-settings" class="tab-content" style="display:none;">
                        <h3>Excluded Post Types</h3>
                        <h2>Select post types to exclude from search</h2>
                        <fieldset class="field-group">
                            <legend>Post Types</legend>
                            <?php $this->render_post_types_settings(); ?>
                        </fieldset>
                    </div>

                    <div id="design-settings" class="tab-content" style="display:none;">
                        <h3>Design Settings</h3>
                        <fieldset class="field-group">
                            <legend>Search Location</legend>
                            <?php $this->render_search_location_settings(); ?>
                        </fieldset>
                        <fieldset class="field-group">
                            <legend>Post Qty in Dropdown</legend>
                            <?php $this->render_search_qty_settings(); ?>
                        </fieldset>
                        <fieldset class="field-group">
                            <legend>Sort Order</legend>
                            <?php $this->render_search_order_settings(); ?>
                        </fieldset>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
        }

        // Get all post types directly from the wp_posts table
        public function get_all_post_types() {
            global $wpdb;

            // Query the wp_posts table to get distinct post types
            $query = "
                SELECT DISTINCT post_type
                FROM {$wpdb->posts}
                WHERE post_type != 'revision' AND post_type != 'nav_menu_item'
            ";

            // Execute the query
            $post_types = $wpdb->get_col($query);

            return $post_types;
        }

        // Render the search location settings
        public function render_search_location_settings() {
            $search_location = get_option('admin_search_location', []);

            $fields = [
                [
                    'value' => 'admin_menu',
                    'label' => 'Admin Menu'
                ],
                [
                    'value' => 'admin_bar',
                    'label' => 'Admin Bar'
                ],
            ];

            echo '<ul>';
            foreach($fields as $field) {
                echo '<li>';
                // Check if the field is selected
                if(empty($search_location) && $field['value'] == 'admin_menu') {
                    $checked = 'checked';
                } else {
                    $checked = in_array($field['value'], $search_location) ? 'checked' : '';
                }
                echo '<label>';
                echo '<input type="radio" name="admin_search_location[]" value="' . $field['value'] . '" ' . $checked . ' /> ';
                echo  $field['label'];
                echo '</label>';
                echo '</li>';
                }
            echo '</ul>';
        }
        // Render the search qty settings
        public function render_search_qty_settings() {
            $search_location = get_option('admin_search_qty', []);

            $fields = [
                [
                    'value' => 3,
                    'label' => 3
                ],
                [
                    'value' => 5,
                    'label' => 5
                ],
                [
                    'value' => 10,
                    'label' => 10
                ],
                [
                    'value' => 15,
                    'label' => 15
                ],
                [
                    'value' => 20,
                    'label' => 26
                ],
                [
                    'value' => 'all',
                    'label' => 'All'
                ],
            ];

            echo '<ul>';
            foreach($fields as $field) {
                echo '<li>';
                // Check if the field is selected
                if(empty($search_location) && $field['value'] == 3) {
                    $checked = 'checked';
                } else {
                    $checked = in_array($field['value'], $search_location) ? 'checked' : '';
                }
                $affix = $field['value'] == 'all' ? ' posts' : ' per post type';
                echo '<label>';
                echo '<input type="radio" name="admin_search_qty[]" value="' . $field['value'] . '" ' . $checked . ' /> ';
                echo  $field['label'] . $affix;
                echo '</label>';
                echo '</li>';
                }
            echo '<ul>';
        }

        public function render_search_order_settings() {
            $sort_order = get_option('admin_search_order', []);
            if(!$sort_order) {
                $direction = 'DESC';
                $order_by = 'post_date';
            } else {
                $direction = $sort_order['direction'];
                $order_by = $sort_order['order_by'];
            }
            ?>
           <ul>
                <li>
                    <label for="sort-order-value">Sort By</label>
                    <select name="admin_search_order[order_by]" id="search-order-value">
                        <option value="post_date" <?php selected($order_by, 'post_date'); ?>>Date</option>
                        <option value="post_title" <?php selected($order_by, 'post_title'); ?>>Title</option>
                        <option value="id" <?php selected($order_by, 'id'); ?>>ID</option>
                    </select>
                    <label for="search-order">Sort Order</label>
                    <select name="admin_search_order[direction]" id="search-order">
                        <option value="DESC" <?php selected($direction, 'DESC'); ?>>Descending</option>
                        <option value="ASC" <?php selected($direction, 'ASC'); ?>>Ascending</option>
                    </select>
                </li>
            <ul>
            <?php
        }

        // Render the post type settings
        public function render_post_types_settings() {
            $excluded_post_types = get_option('excluded_post_types', []);
            $post_types = get_post_types([], 'objects');
            $post_types = $this->get_all_post_types();

            if ($post_types) {
                echo '<ul>';
                foreach ($post_types as $post_type) {
                    $post_object = get_post_type_object($post_type);
                    if($post_object) {
                        // Generate a checkbox for each post type
                        $checked = in_array($post_object->name, $excluded_post_types) ? 'checked' : '';
                        echo '<li>';
                        echo '<label>';
                        echo '<input type="checkbox" name="excluded_post_types[]" value="' . esc_attr($post_object->name) . '" ' . $checked . ' /> ';
                        echo esc_html($post_object->label);
                        echo '</label>';
                        echo '</li>';
                    }
                }
                echo '</ul>';
            } else {
                echo '<p>No post types found.</p>';
            }
        }

        // Register the settings and fields
        public function admin_search_register_settings() {
            register_setting(
                'acf_search_options_group',        // Settings group
                'acf_search_fields',               // Option name where selected fields are saved
                [$this, 'acf_search_fields_sanitize'] // Sanitization callback
            );

            register_setting(
                'acf_search_options_group',        // Settings group
                'excluded_post_types',             // Option name for excluded post types
                [$this, 'acf_search_post_types_sanitize'] // Sanitization callback
            );

            register_setting(
                'acf_search_options_group',
                'admin_search_qty',
                [
                    'type' => 'array',
                    'sanitize_callback' => [$this, 'admin_search_qty_sanitize'],
                    'default' => [3]
                ]
            );

            register_setting(
                'acf_search_options_group',
                'admin_search_order',
                [
                    'type' => 'array',
                    'sanitize_callback' => [$this, 'admin_search_order_sanitize'],
                    'default' => [
                        'direction' => 'desc',
                        'order_by' => 'post_date',
                    ],
                ]
            );

            add_settings_section(
                'acf_search_section',              // Section ID
                'Select ACF Fields to Search',     // Section title
                [$this, 'acf_search_section_callback'], // Section description callback
                'admin_search_settings'              // Settings page to associate with
            );

            add_settings_field(
                'acf_fields',                      // Field ID
                'Searchable ACF Fields',           // Field title
                [$this, 'acf_search_fields_callback'], // Callback to render the fields
                'admin_search_settings',             // Settings page to associate with
                'acf_search_section'               // Section to place this field in
            );
        }

        // Callback for the settings section description
        public function acf_search_section_callback() {
            // Optionally add a description here
            // echo '<p>Select the ACF fields you want to search.</p>';
        }

        // Callback to render the checkboxes for ACF fields grouped by field groups
        public function acf_search_fields_callback() {
            // Get the selected ACF fields from the options table (if any)
            $selected_fields = get_option('acf_search_fields', []);

            // Get all ACF field groups
            $field_groups = acf_get_field_groups(); // Get all field groups

            if ($field_groups) {
                foreach ($field_groups as $group) {
                    // Display the field group name as a heading
                    echo '<fieldset class="field-group">';
                    echo '<legend>' . esc_html($group['title']) . '</legend>'; // Group name

                    // Get all fields within the group
                    $fields = acf_get_fields($group['ID']); // Get fields for each group

                    if ($fields) {
                        echo '<ul>';
                        foreach ($fields as $field) {
                            // Check if the field is selected
                            $checked = in_array($field['name'], $selected_fields) ? 'checked' : '';

                            // Display each field as a checkbox
                            echo '<li>';
                            echo '<label>';
                            echo '<input type="checkbox" name="acf_search_fields[]" value="' . esc_attr($field['name']) . '" ' . $checked . ' /> ';
                            echo esc_html($field['label']);
                            echo '</label>';
                            echo '</li>';
                        }
                        echo '</ul>';  // End of fields list
                    }
                    echo '</fieldset>';  // End of field group
                }
            } else {
                echo '<p>No ACF field groups found. Please create ACF field groups first.</p>';
            }
        }

        // Sanitization callback to sanitize the selected fields
        public function acf_search_fields_sanitize($input) {
            // Ensure it's an array of strings (ACF field keys)
            if (is_array($input)) {
                return array_map('sanitize_text_field', $input);
            }
            return [];
        }

        // Sanitization callback for excluded post types
        public function acf_search_post_types_sanitize($input) {
            // Ensure it's an array of strings (Post type names)
            if (is_array($input)) {
                return array_map('sanitize_text_field', $input);
            }
            return [];
        }

        public function admin_search_location_sanitize($input) {
            // Ensure it's always an array
            if (!is_array($input)) {
                $input = [$input];
            }

            // Valid options
            $valid_options = ['admin_menu', 'admin_bar'];

            // Filter out any invalid values
            return array_filter($input, function($value) use ($valid_options) {
                return in_array($value, $valid_options);
            });
        }
        // Sanitization callback for search quantity
        public function admin_search_qty_sanitize($input) {
            // Ensure it's always an array
            if (!is_array($input)) {
                $input = [$input];
            }

            // Valid options (including 'all' as a string)
            $valid_options = [3, 5, 10, 15, 20, 'all'];

            // Filter out any invalid values
            return array_filter($input, function($value) use ($valid_options) {
                // Convert numeric strings to integers for comparison
                if (is_numeric($value)) {
                    $value = (int)$value;
                }
                return in_array($value, $valid_options);
            });
        }
        // Sanitization callback for search order
        public function admin_search_order_sanitize($input) {
            // Valid values for the direction and order_by
            $valid_directions = ['ASC', 'DESC'];
            $valid_order_bys = ['post_date', 'post_title', 'id'];

            // Sanitize direction and order_by
            $input['direction'] = in_array($input['direction'], $valid_directions) ? $input['direction'] : 'DESC';
            $input['order_by'] = in_array($input['order_by'], $valid_order_bys) ? $input['order_by'] : 'post_date';

            return $input;
        }

    }

    // Instantiate the class
    new JM_WP_ADMIN_SEARCH_SETTINGS;
}
?>
