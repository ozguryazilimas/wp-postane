<?php
/**
 * Plugin Name: Google Analytics Dashboard for WP (GADWP)
 * Plugin URI: https://exactmetrics.com
 * Description: Displays Google Analytics Reports and Real-Time Statistics in your Dashboard. Automatically inserts the tracking code in every page of your website.
 * Author: ExactMetrics
 * Version: 6.3.3
 * Requires at least: 3.8.0
 * Requires PHP: 5.2
 * Author URI: https://exactmetrics.com
 * Text Domain: google-analytics-dashboard-for-wp
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @author  Chris Christoff
 * @access public
 */
final class ExactMetrics_Lite {

	/**
	 * Holds the class object.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var object Instance of instantiated ExactMetrics class.
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var string $version Plugin version.
	 */
	public $version = '6.3.3';

	/**
	 * Plugin file.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var string $file PHP File constant for main file.
	 */
	public $file;

	/**
	 * The name of the plugin.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var string $plugin_name Plugin name.
	 */
	public $plugin_name = 'ExactMetrics Lite';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var string $plugin_slug Plugin slug.
	 */
	public $plugin_slug = 'exactmetrics-lite';

	/**
	 * Holds instance of ExactMetrics License class.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var ExactMetrics_License $license Instance of License class.
	 */
	protected $license;

	/**
	 * Holds instance of ExactMetrics Admin Notice class.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var ExactMetrics_Admin_Notice $notices Instance of Admin Notice class.
	 */
	public $notices;

	/**
	 * Holds instance of ExactMetrics Notifications class.
	 *
	 * @since 6.1
	 * @access public
	 * @var ExactMetrics_Notifications $notifications Instance of Notifications class.
	 */
	public $notifications;

	/**
	 * Holds instance of ExactMetrics Notification Events
	 *
	 * @since 6.2.3
	 * @access public
	 * @var ExactMetrics_Notification_Event $notification_event Instance of ExactMetrics_Notification_Event class.
	 */
	public $notification_event;

	/**
	 * Holds instance of ExactMetrics Reporting class.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var ExactMetrics_Reporting $reporting Instance of Reporting class.
	 */
	public $reporting;

	/**
	 * Holds instance of ExactMetrics Auth class.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var ExactMetrics_Auth $auth Instance of Auth class.
	 */
	protected $auth;

	/**
	 * Holds instance of ExactMetrics API Auth class.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var ExactMetrics_Auth $api_auth Instance of APIAuth class.
	 */
	public $api_auth;

	/**
	 * Holds instance of ExactMetrics API Rest Routes class.
	 *
	 * @since 6.0.0
	 * @access public
	 * @var ExactMetrics_Rest_Routes $routes Instance of rest routes.
	 */
	public $routes;

