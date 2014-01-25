<?php

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

// clean-up the database
delete_option( JSL3_FWF_ADMIN_OPTIONS );
delete_option( JSL3_FWF_VERSION_KEY );
delete_option( 'widget_' . JSL3_FWF_WIDGET );
delete_option( UKI_FWF_VERSION_KEY );
wp_clear_scheduled_hook( JSL3_FWF_SCHED_HOOK );
?>
