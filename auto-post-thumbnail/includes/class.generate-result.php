<?php

namespace WBCR\APT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Result class
 */
class GenerateResult {

	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * @var int
	 */
	public $thumbnail_id;

	/**
	 * @var string
	 */
	private $generate_method;

	/**
	 * @var string
	 */
	public $status;

	/**
	 * @var string
	 */
	public $message;

	/**
	 * @var array
	 */
	private $methods;

	/**
	 * GenerateResult constructor.
	 *
	 * @param int $post_id Post ID
	 * @param string $generate_method Generate method
	 */
	public function __construct( $post_id, $generate_method = '' ) {
		$this->methods = [
			'find'        => __( 'Find in post', 'apt' ),
			'generate'    => __( 'Generate from title', 'apt' ),
			'both'        => __( 'Find or generate', 'apt' ),
			'google'      => __( 'Google', 'apt' ),
			'find_google' => __( 'Find or Google', 'apt' ),
			'use_default' => __( 'Find or use default image', 'apt' ),
		];

		$this->post_id         = $post_id;
		$this->generate_method = $this->getMethod( $generate_method );
	}

	/**
	 * Set the result data.
	 *
	 * @param string $message Message
	 * @param int $thumbnail_id Thumbnail ID
	 * @param string $status Status
	 */
	public function setResult( $message = '', $thumbnail_id = 0, $status = '' ) {

		$this->thumbnail_id = $thumbnail_id;
		$this->status       = ! empty( $status ) ? $status : __( 'Done', 'apt' );
		$this->message      = $message;
	}

	/**
	 * Return self with result data.
	 *
	 * @param string $message Message
	 * @param int $thumbnail_id Thumbnail ID
	 * @param string $status Status
	 *
	 * @return self
	 */
	public function result( $message = '', $thumbnail_id = 0, $status = '' ) {
		$this->setResult( $message, $thumbnail_id, $status );
		$this->write_to_log();

		return $this;
	}

	/**
	 * @param string $method Method
	 *
	 * @return string
	 */
	private function getMethod( $method ) {
		return $this->methods[ $method ] ?? '';
	}

	/**
	 * @return string
	 */
	public function get_generate_method() {
		return $this->generate_method;
	}

	/**
	 * @param string $url File URL
	 *
	 * @return string
	 */
	private function get_file_size( $url ) {
		$path       = '';
		$parsed_url = parse_url( $url );
		if ( empty( $parsed_url['path'] ) ) {
			return '';
		}
		$file = ABSPATH . ltrim( $parsed_url['path'], '/' );
		if ( file_exists( $file ) ) {
			$bytes = filesize( $file );
			$s     = [ 'b', 'Kb', 'Mb', 'Gb' ];
			$e     = floor( log( $bytes ) / log( 1024 ) );

			return sprintf( '%d ' . $s[ $e ], ( $bytes / pow( 1024, floor( $e ) ) ) );
		}

		return '';

	}

	/**
	 *
	 * @return array
	 */
	public function getData() {
		if ( $this->thumbnail_id ) {
			$data = [
				[
					'post_id'       => $this->post_id,
					'thumbnail_url' => wp_get_attachment_image_url( $this->thumbnail_id, 'thumbnail' ),
					'url'           => get_permalink( $this->post_id ),
					'title'         => get_post( $this->post_id )->post_title,
					'image_size'    => $this->get_file_size( wp_get_attachment_image_url( $this->thumbnail_id, 'full' ) ),
					'type'          => $this->get_generate_method(),
					'status'        => $this->status,
				],
			];
		} else {
			$data = [
				[
					'post_id'   => $this->post_id,
					'url'       => get_permalink( $this->post_id ),
					'title'     => get_post( $this->post_id )->post_title,
					'type'      => $this->get_generate_method(),
					'status'    => $this->status,
					'error_msg' => $this->message,
				],
			];
		}

		return $data;
	}

	public function write_to_log() {
		$data = $this->getData();

		$log = \WAPT_Plugin::app()->getPopulateOption( 'generation_log', [] );
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, 0, 100 );
		}
		\WAPT_Plugin::app()->updatePopulateOption( 'generation_log', array_merge( $data, $log ) );

	}
}
