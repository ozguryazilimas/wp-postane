<?php

namespace WBCR\APT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class FoundedImage {

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
		$this->link           = $item['link'] ?? '';
		$this->title          = $item['title'] ?? '';
		$this->context_link   = $item['image']['contextLink'] ?? '';
		$this->thumbnail_link = $item['image']['thumbnailLink'] ?? '';

		$this->image         = new \stdClass();
		$this->image->mime   = $item['mime'] ?? '';
		$this->image->size   = $item['image']['byteSize'] ?? '';
		$this->image->width  = $item['image']['width'] ?? '';
		$this->image->height = $item['image']['height'] ?? '';


		preg_match_all( '/.*\/(.*)\.(\w{3,4})?(\?|\/.*)?$/', $this->link, $match );

		$this->file       = new \stdClass();
		$this->file->name = $match[1][0] ?? '';
		$this->file->ext  = $match[2][0] ?? '';

		$this->more_info = $more_info;
	}

	/**
	 * @param string $path_to
	 *
	 * @return bool
	 */
	public function download( $path_to = '' ) {
		$response = wp_remote_get( $this->link );
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body       = wp_remote_retrieve_body( $response );
			$downloaded = $path_to ? @file_put_contents( $path_to, $body ) : false;
		}

		return isset( $downloaded ) ? (bool) $downloaded : false;
	}
}
