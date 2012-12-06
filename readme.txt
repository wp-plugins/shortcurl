=== ELI's SHORTCURL Shortcode to Fetch and Parse External Content ===
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/shortcurl/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ
Tags: cURL, shortcode, plugin, wp_remote_get, page, post, parse, HTML
Stable tag: 1.2.12.06
Version: 1.2.12.06
Requires at least: 2.7
Tested up to: 3.4.2

This plugin executes wp_remote_get with parameters you pass to the shortcode to display a parsed bit of HTML from another site on your page or post.

== Description ==

Use the shortcode "remote_get" with the parameter "url" to insert the content from that url into your page or post. You can also usr parameters like start and stop (or end) to parse out a specific part of the content that you wish to display.

Updated December-6th

== Installation ==

1. Download and unzip the plugin into your WordPress plugins directory (usually `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' menu in your WordPress Admin.

== Frequently Asked Questions ==

= What do I do after I activate the Plugin? =

Use the shorcode remote_get with a url parameter on a page or post to bring in external content.

== Changelog ==

= 1.2.12.06 =
* Fixed call to plugin_row_meta.

= 1.2.12.05 =
* Added 24 hour caching to speed up page loads.
* Added admin_notices if fatching or parsing produces any errors.

= 1.2.12.01 =
* First versions uploaded to WordPress.

== Upgrade Notice ==

= 1.2.12.06 =
Fixed call to plugin_row_meta.

= 1.2.12.05 =
Added 24 hour caching to speed up page loads and admin_notices if fatching or parsing produces any errors.

= 1.2.12.01 =
First versions available through WordPress.