<?php

namespace WBCR\APT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class FoundedImage {

	/**
	 * @var string
	 */
	public $link;

	/**
	 * @var string
	 */
	public $context_link;

	/**
	 * @var string
	 */
	public $thumbnail_link;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var array
	 */
	public $more_info;

	/**
	 * @var \stdClass
	 */
	public $image;

	/**
	 * @var \stdClass
	 */
	public $file;

	/**
	 * FoundedImage constructor.
	 *
	 * @param array $item
	 * @param array $more_info
	 */
	public function __construct( $item, $more_info = [] ) {
		$this->parse( $item, $more_info );
	}

	/**
	 * Parse image data
	 *
	 * @param array $item
	 * @param array $more_info
	 */
	abstract protected function parse( $item, $more_info = [] );

	/**
	 * @param string $path_to
	 *
	 * @return bool
	 */
	public function download( $path_to = '' ) {
		$response = wp_remote_get( $this->link );
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
