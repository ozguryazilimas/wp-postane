<?php
/**
 * Option functions.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Options
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function exactmetrics_get_options() {
	$settings = array();
	$option_name = exactmetrics_get_option_name();
	//$settings             = get_site_option( $option_name );
	//$use_network_settings = ! empty( $use_network_settings['use_network_settings'] ) ? true : false;
	//$is_network           = is_multisite();

	//if ( $is_network && $use_network_settings ) {
	//    return $settings;
	//} else if ( $is_network ) {
		$settings = get_option( $option_name );
	//} else {
	//    return $settings;
	//}
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		$settings = array();
	}
	return $settings;
}

/**
 * Helper method for getting a setting's value. Falls back to the default
 * setting value if none exists in the options table.
 *
 * @since 6.0.0
 * @access public
 *
 * @param string $key   The setting key to retrieve.
 * @param mixed $default   The default value of the setting key to retrieve.
 * @return string       The value of the setting.
 */
function exactmetrics_get_option( $key = '', $default = false ) {
	global $exactmetrics_settings;
	$value = ! empty( $exactmetrics_settings[ $key ] ) ? $exactmetrics_settings[ $key ] : $default;
	$value = apply_filters( 'exactmetrics_get_option', $value, $key, $default );
	return apply_filters( 'exactmetrics_get_option_' . $key, $value, $key, $default );
}

/**
 * Helper method for getting the UA string.
 *
 * @since 6.0.0
 * @access public
 *
 * @return string The UA to use.
 */
function exactmetrics_get_ua() {
	// Allow short circuiting (for staging sites)
	if ( defined( 'EXACTMETRICS_DISABLE_TRACKING' ) && EXACTMETRICS_DISABLE_TRACKING ) {
		return '';
	}

	// Try getting it from the auth UA
	$ua = ExactMetrics()->auth->get_ua();

	// If that didn't work, try the manual UA at the site level
	if ( empty( $ua ) ) {
		$ua = ExactMetrics()->auth->get_manual_ua();
		// If that didn't work try getting it from the network
		if ( empty( $ua ) ) {
			$ua = exactmetrics_get_network_ua();
			// If that didn't work, try getting it from the overall constant. If it's not there, leave it blank
			if ( empty( $ua ) ) {
				$ua = defined( 'EXACTMETRICS_GA_UA' ) && EXACTMETRICS_GA_UA ? exactmetrics_is_valid_ua( EXACTMETRICS_GA_UA ) : '';
			}
		}
	}

	// Feed through the filter
	$pre_filter = $ua;
	$ua = apply_filters( 'exactmetrics_get_ua', $ua );

	// Only run through exactmetrics_is_valid_ua if it's different than pre-filter
	return $pre_filter === $ua ? $ua : exactmetrics_is_valid_ua( $ua );
}

/**
 * Helper method for getting the network UA string.
 *
 * @since 6.0.0
 * @access public
 *
 * @return string The UA to use.
 */
function exactmetrics_get_network_ua() {
	if ( ! is_multisite() ) {
		return '';
	}

	// First try network auth UA
	$ua = ExactMetrics()->auth->get_network_ua();
	if ( ! empty( $ua ) ) {
		return $ua;
	}

	// Then try manual network UA
	$ua = ExactMetrics()->auth->get_network_manual_ua();
	if ( ! empty( $ua ) ) {
		return $ua;
	}

	// See if the constant is defined
	if ( defined( 'EXACTMETRICS_MS_GA_UA' ) && exactmetrics_is_valid_ua( EXACTMETRICS_MS_GA_UA ) ) {
		return EXACTMETRICS_MS_GA_UA;
	}

	return '';
}

/**
 * Helper method for getting the UA string that's output on the frontend.
 *
 * @since 6.0.0
 * @access public
 *
 * @param array $args Allow calling functions to give args to use in future applications.
 * @return string The UA to use on frontend.
 */
function exactmetrics_get_ua_to_output( $args = array() ) {
	$ua = exactmetrics_get_ua();
	$ua = apply_filters( 'exactmetrics_get_ua_to_output', $ua, $args );
	return exactmetrics_is_valid_ua( $ua );
}

/**
 * Helper method for updating a setting's value.
 *
 * @since 6.0.0
 * @access public
 *
 * @param string $key   The setting key.
 * @param string $value The value to set for the key.
 * @return boolean True if updated, false if not.
 */
function exactmetrics_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = exactmetrics_delete_option( $key );
		return $remove_option;
	}

	$option_name = exactmetrics_get_option_name();

	// First let's grab the current settings

	// if on network panel or if on single site using network settings
	//$settings              = get_site_option( $option_name );
	//$use_network_settings  = ! empty( $use_network_settings['use_network_settings'] ) ? true : false;
	//$is_network            = is_multisite();
	//$update_network_option = true;
	//if ( ! is_network_admin() && ! ( $is_network && $use_network_settings ) ) {
	   $settings = get_option( $option_name );
	//   $update_network_option = false;
	//}

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	// Let's let devs alter that value coming in
	$value = apply_filters( 'exactmetrics_update_option', $value, $key );

	// Next let's try to update the value
	$settings[ $key ] = $value;
	$did_update = false;
	//if ( $update_network_option ) {
	//    $did_update = update_site_option( $option_name, $settings );
	//} else {
		$did_update = update_option( $option_name, $settings );
	//}

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $exactmetrics_settings;
		$exactmetrics_settings[ $key ] = $value;
	}

	return $did_update;
}

 /**
 * Helper method for deleting a setting's value.
 *
 * @since 6.0.0
 * @access public
 *
 * @param string $key   The setting key.
 * @return boolean True if removed, false if not.
 */
