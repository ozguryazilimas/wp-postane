<?php
/* 
Plugin Name: JSL3 Facebook Wall Feed
Plugin URI: http://www.takanudo.com/jsl3-facebook-wall-feed
Description: Displays your facebook wall. Makes use of Fedil Grogan's <a href="http://fedil.ukneeq.com/2011/06/23/facebook-wall-feed-for-wordpress-updated/">Facebook Wall Feed for WordPress</a> code and changes suggested by <a href="http://danielwestergren.se">Daniel Westergren</a> and <a href="http://www.neilpie.co.uk">Neil Pie</a>. German translation provided by Remo Fleckinger.
Version: 1.7.2
Author: Takanudo
Author URI: http://www.takanudo.com
License: GPL2

Copyright 2013  Takanudo  (email : fwf@takanudo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Perform clean-up when uninstalling the widget plugin
 *
 * Removes all WordPress database options created by the widget plugin.
 *
 * PHP version 5
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   WordPress_Plugin
 * @package    JSL3_FWF
 * @author     Takanudo <fwf@takanudo.com>
 * @copyright  2011-2013
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License 3
 * @version    1.7.2
 * @link       http://takando.com/jsl3-facebook-wall-feed
 * @since      File available since Release 1.0
 */

/**
 * Include the constants used by the plugin
 */
include_once 'constants.php';

/**
 * Include the JSL3_Facebook_Wall_Feed class
 */
include_once 'php/class-jsl3-facebook-wall-feed.php';

/**
 * Include the JSL3_FWF_Widget class
 */
include_once 'php/class-jsl3-fwf-widget.php';

/**
 * Include the UKI_Facebook_Wall_Feed class
 */
include_once 'php/class-' . UKI_FWF_NAME . '.php';

// Instantiate a JSL3_Facebook_Wall_Feed object
if ( class_exists( 'JSL3_Facebook_Wall_Feed' ) )
    $jsl3_fwf = new JSL3_Facebook_Wall_Feed();

// {{{ jsl3_facebook_wall_feed_ap()

/**
 * Initializes the admin panel
 *
 * Adds the plugin to the admin settings menu.  Then creates the admin
 * page for this plugin.
 *
 * @access public
 * @since Method available since Release 1.0
 */
if ( ! function_exists( 'jsl3_facebook_wall_feed_ap' ) ) {
    function jsl3_facebook_wall_feed_ap() {
        global $jsl3_fwf_plugin_hook;
        global $jsl3_fwf;

        if ( ! isset( $jsl3_fwf ) )
            return;

        if ( function_exists( 'add_options_page' ) ) {
            $jsl3_fwf_plugin_hook = add_options_page(
                __( 'JSL3 Facebook Wall Feed', JSL3_FWF_TEXT_DOMAIN ),
                __( 'JSL3 Facebook Wall Feed', JSL3_FWF_TEXT_DOMAIN ),
                'manage_options',
                JSL3_FWF_SLUG,
                array( &$jsl3_fwf, 'print_admin_page' ) );
    
            if ( get_bloginfo( 'version' ) >= 3.3 )
                add_action( "load-$jsl3_fwf_plugin_hook",
                    'jsl3_fwf_help_tabs' );
            else
                add_filter( 'contextual_help', 'jsl3_fwf_help', 10, 3 );
    
        }
    }   
}

// }}}
// {{{ jsl3_fwf_print_menu()

/**
 * Print help menu
 *
 * Prints the contextual help menu.
 *
 * @access public
 * @since Method available since Release 1.1
 */
if ( ! function_exists( 'jsl3_fwf_print_menu' ) ) {
    function jsl3_fwf_print_menu() {

        return '<h2 id="jsl3_fwf_top">Menu</h2>' .
               '<ul>' .
               '  <li><a href="#jsl3_fwf_config">' .
               __( 'Configuration', JSL3_FWF_TEXT_DOMAIN ) . '</a></li>' .
               '  <li><a href="#jsl3_fwf_widget">' .
               __( 'Widget Usage', JSL3_FWF_TEXT_DOMAIN ) . '</a></li>' .
               '  <li><a href="#jsl3_fwf_short">' .
               __( 'Shortcode Usage', JSL3_FWF_TEXT_DOMAIN ) . '</a></li>' .
               '</ul>';
    
    }
}

