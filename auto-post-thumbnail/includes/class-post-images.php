<?php

namespace WBCR\APT;

use WP_Post, WP_Error;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PostImages {

	/**
	 * @var \WAPT_Plugin
	 */
	private $plugin;

	/**
	 * @var WP_Post
	 */
	public $post;

	/**
	 * @var array
	 */
	private $images = [];

	/**
	 * Post Images constructor.
	 *
	 * @param WP_Post|int|string $post Post object or post ID or post content
	 */
	public function __construct( $post = null ) {
		$this->plugin = \WAPT_Plugin::app();

		if ( is_numeric( $post ) ) {
			$post       = get_post( $post, 'OBJECT' );
			$this->post = $post;
		} else if ( is_object( $post ) ) {
			$this->post = $post;
		} else if ( is_string( $post ) ) {
			$new_post               = new \stdClass();
			$new_post->post_content = $post;

			$this->post = new WP_Post( $new_post );
		}

		$this->find_images();
	}

	/**
	 * Get an array of images url, contained in the post
	 *
	 */
	private function find_images() {
		$matches = [];
		$images  = [];

		//do shortcodes before search images
		$post_content = do_shortcode( $this->post->post_content ?? '' );

		// Get all images from post's body
		preg_match_all( '/<\s*img .*?src\s*=\s*[\"\']?([^\"\'> ]*).*?>/i', $post_content, $matches );

		if ( count( $matches ) ) {
			//$this->plugin->logger->debug( "Found from regex: " . var_export( $matches[0], true ) );

			foreach ( $matches[0] as $key => $image ) {
				$title = '';
				preg_match_all( '/<\s*img [^\>]*title\s*=\s*[\"\']?([^\"\'> ]*)/i', $image, $matchesTitle );

				if ( count( $matchesTitle ) && isset( $matchesTitle[1] ) && isset( $matchesTitle[1][ $key ] ) ) {
					$title = $matches[1][ $key ];
				}

				$images[] = [
					'tag'   => $image,
					'url'   => $matches[1][ $key ],
					'title' => $title,
				];
			}
		}

		$this->images = $images;
		$this->plugin->logger->debug( "Found images: " . var_export( $images, true ) );
	}

	/**
	 * Get the post object
	 *
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Get an array of images url, contained in the post
	 *
	 * @return array
	 */
	public function get_images() {
		return $this->images;
	}

	/**
	 * Get count of images url, contained in the post
	 *
	 * @return int
	 */
	public function count_images() {
		return count( $this->images );
	}

	/**
	 * If images is founded in post
	 *
	 * @return bool
	 */
	public function is_images() {
		return (bool) $this->count_images();
	}

	/**
	 * @param string $image
	 * @param string $suffix
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function unique_filepath( $image, $suffix = 'image', $post = null ) {
		if ( ! $post ) {
			$post = $this->get_post();
		}

		$uploads   = wp_upload_dir( current_time( 'mysql' ) );
		$extension = pathinfo( $image, PATHINFO_EXTENSION );

		//$slug      = wp_unique_post_slug( $post->post_title, $post->ID, $post->post_status, $post->post_type, $post->post_parent );
		$slug      = "wapt_{$suffix}";
		$file_path = wp_unique_filename( $uploads['path'], "{$slug}_{$post->post_type}_{$post->ID}.{$extension}" );
		$file_path = "{$uploads['path']}/{$file_path}";

		return $file_path;
	}

	/**
	 * @param string $url
	 * @param string $path_to
	 *
	 * @return bool
	 */
	public function download( $url, $path_to ) {
		$response = wp_remote_get( $url );
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body       = wp_remote_retrieve_body( $response );
			$downloaded = $path_to ? @file_put_contents( $path_to, $body ) : false;
		}

		return isset( $downloaded ) ? (bool) $downloaded : false;
	}
}
