<?php
/**
 * Reports class.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Reports
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function exactmetrics_reports_page_body_class( $classes ) {
	if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] === 'exactmetrics_reports' ) {
		$classes .= ' exactmetrics-reporting-page ';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'exactmetrics_reports_page_body_class' );

/**
 * Callback for getting all of the reports tabs for ExactMetrics.
 *
 * @since 6.0.0
 * @access public
 *
 * @return array Array of tab information.
 */
function exactmetrics_get_reports() {
	/**
	 * Developer Alert:
	 *
	 * Per the README, this is considered an internal hook and should
	 * not be used by other developers. This hook's behavior may be modified
	 * or the hook may be removed at any time, without warning.
	 */
	$reports =  apply_filters( 'exactmetrics_get_reports', array() );
	return $reports;
}

/**
 * Callback to output the ExactMetrics reports page.
 *
 * @since 6.0.0
 * @access public
 *
 * @return void
 */
function exactmetrics_reports_page() {
	/**
	 * Developer Alert:
	 *
	 * Per the README, this is considered an internal hook and should
	 * not be used by other developers. This hook's behavior may be modified
	 * or the hook may be removed at any time, without warning.
	 */
	do_action( 'exactmetrics_head' );
	echo exactmetrics_ublock_notice();
	exactmetrics_settings_error_page( 'exactmetrics-reports');
	exactmetrics_settings_inline_js();
}
