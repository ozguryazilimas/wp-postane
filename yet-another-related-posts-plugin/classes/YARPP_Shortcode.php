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
			array( $this, 'render' )
		);
	}

	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'reference_id' => null,
				'template'     => null,
				'limit'        => null,
			),
			$atts
		);
		/** @global $yarpp YARPP */
		global $yarpp;
		$post       = get_post( (int) $atts['reference_id'] );
		$yarpp_args = array(
			'domain' => 'shortcode',
		);

		// Custom templates require .php extension.
		if ( isset( $atts['template'] ) && $atts['template'] ) {
			// Normalize parameter.
			$yarpp_args['template'] = trim( $atts['template'] );
			if ( ( strpos( $yarpp_args['template'], 'yarpp-template-' ) === 0 ) && ( strpos( $yarpp_args['template'], '.php' ) === false ) ) {
				$yarpp_args['template'] .= '.php';
			}
		}

		if ( isset( $atts['limit'] ) && $atts['limit'] ) {
			// Normalize parameter.
			$atts['limit'] = trim( $atts['limit'] );

			// Use only if numeric value is passed.
			if ( is_numeric( $atts['limit'] ) ) {
				$yarpp_args['limit'] = (int) $atts['limit'];
			}
		}

		if ( $post instanceof WP_Post ) {
			return $yarpp->display_related(
				$post->ID,
				$yarpp_args,
				false
			);
		} else {
			return '<!-- YARPP shortcode called but no reference post found. It was probably called outside "the loop" or the reference_id provided was invalid.-->';
		}

	}
}
