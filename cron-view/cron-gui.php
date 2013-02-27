<?php
/*
Plugin Name: Cron GUI
Plugin URI: http://wordpress.org/extend/plugins/cron-view/
Description: See what's in the WP Cron schedule.
Author: Simon Wheatley
Version: 1.03
Author URI: http://simonwheatley.co.uk/wordpress/
*/

/*  Copyright 2008 Simon Wheatley

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require_once( dirname (__FILE__) . '/plugin.php' );

/**
 *
 * @package default
 * @author Simon Wheatley
 **/
class CronGui extends CronGui_Plugin
{

	public function __construct()
	{
		if ( is_admin() ) {
			// Admin menu
			$this->add_action( 'admin_menu' );
		}
		// Off we go
		$this->register_plugin ( 'cron-view', __FILE__ );
	}
	
	// HOOKS
	// -----
	
	public function admin_menu()
	{
		add_submenu_page( 'tools.php', __( 'What\'s in Cron?', 'cron-view' ), __( 'What\'s in Cron?', 'cron-view' ), 'manage_options', 'cron_gui', array( & $this, 'tools_cron_page' )  );
	}
	
	public function tools_cron_page()
	{
		$cron = _get_cron_array();
		$schedules = wp_get_schedules();
		$date_format = _x( 'M j, Y @ G:i', 'Publish box date format', 'cron-view' );
		foreach ( $cron as $timestamp => $cronhooks ) {
			foreach ( (array) $cronhooks as $hook => $events ) {
				foreach ( (array) $events as $key => $event ) {
					$cron[ $timestamp ][ $hook ][ $key ][ 'date' ] = date_i18n( $date_format, $timestamp );
				}
			}
		}
		$this->view_cron( $cron, $schedules, $date_format );
	}
	
	// UTILITIES
	// ---------
	
	// RENDER VIEWS
	// ------------
	
	protected function view_cron( $cron, $schedules )
	{
		$vars = array();
		$vars[ 'cron' ] = $cron;
		$vars[ 'schedules' ] = $schedules;
		$this->render_admin( 'cron-gui', $vars );
	}
	
}

/**
 * Instantiate the plugin
 *
 * @global
 **/

$cron_gui = new CronGui();

?>