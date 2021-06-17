<?php
/**
 * Displays a Feedback Form when a user clicks on the "Deactivate" link on the plugin settings page.
 *
 * @package shareaholic
 */

if ( ! is_admin() ) {
	return;
}

global $pagenow;

if ( 'plugins.php' !== $pagenow ) {
	return;
}

if ( ! function_exists( 'shareaholic_deactivate_feedback' ) ) {

	/**
	 * Handles adding required code for the feedback form
	 */
	function shareaholic_deactivate_feedback() {
		// Plugins.
		/**
		 * Each plugin adds an array to this filtered value in order to register itself. Please see the callback
		 * to see the expected structure of the array.
		 */
		$plugins = apply_filters( 'shareaholic_deactivate_feedback_form_plugins', array() );

		if ( ! $plugins ) {
			return;
		}

		if ( is_array( $plugins ) && end( $plugins )->script_cache_ver ) {
			$script_cache_ver = end( $plugins )->script_cache_ver;
		} else {
			$script_cache_ver = '1.1.1';
		}

		// Enqueue scripts.
		wp_enqueue_script( 'remodal', plugin_dir_url( __FILE__ ) . 'remodal.min.js', array(), $script_cache_ver, false );
		wp_enqueue_style( 'remodal', plugin_dir_url( __FILE__ ) . 'remodal.css', array(), $script_cache_ver );
		wp_enqueue_style( 'remodal-default-theme', plugin_dir_url( __FILE__ ) . 'remodal-default-theme.css', array(), $script_cache_ver );

		wp_enqueue_script(
			'shareaholic-deactivate-feedback-form',
			plugin_dir_url( __FILE__ ) . 'deactivate-feedback-form.js',
			array(),
			$script_cache_ver,
			false
		);
		wp_enqueue_style(
			'shareaholic-deactivate-feedback-form',
			plugin_dir_url( __FILE__ ) . 'deactivate-feedback-form.css',
			array(),
			$script_cache_ver
		);

		$current_user = wp_get_current_user();
		if ( $current_user instanceof WP_User && is_user_logged_in() && $current_user->ID ) {
			$email = $current_user->user_email;
			if ( is_array( $current_user->roles ) ) {
				$role = reset( $current_user->roles );
			} else {
				$role = '';
			}
		} else {
			$email = '';
			$role  = '';
		}

		foreach ( $plugins as $plugin ) {
			$plugin->email = $email;
			$plugin->role  = $role;
		}

		// Send plugin data.
		wp_localize_script(
			'shareaholic-deactivate-feedback-form',
			'shareaholic_deactivate_feedback_form_plugins',
			$plugins
		);
	}
}

add_action( 'admin_enqueue_scripts', 'shareaholic_deactivate_feedback' );
