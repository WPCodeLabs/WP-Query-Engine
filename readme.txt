=== Plugin Name ===
Contributors: @gaiusinvictus
Donate link: https://www.wpcodelabs.com
Tags: admin, pages, query, wp_query, beaver builder, beaver builder addon
Requires at least: 4.0.1
Tested up to: 4.9.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display a custom query using a shortcode

== Description ==

WP Query Engine allows you to perform custom queries using the WP_Query class, using a simple shortcode or Beaver Builder module (requires Beaver Builder).

Supports:
- Most post data
- Taxonomy data
- Meta data

In addition, the plugin offers several exposed filter and action hooks for theme developers to alter the default functionality, including adding additional parameters not specified in the shortcode.

Also includes 3 default templates, including:

1. Default : Standard content wrapped in an article tag
2. List : Post titles with links in an UL
3. Genesis Loop : If using a Genesis theme, will output using the Genesis Loop

Further, theme developers can easily alter or override the default templates using simple actions and filters, as well as include custom templates easily.

= Using the Plugin =

1. Place the [wp_query] shortcode anywhere withen your content
2. Use the do_action( 'wp_query', $args ); action hook anywhere in your theme
3. Using the Beaver Builder Module

You can see the full documentation and available arguments at [https://docs.wpcodelabs.com/wp-query-engine](https://docs.wpcodelabs.com/wp-query-engine)

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'WP Query Engine'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `wp-query-engine.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `wp-query-engine.zip`
2. Extract the `wp-query-engine` directory to your computer
3. Upload the `wp-query-engine` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

= Start Using the Plugin =

1. Place the [wp_query] shortcode anywhere withen your content
2. Use the do_action( 'wp_query', $args ); action hook anywhere in your theme
3. Using the Beaver Builder Module

You can see the full documentation available at [https://docs.wpcodelabs.com/wp-query-engine](https://docs.wpcodelabs.com/wp-query-engine)


== Changelog ==
= 1.1.0 =
- Provided default loop and filter to override inclusion of default loop, for easier templating


= 1.0.1 =
- Moved template action to class for easier override by templates
- Removed function_exist call in loader, to fix conflict with loading order
- Changed how $wp_query global was handled in main query to address bug with conditional calls
- Added additional "Basic Archive" template

= 1.0 =
Initial Release

== Notes ==

This plugin is provided free, and while I will try to provide support to the best of my ability, I only have so much time to dedicate to supporting a free plugin. If you would like to contribute the this project directly, the main development is done at [GitHub](https://github.com/WPCodeLabs/WP-Query-Engine).