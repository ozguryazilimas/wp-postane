<?php
/*
Plugin Name: Ugurcum
Plugin URI: http://www.ozguryazilim.com.tr
Description: This plugin displays a list of multimedia files in a fancy way. Allows addition for logged in users, and modification for admin users.
Version: 1.1.0
Author: Onur Küçük
Author URI: http://www.delipenguen.net
License: GPL2
*/

/*  Copyright (C) 2014, Onur Küçük

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

require_once(dirname(__FILE__) . '/includes/ugurcum_common.php');
$wp_uw = new WP_Ugurcum_Widget();

global $ugurcum_db_version;
$ugurcum_db_version = "1.0";

global $wpdb, $ugurcum_db_main, $ugurcum_db_user_reads;

$ugurcum_db_main = $wpdb->prefix . "ugurcum";
$ugurcum_db_user_reads = $wpdb->prefix . "ugurcum_user_reads";

$installed_version = get_option('ugurcum_db_version');
global $installed_version;

//widget
add_action('widgets_init', 'ugurcum_init');

// add css
add_action('init', 'ugurcum_add_assets');

register_activation_hook(__FILE__, 'ugurcum_init_db');
add_action('plugins_loaded', 'ugurcum_update_db_check');


function ugurcum_init_db() {
  global $ugurcum_db_version, $ugurcum_db_user_reads, $ugurcum_db_main, $installed_version, $wpdb;
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php');

  if ($installed_version != $ugurcum_db_version) {
    $main_sql = "CREATE TABLE $ugurcum_db_main (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      title TEXT NOT NULL,
      description LONGTEXT NOT NULL,
      medialink LONGTEXT NOT NULL,
      user_id BIGINT(20) UNSIGNED NOT NULL,
      visible BOOLEAN DEFAULT NULL,
      created_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      updated_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id)
    );";
    dbDelta($main_sql);

    $user_read_sql = "CREATE TABLE $ugurcum_db_user_reads (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      user_id BIGINT(20) UNSIGNED NOT NULL,
      read_time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id),
                UNIQUE KEY user_id (user_id)
              );";
    dbDelta($user_read_sql);

    update_option("ugurcum_db_version", $ugurcum_db_version);
  }
}

function ugurcum_update_db_check() {
  global $ugurcum_db_version, $installed_version;

  if (get_site_option('ugurcum_db_version') != $ugurcum_db_version) {
    ugurcum_init_db();
  }
}

function ugurcum_add_assets() {
  // enqueue WordPress CSS hook
  wp_register_script('jquery_datatables_js', '//cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js');
  wp_enqueue_script('jquery_datatables_js');

  wp_register_style('jquery_datatables_css', '//cdn.datatables.net/1.10.9/css/jquery.dataTables.css');
  wp_enqueue_style('jquery_datatables_css');

  wp_enqueue_style('ugurcum', get_option('siteurl') . '/wp-content/plugins/ugurcum/css/ugurcum.css');
}

function ugurcum_init() {
  load_plugin_textdomain('ugurcum', false, basename(dirname(__FILE__)) . '/languages' );
  register_widget('WP_Ugurcum_Widget');
}

add_action('template_redirect', 'ugurcum_custom_page_template_redirect');
function ugurcum_custom_page_template_redirect() {
  global $wp_query;

  if ($wp_query->query_vars['name'] == 'ugurcum') {
    $wp_query->is_404 = false;
    // $wp_query->is_archive = true;
    status_header(200);
    include(ABSPATH . 'wp-content/plugins/ugurcum/ugurcum_main.php');
    exit;
  }
}

?>
