<?php
/**
 * Functions to ensure compatibility with WordPress 3.7 or higher.
 *
 * @package yarpp
 *
 * @since 5.19
 */

if ( ! function_exists( 'wp_get_additional_image_sizes' ) ) {
	/**
	 * Fetch additional image sizes.
	 *
	 * @since 5.19
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @return array Additional images sizes.
	 */
	function wp_get_additional_image_sizes() {
		global $_wp_additional_image_sizes;
		if ( ! $_wp_additional_image_sizes ) {
			$_wp_additional_image_sizes = array();// phpcs:ignore.
		}
		return $_wp_additional_image_sizes;
	}
}

if ( ! function_exists( 'wp_parse_list' ) ) {
	/**
	 * Converts a comma- or space-separated list of scalar values to an array.
	 *
	 * @since 5.1.0
	 *
	 * @param array|string $list List of values.
	 * @return array Array of values.
	 */
	function wp_parse_list( $list ) {
		if ( ! is_array( $list ) ) {
			return preg_split( '/[\s,]+/', $list, -1, PREG_SPLIT_NO_EMPTY );
		}
		return $list;
	}
}
