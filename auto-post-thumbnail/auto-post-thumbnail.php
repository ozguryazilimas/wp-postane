<?php

/*
Plugin Name: Auto Post Thumbnail
Plugin URI: http://cm-wp.com/apt/
Description: Automatically generate the Post Thumbnail (Featured Thumbnail) from the first image in post (or any custom post type) only if Post Thumbnail is not set manually.
Version: 3.4.2
Author: Сreativemotion
Author URI: http://cm-wp.com
Text Domain: apt
Domain Path: /languages
*/

/*  Copyright 2019  Сreativemotion  (email : cmwp@gmail.com)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

defined( 'APT_PLUGIN_FILE' ) or define( 'APT_PLUGIN_FILE', __FILE__ );
defined( 'APT_ABSPATH' ) or define( 'APT_ABSPATH', dirname( __FILE__ ) );
defined( 'APT_PLUGIN_BASENAME' ) or define( 'APT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
defined( 'APT_PLUGIN_URL' ) or define( 'APT_PLUGIN_URL', plugins_url( null, __FILE__ ) );

/**
 * Class AutoPostThumbnails
 *
 * @author Alexander Teshabaev <sasha.tesh@gmail.com>
 * @copyright (c) 2018, Webcraftic Ltd
 */
class AutoPostThumbnails {

	/**
	 * @var AutoPostThumbnails
	 */
	public static $instance;

	/**
	 * AutoPostThumbnails constructor.
	 */
	public function __construct () {

		$this->init_includes();
		$this->init();
		$this->init_textdomain();
	}

	/**
	 * Get existing instance or create new one.
	 *
	 * @return AutoPostThumbnails
	 */
	public static function instance () {
		if ( static::$instance === null ) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	/**
	 * Init includes.
	 */
	private function init_includes () {
		require __DIR__ . '/src/class.template.php';
	}

	/**
	 * Initiate all required hooks.
	 */
	private function init () {
		$apt_ag = get_option( 'wbcr_apt_auto_generation' );

		if ( $apt_ag ) {
			add_action( 'publish_post', [ $this, 'publish_post' ] );

			// This hook will now handle all sort publishing including posts, custom types, scheduled posts, etc.
			add_action( 'transition_post_status', [ $this, 'check_required_transition' ], 10, 3 );
		}

		add_action( 'admin_notices', [ $this, 'check_perms' ] );
		add_action( 'admin_menu', [ $this, 'init_admin_menu' ] );

		// Plugin hook for adding CSS and JS files required for this plugin
		add_action( 'admin_enqueue_scripts', [
			$this,
			'enqueue_assets',
		] );

		// Hook to implement AJAX request
		add_action( 'wp_ajax_generatepostthumbnail', [
			$this,
			'ajax_process_post',
		] );
	}

	/**
	 * Init language support.
	 */
	public function init_textdomain () {
		load_plugin_textdomain( "apt", false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Register the management page
	 */
	public function init_admin_menu () {
		add_options_page(
			'Auto Post Thumbnail',
			'Auto Post Thumbnail',
			'manage_options',
			'generate-post-thumbnails',
			[ $this, 'render' ]
		);
	}

	/**
	 * Enqueue assets.
	 *
	 * @param $hook_suffix
	 *
	 * @return void
	 */
	public function enqueue_assets ( $hook_suffix ) {
		if ( 'settings_page_generate-post-thumbnails' != $hook_suffix ) {
			return;
		}

		// WordPress 3.1 vs older version compatibility
		if ( wp_script_is( 'jquery-ui-widget', 'registered' ) ) {
			wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( 'jquery-ui/jquery.ui.progressbar.min.js', __FILE__ ), array(
				'jquery-ui-core',
				'jquery-ui-widget',
			), '1.7.2' );
		} else {
			wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( 'jquery-ui/ui.progressbar.js', __FILE__ ), array( 'jquery-ui-core' ), '1.7.2' );
		}

		wp_enqueue_style( 'style', plugins_url( 'css/style.css', __FILE__ ) );

		wp_enqueue_style( 'jquery-ui-genpostthumbs', plugins_url( 'jquery-ui/redmond/jquery-ui-1.7.2.custom.css', __FILE__ ), array(), '1.7.2' );
	}

	/**
	 * Renders main HTML content of the admin page.
	 */
	public function render () {
		echo APT_Template::render( 'index' );
	}

	/**
	 * Process single post to generate the post thumbnail
	 *
	 * @return void
	 */
	public function ajax_process_post () {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( '-1' );
		}

		$id    = (int) $_POST['id'];

		if ( empty( $id ) ) {
			die( '-1' );
		}

		set_time_limit( 60 );

		// Pass on the id to our 'publish' callback function.
		echo $this->publish_post( $id );

		die( - 1 );
	}