// }}}
// {{{ jsl3_fwf_print_config()

/**
 * Print the configuration help page
 *
 * Prints the configuration contextual help page.
 *
 * @access public
 * @since Method available since Release 1.1
 */
if ( ! function_exists( 'jsl3_fwf_print_config' ) ) {
    function jsl3_fwf_print_config() {

        return '<ol>' .
               '  <li><a href="https://developers.facebook.com/apps">' .
               __( 'Create your Facebook App', JSL3_FWF_TEXT_DOMAIN ) .
               '</a>. ' .
               __( 'NOTE: You cannot use a Facebook Page to create a Facebook App.  You must use your personal Facebook profile.  However, once you create your Facebook App, you can use its App ID and App Secret along with the Facebook ID of the Facebook Page you want to get the feed from on the settings page for the plugin.', JSL3_FWF_TEXT_DOMAIN ) .
               '</li>' .
               '  <li>' .
               __( 'If you get a Request for Permission prompt, then <strong>Allow</strong> Developer to access your basic information.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Allow Developer to access your basic information.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-2.png" alt="' . __( 'Allow Developer to access your basic information.', JSL3_FWF_TEXT_DOMAIN ) . '"  />' .
               '  </li>' .
               '  <li>' .
               __( 'Click <strong>Create New App</strong>.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Click Create New App.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-3.png" alt="' . __( 'Click Create New App.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'Enter an <strong>App Name</strong>. I suggest using the name of your blog. All the other entries are optional. Click <strong>Continue</strong>. You will be prompted with a security check.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Enter an App Name.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-4.png" alt="' . __( 'Enter an App Name.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'On your App page, enter your <strong>App Domain</strong>. Set <strong>Sandbox Mode</strong> to <strong>Disabled</strong>. Under <strong>Select how your app integrates with Facebook</strong> click <strong>Website with Facebook Login</strong> and enter your <strong>Site URL</strong>. Do not use <strong>www.</strong> in your App Domain or Site URL. Then save your changes.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'On your App page, enter your App Domain. Set Sandbox Mode to Disabled. Under Select how your app integrates with Facebook click Website with Facebook Login and enter your Site URL.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-5.png" alt="' . __( 'On your App page, enter your App Domain. Set Sandbox Mode to Disabled. Under Select how your app integrates with Facebook click Website with Facebook Login and enter your Site URL.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'Record your <strong>App ID</strong> and <strong>App Secret</strong>. You will need these later.', JSL3_FWF_TEXT_DOMAIN ) .
               '  </li>' .
               '  <li>' .
               __( 'Go to <strong>JSL3 Facebook Wall Feed</strong> under <strong>Settings</strong> on the Dashboard menu.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Go to JSL3 Facebook Wall Feed under Settings on the Dashboard menu.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-6.png" alt="' . __( 'Go to JSL3 Facebook Wall Feed under Settings on the Dashboard menu.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'Enter your <strong>Facebook ID</strong>. If you do not know your Facebook ID, then use the', JSL3_FWF_TEXT_DOMAIN ) .
               ' <a href="https://developers.facebook.com/tools/explorer">' .
               __( 'Graph API Explorer', JSL3_FWF_TEXT_DOMAIN ) .
               '</a>. ' .
               __( 'Click <strong>Get Access Token</strong>.  You may be prompted to log in.  If you are prompted to <stong>Select permissions</strong>, click <strong>Get Access Token</strong>.  In the text box next to the Submit button, enter the <strong>Facebook Username</strong> used in your Facebook URL (for example, my Facebook URL is http://www.facebook.com/takanudo so my Facebook Username is takanudo) followed by <strong>?fields=id</strong>.  Click <strong>Submit</strong>.  Your Facebook ID will be in the results.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Enter your Facebook ID, App ID, and App Secret.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-7.png" alt="' . __( 'Enter your Facebook ID, App ID, and App Secret.', JSL3_FWF_TEXT_DOMAIN ) . '" /><br />' .
               __( 'Enter the <strong>App ID</strong> and <strong>App Secret</strong> you recorded earlier. Click <strong>Save Changes</strong>.', JSL3_FWF_TEXT_DOMAIN )  .
               '  </li>' .
               '  <li>' .
               __( 'You will be redirected to Facebook. You may be prompted to <strong>Log In</strong> a couple of times.', JSL3_FWF_TEXT_DOMAIN ) .
               '  </li>' .
               '  <li>' .
               __( "Click <strong>Okay</strong> to give your App permission to acess your public profile, friend list, News Feed, status updates and groups and your friends' groups.", JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( "Click Okay to give your App permission to acess your public profile, friend list, News Feed, status updates and groups and your friends' groups.", JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-8.png" alt="' . __( "Click Okay to give your App permission to acess your public profile, friend list, News Feed, status updates and groups and your friends' groups.", JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'Click <strong>Okay</strong> to give your App permission to manage your Pages.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Click Okay to give your App permission to manage your Pages.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-9.png" alt="' . __( 'Click Okay to give your App permission to manage your Pages.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'You will be returned to the JSL3 Facebook Wall Feed settings page with your <strong>Access Token</strong> and its expiration date.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'You will be returned to the JSL3 Facebook Wall Feed settings page with your Access Token and its expiration date.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-10.png" alt="' . __( 'You will be returned to the JSL3 Facebook Wall Feed settings page with your Access Token and its expiration date.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '</ol>';

    }
}

// }}}
// {{{ jsl3_fwf_print_widget()

/**
 * Print the widget usage help page
 *
 * Prints the widget usage contextual help page.
 *
 * @access public
 * @since Method available since Release 1.1
 */
if ( ! function_exists( 'jsl3_fwf_print_widget' ) ) {
    function jsl3_fwf_print_widget() {

        return '<ol>' .
               '  <li>' .
               __( 'Go to <strong>Widgets</strong> under <strong>Appearance</strong> on the Dashboard menu.', JSL3_FWF_TEXT_DOMAIN ) .
               '  </li>' .
               '  <li>' .
               __( 'Drag the <strong>JSL3 Facebook Wall Feed</strong> widget to the sidebar of your choice.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Drag the JSL3 Facebook Wall Feed widget to the sidebar of your choice.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-11.png" alt="' . __( 'Drag the JSL3 Facebook Wall Feed widget to the sidebar of your choice.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'Give the widget a title (or leave it blank) and enter how many posts you want to get from your wall. You may also enter the Facebook ID of the Facebook page you want to display in the widget.  If you leave the Facebook ID blank, the widget will use the Facebook ID entered on the settings page for the plugin.  Click <strong>Save</strong>.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Give the widget a title (or leave it blank) and enter how many posts you want to get from your wall.  You may also enter the Facebook ID of the feed you want to display.  If you leave the Facebook ID blank, the plugin will use the Facebook ID entered on the settings page for the plugin.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-12.png" alt="' . __( 'Give the widget a title (or leave it blank) and enter how many posts you want to get from your wall.  You may also enter the Facebook ID of the feed you want to display.  If you leave the Facebook ID blank, the plugin will use the Facebook ID entered on the settings page for the plugin.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '  <li>' .
               __( 'Go check out your Facebook Wall Feed on your WordPress site.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'Go check out your Facebook Wall Feed on your WordPress site.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-13.png" alt="' . __( 'Go check out your Facebook Wall Feed on your WordPress site.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '</ol>';

    }
}

// }}}
// {{{ jsl3_fwf_print_short()

/**
 * Print the shortcode usage help page
 *
 * Prints the shortcode usage contextual help page.
 *
 * @access public
 * @since Method available since Release 1.1
 */
if ( ! function_exists( 'jsl3_fwf_print_short' ) ) {
    function jsl3_fwf_print_short() {

        return '<ol>' .
               '  <li>' .
               __( 'Add the shortcode <strong>[jsl3_fwf]</strong> or <strong>[jsl3_fwf limit="1"]</strong> or even <strong>[jsl3_fwf limit="1" fb_id="1405307559"]</strong> to the <strong>Text</strong> view of a post or page.  If you do not enter a Facebook ID, the plugin will use the Facebook ID entered on the settings page for the plugin.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title=\'' . __( 'Add the shortcode [jsl3_fwf] or [jsl3_fwf limit="1"] or even [jsl3_fwf limit="1" fb_id="1405307559"] to the Text view of a post or page.  If you do not enter a Facebook ID, the plugin will use the Facebook ID entered on the settings page for the plugin.', JSL3_FWF_TEXT_DOMAIN ) . '\' src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-14.png" alt=\'' . __( 'Add the shortcode [jsl3_fwf] or [jsl3_fwf limit="1"] or even [jsl3_fwf limit="1" fb_id="1405307559"] to the Text view of a post or page.  If you do not enter a Facebook ID, the plugin will use the Facebook ID entered on the settings page for the plugin.', JSL3_FWF_TEXT_DOMAIN ) . '\' />' .
               '  </li>' .
               '  <li>' .
               __( 'View your Facebook Wall Feed on your WordPress post or page.', JSL3_FWF_TEXT_DOMAIN ) . '<br />' .
               '    <img title="' . __( 'View your Facebook Wall Feed on your WordPress post or page.', JSL3_FWF_TEXT_DOMAIN ) . '" src="' . JSL3_FWF_PLUGIN_URL . '/screenshot-15.png" alt="' . __( 'View your Facebook Wall Feed on your WordPress post or page.', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
               '  </li>' .
               '</ol>';

    }
}

// }}}
// {{{ jsl3_fwf_help()

/**
 * Adds contextual help
 *
 * Adds contextual help to the admin page for the plugin on WordPress 3.2.1.
 *
 * @param string $contextual_help the existing contextual help.
 * @param string $screen_id the screen ID.
 * @param string $screen the screen name.
 *
 * @return string the new contextual help
 *
 * @access public
 * @since Method available since Release 1.1
 */
if ( ! function_exists( 'jsl3_fwf_help' ) ) {
    function jsl3_fwf_help( $contextual_help, $screen_id, $screen ) {
        global $jsl3_fwf_plugin_hook;

        if ( $screen_id == $jsl3_fwf_plugin_hook ) {
            $contextual_help =
                jsl3_fwf_print_menu() .
                '<h2 id="jsl3_fwf_config">' .
                __( 'Configuration' , JSL3_FWF_TEXT_DOMAIN ) . '</h2>' .
                jsl3_fwf_print_config() .
                '<a href="#jsl3_fwf_top">' .
                __( 'Back to top', JSL3_FWF_TEXT_DOMAIN ) .
                '</a><br /><br />' .
                '<h2 id="jsl3_fwf_widget">' .
                __( 'Widget Usage', JSL3_FWF_TEXT_DOMAIN ) . '</h2>' .
                jsl3_fwf_print_widget() .
                '<a href="#jsl3_fwf_top">' .
                __( 'Back to top', JSL3_FWF_TEXT_DOMAIN ) .
                '</a><br /><br />' .
                '<h2 id="jsl3_fwf_short">' .
                __( 'Shortcode Usage', JSL3_FWF_TEXT_DOMAIN ) . '</h2>' .
                jsl3_fwf_print_short() .
                '<a href="#jsl3_fwf_top">' .
                __( 'Back to top', JSL3_FWF_TEXT_DOMAIN ) .
                '</a><br /><br />' .
                $contextual_help;
        }

        return $contextual_help;
    }   
}

// }}}
// {{{ jsl3_fwf_help_tabs()

/**
 * Adds contextual help
 *
 * Adds contextual help to the admin page for the plugin on WordPress 3.3.
 *
 * @access public
 * @since Method available since Release 1.1
 */
if ( ! function_exists( 'jsl3_fwf_help_tabs' ) ) {
    function jsl3_fwf_help_tabs() {
        global $jsl3_fwf_plugin_hook;

        $screen = get_current_screen();

        if ( $screen->id == $jsl3_fwf_plugin_hook ) {

            $screen->add_help_tab( array(
                'id' => 'jsl3-fwf-config',
                'title' => __( 'Configuration', JSL3_FWF_TEXT_DOMAIN ),
                'content' => jsl3_fwf_print_config() ) );

            $screen->add_help_tab( array(
                'id' => 'jsl3-fwf-widget',
                'title' => __( 'Widget Usage', JSL3_FWF_TEXT_DOMAIN ),
                'content' => jsl3_fwf_print_widget() ) );

            $screen->add_help_tab( array(
                'id' => 'jsl3-fwf-short',
                'title' => __( 'Shortcode Usage', JSL3_FWF_TEXT_DOMAIN ),
                'content' => jsl3_fwf_print_short() ) );
        
        }
    }   
}

// }}}
// {{{ jsl3_fwf_plugin_action_links()

/**
 * Add a 'Settings' link to the admin plugin page
 *
 * Add a 'Settings' link next to the Deactivate link to the admin plugin
 * page.
 *
 * @param array $links an array of plugin links.
 * @param string $file the basename of the plugin file.
 *
 * @access public
 * @since Method available since Release 1.0
 */
// Add a 'Settings' link to the admin plugin page
if ( ! function_exists( 'jsl3_fwf_plugin_action_links' ) ) {
    function jsl3_fwf_plugin_action_links( $links, $file ) {
        static $this_plugin;

        if ( ! $this_plugin )
            $this_plugin = plugin_basename( __FILE__ );

        if ( $file == $this_plugin ) {
            // The "page" query string value must be equal to the slug
            // of the Settings admin page we defined earlier, which in
            // this case equals "myplugin-settings".
            $settings_link =
                '<a href="' . get_bloginfo('wpurl') .
                '/wp-admin/admin.php?page=' . JSL3_FWF_SLUG .'">' .
                __( 'Settings', JSL3_FWF_TEXT_DOMAIN ) . '</a>';
            array_unshift( $links, $settings_link );
        }

        return $links;
    }
}

// }}}
// {{{ jsl3_fwf_more_schedules()

/**
 * Add more cron schedules.
 *
 * Adds a "Bi-monthly" schedule to Wordress Cron.
 *
 * @access public
 * @since Method available since Release 1.4
 */
if ( ! function_exists( 'jsl3_fwf_more_schedules' ) ) {
    function jsl3_fwf_more_schedules() {
        return array(
            'jsl3_fwf_bimonthly' => array(
                'interval' => 60 * 60 * 24 * 60,
                'display' => 'Once Bimonthly' ) );
    }
}

// }}}

//Actions and Filters
if ( isset( $jsl3_fwf ) ) {
    //Text Domain
    load_plugin_textdomain( JSL3_FWF_TEXT_DOMAIN, FALSE,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    //Actions
    add_action( 'admin_menu', 'jsl3_facebook_wall_feed_ap' );
    add_action( 'activate_' . JSL3_FWF_PLUGIN_NAME . '/' .
        JSL3_FWF_PLUGIN_NAME . '.php', array( &$jsl3_fwf, 'init' ) );
    add_action( 'widgets_init',
        create_function( '',
            "return register_widget( '" . JSL3_FWF_WIDGET . "' );" ) );
    add_action( 'wp_enqueue_scripts', array( &$jsl3_fwf, 'enqueue_style' ) );
    add_action( JSL3_FWF_SCHED_HOOK, array( &$jsl3_fwf, 'renew_token' ) );

    //Filters
    add_filter( 'plugin_action_links', 'jsl3_fwf_plugin_action_links', 10, 2 );
    //add_filter( 'cron_schedules', 'jsl3_fwf_more_schedules' );
    
    //Shortcode
    add_shortcode( JSL3_FWF_SHORTCODE, array( &$jsl3_fwf, 'shortcode_handler' ) );

    //Create initial schedule
    if ( ! wp_next_scheduled( JSL3_FWF_SCHED_HOOK ) )
        wp_schedule_event( time() + 86400, JSL3_FWF_CRON_SCHED, JSL3_FWF_SCHED_HOOK );
}

?>
