<?php
// Settings Page: Search Results
class SIMPLI_WP_ADMIN_SEARCH_RESULTS {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_settings' ) );
	}

	public function create_settings() {
        $parent_slug = null; // Set to not show in admin menu so link will be site_url/wp-admin/admin.php?page=admin_search_results&s=news
		$page_title = 'Search Results';
		$menu_title = 'Search Results';
		$capability = 'manage_options';
		$slug = 'admin_search_results';
		$callback = [$this, 'settings_content'];
		add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $slug, $callback);
	}

	public function settings_content() { ?>
		<div class="wrap">
			<h1>Search Results</h1>

            <?php
            $search_query = isset($_GET['s']) ? $_GET['s'] : '';
            $current_post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
            ?>



            <div class="tablenav top">
                <div class="alignleft search">
                    <input type="text" id="results-page-search-input"
                           value="<?php echo esc_attr($search_query); ?>"
                           placeholder="Search..." autocomplete="off">
                </div>
                <div class="alignleft" id="post-type-filter-wrapper" style="display: none; margin-left: 10px;">
                    <label for="cpt">Choose a Post Type:</label>
                    <select name="cpt" id="post-type-filter">
                        <option value="">All Post Types</option>
                        <?php if ($current_post_type): ?>
                            <option value="<?php echo esc_attr($current_post_type); ?>" selected>
                                <?php echo esc_html(get_post_type_object($current_post_type)->label); ?>
                            </option>
                        <?php endif; ?>
                    </select>
                </div>

            </div>

            <table class="wp-list-table widefat fixed striped table-view-list posts" id="admin-search-results-table">
                <thead>
                    <tr>
                        <th id="sortable-id" class="sortable-column">ID<span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span></th>
                        <th>Title</th>
                        <th>Post Type</th>
                        <th>Post Status</th>
                        <th id="sortable-date" class="sortable-column">Date<span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

            <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                // // Sortable columns (ID and Date)
                // const table = document.querySelector('#admin-search-results-table');
                // const rows = Array.from(table.querySelectorAll('.search-result-row'));

                // // Sort function for numerical and date columns
                // function sortTable(index, isDate) {
                //     console.log(rows);
                //     rows.sort((rowA, rowB) => {
                //         const cellA = rowA.cells[index].textContent.trim();
                //         const cellB = rowB.cells[index].textContent.trim();

                //         // If it's a date column, convert it to a Date object for comparison
                //         if (isDate) {
                //             return new Date(cellA) - new Date(cellB);
                //         }
                //         return parseInt(cellA, 10) - parseInt(cellB, 10);
                //     });

                //     // Reorder rows
                //     rows.forEach(row => table.querySelector('tbody').appendChild(row));
                // }

                // // Add click event listeners for sorting
                // document.querySelector('#sortable-id').addEventListener('click', function () {
                //     sortTable(0, false);  // 0 is for the "ID" column
                // });

                // document.querySelector('#sortable-date').addEventListener('click', function () {
                //     sortTable(4, true);  // 4 is for the "Date" column
                // });
            });
        </script>
		</div> <?php
	}

}
new SIMPLI_WP_ADMIN_SEARCH_RESULTS();