function exactmetrics_delete_option( $key = '' ) {
	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	$option_name = exactmetrics_get_option_name();

	// First let's grab the current settings

	// if on network panel or if on single site using network settings
	//$settings              = get_site_option( $option_name );
	//$use_network_settings  = ! empty( $use_network_settings['use_network_settings'] ) ? true : false;
	//$is_network            = is_multisite();
	//$update_network_option = true;
	//if ( ! is_network_admin() && ! ( $is_network && $use_network_settings ) ) {
	   $settings = get_option( $option_name );
	//   $update_network_option = false;
	//}

	// Next let's try to remove the key
	if( isset( $settings[ $key ] ) ) {
		unset( $settings[ $key ] );
	}

	$did_update = false;
	//if ( $update_network_option ) {
	//    $did_update = update_site_option( 'exactmetrics_settings', $settings );
	//} else {
		$did_update = update_option( $option_name, $settings );
	//}

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $exactmetrics_settings;
		$exactmetrics_settings = $settings;
	}

	return $did_update;
}

 /**
 * Helper method for deleting multiple settings value.
 *
 * @since 6.0.0
 * @access public
 *
 * @param string $key   The setting key.
 * @return boolean True if removed, false if not.
 */
function exactmetrics_delete_options( $keys = array() ) {
	// If no keys, exit
	if ( empty( $keys ) || ! is_array( $keys ) ){
		return false;
	}

	$option_name = exactmetrics_get_option_name();

	// First let's grab the current settings

	// if on network panel or if on single site using network settings
	//$settings              = get_site_option( $option_name );
	//$use_network_settings  = ! empty( $use_network_settings['use_network_settings'] ) ? true : false;
	//$is_network            = is_multisite();
	//$update_network_option = true;
	//if ( ! is_network_admin() && ! ( $is_network && $use_network_settings ) ) {
	   $settings = get_option( $option_name );
	//   $update_network_option = false;
	//}

	// Next let's try to remove the keys
	foreach ( $keys as $key ) {
		if( isset( $settings[ $key ] ) ) {
			unset( $settings[ $key ] );
		}
	}

	$did_update = false;
	//if ( $update_network_option ) {
	//    $did_update = update_site_option( 'exactmetrics_settings', $settings );
	//} else {
		$did_update = update_option( $option_name, $settings );
	//}

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $exactmetrics_settings;
		$exactmetrics_settings = $settings;
	}

	return $did_update;
}

/**
 * Is valid ua code.
 *
 * @access public
 * @since 6.0.0
 *
 * @param string $ua_code UA code to check validity for.
 *
 * @return string|false Return cleaned ua string if valid, else returns false.
 */
function exactmetrics_is_valid_ua( $ua_code = '' ) {
	$ua_code = (string) $ua_code; // Rare case, but let's make sure it never happens.
	$ua_code = trim( $ua_code );

	if ( empty( $ua_code ) ) {
		return '';
	}

	// Replace all type of dashes (n-dash, m-dash, minus) with normal dashes.
	$ua_code = str_replace( array( '–', '—', '−' ), '-', $ua_code );

	if ( preg_match( "/^(UA|YT|MO)-\d{4,}-\d+$/", strval( $ua_code ) ) ) {
		return $ua_code;
	} else {
		return '';
	}
}

/**
 * Helper method for getting the license information.
 *
 * @since 6.0.0
 * @access public
 *
 * @param string $key   The setting key to retrieve.
 * @param mixed $default_value   The default value of the setting key to retrieve.
 * @return string       The value of the setting.
 */
function exactmetrics_get_license() {
	$license  = ExactMetrics()->license->get_site_license();
	$license  = $license ? $license : ExactMetrics()->license->get_network_license();
	$default  = ExactMetrics()->license->get_default_license_key();
	if ( empty( $license ) && ! empty( $default ) ) {
		$license        = array();
		$license['key'] = ExactMetrics()->license->get_default_license_key();
	}
	return $license;
}

/**
 * Helper method for getting the license key.
 *
 * @since 6.0.0
 * @access public
 *
 * @param string $key   The setting key to retrieve.
 * @param mixed $default_value   The default value of the setting key to retrieve.
 * @return string       The value of the setting.
 */
function exactmetrics_get_license_key() {
	if ( exactmetrics_is_pro_version() ) {
		return ExactMetrics()->license->get_license_key();
	}
	return '';
}

function exactmetrics_get_option_name() {
	//if ( exactmetrics_is_pro_version() ) {
		return 'exactmetrics_settings';
	//} else {
	//	return 'exactmetrics_settings';
	//}
}

function exactmetrics_export_settings() {
	$settings = exactmetrics_get_options();
	$exclude  = array(
				'analytics_profile',
				'analytics_profile_code',
				'analytics_profile_name',
				'oauth_version',
				'cron_last_run',
				'exactmetrics_oauth_status',
	);

	foreach ( $exclude as $e ) {
		if ( ! empty( $settings[ $e ] ) ) {
			unset( $settings[ $e ] );
		}
	}
	return wp_json_encode( $settings );
}

/**
 * Always return 'analytics' when grabbing the tracking mode.
 *
 * @param string $value The value to override.
 *
 * @return string
 */
function exactmetrics_force_tracking_mode( $value ) {
	return 'analytics';
}
add_filter( 'exactmetrics_get_option_tracking_mode', 'exactmetrics_force_tracking_mode' );

/**
 * Always return 'js' when grabbing the events mode.
 *
 * @param string $value The value to override.
 *
 * @return string
 */
function exactmetrics_force_events_mode( $value ) {
	return 'js';
}
add_filter( 'exactmetrics_get_option_events_mode', 'exactmetrics_force_events_mode' );
