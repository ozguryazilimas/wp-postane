<?php

class YARPP_Meta_Box_Display_Rest_Api extends YARPP_Meta_Box {
    public function display() {
        echo "<div>";
        $this->checkbox( 'rest_api_display', __( "Display related posts in REST API?", 'yarpp' )." <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( __( "This option adds related posts to the REST API.", 'yarpp' ) ) . "'>&nbsp;</span>", '' );
        echo '<a href="https://support.shareaholic.com/hc/en-us/articles/360046456752">';
        esc_html_e('Read the REST API documentation.', 'yarpp');
        echo "</a></div>";
    }
}