<?php
/*
Plugin Name: Fluxophy
Plugin URI: http://www.ozguryazilim.com.tr
Description: This plugin fetches external JSON sources and displays them in a widget. For example, you can show facebook feeds with URL http://www.facebook.com/feeds/page.php?format=json&id=YOUR_ID
Version: 2.0.0
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

define('FLUXOPHY_DISPLAY_COUNT', 10);
require_once(dirname(__FILE__) . '/includes/class-wp-fp-widget.php');
$wp_fp = new WP_Fluxophy_Widget();

global $wpdb;

add_action('widgets_init', 'fluxophy_init');
add_action('init', 'fluxophy_add_css');


function fluxophy_add_css() {
  wp_enqueue_style('fluxophy', get_option('siteurl') . '/wp-content/plugins/fluxophy/css/fluxophy.css');
}

function fluxophy_init() {
  load_plugin_textdomain('fluxophy', false, basename(dirname(__FILE__)) . '/languages' );
  register_widget('WP_Fluxophy_Widget');
}

?>
