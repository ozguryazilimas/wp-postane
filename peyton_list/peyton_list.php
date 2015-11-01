<?php
/*
Plugin Name: Peyton List
Plugin URI: http://www.ozguryazilim.com.tr
Description: This plugin lists whatever you like with ability to make a list with catgories and links. This is dedicated to the lovely Peyton List (the more beautiful one, with black hair).
Version: 0.2.0
Author: Onur Küçük
Author URI: http://www.delipenguen.net
License: GPL2
*/

/*  Copyright (C) 2015, Onur Küçük

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once(dirname(__FILE__) . '/includes/peyton_list_common.php');

$peyton_list_db_version = "1.0";
global $peyton_list_db_version;

global $wpdb;
$peyton_list_db_main = $wpdb->prefix . 'peyton_list';
global $peyton_list_db_main;

$installed_version = get_option('peyton_list_db_version');
global $installed_version;

$peyton_list_category = array(
  1 => 'Drama',
  2 => 'Komedi',
  3 => 'Komedi Drama',
  4 => 'Animasyon',
  5 => 'Anime',
  6 => 'Reality'
);
global $peyton_list_category;

$peyton_list_status = array(
  1 => 'Tanıtımı Var',
  2 => 'Mini Tanıtım',
  3 => 'Tanıtımı Yok'
);
global $peyton_list_status;


// add css
add_action('init', 'peyton_list_init');

register_activation_hook(__FILE__, 'peyton_list_init_db');
add_action('plugins_loaded', 'peyton_list_update_db_check');


function peyton_list_init_db() {
  global $peyton_list_db_version, $installed_version, $wpdb;
  $peyton_list_db_main = $wpdb->prefix . 'peyton_list';
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

  if ($installed_version != $peyton_list_db_version) {
    $main_sql = "CREATE TABLE $peyton_list_db_main (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      title TEXT NOT NULL,
      link LONGTEXT NOT NULL,
      category INT NOT NULL,
      status INT NOT NULL,
      created_by BIGINT(20) UNSIGNED NOT NULL,
      updated_by BIGINT(20) UNSIGNED NOT NULL,
      created_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      updated_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    );";
    dbDelta($main_sql);

    update_option("peyton_list_db_version", $peyton_list_db_version);
  }
}

function peyton_list_update_db_check() {
  global $peyton_list_db_version, $installed_version;

  if (get_site_option('peyton_list_db_version') != $peyton_list_db_version) {
    peyton_list_init_db();
  }
}

function peyton_list_add_assets() {
  // enqueue WordPress CSS hook
  wp_register_script('jquery_datatables_js', '//cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js');
  wp_enqueue_script('jquery_datatables_js');

  wp_register_style('jquery_datatables_css', '//cdn.datatables.net/1.10.9/css/jquery.dataTables.css');
  wp_enqueue_style('jquery_datatables_css');

  wp_enqueue_style('peyton_list', get_option('siteurl') . '/wp-content/plugins/peyton_list/css/peyton_list.css');
  wp_enqueue_script('peyton_list', get_option('siteurl') . '/wp-content/plugins/peyton_list/js/peyton_list.js');
}

function peyton_list_init() {
  load_plugin_textdomain('peyton_list', false, basename(dirname(__FILE__)) . '/languages' );
}

function peyton_list_get_main_data() {
  peyton_list_add_assets();
  peyton_list_main();
}

add_shortcode('peyton_list', 'peyton_list_get_main_data');


?>
