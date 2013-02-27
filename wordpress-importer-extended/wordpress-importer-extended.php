<?php
/*
Plugin Name: WordPress Importer Extended
Plugin URI: http://www.nicolaskuttler.com/wordpress-plugin/auto-import-wxr-files/
Description: Manual and auto-import of WXR files
Author: Nicolas Kuttler
Version: 0.3
Author URI: http://www.nicolaskuttler.com/

	Copyright (c) 2011 Nicolas Kuttler (http://www.nicolaskuttler.com/)
	WordPress Importer Extended is released under the GNU General Public
	License (GPL)
	http://www.gnu.org/licenses/gpl-2.0.txt
*/

/**
 * Main class
 *
 * @since 0.1
 */
class WordPressImporterExtended {

		/**
		 * Array containing the options
		 *
		 * @since 0.1
		 *
		 * @var array
		 */
		private $options;

		/**
		 * Path to the plugin
		 *
		 * @since 0.1
		 *
		 * @var string
		 */
		protected $plugin_dir;

		/**
		 * Path to the plugin file
		 *
		 * @since 0.1
		 *
		 * @var string
		 */
		protected $plugin_file;

		/**
		 * Plugin config version
		 *
		 * @var string
		 *
		 * @since 0.1
		 */
		protected $version = '0.1';

		/**
		 * Constructor, set up the variables
		 *
		 * @since 0.1
		 */
		public function __construct() {
			$this->options     = get_option('wordpress-importer-extended');
			$this->plugin_file = __FILE__;
			$this->plugin_dir  = dirname( $this->plugin_file );
		}

		/**
		 * Return a specific option value
		 *
		 * @since 0.1
		 *
		 * @param string $option name of option to return
		 * @return mixed
		 */
		protected function get_option( $option ) {
			if ( isset ( $this->options[$option] ) )
				return $this->options[$option];
			else
				return false;
		}

}

if ( is_admin() ) {
	include('inc/admin.php');
	$WordPressImporterExtendedAdmin = new WordPressImporterExtendedAdmin();
}
