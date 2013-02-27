<?php
/**
 * CubePM Admin Functions.
 * Handles admin pages.
 * @package cubepm
 */

/**
 * Hook for setting up admin pages
 * 
 * @return null
 */
function cpm_admin() {
	add_menu_page('CubePM', 'CubePM', 'manage_options', 'cpm_admin_settings', 'cpm_admin_settings');
	add_submenu_page('cpm_admin_settings', 'CubePM - ' .__('Settings','cp'), __('Settings','cp'), 'manage_options', 'cpm_admin_settings', 'cpm_admin_settings');
	add_submenu_page('cpm_admin_settings', 'CubePM - ' .__('Setup','cp'), __('Setup','cp'), 'manage_options', 'cpm_admin_setup', 'cpm_admin_setup');
}

/** Include admin pages */
require_once('cpm_admin_settings.php');
require_once('cpm_admin_setup.php');

/** Hook for admin pages */
add_action('admin_menu', 'cpm_admin');