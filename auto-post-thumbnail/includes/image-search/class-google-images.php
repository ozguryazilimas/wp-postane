<?php

namespace WBCR\APT;

use WAPT_Plugin, Exception;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GoogleImages implements ImageSearch {
	const URL = 'https://www.googleapis.com/customsearch/v1';

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var string
	 */
	private $cse;

	public function __construct() {
		$this->key = WAPT_Plugin::app()->getPopulateOption( 'google_apikey' );
		$this->cse = WAPT_Plugin::app()->getPopulateOption( 'google_cse' );
	}


	/**
	 * @param string $query
	 * @param int $page
	 *
	 * @return SearchResponse
	 * @throws Exception
	 */
	public function search( $query, $page ) {
		$rights = '(cc_publicdomain%7Ccc_attribute%7Ccc_sharealike).-(cc_noncommercial%7Ccc_nonderived)';

		$start  = ( ( $page - 1 ) * 10 ) + 1;
		$params = [
			'searchType' => 'image',
			'start'      => $start,
			'rights'     => $rights,
			'q'          => $query,
			'key'        => $this->key,
			'cx'         => $this->cse,
		];

		/**
		 * Filters the list of GET params for Google search query.
		 *
		 * @param array $params Array of GET params for search query.
		 *
		 * @since 3.9.12
		 */
		$params = apply_filters( 'wysc/generation/google_search_params', $params, $query );

		$url = sprintf( '%s?%s', self::URL, http_build_query( $params ) );

		/**
		 * @var array|null $limit = [
		 *      'expires' => 0 | int,
		 *      'count' => 10 | int,
		 * ]
		 */
		$limit = WAPT_Plugin::app()->getPopulateOption( 'google_limit', [
			'expires' => time(),
			'count'   => 10,
		] );
		if ( time() - $limit['expires'] > 3600 ) {
			$limit['expires'] = time();
			$limit['count']   = 10;
			WAPT_Plugin::app()->updateOption( 'google_limit', $limit );
		}

		if ( ! WAPT_Plugin::app()->premium->is_active() && ! WAPT_Plugin::app()->premium->is_activate() ) {
			if ( $limit['count'] < 1 ) {
				WAPT_Plugin::app()->logger->warning( __( 'You have reached the limit at the moment. Try again in an 1 hour', 'apt' ) );
				throw new Exception( sprintf( __( 'You have reached the limit at the moment. Try again in an 1 hour or <a href="%s">Upgrade to Premium</a>', 'apt' ), WAPT_Plugin::app()->get_support()->get_pricing_url( true, 'license_page' ) ) );
			}
			$limit['count'] --;
		}

		if ( 1 === $start ) {
			WAPT_Plugin::app()->updateOption( 'google_limit', $limit );
		}

		$response = wp_remote_get( $url, [ 'timeout' => 100 ] );
		if ( is_wp_error( $response ) ) {
			WAPT_Plugin::app()->logger->error( 'Google search error: ' . $response->get_error_message() );
			throw new Exception( 'Error: ' . $response->get_error_message() );
		}

		$images   = [];
		$error    = null;
		$response = json_decode( $response['body'], true );
		if ( isset( $response['error'] ) ) {
			$error = $response['error']['message'];
		} elseif ( isset( $response['items'] ) && is_array( $response['items'] ) ) {
			foreach ( $response['items'] as $item ) {
				$image = new GoogleFoundedImage( $item );

				$images[] = $image;
			}
		}

		return new SearchResponse( $images, $error );
	}
}
