<?php
/**
 * Class of activation/deactivation of the plugin. Must be registered in file includes/class.plugin.php
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 02.12.2018, Webcraftic
 * @see           Wbcr_Factory421_Activator
 *
 * @version       1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WAPT_Activation extends Wbcr_Factory421_Activator {

	/**
	 * Method is executed during the activation of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		// Code to be executed during plugin activation
	}

	/**
	 * The method is executed during the deactivation of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		$apt_ds = WAPT_Plugin::app()->getOption( 'delete_settings', false);

		if ( $apt_ds ) {
			// remove plugin options
			global $wpdb;
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wbcr_apt_%';" );
		}
	}
}


