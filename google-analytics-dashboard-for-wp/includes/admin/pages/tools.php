<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ExactMetrics settings export.
 *
 * @since 6.0.0
 * @access public
 *
 * @return void
 */
function exactmetrics_process_export_settings() {
	if ( ! isset( $_POST['exactmetrics_action'] ) || empty( $_POST['exactmetrics_action'] ) ) {
		return;
	}

	if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
		return;
	}

	if ( 'exactmetrics_export_settings' !== $_POST['exactmetrics_action'] ) {
		return;
	}

	if ( empty( $_POST['exactmetrics_export_settings'] ) || ! wp_verify_nonce( $_POST['exactmetrics_export_settings'], 'mi-admin-nonce' ) ) {
		return;
	}

	$settings = exactmetrics_export_settings();
	ignore_user_abort( true );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=exactmetrics-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo $settings;
	exit;
}

add_action( 'admin_init', 'exactmetrics_process_export_settings' );
