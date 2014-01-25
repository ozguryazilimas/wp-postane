<?php

/**
 * Contains the constants
 *
 * Contains the constants used throughout the JSL3 Facebook Wall widget plugin
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

// {{{ constants

/**
 * Path to the current theme directory
 */
if ( ! defined( 'JSL3_FWF_THEME_DIR' ) )
    define( 'JSL3_FWF_THEME_DIR',
        ABSPATH . 'wp-content/themes/' . get_template() );

/**
 * The plugin's directory name
 */
if ( ! defined( 'JSL3_FWF_PLUGIN_NAME' ) )
    define( 'JSL3_FWF_PLUGIN_NAME',
        trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );

/**
 * Path to the plugin directory
 */
if ( ! defined( 'JSL3_FWF_PLUGIN_DIR' ) )
    define( 'JSL3_FWF_PLUGIN_DIR',
        WP_PLUGIN_DIR . '/' . JSL3_FWF_PLUGIN_NAME );

/**
 * URL to the plugin directory
 */
if ( ! defined( 'JSL3_FWF_PLUGIN_URL' ) )
    define( 'JSL3_FWF_PLUGIN_URL',
        WP_PLUGIN_URL . '/' . JSL3_FWF_PLUGIN_NAME );

/**
 * Text domain
 */
 if ( ! defined( 'JSL3_FWF_TEXT_DOMAIN' ) )
    define( 'JSL3_FWF_TEXT_DOMAIN', 'jsl3-fwf' );

/**
 * The name of the admin options stored in the WordPress database
 */
if ( ! defined( 'JSL3_FWF_ADMIN_OPTIONS' ) )
    define( 'JSL3_FWF_ADMIN_OPTIONS', 'jsl3_fwf_admin_options' );

/**
 * The name of the widget class
 */
if ( ! defined( 'JSL3_FWF_WIDGET' ) )
    define( 'JSL3_FWF_WIDGET', 'JSL3_FWF_Widget' );

/**
 * The default value of the widget title
 */
if ( ! defined( 'JSL3_FWF_WIDGET_TITLE' ) )
    define( 'JSL3_FWF_WIDGET_TITLE', '' );

/**
 * The default value of the widget limit
 */
if ( ! defined( 'JSL3_FWF_WIDGET_LIMIT' ) )
    define( 'JSL3_FWF_WIDGET_LIMIT', 25 );

/**
 * The default value of the time to expiration
 */
if ( ! defined( 'JSL3_FWF_TIME_TO_EXPIRE' ) )
    define( 'JSL3_FWF_TIME_TO_EXPIRE', 60 * 60 * 24 * 7 );

/**
 * The default value of the wp cron schedule
 */
if ( ! defined( 'JSL3_FWF_CRON_SCHED' ) )
    define( 'JSL3_FWF_CRON_SCHED', 'daily' );

/**
 * The admin page slug
 */
if ( ! defined( 'JSL3_FWF_SLUG' ) )
    define( 'JSL3_FWF_SLUG', 'jsl3-fwf-options' );

/**
 * The version key used in the WordPress database
 */
if ( ! defined( 'JSL3_FWF_VERSION_KEY' ) )
    define( 'JSL3_FWF_VERSION_KEY', 'jsl3_fwf_version' );

/**
 * The version number used in the WordPress database
 */
if ( ! defined( 'JSL3_FWF_VERSION_NUM' ) )
    define( 'JSL3_FWF_VERSION_NUM', '1.7.2' );

/**
 * The file name used for the UKI files
 */
if ( ! defined( 'UKI_FWF_NAME' ) )
    define( 'UKI_FWF_NAME', 'uki-facebook-wall-feed' );

/**
 * The version key of the UKI files
 */
if ( ! defined( 'UKI_FWF_VERSION_KEY' ) )
    define( 'UKI_FWF_VERSION_KEY', 'uki_fwf_version' );

/**
 * The version number of the UKI files
 */
if ( ! defined( 'UKI_FWF_VERSION_NUM' ) )
    define( 'UKI_FWF_VERSION_NUM', '0.9.5' );

/**
 * The WordPress CRON scheduling hook
 */
if ( ! defined( 'JSL3_FWF_SCHED_HOOK' ) )
    define( 'JSL3_FWF_SCHED_HOOK', 'jsl3_fwf_schedule_hook' );

/**
 * The WordPress CRON scheduling hook
 */
if ( ! defined( 'JSL3_FWF_SHORTCODE' ) )
    define( 'JSL3_FWF_SHORTCODE', 'jsl3_fwf' );

// }}}

// add the version numbers to the WordPress database
update_option( JSL3_FWF_VERSION_KEY, JSL3_FWF_VERSION_NUM );
update_option( UKI_FWF_VERSION_KEY, UKI_FWF_VERSION_NUM );

//echo JSL3_FWF_THEME_DIR . '<br />';
//echo JSL3_FWF_PLUGIN_NAME . '<br />';
//echo JSL3_FWF_PLUGIN_DIR . '<br />';
//echo JSL3_FWF_PLUGIN_URL . '<br />';
//echo basename(__FILE__) . '<br />';

?>
