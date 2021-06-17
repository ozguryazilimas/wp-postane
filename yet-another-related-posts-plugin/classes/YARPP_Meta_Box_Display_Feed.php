<?php

class YARPP_Meta_Box_Display_Feed extends YARPP_Meta_Box {
	public function display() {
		global $yarpp;

		echo '<div>';
		echo '<div class="rss_displayed yarpp_code_display"';
		if ( ! $yarpp->get_option( 'code_display' ) ) {
			echo ' style="display: none;"';
		}
		echo '><b>' . __( 'RSS display code example', 'yarpp' ) . '</b><br /><small>' . __( '(Update options to reload.)', 'yarpp' ) . "</small><br/><div id='display_demo_rss'></div></div>";

		$this->checkbox( 'rss_display', __( 'Display related posts in feeds?', 'yarpp' ) . " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( __( 'This option displays related posts at the end of each item in your RSS and Atom feeds. No template changes are needed.', 'yarpp' ) ) . "'>&nbsp;</span>", '' );
		$this->checkbox( 'rss_excerpt_display', __( 'Display related posts in the descriptions?', 'yarpp' ) . " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( __( 'This option displays the related posts in the RSS description fields, not just the content. If your feeds are set up to only display excerpts, however, only the description field is used, so this option is required for any display at all.', 'yarpp' ) ) . "'>&nbsp;</span>", 'rss_displayed' );

		$this->textbox( 'rss_limit', __( 'Maximum number of posts:', 'yarpp' ), 2, 'rss_displayed' );
		$this->template_checkbox( true, 'rss_displayed' );
		echo '</div>';

		$chosen_template = yarpp_get_option( 'rss_template' );
		$choice          = false === $chosen_template ? 'builtin' :
			( $chosen_template == 'thumbnails' ? 'thumbnails' : 'custom' );

		// Wrap all the options in a div with a gray border
		echo '<div class="postbox rss_displayed">';
		echo "<div class='yarpp_subbox template_options_custom'";
		if ( $choice != 'custom' ) {
			echo ' style="display: none;"';
		}
		echo '>';
		echo '<div class="yarpp_form_row"><div>' . $this->template_text . '</div></div>';
		$this->template_file( true );
		echo '</div>';

		echo "<div class='yarpp_subbox template_options_thumbnails'";
		if ( $choice != 'thumbnails' ) {
			echo ' style="display: none;"';
		}
		echo '>';
		$this->textbox( 'rss_thumbnails_heading', __( 'Heading:', 'yarpp' ), 40 );
		$this->textbox( 'rss_thumbnails_default', __( 'Default image (URL):', 'yarpp' ), 40 );
		$get_image_sizes = yarpp_get_image_sizes();
		echo '<div class="yarpp_form_row yarpp_form_radio_label">';
					echo '<div class="yarpp_form_label">' . esc_html( 'Thumbnail Size', 'yarpp' ) . '</div><div>';
		foreach ( $get_image_sizes as $key => $_size ) {
					/* translators: %s: thumbnail key's name */
					$name = sprintf( __( '%1$s (%2$s X %3$s)', 'yarpp' ), $key, $_size['width'], $_size['height'] );
					$this->radio( 'thumbnail_size_feed_display', $name, '', $key );
		}
		  echo '</div></div>';
		  echo '</div>';

		echo "<div class='yarpp_subbox template_options_builtin'";
		if ( $choice != 'builtin' ) {
			echo ' style="display: none;"';
		}
		echo '>';
		$this->beforeafter( array( 'rss_before_related', 'rss_after_related' ), __( 'Before / after related entries:', 'yarpp' ), 15, '', __( 'For example:', 'yarpp' ) . ' &lt;ol&gt;&lt;/ol&gt;' . __( ' or ', 'yarpp' ) . '&lt;div&gt;&lt;/div&gt;' );
		$this->beforeafter( array( 'rss_before_title', 'rss_after_title' ), __( 'Before / after each related entry:', 'yarpp' ), 15, '', __( 'For example:', 'yarpp' ) . ' &lt;li&gt;&lt;/li&gt;' . __( ' or ', 'yarpp' ) . '&lt;dl&gt;&lt;/dl&gt;' );

		$this->checkbox( 'rss_show_excerpt', __( 'Show excerpt?', 'yarpp' ), 'show_excerpt' );
		$this->textbox( 'rss_excerpt_length', __( 'Excerpt length (No. of words):', 'yarpp' ), 10, 'excerpted' );

		$this->beforeafter( array( 'rss_before_post', 'rss_after_post' ), __( 'Before / after (excerpt):', 'yarpp' ), 10, 'excerpted', __( 'For example:', 'yarpp' ) . ' &lt;li&gt;&lt;/li&gt;' . __( ' or ', 'yarpp' ) . '&lt;dl&gt;&lt;/dl&gt;' );
		echo '</div>';

		echo '<div class="yarpp_no_results">';
		$this->textbox( 'rss_no_results', __( 'Default display if no results:', 'yarpp' ), 40, 'sync_rss_no_results' );
		echo '</div>';

		// Close the div that wraps all the options
		echo '</div>';

		$this->displayorder( 'rss_order', 'rss_displayed' );

		$this->checkbox( 'rss_promote_yarpp', __( 'Link to YARPP?', 'yarpp' ) . " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( sprintf( __( 'This option will add the code %s These links are greatly appreciated and keeps us motivated.', 'yarpp' ), '<code>' . htmlspecialchars( sprintf( __( "Powered by <a href='%s' title='WordPress Related Posts Plugin' target='_blank'>YARPP</a>.", 'yarpp' ), 'https://yarpp.com' ) ) . '</code>' ) ) . "'>&nbsp;</span>", 'rss_displayed' );
	}
}
