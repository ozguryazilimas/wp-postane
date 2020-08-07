<?php
/**
 * ExactMetrics Onboarding Wizard
 *
 * @since 7.3
 *
 * @package ExactMetrics
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_Dashboard_Widget
 */
class ExactMetrics_Onboarding_Wizard {


	/**
	 * ExactMetrics_Onboarding_Wizard constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'maybe_load_onboarding_wizard' ) );

		add_action( 'admin_menu', array( $this, 'add_dashboard_page' ) );
		add_action( 'network_admin_menu', array( $this, 'add_dashboard_page' ) );

		add_action( 'wp_ajax_exactmetrics_onboarding_wpforms_install', array(
			$this,
			'install_and_activate_wpforms',
		) );

		add_action( 'wp_ajax_exactmetrics_onboarding_get_errors', array(
			$this,
			'get_install_errors',
		) );

		// This will only be called in the Onboarding Wizard context because of previous checks.
		add_filter( 'exactmetrics_maybe_authenticate_siteurl', array( $this, 'change_return_url' ) );
		add_filter( 'exactmetrics_auth_success_redirect_url', array( $this, 'change_success_url' ) );
		add_filter( 'exactmetrics_reauth_success_redirect_url', array( $this, 'change_success_url' ) );

	}

	/**
	 * Checks if the Wizard should be loaded in current context.
	 */
	public function maybe_load_onboarding_wizard() {

		// Check for wizard-specific parameter
		// Allow plugins to disable the onboarding wizard
		// Check if current user is allowed to save settings.
		if ( ! ( isset( $_GET['page'] ) || 'exactmetrics-onboarding' !== $_GET['page'] || apply_filters( 'exactmetrics_enable_onboarding_wizard', true ) || ! current_user_can( 'exactmetrics_save_settings' ) ) ) { // WPCS: CSRF ok, input var ok.
			return;
		}

		// Don't load the interface if doing an ajax call.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		set_current_screen();

		// Remove an action in the Gutenberg plugin ( not core Gutenberg ) which throws an error.
		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

		$this->load_onboarding_wizard();

	}

	/**
	 * Register page through WordPress's hooks.
	 */
	public function add_dashboard_page() {
		add_dashboard_page( '', '', 'exactmetrics_save_settings', 'exactmetrics-onboarding', '' );
	}

	/**
	 * Load the Onboarding Wizard template.
	 */
	private function load_onboarding_wizard() {

		$this->enqueue_scripts();

		$this->onboarding_wizard_header();
		$this->onboarding_wizard_content();
		$this->onboarding_wizard_footer();

		exit;

	}