	/**
	 * Check whether the required directory structure is available so that the plugin can create thumbnails if needed.
	 * If not, don't allow plugin activation.
	 */
	public function check_perms () {
		$uploads = wp_upload_dir( current_time( 'mysql' ) );

		if ( $uploads['error'] ) {
			echo '<div class="updated"><p>';
			echo $uploads['error'];

			if ( function_exists( 'deactivate_plugins' ) ) {
				deactivate_plugins( 'auto-post-thumbnail/auto-post-thumbnail.php', 'auto-post-thumbnail.php' );
				echo '<br /> ' . esc_html__( 'This plugin has been automatically deactivated.', 'apt' );
			}

			echo '</p></div>';
		}
	}

	/**
	 * Function to check whether scheduled post is being published. If so, apt_publish_post should be called.
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param WP_Post $post Instance of post.
	 *
	 * @return void
	 */
	public function check_required_transition ( $new_status = '', $old_status = '', $post = '' ) {

		if ( 'publish' == $new_status ) {
			$this->publish_post( $post->ID );
		}
	}

	/**
	 * Return sql query, which allows to receive all the posts without thumbnails
	 *
	 * @return string
	 */
	public function get_posts_query() {
		global $wpdb;

		return "SELECT * FROM {$wpdb->posts} p WHERE p.post_status = 'publish' AND p.post_type = 'post' AND (
 			p.ID NOT IN (
				SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_thumbnail_id', 'skip_post_thumb')
			) OR
			NOT EXISTS (SELECT p2.ID FROM {$wpdb->posts} p2 WHERE p2.ID = (SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND post_id = p.ID) AND p2.post_type = 'attachment')
		)";
	}

	/**
	 * Function to save first image in post as post thumbmail.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool
	 */
	public function publish_post( $post_id ) {
		global $wpdb;

		// First check whether Post Thumbnail is already set for this post.
		$_thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
		if (
			$_thumbnail_id && $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE id = '$_thumbnail_id' AND post_type = 'attachment'" )
			|| get_post_meta( $post_id, 'skip_post_thumb', true )
		) {
			return true;
		}

		$post = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE id = $post_id" );

		// Initialize variable used to store list of matched images as per provided regular expression
		$matches = array();

		$thumb_id = false;

		// Get all images from post's body
		preg_match_all( '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)/i', $post[0]->post_content, $matches );

		if ( count( $matches ) ) {
			foreach ( $matches[0] as $key => $image ) {
				/**
				 * If the image is from the WordPress own media gallery, then it appends the thumbnail id to a css class.
				 * Look for this id in the IMG tag.
				 */
				preg_match( '/wp-image-([\d]*)/i', $image, $thumb_id );

				if ( $thumb_id ) {
					$thumb_id = $thumb_id[1];
				}

				if ( ! get_post( $thumb_id ) ) {
					$thumb_id = false;
				}

				// If thumb id is not found, try to look for the image in DB. Thanks to "Erwin Vrolijk" for providing this code.
				if ( ! $thumb_id ) {
					$image  = substr( $image, strpos( $image, '"' ) + 1 );
					$result = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE guid = '" . $image . "'" );
					if ( $result ) {
						$thumb_id = $result[0]->ID;
					}
				}

				// Still no id found? Try found by post_name
				if ( ! $thumb_id ) {
					if ( isset( $matches[0][ $key ] ) && ! empty( $matches[0][ $key ] ) ) {
						$image_url = trim( $matches[0][ $key ] );
						$_parts    = explode( '/', $image_url );
						$image_url = array_pop( $_parts );
						$_parts    = explode( '.', $image_url );
						$image_url = array_shift( $_parts );

						if ( $image_url ) {
							$result = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_name = '" . $image_url . "' AND post_type = 'attachment'" );
							if ( $result ) {
								$thumb_id = $result[0]->ID;
							}
						}
					}
				}

				// Ok. Still no id found. Some other way used to insert the image in post. Now we must fetch the image from URL and do the needful.
				if ( ! $thumb_id ) {
					$thumb_id = $this->generate_post_thumb( $matches, $key, $post[0]->post_content, $post_id );
				}

				// If we succeed in generating thumb, let's update post meta
				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
					break;
				}
			}
		}

		return (bool) $thumb_id;
	}

	/**
	 * Search through an array for a matching key.
	 *
	 * Examples:
	 * <code>
	 *      $array = array(
	 *          "database.name" => "my_db_name",
	 *          "database.host" => "myhost.com",
	 *          "database.user" => "admin",
	 *          "database.pass" => "a secret."
	 *      );
	 *
	 *      $search = array_contains_key($array, "database");
	 *      var_dump($search);
	 *
	 *      Result:
	 *      array (size=4)
	 *          'database.name' => string 'my_db_name' (length=10)
	 *          'database.host' => string 'myhost.com' (length=10)
	 *          'database.user' => string 'admin' (length=5)
	 *          'database.pass' => string 'a secret.' (length=9)
	 * </code>
	 *
	 * https://gist.github.com/steve-todorov/3671626
	 *
	 * @param array  $input_array
	 * @param string $search_value
	 * @param bool   $case_sensitive
	 *
	 * @return array
	 */
	function array_contains_key( array $input_array, $search_value, $case_sensitive = false ) {
		if ( $case_sensitive ) {
			$preg_match = '/' . $search_value . '/';
		} else {
			$preg_match = '/' . $search_value . '/i';
		}
		$return_array = array();
		$keys         = array_keys( $input_array );
		foreach ( $keys as $k ) {
			if ( preg_match( $preg_match, $k ) ) {
				$return_array[ $k ] = $input_array[ $k ];
			}
		}

		return $return_array;
	}

	/**
	 * Fetch image from URL and generate required thumbnails.
	 *
	 * @param $matches
	 * @param $key
	 * @param $post_content
	 * @param $post_id
	 *
	 * @return int|WP_Error|null
	 */
	public function generate_post_thumb ( $matches, $key, $post_content, $post_id ) {
		// Make sure to assign correct title to the image. Extract it from img tag
		$imageTitle = '';
		preg_match_all( '/<\s*img [^\>]*title\s*=\s*[\""\']?([^\""\'>]*)/i', $post_content, $matchesTitle );

		if ( count( $matchesTitle ) && isset( $matchesTitle[1] ) && isset( $matchesTitle[1][ $key ] ) ) {
			$imageTitle = $matchesTitle[1][ $key ];
		}

		// Get the URL now for further processing
		$imageUrl = $matches[1][ $key ];

		// Get the file name
		$filename = substr( $imageUrl, ( strrpos( $imageUrl, '/' ) ) + 1 );

		if ( ! ( ( $uploads = wp_upload_dir( current_time( 'mysql' ) ) ) && false === $uploads['error'] ) ) {
			return null;
		}

		// Generate unique file name
		$filename = wp_unique_filename( $uploads['path'], $filename );

		// Move the file to the uploads dir
		$new_file = $uploads['path'] . "/$filename";

		if ( ! ini_get( 'allow_url_fopen' ) ) {
			$file_data = $this->curl_get_file_contents( $imageUrl );
		} else {
			$file_data = @file_get_contents( $imageUrl );
		}

		if ( ! $file_data ) {
			return null;
		}

		//Fix for checking file extensions
		$exts = explode( ".", $filename );
		if ( count( $exts ) > 2 ) {
			return null;
		}

		$allowed = get_allowed_mime_types();
		$ext     = pathinfo( $new_file, PATHINFO_EXTENSION );
		if ( ! $this->array_contains_key( $allowed, $ext ) ) {
			return null;
		}

		file_put_contents( $new_file, $file_data );

		// Set correct file permissions
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		$mimes = $type = $file = null;

		// Get the file type. Must to use it as a post thumbnail.
		$wp_filetype = wp_check_filetype( $filename, $mimes );

		extract( $wp_filetype );

		// No file type! No point to proceed further
		if ( ( ! $type || ! $ext ) && ! current_user_can( 'unfiltered_upload' ) ) {
			return null;
		}

		// Compute the URL
		$url = $uploads['url'] . "/$filename";

		// Construct the attachment array
		$attachment = array(
			'post_mime_type' => $type,
			'guid'           => $url,
			'post_parent'    => null,
			'post_title'     => $imageTitle,
			'post_content'   => '',
		);

		$thumb_id = wp_insert_attachment( $attachment, $file, $post_id );
		if ( ! is_wp_error( $thumb_id ) ) {
			require_once( ABSPATH . '/wp-admin/includes/image.php' );

			// Added fix by misthero as suggested
			wp_update_attachment_metadata( $thumb_id, wp_generate_attachment_metadata( $thumb_id, $new_file ) );
			update_attached_file( $thumb_id, $new_file );

			return $thumb_id;
		}

		return null;
	}

	/**
	 * Function to fetch the contents of URL using curl in absence of allow_url_fopen.
	 *
	 * Copied from user comment on php.net (http://in.php.net/manual/en/function.file-get-contents.php#82255)
	 */
	public function curl_get_file_contents ( $URL ) {
		$c = curl_init();
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $c, CURLOPT_URL, $URL );
		$contents = curl_exec( $c );
		curl_close( $c );

		if ( $contents ) {
			return $contents;
		}

		return false;
	}
}

/**
 * Get instance of the core class.
 *
 * @return AutoPostThumbnails
 */
function auto_post_thumbnails () {
	return AutoPostThumbnails::instance();
}

// Bootstrap
auto_post_thumbnails();


