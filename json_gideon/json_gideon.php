<?php
/*
Plugin Name: JSON Gideon
Plugin URI: https://www.ozguryazilim.com.tr
Description: Disables JSON API for anonymous and non-admin users.
Version: 0.5.1
Author: Onur Küçük
Author URI: https://www.delipenguen.net
License: GPL2
*/

/*  Copyright (C) 2020, Onur Küçük

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


// allows JSON API only for admins, see output of https://your_wordpress_site/wp-json/wp/v2/users
function json_gideon_disable_json_api_for_non_admin($result) {
  if (!empty($result)) {
    return $result;
  }

  if (!is_user_logged_in()) {
    return new WP_Error('rest_not_logged_in', 'You are not currently logged in.', array('status' => 401));
  }

  if (!current_user_can('administrator')) {
    # return new WP_Error('rest_not_admin', 'You are not an administrator.', array('status' => 401));
    return new WP_Error('rest_not_logged_in', 'You are not currently logged in.', array('status' => 401));
  }

  return $result;
}
add_filter('rest_authentication_errors', 'json_gideon_disable_json_api_for_non_admin');


?>
