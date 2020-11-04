<?php
/**
 * ExactMetrics Connect is our service that makes it easy for non-techy users to
 * upgrade to ExactMetrics Pro without having to manually install the ExactMetrics Pro plugin.
 *
 * @package ExactMetrics
 * @since 7.7.2
 */

/**
 * Class ExactMetrics_Connect
 */
class ExactMetrics_Connect {

	/**
	 * ExactMetrics_Connect constructor.
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Add hooks for Connect.
	 */
	public function hooks() {

		add_action( 'wp_ajax_exactmetrics_connect_url', array( $this, 'generate_connect_url' ) );

		add_action( 'wp_ajax_nopriv_exactmetrics_connect_process', array( $this, 'process' ) );
	}

	/**
	 * Generates and returns ExactMetrics Connect URL.
	 */
	public function generate_connect_url() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		// Check for permissions.
		if ( ! exactmetrics_can_install_plugins() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You are not allowed to install plugins.', 'google-analytics-dashboard-for-wp' ) ) );
		}

		if ( exactmetrics_is_dev_url( home_url() ) ) {
			wp_send_json_success( array(
				'url' => 'https://www.exactmetrics.com/docs/go-lite-pro/#manual-upgrade',
			) );
		}

		$key = ! empty( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( empty( $key ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Please enter your license key to connect.', 'google-analytics-dashboard-for-wp' ),
				)
			);
		}

		// Verify pro version is not installed.
		$active = activate_plugin( 'exactmetrics-premium/exactmetrics-premium.php', false, false, true );
		if ( ! is_wp_error( $active ) ) {
			// Deactivate plugin.
			deactivate_plugins( plugin_basename( EXACTMETRICS_PLUGIN_FILE ), false, false );
			wp_send_json_error( array(
				'message' => esc_html__( 'Pro version is already installed.', 'google-analytics-dashboard-for-wp' ),
				'reload'  => true,
			) );
		}

		// Network?
		$network = ! empty( $_POST['network'] ) && $_POST['network'];

		// Redirect.
		$oth = hash( 'sha512', wp_rand() );
		update_option( 'exactmetrics_connect', array(
			'key'     => $key,
			'time'    => time(),
			'network' => $network,
		) );
		update_option( 'exactmetrics_connect_token', $oth );
		$version  = ExactMetrics()->version;
		$siteurl  = admin_url();
		$endpoint = admin_url( 'admin-ajax.php' );
		$redirect = $network ? network_admin_url( 'admin.php?page=exactmetrics_network' ) : admin_url( 'admin.php?page=exactmetrics_settings' );

		$url = add_query_arg( array(
			'key'      => $key,
			'oth'      => $oth,
			'endpoint' => $endpoint,
			'version'  => $version,
			'siteurl'  => $siteurl,
			'homeurl'  => home_url(),
			'redirect' => rawurldecode( base64_encode( $redirect ) ),
			'v'        => 2,
		), 'https://upgrade.exactmetrics.com' );

		wp_send_json_success( array(
			'url' => $url,
		) );
	}

	/**
	 * Process ExactMetrics Connect.
	 */
	public function process() {
		$error = esc_html__( 'Could not install upgrade. Please download from exactmetrics.com and install manually.', 'google-analytics-dashboard-for-wp' );

		// verify params present (oth & download link).
		$post_oth = ! empty( $_REQUEST['oth'] ) ? sanitize_text_field( $_REQUEST['oth'] ) : '';
		$post_url = ! empty( $_REQUEST['file'] ) ? $_REQUEST['file'] : '';
		$license  = get_option( 'exactmetrics_connect', false );
		$network  = ! empty( $license['network'] ) ? boolval( $license['network'] ) : false;
		if ( empty( $post_oth ) || empty( $post_url ) ) {
			wp_send_json_error( $error );
		}
		// Verify oth.
		$oth = get_option( 'exactmetrics_connect_token' );
		if ( empty( $oth ) ) {
			wp_send_json_error( $error );
		}
		if ( ! hash_equals( $oth, $post_oth ) ) {
			wp_send_json_error( $error );
		}
		// Delete so cannot replay.
		delete_option( 'exactmetrics_connect_token' );
		// Set the current screen to avoid undefined notices.
		set_current_screen( 'exactmetrics_page_exactmetrics_settings' );
		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				array(
					'page' => 'exactmetrics-settings',
				),
				admin_url( 'admin.php' )
			)
		);
		// Verify pro not activated.
		if ( exactmetrics_is_pro_version() ) {
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'google-analytics-dashboard-for-wp' ) );
		}
		// Verify pro not installed.
		$active = activate_plugin( 'exactmetrics-premium/exactmetrics-premium.php', $url, $network, true );
		if ( ! is_wp_error( $active ) ) {
			deactivate_plugins( plugin_basename( EXACTMETRICS_PLUGIN_FILE ), false, $network );
			wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'google-analytics-dashboard-for-wp' ) );
		}
		$creds = request_filesystem_credentials( $url, '', false, false, null );
		// Check for file system permissions.
		if ( false === $creds ) {
			wp_send_json_error( $error );
		}
		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $error );
		}
		// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		exactmetrics_require_upgrader();
		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
		// Create the plugin upgrader with our custom skin.
		$installer = new ExactMetrics_Plugin_Upgrader( new ExactMetrics_Skin() );
		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error( $error );
		}

		// Check license key.
		if ( empty( $license['key'] ) ) {
			wp_send_json_error( new WP_Error( '403', esc_html__( 'You are not licensed.', 'google-analytics-dashboard-for-wp' ) ) );
		}

		$installer->install( $post_url ); // phpcs:ignore
		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $installer->plugin_info() ) {
			$plugin_basename = $installer->plugin_info();

			// Deactivate the lite version first.
			deactivate_plugins( plugin_basename( EXACTMETRICS_PLUGIN_FILE ), false, $network );

			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename, '', $network, true );
			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success( esc_html__( 'Plugin installed & activated.', 'google-analytics-dashboard-for-wp' ) );
			} else {
				// Reactivate the lite plugin if pro activation failed.
				activate_plugin( plugin_basename( EXACTMETRICS_PLUGIN_FILE ), '', $network, true );
				wp_send_json_error( esc_html__( 'Pro version installed but needs to be activated from the Plugins page inside your WordPress admin.', 'google-analytics-dashboard-for-wp' ) );
			}
		}
		wp_send_json_error( $error );
	}

}

new ExactMetrics_Connect();
