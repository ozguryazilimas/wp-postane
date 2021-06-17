<?php

function yarpp_init() {
	global $yarpp;
	$yarpp = new YARPP();
}

function yarpp_plugin_activate( $network_wide ) {
	update_option( 'yarpp_activated', true );
}

function yarpp_set_option( $options, $value = null ) {
	global $yarpp;
	$yarpp->set_option( $options, $value );
}

function yarpp_get_option( $option = null ) {
	global $yarpp;
	return $yarpp->get_option( $option );
}
/**
 * Get user selected thumbnail size.
 *
 * @param string $option option name.
 * @param string $default_option default thumbnail size name.
 * @return string name of thumbnail.
 */
function yarpp_get_option_thumbnail( $option = null, $default_option = 'thumbnail' ) {
	global $add_image_size_by_yarpp;
	$get_template            = yarpp_get_option( 'template' );
	$choice                  = false === $get_template ? 'builtin' : ( 'thumbnails' === $get_template ? 'thumbnails' : 'custom' );
	$user_selected_thumbnail = yarpp_get_option( $option );
	// If yarpp-thumbnail is added by other than yarpp plugin then default selection will be yarpp-thumbnail otherwise thumbnail.
	$default_checked = ( true === $add_image_size_by_yarpp ? 'thumbnail' : 'yarpp-thumbnail' );
	/**
	 * If existing user upgrades to v5.18.1 then continue using YARPP-thumbnail as default option.
	 * If this is a fresh install then YARPP will use "thumbnail" (WordPress default) because this is always available and does not require images to regenerate.
	 * Lastly, fallback to the provided fallback default.
	 */
	if ( empty( $user_selected_thumbnail ) && ( 'thumbnails' === $get_template || 'custom' === $choice ) ) {
		$thumbnail_size = 'yarpp-thumbnail';
	} elseif ( ! empty( $user_selected_thumbnail ) && ( 'thumbnails' === $get_template || 'custom' === $choice ) ) {
		// Check whether user selected thumbnail is still registered.
		if ( false === yarpp_get_image_sizes( $user_selected_thumbnail ) ) {
			$thumbnail_size = 'yarpp-thumbnail';
		} else {
			$thumbnail_size = $user_selected_thumbnail;
		}
	} else {
		$thumbnail_size = $default_checked;
	}
	return $thumbnail_size;
}
/**
 * Get user selected thumbnail dimension.
 *
 * @param string $size thumbnail size.
 * @return array user selected thumbnail dimension.
 */
function yarpp_get_thumbnail_image_dimensions( $size = 'thumbnail_size_display' ) {
	$user_thumbnail_choice = yarpp_get_option_thumbnail( $size );
	$dimensions            = yarpp_get_image_sizes( $user_thumbnail_choice );
	$dimensions['size']    = $user_thumbnail_choice;
	/* Ensure thumbnail dimensions format: */
	$dimensions['width']  = (int) $dimensions['width'];
	$dimensions['height'] = (int) $dimensions['height'];
	return $dimensions;
}
/**
 * Get information about available image sizes.
 *
 * @param string $size thumbnail size.
 * @return string[] An array of image size names.
 */
function yarpp_get_image_sizes( $size = '' ) {
	$wp_additional_image_sizes    = wp_get_additional_image_sizes();
	$sizes                        = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();
	// Create the full array with sizes and crop info.
	foreach ( $get_intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ), true ) ) {
			$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );
		} elseif ( isset( $wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $wp_additional_image_sizes[ $_size ]['width'],
				'height' => $wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}
	// Get only 1 size if found.
	if ( $size ) {
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}
	return $sizes;
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
