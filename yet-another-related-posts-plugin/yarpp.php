<?php
/*
Plugin Name: Yet Another Related Posts Plugin (YARPP)
Description: Adds related posts to your site and in RSS feeds, based on a powerful, customizable algorithm.
Version: 5.30.10
Author: YARPP
Author URI: https://yarpp.com/
Plugin URI: https://yarpp.com/
Text Domain: yet-another-related-posts-plugin
*/

/**
 * Make sure we don't expose any info if called directly
 */
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	$tr = get_theme_root();
	define( 'WP_CONTENT_DIR', substr( $tr, 0, strrpos( $tr, '/' ) ) );
}

define( 'YARPP_VERSION', '5.30.10' );

define( 'YARPP_DIR', __DIR__ );
/**
 * @deprecated Instead use plugins_url(...,YARPP_MAIN_FILE);. See https://wordpress.org/support/topic/support-for-multilingual-2/
 * This may be removed after October 2020.
 */
define( 'YARPP_URL', plugins_url( '', __FILE__ ) );
define( 'YARPP_MAIN_FILE', __FILE__ );

define( 'YARPP_NO_RELATED', ':(' );
define( 'YARPP_RELATED', ':)' );
define( 'YARPP_NOT_CACHED', ':/' );
define( 'YARPP_DONT_RUN', 'X(' );

/*
Since v3.2: YARPP uses it own cache engine, which uses custom db tables by default.
Use postmeta instead to avoid custom tables by un-commenting postmeta line and comment out the tables one.
*/

/*
Enable postmeta cache: */
// if(!defined('YARPP_CACHE_TYPE')) define('YARPP_CACHE_TYPE', 'postmeta');

/* Enable Yarpp cache engine - Default: */
if ( ! defined( 'YARPP_CACHE_TYPE' ) ) {
	define( 'YARPP_CACHE_TYPE', 'tables' );
}

/* Load proper cache constants */
switch ( YARPP_CACHE_TYPE ) {
	case 'tables':
		define( 'YARPP_TABLES_RELATED_TABLE', 'yarpp_related_cache' );
		break;
	case 'postmeta':
		define( 'YARPP_POSTMETA_KEYWORDS_KEY', '_yarpp_keywords' );
		define( 'YARPP_POSTMETA_RELATED_KEY', '_yarpp_related' );
		break;
}

/* New in 3.5: Set YARPP extra weight multiplier */
if ( ! defined( 'YARPP_EXTRA_WEIGHT' ) ) {
	define( 'YARPP_EXTRA_WEIGHT', 3 );
}

/* Includes ----------------------------------------------------------------------------------------------------------*/
require_once YARPP_DIR . '/includes/compat.php';
require_once YARPP_DIR . '/includes/init_functions.php';
require_once YARPP_DIR . '/includes/related_functions.php';
require_once YARPP_DIR . '/includes/template_functions.php';

require_once YARPP_DIR . '/classes/YARPP_Core.php';
require_once YARPP_DIR . '/classes/YARPP_Block.php';
require_once YARPP_DIR . '/classes/YARPP_Widget.php';
require_once YARPP_DIR . '/classes/YARPP_Cache.php';
require_once YARPP_DIR . '/classes/YARPP_Cache_Bypass.php';
require_once YARPP_DIR . '/classes/YARPP_Cache_Demo_Bypass.php';
require_once YARPP_DIR . '/classes/YARPP_Cache_' . ucfirst( YARPP_CACHE_TYPE ) . '.php';
require_once YARPP_DIR . '/lib/plugin-deactivation-survey/deactivate-feedback-form.php';
require_once YARPP_DIR . '/classes/YARPP_DB_Schema.php';
require_once YARPP_DIR . '/classes/YARPP_DB_Options.php';
require_once YARPP_DIR . '/classes/YARPP_Shortcode.php';

/* WP hooks ----------------------------------------------------------------------------------------------------------*/
add_action( 'init', 'yarpp_init' );
add_action( 'activate_' . plugin_basename( __FILE__ ), 'yarpp_plugin_activate', 10, 1 );
