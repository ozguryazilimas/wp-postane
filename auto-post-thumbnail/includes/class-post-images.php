<?php

namespace WBCR\APT;

use WP_Post, WP_Error;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post images class
 */
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
		} elseif ( is_object( $post ) ) {
			$this->post = $post;
		} elseif ( is_string( $post ) ) {
			$new_post               = new \stdClass();
			$new_post->post_content = $post;

			$this->post = new WP_Post( $new_post );
		}

		$this->find_images();

	}

	/**
	 * Get an array of images url, contained in the post
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
				preg_match_all( '/<\s*img [^\>]*title\s*=\s*[\"\']?([^\"\'> ]*)/i', $image, $matches_title );


				if ( count( $matches_title ) && isset( $matches_title[1] ) && isset( $matches_title[1][ $key ] ) ) {
					$title = $matches[1][ $key ];
				}

				$images[] = [
					'tag'   => $image,
					'url'   => $matches[1][ $key ],
					'title' => $title,
				];
			}
		} else {
			// find all matches youtube links :

			// youtube.com/v/vidid
			//youtube.com/vi/vidid
			//youtube.com/?v=vidid
			//youtube.com/?vi=vidid
			//youtube.com/watch?v=vidid
			//youtube.com/watch?vi=vidid
			//youtu.be/vidid
			//youtube.com/embed/vidid
			//http://youtube.com/v/vidid
			//http://www.youtube.com/v/vidid
			//https://www.youtube.com/v/vidid
			//youtube.com/watch?v=vidid&wtv=wtv
			//http://www.youtube.com/watch?dev=inprogress&v=vidid&feature=related
			//https://m.youtube.com/watch?v=vidid

			preg_match_all("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $post_content, $matches);

			if (count($matches)) {

				foreach ($matches[0] as $key=> $image) {
					$image = '<img src="//img.youtube.com/vi/'.$image.'/maxresdefault.jpg">';
					$title = '';
					preg_match_all( '/<\s*img [^\>]*title\s*=\s*[\"\']?([^\"\'> ]*)/i', $image, $matches_title );

					if ( count( $matches_title ) && isset( $matches_title[1] ) && isset( $matches_title[1][ $key ] ) ) {
						$title = $matches[1][ $key ];
					}

					$images[] = [
						'tag'   => $image,
						'url'   => $matches[1][ $key ],
						'title' => $title,
					];
				}
			}
		}

		$this->images = $images;
		$this->plugin->logger->debug( 'Found images: ' . var_export( $images, true ) );
	}

	private function find_videos() {
		$matches = [];
		$videos  = [];

		//do shortcodes before search images
		$post_content = do_shortcode( $this->post->post_content ?? '' );

		// Get all youtube videos from post's body
		preg_match_all( "#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $post_content, $matches );

		if ( count( $matches ) ) {
			//$this->plugin->logger->debug( "Found from regex: " . var_export( $matches[0], true ) );

			foreach ( $matches[0] as $key => $video ) {
				$title = '';
				preg_match_all( '/<\s*img [^\>]*title\s*=\s*[\"\']?([^\"\'> ]*)/i', $video, $matches_title );

				if ( count( $matches_title ) && isset( $matches_title[1] ) && isset( $matches_title[1][ $key ] ) ) {
					$title = $matches[1][ $key ];
				}

				$videos[] = [
					'tag'   => $video,
					'url'   => $matches[1][ $key ],
					'title' => $title,
				];
			}
		}

		$this->images = $videos;
		$this->plugin->logger->debug( 'Found videos: ' . var_export( $videos, true ) );
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

	public function get_videos() {
		return $this->videos;
	}

	/**
	 * Get count of images url, contained in the post
	 *
	 * @return int
	 */
	public function count_images() {
		return count( $this->images );
	}

	public function count_videos() {
		return count( $this->videos );
	}

	/**
	 * If images is founded in post
	 *
	 * @return bool
	 */
	public function is_images() {
		return (bool) $this->count_images();
	}

	public function is_videos() {
		return (bool) $this->count_videos();
	}

	/**
	 * @param string $image Image path
	 * @param string $suffix Slug suffix
	 * @param WP_Post $post Post object
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
	 * @param string $url URL
	 * @param string $path_to Path to download
	 *
	 * @return bool
	 */
	public function download( $url, $path_to ) {
		$response = wp_remote_get( $url );
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				WP_Filesystem();
			}

			$downloaded = $path_to ? $wp_filesystem->put_contents( $path_to, $body ) : false;
		}

		return isset( $downloaded ) ? (bool) $downloaded : false;
	}
}
