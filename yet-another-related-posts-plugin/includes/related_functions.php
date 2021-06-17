<?php
/*
---------------------------------------------------------------------------------------------------------------------
Here are the related_WHATEVER functions, as introduced in 1.1.
Since YARPP 2.1, these functions receive (optionally) one array argument.
----------------------------------------------------------------------------------------------------------------------*/

/**
 * Gets the HTML for displaying related posts.
 *
 * @param array $args see readme.txt installation tab's  "YARPP functions()" section
 * @param int   $reference_ID the post ID to search against. If used from within "the loop", defaults to the
 *                            $current_post
 * @param bool  $echo if false only returns the HTML string
 * @return string HTML output
 */
function yarpp_related( $args = array(), $reference_ID = false, $echo = true ) {
	global $yarpp;

	if ( is_array( $reference_ID ) ) {
		_doing_it_wrong( __FUNCTION__, 'This YARPP function now takes $args first and $reference_ID second.', '3.5' );
		return;
	}

	return $yarpp->display_related( $reference_ID, $args, $echo );
}

/**
 * Whether there are any related posts.
 *
 * @param array $args see readme.txt installation tab's  "YARPP functions()" section
 * @param int   $reference_ID the post ID to search against. If used from within "the loop", defaults to the
 *                            $current_post
 * @return bool
 */
function yarpp_related_exist( $args = array(), $reference_ID = false ) {
	global $yarpp;

	if ( is_array( $reference_ID ) ) {
		_doing_it_wrong( __FUNCTION__, 'This YARPP function now takes $args first and $reference_ID second.', '3.5' );
		return;
	}

	return $yarpp->related_exist( $reference_ID, $args );
}

/**
 * Gets an array of related posts.
 *
 * @param array $args see readme.txt installation tab's  "YARPP functions()" section
 * @param int   $reference_ID the post ID to search against. If used from within "the loop", defaults to the
 *                            $current_post
 *
 * @return WP_Post[]
 */
function yarpp_get_related( $args = array(), $reference_ID = false ) {
	global $yarpp;
	return $yarpp->get_related( $reference_ID, $args );
}

/**
 * @deprecated 5.12.0 use yarpp_related instead
 *
 * @param array $args
 * @param bool  $reference_ID
 * @param bool  $echo
 */
function related_posts( $args = array(), $reference_ID = false, $echo = true ) {
	_deprecated_function( 'related_posts', '5.12.0', 'yarpp_related' );
	global $yarpp;

	if ( false !== $reference_ID && is_bool( $reference_ID ) ) {
		_doing_it_wrong( __FUNCTION__, 'This YARPP function now takes $args first and $reference_ID second.', '3.5' );
		return;
	}

	if ( $yarpp->get_option( 'cross_relate' ) ) {
		$args['post_type'] = $yarpp->get_post_types();
	} else {
		$args['post_type'] = array( 'post' );
	}

	return yarpp_related( $args, $reference_ID, $echo );
}

/**
 *
 * @deprecated since 5.12.0 use yarpp_related() instead
 * @param array $args
 * @param bool  $reference_ID
 * @param bool  $echo
 * @return array
 */
function related_pages( $args = array(), $reference_ID = false, $echo = true ) {
	_deprecated_function( 'related_pages', '5.12.0', 'yarpp_related' );
	global $yarpp;

	if ( false !== $reference_ID && is_bool( $reference_ID ) ) {
		_doing_it_wrong( __FUNCTION__, 'This YARPP function now takes $args first and $reference_ID second.', '3.5' );
		return;
	}

	if ( $yarpp->get_option( 'cross_relate' ) ) {
		$args['post_type'] = $yarpp->get_post_types();
	} else {
		$args['post_type'] = array( 'page' );
	}

	return yarpp_related( $args, $reference_ID, $echo );
}

/**
 * @deprecated since 5.12.0 use yarpp_related() instead
 * @param array $args
 * @param int   $reference_ID
 * @param bool  $echo
 *
 * @return string|void
 */
function related_entries( $args = array(), $reference_ID = false, $echo = true ) {
	_deprecated_function( 'related_entries', '5.12.0', 'yarpp_related' );
	global $yarpp;

	if ( false !== $reference_ID && is_bool( $reference_ID ) ) {
		_doing_it_wrong( __FUNCTION__, 'This YARPP function now takes $args first and $reference_ID second.', '3.5' );
		return;
	}

	$args['post_type'] = $yarpp->get_post_types();

	return yarpp_related( $args, $reference_ID, $echo );
}

/**
 * @deprecated since 5.12.0 use yarpp_related_exist() instead
 * @param array $args
 * @param int   $reference_ID
 *
 * @return bool
 */
function related_posts_exist( $args = array(), $reference_ID = false ) {
	_deprecated_function( 'related_posts_exist', '5.12.0', 'yarpp_related_exist' );
	global $yarpp;

	if ( $yarpp->get_option( 'cross_relate' ) ) {
		$args['post_type'] = $yarpp->get_post_types();
	} else {
		$args['post_type'] = array( 'post' );
	}

	return yarpp_related_exist( $args, $reference_ID );
}

/**
 * @deprecated since 5.12.0 use yarpp_related_exist() instead
 * @param array $args
 * @param bool  $reference_ID
 *
 * @return bool
 */
function related_pages_exist( $args = array(), $reference_ID = false ) {
	_deprecated_function( 'related_pages_exist', '5.12.0', 'yarpp_related_exist' );
	global $yarpp;

	if ( $yarpp->get_option( 'cross_relate' ) ) {
		$args['post_type'] = $yarpp->get_post_types();
	} else {
		$args['post_type'] = array( 'page' );
	}

	return yarpp_related_exist( $args, $reference_ID );
}

/**
 * @deprecated since 5.12.0 use yarpp_related_exist() instead
 * @param array $args
 * @param bool  $reference_ID
 *
 * @return bool
 */
function related_entries_exist( $args = array(), $reference_ID = false ) {
	_deprecated_function( 'related_entries_exist', '5.12.0', 'yarpp_related_exist' );
	global $yarpp;

	$args['post_type'] = $yarpp->get_post_types();

	return yarpp_related_exist( $args, $reference_ID );
}
