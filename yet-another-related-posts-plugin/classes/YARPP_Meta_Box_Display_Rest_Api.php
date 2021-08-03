<?php

class YARPP_Meta_Box_Display_Rest_Api extends YARPP_Meta_Box {
	public function display() {

		$this->checkbox( 'rest_api_display', __( 'Display related posts in REST API?', 'yet-another-related-posts-plugin' ) . " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( __( 'This option adds related posts to the REST API.', 'yet-another-related-posts-plugin' ) ) . "'>&nbsp;</span>", '' );
		echo "<div class='yarpp_rest_displayed'>";
		$this->checkbox( 'rest_api_client_side_caching', __( 'Enable in-browser caching?', 'yet-another-related-posts-plugin' ) . " <span class='yarpp_help dashicons dashicons-editor-help' data-help='" . esc_attr( __( 'Web browsers will be instructed to cache YARPP REST API results. This can dramatically increase the speed of subsequent YARPP REST API requests, but it also means stale content might be served for the length of the cache time.', 'yet-another-related-posts-plugin' ) ) . "'>&nbsp;</span>", '' );
		echo '<div class="yarpp_rest_browser_cache_displayed">';
		$this->textbox( 'yarpp_rest_api_cache_time', __( 'Cache time (in minutes)', 'yet-another-related-posts-plugin' ) );
		echo '</div>';

		echo '<a href="https://support.shareaholic.com/hc/en-us/articles/360046456752">';
		esc_html_e( 'YARPP REST API documentation →', 'yet-another-related-posts-plugin' );
		echo '</a>';

		echo '</div>';
	}
}
