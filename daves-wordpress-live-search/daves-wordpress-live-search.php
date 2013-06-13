<?php

/*
Plugin Name: Dave's WordPress Live Search
Description: Adds "live search" functionality to your WordPress site. Uses the built-in search and jQuery.
Version: 3.3
Author: Dave Ross
Author URI: http://davidmichaelross.com/
Plugin URI: http://wordpress.org/extend/plugins/daves-wordpress-live-search/
*/

/**
 * Copyright (c) 2009 Dave Ross <dave@csixty4.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 **/

if(5.0 > floatval(phpversion())) {
	// Call the special error handler that displays an error
	add_action('admin_notices', 'daves_wp_live_search_phpver_admin_notice');
}

add_action('init', 'daves_wp_live_search_init');
include_once "DWLSTransients.php";

function daves_wp_live_search_init() {
	if(defined('DOING_AJAX')) {
		include_once "DavesWordPressLiveSearchResults.php";
	}
	else {
		include_once "DavesWordPressLiveSearch.php";
		add_action('admin_notices', array('DavesWordPressLiveSearch', 'admin_notices'));

		// Register hooks
		add_action('admin_menu', array('DavesWordPressLiveSearch', 'admin_menu'));
		add_action('wp_head', array('DavesWordPressLiveSearch', 'head'));
		add_action('admin_head', array('DavesWordPressLiveSearch', 'head'));
		register_activation_hook(__FILE__, array('DavesWordPressLiveSearch', 'activate'));
		add_action('admin_enqueue_scripts', array('DavesWordPressLiveSearch', 'admin_enqueue_scripts'));
		DavesWordPressLiveSearch::advanced_search_init();
	}
}

function daves_wp_live_search_phpver_admin_notice() {
	$alertMessage = __("Dave's WordPress Live Search requires PHP 5.0 or higher");
	echo "<div class=\"updated\"><p><strong>$alertMessage</strong></p></div>";
}

// Provide a json_encode implementation if none exists (PHP < 5.2.0)
if(!function_exists('json_encode')) {
    require ABSPATH."/wp-includes/compat.php" ;
}

// Relevanssi "bridge" plugin
class DWLS_Relevanssi_Bridge {
	static function init() {
		if(function_exists('relevanssi_do_query')) {
			add_filter('dwls_alter_results', array('DWLS_Relevanssi_Bridge', 'dwls_alter_results'), 10, 3);
		}
	}

	static function dwls_alter_results($wpQueryResults, $maxResults, $results) {
		global $wp_query;

		// WP_Query::query() set everything up
		// Now have Relevanssi do the query over again
		// but do it in its own way
		// Thanks Mikko!
		relevanssi_do_query($wp_query);
		$results->relevanssi = true;

		// Mikko says Relevanssi 2.5 doesn't handle limits
		// when it queries, so I need to handle them on my own.
		$wpQueryResults = array_slice($wp_query->posts, 0, $maxResults);

		return $wpQueryResults;
	}
}
add_action('init', array('DWLS_Relevanssi_Bridge', 'init'));