	/**
	 * Primary class constructor.
	 *
	 * @since 6.0.0
	 * @access public
	 */
	public function __construct() {
		// We don't use this
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @access public
	 * @since 6.0.0
	 *
	 * @return object The ExactMetrics_Lite object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ExactMetrics_Lite ) ) {
			self::$instance = new ExactMetrics_Lite();
			self::$instance->file = __FILE__;

			global $wp_version;

			// Detect non-supported WordPress version and return early
			if ( version_compare( $wp_version, '3.8', '<' ) && ( ! defined( 'EXACTMETRICS_FORCE_ACTIVATION' ) || ! EXACTMETRICS_FORCE_ACTIVATION ) ) {
				add_action( 'admin_notices', array( self::$instance, 'exactmetrics_wp_notice' ) );
				return;
			}

			// Detect Pro version and return early
			if ( defined( 'EXACTMETRICS_PRO_VERSION' ) ) {
				add_action( 'admin_notices', array( self::$instance, 'exactmetrics_pro_notice' ) );
				return;
			}

			// Define constants
			self::$instance->define_globals();

			// Load in settings
			self::$instance->load_settings();

			// Load in Licensing
			self::$instance->load_licensing();

			// Load in Auth
			self::$instance->load_auth();

			// Load files
			self::$instance->require_files();

			// This does the version to version background upgrade routines and initial install
			$em_version = get_option( 'exactmetrics_current_version', '5.5.3' );
			if ( version_compare( $em_version, '6.3.0', '<' ) ) {
				exactmetrics_lite_call_install_and_upgrade();
			}

			if ( is_admin() ) {
				new AM_Deactivation_Survey( 'ExactMetrics', 'google-analytics-dashboard-for-wp' );
			}

			// Load the plugin textdomain.
			add_action( 'plugins_loaded', array( self::$instance, 'load_plugin_textdomain' ), 15 );

			// Load admin only components.
			if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
				self::$instance->notices            = new ExactMetrics_Notice_Admin();
				self::$instance->reporting          = new ExactMetrics_Reporting();
				self::$instance->api_auth           = new ExactMetrics_API_Auth();
				self::$instance->routes             = new ExactMetrics_Rest_Routes();
				self::$instance->notifications      = new ExactMetrics_Notifications();
				self::$instance->notification_event = new ExactMetrics_Notification_Event();
			}

			if ( exactmetrics_is_pro_version() ) {
				require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/load.php';
			} else {
				require_once EXACTMETRICS_PLUGIN_DIR . 'lite/includes/load.php';
			}

			// Run hook to load ExactMetrics addons.
			do_action( 'exactmetrics_load_plugins' ); // the updater class for each addon needs to be instantiated via `exactmetrics_updater`
		}

		return self::$instance;

	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 6.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'google-analytics-dashboard-for-wp' ), '6.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * Attempting to wakeup an ExactMetrics instance will throw a doing it wrong notice.
	 *
	 * @since 6.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'google-analytics-dashboard-for-wp' ), '6.0.0' );
	}

	/**
	 * Magic get function.
	 *
	 * We use this to lazy load certain functionality. Right now used to lazyload
	 * the API & Auth frontend, so it's only loaded if user is using a plugin
	 * that requires it.
	 *
	 * @since 6.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function __get( $key ) {
		if ( $key === 'auth' ) {
			if ( empty( self::$instance->auth ) ) {
				// LazyLoad Auth for Frontend
				require_once EXACTMETRICS_PLUGIN_DIR . 'includes/auth.php';
				self::$instance->auth = new ExactMetrics_Auth();
			}
			return self::$instance->$key;
		} else {
			return self::$instance->$key;
		}
	}

	/**
	 * Define ExactMetrics constants.
	 *
	 * This function defines all of the ExactMetrics PHP constants.
	 *
	 * @since 6.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function define_globals() {

		if ( ! defined( 'EXACTMETRICS_VERSION' ) ) {
			define( 'EXACTMETRICS_VERSION', $this->version );
		}

		if ( ! defined( 'EXACTMETRICS_LITE_VERSION' ) ) {
			define( 'EXACTMETRICS_LITE_VERSION', EXACTMETRICS_VERSION );
		}

		if ( ! defined( 'EXACTMETRICS_PLUGIN_NAME' ) ) {
			define( 'EXACTMETRICS_PLUGIN_NAME', $this->plugin_name );
		}

		if ( ! defined( 'EXACTMETRICS_PLUGIN_SLUG' ) ) {
			define( 'EXACTMETRICS_PLUGIN_SLUG', $this->plugin_slug );
		}

		if ( ! defined( 'EXACTMETRICS_PLUGIN_FILE' ) ) {
			define( 'EXACTMETRICS_PLUGIN_FILE', $this->file );
		}

		if ( ! defined( 'EXACTMETRICS_PLUGIN_DIR' ) ) {
			define( 'EXACTMETRICS_PLUGIN_DIR', plugin_dir_path( $this->file )  );
		}

		if ( ! defined( 'EXACTMETRICS_PLUGIN_URL' ) ) {
			define( 'EXACTMETRICS_PLUGIN_URL', plugin_dir_url( $this->file )  );
		}
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @access public
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {

		$mi_locale = get_locale();
		if ( function_exists( 'get_user_locale' ) ) {
			$mi_locale = get_user_locale();
		}

		// Traditional WordPress plugin locale filter.
		$mi_locale  = apply_filters( 'plugin_locale',  $mi_locale, 'google-analytics-dashboard-for-wp' );
		$mi_mofile  = sprintf( '%1$s-%2$s.mo', 'google-analytics-dashboard-for-wp', $mi_locale );

		// Look for wp-content/languages/google-analytics-dashboard-for-wp/google-analytics-dashboard-for-wp-{lang}_{country}.mo
		$mi_mofile1 = WP_LANG_DIR . '/google-analytics-dashboard-for-wp/' . $mi_mofile;

		// Look in wp-content/languages/plugins/google-analytics-dashboard-for-wp/google-analytics-dashboard-for-wp-{lang}_{country}.mo
		$mi_mofile2 = WP_LANG_DIR . '/plugins/google-analytics-dashboard-for-wp/' . $mi_mofile;

		// Look in wp-content/languages/plugins/google-analytics-dashboard-for-wp-{lang}_{country}.mo
		$mi_mofile3 = WP_LANG_DIR . '/plugins/' . $mi_mofile;

		// Look in wp-content/plugins/google-analytics-dashboard-for-wp/languages/google-analytics-dashboard-for-wp-{lang}_{country}.mo
		$mi_mofile4 = dirname( plugin_basename( EXACTMETRICS_PLUGIN_FILE ) ) . '/languages/';
		$mi_mofile4 = apply_filters( 'exactmetrics_lite_languages_directory', $mi_mofile4 );

		if ( file_exists( $mi_mofile1 ) ) {
			load_textdomain( 'google-analytics-dashboard-for-wp', $mi_mofile1 );
		} elseif ( file_exists( $mi_mofile2 ) ) {
			load_textdomain( 'google-analytics-dashboard-for-wp', $mi_mofile2 );
		} elseif ( file_exists( $mi_mofile3 ) ) {
			load_textdomain( 'google-analytics-dashboard-for-wp', $mi_mofile3 );
		} else {
			load_plugin_textdomain( 'google-analytics-dashboard-for-wp', false, $mi_mofile4 );
		}

	}

	/**
	 * Output a nag notice if the user has an out of date WP version installed
	 *
	 * @access public
	 * @since 6.0.0
	 *
	 * @return 	void
	 */
	public function exactmetrics_wp_notice() {
		$url = admin_url( 'plugins.php' );
		// Check for MS dashboard
		if( is_network_admin() ) {
			$url = network_admin_url( 'plugins.php' );
		}
		?>
		<div class="error">
			<p>
				<?php
				// Translators: Make version number bold and add a link to return to the plugins page.
				echo sprintf( esc_html__( 'Sorry, but your version of WordPress does not meet ExactMetrics\'s required version of %1$s3.8%2$s to run properly. The plugin not been activated. %3$sClick here to return to the Dashboard%4$s.', 'google-analytics-dashboard-for-wp' ), '<strong>', '</strong>', '<a href="' . $url . '">', '</a>' );
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Output a nag notice if the user has both Lite and Pro activated
	 *
	 * @access public
	 * @since 6.0.0
	 *
	 * @return 	void
	 */
	public function exactmetrics_pro_notice() {
		$url = admin_url( 'plugins.php' );
		// Check for MS dashboard
		if( is_network_admin() ) {
			$url = network_admin_url( 'plugins.php' );
		}
		?>
		<div class="error">
			<p>
				<?php
				// Translators: Adds a link to the plugins page.
				echo sprintf( esc_html__( 'Please %1$suninstall%2$s the ExactMetrics Lite Plugin. Your Pro version of ExactMetrics may not work as expected until the Lite version is uninstalled.', 'google-analytics-dashboard-for-wp' ), '<a href="' . $url . '">', '</a>' );
				?>
			</p>
		</div>
		<?php

	}

	/**
	 * Loads ExactMetrics settings
	 *
	 * Adds the items to the base object, and adds the helper functions.
	 *
	 * @since 6.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_settings() {
		global $exactmetrics_settings;
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/options.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/helpers.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/deprecated.php';
		$exactmetrics_settings  = exactmetrics_get_options();
	}


	/**
	 * Loads ExactMetrics License
	 *
	 * Loads license class used by ExactMetrics
	 *
	 * @since 6.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_licensing(){
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			require_once EXACTMETRICS_PLUGIN_DIR . 'lite/includes/license-compat.php';
			self::$instance->license = new ExactMetrics_License_Compat();
		}
	}

	/**
	 * Loads ExactMetrics Auth
	 *
	 * Loads auth used by ExactMetrics
	 *
	 * @since 6.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_auth() {
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/auth.php';
			self::$instance->auth = new ExactMetrics_Auth();
		}
	}

	/**
	 * Loads all files into scope.
	 *
	 * @access public
	 * @since 6.0.0
	 *
	 * @return 	void
	 */
	public function require_files() {

		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/capabilities.php';

		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {

			// Lite and Pro files
			require_once EXACTMETRICS_PLUGIN_DIR . 'assets/lib/pandora/class-am-deactivation-survey.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/ajax.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/admin.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/common.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/notice.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/licensing/autoupdate.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/review.php';

			// Pages
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/pages/settings.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/pages/tools.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/pages/reports.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/pages/addons.php';

			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/api-auth.php';

			// Reports
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/reports/abstract-report.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/reports/overview.php';

			// Reporting Functionality
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/reporting.php';

			// Routes used by Vue
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/routes.php';

			// Load gutenberg editor functions
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/gutenberg/gutenberg.php';

			// Emails
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/emails/class-emails.php';

			// Notifications class.
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/notifications.php';
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/notification-event.php';
			// Add notification manual events for lite version.
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/notifications/notification-events.php';
		}

		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/api-request.php';

		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			// Late loading classes (self instantiating)
			require_once EXACTMETRICS_PLUGIN_DIR . 'includes/admin/tracking.php';
		}

		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/frontend/frontend.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/frontend/seedprod.php';
		require_once EXACTMETRICS_PLUGIN_DIR . 'includes/measurement-protocol.php';
	}
}

