<?php
/**
 * Contains the functions which are use on plugin admin pages
 * @package Captcha
 * @since   4.2.3
 */

/**
 * Fetch plugin default options
 * @param  void
 * @return array
 */
if ( ! function_exists( 'hctpc_get_default_options' ) ) {
	function hctpc_get_default_options() {
		global $hctpc_plugin_info;

		$default_options = array(
			'plugin_option_version'        => $hctpc_plugin_info["Version"],
			'str_key'                      => array( 'time' => '', 'key' => '' ),
			'type'							=> 'math_actions',
			'math_actions'                 => array( 'plus', 'minus', 'multiplications' ),
			'operand_format'               => array( 'numbers', 'words', 'images' ),
			'images_count'					=> 5,
			'title'                        => '',
			'required_symbol'              => '*',
			'display_reload_button'        => true,
			'enlarge_images'               => false,
			'used_packages'                => array(),
			'enable_time_limit'            => false,
			'time_limit'                   => 120,
			'no_answer'                    => __( 'Please complete the captcha.', 'captcha' ),
			'wrong_answer'                 => __( 'Please enter correct captcha value.', 'captcha' ),
			'time_limit_off'               => __( 'Time limit exceeded. Please complete the captcha once again.', 'captcha' ),
			'time_limit_off_notice'        => __( 'Time limit exceeded. Please complete the captcha once again.', 'captcha' ),
			'whitelist_message'            => __( 'Your IP address is Whitelisted.', 'captcha' ),
			'load_via_ajax'                => false,
			'display_settings_notice'      => 1,
			'suggest_feature_banner'       => 1,
			'forms'                        => array(),
		);

		$forms = hctpc_get_default_forms();

		foreach ( $forms as $form ) {
			$default_options['forms'][ $form ] = array(
				'enable'               => in_array( $form, array( 'wp_login', 'wp_register', 'wp_lost_password', 'wp_comments' ) ),
				'hide_from_registered' => 'wp_comments' == $form,
			);
		}

		return $default_options;
	}
}

/**
 * Fetch the list of forms which are compatible with the plugin
 * @param  void
 * @return array
 */
if ( ! function_exists( 'hctpc_get_default_forms' ) ) {
	function hctpc_get_default_forms() {
		$defaults = array(
			'wp_login', 'wp_register',
			'wp_lost_password', 'wp_comments'
		);

		/*
		 * Add user forms to defaults
		 */
		$new_forms = apply_filters( 'hctpc_add_form', array() );

		if ( ! is_array( $new_forms ) || empty( $new_forms ) )
			return $defaults;

		$new = array_filter( array_map( 'esc_attr', array_keys( $new_forms ) ) );

		return array_unique( array_merge( $defaults, $new ) );
	}
}