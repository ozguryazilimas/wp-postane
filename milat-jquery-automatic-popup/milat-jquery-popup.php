<?php
/*
 * @package MiLaT
 * @subpackage jQuery Otomatik Popup
 *
 * Plugin Name: Milat jQuery Automatic Popup
 * Plugin URI: http://www.milat.org/wordpress-otomatik-popup-eklentisi/
 * Description: jQuery ile Sayfa Yüklendikten Sonra Otomatik Açılan Popup
 * Version: 1.3.1
 * Author: MiLaT
 * Author URI: http://www.milat.org/
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }

define ( 'MILAT_PLUGIN_URL', WP_PLUGIN_URL . '/' . dirname( plugin_basename( __FILE__ ) )  .'/');
define ( 'MILAT_MILAT', 'milat_milat');


include_once 'milat-load.php';
    load_plugin_textdomain( 'milat_milat', false, dirname( plugin_basename(__FILE__) ) . '/lang' );
/* Kurulum */
include_once 'lib/includes/admin.install.php';
?>