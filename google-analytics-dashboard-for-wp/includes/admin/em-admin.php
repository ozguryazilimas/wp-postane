<?php
/**
 * Add menu items in admin bar
 *
 * @since 6.6.0
 *
 * @param object $wp_admin_bar WP_Admin_Bar instance, passed by reference
 */
function exactmetrics_admin_bar_items( $admin_bar ) {
	if ( ! current_user_can( 'exactmetrics_view_dashboard' ) ) {
		return;
	}

	$admin_bar->add_menu( array(
		'id'     => 'exactmetrics-analyltics-reports',
		'parent' => 'wp-logo',
		'group'  => null,
		'title'  => 'ExactMetrics',
		'href'   => add_query_arg( 'page', 'exactmetrics_reports', admin_url( 'admin.php' ) ),
		'meta'   => array(
			'title' => 'ExactMetrics',
		),
	) );
}
add_action( 'admin_bar_menu', 'exactmetrics_admin_bar_items', 500 );
