# WP Admin Advanced Search

## Description

Add support to easily search for any media, product, post or page in the backend

* Adds a search field in the WordPress admin
* Supports searching for title, ACF Field value and WooCommerce product SKU

## Usage

First step is to go to the [Settings Page](#settings) to configure

By default the search field is at the top of the admin menu, just start typing and it will show the results grouped by post type.

When you see the post you are interested in you can just click on it and be taken to the edit screen. This includes media files.

By default it only shows 3 results per post type and sorts by date in descending order (newest to oldest).

If there are more than 3 posts in any given post type then a button will appear to view all.

This opens a page in the backend where you can see all the results and sort by post type, id an date.

![Screenshot of the small results](https://github.com/westcoastdigital/WP-Admin-Advanced-Search/blob/main/assets/image/screenshot-small-results.png)

![Screenshot of the large results](https://github.com/westcoastdigital/WP-Admin-Advanced-Search/blob/main/assets/image/screenshot-large-results.png)

![Screenshot of the view all results](https://github.com/westcoastdigital/WP-Admin-Advanced-Search/blob/main/assets/image/screenshot-view-all.png)

## Settings

Whilst this plugin works out of the box we do have a few settings for you.

These settings can be found under Settings > Admin Search Settings

In there you will have up to 3 tabs

* [ACF Settings](#acf-settings) (only will be there if ACF is active)
* [Post Types](#post-types)
* [Design Settings](#design-settings)

### ACF Settings

All your created fields will show up in here, within their respective field groups.

Select the ones you want to be used in the search query

![Screenshot of the ACF Settings](https://github.com/westcoastdigital/WP-Admin-Advanced-Search/blob/main/assets/image/screenshot-acf-settings.png)

### Post Types

All the available post types, including core ones from WordPress, are included here.

Select the ones you want to be used in the search query

![Screenshot of the Post Types Settings](https://github.com/westcoastdigital/WP-Admin-Advanced-Search/blob/main/assets/image/screenshot-post-type-settings.png)

### Design Settings

There are 3 settings in here

* Choose where you want the search field to show up, top admin bar or in the side menu - Default is Admin Menu
* Choose the amount of posts per post type you want to show in the search field dropdown results, this does not affect the View All page - Default is 3 per post type.
* Choose your sort order. You can sort by the ID, Title or Date and then whether you want it to be ascending or descending order - Default is by Date in Descending order so newest to oldest

![Screenshot of the Design Settings](https://github.com/westcoastdigital/WP-Admin-Advanced-Search/blob/main/assets/image/screenshot-design-settings.png)

## Frequently Asked Questions

<details>
<summary>Why did you build this plugin?</summary>

I wanted an easy way to find posts, pages and media within the backend and I wanted it to be accessible where I wanted it and work via ajax so it is low resources when not being used

</details>

<details>
<summary>Is there limitations on post types?</summary>

No, this works with all registered post types, you can choose which ones to ignore in the [Settings](#settings) page

</details>

<details>
<summary>Does this work with any post meta?</summary>

No, this only searches ACF fields that are enabled in the [Settings](#settings) page and WooCommerce Product SKUs

</details>

<details>
<summary>Is there a filter to disable post types?</summary>

Yes, by default excluded post types are:<br>
```
$excluded_post_types = [
    'acf-field',
    'acf-field-group',
    'oembed_cache',
    'search-filter-widget',
    'wp_global_styles'
];
```
<br><br>
There is a ```simpli_wp_admin_search_excluded_post_types``` filter example:<br>
```
add_filter( 'simpli_wp_admin_search_excluded_post_types', function ( $post_types ) {
    $post_types[] = 'my_custom_post_type';
    $post_types[] = 'another_one';
    return $post_types;
});
```

</details>

## Changelog

### 1.0.0
- Update to support GitHub plugin updater in the WP dashboard

### 0.1.0 (Initial Release)
- Initial Release

## Support

For support, feature requests, or bug reports:

- Author: Jon Mather
- Website: [https://jonmather.au](https://jonmather.au)
- GitHub: [https://github.com/westcoastdigital/Simpli-WP-Optimser](https://github.com/westcoastdigital/Simpli-WP-Optimser)

## Credits

Developed by Jon Mather at SimpliWeb

## License

GPL v2 or later - [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.