<?php
/**
 * Gutenberg-specific scripts.
 */

/**
 * Gutenberg editor assets.
 */
function exactmetrics_gutenberg_editor_assets() {
	global $wp_version;

	if ( version_compare( $wp_version, '5.4', '<' ) ) {
		return;
	}

	$plugins_js_path    = '/assets/gutenberg/js/editor.min.js';
	$plugins_style_path = '/assets/gutenberg/css/editor.css';

	$js_dependencies = array(
		'wp-plugins',
		'wp-element',
		'wp-edit-post',
		'wp-i18n',
		'wp-api-request',
		'wp-data',
		'wp-hooks',
		'wp-plugins',
		'wp-components',
		'wp-blocks',
		'wp-editor',
		'wp-compose',
	);

	// Enqueue our plugin JavaScript.
	wp_enqueue_script(
		'exactmetrics-gutenberg-editor-js',
		plugins_url( $plugins_js_path, EXACTMETRICS_PLUGIN_FILE ),
		$js_dependencies,
		exactmetrics_get_asset_version(),
		true
	);

	// Enqueue our plugin JavaScript.
	wp_enqueue_style(
		'exactmetrics-gutenberg-editor-css',
		plugins_url( $plugins_style_path, EXACTMETRICS_PLUGIN_FILE ),
		array(),
		exactmetrics_get_asset_version()
	);

	// Localize script for sidebar plugins.
	wp_localize_script(
		'exactmetrics-gutenberg-editor-js',
		'exactmetrics_gutenberg_tool_vars',
		array(
			'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
			'nonce'                        => wp_create_nonce( 'exactmetrics_gutenberg_headline_nonce' ),
			'allowed_post_types'           => apply_filters( 'exactmetrics_headline_analyzer_post_types', array( 'post' ) ),
			'current_post_type'            => exactmetrics_get_current_post_type(),
			'translations'                 => wp_get_jed_locale_data( exactmetrics_is_pro_version() ? 'exactmetrics-premium' : 'google-analytics-dashboard-for-wp' ),
			'is_headline_analyzer_enabled' => apply_filters( 'exactmetrics_headline_analyzer_enabled', true ) && 'true' !== exactmetrics_get_option( 'disable_headline_analyzer' ),
			'reports_url'                  => add_query_arg( 'page', 'exactmetrics_reports', admin_url( 'admin.php' ) ),
		)
	);
}

add_action( 'enqueue_block_editor_assets', 'exactmetrics_gutenberg_editor_assets' );
