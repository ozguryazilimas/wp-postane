<?php
/*
Plugin Name: Plugin Update Hider
Plugin URI: http://www.squarepenguin.com/wordpress/plugin_update_hider/
Description: Allows you to hide update notifications for plugins that you do not want to update.
Author: Jason Newman
Version: 0.0.4
Author URI: http://www.squarepenguin.com/wordpress
*/

/***********************************
  Wordpress Hooks/Actions/Filters
***********************************/
if(defined('jn_puh_loaded')) {
        return;
}
define('jn_puh_loaded', 1);

add_action('init','jn_puh_get');
add_action('init','jn_puh_addFilters');

add_filter("plugin_row_meta", 'jn_puh_pluginLinks', 10, 2);
add_filter('site_transient_update_plugins', 'jn_puh_blockUpdateNotifications');
//add_filter('site_transient_update_themes', 'jn_puh_blockUpdateNotifications');


function jn_puh_addFilters() {
        if(!current_user_can('update_plugins')) {
                return;
        }

        $plugins = get_site_transient('update_plugins');
        $to_block = get_option('jn_puh_blocked');
	
	if(isset($plugins->response)) {
	        // loop through all of the plugins with updates available and attach the appropriate filter
		foreach($plugins->response as $filename => $plugin) {
                	// check that the version is the version we want to block updates to
                	$s = 'after_plugin_row_' . $filename;
                	//in_plugin_update_message-
                	add_action($s, 'jn_puh_blockLink', -1, 1);
		}
        }
	if(isset($plugins->jn_puh)) {
        	foreach($plugins->jn_puh as $filename => $plugin) {
                	// check that the version is the version we want to block updates to
                	$s = 'after_plugin_row_' . $filename;
                	add_action($s, 'jn_puh_unblockLink', 2, 1);
		}
        }
}

function jn_puh_get() {

        if(!current_user_can('update_plugins')) {
                return;
        }

        // see if there are actions to process
        if(!isset($_GET['jn_puh']) || !isset($_GET['_wpnonce'])) {
                return;
        }

        if(!wp_verify_nonce($_GET['_wpnonce'], 'jn_puh')) {
                return;
        }

        $blocked = get_option('jn_puh_blocked');
        $plugins = get_site_transient('update_plugins');

        // block action
        if(isset($_GET['block']) && isset($plugins->response) && isset($plugins->response[$_GET['block']])) {
                $p = $plugins->response[$_GET['block']];
                $blocked[$_GET['block']] = array('slug' => $p->slug, 'new_version' => $p->new_version);
        }

        if(isset($_GET['unblock'])) {
                unset($blocked[$_GET['unblock']]);

        }

        update_option('jn_puh_blocked', $blocked);

}

function jn_puh_blockUpdateNotifications($plugins) {

	if(!isset($plugins->response) || count($plugins->response) == 0) {
		return $plugins;
	}

        $to_block = (array)get_option('jn_puh_blocked');

        foreach($to_block as $filename => $plugin) {

                if(isset($plugins->response[$filename])
                        && $plugins->response[$filename]->new_version == $plugin['new_version']) {

                        $plugins->jn_puh[$filename] = $plugins->response[$filename];
                        unset($plugins->response[$filename]);
                }
        }
        return $plugins;
}

function jn_puh_unblockLink($filename) {
        jn_puh_linkStart();
        echo 'Update notifications for this plugin are blocked. <a href="plugins.php?_wpnonce=' . wp_create_nonce('jn_puh') . '&jn_puh&unblock=' . $filename . '">Unblock Now</a>.</div></td></tr>';
}

function jn_puh_blockLink($filename) {
        jn_puh_linkStart();
        echo ' <a href="plugins.php?_wpnonce=' . wp_create_nonce('jn_puh') . '&jn_puh&block=' . $filename . '">Block update notifications for this plugin</a>.</div></td></tr>';
}

function jn_puh_linkStart() {

        // wp_plugin_update_row
        // wp-admin/includes/update.php

        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
        echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';
}

function jn_puh_pluginLinks( $links, $file ) {
        $plugin = plugin_basename(__FILE__);
	if($file == $plugin) {
		$links[] = '<a target="_BLANK" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LC5UR6667DLXU">Donate</a>';
        }
        return $links;
}

if(!function_exists('printr')) {
        function printr($txt) {
                echo '<pre>'; print_r($txt); echo '</pre>';
        }
}

?>
