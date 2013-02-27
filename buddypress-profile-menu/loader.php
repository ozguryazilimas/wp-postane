<?php 
/**
Plugin Name: BP Menus
Plugin URI: http://buddypress.org
Description: Adds BuddyPress Menus to WordPress admin. 
Version: 2.0.3
Author: modemlooper
Author URI: http://buddypress.org/community/members/modemlooper
License:GPL2
**/

/*
 * Make sure BuddyPress is loaded before we do anything.
 */
if ( !function_exists( 'bp_core_install' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		require_once ( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
	} else {
		add_action( 'admin_notices', 'bp_profile_menu_install_buddypress_notice' );
		return;
	}
}


function bp_profile_menu_install_buddypress_notice() {
	echo '<div id="message" class="error fade"><p style="line-height: 150%">';
	_e('<strong>BP Menus</strong></a> requires the BuddyPress plugin to work. Please <a href="http://buddypress.org/download">install BuddyPress</a> first, or <a href="plugins.php">deactivate BP Menus</a>.');
	echo '</p></div>';
}

function bp_profile_menu_init() {
	require( dirname( __FILE__ ) . '/bp-menu-router.php' );
	require( dirname( __FILE__ ) . '/bp-menu-meta-box.php' );
}
add_action( 'bp_include', 'bp_profile_menu_init' );

 
function bp_menu_item_script() {  
    // Register the script like this for a plugin:  
    wp_register_script( 'my-buddypress-links_menus', plugins_url( '/bp-menus.js', __FILE__ ), array( 'jquery' ) );  

     // For either a plugin or a theme, you can then enqueue the script:  
    wp_enqueue_script( 'my-buddypress-links_menus' );  
}  
add_action( 'wp_enqueue_scripts', 'bp_menu_item_script' );  



?>