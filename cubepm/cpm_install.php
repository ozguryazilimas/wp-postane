<?php
/**
 * CubePM Installation.
 * Handles the installation of CubePM.
 * 
 * @package cubepm
 */

/**
 * Installs CubePM
 *
 * @global object $wpdb
 * @return null
 */
function cpm_install() {

	// set default values
	add_option('cpm_permission_newtopic', array('administrator', 'editor', 'author', 'contributor', 'subscriber'));
	add_option('cpm_email_enabled', 1);
	add_option('cpm_email_from_name', '%blog_name%');
	add_option('cpm_email_from_email', '%blog_email%');
	add_option('cpm_email_subject', __('New PM from %sender%: "%subject%"' ,'cubepm'));
	add_option('cpm_email_body', __('Hello %recipient%!', 'cubepm') . "\n\n" . __('%sender% has sent you a private message entitled "%subject%" on %blog_name%.', 'cubepm') . "\n\n" . __('To read it now, go to the following address', 'cubepm') . ':' . "\n" . '<a href="' . __('%pm_link%', 'cubepm') . '">' . __('%pm_link%', 'cubepm') . '</a>' . "\n\n" . __('Message contents', 'cubepm') . ':' . "\n\n" . __('%message%', 'cubepm'));
	add_option('cpm_ver', CPM_VER);

	// setup msg database
	global $wpdb;
 	if($wpdb->get_var("SHOW TABLES LIKE '".CPM_DB_MSG."'") != CPM_DB_MSG) {
		$sql = "CREATE TABLE " . CPM_DB_MSG . " (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
			  thread_id bigint(20) NOT NULL,
			  sender_id bigint(20) NOT NULL,
			  message TEXT NOT NULL,
			  subject TEXT NULL,
			  timestamp bigint(20) NOT NULL,
			  UNIQUE KEY id (id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("cpm_db_version", 1.0);
	}
	
	// setup meta database
 	if($wpdb->get_var("SHOW TABLES LIKE '".CPM_DB_META."'") != CPM_DB_META) {
		$sql = "CREATE TABLE " . CPM_DB_META . " (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
			  thread_id bigint(20) NOT NULL,
			  user_id bigint(20) NOT NULL,
			  opened BOOL,
			  subscribe BOOL,
			  UNIQUE KEY id (id)
			);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("cpm_db_version", 1.0);
	}

}

?>