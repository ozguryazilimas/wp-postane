<?php
/**
 * SeedProd Tracking for 404 and Coming Soon.
 *
 * @since 7.3.0
 *
 * @package ExactMetrics
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Disable SeedProd settings (done in seedprod)
// 2. Output tracking code, if settings is not set to use wp_head() (done in seedprod and below)
// 3. Disable ga_tracking in their setting (done in seedprod)
function exactmetrics_seedprod_tracking( $settings ) {
    require_once plugin_dir_path( EXACTMETRICS_PLUGIN_FILE ) . 'includes/frontend/class-tracking-abstract.php';


    do_action( 'exactmetrics_tracking_before_analytics' );
    do_action( 'exactmetrics_tracking_before', 'analytics' );

    require_once plugin_dir_path( EXACTMETRICS_PLUGIN_FILE ) . 'includes/frontend/tracking/class-tracking-analytics.php';
    $tracking = new ExactMetrics_Tracking_Analytics();
    echo $tracking->frontend_output();

    do_action( 'exactmetrics_tracking_after_analytics' );
    do_action( 'exactmetrics_tracking_after', 'analytics' );

    $track_user    = exactmetrics_track_user();

    if ( $track_user ) {
        require_once plugin_dir_path( EXACTMETRICS_PLUGIN_FILE ) . 'includes/frontend/events/class-analytics-events.php';
        new ExactMetrics_Analytics_Events();

        // Let's run form tracking if we find it
        if ( function_exists( 'exactmetrics_forms_output_after_script' ) ) {
            exactmetrics_forms_output_after_script( array() );
        }
    }
}
add_action( 'seedprod_exactmetrics_output_tracking', 'exactmetrics_seedprod_tracking', 6, 1 );