<?php

namespace WBCR\APT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class GoogleFoundedImage extends FoundedImage {

	/**
	 * Parse image data
	 *
	 * @param array $item
	 * @param array $more_info
	 */
	protected function parse( $item, $more_info = [] ) {
		$this->link           = $item['link'] ?? '';
		$this->title          = $item['title'] ?? '';
		$this->context_link   = $item['image']['contextLink'] ?? '';
		$this->thumbnail_link = $item['image']['thumbnailLink'] ?? '';

		$this->image         = new \stdClass();
		$this->image->mime   = $item['mime'] ?? '';
		$this->image->size   = $item['image']['byteSize'] ?? '';
		$this->image->width  = $item['image']['width'] ?? '';
		$this->image->height = $item['image']['height'] ?? '';

		$path = parse_url( $this->link, PHP_URL_PATH );
		preg_match_all( '/.*\/(.*)\.(\w{3,4})?(\?|\/.*)?/', $path, $match );

		$this->file       = new \stdClass();
		$this->file->name = $match[1][0] ?? '';
		$this->file->ext  = $match[2][0] ?? '';

		$this->more_info = $more_info;
	}
}
