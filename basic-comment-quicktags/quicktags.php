<?php
/*
  Plugin Name: Basic Comment Quicktags
  Plugin URI: http://halfelf.org/plugins/basic-comment-quicktags/
  Description: Displays a bold, italic, add link and quote button on top of the comment form
  Version: 2.2
  Author: Mika "Ipstenu" Epstein
  Author URI: http://ipstenu.org

  Original author: Marc Tönsing -- http://www.marctv.de/blog/2010/08/25/marctv-wordpress-plugins/
  Copyright 2012 Mika Epstein

    This file is part of Basic Comment Quicktags, a plugin for WordPress.

    Basic Comment Quicktags is free software: you can redistribute it and/or 
	modify it under the terms of the GNU General Public License as published 
	by the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    Basic Comment Quicktags is distributed in the hope that it will be
    useful, but WITHOUT ANY WARRANTY; without even the implied warranty
    of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/

global $wp_version;
$exit_msg_ver = 'This plugin requires WordPress 3.4';
if (version_compare($wp_version,"3.4","<")) { exit($exit_msg_ver); }

function ippy_bcq_add_scripts() {

$options = get_option('ippy_bcq_options');
$valuebb = $options['bbpress'];
$valueco = $options['comments'];
$ippy_bcq_bbp_fancy = get_option( '_bbp_use_wp_editor' );

  if ( function_exists('is_bbpress') ) {
          if ( is_bbpress()  && ( $valuebb != '0') && !is_null($valuebb) && ($ippy_bcq_bbp_fancy == '0') && !is_admin() ) {
            wp_enqueue_script("bcq_quicktags", plugin_dir_url(__FILE__) . "quicktags.js", array("quicktags","jquery"), "1.8", 1);
            wp_enqueue_style("bcq_quicktags", plugin_dir_url(__FILE__) . "quicktags.css", false, "2.0");
        }
  }
  if ( ( $valueco != '0') && !is_null($valueco) && !is_admin() && is_singular() && comments_open() ) {
            wp_enqueue_script("bcq_quicktags", plugin_dir_url(__FILE__) . "quicktags.js", array("quicktags","jquery"), "1.8", 1);
            wp_enqueue_style("bcq_quicktags", plugin_dir_url(__FILE__) . "quicktags.css", false, "2.0");
  }
}

if( !is_admin() ) {
	add_action('wp_print_styles', 'ippy_bcq_add_scripts');
}

// donate link on manage plugin page
add_filter('plugin_row_meta', 'ippy_bcq_donate_link', 10, 2);
function ippy_bcq_donate_link($links, $file) {
        if ($file == plugin_basename(__FILE__)) {
                $donate_link = '<a href="https://www.wepay.com/donations/halfelf-wp">Donate</a>';
                $links[] = $donate_link;
        }
        return $links;
}

// Register and define the settings
add_action('admin_init', 'ippy_bcq_admin_init');

function ippy_bcq_admin_init(){

	register_setting(
		'discussion',               // settings page
		'ippy_bcq_options',         // option name
		'ippy_bcq_validate_options' // validation callback
	);
	
	add_settings_field(
		'ippy_bcq_bbpress',         // id
		'Quicktags',                // setting title
		'ippy_bcq_setting_input',   // display callback
		'discussion',               // settings page
		'default'                   // settings section
	);
}

register_activation_hook( __FILE__, 'ippy_bcq_activate' );

function ippy_bcq_activate() {
	$options = get_option( 'ippy_bcq_options' );
	$options['comments'] = '0';
	$options['bbpress'] = '0';
	update_option('ippy_bcq_options', $options);
}

// Display and fill the form field
function ippy_bcq_setting_input() {
	// get option value from the database
	$options = get_option( 'ippy_bcq_options' );
	$valuebb = $options['bbpress'];
	$valueco = $options['comments'];
	
	$ippy_bcq_bbp_fancy = get_option( '_bbp_use_wp_editor' );
	
	// echo the field
	?>
<p><?php 
	if ( function_exists('is_bbpress') && ($ippy_bcq_bbp_fancy == '0') ) { ?>
<input id='bbpress' name='ippy_bcq_options[bbpress]' type='checkbox' value='1' <?php if ( ( $valuebb != '0') && !is_null($valuebb) ) { echo ' checked="checked"'; } ?> /> Activate Quicktags for bbPress<br /> <?php } 
	else { ?>
	<input type='hidden' id='bbpress' name='ippy_bcq_options[bbpress]' value='0'> <?php } 
?>
<input id='comments' name='ippy_bcq_options[comments]' type='checkbox' value='1' <?php if ( ( $valueco != '0') && !is_null($valueco) ) { echo ' checked="checked"'; } ?> /> Activate Quicktags for comments
	<?php
}

// Validate user input
function ippy_bcq_validate_options( $input ) {
	$valid = array();
	$valid['comments'] = $input['comments'];
	$valid['bbpress'] = $input['bbpress'];
	unset( $input );
	return $valid;
}