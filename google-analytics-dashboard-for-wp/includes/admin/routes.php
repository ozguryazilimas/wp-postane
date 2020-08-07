<?php
/**
 * Routes for VUE are registered here.
 *
 * @package exactmetrics
 */

/**
 * Class ExactMetrics_Rest_Routes
 */
class ExactMetrics_Rest_Routes {

	/**
	 * ExactMetrics_Rest_Routes constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_exactmetrics_vue_get_license', array( $this, 'get_license' ) );
		add_action( 'wp_ajax_exactmetrics_vue_get_profile', array( $this, 'get_profile' ) );
		add_action( 'wp_ajax_exactmetrics_vue_get_settings', array( $this, 'get_settings' ) );
		add_action( 'wp_ajax_exactmetrics_vue_update_settings', array( $this, 'update_settings' ) );
		add_action( 'wp_ajax_exactmetrics_vue_get_addons', array( $this, 'get_addons' ) );
		add_action( 'wp_ajax_exactmetrics_update_manual_ua', array( $this, 'update_manual_ua' ) );
		add_action( 'wp_ajax_exactmetrics_vue_get_report_data', array( $this, 'get_report_data' ) );
		add_action( 'wp_ajax_exactmetrics_vue_install_plugin', array( $this, 'install_plugin' ) );
		add_action( 'wp_ajax_exactmetrics_vue_notice_status', array( $this, 'get_notice_status' ) );
		add_action( 'wp_ajax_exactmetrics_vue_notice_dismiss', array( $this, 'dismiss_notice' ) );

		add_action( 'wp_ajax_exactmetrics_handle_settings_import', array( $this, 'handle_settings_import' ) );

		add_action( 'admin_notices', array( $this, 'hide_old_notices' ), 0 );

		add_action( 'wp_ajax_exactmetrics_vue_dismiss_first_time_notice', array( $this, 'dismiss_first_time_notice' ) );
	}

	/**
	 * Ajax handler for grabbing the license
	 */
	public function get_license() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_view_dashboard' ) || ! exactmetrics_is_pro_version() ) {
			return;
		}

		$site_license    = array(
			'key'         => ExactMetrics()->license->get_site_license_key(),
			'type'        => ExactMetrics()->license->get_site_license_type(),
			'is_disabled' => ExactMetrics()->license->site_license_disabled(),
			'is_expired'  => ExactMetrics()->license->site_license_expired(),
			'is_invalid'  => ExactMetrics()->license->site_license_invalid(),
		);
		$network_license = array(
			'key'         => ExactMetrics()->license->get_network_license_key(),
			'type'        => ExactMetrics()->license->get_network_license_type(),
			'is_disabled' => ExactMetrics()->license->network_license_disabled(),
			'is_expired'  => ExactMetrics()->license->network_license_expired(),
			'is_invalid'  => ExactMetrics()->license->network_license_disabled(),
		);

		wp_send_json( array(
			'site'    => $site_license,
			'network' => $network_license,
		) );

	}

	/**
	 * Ajax handler for grabbing the current authenticated profile.
	 */
	public function get_profile() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
			return;
		}

		wp_send_json( array(
			'ua'                => ExactMetrics()->auth->get_ua(),
			'viewname'          => ExactMetrics()->auth->get_viewname(),
			'manual_ua'         => ExactMetrics()->auth->get_manual_ua(),
			'network_ua'        => ExactMetrics()->auth->get_network_ua(),
			'network_viewname'  => ExactMetrics()->auth->get_network_viewname(),
			'network_manual_ua' => ExactMetrics()->auth->get_network_manual_ua(),
		) );

	}

	/**
	 * Ajax handler for grabbing the settings.
	 */
	public function get_settings() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
			return;
		}

		$options = exactmetrics_get_options();

		// Array fields are needed even if empty.
		$array_fields = array( 'view_reports', 'save_settings', 'ignore_users' );
		foreach ( $array_fields as $array_field ) {
			if ( ! isset( $options[ $array_field ] ) ) {
				$options[ $array_field ] = array();
			}
		}
		if ( isset( $options['custom_code'] ) ) {
			$options['custom_code'] = stripslashes( $options['custom_code'] );
		}

		//add email summaries options
		if ( exactmetrics_is_pro_version() ) {
			$default_email = array(
				'email' => get_option( 'admin_email' ),
			);

			if ( ! isset( $options['email_summaries'] ) ) {
				$options['email_summaries'] = 'on';
			}

			if ( ! isset( $options['summaries_email_addresses'] ) ) {
				$options['summaries_email_addresses'] = array(
					$default_email,
				);
			}

			if ( ! isset( $options['summaries_html_template'] ) ) {
				$options['summaries_html_template'] = 'yes';
			}


			if ( ! isset( $options['summaries_carbon_copy'] ) ) {
				$options['summaries_carbon_copy'] = 'no';
			}


			if ( ! isset( $options['summaries_header_image'] ) ) {
				$options['summaries_header_image'] = '';
			}
		}

		wp_send_json( $options );

	}

	/**
	 * Ajax handler for updating the settings.
	 */
	public function update_settings() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
			return;
		}

		if ( isset( $_POST['setting'] ) ) {
			$setting = sanitize_text_field( wp_unslash( $_POST['setting'] ) );
			if ( isset( $_POST['value'] ) ) {
				$value = $this->handle_sanitization( $setting, $_POST['value'] );
				exactmetrics_update_option( $setting, $value );
				do_action( 'exactmetrics_after_update_settings', $setting, $value );
			} else {
				exactmetrics_update_option( $setting, false );
				do_action( 'exactmetrics_after_update_settings', $setting, false );
			}
		}

		wp_send_json_success();

	}

	/**
	 * Sanitization specific to each field.
	 *
	 * @param string $field The key of the field to sanitize.
	 * @param string $value The value of the field to sanitize.
	 *
	 * @return mixed The sanitized input.
	 */
	private function handle_sanitization( $field, $value ) {

		$value = wp_unslash( $value );

		// Textarea fields.
		$textarea_fields = array(
			'custom_code',
		);

		if ( in_array( $field, $textarea_fields, true ) ) {
			if ( function_exists( 'sanitize_textarea_field' ) ) {
				return sanitize_textarea_field( $value );
			} else {
				return wp_kses( $value, array() );
			}
		}

		$array_value = json_decode( $value, true );
		if ( is_array( $array_value ) ) {
			$value = $array_value;
			// Don't save empty values.
			foreach ( $value as $key => $item ) {
				if ( is_array( $item ) ) {
					$empty = true;
					foreach ( $item as $item_value ) {
						if ( ! empty( $item_value ) ) {
							$empty = false;
						}
					}
					if ( $empty ) {
						unset( $value[ $key ] );
					}
				}
			}

			// Reset array keys because JavaScript can't handle arrays with non-sequential keys.
			$value = array_values( $value );

			return $value;
		}

		return sanitize_text_field( $value );

	}

	/**
	 * Return the state of the addons ( installed, activated )
	 */
	public function get_addons() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
			return;
		}

		if ( isset( $_POST['network'] ) && intval( $_POST['network'] ) > 0 ) {
			define( 'WP_NETWORK_ADMIN', true );
		}

		$addons_data       = exactmetrics_get_addons();
		$parsed_addons     = array();
		$installed_plugins = get_plugins();

		if ( ! is_array( $addons_data ) ) {
			$addons_data = array();
		}

		foreach ( $addons_data as $addons_type => $addons ) {
			foreach ( $addons as $addon ) {
				$slug = 'exactmetrics-' . $addon->slug;
				if ( 'exactmetrics-ecommerce' === $slug ) {
					$addon = $this->get_addon( $installed_plugins, $addons_type, $addon, $slug );
					if ( empty( $addon->installed ) ) {
						$slug  = 'ga-ecommerce';
						$addon = $this->get_addon( $installed_plugins, $addons_type, $addon, $slug );
					}
				} else {
					$addon = $this->get_addon( $installed_plugins, $addons_type, $addon, $slug );
				}
				$parsed_addons[ $addon->slug ] = $addon;
			}
		}

		// Include data about the plugins needed by some addons ( WooCommerce, EDD, Google AMP, CookieBot, etc ).
		// WooCommerce.
		$parsed_addons['woocommerce'] = array(
			'active' => class_exists( 'WooCommerce' ),
		);
		// Edd.
		$parsed_addons['easy_digital_downloads'] = array(
			'active' => class_exists( 'Easy_Digital_Downloads' ),
		);
		// MemberPress.
		$parsed_addons['memberpress'] = array(
			'active' => defined( 'MEPR_VERSION' ) && version_compare( MEPR_VERSION, '1.3.43', '>' ),
		);
		// LifterLMS.
		$parsed_addons['lifterlms'] = array(
			'active' => function_exists( 'LLMS' ) && version_compare( LLMS()->version, '3.32.0', '>=' ),
		);
		// Cookiebot.
		$parsed_addons['cookiebot'] = array(
			'active' => function_exists( 'cookiebot_active' ) && cookiebot_active(),
		);
		// Cookie Notice.
		$parsed_addons['cookie_notice'] = array(
			'active' => class_exists( 'Cookie_Notice' ),
		);
		// Fb Instant Articles.
		$parsed_addons['instant_articles'] = array(
			'active' => defined( 'IA_PLUGIN_VERSION' ) && version_compare( IA_PLUGIN_VERSION, '3.3.4', '>' ),
		);
		// Google AMP.
		$parsed_addons['google_amp'] = array(
			'active' => defined( 'AMP__FILE__' ),
		);
		// WPForms.
		$parsed_addons['wpforms-lite'] = array(
			'active'    => function_exists( 'wpforms' ),
			'icon'      => plugin_dir_url( EXACTMETRICS_PLUGIN_FILE ) . 'assets/images/plugin-wpforms.png',
			'title'     => 'WPForms',
			'excerpt'   => __( 'The most beginner friendly drag & drop WordPress forms plugin allowing you to create beautiful contact forms, subscription forms, payment forms, and more in minutes, not hours!', 'google-analytics-dashboard-for-wp' ),
			'installed' => array_key_exists( 'wpforms-lite/wpforms.php', $installed_plugins ),
			'basename'  => 'wpforms-lite/wpforms.php',
			'slug'      => 'wpforms-lite',
		);
		// OptinMonster.
		$parsed_addons['optinmonster'] = array(
			'active'    => class_exists( 'OMAPI' ),
			'icon'      => plugin_dir_url( EXACTMETRICS_PLUGIN_FILE ) . 'assets/images/plugin-om.png',
			'title'     => 'OptinMonster',
			'excerpt'   => __( 'Our high-converting optin forms like Exit-Intent® popups, Fullscreen Welcome Mats, and Scroll boxes help you dramatically boost conversions and get more email subscribers.', 'google-analytics-dashboard-for-wp' ),
			'installed' => array_key_exists( 'optinmonster/optin-monster-wp-api.php', $installed_plugins ),
			'basename'  => 'optinmonster/optin-monster-wp-api.php',
			'slug'      => 'optinmonster',
		);
		// WP Mail Smtp.
		$parsed_addons['wp-mail-smtp'] = array(
			'active'    => function_exists( 'wp_mail_smtp' ),
			'icon'      => plugin_dir_url( EXACTMETRICS_PLUGIN_FILE ) . 'assets/images/plugin-smtp.png',
			'title'     => 'WP Mail SMTP',
			'excerpt'   => __( 'SMTP (Simple Mail Transfer Protocol) is an industry standard for sending emails. SMTP helps increase email deliverability by using proper authentication.', 'google-analytics-dashboard-for-wp' ),
			'installed' => array_key_exists( 'wp-mail-smtp/wp_mail_smtp.php', $installed_plugins ),
			'basename'  => 'wp-mail-smtp/wp_mail_smtp.php',
			'slug'      => 'wp-mail-smtp',
		);
		// Pretty Links
		$parsed_addons['pretty-link'] = array(
			'active'    => class_exists( 'PrliBaseController' ),
			'icon'      => '',
			'title'     => 'Pretty Links',
			'excerpt'   => __( 'Pretty Links helps you shrink, beautify, track, manage and share any URL on or off of your WordPress website. Create links that look how you want using your own domain name!', 'google-analytics-dashboard-for-wp' ),
			'installed' => array_key_exists( 'pretty-link/pretty-link.php', $installed_plugins ),
			'basename'  => 'pretty-link/pretty-link.php',
			'slug'      => 'pretty-link',
		);
		// SeedProd.
		$parsed_addons['coming-soon'] = array(
			'active'    => function_exists( 'seed_csp4_activation' ),
			'icon'      => plugin_dir_url( EXACTMETRICS_PLUGIN_FILE ) . 'assets/images/seedprod.png',
			'title'     => 'SeedProd',
			'excerpt'   => __( 'Better Coming Soon & Maintenance Mode Pages', 'google-analytics-dashboard-for-wp' ),
			'installed' => array_key_exists( 'coming-soon/coming-soon.php', $installed_plugins ),
			'basename'  => 'coming-soon/coming-soon.php',
			'slug'      => 'coming-soon',
		);
		$parsed_addons['rafflepress'] = array(
			'active'    => function_exists( 'rafflepress_lite_activation' ),
			'icon'      => plugin_dir_url( EXACTMETRICS_PLUGIN_FILE ) . 'assets/images/rafflepress.png',
			'title'     => 'RafflePress',
			'excerpt'   => __( 'Get More Traffic with Viral Giveaways', 'google-analytics-dashboard-for-wp' ),
			'installed' => array_key_exists( 'rafflepress/rafflepress.php', $installed_plugins ),
			'basename'  => 'rafflepress/rafflepress.php',
			'slug'      => 'rafflepress',
		);
		$parsed_addons['trustpulse-api'] = array(
			'active'    => class_exists( 'TPAPI' ),
			'icon'      => plugin_dir_url( EXACTMETRICS_PLUGIN_FILE ) . 'assets/images/trustpulse.png',
			'title'     => 'TrustPulse',
			'excerpt'   => __( 'Social Proof Notifications that Boost Sales', 'google-analytics-dashboard-for-wp' ),
			'installed' => array_key_exists( 'trustpulse-api/trustpulse.php', $installed_plugins ),
			'basename'  => 'trustpulse-api/trustpulse.php',
			'slug'      => 'trustpulse-api',
		);
		// Gravity Forms.
		$parsed_addons['gravity_forms'] = array(
			'active' => class_exists( 'GFCommon' ),
		);
		// Formidable Forms.
		$parsed_addons['formidable_forms'] = array(
			'active' => class_exists( 'FrmHooksController' ),
		);
		// Manual UA Addon.
		if ( ! isset( $parsed_addons['manual_ua'] ) ) {
			$parsed_addons['manual_ua'] = array(
				'active' => class_exists( 'ExactMetrics_Manual_UA' ),
			);
		}

		wp_send_json( $parsed_addons );
	}

	public function get_addon( $installed_plugins, $addons_type, $addon, $slug ) {
		$active          = false;
		$installed       = false;
		$plugin_basename = exactmetrics_get_plugin_basename_from_slug( $slug );

		if ( isset( $installed_plugins[ $plugin_basename ] ) ) {
			$installed = true;

			if ( is_multisite() && is_network_admin() ) {
				$active = is_plugin_active_for_network( $plugin_basename );
			} else {
				$active = is_plugin_active( $plugin_basename );
			}
		}
		if ( empty( $addon->url ) ) {
			$addon->url = '';
		}

		$addon->type      = $addons_type;
		$addon->installed = $installed;
		$addon->active    = $active;
		$addon->basename  = $plugin_basename;

		return $addon;
	}

	/**
	 * Use custom notices in the Vue app on the Settings screen.
	 */
	public function hide_old_notices() {

		global $wp_version;
		if ( version_compare( $wp_version, '4.6', '<' ) ) {
			// remove_all_actions triggers an infinite loop on older versions.
			return;
		}

		$screen = get_current_screen();
		// Bail if we're not on a ExactMetrics screen.
		if ( empty( $screen->id ) || strpos( $screen->id, 'exactmetrics' ) === false ) {
			return;
		}

		// Hide admin notices on the settings screen.
		if ( exactmetrics_is_settings_page() ) {
			remove_all_actions( 'admin_notices' );
		}

	}

	/**
	 * Update manual ua.
	 */
	public function update_manual_ua() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
			return;
		}

		$manual_ua_code = isset( $_POST['manual_ua_code'] ) ? sanitize_text_field( wp_unslash( $_POST['manual_ua_code'] ) ) : '';
		$manual_ua_code = exactmetrics_is_valid_ua( $manual_ua_code ); // Also sanitizes the string.
		if ( ! empty( $_REQUEST['isnetwork'] ) && sanitize_text_field( wp_unslash( $_REQUEST['isnetwork'] ) ) ) {
			define( 'WP_NETWORK_ADMIN', true );
		}
		$manual_ua_code_old = is_network_admin() ? ExactMetrics()->auth->get_network_manual_ua() : ExactMetrics()->auth->get_manual_ua();

		if ( $manual_ua_code && $manual_ua_code_old && $manual_ua_code_old === $manual_ua_code ) {
			// Same code we had before
			// Do nothing.
			wp_send_json_success();
		} else if ( $manual_ua_code && $manual_ua_code_old && $manual_ua_code_old !== $manual_ua_code ) {
			// Different UA code.
			if ( is_network_admin() ) {
				ExactMetrics()->auth->set_network_manual_ua( $manual_ua_code );
			} else {
				ExactMetrics()->auth->set_manual_ua( $manual_ua_code );
			}
		} else if ( $manual_ua_code && empty( $manual_ua_code_old ) ) {
			// Move to manual.
			if ( is_network_admin() ) {
				ExactMetrics()->auth->set_network_manual_ua( $manual_ua_code );
			} else {
				ExactMetrics()->auth->set_manual_ua( $manual_ua_code );
			}
		} else if ( empty( $manual_ua_code ) && $manual_ua_code_old ) {
			// Deleted manual.
			if ( is_network_admin() ) {
				ExactMetrics()->auth->delete_network_manual_ua();
			} else {
				ExactMetrics()->auth->delete_manual_ua();
			}
		} else if ( isset( $_POST['manual_ua_code'] ) && empty( $manual_ua_code ) ) {
			wp_send_json_error( array(
				'error' => __( 'Invalid UA code', 'google-analytics-dashboard-for-wp' ),
			) );
		}

		wp_send_json_success();
	}

	/**
	 *
	 */
	public function handle_settings_import() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
			return;
		}

		if ( ! isset( $_FILES['import_file'] ) ) {
			return;
		}

		$extension = explode( '.', sanitize_text_field( wp_unslash( $_FILES['import_file']['name'] ) ) );
		$extension = end( $extension );

		if ( 'json' !== $extension ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Please upload a valid .json file', 'google-analytics-dashboard-for-wp' ),
			) );
		}

		$import_file = sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) );

		$file = file_get_contents( $import_file );
		if ( empty( $file ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Please upload a file to import', 'google-analytics-dashboard-for-wp' ),
			) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$new_settings = json_decode( wp_json_encode( json_decode( $file ) ), true );
		$settings     = exactmetrics_get_options();
		$exclude      = array(
			'analytics_profile',
			'analytics_profile_code',
			'analytics_profile_name',
			'oauth_version',
			'cron_last_run',
			'exactmetrics_oauth_status',
		);

		foreach ( $exclude as $e ) {
			if ( ! empty( $new_settings[ $e ] ) ) {
				unset( $new_settings[ $e ] );
			}
		}

		if ( ! is_super_admin() ) {
			if ( ! empty( $new_settings['custom_code'] ) ) {
				unset( $new_settings['custom_code'] );
			}
		}

		foreach ( $exclude as $e ) {
			if ( ! empty( $settings[ $e ] ) ) {
				$new_settings = $settings[ $e ];
			}
		}

		global $exactmetrics_settings;
		$exactmetrics_settings = $new_settings;

		update_option( exactmetrics_get_option_name(), $new_settings );

		wp_send_json_success( $new_settings );

	}

	/**
	 * Generic Ajax handler for grabbing report data in JSON.
	 */
	public function get_report_data() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'exactmetrics_view_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( "You don't have permission to view ExactMetrics reports.", 'google-analytics-dashboard-for-wp' ) ) );
		}

		if ( ! empty( $_REQUEST['isnetwork'] ) && $_REQUEST['isnetwork'] ) {
			define( 'WP_NETWORK_ADMIN', true );
		}
		$settings_page = admin_url( 'admin.php?page=exactmetrics_settings' );

		// Only for Pro users, require a license key to be entered first so we can link to things.
		if ( exactmetrics_is_pro_version() ) {
			if ( ! ExactMetrics()->license->is_site_licensed() && ! ExactMetrics()->license->is_network_licensed() ) {
				wp_send_json_error( array(
					'message' => __( "You can't view ExactMetrics reports because you are not licensed.", 'google-analytics-dashboard-for-wp' ),
					'footer'  => '<a href="' . $settings_page . '">' . __( 'Add your license', 'google-analytics-dashboard-for-wp' ) . '</a>',
				) );
			} else if ( ExactMetrics()->license->is_site_licensed() && ! ExactMetrics()->license->site_license_has_error() ) {
				// Good to go: site licensed.
			} else if ( ExactMetrics()->license->is_network_licensed() && ! ExactMetrics()->license->network_license_has_error() ) {
				// Good to go: network licensed.
			} else {
				wp_send_json_error( array( 'message' => __( "You can't view ExactMetrics reports due to license key errors.", 'google-analytics-dashboard-for-wp' ) ) );
			}
		}

		// We do not have a current auth.
		$site_auth = ExactMetrics()->auth->get_viewname();
		$ms_auth   = is_multisite() && ExactMetrics()->auth->get_network_viewname();
		if ( ! $site_auth && ! $ms_auth ) {
			wp_send_json_error( array( 'message' => __( 'You must authenticate with ExactMetrics before you can view reports.', 'google-analytics-dashboard-for-wp' ) ) );
		}

		$report_name = isset( $_POST['report'] ) ? sanitize_text_field( wp_unslash( $_POST['report'] ) ) : '';

		if ( empty( $report_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'google-analytics-dashboard-for-wp' ) ) );
		}

		$report = ExactMetrics()->reporting->get_report( $report_name );

		$isnetwork = ! empty( $_REQUEST['isnetwork'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['isnetwork'] ) ) : '';
		$start     = ! empty( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : $report->default_start_date();
		$end       = ! empty( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : $report->default_end_date();

		$args      = array(
			'start' => $start,
			'end'   => $end,
		);

		if ( $isnetwork ) {
			$args['network'] = true;
		}

		if ( exactmetrics_is_pro_version() && ! ExactMetrics()->license->license_can( $report->level ) ) {
			$data = array(
				'success' => false,
				'error'   => 'license_level',
			);
		} else {
			$data = apply_filters( 'exactmetrics_vue_reports_data', $report->get_data( $args ), $report_name, $report );
		}

		if ( ! empty( $data['success'] ) && ! empty( $data['data'] ) ) {
			wp_send_json_success( $data['data'] );
		} else if ( isset( $data['success'] ) && false === $data['success'] && ! empty( $data['error'] ) ) {
			// Use a custom handler for invalid_grant errors.
			if ( strpos( $data['error'], 'invalid_grant' ) > 0 ) {
				wp_send_json_error(
					array(
						'message' => 'invalid_grant',
						'footer'  => '',
					)
				);
			}

			wp_send_json_error(
				array(
					'message' => $data['error'],
					'footer'  => isset( $data['data']['footer'] ) ? $data['data']['footer'] : '',
				)
			);
		}

		wp_send_json_error( array( 'message' => __( 'We encountered an error when fetching the report data.', 'google-analytics-dashboard-for-wp' ) ) );

	}

	/**
	 * Install plugins which are not addons.
	 */
	public function install_plugin() {
		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json( array(
				'message' => esc_html__( 'You are not allowed to install plugins', 'google-analytics-dashboard-for-wp' ),
			) );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : false;

		if ( ! $slug ) {
			wp_send_json( array(
				'message' => esc_html__( 'Missing plugin name.', 'google-analytics-dashboard-for-wp' ),
			) );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api( 'plugin_information', array(
			'slug'   => $slug,
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
		$url    = add_query_arg(
			array(
				'page' => 'exactmetrics-settings',
			),
			admin_url( 'admin.php' )
		);
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
		exactmetrics_require_upgrader();

		// Prevent language upgrade in ajax calls.
		remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
		// Create the plugin upgrader with our custom skin.
		$installer = new ExactMetrics_Plugin_Upgrader( new ExactMetrics_Skin() );
		$installer->install( $download_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();
		wp_send_json_success();

		wp_die();
	}

	/**
	 * Store that the first run notice has been dismissed so it doesn't show up again.
	 */
	public function dismiss_first_time_notice() {

		exactmetrics_update_option( 'exactmetrics_first_run_notice', true );

		wp_send_json_success();
	}

	/**
	 * Get the notice status by id.
	 */
	public function get_notice_status() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		$notice_id = empty( $_POST['notice'] ) ? false : sanitize_text_field( wp_unslash( $_POST['notice'] ) );
		if ( ! $notice_id ) {
			wp_send_json_error();
		}
		$is_dismissed = ExactMetrics()->notices->is_dismissed( $notice_id );

		wp_send_json_success( array(
			'dismissed' => $is_dismissed,
		) );
	}

	/**
	 * Dismiss notices by id.
	 */
	public function dismiss_notice() {

		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		$notice_id = empty( $_POST['notice'] ) ? false : sanitize_text_field( wp_unslash( $_POST['notice'] ) );
		if ( ! $notice_id ) {
			wp_send_json_error();
		}
		ExactMetrics()->notices->dismiss( $notice_id );

		wp_send_json_success();
	}
}
