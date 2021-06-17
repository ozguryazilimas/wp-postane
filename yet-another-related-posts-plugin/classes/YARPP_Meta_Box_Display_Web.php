<?php

class YARPP_Meta_Box_Display_Web extends YARPP_Meta_Box {
	public function display() {
		global $yarpp;
		$disabled_checkbox           = '';
		$generate_missing_thumbnails = yarpp_get_option( 'generate_missing_thumbnails' );
		if ( defined( 'YARPP_GENERATE_THUMBNAILS' ) ) {
			$disabled_checkbox           = 'disabled';
			$generate_missing_thumbnails = YARPP_GENERATE_THUMBNAILS;
		}
		$yarpp_args = array(
			'template_type'     => 'right-aligned-checkbox',
			'disabled_checkbox' => $disabled_checkbox,
			'option_value'      => $generate_missing_thumbnails,
		);

		echo '<div>';
		echo '<div class="yarpp_code_display"';
		if ( ! $yarpp->get_option( 'code_display' ) ) {
			echo ' style="display: none;"';
		}
		echo '><strong>' . __( 'Website display code example', 'yarpp' ) . '</strong><br /><small>' . __( '(Update options to reload.)', 'yarpp' ) . "</small><br/><div id='display_demo_web'></div></div>";

		echo "<div class='yarpp_form_row yarpp_form_post_types'><div>";
		echo __( 'Automatically display related content on: ', 'yarpp' );
		echo " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( __( 'This option automatically displays YARPP right after the content on single entry pages. If this option is off, you will need to manually insert the <code>[yarpp]</code> shortcode, block or  <code>yarpp_related()</code> PHP function into your theme files.', 'yarpp' ) ) . "'>&nbsp;</span>&nbsp;&nbsp;";
		echo '</div><div>';
		$post_types        = yarpp_get_option( 'auto_display_post_types' );
		$include_post_type = yarpp_get_option( 'include_post_type' );
		$include_post_type = wp_parse_list( $include_post_type );
		foreach ( $yarpp->get_post_types( 'objects' ) as $post_type ) {
			$post_type_title   = $post_type->labels->name;
			$disabled_checkbox = '';
			$hide_help_text    = 'style="display: none;"';
			if ( ! yarpp_get_option( 'cross_relate' ) && ! in_array( $post_type->name, $include_post_type, true ) ) {
				$disabled_checkbox = 'disabled';
				$hide_help_text    = '';
			}
			$help_text = "<span {$hide_help_text} style='color: #d63638;' class='yarpp_help dashicons dashicons-warning' data-help='" . '<p>' . esc_attr( __( "This option is disabled because 'The Pool':", 'yarpp' ) ) . '</p><p>' . esc_attr( __( '1. does not include this post type', 'yarpp' ) ) . '</p><p>' . esc_attr( __( '2. limits results to the same post type as the current post', 'yarpp' ) ) . '</p><p>' . esc_attr( __( 'This combination will always result in no posts displaying on this post type. To enable, in The Pool either include this post type or do not limit results to the same post type.', 'yarpp' ) ) . '</p>' . "'>&nbsp;</span>&nbsp;&nbsp;";
			// Clarify "topics" are from bbPress plugin
			if ( $post_type->name == 'topic' && class_exists( 'bbPress' ) ) {
				$post_type_title = sprintf(
					__( 'BuddyPress %s', 'yarpp' ),
					$post_type_title
				);
			}
			echo "<label for='yarpp_post_type_{$post_type->name}'><input id='yarpp_post_type_{$post_type->name}' name='auto_display_post_types[{$post_type->name}]' type='checkbox' ";
			checked( in_array( $post_type->name, $post_types ) );
			echo $disabled_checkbox;
			echo "/> {$post_type_title}{$help_text}</label> ";
		}
		echo '</div></div>';

		$this->checkbox( 'auto_display_archive', __( 'Display on the front page, category and archive pages', 'yarpp' ) );

		$this->textbox( 'limit', __( 'Maximum number of posts:', 'yarpp' ) );

		$this->template_checkbox( false );
		echo '</div>';
		$get_image_sizes = yarpp_get_image_sizes();
		$chosen_template = yarpp_get_option( 'template' );
		$choice          = false === $chosen_template ? 'builtin' :
			( $chosen_template == 'thumbnails' ? 'thumbnails' : 'custom' );

		// Wrap all the options in a div with a gray border
		echo '<div class="postbox">';

		echo "<div class='yarpp_subbox template_options_custom'";
		if ( $choice != 'custom' ) {
			echo ' style="display: none;"';
		}
		echo '>';
		echo '<div class="yarpp_form_row"><div>' . $this->template_text . '</div></div>';
		$this->template_file( false );
		echo '<div class="yarpp_form_row yarpp_form_radio_label">';
		echo '<div class="yarpp_form_label">' . esc_html( 'Thumbnail Size', 'yarpp' ) . '</div><div>';
		foreach ( $get_image_sizes as $key => $_size ) {
					/* translators: %s: thumbnail key's name */
					$name = sprintf( __( '%1$s (%2$sx%3$s)', 'yarpp' ), $key, $_size['width'], $_size['height'] );
					$this->radio( 'custom_theme_thumbnail_size_display', $name, '', $key );
		}
		echo '</div></div>';
		echo '</div>';

		echo "<div class='yarpp_subbox template_options_thumbnails'";
		if ( $choice != 'thumbnails' ) {
			echo ' style="display: none;"';
		}
		echo '>';
		$this->textbox( 'thumbnails_heading', __( 'Heading:', 'yarpp' ), 40 );
		$this->textbox( 'thumbnails_default', __( 'Default image (URL):', 'yarpp' ), 40 );
		echo '<div class="yarpp_form_row yarpp_form_radio_label">';
		echo '<div class="yarpp_form_label">' . esc_html( 'Thumbnail Size', 'yarpp' ) . '</div><div>';
		foreach ( $get_image_sizes as $key => $_size ) {
					/* translators: %s: thumbnail key's name */
					$name = sprintf( __( '%1$s (%2$sx%3$s)', 'yarpp' ), $key, $_size['width'], $_size['height'] );
					$this->radio( 'thumbnail_size_display', $name, '', $key );
		}
		echo '</div></div>';
		echo '</div>';
		echo '<div class="generate_missing_thumbnails">';
		$this->checkbox( 'generate_missing_thumbnails', __( 'Generate missing thumbnail sizes: ', 'yarpp' ) . "<span class='yarpp_help dashicons dashicons-editor-help' data-help='" . '<p>' . esc_attr( __( 'When enabled, missing thumbnail sizes will be  automatically generated on the fly. Doing this type of processing on the fly may not scale well for larger sites.', 'yarpp' ) ) . '</p><p>' . sprintf( __( 'For larger sites, we recommend the %1$s or %2$s to generate missing thumbnail sizes in a batch process.', 'yarpp' ), '<a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerate Thumbnails plugin</a>', '<a href="https://developer.wordpress.org/cli/commands/media/regenerate/" target="_blank">WP-CLI</a>' ) . '</p><p>' . esc_attr( __( 'New images should continue to automatically get all active thumbnail sizes generated when they are uploaded.', 'yarpp' ) ) . '</p>' . "'>&nbsp;</span>&nbsp;&nbsp;", 'yarpp', $yarpp_args );
		echo '</div>';
		echo "<div class='yarpp_subbox template_options_builtin'";
		if ( $choice != 'builtin' ) {
			echo ' style="display: none;"';
		}
		echo '>';
		$this->beforeafter( array( 'before_related', 'after_related' ), __( 'Before / after related entries:', 'yarpp' ), 15, '', __( 'For example:', 'yarpp' ) . ' &lt;ol&gt;&lt;/ol&gt;' . __( ' or ', 'yarpp' ) . '&lt;div&gt;&lt;/div&gt;' );
		$this->beforeafter( array( 'before_title', 'after_title' ), __( 'Before / after each related entry:', 'yarpp' ), 15, '', __( 'For example:', 'yarpp' ) . ' &lt;li&gt;&lt;/li&gt;' . __( ' or ', 'yarpp' ) . '&lt;dl&gt;&lt;/dl&gt;' );

		$this->checkbox( 'show_excerpt', __( 'Show excerpt?', 'yarpp' ), 'show_excerpt' );
		$this->textbox( 'excerpt_length', __( 'Excerpt length (No. of words):', 'yarpp' ), 10, 'excerpted' );

		$this->beforeafter( array( 'before_post', 'after_post' ), __( 'Before / after (excerpt):', 'yarpp' ), 10, 'excerpted', __( 'For example:', 'yarpp' ) . ' &lt;li&gt;&lt;/li&gt;' . __( ' or ', 'yarpp' ) . '&lt;dl&gt;&lt;/dl&gt;' );
		echo '</div>';

		echo '<div class="yarpp_no_results">';
		$this->textbox( 'no_results', __( 'Default display if no results:', 'yarpp' ), 40, 'sync_no_results' );
		echo '</div>';

		// Close the div that wraps all the options
		echo '</div>';

		$this->displayorder( 'order' );
		$this->checkbox( 'promote_yarpp', __( 'Link to YARPP?', 'yarpp' ) . " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( sprintf( __( 'This option will add the code %s These links are greatly appreciated and keeps us motivated.', 'yarpp' ), '<code>' . htmlspecialchars( sprintf( __( "Powered by <a href='%s' title='WordPress Related Posts Plugin' target='_blank'>YARPP</a>.", 'yarpp' ), 'https://yarpp.com' ) ) . '</code>' ) ) . "'>&nbsp;</span>", 'yarpp' );
	}
}