/**
 * Fired when the plugin is activated.
 *
 * @access public
 * @since 6.0.0
 *
 * @global int $wp_version      The version of WordPress for this install.
 * @global object $wpdb         The WordPress database object.
 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
 *
 * @return void
 */
function exactmetrics_lite_activation_hook( $network_wide ) {

	global $wp_version;

	$url = admin_url( 'plugins.php' );
	// Check for MS dashboard
	if ( is_network_admin() ) {
		$url = network_admin_url( 'plugins.php' );
	}

	if ( version_compare( $wp_version, '3.8', '<' ) && ( ! defined( 'EXACTMETRICS_FORCE_ACTIVATION' ) || ! EXACTMETRICS_FORCE_ACTIVATION ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( sprintf( esc_html__( 'Sorry, but your version of WordPress does not meet MonsterInsight\'s required version of %1$s3.8%2$s to run properly. The plugin not been activated. %3$sClick here to return to the Dashboard%4$s.', 'google-analytics-by-wordpress' ), '<strong>', '</strong>', '<a href="' . $url . '">', '</a>' ) );
	}

	if ( class_exists( 'ExactMetrics' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( sprintf( esc_html__( 'Please uninstall and remove ExactMetrics Pro before activating Google Analytics Dashboard for WP (GADWP). The Lite version has not been activated. %1$sClick here to return to the Dashboard%2$s.', 'google-analytics-by-wordpress' ), '<a href="' . $url . '">', '</a>' ) );
	}

	// Add transient to trigger redirect.
	set_transient( '_exactmetrics_activation_redirect', 1, 30 );
}
register_activation_hook( __FILE__, 'exactmetrics_lite_activation_hook' );

/**
 * Fired when the plugin is uninstalled.
 *
 * @access public
 * @since 6.0.0
 *
 * @return 	void
 */
function exactmetrics_lite_uninstall_hook() {
	wp_cache_flush();

	// Note, if both MI Pro and Lite are active, this is an MI Pro instance
	// Therefore MI Lite can only use functions of the instance common to
	// both plugins. If it needs to be pro specific, then include a file that
	// has that method.
	$instance = ExactMetrics();

	// If uninstalling via wp-cli load admin-specific files only here.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		define( 'WP_ADMIN', true );
		$instance->require_files();
		$instance->load_auth();
		$instance->notices         = new ExactMetrics_Notice_Admin();
		$instance->reporting       = new ExactMetrics_Reporting();
		$instance->api_auth        = new ExactMetrics_API_Auth();
	}

	// Don't delete any data if the PRO version is already active.
	if ( exactmetrics_is_pro_version() ) {
		return;
	}

	if ( is_multisite() ) {
		$site_list = get_sites();
		foreach ( (array) $site_list as $site ) {
			switch_to_blog( $site->blog_id );

			// Delete auth
			$instance->api_auth->delete_auth();

			// Delete data
			$instance->reporting->delete_aggregate_data('site');

			restore_current_blog();
		}
		// Delete network auth using a custom function as some variables are not initiated.
		$instance->api_auth->uninstall_network_auth();

		// Delete network data
		$instance->reporting->delete_aggregate_data('network');
	} else {
		// Delete auth
		$instance->api_auth->delete_auth();

		// Delete data
		$instance->reporting->delete_aggregate_data('site');
	}

	// Clear notification cron schedules
	$schedules = wp_get_schedules();

	if  ( is_array( $schedules ) && ! empty( $schedules ) ) {
		foreach ( $schedules as $key => $value ) {
			if ( 0 === strpos($key, "exactmetrics_notification_") ) {
				$cron_hook = implode("_", explode( "_", $key, -2 ) ) . '_cron';
				wp_clear_scheduled_hook( $cron_hook );
			}
		}
	}

}
register_uninstall_hook( __FILE__, 'exactmetrics_lite_uninstall_hook' );

/**
 * The main function responsible for returning the one true ExactMetrics_Lite
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $exactmetrics = ExactMetrics_Lite(); ?>
 *
 * @since 6.0.0
 *
 * @uses ExactMetrics_Lite::get_instance() Retrieve ExactMetrics_Lite instance.
 *
 * @return ExactMetrics_Lite The singleton ExactMetrics_Lite instance.
 */
function ExactMetrics_Lite() {
	return ExactMetrics_Lite::get_instance();
}

/**
 * ExactMetrics Install and Updates.
 *
 * This function is used install and upgrade ExactMetrics. This is used for upgrade routines
 * that can be done automatically, behind the scenes without the need for user interaction
 * (for example pagination or user input required), as well as the initial install.
 *
 * @since 6.0.0
 * @access public
 *
 * @global string $wp_version WordPress version (provided by WordPress core).
 * @uses ExactMetrics_Lite::load_settings() Loads ExactMetrics settings
 * @uses ExactMetrics_Install::init() Runs upgrade process
 *
 * @return void
 */
function exactmetrics_lite_install_and_upgrade() {
	global $wp_version;

	// If the WordPress site doesn't meet the correct WP version requirements, don't activate ExactMetrics
	if ( version_compare( $wp_version, '3.8', '<' ) ) {
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			return;
		}
	}

	// Don't run if ExactMetrics Pro is installed
	if ( class_exists( 'ExactMetrics' ) ) {
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			return;
		}
	}


	// Load settings and globals (so we can use/set them during the upgrade process)
	ExactMetrics_Lite()->define_globals();
	ExactMetrics_Lite()->load_settings();

	// Load in Auth
	ExactMetrics()->load_auth();

	// Load upgrade file
	require_once EXACTMETRICS_PLUGIN_DIR . 'includes/em-install.php';

	// Run the ExactMetrics upgrade routines
	$updates = new ExactMetrics_Install();
	$updates->init();
}

