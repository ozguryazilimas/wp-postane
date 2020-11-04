<?php
/**
 * Common admin class.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Common
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function exactmetrics_is_settings_page() {
	$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
	global $admin_page_hooks;

	if ( ! is_object( $current_screen ) || empty( $current_screen->id ) || empty( $admin_page_hooks ) ) {
		return false;
	}

	$settings_page = false;
	if ( ! empty( $admin_page_hooks['exactmetrics_settings'] ) && $current_screen->id === $admin_page_hooks['exactmetrics_settings'] ) {
		$settings_page = true;
	}

	if ( $current_screen->id === 'toplevel_page_exactmetrics_settings' ) {
		$settings_page = true;
	}

	if ( $current_screen->id === 'exactmetrics_page_exactmetrics_settings' ) {
		$settings_page = true;
	}

	if ( strpos( $current_screen->id, 'exactmetrics_settings' ) !== false ) {
		$settings_page = true;
	}

	if ( ! empty( $current_screen->base ) && strpos( $current_screen->base, 'exactmetrics_network' ) !== false ) {
		$settings_page = true;
	}

	return $settings_page;
}

/**
 * Determine if the current page is the Reports page.
 *
 * @return bool
 */
function exactmetrics_is_reports_page() {
	$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
	global $admin_page_hooks;

	if ( ! is_object( $current_screen ) || empty( $current_screen->id ) || empty( $admin_page_hooks ) ) {
		return false;
	}

	$reports_page = false;
	if ( ! empty( $admin_page_hooks['exactmetrics_reports'] ) && $current_screen->id === $admin_page_hooks['exactmetrics_reports'] ) {
		$reports_page = true;
	}

	if ( 'toplevel_page_exactmetrics_reports' === $current_screen->id ) {
		$reports_page = true;
	}

	if ( strpos( $current_screen->id, 'exactmetrics_reports' ) !== false ) {
		$reports_page = true;
	}

	return $reports_page;
}

/**
 * Loads styles for all ExactMetrics-based Administration Screens.
 *
 * @return null Return early if not on the proper screen.
 * @since 6.0.0
 * @access public
 *
 */
