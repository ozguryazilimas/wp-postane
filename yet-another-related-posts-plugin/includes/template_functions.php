<?php

// two YARPP-specific Template Tags, to be used in the YARPP-template Loop.

function the_score() {
	echo get_the_score();
}

function get_the_score() {
	global $post;

	$score = $post->score;
	return apply_filters( 'get_the_score', $score );
}
/**
 * Get Dynamic styles for YARPP's built-in thumbnails template
 *
 * Return CSS for thumbnail sizes.
 *
 * @param array $dimension thumbnail dimension size.
 * @return string
 * @since 5.20.0
 */
function yarpp_thumbnail_inline_css( $dimension = array() ) {
	$height             = ( isset( $dimension['height'] ) ) ? (int) $dimension['height'] : 120;
	$width              = ( isset( $dimension['width'] ) ) ? (int) $dimension['width'] : 120;
	$margin             = 5;
	$width_with_margins = ( $margin * 2 ) + $width;
	$height_with_text   = $height + 50;
	$extra_margin       = 7;
	$yarpp_css          = '';
	$yarpp_css         .= '.yarpp-thumbnails-horizontal .yarpp-thumbnail {';
	$yarpp_css         .= 'width: ' . $width_with_margins . 'px;';
	$yarpp_css         .= 'height: ' . $height_with_text . 'px;';
	$yarpp_css         .= 'margin: ' . $margin . 'px;';
	$yarpp_css         .= 'margin-left: 0px;';
	$yarpp_css         .= '}';

	$yarpp_css .= '.yarpp-thumbnail > img, .yarpp-thumbnail-default {';
	$yarpp_css .= 'width: ' . $width . 'px;';
	$yarpp_css .= 'height: ' . $height . 'px;';
	$yarpp_css .= 'margin: ' . $margin . 'px;';
	$yarpp_css .= '}';

	if ( is_admin() ) {
		$yarpp_css .= '.yarpp-thumbnail > img {';
		$yarpp_css .= 'width: ' . $width . 'px;';
		$yarpp_css .= 'height: ' . $height . 'px !important;';
		$yarpp_css .= 'margin: ' . $margin . 'px;';
		$yarpp_css .= '}';
	}

	$yarpp_css .= '.yarpp-thumbnails-horizontal .yarpp-thumbnail-title {';
	$yarpp_css .= 'margin: ' . $extra_margin . 'px;';
	$yarpp_css .= 'margin-top: 0px;';
	$yarpp_css .= 'width: ' . $width . 'px;';
	$yarpp_css .= '}';

	$yarpp_css .= '.yarpp-thumbnail-default > img {';
	$yarpp_css .= 'min-height: ' . $height . 'px;';
	$yarpp_css .= 'min-width: ' . $width . 'px;';
	$yarpp_css .= '}';

	/**
	 * Filter inline css.
	 *
	 * @param string $yarpp_css inline css.
	 */
	$yarpp_css = apply_filters( 'yarpp_filter_inline_style', $yarpp_css );
	return $yarpp_css;
}
