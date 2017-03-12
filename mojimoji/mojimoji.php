<?php
/*
Plugin Name: MojiMoji
Plugin URI: http://www.ozguryazilim.com.tr
Description: Extends Wordpress emoji library by using Emoji provided by Emoji One http://emojione.com
Version: 0.1.0
Author: Onur Küçük
Author URI: http://www.delipenguen.net
License: Plugin code GPL2 and Emoji One CC By 4.0
*/

/*  Copyright (C) 2016, Onur Küçük

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

require_once(dirname(__FILE__) . '/includes/emoji_mapping.php');
// not used currently but keep it as a reference
// require_once(dirname(__FILE__) . '/lib/emojione/autoload.php');

global $wpsmiliestrans;
$wpsmiliestrans = mojimoji_all_emojies();

function mojimoji_register_globals() {
  global $mojimoji_all_emojies, $mojimoji_category_map, $mojimoji_categories;

  $mojimoji_all_emojies = mojimoji_all_emojies();
  $mojimoji_category_map = mojimoji_category_map();
  $mojimoji_categories = mojimoji_categories();
}
add_action('init', 'mojimoji_register_globals');

function my_custom_smilies_src($img_src, $img, $siteurl) {
  // return $siteurl . '/wp-content/plugins/mojimoji/emoji/' . $img;
  return $siteurl . '/wp-content/plugins/mojimoji/emoji/' . $img;
}
add_filter('smilies_src', 'my_custom_smilies_src', 1, 10);

function mojimoji_ajax() {
  if (!is_user_logged_in()) {
    wp_die();
  }

  global $mojimoji_all_emojies, $mojimoji_category_map, $mojimoji_categories;
  $output = '';
  $category = $_POST['category'];

  foreach ($mojimoji_category_map[$category] as $smiley) {
    $output .= '<span class="smiley_smiley" onclick="smiley_insert(\''.$smiley.'\')">';
    $output .= convert_smilies($smiley);
    $output .= '</span>';
  }

  echo $output;
  wp_die();
}
add_action('wp_ajax_mojimoji', 'mojimoji_ajax');


?>
