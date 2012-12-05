<?php
/*
Plugin Name: ELI's cURL Shortcode Parser
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/shortcurl/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Description: This plugin executes wp_remote_get with parameters you pass through a shortcode to display a parsed bit of HTML from another site in your page or post.
Version: 1.2.12.01
*/
$SHORTCURL_Version='1.2.12.01';
/**
 * SHORTCURL Main Plugin File
 * @package SHORTCURL
*/
/*  Copyright 2012 Eli Scheetz (email: wordpress@ieonly.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
function SHORTCURL_admin_notices() {
	$admin_notices = get_option('SHORTCURL_admin_notices');
	if (is_array($admin_notices))
		foreach ($admin_notices as $admin_notice)
			echo "<div class=\"error\">$admin_notice</div>";
}
add_action('admin_notices', 'SHORTCURL_admin_notices');
if (!headers_sent($filename, $linenum) && !isset($_SESSION)) @session_start();
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) die('You are not allowed to call this page directly.<p>You could try starting <a href="http://'.$_SERVER['SERVER_NAME'].'">here</a>.');
function SHORTCURL_install() {
	global $wp_version;
	if (version_compare($wp_version, "2.6", "<"))
		die("This Plugin requires WordPress version 2.6 or higher");
}
function SHORTCURL_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10) {
		$links_array = array_merge($links_array, array('<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ">'.__( 'Donate' ).'</a>'));
	}
	return $links_array;
}
function SHORTCURL_shortcode($attr) {
	$return = '';
	if (isset($attr['url']) && strlen(trim($attr['url']))) {
		if (!(isset($attr['timeout']) && is_numeric($attr['timeout'])))
			$attr['timeout'] = 30;
		if (isset($_SESSION[$attr['url']]))
			$return = $_SESSION[$attr['url']];
		else {
			$cache_file = dirname(__FILE__).'/cache/'.md5($attr['url']);
			if (is_file($cache_file))
				$return = @file_get_contents($cache_file);
			else {
				$return = wp_remote_get($attr['url'], array("timeout" => $attr['timeout']));
				if (is_wp_error($return))
					return 'ERROR: '.print_r($return, true);
				elseif (isset($return['body'])) {
					$return = $return['body'];
					if (!is_dir(dirname(__FILE__).'/cache/'))
						@mkdir(dirname(__FILE__).'/cache/');
					@file_put_contents($cache_file, $return);
				}
			}
			$_SESSION[$attr['url']] = $return;
		}
		$debug = '';
		if (isset($attr['start']) && strpos($return, html_entity_decode($attr['start'])))
			$return = substr($return, strpos($return, html_entity_decode($attr['start'])));
		elseif (isset($attr['start'])) $debug .= '<li>start=<textarea>'.($attr['start']).'</textarea>';
		if (isset($attr['stop']) && strpos($return, html_entity_decode($attr['stop'])))
			$return = substr($return, 0, strpos($return, html_entity_decode($attr['stop'])));
		elseif (isset($attr['stop'])) $debug .= '<li>stop=<textarea>'.($attr['stop']).'</textarea>';
		if (isset($attr['end']) && strpos($return, $attr['end']))
			$return = substr($return, 0, strpos($return, $attr['end']) + strlen($attr['end']));
		elseif (isset($attr['end'])) $debug .= '<li>end=<textarea>'.($attr['end']).'</textarea>';
		if (isset($attr['length']) && is_numeric($attr['length']) && strlen($return) > abs($attr['length']))
			$return = substr($return, 0, $attr['length']);
		elseif (isset($attr['length'])) $debug .= '<li>length=<textarea>'.($attr['length']).'</textarea>';
		if (isset($attr['replace']) && isset($attr['with']) && strlen($attr['replace']) && strlen($attr['with']))
			$return = str_replace($attr['replace'], $attr['with'], $return);
		if (isset($attr['replace2']) && isset($attr['with2']) && strlen($attr['replace2']) && strlen($attr['with2']))
			$return = str_replace($attr['replace2'], $attr['with2'], $return);
	}
	return (strlen($debug)?$debug.'<textarea>'.$return.'</textarea>':$return);
}
add_filter('plugin_row_meta', $SHORTCURL_plugin_dir.'_set_plugin_row_meta', 1, 2);
register_activation_hook(__FILE__,$SHORTCURL_plugin_dir.'_install');
add_shortcode("remote_get", "SHORTCURL_shortcode");
?>
