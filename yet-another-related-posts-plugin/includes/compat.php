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
