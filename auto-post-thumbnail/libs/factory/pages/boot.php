<?php
/**
 * Factory Pages
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.1
 * @package       core
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

// module provides function only for the admin area
if( !is_admin() ) {
	return;
}

if( defined('FACTORY_PAGES_451_LOADED') ) {
	return;
}

define('FACTORY_PAGES_451_LOADED', true);

define('FACTORY_PAGES_451_VERSION', '4.5.1');

define('FACTORY_PAGES_451_DIR', dirname(__FILE__));
define('FACTORY_PAGES_451_URL', plugins_url(null, __FILE__));

if( !defined('FACTORY_FLAT_ADMIN') ) {
	define('FACTORY_FLAT_ADMIN', true);
}

load_plugin_textdomain('wbcr_factory_pages_451', false, dirname(plugin_basename(__FILE__)) . '/langs');

require(FACTORY_PAGES_451_DIR . '/pages.php');
require(FACTORY_PAGES_451_DIR . '/includes/page.class.php');
require(FACTORY_PAGES_451_DIR . '/includes/admin-page.class.php');


