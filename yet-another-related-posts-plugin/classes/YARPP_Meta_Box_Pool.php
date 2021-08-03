<?php

class YARPP_Meta_Box_Pool extends YARPP_Meta_Box {
	public function exclude( $taxonomy, $string, $_builtin = true ) {
		global $yarpp;

		echo "<div class='yarpp_form_row yarpp_form_exclude'><div class='yarpp_form_label'>";
		echo $string;
		if ( $_builtin == false ) {
			echo " <span class='yarpp_help dashicons dashicons-info' data-help='" . esc_attr( __( 'This belongs to a custom taxonomy', 'yet-another-related-posts-plugin' ) ) . "'></span>";
		}
		echo "</div><div class='yarpp_scroll_wrapper'><div class='exclude_terms' id='exclude_{$taxonomy}'>";

		$exclude_tt_ids   = wp_parse_id_list( yarpp_get_option( 'exclude' ) );
		$exclude_term_ids = $yarpp->admin->get_term_ids_from_tt_ids( $taxonomy, $exclude_tt_ids );
		if ( count( $exclude_term_ids ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'include'    => $exclude_term_ids,
					'hide_empty' => false,
				)
			);
			foreach ( $terms as $term ) {
				echo "<input type='checkbox' name='exclude[{$term->term_taxonomy_id}]' id='exclude_{$term->term_taxonomy_id}' value='true' checked='checked' /> <label for='exclude_{$term->term_taxonomy_id}'>" . esc_html( $term->name ) . '</label> ';
			}
		}

		echo '</div></div></div>';
	}
	/**
	 * Displays the "include post type" input's HTML.
	 *
	 * @since 5.20.1
	 * @return void
	 */
	public function include_post_type() {
		global $yarpp;

		echo "<div class='yarpp_form_row yarpp_form_include_post_type'><div class='yarpp_form_label'>";
		esc_html_e( 'Post types to include:', 'yet-another-related-posts-plugin' );
		echo "</div><div class='yarpp_scroll_wrapper'><div class='include_post_type' id='include_post_type'>";

		$include_post_type       = yarpp_get_option( 'include_post_type' );
		$include_post_type_array = wp_parse_list( $include_post_type );
		$post_types              = $yarpp->get_post_types( 'objects' );
		foreach ( $post_types as $post_type ) {
			$post_type_title = $post_type->labels->name;
			// Clarify "topics" are from bbPress plugin
			if ( $post_type->name == 'topic' && class_exists( 'bbPress' ) ) {
				$post_type_title = sprintf(
					__( 'BuddyPress %s', 'yet-another-related-posts-plugin' ),
					$post_type_title
				);
			}
			echo "<input data-post-type='{$post_type->name}' type='checkbox' " . checked( in_array( $post_type->name, $include_post_type_array, true ), 1, false ) . " name='include_post_type[{$post_type->name}]' id='include_post_type_{$post_type->name}' value='true' /> <label for='include_post_type_{$post_type->name}'>" . esc_html( $post_type_title ) . '</label> ';
		}
		echo '</div></div></div>';
	}
	public function display() {
		global $yarpp;
		$postTypeHelpMsg =
			'If you don&#39;t want one of these post types to display as related content, ' .
			'uncheck the appropriate box in the &ldquo;Display Options&rdquo; panel below. Make sure you ' .
			'click the &ldquo;Save Changes button&rdquo; at the bottom of this page.';

		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_pool.phtml';
	}

}
