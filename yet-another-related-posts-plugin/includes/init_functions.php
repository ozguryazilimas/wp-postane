<?php

function yarpp_init() {
	global $yarpp;
	$yarpp = new YARPP;
}

function yarpp_plugin_activate($network_wide) {
    update_option('yarpp_activated', true);
}

function yarpp_set_option($options, $value = null) {
	global $yarpp;
	$yarpp->set_option($options, $value);
}

function yarpp_get_option($option = null) {
	global $yarpp;
	return $yarpp->get_option($option);
}
/**
 * Given a minified path, and a non-minified path, will return
 * a minified or non-minified file URL based on whether SCRIPT_DEBUG is set true or not.
 *
 * @param string $minified_path     minified path.
 * @param string $non_minified_path non-minified path.
 * @return string The URL to the file.
 */
function yarpp_get_file_url_for_environment( $minified_path, $non_minified_path ) {
	$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	if ( true === $script_debug ) {
		$path = plugins_url( $non_minified_path, YARPP_MAIN_FILE );
	} elseif ( false === $script_debug ) {
		$path = plugins_url( $minified_path, YARPP_MAIN_FILE );
	} else {
		// This should work in any case.
		$path = plugins_url( $non_minified_path, YARPP_MAIN_FILE );
	}
	return $path;
}
