<?php
if ( ! is_admin() ) {
	return;
}

global $pagenow;

if ( $pagenow != 'plugins.php' ) {
	return;
}

if ( ! function_exists( 'shareaholic_deactivate_feedback' ) ) {
	function shareaholic_deactivate_feedback() {
		// Plugins
		/**
		 * Each plugin adds an array to this filtered value in order to register itself. Please see the callback
		 * to see the expected structure of the array
		 */
		$plugins = apply_filters( 'shareaholic_deactivate_feedback_form_plugins', array() );

		if ( ! $plugins ) {
			return;
		}

		// Enqueue scripts
		wp_enqueue_script( 'remodal', plugin_dir_url( __FILE__ ) . 'remodal.min.js', array(), '1.1.1' );
		wp_enqueue_style( 'remodal', plugin_dir_url( __FILE__ ) . 'remodal.css', array(), '1.1.1' );
		wp_enqueue_style( 'remodal-default-theme', plugin_dir_url( __FILE__ ) . 'remodal-default-theme.css', array(), '1.1.1' );

		wp_enqueue_script(
			'shareaholic-deactivate-feedback-form',
			plugin_dir_url( __FILE__ ) . 'deactivate-feedback-form.js',
			array(),
			'1.1.1'
		);
		wp_enqueue_style(
			'shareaholic-deactivate-feedback-form',
			plugin_dir_url( __FILE__ ) . 'deactivate-feedback-form.css',
			array(),
			'1.1.1'
		);

		$current_user = wp_get_current_user();
		if ( $current_user instanceof WP_User && $current_user->ID ) {
			$email = $current_user->user_email;
		} else {
			$email = '';
		}

		// Localized strings
		wp_localize_script(
			'shareaholic-deactivate-feedback-form',
			'shareaholic_deactivate_feedback_form_strings',
			array_merge(
				$plugins[0]->translations,
				array(
					'email' => $email,
				)
			)
		);

		// Send plugin data
		wp_localize_script(
			'shareaholic-deactivate-feedback-form',
			'shareaholic_deactivate_feedback_form_plugins',
			$plugins
		);
	}
}

add_action( 'admin_enqueue_scripts', 'shareaholic_deactivate_feedback' );
