<?php
/*
Plugin Name: EZ SHORTCURL Shortcodes to Fetch and Parse External Content
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/shortcurl/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Description: Use the shortcode "remote_get" with the parameter "url" to insert the content from that url into your page or post.
Version: 3.14.34
*/
$SHORTCURL_Version="3.14.34";
/*            ___
 *           /  /\     SHORTCURL Main Plugin File
 *          /  /:/     @package SHORTCURL
 *         /__/::\
 Copyright \__\/\:\__  © 2012-2014 Eli Scheetz (email: wordpress@ieonly.com)
 *            \  \:\/\
 *             \__\::/ This program is free software; you can redistribute it
 *     ___     /__/:/ and/or modify it under the terms of the GNU General Public
 *    /__/\   _\__\/ License as published by the Free Software Foundation;
 *    \  \:\ /  /\  either version 2 of the License, or (at your option) any
 *  ___\  \:\  /:/ later version.
 * /  /\\  \:\/:/
  /  /:/ \  \::/ This program is distributed in the hope that it will be useful,
 /  /:/_  \__\/ but WITHOUT ANY WARRANTY; without even the implied warranty
/__/:/ /\__    of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
\  \:\/:/ /\  See the GNU General Public License for more details.
 \  \::/ /:/
  \  \:\/:/ You should have received a copy of the GNU General Public License
 * \  \::/ with this program; if not, write to the Free Software Foundation,
 *  \__\/ Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA        */

foreach (array("get_option", "add_action", "add_shortcode", "register_activation_hook") as $func)
	if (!function_exists("$func"))
		die('You are not allowed to call this page directly.<p>You could try starting <a href="/">here</a>.');

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
add_action("admin_notices", "SHORTCURL_admin_notices");

function SHORTCURL_install() {
	global $wp_version;
	if (version_compare($wp_version, "2.7", "<") || !function_exists("wp_remote_get"))
		die("This Plugin requires WordPress version 2.7 or higher for wp_remote_get() to work!");
}
register_activation_hook(__FILE__, "SHORTCURL_install");

function SHORTCURL_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(__file__, (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
		$links_array = array_merge($links_array, array('<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ">'.__( 'Donate' ).'</a>'));
	return $links_array;
}
add_filter("plugin_row_meta", "SHORTCURL_set_plugin_row_meta", 1, 2);

function SHORTCURL_preg_replace($attr = array(), $content) {
	return SHORTCURL_preg_replace_shortcode($attr, do_shortcode($content));
}
add_shortcode("preg_replace", "SHORTCURL_preg_replace");

function SHORTCURL_preg_replace_shortcode($attr = array(), $content) {
	$regex = array();
	$with = array();
	foreach (array_keys($attr) as $k)
		if (substr($k, 0, 1) == 'r')
			$regex[] = $attr[$k];
		elseif (substr($k, 0, 1) == 'w')
			$with[] = $attr[$k];
	if (count($regex) && count($with))
		$content = preg_replace($regex, $with, $content);
	return do_shortcode($content);
}
add_shortcode("preg_replace_shortcode", "SHORTCURL_preg_replace_shortcode");

function SHORTCURL_str_replace($attr = array(), $content) {
	if (isset($attr['replace']) && strlen($attr['replace']) && isset($attr['with']))
		$content = str_replace($attr['replace'], $attr['with'], $content);
//		$content = str_replace(html_entity_decode($attr['replace']), html_entity_decode($attr['with']), $content);//maybe this would be better, maybe...
	return $content;
}
add_shortcode("str_replace", "SHORTCURL_str_replace");

function SHORTCURL_remote_get($attr, $url = "") {
	$return = '';
	$debug = '';
	$error = '';
	if (strlen(trim($url)))
		$attr["url"] = $url;
	if (isset($attr['url']) && strlen(trim($attr['url']))) {
		if (!(isset($attr['timeout']) && is_numeric($attr['timeout'])))
			$attr['timeout'] = 30; //default remote page to timeout after 30 seconds
		if (!(isset($attr['expire']) && is_numeric($attr['expire'])))
			$attr['expire'] = 60*60*24; //default cache to expire in 24 hours
		if (!isset($GLOBALS["SC_URL"][$attr['url']]['date'])) {
			$cache_file = dirname(__FILE__).'/cache/'.md5($attr['url']);
			if (is_file($cache_file) && $GLOBALS["SC_URL"][$attr['url']]['body'] = @file_get_contents($cache_file))
				$GLOBALS["SC_URL"][$attr['url']]['date'] = filemtime($cache_file);
		}
		if (isset($GLOBALS["SC_URL"][$attr['url']]['date']) && $GLOBALS["SC_URL"][$attr['url']]['date']>(time()-($attr['expire'])))
			$debug .= html_entity_decode($attr["url"]).'====='.$attr["url"].'SHORTCURL cached('.date("Y-m-d H:i:s", $GLOBALS["SC_URL"][$attr['url']]['date'])."): ".(floor((time()-$GLOBALS["SC_URL"][$attr['url']]['date'])/60)>59?floor((time()-$GLOBALS["SC_URL"][$attr['url']]['date'])/60/60)." hours":floor((time()-$GLOBALS["SC_URL"][$attr['url']]['date'])/60)." minutes")." ago;\n";
		elseif ($got = wp_remote_get(html_entity_decode($attr['url']), (isset($attr['timeout'])?array("timeout" => $attr['timeout']):array()))) {
			if (is_wp_error($got))
				$error .= "SHORTCURL ERROR: wp_remote_get(".html_entity_decode($attr['url']).") returned ".print_r(array("ERROR"=>$got), true)."\n";
			elseif (isset($got['body']) && strlen($got['body'])) {
				$GLOBALS["SC_URL"][$attr['url']]['body'] = $got['body'];
				$GLOBALS["SC_URL"][$attr['url']]['date'] = time();
				if ($written = @file_put_contents($cache_file, $GLOBALS["SC_URL"][$attr["url"]]["body"]))
					$debug .= "SHORTCURL cached(".strlen($GLOBALS["SC_URL"][$attr["url"]]["body"]).") bytes to ".md5($attr["url"]).";\n";
			}
		}
		if (isset($GLOBALS["SC_URL"][$attr['url']]['body'])) {
			$return = $GLOBALS["SC_URL"][$attr['url']]['body'];
			$debug .= "SHORTCURL body_length(".strlen($return).");\n";
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
			if (isset($attr['replace']) && isset($attr['with']) && strlen($attr['replace']))
				$return = str_replace($attr['replace'], $attr['with'], $return);
			if (isset($attr['replace2']) && isset($attr['with2']) && strlen($attr['replace2']))
				$return = str_replace($attr['replace2'], $attr['with2'], $return);
		} else
			$error .= "SHORTCURL ERROR: wp_remote_get($attr[url]) returned NOTHING!\n";
	}
	if ($error) {
		$admin_notices = get_option('SHORTCURL_admin_notices');
		$admin_notices[md5($error)] = date("m-d H:i: ").$_SERVER["REQUEST_URI"]."<li>$error</li><br /><textarea>".htmlspecialchars($GLOBALS["SC_URL"][$attr["url"]]["body"])."</textarea>";
		update_option('SHORTCURL_admin_notices', $admin_notices);
	}
	return "<!-- $debug -->\n$return";
}
add_shortcode("remote_get", "SHORTCURL_remote_get");