<?php
/*
  Plugin Name: Basic Comment Quicktags
  Plugin URI: http://halfelf.org/plugins/basic-comment-quicktags/
  Description: Make commenting easier with bold, italic, add link and quote buttons on top of the form.
  Version: 3.4.0
  Author: Mika "Ipstenu" Epstein
  Author URI: http://halfelf.org
  Text Domain: basic-comment-quicktags

	Original author: Marc Tönsing -- http://www.marctv.de/blog/2010/08/25/marctv-wordpress-plugins/
	Copyright 2012-2018 Mika Epstein (ipstenu@halfelf.org)

	This file is part of Basic Comment Quicktags, a plugin for WordPress.

	Basic Comment Quicktags is free software: you can redistribute it and/or 
	modify it under the terms of the GNU General Public License as published 
	by the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	Basic Comment Quicktags is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty
	of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WordPress.  If not, see <http://www.gnu.org/licenses/>.
*/

global $wp_version;
if ( version_compare( $wp_version, "4.6", "<" ) ) { 
	exit( __('This plugin requires WordPress 4.6 or greater.', 'basic-comment-quicktags' ) ); 
}

if ( !class_exists( 'BasicCommentsQuicktagsHELF' ) ) {
	class BasicCommentsQuicktagsHELF {

		protected static $version;
		var $bcq_defaults;
		var $bcq_bbp_fancy;

		/**
		 * __construct
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			add_action( 'init', array( &$this, 'init' ) );

			// Setting plugin defaults here:
			self::$version = '3.4.0';
			$this->bcq_defaults = array(
				'comments' => '1',
				'bbpress'  => '0',
			);

			//global $bcq_bbp_fancy;
			$this->bcq_bbp_fancy = get_option( '_bbp_use_wp_editor' );

			add_option( 'ippy_bcq_options', $this->bcq_defaults );
			
		}

		/**
		 * init function.
		 * 
		 * @access public
		 * @return void
		 */
		public function init() {

			if( !is_admin() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
				add_action( 'wp_print_scripts', array( $this,'add_scripts_frontend' ) );
				add_action( 'wp_print_styles', array( $this,'add_styles_frontend' ) );
			}

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			
			add_filter( 'plugin_row_meta', array( $this, 'donate_link' ), 10, 2 );
			add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
		}

		function add_styles() {
			wp_enqueue_style( 'basic-comment-quicktags', plugins_url( '/quicktags.css' ,__FILE__) );
		}
		function add_scripts() {
			wp_enqueue_script( 'basic-comment-quicktags', plugins_url( '/quicktags.js' ,__FILE__ ), array( 'quicktags', 'jquery' ), self::$version, 1 ); 
			wp_localize_script('basic-comment-quicktags', 'bcq_script_vars', array(
				'quote' => __('quote', 'basic-comment-quicktags')
			) );
		}

		/**
		 * add_styles_frontend function.
		 * 
		 * @access public
		 * @return void
		 */
		function add_styles_frontend() {
			$options = wp_parse_args( get_option( 'ippy_bcq_options'), $this->bcq_defaults );
			if ( function_exists( 'is_bbpress' ) ) {
				if ( is_bbpress()  && ( $options['bbpress'] != '0' ) && !is_null( $options['bbpress'] ) && ( $this->bcq_bbp_fancy == false ) ) {
					$this->add_styles();
				}
			  }
			if ( is_singular() && comments_open() && ( $options['comments'] != '0' ) && !is_null( $options['comments'] ) ) {
				$this->add_styles();
			}
		}

		/**
		 * add_scripts_frontend function.
		 * 
		 * @access public
		 * @return void
		 */
		function add_scripts_frontend() {
			$options = wp_parse_args(get_option( 'ippy_bcq_options'), $this->bcq_defaults );
			if ( function_exists('is_bbpress') ) {
				if ( is_bbpress()  && ( $options['bbpress'] != '0' ) && !is_null( $options['bbpress'] ) && ( $this->bcq_bbp_fancy == false ) ) {
					$this->add_scripts();
				}
			}

			if ( is_singular() && comments_open() && ( $options['comments'] != '0' ) && !is_null( $options['comments'] ) ) {
				$this->add_scripts();
			}
		}

		/**
		 * admin_init function.
		 * 
		 * @access public
		 * @return void
		 */
		function admin_init(){
			register_setting(
				'discussion',                      // settings page
				'ippy_bcq_options',                // option name
				array( $this, 'validate_options')  // validation callback
			);
			
			add_settings_field(
				'ippy_bcq_bbpress',                         // id
				__('Quicktags', 'basic-comment-quicktags'), // setting title
				array( $this, 'setting_input' ),            // display callback
				'discussion',                               // settings page
				'default'                                   // settings section
			);
		}

		/**
		 * setting_input function.
		 * Display and fill the form field
		 * 
		 * @access public
		 * @return void
		 */
		function setting_input() {
			$options = wp_parse_args(get_option( 'ippy_bcq_options'), $this->bcq_defaults );
			
			// echo the field
			?>
			<a name="bcq" value="bcq"></a><input id='comments' name='ippy_bcq_options[comments]' type='checkbox' value='1' <?php checked( $options['comments'], 1 ); ?> /> <?php _e( 'Activate Quicktags for comments', 'basic-comment-quicktags' );
			if ( function_exists( 'is_bbpress' ) && ( $this->bcq_bbp_fancy == false ) ) { ?>
			  <br /><input id='bbpress' name='ippy_bcq_options[bbpress]' type='checkbox' value='1' <?php checked( $options['bbpress'], 1 ); ?> /> <?php _e( 'Activate Quicktags for bbPress', 'basic-comment-quicktags' ); } 
		}
		
		/**
		 * Validate user input.
		 * 
		 * @access public
		 * @param mixed $input
		 * @return void
		 */
		function validate_options( $input ) {
			$options = get_option( 'ippy_bcq_options' );
			$valid = array();

			foreach ( $options as $key => $value ) {
				if ( !isset( $input[$key] ) ) $input[$key] = '0';
			}
			
			foreach ( $options as $key => $value) {
				$valid[$key] = $input[$key];
			}

			unset( $input );
			return $valid;
		}

		/**
		 * Donate link
		 * 
		 * @access public
		 * @param mixed $links
		 * @param mixed $file
		 * @return links
		 */
		function donate_link($links, $file) {
			if ( $file == plugin_basename(__FILE__)) {
				$donate_link = '<a href="https://ko-fi.com/A236CENl/">' . __( 'Donate', 'basic-comment-quicktags' ) . '</a>';
				$links[] = $donate_link;
			}
			return $links;
		}
		
		/**
		 * Add settings to discussion page
		 * 
		 * @access public
		 * @param mixed $links
		 * @param mixed $file
		 * @return void
		 */
		function add_settings_link( $links, $file ) {
			if ( plugin_basename( __FILE__ ) == $file ) {
				$settings_link = '<a href="' . admin_url( 'options-discussion.php' ) . '#bcq">' . __( 'Settings', 'basic-comment-quicktags' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		}

	}
}

//instantiate the class
if ( class_exists( 'BasicCommentsQuicktagsHELF' ) ) {
	new BasicCommentsQuicktagsHELF();
}