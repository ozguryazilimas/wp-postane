<?php
/*
Plugin Name: Peyton List
Plugin URI: http://www.ozguryazilim.com.tr
Description: This plugin lists whatever you like with ability to make a list with catgories and links. This is dedicated to the lovely Peyton List (the more beautiful one, with black hair).
Version: 0.9.0
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
require_once(dirname(__FILE__) . '/includes/daisy_fortune_common.php');

$peyton_list_db_version = '1.1';
global $peyton_list_db_version;

$peyton_list_version = '0.9.0';
global $peyton_list_version;

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
  6 => 'Reality',
  7 => 'Belgesel',
  8 => 'Yerli Dizi'
);
global $peyton_list_category;

$peyton_list_category_color = array(
  1 => 'peyton_list_category_color_drama',
  2 => 'peyton_list_category_color_komedi',
  3 => 'peyton_list_category_color_komedi_drama',
  4 => 'peyton_list_category_color_animasyon',
  5 => 'peyton_list_category_color_anime',
  6 => 'peyton_list_category_color_reality',
  7 => 'peyton_list_category_color_belgesel',
  8 => 'peyton_list_category_color_yerli_dizi'
);
global $peyton_list_category_color;

$peyton_list_status = array(
  1 => 'Tanıtım',
  2 => 'Mini Tanıtım',
  3 => 'Tanıtımsız'
);
global $peyton_list_status;

$peyton_list_status_editor = array(
  1 => 'Tanıtımı Var',
  2 => 'Mini Tanıtım',
  3 => 'Tanıtımı Yok'
);
global $peyton_list_status_editor;

$peyton_list_status_image = array(
  1 => 'thumbs_up.svg',
  2 => 'thumbs_side.svg',
  3 => 'thumbs_down.svg'
);
global $peyton_list_status_image;


$daisy_fortune_onair = array(
  1 => 'Running',
  2 => 'Mini',
  3 => 'Canceled',
  4 => 'Unclear',
  0 => 'Missing'
);
global $daisy_fortune_onair;

$daisy_fortune_onair_translated = array(
  1 => __('Running', 'peyton_list'),
  2 => __('Mini', 'peyton_list'),
  3 => __('Canceled', 'peyton_list'),
  4 => __('Unclear', 'peyton_list'),
  5 => __('Missing', 'peyton_list')
);
global $daisy_fortune_onair_translated;

$daisy_fortune_onair_image = array(
  0 => 'daisy_fortune_missing.png',
  1 => 'daisy_fortune_check.png',
  2 => 'daisy_fortune_mini.png',
  3 => 'daisy_fortune_cross.png',
  4 => 'daisy_fortune_question.png'
);
global $daisy_fortune_onair_image;


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
      onair INT NOT NULL,
      comment TEXT NOT NULL,
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
  wp_register_script('jquery_datatables_js', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', array('jquery'), $peyton_list_version);
  wp_enqueue_script('jquery_datatables_js');

  wp_register_style('jquery_datatables_css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.css', $peyton_list_version);
  wp_enqueue_style('jquery_datatables_css');
}
add_action('wp_enqueue_scripts', 'peyton_list_add_assets');

function peyton_list_add_custom_assets() {
  wp_register_style('peyton_list', plugins_url('css/peyton_list.css', __FILE__), $peyton_list_version);
  wp_enqueue_style('peyton_list');

  wp_register_script('peyton_list', plugins_url('js/peyton_list.js', __FILE__), array('jquery', 'jquery_datatables_js'), $peyton_list_version);
  wp_enqueue_script('peyton_list');
}

function daisy_fortune_add_custom_assets() {
  wp_register_style('daisy_fortune', plugins_url('css/daisy_fortune.css', __FILE__), $peyton_list_version);
  wp_enqueue_style('daisy_fortune');

  wp_register_script('daisy_fortune', plugins_url('js/daisy_fortune.js', __FILE__), array('jquery', 'jquery_datatables_js'), $peyton_list_version);
  wp_enqueue_script('daisy_fortune');
}

function peyton_list_init() {
  load_plugin_textdomain('peyton_list', false, basename(dirname(__FILE__)) . '/languages' );
}

function peyton_list_get_main_data() {
  peyton_list_add_custom_assets();
  return peyton_list_main();
}

function daisy_fortune_get_main_data() {
  daisy_fortune_add_custom_assets();
  return daisy_fortune_main();
}

add_shortcode('peyton_list', 'peyton_list_get_main_data');
add_shortcode('daisy_fortune', 'daisy_fortune_get_main_data');

function peyton_list_ajax() {
  $has_perm = peyton_list_user_has_permission();
  $action = $_POST['peyton_list_action'];
  $data = $_POST['entry'];

  if (!$has_perm || !isset($data)) {
    return;
  }

  switch ($action) {
  case 'update':
    $updated_data = array();
    $success = peyton_list_update_entry($data);

    if ($success) {
      $updated_data = peyton_list_get_single_entry($data['id']);
    }

    echo json_encode(
      array(
        'success' => $success,
        'data' => $updated_data
      )
    );
    break;
  case 'delete':
    $delete_id = $data['id'];
    $success = peyton_list_delete_entry($delete_id);

    echo json_encode(array('success' => $success));
    break;
  }

  wp_die();
}
add_action('wp_ajax_peyton_list', 'peyton_list_ajax');

function daisy_fortune_ajax() {
  $has_perm = daisy_fortune_user_has_permission();
  $action = $_POST['daisy_fortune_action'];
  $data = $_POST['entry'];

  if (!$has_perm || !isset($data)) {
    return;
  }

  switch ($action) {
  case 'update':
    $updated_data = array();
    $success = daisy_fortune_update_entry($data);

    if ($success) {
      $updated_data = daisy_fortune_get_single_entry($data['id']);
    }

    echo json_encode(
      array(
        'success' => $success,
        'data' => $updated_data
      )
    );
    break;
  case 'delete':
    $delete_id = $data['id'];
    $success = daisy_fortune_delete_entry($delete_id);

    echo json_encode(array('success' => $success));
    break;
  }

  wp_die();
}
add_action('wp_ajax_daisy_fortune', 'daisy_fortune_ajax');


?>
