jQuery(document).ready(function ($) {
    let searchTimeout;
    let sortDirectionId = 'asc';  // Track ID column sort direction (asc or desc)
    let sortDirectionDate = 'asc';  // Track Date column sort direction (asc or desc)
    let postQty = jmAdminSearch.searchqty;
    console.log(postQty);

    // Get the results container (tbody)
    var resultsTbody = $('#admin-search-results-table tbody');
    if (resultsTbody) {
        resultsTbody.empty();
    }

    // Initialize search functionality for both locations
    function initSearch(inputSelector, resultsSelector, formSelector) {
        const searchInput = $(inputSelector);
        const resultsContainer = $(resultsSelector);
        const searchForm = $(formSelector);
        if (!searchInput.length) return;

        // Prevent form submission and link clicking
        searchForm.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

        searchInput.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

        searchInput.on('input', function () {
            const searchTerm = $(this).val();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            // Hide results if search term is empty
            if (searchTerm.length < 2) {
                resultsContainer.hide().empty();
                return;
            }

            // Set new timeout for search
            searchTimeout = setTimeout(function () {
                $(formSelector).addClass('active');
                $.ajax({
                    url: jmAdminSearch.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'search_posts',
                        search_term: searchTerm,
                        nonce: jmAdminSearch.nonce
                    },
                    success: function (response) {
                        if (response.success && Object.keys(response.data).length > 0) {
                            let html = '';

                            let showMore = false;

                            // Iterate through the grouped post types
                            $.each(response.data, function (postType, posts) {
                                html += `<div class="search-result-group">
                                            <h3>${postType}</h3>
                                            <div class="search-result-items">`;

                                // Iterate through the posts for this post type and Show only first 3 posts

                                if (postQty != 'all') {
                                    posts.slice(0, postQty).forEach(function (post) {
                                        const statusClass = 'status-' + post.status;
                                        let statusString = '';
                                        html += `
                                        <div class="search-result-item" onclick="window.location.href='${post.edit_url}'">
                                            ${post.title}
                                            ${statusString}
                                        </div>
                                    `;
                                    });
                                } else {
                                    posts.forEach(function (post) {
                                        const statusClass = 'status-' + post.status;
                                        let statusString = '';
                                        html += `
                                        <div class="search-result-item" onclick="window.location.href='${post.edit_url}'">
                                            ${post.title}
                                            ${statusString}
                                        </div>
                                    `;
                                    });
                                }

                                // Add "View All" link if there are more than 3 posts
                                if (posts.length > 3 && showMore == false) {
                                    showMore = true;
                                }

                                html += `</div>`;
                                html += `</div>`; // Close the group and items div
                            });


                            if (showMore == true) {
                                // You can customize this URL to point to your desired "view all" page
                                const viewAllUrl = `/wp-admin/admin.php?page=admin_search_results&s=${searchTerm}`;
                                html += `
                                    <div class="search-result-item view-all" onclick="window.location.href='${viewAllUrl}'">
                                        View all results
                                    </div>
                                `;
                            }

                            resultsContainer.html(html).show();
                        } else {
                            resultsContainer.html('<div class="search-result-item">No results found</div>').show();
                        }

                        $(formSelector).removeClass('active');
                    }
                });
            }, 300);
        });

        // Hide results when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest(formSelector).length) {
                resultsContainer.hide();
            }
        });
    }

    // Initialize search for menu location
    initSearch('#menu-search-input', '#search-results', '.menu-form-container');

    // Initialize search for admin bar location
    initSearch('#admin-bar-search-input', '#admin-bar-search-results', '.admin-bar-search-container');

    // Prevent default behavior on result item clicks
    $(document).on('click', '.search-result-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        window.location.href = $(this).data('url');
    });

    // Settings page tab functionality
    function initSettingsTabs() {
        // Hide all tab content except the ACF settings by default
        $('#post-types-settings, #design-settings').hide();

        // Function to handle tab switching
        function switchTab(showSelector, clickedTab) {
            // Hide all content
            $('#acf-settings, #post-types-settings, #design-settings').hide();
            // Show selected content
            $(showSelector).show();
            // Remove active class from all tabs
            $('.nav-tab').removeClass('nav-tab-active');
            // Add active class to clicked tab
            $(clickedTab).addClass('nav-tab-active');
        }

        // ACF Settings tab
        $('#acf-settings-tab').click(function (event) {
            event.preventDefault();
            switchTab('#acf-settings', this);
        });

        // Post Types tab
        $('#post-types-tab').click(function (event) {
            event.preventDefault();
            switchTab('#post-types-settings', this);
        });

        // Design Settings tab
        $('#design-settings-tab').click(function (event) {
            event.preventDefault();
            switchTab('#design-settings', this);
        });
    }

    // Initialize settings tabs if we're on the settings page
    if ($('.nav-tab-wrapper').length) {
        initSettingsTabs();
    }

    function searchResultsOnLoad(searchTerm) {

        if (searchTerm) {

            // Get the results container (tbody)
            var resultsContainer = $('#admin-search-results-table tbody');

            // Clear existing rows and show loading message
            resultsContainer.html(`
               <tr>
                   <td colspan="5" class="loading-message">Searching...</td>
               </tr>
           `);

            // Get current URL parameters
            let urlParams = new URLSearchParams(window.location.search);
            // Update the 's' parameter while preserving other parameters (like 'page')
            urlParams.set('s', searchTerm);
            // Update the URL's search parameter 's' without adding a new history entry
            history.pushState(null, '', window.location.pathname + '?' + urlParams.toString());

            // Trigger the AJAX search if search term exists
            searchTimeout = setTimeout(function () {
                $.ajax({
                    url: jmAdminSearch.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'search_posts',
                        search_term: searchTerm,
                        nonce: jmAdminSearch.nonce
                    },
                    success: function (response) {
                        // Clear the "loading" message
                        resultsContainer.empty();

                        if (response.success && Object.keys(response.data).length > 0) {
                            let html = '';
                            let postTypesArray = [];
                            // Iterate through the grouped post types
                            $.each(response.data, function (postType, posts) {

                                // Iterate through the posts for this post type
                                posts.forEach(function (post) {
                                    const statusClass = 'status-' + post.status;
                                    let statusString = '';
                                    let title = toTitleCase(post.status);
                                    // Check if the array already contains an object with the same slug
                                    if (!postTypesArray.some(item => item.slug === post.post_type_slug)) {
                                        postTypesArray.push({
                                            slug: post.post_type_slug,
                                            name: post.post_type
                                        });
                                    }
                                    html += `
                                            <tr class="search-result-row">
                                            <td>${post.id}</td>
                                            <td><a href="${post.edit_url}">${post.title}</a></td>
                                            <td>${post.post_type}</td>
                                            <td>${title}</td>
                                            <td>${post.date}</td>
                                            </tr>
                                        `;
                                });

                            });

                            resultsContainer.html(html).show();
                            populatePostTypeFilter(postTypesArray);

                            // Rebind the sort click event after AJAX
                            $('#sortable-id').click(function () {
                                sortTable(0, false);  // 0 is for the "ID" column
                            });

                            $('#sortable-date').click(function () {
                                sortTable(4, true);  // 4 is for the "Date" column
                            });
                        } else {
                            resultsContainer.html('<tr <td colspan="5" class="search-result-item">No results found</td></tr>').show();
                        }
                    }
                });
            }, 300);
        }
    }

    // Check if the search term exists in the URL (query parameter 's')
    const searchTerm = new URLSearchParams(window.location.search).get('s');
    if (searchTerm) {
        searchResultsOnLoad(searchTerm);
    }

    // Bind the search input field to trigger AJAX on typing
    $('#results-page-search-input').on('input', function () {
        var searchTerm = $(this).val();
        searchResultsOnLoad(searchTerm); // Call the search function on every input change
    });

    // Capitalise string
    function toTitleCase(str) {
        return str.replace(/\w\S*/g, function (txt) { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase(); });
    }

    function populatePostTypeFilter(postTypes) {
        if (postTypes) {
            let cptSelect = $('#post-type-filter');
            let options = '<<option value="">All Post Types</option>';
            // Iterate through the posts for this post type
            postTypes.forEach(function (cpt) {
                options += `<option value="${cpt.name}">${cpt.name}</option>`
            });
            cptSelect.html(options)
            $('#post-type-filter-wrapper').show();
        }
    }

    function filterTable(value) {
        const table = $('#admin-search-results-table');

        if (table) {
            // Loop through each row (skip the header row)
            table.find('tbody tr').each(function () {
                const postTypeCell = $(this).find('td').eq(2); // Index 2 corresponds to the 'Post Type' column
                const postType = postTypeCell.text().trim();

                if (!value || postType === value) {
                    // Show row if value is empty or if post type matches the filter value
                    $(this).show();
                } else {
                    // Hide row if post type doesn't match the filter value
                    $(this).hide();
                }
            });
        }
    }
    $('#post-type-filter').change(function () {
        filterTable(this.value);
    });

    // Sort function for numerical and date columns using jQuery
    function sortTable(index, isDate) {
        // Sortable columns (ID and Date)
        var $table = $('#admin-search-results-table');
        var $rows = $table.find('.search-result-row').get();  // Get rows as an array

        // Determine if sorting ascending or descending based on current direction
        var direction = (isDate ? sortDirectionDate : sortDirectionId) === 'asc' ? 1 : -1;

        $rows.sort(function (rowA, rowB) {
            var cellA = $(rowA).children().eq(index).text().trim();
            var cellB = $(rowB).children().eq(index).text().trim();

            // If it's a date column, convert it to a Date object for comparison
            if (isDate) {
                return direction * (new Date(cellA) - new Date(cellB));
            }
            return direction * (parseInt(cellA, 10) - parseInt(cellB, 10));
        });

        // Reorder rows by appending them in the new order
        $.each($rows, function (_, row) {
            $table.find('tbody').append(row);
        });

        // Toggle the sort direction for next click
        if (isDate) {
            sortDirectionDate = (sortDirectionDate === 'asc' ? 'desc' : 'asc');
        } else {
            sortDirectionId = (sortDirectionId === 'asc' ? 'desc' : 'asc');
        }
    }
});
