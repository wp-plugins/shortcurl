=== ELI's SHORTCURL Shortcode to Fetch and Parse External Content ===
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/shortcurl/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ
Tags: cURL, shortcode, plugin, wp_remote_get, page, post, parse, HTML
Stable tag: 1.3.03.15
Version: 1.3.03.15
Requires at least: 2.7
Tested up to: 3.5.1

This plugin executes wp_remote_get with parameters you pass to the shortcode to display a parsed bit of HTML from another site on your page or post.

== Description ==

Use the shortcode "remote_get" with the parameter "url" to insert the content from that url into your page or post. You can also usr parameters like start and stop (or end) to parse out a specific part of the content that you wish to display.

Updated March-15th

== Installation ==

1. Download and unzip the plugin into your WordPress plugins directory (usually `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' menu in your WordPress Admin.

== Frequently Asked Questions ==

= What do I do after I activate the Plugin? =

Use the shorcode remote_get with a url parameter on a page or post to bring in external content.

= What does a example of the shortcode look like? =

[remote_get url="http://wordpress.org/extend/plugins/shortcurl/stats/" start='<div class="block-content"' stop='!-- block-content--' length="-1" replace="='/extend" with="='http://wordpress.org/extend" replace2="%2Fextend%2F" with2="http%3A%2F%2Fwordpress.org%2Fextend%2F"]

== Changelog ==

= 1.3.03.15 =
* Fixed error handling on line 71 to report the whole curl error on the admin pages.

= 1.2.12.06 =
* Fixed call to plugin_row_meta.

= 1.2.12.05 =
* Added 24 hour caching to speed up page loads.
* Added admin_notices if fatching or parsing produces any errors.

= 1.2.12.01 =
* First versions uploaded to WordPress.

== Upgrade Notice ==

= 1.3.03.15 =
Fixed error handling on line 71 to report the whole curl error on the admin pages.

= 1.2.12.06 =
Fixed call to plugin_row_meta.

= 1.2.12.05 =
Added 24 hour caching to speed up page loads and admin_notices if fatching or parsing produces any errors.

= 1.2.12.01 =
First versions available through WordPress.