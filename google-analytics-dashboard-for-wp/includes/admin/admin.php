<?php
/**
 * Admin class.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Admin
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register menu items for ExactMetrics.
 *
 * @since 6.0.0
 * @access public
 *
 * @return void
 */
function exactmetrics_admin_menu() {
    $hook             = exactmetrics_get_menu_hook();
    $menu_icon_inline = exactmetrics_get_inline_menu_icon();

    if ( $hook === 'exactmetrics_settings' ) {
        // If dashboards disabled, first settings page
        add_menu_page( __( 'ExactMetrics', 'google-analytics-dashboard-for-wp' ), 'ExactMetrics' . ExactMetrics()->notifications->get_menu_count(), 'exactmetrics_save_settings', 'exactmetrics_settings', 'exactmetrics_settings_page',  $menu_icon_inline, '100.00013467543' );
        $hook = 'exactmetrics_settings';

        add_submenu_page( $hook, __( 'ExactMetrics', 'google-analytics-dashboard-for-wp' ), __( 'Settings', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_save_settings', 'exactmetrics_settings' );
    } else {
        // if dashboards enabled, first dashboard
        add_menu_page( __( 'General:', 'google-analytics-dashboard-for-wp' ), 'ExactMetrics' . ExactMetrics()->notifications->get_menu_count(), 'exactmetrics_view_dashboard', 'exactmetrics_reports', 'exactmetrics_reports_page',  $menu_icon_inline, '100.00013467543' );

        add_submenu_page( $hook, __( 'General Reports:', 'google-analytics-dashboard-for-wp' ), __( 'Reports', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_view_dashboard', 'exactmetrics_reports', 'exactmetrics_reports_page' );

        // then settings page
        add_submenu_page( $hook, __( 'ExactMetrics', 'google-analytics-dashboard-for-wp' ), __( 'Settings', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_save_settings', 'exactmetrics_settings', 'exactmetrics_settings_page' );

        // Add dashboard submenu.
        add_submenu_page( 'index.php', __( 'General Reports:', 'google-analytics-dashboard-for-wp' ), 'ExactMetrics', 'exactmetrics_view_dashboard', 'admin.php?page=exactmetrics_reports' );
    }

    $submenu_base = add_query_arg( 'page', 'exactmetrics_settings', admin_url( 'admin.php' ) );

    // then tools
    add_submenu_page( $hook, __( 'Tools:', 'google-analytics-dashboard-for-wp' ), __( 'Tools', 'google-analytics-dashboard-for-wp' ), 'manage_options', $submenu_base . '#/tools' );

    // then addons
    $network_key = exactmetrics_is_pro_version() ? ExactMetrics()->license->get_network_license_key() : '';
    if ( ! exactmetrics_is_network_active() || ( exactmetrics_is_network_active() && empty( $network_key ) ) ) {
        add_submenu_page( $hook, __( 'Addons:', 'google-analytics-dashboard-for-wp' ), '<span style="color:' . exactmetrics_menu_highlight_color() . '"> ' . __( 'Addons', 'google-analytics-dashboard-for-wp' ) . '</span>', 'exactmetrics_save_settings', $submenu_base . '#/addons' );
    }

    // Add About us page.
    add_submenu_page( $hook, __( 'About Us:', 'google-analytics-dashboard-for-wp' ), __( 'About Us', 'google-analytics-dashboard-for-wp' ), 'manage_options', $submenu_base . '#/about' );
}
add_action( 'admin_menu', 'exactmetrics_admin_menu' );

function exactmetrics_get_menu_hook() {
    $dashboards_disabled = exactmetrics_get_option( 'dashboards_disabled', false );
    if ( $dashboards_disabled || ( current_user_can( 'exactmetrics_save_settings' ) && ! current_user_can( 'exactmetrics_view_dashboard' ) ) ) {
        return 'exactmetrics_settings';
    } else {
        return 'exactmetrics_reports';
    }
}

function exactmetrics_network_admin_menu() {
    // Get the base class object.
    $base = ExactMetrics();

    // First, let's see if this is an MS network enabled plugin. If it is, we should load the license
    // menu page and the updater on the network panel
    if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }

    $plugin = plugin_basename( EXACTMETRICS_PLUGIN_FILE );
    if ( ! is_plugin_active_for_network( $plugin ) ) {
        return;
    }

    $menu_icon_inline = exactmetrics_get_inline_menu_icon();
    $hook = 'exactmetrics_network';
    $submenu_base = add_query_arg( 'page', 'exactmetrics_network', network_admin_url( 'admin.php' ) );
    add_menu_page( __( 'Network Settings:', 'google-analytics-dashboard-for-wp' ), 'ExactMetrics' . ExactMetrics()->notifications->get_menu_count(), 'exactmetrics_save_settings', 'exactmetrics_network', 'exactmetrics_network_page',  $menu_icon_inline, '100.00013467543' );

    add_submenu_page( $hook, __( 'Network Settings:', 'google-analytics-dashboard-for-wp' ), __( 'Network Settings', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_save_settings', 'exactmetrics_network', 'exactmetrics_network_page' );

    add_submenu_page( $hook, __( 'General Reports:', 'google-analytics-dashboard-for-wp' ), __( 'Reports', 'google-analytics-dashboard-for-wp' ), 'exactmetrics_view_dashboard', 'exactmetrics_reports', 'exactmetrics_reports_page' );

    // then addons
    add_submenu_page( $hook, __( 'Addons:', 'google-analytics-dashboard-for-wp' ), '<span style="color:' . exactmetrics_menu_highlight_color() . '"> ' . __( 'Addons', 'google-analytics-dashboard-for-wp' ) . '</span>', 'exactmetrics_save_settings', $submenu_base . '#/addons' );

    $submenu_base = add_query_arg( 'page', 'exactmetrics_network', network_admin_url( 'admin.php' ) );

    // Add About us page.
    add_submenu_page( $hook, __( 'About Us:', 'google-analytics-dashboard-for-wp' ), __( 'About Us', 'google-analytics-dashboard-for-wp' ), 'manage_options', $submenu_base . '#/about' );
}
add_action( 'network_admin_menu', 'exactmetrics_network_admin_menu', 5 );

/**
 * Adds one or more classes to the body tag in the dashboard.
 *
 * @param  String $classes Current body classes.
 * @return String          Altered body classes.
 */
function exactmetrics_add_admin_body_class( $classes ) {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
    if ( empty( $screen ) || empty( $screen->id ) || strpos( $screen->id, 'exactmetrics' ) === false ) {
        return $classes;
    }

    return "$classes exactmetrics_page ";
}
add_filter( 'admin_body_class', 'exactmetrics_add_admin_body_class', 10, 1 );

/**
 * Adds one or more classes to the body tag in the dashboard.
 *
 * @param  String $classes Current body classes.
 * @return String          Altered body classes.
 */
function exactmetrics_add_admin_body_class_tools_page( $classes ) {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

    if ( empty( $screen ) || empty( $screen->id ) || strpos( $screen->id, 'exactmetrics_tools' ) === false || 'insights_page_exactmetrics_tools' === $screen->id  ) {
        return $classes;
    }

    return "$classes insights_page_exactmetrics_tools ";
}
add_filter( 'admin_body_class', 'exactmetrics_add_admin_body_class_tools_page', 10, 1 );

/**
 * Adds one or more classes to the body tag in the dashboard.
 *
 * @param  String $classes Current body classes.
 * @return String          Altered body classes.
 */
function exactmetrics_add_admin_body_class_addons_page( $classes ) {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
    if ( empty( $screen ) || empty( $screen->id ) || strpos( $screen->id, 'exactmetrics_addons' ) === false || 'insights_page_exactmetrics_addons' === $screen->id  ) {
        return $classes;
    }

    return "$classes insights_page_exactmetrics_addons ";
}
add_filter( 'admin_body_class', 'exactmetrics_add_admin_body_class_addons_page', 10, 1 );

/**
 * Add a link to the settings page to the plugins list
 *
 * @param array $links array of links for the plugins, adapted when the current plugin is found.
 *
 * @return array $links
 */
function exactmetrics_add_action_links( $links ) {
    $docs = '<a title="' . esc_html__( 'ExactMetrics Knowledge Base', 'google-analytics-dashboard-for-wp' ) . '" href="'. exactmetrics_get_url( 'all-plugins', 'kb-link', "https://www.exactmetrics.com/docs/" ) .'">' . esc_html__( 'Documentation', 'google-analytics-dashboard-for-wp' ) . '</a>';
    array_unshift( $links, $docs );

    // If lite, show a link where they can get pro from
    if ( ! exactmetrics_is_pro_version() ) {
        $get_pro = '<a title="' . esc_html__( 'Get ExactMetrics Pro', 'google-analytics-dashboard-for-wp' ) .'" href="'. exactmetrics_get_upgrade_link( 'all-plugins', 'upgrade-link', "https://www.exactmetrics.com/docs/" ) .'">' . esc_html__( 'Get ExactMetrics Pro', 'google-analytics-dashboard-for-wp' ) . '</a>';
        array_unshift( $links, $get_pro );
    }

    // If Lite, support goes to forum. If pro, it goes to our website
    if ( exactmetrics_is_pro_version() ) {
        $support = '<a title="ExactMetrics Pro Support" href="'. exactmetrics_get_url( 'all-plugins', 'pro-support-link', "https://www.exactmetrics.com/my-account/support/" ) .'">' . esc_html__( 'Support', 'google-analytics-dashboard-for-wp' ) . '</a>';
        array_unshift( $links, $support );
    } else {
        $support = '<a title="ExactMetrics Lite Support" href="'. exactmetrics_get_url( 'all-plugins', 'lite-support-link', "https://www.exactmetrics.com/lite-support/" ) .'">' . esc_html__( 'Support', 'google-analytics-dashboard-for-wp' ) . '</a>';
        array_unshift( $links, $support );
    }

	if ( is_network_admin() ) {
		$settings_link = '<a href="' . esc_url( network_admin_url( 'admin.php?page=exactmetrics_network' ) ) . '">' . esc_html__( 'Network Settings', 'google-analytics-dashboard-for-wp' ) . '</a>';
	} else {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=exactmetrics_settings' ) ) . '">' . esc_html__( 'Settings', 'google-analytics-dashboard-for-wp' ) . '</a>';
	}

    array_unshift( $links, $settings_link );

    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( EXACTMETRICS_PLUGIN_FILE ), 'exactmetrics_add_action_links' );
add_filter( 'network_admin_plugin_action_links_' . plugin_basename( EXACTMETRICS_PLUGIN_FILE ), 'exactmetrics_add_action_links' );

/**
 * Loads a partial view for the Administration screen
 *
 * @access public
 * @since 6.0.0
 *
 * @param   string  $template   PHP file at includes/admin/partials, excluding file extension
 * @param   array   $data       Any data to pass to the view
 * @return  void
 */
function exactmetrics_load_admin_partial( $template, $data = array() ) {

    if ( exactmetrics_is_pro_version() ) {
        $dir = trailingslashit( plugin_dir_path( ExactMetrics()->file ) . 'pro/includes/admin/partials' );

        if ( file_exists( $dir . $template . '.php' ) ) {
            require_once(  $dir . $template . '.php' );
            return true;
        }
    } else {
        $dir = trailingslashit( plugin_dir_path( ExactMetrics()->file ) . 'lite/includes/admin/partials' );

        if ( file_exists( $dir . $template . '.php' ) ) {
            require_once(  $dir . $template . '.php' );
            return true;
        }
    }

    $dir = trailingslashit( plugin_dir_path( ExactMetrics()->file ) . 'includes/admin/partials' );

    if ( file_exists( $dir . $template . '.php' ) ) {
        require_once(  $dir . $template . '.php' );
        return true;
    }

    return false;
}

/**
 * When user is on a ExactMetrics related admin page, display footer text
 * that graciously asks them to rate us.
 *
 * @since 6.0.0
 * @param string $text
 * @return string
 */
function exactmetrics_admin_footer( $text ) {
    global $current_screen;
    if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'exactmetrics' ) !== false ) {
        $url  = 'https://wordpress.org/support/view/plugin-reviews/google-analytics-dashboard-for-wp?filter=5';
        // Translators: Placeholders add a link to the wordpress.org repository.
        $text = sprintf( esc_html__( 'Please rate %1$sExactMetrics%2$s on %3$s %4$sWordPress.org%5$s to help us spread the word. Thank you from the ExactMetrics team!', 'google-analytics-dashboard-for-wp' ), '<strong>', '</strong>', '<a class="exactmetrics-no-text-decoration" href="' .  $url . '" target="_blank" rel="noopener noreferrer"><i class="monstericon-star"></i><i class="monstericon-star"></i><i class="monstericon-star"></i><i class="monstericon-star"></i><i class="monstericon-star"></i></a>', '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">', '</a>' );
    }
    return $text;
}
add_filter( 'admin_footer_text', 'exactmetrics_admin_footer', 1, 2 );

function exactmetrics_admin_setup_notices() {

    // Don't show on ExactMetrics pages
    $screen = get_current_screen();
    if ( empty( $screen->id ) || strpos( $screen->id, 'exactmetrics' ) !== false ) {
        return;
    }

    // Make sure they have the permissions to do something
    if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
        return;
    }

    // Priority:
    // 1. Google Analytics not authenticated
    // 2. License key not entered for pro
    // 3. License key not valid/okay for pro
    // 4. WordPress + PHP min versions
    // 5. (old) Optin setting not configured
    // 6. Manual UA code
    // 7. Automatic updates not configured
    // 8. Woo upsell
    // 9. EDD upsell


    // 1. Google Analytics not authenticated
	if ( ! is_network_admin() && ! exactmetrics_get_ua() && ! defined( 'EXACTMETRICS_DISABLE_TRACKING' ) ) {

        $submenu_base = is_network_admin() ? add_query_arg( 'page', 'exactmetrics_network', network_admin_url( 'admin.php' ) ) : add_query_arg( 'page', 'exactmetrics_settings', admin_url( 'admin.php' ) );
        $title     = esc_html__( 'Please Setup Website Analytics to See Audience Insights', 'google-analytics-dashboard-for-wp' );
        $primary   = esc_html__( 'Connect ExactMetrics and Setup Website Analytics', 'google-analytics-dashboard-for-wp' );
        $urlone    = is_network_admin() ? network_admin_url( 'admin.php?page=exactmetrics-onboarding' ) : admin_url( 'admin.php?page=exactmetrics-onboarding' );
        $secondary = esc_html__( 'Learn More', 'google-analytics-dashboard-for-wp' );
        $urltwo    = $submenu_base . '#/about/getting-started';
        $message   = esc_html__( 'ExactMetrics, WordPress analytics plugin, helps you connect your website with Google Analytics, so you can see how people find and use your website. Over 1 million website owners use ExactMetrics to see the stats that matter and grow their business.', 'google-analytics-dashboard-for-wp' );
        echo '<div class="notice notice-info"><p style="font-weight:700">'. $title .'</p><p>'. $message.'</p><p><a href="'. $urlone .'" class="button-primary">'. $primary .'</a>&nbsp;&nbsp;&nbsp;<a href="'. $urltwo .'" class="button-secondary">'. $secondary .'</a></p></div>';
        return;
    }

    // 2. License key not entered for pro
    $key = exactmetrics_is_pro_version() ? ExactMetrics()->license->get_license_key() : '';
    if ( exactmetrics_is_pro_version() && empty( $key ) ) {
        $page = is_network_admin() ? network_admin_url( 'admin.php?page=exactmetrics_network' ) : admin_url( 'admin.php?page=exactmetrics_settings' );
        // Translators: Adds a link to retrieve the license.
        $message = sprintf( esc_html__( 'Warning: No valid license key has been entered for ExactMetrics. You are currently not getting updates, and are not able to view reports. %1$sPlease click here to enter your license key and begin receiving updates and reports.%2$s', 'google-analytics-dashboard-for-wp' ), '<a href="'. esc_url( $page ) . '">', '</a>' );
        echo '<div class="error"><p>'. $message.'</p></div>';
        return;
    }

    // 3. License key not valid/okay for pro
    if ( exactmetrics_is_pro_version() ) {
        $message = '';
        if ( ExactMetrics()->license->get_site_license_key() ){
            if ( ExactMetrics()->license->site_license_expired() ) {
	            // Translators: Adds a link to the license renewal.
                $message = sprintf( esc_html__( 'Your license key for ExactMetrics has expired. %1$sPlease click here to renew your license key.%2$s', 'google-analytics-dashboard-for-wp' ), '<a href="'. exactmetrics_get_url( 'admin-notices', 'expired-license', "https://www.exactmetrics.com/login/" ) .'" target="_blank" rel="noopener noreferrer" referrer="no-referrer">', '</a>' );
            } else if ( ExactMetrics()->license->site_license_disabled() ) {
                $message = esc_html__( 'Your license key for ExactMetrics has been disabled. Please use a different key.', 'google-analytics-dashboard-for-wp' );
            } else if ( ExactMetrics()->license->site_license_invalid() ) {
                $message = esc_html__( 'Your license key for ExactMetrics is invalid. The key no longer exists or the user associated with the key has been deleted. Please use a different key.', 'google-analytics-dashboard-for-wp' );
            }
        } else if ( ExactMetrics()->license->get_network_license_key() ) {
            if ( ExactMetrics()->license->network_license_expired() ) {
            	// Translators: Adds a link to renew license.
                $message = sprintf( esc_html__( 'Your network license key for ExactMetrics has expired. %1$sPlease click here to renew your license key.%2$s', 'google-analytics-dashboard-for-wp' ), '<a href="'. exactmetrics_get_url( 'admin-notices', 'expired-license', "https://www.exactmetrics.com/login/" ) .'" target="_blank" rel="noopener noreferrer" referrer="no-referrer">', '</a>' );
            } else if ( ExactMetrics()->license->network_license_disabled() ) {
                $message = esc_html__( 'Your network license key for ExactMetrics has been disabled. Please use a different key.', 'google-analytics-dashboard-for-wp' );
            } else if ( ExactMetrics()->license->network_license_invalid() ) {
                $message = esc_html__( 'Your network license key for ExactMetrics is invalid. The key no longer exists or the user associated with the key has been deleted. Please use a different key.', 'google-analytics-dashboard-for-wp' );
            }
        }
        if ( ! empty( $message ) ) {
            echo '<div class="error"><p>'. $message.'</p></div>';
            return;
        }
    }

    // 4. Notices for PHP/WP version deprecations
    if ( current_user_can( 'update_core' ) ) {
        global $wp_version;

        // PHP 5.2-5.5
        if ( version_compare( phpversion(), '5.6', '<' ) ) {
            $url = exactmetrics_get_url( 'global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-php/' );
            // Translators: Placeholders add the PHP version, a link to the ExactMetrics blog and a line break.
            $message = sprintf( esc_html__( 'Your site is running an outdated, insecure version of PHP (%1$s), which could be putting your site at risk for being hacked.%4$sWordPress stopped supporting your PHP version in April, 2019.%4$sUpdating PHP only takes a few minutes and will make your website significantly faster and more secure.%4$s%2$sLearn more about updating PHP%3$s', 'google-analytics-dashboard-for-wp' ), phpversion(), '<a href="' . $url . '" target="_blank">', '</a>', '<br>' );
            echo '<div class="error"><p>'. $message.'</p></div>';
            return;
        }
        // WordPress 3.0 - 4.5
        else if ( version_compare( $wp_version, '4.9', '<' ) ) {
            $url = exactmetrics_get_url( 'global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-wordpress/' );
            // Translators: Placeholders add the current WordPress version and links to the ExactMetrics blog
            $message = sprintf( esc_html__( 'Your site is running an outdated version of WordPress (%1$s).%4$sExactMetrics will stop supporting WordPress versions lower than 4.9 in 2020.%4$sUpdating WordPress takes just a few minutes and will also solve many bugs that exist in your WordPress install.%4$s%2$sLearn more about updating WordPress%3$s', 'google-analytics-dashboard-for-wp' ), $wp_version, '<a href="' . $url . '" target="_blank">', '</a>', '<br>' );
            echo '<div class="error"><p>'. $message.'</p></div>';
            return;
        }
        // PHP 5.4/5.5
        // else if ( version_compare( phpversion(), '5.6', '<' ) ) {
        //  $url = exactmetrics_get_url( 'global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-php/' );
        //  $message = sprintf( esc_html__( 'Your site is running an outdated, insecure version of PHP (%1$s), which could be putting your site at risk for being hacked.%4$sWordPress will stop supporting your PHP version in April, 2019.%4$sUpdating PHP only takes a few minutes and will make your website significantly faster and more secure.%4$s%2$sLearn more about updating PHP%3$s', 'google-analytics-dashboard-for-wp' ), phpversion(), '<a href="' . $url . '" target="_blank">', '</a>', '<br>' );
        //  echo '<div class="error"><p>'. $message.'</p></div>';
        //  return;
        // }
        // // WordPress 4.6 - 4.8
        // else if ( version_compare( $wp_version, '4.9', '<' ) ) {
        //  $url = exactmetrics_get_url( 'global-notice', 'settings-page', 'https://www.exactmetrics.com/docs/update-wordpress/' );
        //  $message = sprintf( esc_html__( 'Your site is running an outdated version of WordPress (%1$s).%4$sExactMetrics will stop supporting WordPress versions lower than 4.9 in October, 2019.%4$sUpdating WordPress takes just a few minutes and will also solve many bugs that exist in your WordPress install.%4$s%2$sLearn more about updating WordPress%3$s', 'google-analytics-dashboard-for-wp' ), $wp_version, '<a href="' . $url . '" target="_blank">', '</a>', '<br>' );
        //  echo '<div class="error"><p>'. $message.'</p></div>';
        //  return;
        // }
    }

    // 5. Optin setting not configured
    // if ( ! is_network_admin() ) {
    //     if ( ! get_option( 'exactmetrics_tracking_notice' ) ) {
    //         if ( ! exactmetrics_get_option( 'anonymous_data', false ) ) {
    //             if ( ! exactmetrics_is_dev_url( network_site_url( '/' ) ) ) {
    //                 if ( exactmetrics_is_pro_version() ) {
    //                     exactmetrics_update_option( 'anonymous_data', 1 );
    //                     return;
    //                 }
    //                 $optin_url  = add_query_arg( 'mi_action', 'opt_into_tracking' );
    //                 $optout_url = add_query_arg( 'mi_action', 'opt_out_of_tracking' );
    //                 echo '<div class="updated"><p>';
    //                 echo esc_html__( 'Allow ExactMetrics to track plugin usage? Opt-in to tracking and our newsletter to stay informed of the latest changes to ExactMetrics and help us ensure compatibility.', 'google-analytics-dashboard-for-wp' );
    //                 echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . __( 'Allow', 'google-analytics-dashboard-for-wp' ) . '</a>';
    //                 echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'google-analytics-dashboard-for-wp' ) . '</a>';
    //                 echo '</p></div>';
    //                 return;
    //             } else {
    //                 // is testing site
    //                  update_option( 'exactmetrics_tracking_notice', '1' );
    //             }
    //         }
    //     }
    // }

    $notices   = get_option( 'exactmetrics_notices' );
    if ( ! is_array( $notices ) ) {
        $notices = array();
    }

    // 6. Authenticate, not manual
	$authed      = ExactMetrics()->auth->is_authed() || ExactMetrics()->auth->is_network_authed();
	$url         = is_network_admin() ? network_admin_url( 'admin.php?page=exactmetrics_network' ) : admin_url( 'admin.php?page=exactmetrics_settings' );
	$ua_code     = exactmetrics_get_ua_to_output();
	// Translators: Placeholders add links to the settings panel.
	$manual_text = sprintf( esc_html__( 'Important: You are currently using manual UA code output. We highly recommend %1$sauthenticating with ExactMetrics%2$s so that you can access our new reporting area and take advantage of new ExactMetrics features.', 'google-analytics-dashboard-for-wp' ), '<a href="' . $url . '">', '</a>' );
	$migrated    = exactmetrics_get_option( 'gadwp_migrated', 0 );
	if ( $migrated > 0 ) {
		$url         = admin_url( 'admin.php?page=exactmetrics-getting-started&exactmetrics-migration=1' );
		// Translators: Placeholders add links to the settings panel.
		$text        = esc_html__( 'Click %1$shere%2$s to reauthenticate to be able to access reports. For more information why this is required, see our %3$sblog post%4$s.', 'google-analytics-dashboard-for-wp' );
		$manual_text = sprintf( $text, '<a href="' . $url . '">', '</a>', '<a href="' . exactmetrics_get_url( 'notice', 'manual-ua', 'https://www.exactmetrics.com/why-did-we-implement-the-new-google-analytics-authentication-flow-challenges-explained/' ) . '" target="_blank">', '</a>' );
	}

	if ( empty( $authed ) && ! isset( $notices['exactmetrics_auth_not_manual'] ) && ! empty( $ua_code ) ) {
		echo '<div class="notice notice-info is-dismissible exactmetrics-notice" data-notice="exactmetrics_auth_not_manual">';
		echo '<p>';
		echo $manual_text;
		echo '</p>';
		echo '</div>';

		return;
	}

    // 7. Automatic updates not configured
    // if ( ! is_network_admin() ) {
    //     $updates   = exactmetrics_get_option( 'automatic_updates', false );
    //     $url       = admin_url( 'admin.php?page=exactmetrics_settings' );

    //     if ( empty( $updates) && ! isset( $notices['exactmetrics_automatic_updates' ] ) ) {
    //         echo '<div class="notice notice-info is-dismissible exactmetrics-notice" data-notice="exactmetrics_automatic_updates">';
    //             echo '<p>';
    //             echo sprintf( esc_html__( 'Important: Please %1$sconfigure the Automatic Updates Settings%2$s in ExactMetrics.', 'google-analytics-dashboard-for-wp' ), '<a href="' . $url .'">', '</a>' );
    //             echo '</p>';
    //         echo '</div>';
    //         return;
    //     }
    // }

    // 8. WooUpsell
    if ( ! exactmetrics_is_pro_version() && class_exists( 'WooCommerce' ) ) {
        if ( ! isset( $notices['exactmetrics_woocommerce_tracking_available' ] ) ) {
            echo '<div class="notice notice-success is-dismissible exactmetrics-notice exactmetrics-wooedd-upsell-row" data-notice="exactmetrics_woocommerce_tracking_available">';
                echo '<div class="exactmetrics-wooedd-upsell-left">';
                    echo '<p><strong>';
                    echo esc_html( 'Enhanced Ecommerce Analytics for Your WooCommerce Store', 'google-analytics-dashboard-for-wp' );
                    echo '</strong></p>';
                    echo '<img class="exactmetrics-wooedd-upsell-image exactmetrics-wooedd-upsell-image-small" src="' . trailingslashit( EXACTMETRICS_PLUGIN_URL ) . 'assets/images/upsell/woo-edd-upsell.png">';
                    echo '<p>';
                    echo esc_html( 'ExactMetrics Pro gives you detailed stats and insights about your customers.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    echo '<p>';
                    echo esc_html( 'This helps you make data-driven decisions about your content, and marketing strategy so you can increase your website traffic, leads, and sales.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    echo '<p>';
                    echo esc_html( 'Pro customers also get Form Tracking, Custom Dimensions Tracking, UserID Tracking and much more.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    echo '<p>';
                    echo esc_html( 'Start making data-driven decisions to grow your business.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    // Translators: Placeholders add a link to the ExactMetrics website.
                    echo sprintf( esc_html__( '%1$sGet ExactMetrics Pro%2$s', 'google-analytics-dashboard-for-wp' ), '<a class="button button-primary button-hero" href="'. exactmetrics_get_upgrade_link( 'admin-notices', 'woocommerce-upgrade' ) .'">', ' &raquo;</a>' );
                    echo '</p>';
                echo '</div><div class="exactmetrics-wooedd-upsell-right">';
                    echo '<img class="exactmetrics-wooedd-upsell-image exactmetrics-wooedd-upsell-image-large" src="' . trailingslashit( EXACTMETRICS_PLUGIN_URL ) . 'assets/images/upsell/woo-edd-upsell.png">';
                echo '</div>';
            echo '</div>';
            return;
        }
    }

    // 9. EDDUpsell
    if ( ! exactmetrics_is_pro_version() && class_exists( 'Easy_Digital_Downloads' ) ) {
        if ( ! isset( $notices['exactmetrics_edd_tracking_available' ] ) ) {
            echo '<div class="notice notice-success is-dismissible exactmetrics-notice exactmetrics-wooedd-upsell-row" data-notice="exactmetrics_edd_tracking_available">';
                echo '<div class="exactmetrics-wooedd-upsell-left">';
                    echo '<p><strong>';
                    echo esc_html( 'Enhanced Ecommerce Analytics for Your Easy Digital Downloads Store', 'google-analytics-dashboard-for-wp' );
                    echo '</strong></p>';
                    echo '<img class="exactmetrics-wooedd-upsell-image exactmetrics-wooedd-upsell-image-small" src="' . trailingslashit( EXACTMETRICS_PLUGIN_URL ) . 'assets/images/upsell/woo-edd-upsell.png">';
                    echo '<p>';
                    echo esc_html( 'ExactMetrics Pro gives you detailed stats and insights about your customers.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    echo '<p>';
                    echo esc_html( 'This helps you make data-driven decisions about your content, and marketing strategy so you can increase your website traffic, leads, and sales.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    echo '<p>';
                    echo esc_html( 'Pro customers also get Form Tracking, Custom Dimensions Tracking, UserID Tracking and much more.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    echo '<p>';
                    echo esc_html( 'Start making data-driven decisions to grow your business.', 'google-analytics-dashboard-for-wp' );
                    echo '</p>';
                    echo sprintf( esc_html__( '%1$sGet ExactMetrics Pro%2$s', 'google-analytics-dashboard-for-wp' ), '<a class="button button-primary button-hero" href="'. exactmetrics_get_upgrade_link( 'admin-notices', 'edd-upgrade' ) .'">', ' &raquo;</a>' );
                    echo '</p>';
                echo '</div><div class="exactmetrics-wooedd-upsell-right">';
                    echo '<img class="exactmetrics-wooedd-upsell-image exactmetrics-wooedd-upsell-image-large" src="' . trailingslashit( EXACTMETRICS_PLUGIN_URL ) . 'assets/images/upsell/woo-edd-upsell.png">';
                echo '</div>';
            echo '</div>';
            return;
        }
    }

    if ( isset( $notices['exactmetrics_cross_domains_extracted'] ) && false === $notices['exactmetrics_cross_domains_extracted'] ) {
        $page = is_network_admin() ? network_admin_url( 'admin.php?page=exactmetrics_network' ) : admin_url( 'admin.php?page=exactmetrics_settings' );
        $page = $page . '#/advanced';
        // Translators: Adds a link to the settings panel.
        $message = sprintf( esc_html__( 'Warning: ExactMetrics found cross-domain settings in the custom code field and converted them to the new settings structure.  %1$sPlease click here to review and remove the code no longer needed.%2$s', 'google-analytics-dashboard-for-wp' ), '<a href="'. esc_url( $page ) . '">', '</a>' );
        echo '<div class="notice notice-success is-dismissible exactmetrics-notice" data-notice="exactmetrics_cross_domains_extracted"><p>'. $message.'</p></div>';
        return;
    }
}
add_action( 'admin_notices', 'exactmetrics_admin_setup_notices' );
add_action( 'network_admin_notices', 'exactmetrics_admin_setup_notices' );


// AM Notices
function exactmetrics_am_notice_optout( $super_admin ) {
    if ( exactmetrics_get_option( 'hide_am_notices', false ) || exactmetrics_get_option( 'network_hide_am_notices', false ) ) {
        return false;
    }
    return $super_admin;
}
add_filter( "am_notifications_display", 'exactmetrics_am_notice_optout', 10, 1 );

/**
 * Inline critical css for the menu to prevent breaking the layout when our scripts get blocked by browsers.
 */
function exactmetrics_admin_menu_inline_styles() {
	?>
	<style type="text/css">
		#toplevel_page_exactmetrics_reports .wp-menu-image img,
		#toplevel_page_exactmetrics_settings .wp-menu-image img,
		#toplevel_page_exactmetrics_network .wp-menu-image img {
			width: 18px;
			height: auto;
			padding-top: 7px;
		}
	</style>
	<?php
}

add_action( 'admin_footer', 'exactmetrics_admin_menu_inline_styles', 300 );
