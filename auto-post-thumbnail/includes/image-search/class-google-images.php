<?php


class WAPT_GoogleImages implements WAPT_ImageSearch {
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
		$this->key = WAPT_Plugin::app()->getOption( 'google_apikey' );
		$this->cse = WAPT_Plugin::app()->getOption( 'google_cse' );
	}


	/**
	 * @param string $query
	 * @param int $page
	 *
	 * @return WAPT_SearchResponse
	 * @throws Exception
	 */
	public function search( $query, $page ) {
		if ( isset( $_POST['rights'] ) && (int) $_POST['rights'] ) {
			$rights = "&rights=(cc_publicdomain%7Ccc_attribute%7Ccc_sharealike).-(cc_noncommercial%7Ccc_nonderived)";
		} else {
			$rights = '';
		}

		$start = ( ( $page - 1 ) * 10 ) + 1;
		$url   = sprintf( "%s?%s", self::URL, http_build_query( [
			'searchType' => 'image',
			'start'      => $start . $rights,
			'q'          => $query,
			'key'        => $this->key,
			'cx'         => $this->cse,
		] ) );

		/**
		 * @var array|null $limit = [
		 *      'expires' => 0 | int,
		 *      'count' => 10 | int,
		 * ]
		 */
		$limit = WAPT_Plugin::app()->getOption( 'google_limit', [ 'expires' => time(), 'count' => 10 ] );
		if ( time() - $limit['expires'] > 3600 ) {
			$limit['expires'] = time();
			$limit['count']   = 10;
			WAPT_Plugin::app()->updateOption( 'google_limit', $limit );
		}

		if ( ! WAPT_Plugin::app()->premium->is_active() && ! WAPT_Plugin::app()->premium->is_activate() ) {
			if ( $limit['count'] < 1 ) {
				throw new Exception(
					sprintf( __( 'You have reached the limit at the moment. Try again in an 1 hour or <a href="%s">Upgrade to Premium</a>', 'apt' ), WAPT_Plugin::app()->get_support()->get_pricing_url( true, 'license_page' ) )
				);
			}
			$limit['count'] --;
		}

		if ( $start === 1 ) {
			WAPT_Plugin::app()->updateOption( 'google_limit', $limit );
		}

		$response = wp_remote_get( $url, [ 'timeout' => 100 ] );
		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Error: ' . $response->get_error_message() );
		}

		$images   = [];
		$error    = null;
		$response = json_decode( $response['body'], true );
		if ( isset( $response['error'] ) ) {
			$error = $response['error']['message'];
		} elseif ( isset( $response['items'] ) && is_array( $response['items'] ) ) {
			foreach ( $response['items'] as $item ) {
				$image = new WAPT_FoundedImage(
					$item['link'],
					$item['image']['contextLink'],
					$item['image']['thumbnailLink'],
					$item['title'],
					$item['image']['width'],
					$item['image']['height']
				);

				$images[] = $image;
			}
		}

		return new WAPT_SearchResponse( $images, $error );
	}
}