	/**
	 * Load the scripts needed for the Onboarding Wizard.
	 */
	public function enqueue_scripts() {

		global $wp_version;
		$version_path = exactmetrics_is_pro_version() ? 'pro' : 'lite';
		$rtl          = is_rtl() ? '.rtl' : '';
		if ( ! defined( 'EXACTMETRICS_LOCAL_WIZARD_JS_URL' ) ) {
			wp_enqueue_style( 'exactmetrics-vue-style-vendors', plugins_url( $version_path . '/assets/vue/css/chunk-vendors' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-style-common', plugins_url( $version_path . '/assets/vue/css/chunk-common' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-style', plugins_url( $version_path . '/assets/vue/css/wizard' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_script( 'exactmetrics-vue-vendors', plugins_url( $version_path . '/assets/vue/js/chunk-vendors.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-common', plugins_url( $version_path . '/assets/vue/js/chunk-common.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
			wp_register_script( 'exactmetrics-vue-script', plugins_url( $version_path . '/assets/vue/js/wizard.js', EXACTMETRICS_PLUGIN_FILE ), array(
				'exactmetrics-vue-vendors',
				'exactmetrics-vue-common',
			), exactmetrics_get_asset_version(), true );
		} else {
			wp_enqueue_script( 'exactmetrics-vue-vendors', EXACTMETRICS_LOCAL_VENDORS_JS_URL, array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-common', EXACTMETRICS_LOCAL_COMMON_JS_URL, array(), exactmetrics_get_asset_version(), true );
			wp_register_script( 'exactmetrics-vue-script', EXACTMETRICS_LOCAL_WIZARD_JS_URL, array(
				'exactmetrics-vue-vendors',
				'exactmetrics-vue-common',
			), exactmetrics_get_asset_version(), true );
		}
		wp_enqueue_script( 'exactmetrics-vue-script' );

		$settings_page = is_network_admin() ? add_query_arg( 'page', 'exactmetrics_network', network_admin_url( 'admin.php' ) ) : add_query_arg( 'page', 'exactmetrics_settings', admin_url( 'admin.php' ) );

		wp_localize_script(
			'exactmetrics-vue-script',
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
				'is_eu'                => $this->should_include_eu_addon(),
				'activate_nonce'       => wp_create_nonce( 'exactmetrics-activate' ),
				'install_nonce'        => wp_create_nonce( 'exactmetrics-install' ),
				'exit_url'             => $settings_page,
				'shareasale_id'        => exactmetrics_get_shareasale_id(),
				'shareasale_url'       => exactmetrics_get_shareasale_url( exactmetrics_get_shareasale_id(), '' ),
				// Used to add notices for future deprecations.
				'versions'             => exactmetrics_get_php_wp_version_warning_data(),
				'plugin_version'       => EXACTMETRICS_VERSION,
				'migrated'             => exactmetrics_get_option( 'gadwp_migrated', false ),
			)
		);

	}

	/**
	 * Outputs the simplified header used for the Onboarding Wizard.
	 */
	public function onboarding_wizard_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php esc_html_e( 'ExactMetrics &rsaquo; Onboarding Wizard', 'google-analytics-dashboard-for-wp' ); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="exactmetrics-onboarding">
		<?php
	}

	/**
	 * Outputs the content of the current step.
	 */
	public function onboarding_wizard_content() {
		$admin_url = is_network_admin() ? network_admin_url() : admin_url();

		exactmetrics_settings_error_page( 'exactmetrics-vue-onboarding-wizard', '<a href="' . $admin_url . '">' . esc_html__( 'Return to Dashboard', 'google-analytics-dashboard-for-wp' ) . '</a>' );
		exactmetrics_settings_inline_js();
	}

	/**
	 * Outputs the simplified footer used for the Onboarding Wizard.
	 */
	public function onboarding_wizard_footer() {
		?>
		<?php wp_print_scripts( 'exactmetrics-vue-script' ); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Check if we should suggest the EU Compliance addon by attempting to determine the website's location.
	 *
	 * @return bool
	 */
	public function should_include_eu_addon() {

		// Is WooCommerce installed and the countries class installed.
		if ( class_exists( 'WooCommerce' ) && class_exists( 'WC_Countries' ) ) {
			$wc_countries = new WC_Countries();
			$country      = $wc_countries->get_base_country();
			$continent    = $wc_countries->get_continent_code_for_country( $country );

			if ( 'EU' === $continent ) {
				return true;
			}
		}

		// Is EDD installed?
		if ( class_exists( 'Easy_Digital_Downloads' ) && function_exists( 'edd_get_shop_country' ) ) {
			$country      = strtoupper( edd_get_shop_country() );
			$eu_countries = self::get_eu_countries();

			// Check if the country code is in the list of EU countries we have stored.
			if ( in_array( $country, $eu_countries, true ) ) {
				return true;
			}
		}

		// If no store installed, check the timezone setting.
		$timezone_string = get_option( 'timezone_string' );
		if ( 0 === strpos( strtolower( $timezone_string ), 'europe' ) ) {
			// If the timezone is set to Europe, assume the website is based in Europe.
			return true;
		}

		return false;

	}

	/**
	 * Install WPForms lite and activate it, prevent initial setup step.
	 *
	 * @return null|string
	 */
	public function install_and_activate_wpforms() {

		check_ajax_referer( 'exactmetrics-install', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json( array(
				'message' => esc_html__( 'You are not allowed to install plugins', 'google-analytics-dashboard-for-wp' ),
			) );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api( 'plugin_information', array(
			'slug'   => 'wpforms-lite',
			'fields' => array(
				'short_description' => false,
				'sections'          => false,
				'requires'          => false,
				'rating'            => false,
				'ratings'           => false,
				'downloaded'        => false,
				'last_updated'      => false,
				'added'             => false,
				'tags'              => false,
				'compatibility'     => false,
				'homepage'          => false,
				'donate_link'       => false,
			),
		) );

		if ( is_wp_error( $api ) ) {
			return $api->get_error_message();
		}

		$download_url = $api->download_link;

		$method = '';
		$url    = is_network_admin() ? add_query_arg( 'page', 'exactmetrics_network', network_admin_url( 'admin.php' ) ) : add_query_arg( 'page', 'exactmetrics_settings', admin_url( 'admin.php' ) );
		$url    = esc_url( $url );

		ob_start();
		if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, null ) ) ) {
			$form = ob_get_clean();

			wp_send_json( array( 'form' => $form ) );
		}

		// If we are not authenticated, make it happen now.
		if ( ! WP_Filesystem( $creds ) ) {
			ob_start();
			request_filesystem_credentials( $url, $method, true, false, null );
			$form = ob_get_clean();

			wp_send_json( array( 'form' => $form ) );

		}

		// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		exactmetrics_require_upgrader( false );

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( new ExactMetrics_Skin() );
		$installer->install( $download_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();
		if ( $installer->plugin_info() ) {
			// Set this option to prevent WP Forms setup from showing up after the wizard completes.
			update_option( 'wpforms_activation_redirect', true );
			activate_plugin( $installer->plugin_info() );
			wp_send_json_success();
		}

		wp_die();

	}

	/**
	 * Update the redirect url so the user returns to the Onboarding Wizard after auth.
	 *
	 * @param string $siteurl The url to which the user is redirected for auth.
	 *
	 * @return mixed
	 */
	public function change_return_url( $siteurl ) {

		$url       = wp_parse_url( $siteurl );
		$admin_url = is_network_admin() ? network_admin_url() : admin_url();

		if ( isset( $url['query'] ) ) {

			parse_str( $url['query'], $parameters );

			$parameters['return'] = rawurlencode( add_query_arg( array(
				'page' => 'exactmetrics-onboarding',
			), $admin_url ) );

			$siteurl = str_replace( $url['query'], '', $siteurl );

			$siteurl = add_query_arg( $parameters, $siteurl );

			$siteurl .= '#/authenticate';

		}

		return $siteurl;

	}

	/**
	 * Update the success redirect URL so if all is well we get to the next step.
	 *
	 * @param string $siteurl The url to which the user is redirected after a successful auth.
	 *
	 * @return mixed
	 */
	public function change_success_url( $siteurl ) {

		$admin_url   = is_network_admin() ? network_admin_url() : admin_url();
		$return_step = is_network_admin() ? 'recommended_addons' : 'recommended_settings';

		$siteurl = add_query_arg( array(
			'page' => 'exactmetrics-onboarding',
		), $admin_url );

		$siteurl .= '#/' . $return_step;

		return $siteurl;

	}

	/**
	 * Retrieve an array of European countries.
	 *
	 * @return array
	 */
	public static function get_eu_countries() {
		return array(
			'AD',
			'AL',
			'AT',
			'AX',
			'BA',
			'BE',
			'BG',
			'BY',
			'CH',
			'CY',
			'CZ',
			'DE',
			'DK',
			'EE',
			'ES',
			'FI',
			'FO',
			'FR',
			'GB',
			'GG',
			'GI',
			'GR',
			'HR',
			'HU',
			'IE',
			'IM',
			'IS',
			'IT',
			'JE',
			'LI',
			'LT',
			'LU',
			'LV',
			'MC',
			'MD',
			'ME',
			'MK',
			'MT',
			'NL',
			'NO',
			'PL',
			'PT',
			'RO',
			'RS',
			'RU',
			'SE',
			'SI',
			'SJ',
			'SK',
			'SM',
			'TR',
			'UA',
			'VA',
		);
	}

	/**
	 * Ajax handler for grabbing the installed code status.
	 */
	public function get_install_errors() {

		wp_send_json( exactmetrics_is_code_installed_frontend() );

	}

}

new ExactMetrics_Onboarding_Wizard();
