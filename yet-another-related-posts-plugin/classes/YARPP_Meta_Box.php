<?php

class YARPP_Meta_Box {
	protected $template_text = null;
	/**
	 * @var YARPP
	 */
	protected $yarpp = null;

	public function __construct() {
		global $yarpp;
		$this->yarpp         = $yarpp;
		$this->template_text =
			sprintf(__(
			'This advanced option gives you full power to easily customize the look and feel of YARPP.' . ' ' .
			'YARPP Custom Templates are written in PHP and saved in your active theme folder.' . ' ' .
			'%1$sLearn more about YARPP Custom Templates%2$s.',
			'yet-another-related-posts-plugin'
			),
			'<a href="https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation" target="_blank">', '</a>');
	}

	private function offer_copy_templates() {
		return ( ! $this->yarpp->diagnostic_custom_templates() && $this->yarpp->admin->can_copy_templates() );
	}

	public function checkbox( $option, $desc, $class = null, $yarpp_args = array() ) {
		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_checkbox.phtml';
	}

	public function radio( $option, $desc, $class = null, $value = null ) {
		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_radio.phtml';
	}

	public function template_checkbox( $rss = false, $class = null ) {
		$pre             = ( $rss ) ? 'rss_' : '';
		$chosen_template = yarpp_get_option( $pre . 'template' );
		$choice          = ( $chosen_template === false )
							? 'builtin' : ( ( $chosen_template === 'thumbnails' ) ? 'thumbnails' : 'custom' );

		$builtIn = ( $choice === 'builtin' ) ? 'active' : null;

		$thumbnails     = ( $choice === 'thumbnails' ) ? 'active' : null;
		$diagPostThumbs = ( ! $this->yarpp->diagnostic_post_thumbnails() ) ? 'disabled' : null;

		$custom         = ( $choice === 'custom' ) ? 'active' : null;
		$diagCustTemplt = ( ! $this->yarpp->diagnostic_custom_templates() ) ? 'disabled' : null;

		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_template_checkbox.phtml';
	}

	public function template_file( $rss = false, $class = null ) {
		$pre             = ( $rss ) ? 'rss_' : '';
		$chosen_template = yarpp_get_option( $pre . 'template' );

		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_template_file.phtml';
	}

	public function textbox( $option, $desc, $size = 2, $class = null, $note = null ) {
		$value = esc_attr( yarpp_get_option( $option ) );

		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_textbox.phtml';
	}

	public function beforeafter( $options, $desc, $size = 10, $class = null, $note = null ) {
		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_beforeafter.phtml';
	}

	/* MARK: Last cleaning spot */
	public function tax_weight( $taxonomy ) {
		$weight  = (int) yarpp_get_option( "weight[tax][{$taxonomy->name}]" );
		$require = (int) yarpp_get_option( "require_tax[{$taxonomy->name}]" );

		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_tax_weight.phtml';
	}
	/**
	 * Render the select options.
	 *
	 * @param string $name Select option name.
	 * @param array  $options Array of option value.
	 * @param string $desc label description.
	 * @param array  $args Array of additional argument.
	 */
	public function yarpp_select_option( $name, $options, $desc = '', $args = '' ) {
		include YARPP_DIR . '/includes/phtmls/yarpp_meta_box_select.phtml';
	}
	/* MARK: Last cleaning spot */
	public function weight( $option, $desc ) {
		$weight = (int) yarpp_get_option( "weight[$option]" );

		$fulltext = $this->yarpp->db_schema->database_supports_fulltext_indexes() ? '' : ' readonly="readonly" disabled="disabled"';

		echo "<div class='yarpp_form_row yarpp_form_select'><div class='yarpp_form_label'>{$desc}</div><div>";
		echo "<select name='weight[{$option}]'>";
		echo "<option {$fulltext} value='no'" . ( ( ! $weight ) ? ' selected="selected"' : '' ) . '  >' . __( 'do not consider', 'yet-another-related-posts-plugin' ) . '</option>';
		echo "<option {$fulltext} value='consider'" . ( ( $weight == 1 ) ? ' selected="selected"' : '' ) . '  > ' . __( 'consider', 'yet-another-related-posts-plugin' ) . '</option>';
		echo "<option {$fulltext} value='consider_extra'" . ( ( $weight > 1 ) ? ' selected="selected"' : '' ) . '  > ' . __( 'consider with extra weight', 'yet-another-related-posts-plugin' ) . '</option>';
		echo '</select>';
		echo '</div></div>';
	}

	public function displayorder( $option, $class = null ) {
		echo "<div class='yarpp_form_row yarpp_form_select $class'><div class='yarpp_form_label'>";
			_e( 'Order results:', 'yet-another-related-posts-plugin' );
			echo "</div><div><select name='" . esc_attr( $option ) . "' id='" . esc_attr( $option ) . "'>";
				$order = yarpp_get_option( $option );
		?>
				<option value="score DESC" <?php echo ( $order == 'score DESC' ? ' selected="selected"' : '' ); ?>><?php _e( 'score (high relevance to low)', 'yet-another-related-posts-plugin' ); ?></option>
				<option value="score ASC" <?php echo ( $order == 'score ASC' ? ' selected="selected"' : '' ); ?>><?php _e( 'score (low relevance to high)', 'yet-another-related-posts-plugin' ); ?></option>
				<option value="post_date DESC" <?php echo ( $order == 'post_date DESC' ? ' selected="selected"' : '' ); ?>><?php _e( 'date (new to old)', 'yet-another-related-posts-plugin' ); ?></option>
				<option value="post_date ASC" <?php echo ( $order == 'post_date ASC' ? ' selected="selected"' : '' ); ?>><?php _e( 'date (old to new)', 'yet-another-related-posts-plugin' ); ?></option>
				<option value="post_title ASC" <?php echo ( $order == 'post_title ASC' ? ' selected="selected"' : '' ); ?>><?php _e( 'title (alphabetical)', 'yet-another-related-posts-plugin' ); ?></option>
				<option value="post_title DESC" <?php echo ( $order == 'post_title DESC' ? ' selected="selected"' : '' ); ?>><?php _e( 'title (reverse alphabetical)', 'yet-another-related-posts-plugin' ); ?></option>
				<option value="rand" <?php echo ( $order == 'rand' ? ' selected="selected"' : '' ); ?>><?php _e( 'random', 'yet-another-related-posts-plugin' ); ?></option>
				<?php
				echo '</select></div></div>';
	}
}
