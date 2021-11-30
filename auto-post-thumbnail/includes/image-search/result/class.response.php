<?php
namespace WBCR\APT;

use WAPT_Plugin, Exception;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SearchResponse implements \JsonSerializable {

	/**
	 * @var int
	 */
	public $images_count;

	/**
	 * @var FoundedImage
	 */
	public $images;

	/**
	 * @var string|null
	 */
	public $error;

	/**
	 * SearchResponse constructor.
	 *
	 * @param FoundedImage[] $images
	 * @param string|null $images
	 */
	public function __construct( $images, $error = null ) {
		$this->images       = $images;
		$this->error        = $error;
		$this->images_count = count( $images );
	}

	/**
	 * @param $limit
	 */
	public function limit( $limit ) {
		$this->images       = array_slice( $this->images, 0, $limit );
		$this->images_count = count( $this->images );
	}

	/**
	 * @return bool
	 */
	public function is_error() {
		return ! is_null( $this->error );
	}

	public function jsonSerialize() {
		if ( $this->is_error() ) {
			return [
				'error' => $this->error,
			];
		}

		return [
			'images'       => $this->images,
			'images_count' => $this->images_count,
		];
	}
}
