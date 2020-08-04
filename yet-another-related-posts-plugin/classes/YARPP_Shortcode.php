<?php



/**
 * Class YARPP_Shortcode
 * Adds the YARPP shortcode.
 *
 * @author         Mike Nelson
 * @since          5.4.0
 */
class YARPP_Shortcode {
	public function register() {
		add_shortcode(
			'yarpp',
			array($this,'render')
		);
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function render($atts) {
		/** @global $yarpp YARPP */
		global $yarpp;
		$post = get_post();
		if($post instanceof WP_Post){
			return $yarpp->display_related($post->ID,array('domain' => 'shortcode'), false);
		} else {
			return '<!-- YARPP shortcode called but no reference post found. It was probably called outside "the loop".-->';
		}

	}
}