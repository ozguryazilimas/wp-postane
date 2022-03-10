<?php
/*
Plugin Name: Bududu
Plugin URI: http://www.ozguryazilim.com.tr
Description: Shows social media buttons under posts. Note that this plugin does not track your site or users but the share buttons of the services shown may behave differently. Please refer to related service privacy and terms documents.
Version: 0.7.0
Author: Onur Küçük
Author URI: http://www.delipenguen.net
License: GPL2
 */

/*  Copyright (C) 2018, Onur Küçük

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

require_once(dirname(__FILE__) . '/includes/bududu_functions.php');

function bududu_add_assets() {
  wp_register_script('bududu', plugins_url('js/bududu.js', __FILE__), array(), '0.6.0');
  wp_enqueue_script('bududu');
  wp_register_style('bududu_fontawesome', plugins_url('fontawesome/fontawesome.css', __FILE__), array(), '5.2.0');
  wp_enqueue_style('bududu_fontawesome');
  wp_register_style('bududu_fontawesome_brands', plugins_url('fontawesome/brands.css', __FILE__), array(), '5.2.0');
  wp_enqueue_style('bududu_fontawesome_brands');
  wp_register_style('bududu', plugins_url('css/bududu.css', __FILE__), array(), '0.6.0');
  wp_enqueue_style('bududu');
}
add_action('wp_enqueue_scripts', 'bududu_add_assets');

add_filter('the_content', 'bududu_content_filter', 20);

add_action('wp_head', 'bududu_opengraph_tags');
# add_action('wp_footer', 'bududu_footer');

?>
