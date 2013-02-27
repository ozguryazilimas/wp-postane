<?php
/*
Plugin Name: CubePM
Plugin URI: http://cubepoints.com
Description: CubePM is a complete Private Messaging system for your WordPress site.
Version: 1.0
Author: Jonathan Lau
Author URI: http://cubepoints.com
*/

global $wpdb;

/** Define constants */
define('CPM_VER', '1.0');
define('CPM_DB_MSG', $wpdb->base_prefix . 'cpm_msg');
define('CPM_DB_META', $wpdb->base_prefix . 'cpm_meta');
define('CPM_PATH', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));
define('CPM_USERMETA_KEY', 'cubepm');

/** Loads the plugin's translated strings */
load_plugin_textdomain('cpm', false, dirname(plugin_basename(__FILE__)).'/languages');

/** Includes install script */
require_once 'cpm_install.php';

/** Includes core functions */
require_once 'cpm_core.php';

/** Includes main functions */
require_once 'cpm_main.php';

/** Includes admin pages */
require_once 'cpm_admin.php';

/** Includes function pages */
require_once 'cpm_page_inbox.php';
require_once 'cpm_page_read.php';
require_once 'cpm_page_new.php';
require_once 'cpm_page_admin_inbox.php';

/** Includes functions to process email subscriptions */
require_once 'cpm_email.php';

/** Includes filters */
require_once 'cpm_filters.php';

/** Hook for plugin installation */
register_activation_hook( __FILE__ , 'cpm_install' );

/** Adds the shortcode for output of HTML */
add_shortcode( 'cubepm', 'cpm_shortcode' );

/** Adds the ajax handler for autocompleting recipients */
add_action('wp_ajax_cpm_recipient', 'cpm_ajax_recipient');

/** Enqueues CubePM's JS and CSS */
//add_action('wp_print_scripts', 'cpm_enqueue_scripts');
//add_action('wp_print_styles', 'cpm_enqueue_styles');

add_action('wp_enqueue_scripts', 'cpm_enqueue_scripts');
add_action('wp_enqueue_scripts', 'cpm_enqueue_styles');

?>