/**
 * ExactMetrics check for install and update processes.
 *
 * This function is used to call the ExactMetrics automatic upgrade class, which in turn
 * checks to see if there are any update procedures to be run, and if
 * so runs them. Also installs ExactMetrics for the first time.
 *
 * @since 6.0.0
 * @access public
 *
 * @uses ExactMetrics_Install() Runs install and upgrade process.
 *
 * @return void
 */
function exactmetrics_lite_call_install_and_upgrade(){
	add_action( 'wp_loaded', 'exactmetrics_lite_install_and_upgrade' );
}

/**
 * Returns the ExactMetrics combined object that you can use for both
 * ExactMetrics Lite and Pro Users. When both plugins active, defers to the
 * more complete Pro object.
 *
 * Warning: Do not use this in Lite or Pro specific code (use the individual objects instead).
 * Also do not use in the ExactMetrics Lite/Pro upgrade and install routines.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Prevents the need to do conditional global object logic when you have code that you want to work with
 * both Pro and Lite.
 *
 * Example: <?php $exactmetrics = ExactMetrics(); ?>
 *
 * @since 6.0.0
 *
 * @uses ExactMetrics::get_instance() Retrieve ExactMetrics Pro instance.
 * @uses ExactMetrics_Lite::get_instance() Retrieve ExactMetrics Lite instance.
 *
 * @return ExactMetrics The singleton ExactMetrics instance.
 */
if ( ! function_exists( 'ExactMetrics' ) ) {
	function ExactMetrics() {
		return ( class_exists( 'ExactMetrics' ) ? ExactMetrics_Pro() : ExactMetrics_Lite() );
	}
	add_action( 'plugins_loaded', 'ExactMetrics' );
}
