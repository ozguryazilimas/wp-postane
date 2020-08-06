<?php

/**
 * Class ExactMetrics_Welcome
 */
class ExactMetrics_Welcome {

	/**
	 * ExactMetrics_Welcome constructor.
	 */
	public function __construct() {

		// If we are not in admin or admin ajax, return
		if ( ! is_admin() ) {
			return;
		}

		// If user is in admin ajax or doing cron, return
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		// If user is not logged in, return
		if ( ! is_user_logged_in() ) {
			return;
		}

		// If user cannot manage_options, return
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'maybe_redirect' ), 9999 );

		add_action( 'admin_menu', array( $this, 'register' ) );
		// Add the welcome screen to the network dashboard.
		add_action( 'network_admin_menu', array( $this, 'register' ) );

		add_action( 'admin_head', array( $this, 'hide_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'welcome_scripts' ) );
	}

	/**
	 * Register the pages to be used for the Welcome screen.
	 *
	 * These pages will be removed from the Dashboard menu, so they will
	 * not actually show. Sneaky, sneaky.
	 *
	 * @since 1.0.0
	 */
	public function register() {

		// Getting started - shows after installation.
		add_dashboard_page(
			esc_html__( 'Welcome to ExactMetrics', 'google-analytics-dashboard-for-wp' ),
			esc_html__( 'Welcome to ExactMetrics', 'google-analytics-dashboard-for-wp' ),
			apply_filters( 'exactmetrics_welcome_cap', 'manage_options' ),
			'exactmetrics-getting-started',
			array( $this, 'welcome_screen' )
		);
	}

	/**
	 * Removed the dashboard pages from the admin menu.
	 *
	 * This means the pages are still available to us, but hidden.
	 *
	 * @since 1.0.0
	 */
	public function hide_menu() {
		remove_submenu_page( 'index.php', 'exactmetrics-getting-started' );
	}


	/**
	 * Check if we should do any redirect.
	 */
	public function maybe_redirect() {

		// Bail if no activation redirect.
		if ( ! get_transient( '_exactmetrics_activation_redirect' ) || isset( $_GET['exactmetrics-redirect'] ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( '_exactmetrics_activation_redirect' );

		// Bail if activating from network, or bulk.
		if ( isset( $_GET['activate-multi'] ) ) { // WPCS: CSRF ok, input var ok.
			return;
		}

		$upgrade = get_option( 'exactmetrics_version_upgraded_from', false );
		if ( apply_filters( 'exactmetrics_enable_onboarding_wizard', false === $upgrade ) ) {
			$redirect = admin_url( 'index.php?page=exactmetrics-getting-started&exactmetrics-redirect=1' );
			$path     = 'index.php?page=exactmetrics-getting-started&exactmetrics-redirect=1';
			$redirect = is_network_admin() ? network_admin_url( $path ) : admin_url( $path );
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Scripts for loading the welcome screen Vue instance.
	 */
	public function welcome_scripts() {

		$current_screen = get_current_screen();
		$screens        = array(
			'dashboard_page_exactmetrics-getting-started',
			'index_page_exactmetrics-getting-started-network',
		);

		if ( empty( $current_screen->id ) || ! in_array( $current_screen->id, $screens, true ) ) {
			return;
		}

		global $wp_version;
		$version_path = exactmetrics_is_pro_version() ? 'pro' : 'lite';
		if ( ! defined( 'EXACTMETRICS_LOCAL_WIZARD_JS_URL' ) ) {
			wp_enqueue_style( 'exactmetrics-vue-welcome-style-vendors', plugins_url( $version_path . '/assets/vue/css/chunk-vendors.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-welcome-style-common', plugins_url( $version_path . '/assets/vue/css/chunk-common.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-welcome-style', plugins_url( $version_path . '/assets/vue/css/wizard.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_script( 'exactmetrics-vue-welcome-vendors', plugins_url( $version_path . '/assets/vue/js/chunk-vendors.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-welcome-common', plugins_url( $version_path . '/assets/vue/js/chunk-common.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
			wp_register_script( 'exactmetrics-vue-welcome-script', plugins_url( $version_path . '/assets/vue/js/wizard.js', EXACTMETRICS_PLUGIN_FILE ), array(
				'exactmetrics-vue-welcome-vendors',
				'exactmetrics-vue-welcome-common',
			), exactmetrics_get_asset_version(), true );
		} else {
			wp_enqueue_script( 'exactmetrics-vue-welcome-vendors', EXACTMETRICS_LOCAL_VENDORS_JS_URL, array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-welcome-common', EXACTMETRICS_LOCAL_COMMON_JS_URL, array(), exactmetrics_get_asset_version(), true );
			wp_register_script( 'exactmetrics-vue-welcome-script', EXACTMETRICS_LOCAL_WIZARD_JS_URL, array(
				'exactmetrics-vue-welcome-vendors',
				'exactmetrics-vue-welcome-common',
			), exactmetrics_get_asset_version(), true );
		}
		wp_enqueue_script( 'exactmetrics-vue-welcome-script' );

		$user_data = wp_get_current_user();

		wp_localize_script(
			'exactmetrics-vue-welcome-script',
			'exactmetrics',
			array(
				'ajax'                 => add_query_arg( 'page', 'exactmetrics-onboarding', admin_url( 'admin-ajax.php' ) ),
				'nonce'                => wp_create_nonce( 'mi-admin-nonce' ),
				'network'              => is_network_admin(),
				'translations'         => wp_get_jed_locale_data( 'mi-vue-app' ),
				'assets'               => plugins_url( $version_path . '/assets/vue', EXACTMETRICS_PLUGIN_FILE ),
				'roles'                => exactmetrics_get_roles(),
				'roles_manage_options' => exactmetrics_get_manage_options_roles(),
				'wizard_url'           => is_network_admin() ? network_admin_url( 'index.php?page=exactmetrics-onboarding' ) : admin_url( 'index.php?page=exactmetrics-onboarding' ),
				'shareasale_id'        => exactmetrics_get_shareasale_id(),
				'shareasale_url'       => exactmetrics_get_shareasale_url( exactmetrics_get_shareasale_id(), '' ),
				// Used to add notices for future deprecations.
				'versions'             => exactmetrics_get_php_wp_version_warning_data(),
				'plugin_version'       => EXACTMETRICS_VERSION,
				'first_name'           => ! empty( $user_data->first_name ) ? $user_data->first_name : '',
				'exit_url'             => add_query_arg( 'page', 'exactmetrics_settings', admin_url( 'admin.php' ) ),
				'had_ecommerce'        => exactmetrics_get_option( 'gadwp_ecommerce', false ),
			)
		);
	}

	/**
	 * Load the welcome screen content.
	 */
	public function welcome_screen() {
		do_action( 'exactmetrics_head' );

		exactmetrics_settings_error_page( $this->get_screen_id() );
		exactmetrics_settings_inline_js();
	}

	/**
	 * Get the screen id to control which Vue component is loaded.
	 *
	 * @return string
	 */
	public function get_screen_id() {
		$screen_id = 'exactmetrics-welcome';

		if ( defined( 'EXACTMETRICS_VERSION' ) && function_exists( 'ExactMetrics' ) ) {
			$migrated = exactmetrics_get_option( 'gadwp_migrated', 0 );
			if ( time() - $migrated < HOUR_IN_SECONDS || isset( $_GET['exactmetrics-migration'] ) ) {
				$screen_id = 'exactmetrics-migration-wizard';
			}
		}

		return $screen_id;
	}
}

new ExactMetrics_Welcome();
