<?php
/*
Plugin Name: ELI's SHORTCURL Shortcode to Fetch and Parse External Content
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/shortcurl/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Description: Use the shortcode "remote_get" with the parameter "url" to insert the content from that url into your page or post.
Version: 1.3.03.15
*/
$SHORTCURL_Version="1.3.03.15";
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
	if (isset($_GET['SHORTCURL_admin_key']) && isset($admin_notices[$_GET['SHORTCURL_admin_key']])) {
		unset($admin_notices[$_GET['SHORTCURL_admin_key']]);
		update_option('SHORTCURL_admin_notices', $admin_notices);
	}
	$_SERVER_REQUEST_URI = str_replace('&amp;','&', htmlspecialchars( $_SERVER['REQUEST_URI'] , ENT_QUOTES ) );
	$script_URI = $_SERVER_REQUEST_URI.(strpos($_SERVER_REQUEST_URI,'?')?'&':'?').'ts='.microtime(true);
	if (is_array($admin_notices))
		foreach ($admin_notices as $key=>$admin_notice)
			echo "<div class=\"error\">$admin_notice <a href='$script_URI&SHORTCURL_admin_key=$key'>[dismiss]</a></div>";
}
if (!headers_sent($filename, $linenum) && !isset($_SESSION)) @session_start();
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) die('You are not allowed to call this page directly.<p>You could try starting <a href="http://'.$_SERVER['SERVER_NAME'].'">here</a>.');
function SHORTCURL_install() {
	global $wp_version;
	if (version_compare($wp_version, "2.7", "<"))
		die("This Plugin requires WordPress version 2.7 or higher for wp_remote_get() to work!");
}
function SHORTCURL_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
		$links_array = array_merge($links_array, array('<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ">'.__( 'Donate' ).'</a>'));
	return $links_array;
}
function SHORTCURL_shortcode($attr) {
	$return = '';
	$debug = '';
	$error = '';
	if (isset($attr['url']) && strlen(trim($attr['url']))) {
		if (!(isset($attr['timeout']) && is_numeric($attr['timeout'])))
			$attr['timeout'] = 30;
		if (!isset($_SESSION[$attr['url']]['date'])) {
			$cache_file = dirname(__FILE__).'/cache/'.md5($attr['url']);
			if (is_file($cache_file) && $_SESSION[$attr['url']]['body'] = @file_get_contents($cache_file))
				$_SESSION[$attr['url']]['date'] = filemtime($cache_file);
		}
		if (isset($_SESSION[$attr['url']]['date']) && $_SESSION[$attr['url']]['date']>(time()-(60*60*24)))
			$debug .= 'SHORTCURL cached('.date("Y-m-d H:i:s", $_SESSION[$attr['url']]['date'])."): ".floor((time()-$_SESSION[$attr['url']]['date'])/60/60)." hours ago;\n";
		elseif ($got = wp_remote_get($attr['url'], (isset($attr['timeout'])?array("timeout" => $attr['timeout']):array()))) {
			if (is_wp_error($got))
				$error .= "SHORTCURL ERROR: wp_remote_get($attr[url]) returned ".print_r($got, true)."\n";
			elseif (isset($got['body']) && strlen($got['body'])) {
				$_SESSION[$attr['url']]['body'] = $got['body'];
				$_SESSION[$attr['url']]['date'] = time();
				@file_put_contents($cache_file, $_SESSION[$attr['url']]['body']);
			}
		}
		if (isset($_SESSION[$attr['url']]['body'])) {
			$debug .= "SHORTCURL wp_remote_get($attr[url]);\n";
			$return = $_SESSION[$attr['url']]['body'];
			if (isset($attr['start']) && strpos($return, html_entity_decode($attr['start'])))
				$return = substr($return, strpos($return, html_entity_decode($attr['start'])));
			elseif (isset($attr['start'])) $error .= "SHORTCURL start=<b>".htmlspecialchars($attr['start'])."</b> but not found in ($attr[url])!\n";
			if (isset($attr['stop']) && strpos($return, html_entity_decode($attr['stop'])))
				$return = substr($return, 0, strpos($return, html_entity_decode($attr['stop'])));
			elseif (isset($attr['stop'])) $error .= "SHORTCURL stop=<b>".htmlspecialchars($attr['stop'])."</b> but not found in ($attr[url])!\n";
			if (isset($attr['end']) && strpos($return, $attr['end']))
				$return = substr($return, 0, strpos($return, $attr['end']) + strlen($attr['end']));
			elseif (isset($attr['end'])) $error .= "SHORTCURL end=<b>".htmlspecialchars($attr['end'])."</b> but not found in ($attr[url])!\n";
			if (isset($attr['length']) && is_numeric($attr['length']) && strlen($return) > abs($attr['length']))
				$return = substr($return, 0, $attr['length']);
			elseif (isset($attr['length'])) $error .= "SHORTCURL length=<b>".($attr['length'])."</b> Invalid when content length=<b>".strlen($return)."</b>!\n";
			if (isset($attr['replace']) && isset($attr['with']) && strlen($attr['replace']) && strlen($attr['with']))
				$return = str_replace($attr['replace'], $attr['with'], $return);
			if (isset($attr['replace2']) && isset($attr['with2']) && strlen($attr['replace2']) && strlen($attr['with2']))
				$return = str_replace($attr['replace2'], $attr['with2'], $return);
		} else
			$error .= "SHORTCURL ERROR: wp_remote_get($attr[url]) returned NOTHING!\n";
	}
	if ($error) {
		$admin_notices = get_option('SHORTCURL_admin_notices');
		$admin_notices[md5($error)] = date("m-d H:i: ").$error;
		update_option('SHORTCURL_admin_notices', $admin_notices);
	}
	return "<!-- $debug -->\n$return";
}
add_action("admin_notices", "SHORTCURL_admin_notices");
add_filter("plugin_row_meta", "SHORTCURL_set_plugin_row_meta", 1, 2);
register_activation_hook(__FILE__, "SHORTCURL_install");
add_shortcode("remote_get", "SHORTCURL_shortcode");
?>
