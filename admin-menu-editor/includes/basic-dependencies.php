<?php
if ( !defined('AME_ROOT_DIR') ) {
	define('AME_ROOT_DIR', dirname(dirname(__FILE__)));
}

$thisDirectory = dirname(__FILE__);
require_once $thisDirectory . '/shadow_plugin_framework.php';
require_once $thisDirectory . '/role-utils.php';
require_once $thisDirectory . '/ame-utils.php';
require_once $thisDirectory . '/ame-option.php';
require_once $thisDirectory . '/menu-item.php';
require_once $thisDirectory . '/menu.php';
require_once $thisDirectory . '/auto-versioning.php';
require_once $thisDirectory . '/../ajax-wrapper/AjaxWrapper.php';
require_once $thisDirectory . '/AmeAutoloader.php';

//Customizable library.
$wsAmeFreeAutoloader = new YahnisElsts\AdminMenuEditor\AmeAutoloader([
	'YahnisElsts\\AdminMenuEditor\\Customizable\\' => AME_ROOT_DIR . '/customizables',
]);
$wsAmeFreeAutoloader->register();
require_once $thisDirectory . '/../customizables/constants.php';

if ( file_exists($thisDirectory . '/../extras/pro-autoloader.php') ) {
	require_once $thisDirectory . '/../extras/pro-autoloader.php';
}

require_once $thisDirectory . '/module.php';
require_once $thisDirectory . '/persistent-module.php';
require_once $thisDirectory . '/shortcodes.php';

if ( !class_exists('WPMenuEditor', false) ) {
	require_once $thisDirectory . '/menu-editor-core.php';
}