function exactmetrics_admin_styles() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Load Common admin styles.
	wp_register_style( 'exactmetrics-admin-common-style', plugins_url( 'assets/css/admin-common' . $suffix . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
	wp_enqueue_style( 'exactmetrics-admin-common-style' );

	// Get current screen.
	$screen = get_current_screen();

	// Bail if we're not on a ExactMetrics screen.
	if ( empty( $screen->id ) || strpos( $screen->id, 'exactmetrics' ) === false ) {
		return;
	}

	$version_path = exactmetrics_is_pro_version() ? 'pro' : 'lite';
	$rtl          = is_rtl() ? '.rtl' : '';

	// For the settings page, load the Vue app styles.
	if ( exactmetrics_is_settings_page() ) {
		if ( ! defined( 'EXACTMETRICS_LOCAL_JS_URL' ) ) {
			wp_enqueue_style( 'exactmetrics-vue-style-vendors', plugins_url( $version_path . '/assets/vue/css/chunk-vendors' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-style-common', plugins_url( $version_path . '/assets/vue/css/chunk-common' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-style', plugins_url( $version_path . '/assets/vue/css/settings' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
		}

		// Don't load other styles on the settings page.
		return;
	}

	if ( exactmetrics_is_reports_page() ) {
		if ( ! defined( 'EXACTMETRICS_LOCAL_REPORTS_JS_URL' ) ) {
			wp_enqueue_style( 'exactmetrics-vue-style-vendors', plugins_url( $version_path . '/assets/vue/css/chunk-vendors' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-style-common', plugins_url( $version_path . '/assets/vue/css/chunk-common' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
			wp_enqueue_style( 'exactmetrics-vue-style', plugins_url( $version_path . '/assets/vue/css/reports' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
		}

		return;
	}

	// Tooltips
	wp_enqueue_script( 'jquery-ui-tooltip' );
}

add_action( 'admin_enqueue_scripts', 'exactmetrics_admin_styles' );

/**
 * Loads scripts for all ExactMetrics-based Administration Screens.
 *
 * @return null Return early if not on the proper screen.
 * @since 6.0.0
 * @access public
 *
 */
function exactmetrics_admin_scripts() {

	// Our Common Admin JS.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script( 'exactmetrics-admin-common-script', plugins_url( 'assets/js/admin-common' . $suffix . '.js', EXACTMETRICS_PLUGIN_FILE ), array( 'jquery' ), exactmetrics_get_asset_version() );

	wp_enqueue_script( 'exactmetrics-admin-common-script' );

	wp_localize_script(
		'exactmetrics-admin-common-script',
		'exactmetrics_admin_common',
		array(
			'ajax'                 => admin_url( 'admin-ajax.php' ),
			'dismiss_notice_nonce' => wp_create_nonce( 'exactmetrics-dismiss-notice' ),
		)
	);

	// Get current screen.
	$screen = get_current_screen();

	// Bail if we're not on a ExactMetrics screen.
	if ( empty( $screen->id ) || strpos( $screen->id, 'exactmetrics' ) === false ) {
		return;
	}

	$version_path = exactmetrics_is_pro_version() ? 'pro' : 'lite';

	// For the settings page, load the Vue app.
	if ( exactmetrics_is_settings_page() ) {
		if ( ! defined( 'EXACTMETRICS_LOCAL_VENDORS_JS_URL' ) ) {
			wp_enqueue_script( 'exactmetrics-vue-vendors', plugins_url( $version_path . '/assets/vue/js/chunk-vendors.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-common', plugins_url( $version_path . '/assets/vue/js/chunk-common.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
		} else {
			wp_enqueue_script( 'exactmetrics-vue-vendors', EXACTMETRICS_LOCAL_VENDORS_JS_URL, array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-common', EXACTMETRICS_LOCAL_COMMON_JS_URL, array(), exactmetrics_get_asset_version(), true );
		}
		$app_js_url = defined( 'EXACTMETRICS_LOCAL_JS_URL' ) && EXACTMETRICS_LOCAL_JS_URL ? EXACTMETRICS_LOCAL_JS_URL : plugins_url( $version_path . '/assets/vue/js/settings.js', EXACTMETRICS_PLUGIN_FILE );
		wp_register_script( 'exactmetrics-vue-script', $app_js_url, array(), exactmetrics_get_asset_version(), true );
		wp_enqueue_script( 'exactmetrics-vue-script' );
		$plugins         = get_plugins();
		$install_amp_url = false;
		if ( exactmetrics_can_install_plugins() ) {
			$amp_key = 'amp/amp.php';
			if ( array_key_exists( $amp_key, $plugins ) ) {
				$install_amp_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $amp_key ), 'activate-plugin_' . $amp_key );
			} else {
				$install_amp_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=amp' ), 'install-plugin_amp' );
			}
		}
		$install_woocommerce_url = false;
		if ( exactmetrics_can_install_plugins() ) {
			$woo_key = 'woocommerce/woocommerce.php';
			if ( array_key_exists( $woo_key, $plugins ) ) {
				$install_woocommerce_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $woo_key ), 'activate-plugin_' . $woo_key );
			} else {
				$install_woocommerce_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
			}
		}
		$install_fbia_url = false;
		if ( exactmetrics_can_install_plugins() ) {
			$fbia_key = 'fb-instant-articles/facebook-instant-articles.php';
			if ( array_key_exists( $fbia_key, $plugins ) ) {
				$install_fbia_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $fbia_key ), 'activate-plugin_' . $fbia_key );
			} else {
				$install_fbia_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=fb-instant-articles' ), 'install-plugin_fb-instant-articles' );
			}
		}

		$prepared_dimensions = array();
		if ( class_exists( 'ExactMetrics_Admin_Custom_Dimensions' ) ) {
			$dimensions          = new ExactMetrics_Admin_Custom_Dimensions();
			$dimensions          = $dimensions->custom_dimensions();
			$prepared_dimensions = array();
			foreach ( $dimensions as $dimension_type => $dimension ) {
				$dimension['type']     = $dimension_type;
				$prepared_dimensions[] = $dimension;
			}
		}
		$is_authed = ( ExactMetrics()->auth->is_authed() || ExactMetrics()->auth->is_network_authed() );

		wp_localize_script(
			'exactmetrics-vue-script',
			'exactmetrics',
			array(
				'ajax'                            => admin_url( 'admin-ajax.php' ),
				'nonce'                           => wp_create_nonce( 'mi-admin-nonce' ),
				'network'                         => is_network_admin(),
				'translations'                    => wp_get_jed_locale_data( exactmetrics_is_pro_version() ? 'exactmetrics-premium' : 'google-analytics-dashboard-for-wp' ),
				'assets'                          => plugins_url( $version_path . '/assets/vue', EXACTMETRICS_PLUGIN_FILE ),
				'roles'                           => exactmetrics_get_roles(),
				'roles_manage_options'            => exactmetrics_get_manage_options_roles(),
				'shareasale_id'                   => exactmetrics_get_shareasale_id(),
				'shareasale_url'                  => exactmetrics_get_shareasale_url( exactmetrics_get_shareasale_id(), '' ),
				'addons_url'                      => is_multisite() ? network_admin_url( 'admin.php?page=exactmetrics_network#/addons' ) : admin_url( 'admin.php?page=exactmetrics_settings#/addons' ),
				'email_summary_url'               => admin_url( 'admin.php?exactmetrics_email_preview&exactmetrics_email_template=summary' ),
				'install_amp_url'                 => $install_amp_url,
				'install_fbia_url'                => $install_fbia_url,
				'install_woo_url'                 => $install_woocommerce_url,
				'dimensions'                      => $prepared_dimensions,
				'wizard_url'                      => is_network_admin() ? network_admin_url( 'index.php?page=exactmetrics-onboarding' ) : admin_url( 'index.php?page=exactmetrics-onboarding' ),
				'install_plugins'                 => exactmetrics_can_install_plugins(),
				'unfiltered_html'                 => current_user_can( 'unfiltered_html' ),
				'activate_nonce'                  => wp_create_nonce( 'exactmetrics-activate' ),
				'deactivate_nonce'                => wp_create_nonce( 'exactmetrics-deactivate' ),
				'install_nonce'                   => wp_create_nonce( 'exactmetrics-install' ),
				// Used to add notices for future deprecations.
				'versions'                        => exactmetrics_get_php_wp_version_warning_data(),
				'plugin_version'                  => EXACTMETRICS_VERSION,
				'is_admin'                        => true,
				'admin_email'                     => get_option( 'admin_email' ),
				'site_url'                        => get_site_url(),
				'reports_url'                     => add_query_arg( 'page', 'exactmetrics_reports', admin_url( 'admin.php' ) ),
				'first_run_notice'                => apply_filters( 'exactmetrics_settings_first_time_notice_hide', exactmetrics_get_option( 'exactmetrics_first_run_notice' ) ),
				'getting_started_url'             => is_network_admin() ? network_admin_url( 'admin.php?page=exactmetrics_network#/about' ) : admin_url( 'admin.php?page=exactmetrics_settings#/about/getting-started' ),
				'authed'                          => $is_authed,
				'new_pretty_link_url'             => admin_url( 'post-new.php?post_type=pretty-link' ),
				'wpmailsmtp_admin_url'            => admin_url( 'admin.php?page=wp-mail-smtp' ),
				'load_headline_analyzer_settings' => exactmetrics_load_gutenberg_app() ? 'true' : 'false',
			)
		);

		// Don't load other scripts on the settings page.
		return;
	}

	if ( exactmetrics_is_reports_page() ) {
		global $wp_version;
		if ( ! defined( 'EXACTMETRICS_LOCAL_VENDORS_JS_URL' ) ) {
			wp_enqueue_script( 'exactmetrics-vue-vendors', plugins_url( $version_path . '/assets/vue/js/chunk-vendors.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-common', plugins_url( $version_path . '/assets/vue/js/chunk-common.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
		} else {
			wp_enqueue_script( 'exactmetrics-vue-vendors', EXACTMETRICS_LOCAL_VENDORS_JS_URL, array(), exactmetrics_get_asset_version(), true );
			wp_enqueue_script( 'exactmetrics-vue-common', EXACTMETRICS_LOCAL_COMMON_JS_URL, array(), exactmetrics_get_asset_version(), true );
		}
		$app_js_url = defined( 'EXACTMETRICS_LOCAL_REPORTS_JS_URL' ) && EXACTMETRICS_LOCAL_REPORTS_JS_URL ? EXACTMETRICS_LOCAL_REPORTS_JS_URL : plugins_url( $version_path . '/assets/vue/js/reports.js', EXACTMETRICS_PLUGIN_FILE );
		wp_register_script( 'exactmetrics-vue-reports', $app_js_url, array(), exactmetrics_get_asset_version(), true );
		wp_enqueue_script( 'exactmetrics-vue-reports' );

		// We do not have a current auth.
		$site_auth = ExactMetrics()->auth->get_viewname();
		$ms_auth   = is_multisite() && ExactMetrics()->auth->get_network_viewname();

		wp_localize_script(
			'exactmetrics-vue-reports',
			'exactmetrics',
			array(
				'ajax'             => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'mi-admin-nonce' ),
				'network'          => is_network_admin(),
				'translations'     => wp_get_jed_locale_data( exactmetrics_is_pro_version() ? 'exactmetrics-premium' : 'google-analytics-dashboard-for-wp' ),
				'assets'           => plugins_url( $version_path . '/assets/vue', EXACTMETRICS_PLUGIN_FILE ),
				'shareasale_id'    => exactmetrics_get_shareasale_id(),
				'shareasale_url'   => exactmetrics_get_shareasale_url( exactmetrics_get_shareasale_id(), '' ),
				'addons_url'       => is_multisite() ? network_admin_url( 'admin.php?page=exactmetrics_network#/addons' ) : admin_url( 'admin.php?page=exactmetrics_settings#/addons' ),
				'timezone'         => date( 'e' ),
				'authed'           => $site_auth || $ms_auth,
				'settings_url'     => add_query_arg( 'page', 'exactmetrics_settings', admin_url( 'admin.php' ) ),
				// Used to add notices for future deprecations.
				'versions'         => exactmetrics_get_php_wp_version_warning_data(),
				'plugin_version'   => EXACTMETRICS_VERSION,
				'is_admin'         => true,
				'admin_email'      => get_option( 'admin_email' ),
				'site_url'         => get_site_url(),
				'wizard_url'       => is_network_admin() ? network_admin_url( 'index.php?page=exactmetrics-onboarding' ) : admin_url( 'index.php?page=exactmetrics-onboarding' ),
				'install_nonce'    => wp_create_nonce( 'exactmetrics-install' ),
				'activate_nonce'   => wp_create_nonce( 'exactmetrics-activate' ),
				'deactivate_nonce' => wp_create_nonce( 'exactmetrics-deactivate' ),
				'update_settings'  => current_user_can( 'exactmetrics_save_settings' ),
				'migrated'         => exactmetrics_get_option( 'gadwp_migrated', 0 ),
			)
		);

		return;
	}
	// ublock notice
	add_action( 'admin_print_footer_scripts', 'exactmetrics_settings_ublock_error_js', 9999999 );
}

add_action( 'admin_enqueue_scripts', 'exactmetrics_admin_scripts' );

/**
 * Remove Assets that conflict with ours from our screens.
 *
 * @return null Return early if not on the proper screen.
 * @since 6.0.4
 * @access public
 *
 */
function exactmetrics_remove_conflicting_asset_files() {

	// Get current screen.
	$screen = get_current_screen();

	// Bail if we're not on a ExactMetrics screen.
	if ( empty( $screen->id ) || strpos( $screen->id, 'exactmetrics' ) === false ) {
		return;
	}

	$styles = array(
		'kt_admin_css', // Pinnacle theme
		'select2-css', // Schema theme
		'tweetshare_style', // TweetShare - Click To Tweet
		'tweetshare_custom_style', // TweetShare - Click To Tweet
		'tweeetshare_custome_style', // TweetShare - Click To Tweet
		'tweeetshare_notice_style', // TweetShare - Click To Tweet
		'tweeetshare_theme_style', // TweetShare - Click To Tweet
		'tweeetshare_tweet_box_style', // TweetShare - Click To Tweet
		'soultype2-admin', // SoulType Plugin
		'thesis-options-stylesheet', // Thesis Options Stylesheet
		'imagify-sweetalert-core', // Imagify
		'imagify-sweetalert', // Imagify
		'smls-backend-style', // Smart Logo Showcase Lite
		'wp-reactjs-starter', // wp-real-media-library
		'control-panel-modal-plugin', // Ken Theme
		'theme-admin-css', // Vitrine Theme
		'qi-framework-styles', //  Artisan Nayma Theme
		'artisan-pages-style', // Artisan Pages Plugin
		'control-panel-modal-plugin', // Ken Theme
		'sweetalert', //  Church Suite Theme by Webnus
		'woo_stock_alerts_admin_css', // WooCommerce bolder product alerts
		'custom_wp_admin_css', // Fix for Add Social Share
		'fo_css', // Fix for Add Social Share
		'font_css', // Fix for Add Social Share
		'font2_css', // Fix for Add Social Share
		'font3_css', // Fix for Add Social Share
		'hover_css', // Fix for Add Social Share
		'fontend_styling', // Fix for Add Social Share
		'datatable', // WP Todo
		'bootstrap', // WP Todo
		'flipclock', // WP Todo
		'repuso_css_admin', // Social testimonials and reviews by Repuso
	);

	$scripts = array(
		'kad_admin_js', // Pinnacle theme
		'dt-chart', // DesignThemes core features plugin
		'tweeetshare_font_script', // TweetShare - Click To Tweet
		'tweeetshare_jquery_script',  // TweetShare - Click To Tweet
		'tweeetshare_jqueryui_script', // TweetShare - Click To Tweet
		'tweeetshare_custom_script', // TweetShare - Click To Tweet
		'imagify-promise-polyfill', // Imagify
		'imagify-sweetalert', // Imagify
		'imagify-chart', // Imagify
		'chartjs', // Comet Cache Pro
		'wp-reactjs-starter', // wp-real-media-library
		'jquery-tooltipster', // WP Real Media Library
		'jquery-nested-sortable', // WP Real Media Library
		'jquery-aio-tree', // WP Real Media Library
		'wp-media-picker', // WP Real Media Library
		'rml-general', // WP Real Media Library
		'rml-library', // WP Real Media Library
		'rml-grid', // WP Real Media Library
		'rml-list', // WP Real Media Library
		'rml-modal', // WP Real Media Library
		'rml-order', // WP Real Media Library
		'rml-meta', // WP Real Media Library
		'rml-uploader',  // WP Real Media Library
		'rml-options',  // WP Real Media Library
		'rml-usersettings',  // WP Real Media Library
		'rml-main', // WP Real Media Library
		'control-panel-sweet-alert', // Ken Theme
		'sweet-alert-js', // Vitrine Theme
		'theme-admin-script', // Vitrine Theme
		'sweetalert', //  Church Suite Theme by Webnus
		'be_alerts_charts', //  WooCommerce bolder product alerts
		'magayo-lottery-results',  //  Magayo Lottery Results
		'control-panel-sweet-alert', // Ken Theme
		'cpm_chart', // WP Project Manager
		'adminscripts', //  Artisan Nayma Theme
		'artisan-pages-script', // Artisan Pages Plugin
		'tooltipster', // Grand News Theme
		'fancybox', // Grand News Theme
		'grandnews-admin-cript', // Grand News Theme
		'colorpicker', // Grand News Theme
		'eye', // Grand News Theme
		'icheck', // Grand News Theme
		'learn-press-chart', //  LearnPress
		'theme-script-main', //  My Listing Theme by 27collective
		'selz', //  Selz eCommerce
		'tie-admin-scripts', //   Tie Theme
		'blossomthemes-toolkit', //   BlossomThemes Toolkit
		'illdy-widget-upload-image', //   Illdy Companion By Colorlib
		'moment.js', // WooCommerce Table Rate Shipping
		'default', //   Bridge Theme
		'qode-tax-js', //   Bridge Theme
		'wc_smartship_moment_js', // WooCommerce Posti SmartShip by markup.fi
		'ecwid-admin-js', // Fixes Conflict for Ecwid Shopping Cart
		'td-wp-admin-js', // Newspaper by tagDiv
		'moment', // Screets Live Chat
		'wpmf-base', //  WP Media Folder Fix
		'wpmf-media-filters', //  WP Media Folder Fix
		'wpmf-folder-tree', //  WP Media Folder Fix
		'wpmf-assign-tree', //  WP Media Folder Fix
		'js_files_for_wp_admin', //  TagDiv Composer Fix
		'tdb_js_files_for_wp_admin_last', //  TagDiv Composer Fix
		'tdb_js_files_for_wp_admin', //  TagDiv Composer Fix
		'wd-functions', //  affiliate boxes
		'ellk-aliExpansion', // Ali Dropship Plugin
		'ftmetajs', // Houzez Theme
		'qode_admin_default', //  Fix For Stockholm Theme
		'qodef-tax-js', // Fix for Prowess Theme
		'qodef-user-js', // Fix for Prowess Theme
		'qodef-ui-admin', // Fix for Prowess Theme
		'ssi_script', // Fix for Add Social Share
		'live_templates', // Fix for Add Social Share
		'default', // Fix for Add Social Share
		'handsontable', // Fix WP Tables
		'moment-js', // Magee Shortcodes
		'postbox', // Scripts from wp-admin enqueued everywhere by WP Posts Filter
		'link', // Scripts from wp-admin enqueued everywhere by WP Posts Filter
		'wpvr_scripts', // WP Video Robot
		'wpvr_scripts_loaded', // WP Video Robot
		'wpvr_scripts_assets', // WP Video Robot
		'writee_widget_admin', // Fix for the Writtee theme
		'__ytprefs_admin__', // Fix for YouTube by EmbedPlus plugin
		'momentjs', // Fix for Blog Time plugin
		'c2c_BlogTime', //  Fix for Blog Time plugin
		'material-wp', // Fix for MaterialWP plugin
		'wp-color-picker-alpha', // Fix for MaterialWP plugin
		'grandtour-theme-script', // Grandtour Theme
		'swifty-img-widget-admin-script', // Fix for Swifty Image Widget
		'datatable', // WP Todo
		'flipclock', // WP Todo
		'bootstrap', // WP Todo
		'repuso_js_admin', // Social testimonials and reviews by Repuso
		'chart', // Video Mate Pro Theme
		'reuse_vendor', // RedQ Reuse Form
		'jetpack-onboarding-vendor', // Jetpack Onboarding Bluehost
		'date-js', // Google Analytics by Web Dorado
	);

	if ( ! empty( $styles ) ) {
		foreach ( $styles as $style ) {
			wp_dequeue_style( $style ); // Remove CSS file from MI screen
			wp_deregister_style( $style );
		}
	}
	if ( ! empty( $scripts ) ) {
		foreach ( $scripts as $script ) {
			wp_dequeue_script( $script ); // Remove JS file from MI screen
			wp_deregister_script( $script );
		}
	}

	$third_party = array(
		'select2',
		'sweetalert',
		'clipboard',
		'matchHeight',
		'inputmask',
		'jquery-confirm',
		'list',
		'toastr',
		'tooltipster',
		'flag-icon',
		'bootstrap',
	);

	global $wp_styles;
	foreach ( $wp_styles->queue as $handle ) {
		if ( strpos( $wp_styles->registered[ $handle ]->src, 'wp-content' ) === false ) {
			return;
		}

		if ( strpos( $wp_styles->registered[ $handle ]->handle, 'exactmetrics' ) !== false ) {
			return;
		}

		foreach ( $third_party as $partial ) {
			if ( strpos( $wp_styles->registered[ $handle ]->handle, $partial ) !== false ) {
				wp_dequeue_style( $handle ); // Remove css file from MI screen
				wp_deregister_style( $handle );
				break;
			} else if ( strpos( $wp_styles->registered[ $handle ]->src, $partial ) !== false ) {
				wp_dequeue_style( $handle ); // Remove css file from MI screen
				wp_deregister_style( $handle );
				break;
			}
		}
	}

	global $wp_scripts;
	foreach ( $wp_scripts->queue as $handle ) {
		if ( strpos( $wp_scripts->registered[ $handle ]->src, 'wp-content' ) === false ) {
			return;
		}

		if ( strpos( $wp_scripts->registered[ $handle ]->handle, 'exactmetrics' ) !== false ) {
			return;
		}

		foreach ( $third_party as $partial ) {
			if ( strpos( $wp_scripts->registered[ $handle ]->handle, $partial ) !== false ) {
				wp_dequeue_script( $handle ); // Remove JS file from MI screen
				wp_deregister_script( $handle );
				break;
			} else if ( strpos( $wp_scripts->registered[ $handle ]->src, $partial ) !== false ) {
				wp_dequeue_script( $handle ); // Remove JS file from MI screen
				wp_deregister_script( $handle );
				break;
			}
		}
	}

	// Remove actions from themes that are not following best practices and break the admin doing so
	// Theme: Newspaper by tagDiv
	remove_action( 'admin_enqueue_scripts', 'load_wp_admin_js' );
	remove_action( 'admin_enqueue_scripts', 'load_wp_admin_css' );
	remove_action( 'admin_print_scripts-widgets.php', 'td_on_admin_print_scripts_farbtastic' );
	remove_action( 'admin_print_styles-widgets.php', 'td_on_admin_print_styles_farbtastic' );
	remove_action( 'admin_print_footer_scripts', 'check_if_media_uploads_is_loaded', 9999 );
	remove_action( 'print_media_templates', 'td_custom_gallery_settings_hook' );
	remove_action( 'print_media_templates', 'td_change_backbone_js_hook' );
	remove_action( 'admin_head', 'tdc_on_admin_head' ); //  TagDiv Composer Fix
	remove_action( 'print_media_templates', 'us_media_templates' ); // Impreza Theme Fix
	remove_action( 'admin_footer', 'gt3pg_add_gallery_template' ); // GT3 Photo & Video Gallery By GT3 Themes Plugin Fix
	// Plugin WP Booklist:
	remove_action( 'admin_footer', 'wpbooklist_jre_dismiss_prem_notice_forever_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_add_book_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_show_form_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_show_book_in_colorbox_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_new_lib_shortcode_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_save_library_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_save_post_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_save_page_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_update_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_pagination_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_switch_lib_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_search_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_actual_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_delete_book_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_user_apis_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_upload_new_stylepak_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_upload_new_post_template_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_upload_new_page_template_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_create_db_library_backup_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_restore_db_library_backup_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_create_csv_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_amazon_localization_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_delete_book_bulk_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_reorder_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_exit_results_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_select_category_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_get_story_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_expand_browse_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_save_settings_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_delete_story_action_javascript' );
}

add_action( 'admin_enqueue_scripts', 'exactmetrics_remove_conflicting_asset_files', 9999 );

/**
 * Remove non-MI notices from MI page.
 *
 * @return null Return early if not on the proper screen.
 * @since 6.0.0
 * @access public
 *
 */
function hide_non_exactmetrics_warnings() {
	// Bail if we're not on a ExactMetrics screen.
	if ( empty( $_REQUEST['page'] ) || strpos( $_REQUEST['page'], 'exactmetrics' ) === false ) {
		return;
	}

	global $wp_filter;
	if ( ! empty( $wp_filter['user_admin_notices']->callbacks ) && is_array( $wp_filter['user_admin_notices']->callbacks ) ) {
		foreach ( $wp_filter['user_admin_notices']->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
					unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
					continue;
				}
				if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'exactmetrics' ) !== false ) {
					continue;
				}
				if ( ! empty( $name ) && strpos( $name, 'exactmetrics' ) === false ) {
					unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}

	if ( ! empty( $wp_filter['admin_notices']->callbacks ) && is_array( $wp_filter['admin_notices']->callbacks ) ) {
		foreach ( $wp_filter['admin_notices']->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
					unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
					continue;
				}
				if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'exactmetrics' ) !== false ) {
					continue;
				}
				if ( ! empty( $name ) && strpos( $name, 'exactmetrics' ) === false ) {
					unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}

	if ( ! empty( $wp_filter['all_admin_notices']->callbacks ) && is_array( $wp_filter['all_admin_notices']->callbacks ) ) {
		foreach ( $wp_filter['all_admin_notices']->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
					unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
					continue;
				}
				if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'exactmetrics' ) !== false ) {
					continue;
				}
				if ( ! empty( $name ) && strpos( $name, 'exactmetrics' ) === false ) {
					unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}
}

add_action( 'admin_print_scripts', 'hide_non_exactmetrics_warnings' );
add_action( 'admin_head', 'hide_non_exactmetrics_warnings', PHP_INT_MAX );

/**
 * Called whenever an upgrade button / link is displayed in Lite, this function will
 * check if there's a shareasale ID specified.
 *
 * There are three ways to specify an ID, ordered by highest to lowest priority
 * - add_filter( 'exactmetrics_shareasale_id', function() { return 1234; } );
 * - define( 'EXACTMETRICS_SHAREASALE_ID', 1234 );
 * - get_option( 'exactmetrics_shareasale_id' ); (with the option being in the wp_options table)
 *
 * If an ID is present, returns the ShareASale link with the affiliate ID, and tells
 * ShareASale to then redirect to exactmetrics.com/lite
 *
 * If no ID is present, just returns the exactmetrics.com/lite URL with UTM tracking.
 *
 * @return string Upgrade link.
 * @since 6.0.0
 * @access public
 *
 */
function exactmetrics_get_upgrade_link( $medium = '', $campaign = '', $url = '' ) {
	$url = exactmetrics_get_url( $medium, $campaign, $url, false );

	if ( exactmetrics_is_pro_version() ) {
		return esc_url( $url );
	}

	// Get the ShareASale ID
	$shareasale_id = exactmetrics_get_shareasale_id();

	// If we have a shareasale ID return the shareasale url
	if ( ! empty( $shareasale_id ) ) {
		$shareasale_id = absint( $shareasale_id );

		return esc_url( exactmetrics_get_shareasale_url( $shareasale_id, $url ) );
	} else {
		return esc_url( $url );
	}
}

function exactmetrics_get_url( $medium = '', $campaign = '', $url = '', $escape = true ) {
	// Setup Campaign variables
	$source      = exactmetrics_is_pro_version() ? 'proplugin' : 'liteplugin';
	$medium      = ! empty( $medium ) ? $medium : 'defaultmedium';
	$campaign    = ! empty( $campaign ) ? $campaign : 'defaultcampaign';
	$content     = EXACTMETRICS_VERSION;
	$default_url = exactmetrics_is_pro_version() ? '' : 'lite/';
	$url         = ! empty( $url ) ? $url : 'https://www.exactmetrics.com/' . $default_url;

	// Put together redirect URL
	$url = add_query_arg(
		array(
			'utm_source'   => $source,   // Pro/Lite Plugin
			'utm_medium'   => sanitize_key( $medium ),   // Area of ExactMetrics (example Reports)
			'utm_campaign' => sanitize_key( $campaign ), // Which link (example eCommerce Report)
			'utm_content'  => $content,  // Version number of MI
		),
		trailingslashit( $url )
	);

	if ( $escape ) {
		return esc_url( $url );
	} else {
		return $url;
	}
}

function exactmetrics_settings_ublock_error_js() {
	echo "<script type='text/javascript'>\n";
	echo "jQuery( document ).ready( function( $ ) {
			if ( window.uorigindetected == null){
			   $('#exactmetrics-ublock-origin-error').show();
			   $('.exactmetrics-nav-tabs').hide();
			   $('.exactmetrics-nav-container').hide();
			   $('#exactmetrics-addon-heading').hide();
			   $('#exactmetrics-addons').hide();
			   $('#exactmetrics-reports').hide();
			}
		});";
	echo "\n</script>";
}

function exactmetrics_ublock_notice() {
	ob_start(); ?>
	<div id="exactmetrics-ublock-origin-error" class="error inline" style="display:none;">
		<?php
		// Translators: Placeholders are for links to fix the issue.
		echo sprintf( esc_html__( 'ExactMetrics has detected that it\'s files are being blocked. This is usually caused by a adblock browser plugin (particularly uBlock Origin), or a conflicting WordPress theme or plugin. This issue only affects the admin side of ExactMetrics. To solve this, ensure ExactMetrics is whitelisted for your website URL in any adblock browser plugin you use. For step by step directions on how to do this, %1$sclick here%2$s. If this doesn\'t solve the issue (rare), send us a ticket %3$shere%2$s and we\'ll be happy to help diagnose the issue.', 'google-analytics-dashboard-for-wp' ), '<a href="https://exactmetrics.com/docs/exactmetrics-asset-files-blocked/" target="_blank" rel="noopener noreferrer" referrer="no-referrer">', '</a>', '<a href="https://exactmetrics.com/contact/" target="_blank" rel="noopener noreferrer" referrer="no-referrer">' );
		?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Some themes/plugins don't add proper checks and load JS code in all admin pages causing conflicts.
 */
function exactmetrics_remove_unnecessary_footer_hooks() {

	$screen = get_current_screen();
	// Bail if we're not on a ExactMetrics screen.
	if ( empty( $screen->id ) || strpos( $screen->id, 'exactmetrics' ) === false ) {
		return;
	}

	// Remove js code added by Newspaper theme - version 8.8.0.
	remove_action( 'print_media_templates', 'td_custom_gallery_settings_hook' );
	remove_action( 'print_media_templates', 'td_change_backbone_js_hook' );
	// Remove js code added by the Brooklyn theme - version 4.5.3.1.
	remove_action( 'print_media_templates', 'ut_create_gallery_options' );

	// Remove js code added by WordPress Book List Plugin - version 5.8.1.
	remove_action( 'admin_footer', 'wpbooklist_jre_dismiss_prem_notice_forever_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_add_book_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_show_form_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_show_book_in_colorbox_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_new_lib_shortcode_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_save_library_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_save_post_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_dashboard_save_page_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_update_display_options_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_pagination_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_switch_lib_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_search_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_edit_book_actual_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_delete_book_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_user_apis_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_upload_new_stylepak_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_upload_new_post_template_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_upload_new_page_template_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_create_db_library_backup_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_restore_db_library_backup_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_create_csv_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_amazon_localization_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_delete_book_bulk_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_reorder_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_exit_results_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_select_category_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_get_story_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_expand_browse_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_storytime_save_settings_action_javascript' );
	remove_action( 'admin_footer', 'wpbooklist_delete_story_action_javascript' );
}

add_action( 'admin_head', 'exactmetrics_remove_unnecessary_footer_hooks', 15 );

/**
 * Display dismissable admin pointer for year in review 2019 report
 *
 */
function exactmetrics_yearinreview_admin_menu_tooltip() {

	$dismiss_tooltip     = get_option( 'exactmetrics_yearinreview_dismiss_admin_tooltip', false );
	$activated           = get_option( 'exactmetrics_over_time', array() );
	$ua_code             = exactmetrics_get_ua();
	$dashboards_disabled = exactmetrics_get_option( 'dashboards_disabled', false );

	if ( $dashboards_disabled ) {
		return;
	}

	if ( ! current_user_can( 'exactmetrics_view_dashboard' ) ) {
		return;
	}

	if ( exactmetrics_is_reports_page() || exactmetrics_is_settings_page() ) {
		// Don't show on MI pages.
		return;
	}

	// equivalent to: 01/01/2020 @ 12:00am (UTC)
	$new_year = '1577836800';

	// equivalent to: 01/02/2020 @ 12:00am (UTC)
	$start_time = '1577923200';

	// equivalent to: 01/13/2020 @ 12:00am (UTC)
	$end_time = '1578873600';

	if ( $dismiss_tooltip ) {
		return;
	}

	// don't show before January 02, 2020
	if ( $start_time > time() ) {
		return;
	}

	// don't show after January 13, 2020
	if ( $end_time < time() ) {
		return;
	}

	if ( empty( $activated['connected_date'] ) || ( $activated['connected_date'] > $new_year ) || empty( $ua_code ) ) {
		return;
	}

	// remove lite upsell
	remove_action( 'adminmenu', 'exactmetrics_get_admin_menu_tooltip' );

	$url = admin_url( 'admin.php?page=exactmetrics_reports#/year-in-review' );
	?>
	<div id="exactmetrics-yearinreview-admin-menu-tooltip" class="exactmetrics-yearinreview-admin-menu-tooltip-hide">
		<div class="exactmetrics-yearinreview-admin-menu-tooltip-header">
			<span class="exactmetrics-yearinreview-admin-menu-tooltip-icon">
				<span class="dashicons dashicons-megaphone"></span>
			</span>
			<?php esc_html_e( 'Your 2019 Analytics Report', 'google-analytics-dashboard-for-wp' ); ?>
			<a href="#" class="exactmetrics-yearinreview-admin-menu-tooltip-close">
				<span class="dashicons dashicons-dismiss"></span>
			</a>
		</div>
		<div class="exactmetrics-yearinreview-admin-menu-tooltip-content">
			<strong><?php esc_html_e( 'See how your website performed this year and find tips along the way to help grow even more in 2020!', 'google-analytics-dashboard-for-wp' ); ?></strong>
			<p>
				<a href="<?php echo esc_url( $url ); ?>" class="button button-primary exactmetrics-yearinreview-admin-menu-tooltip-btn-link">
					<?php esc_html_e( 'View 2019 Year in Review report!', 'google-analytics-dashboard-for-wp' ); ?>
				</a>
			</p>
		</div>
	</div>
	<style type="text/css">
		#exactmetrics-yearinreview-admin-menu-tooltip {
			position: absolute;
			left: 100%;
			top: 100%;
			background: #fff;
			margin-left: 16px;
			width: 350px;
			box-shadow: 0px 4px 7px 0px #ccc;
		}

		#exactmetrics-yearinreview-admin-menu-tooltip:before {
			content: '';
			width: 0;
			height: 0;
			border-style: solid;
			border-width: 12px 12px 12px 0;
			border-color: transparent #fff transparent transparent;
			position: absolute;
			right: 100%;
			top: 130px;
			z-index: 10;
		}

		#exactmetrics-yearinreview-admin-menu-tooltip:after {
			content: '';
			width: 0;
			height: 0;
			border-style: solid;
			border-width: 13px 13px 13px 0;
			border-color: transparent #ccc transparent transparent;
			position: absolute;
			right: 100%;
			margin-left: -1px;
			top: 129px;
			z-index: 5;
		}

		#exactmetrics-yearinreview-admin-menu-tooltip.exactmetrics-yearinreview-tooltip-arrow-top:before {
			top: 254px;
		}

		#exactmetrics-yearinreview-admin-menu-tooltip.exactmetrics-yearinreview-tooltip-arrow-top:after {
			top: 253px;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-header {
			background: #03a0d2;
			padding: 5px 12px;
			font-size: 14px;
			font-weight: 700;
			font-family: Arial, Helvetica, "Trebuchet MS", sans-serif;
			color: #fff;
			line-height: 1.6;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-icon {
			background: #fff;
			border-radius: 50%;
			width: 28px;
			height: 25px;
			display: inline-block;
			color: #03a0d2;
			text-align: center;
			padding: 3px 0 0;
			margin-right: 6px;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-hide {
			display: none;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-content {
			padding: 20px 15px 7px;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-content strong {
			font-size: 14px;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-content p strong {
			font-size: 13px;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-close {
			color: #fff;
			text-decoration: none;
			position: absolute;
			right: 10px;
			top: 12px;
			display: block;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-close:hover {
			color: #fff;
			text-decoration: none;
		}

		.exactmetrics-yearinreview-admin-menu-tooltip-close .dashicons {
			font-size: 14px;
		}

		@media ( max-width: 782px ) {
			#exactmetrics-yearinreview-admin-menu-tooltip {
				display: none;
			}
		}
	</style>
	<script type="text/javascript">
		if ( 'undefined' !== typeof jQuery ) {
			jQuery( function ( $ ) {
				var $tooltip = $( document.getElementById( 'exactmetrics-yearinreview-admin-menu-tooltip' ) );
				var $menuwrapper = $( document.getElementById( 'adminmenuwrap' ) );
				var $menuitem = $( document.getElementById( 'toplevel_page_exactmetrics_reports' ) );
				if ( 0 === $menuitem.length ) {
					$menuitem = $( document.getElementById( 'toplevel_page_exactmetrics_network' ) );
				}

				if ( $menuitem.length ) {
					$menuwrapper.append( $tooltip );
					$tooltip.removeClass( 'exactmetrics-yearinreview-admin-menu-tooltip-hide' );
				}

				function alignTooltip() {
					var sticky = $( 'body' ).hasClass( 'sticky-menu' );

					var menuitem_pos = $menuitem.position();
					var tooltip_top = menuitem_pos.top - 124;
					if ( sticky && $( window ).height() > $menuwrapper.height() + 150 ) {
						$tooltip.removeClass( 'exactmetrics-yearinreview-tooltip-arrow-top' );
					} else {
						tooltip_top = menuitem_pos.top - 250;
						$tooltip.addClass( 'exactmetrics-yearinreview-tooltip-arrow-top' );
					}
					// Don't let the tooltip go outside of the screen and make the close button not visible.
					if ( tooltip_top < 40 ) {
						tooltip_top = 40;
					}
					$tooltip.css( {
						top: tooltip_top + 'px'
					} );
				}

				var $document = $( document );
				var timeout = setTimeout( alignTooltip, 10 );
				$document.on( 'wp-pin-menu wp-window-resized.pin-menu postboxes-columnchange.pin-menu postbox-toggled.pin-menu wp-collapse-menu.pin-menu wp-scroll-start.pin-menu', function () {
					if ( timeout ) {
						clearTimeout( timeout );
					}
					timeout = setTimeout( alignTooltip, 10 );
				} );

				$( '.exactmetrics-yearinreview-admin-menu-tooltip-btn-link' ).on( 'click', function ( e ) {
					hideYearInReviewTooltip();
				} );

				$( '.exactmetrics-yearinreview-admin-menu-tooltip-close' ).on( 'click', function ( e ) {
					e.preventDefault();
					hideYearInReviewTooltip();
				} );

				function hideYearInReviewTooltip() {
					$tooltip.addClass( 'exactmetrics-yearinreview-admin-menu-tooltip-hide' );
					$.post( ajaxurl, {
						action: 'exactmetrics_yearinreview_hide_admin_tooltip',
						nonce: '<?php echo esc_js( wp_create_nonce( 'mi-admin-nonce' ) ); ?>',
					} );
				}
			} );
		}
	</script>
	<?php
}

add_action( 'adminmenu', 'exactmetrics_yearinreview_admin_menu_tooltip', 5 );

/**
 * Store the time when the year in review tooltip was hidden so it won't show again
 */
function exactmetrics_mark_yearinreview_tooltip_hidden() {
	check_ajax_referer( 'mi-admin-nonce', 'nonce' );
	update_option( 'exactmetrics_yearinreview_dismiss_admin_tooltip', true );
	wp_send_json_success();
}

add_action( 'wp_ajax_exactmetrics_yearinreview_hide_admin_tooltip', 'exactmetrics_mark_yearinreview_tooltip_hidden' );

/**
 * Prevent plugins/themes from removing the version number from scripts loaded by our plugin.
 * Ideally those plugins/themes would follow WordPress coding best practices, but in lieu of that
 * we can at least attempt to prevent 99% of them from doing bad things.
 *
 * @param string $src The script source.
 *
 * @return string
 */
function exactmetrics_prevent_version_number_removal( $src ) {
	// Apply this only to admin-side scripts.
	if ( ! is_admin() ) {
		return $src;
	}

	// Make sure are only changing our scripts and only if the version number is missing.
	if ( ( false !== strpos( $src, 'exactmetrics' ) || false !== strpos( $src, 'google-analytics-dashboard-for-wp' ) || false !== strpos( $src, 'exactmetrics-premium' ) ) && false === strpos( $src, '?ver' ) ) {
		$src = add_query_arg( 'ver', exactmetrics_get_asset_version(), $src );
	}

	return $src;
}

add_filter( 'script_loader_src', 'exactmetrics_prevent_version_number_removal', 9999, 1 );
add_filter( 'style_loader_src', 'exactmetrics_prevent_version_number_removal', 9999, 1 );

/**
 * Data used for the Vue scripts to display old PHP and WP versions warnings.
 */
function exactmetrics_get_php_wp_version_warning_data() {
	global $wp_version;

	return array(
		'php_version'          => phpversion(),
		'php_version_below_54' => apply_filters( 'exactmetrics_temporarily_hide_php_under_56_upgrade_warnings', version_compare( phpversion(), '5.6', '<' ) ),
		'php_version_below_56' => apply_filters( 'exactmetrics_temporarily_hide_php_56_upgrade_warnings', version_compare( phpversion(), '5.6', '>=' ) && version_compare( phpversion(), '7', '<' ) ),
		'php_update_link'      => exactmetrics_get_url( 'settings-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-php/' ),
		'wp_version'           => $wp_version,
		'wp_version_below_46'  => version_compare( $wp_version, '4.9', '<' ),
		'wp_version_below_49'  => version_compare( $wp_version, '5.3', '<' ),
		'wp_update_link'       => exactmetrics_get_url( 'settings-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-wordpress/' ),
	);
}
