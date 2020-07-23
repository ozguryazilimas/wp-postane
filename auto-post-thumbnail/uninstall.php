<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$apt_ds = get_option( 'wapt_delete_settings' );

if ( ! $apt_ds ) {
	return;
}

// remove plugin options
global $wpdb;

$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wapt_%';" );
