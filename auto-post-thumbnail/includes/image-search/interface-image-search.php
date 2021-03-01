<?php
namespace WBCR\APT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ImageSearch {

	/**
	 * @param string $query
	 * @param int $page
	 *
	 * @return mixed
	 * @throws \Exception
	 *
	 */
	public function search( $query, $page );

}
