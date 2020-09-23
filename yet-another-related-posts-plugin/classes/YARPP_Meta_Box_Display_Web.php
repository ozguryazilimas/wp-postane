<?php

class YARPP_Meta_Box_Display_Web extends YARPP_Meta_Box {
    public function display() {
        global $yarpp;

        echo "<div>";
        echo '<div class="yarpp_code_display"';
        if ( !$yarpp->get_option('code_display') )
            echo ' style="display: none;"';
        echo '><strong>' . __( "Website display code example", 'yarpp' ) . '</strong><br /><small>' . __( "(Update options to reload.)", 'yarpp' ) . "</small><br/><div id='display_demo_web'></div></div>";

        echo "<div class='yarpp_form_row yarpp_form_post_types'><div>";
        echo __( "Automatically display related content on: ", 'yarpp' );
        echo " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( __( "This option automatically displays related posts right after the content on single entry pages. If this option is off, you will need to manually insert the shortcode <code>[yarpp]</code>, or PHP functions <code>related_posts()</code> or variants (<code>related_pages()</code> and <code>related_entries()</code>) into your theme files.", 'yarpp' ) ) . "'>&nbsp;</span>&nbsp;&nbsp;";
        echo "</div><div>";
        $post_types = yarpp_get_option( 'auto_display_post_types' );
        foreach ($yarpp->get_post_types('objects') as $post_type) {
            echo "<label for='yarpp_post_type_{$post_type->name}'><input id='yarpp_post_type_{$post_type->name}' name='auto_display_post_types[{$post_type->name}]' type='checkbox' ";
            checked( in_array( $post_type->name, $post_types ) );
            echo "/> {$post_type->labels->name}</label> ";
        }
        echo "</div></div>";

        $this->checkbox( 'auto_display_archive', __( "Display on the front page, category and archive pages", 'yarpp' ) );

        $this->textbox( 'limit', __( 'Maximum number of posts:', 'yarpp' ) );
								
        $this->template_checkbox( false );
        echo "</div>";
				
        $chosen_template = yarpp_get_option( "template" );
        $choice = false === $chosen_template ? 'builtin' :
            ( $chosen_template == 'thumbnails' ? 'thumbnails' : 'custom' );

	    // Wrap all the options in a div with a gray border
	    echo '<div class="postbox">';

        echo "<div class='yarpp_subbox template_options_custom'";
        if ( $choice != 'custom' )
            echo ' style="display: none;"';
        echo ">";
        echo '<div class="yarpp_form_row"><div>' . $this->template_text . '</div></div>';
        $this->template_file( false );
        echo "</div>";

        echo "<div class='yarpp_subbox template_options_thumbnails'";
        if ( $choice != 'thumbnails' )
            echo ' style="display: none;"';
        echo ">";
        $this->textbox( 'thumbnails_heading', __( 'Heading:', 'yarpp' ), 40 );
        $this->textbox( 'thumbnails_default', __( 'Default image (URL):', 'yarpp' ), 40 );
        echo "</div>";

        echo "<div class='yarpp_subbox template_options_builtin'";
        if ( $choice != 'builtin' )
            echo ' style="display: none;"';
        echo ">";
        $this->beforeafter( array( 'before_related', 'after_related' ), __( "Before / after related entries:", 'yarpp' ), 15, '', __( "For example:", 'yarpp' ) . ' &lt;ol&gt;&lt;/ol&gt;' . __( ' or ', 'yarpp' ) . '&lt;div&gt;&lt;/div&gt;' );
        $this->beforeafter( array( 'before_title', 'after_title' ), __( "Before / after each related entry:", 'yarpp' ), 15, '', __( "For example:", 'yarpp' ) . ' &lt;li&gt;&lt;/li&gt;' . __( ' or ', 'yarpp' ) . '&lt;dl&gt;&lt;/dl&gt;' );

        $this->checkbox( 'show_excerpt', __( "Show excerpt?", 'yarpp' ), 'show_excerpt' );
        $this->textbox( 'excerpt_length', __( 'Excerpt length (No. of words):', 'yarpp' ), 10, 'excerpted' );

        $this->beforeafter( array( 'before_post', 'after_post' ), __( "Before / after (excerpt):", 'yarpp' ), 10, 'excerpted', __( "For example:", 'yarpp' ) . ' &lt;li&gt;&lt;/li&gt;' . __( ' or ', 'yarpp' ) . '&lt;dl&gt;&lt;/dl&gt;' );
        echo "</div>";

	    echo '<div class="yarpp_no_results">';
	    $this->textbox( 'no_results', __( 'Default display if no results:', 'yarpp' ), 40, 'sync_no_results' );
	    echo '</div>';

	    // Close the div that wraps all the options
	    echo '</div>';

        $this->displayorder( 'order' );
        $this->checkbox( 'promote_yarpp', __( "Link to YARPP?", 'yarpp' ) . " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( sprintf( __( "This option will add the code %s These links are greatly appreciated and keeps us motivated.", 'yarpp' ), "<code>" . htmlspecialchars( sprintf( __( "Powered by <a href='%s' title='WordPress Related Posts Plugin' target='_blank'>YARPP</a>.", 'yarpp' ), 'https://yarpp.com' ) )."</code>" ) ) . "'>&nbsp;</span>", 'yarpp' );
    }
}