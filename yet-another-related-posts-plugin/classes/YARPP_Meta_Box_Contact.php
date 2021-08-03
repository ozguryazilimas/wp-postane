<?php

class YARPP_Meta_Box_Contact extends YARPP_Meta_Box {
	public function display() {
		global $yarpp;

		$happy = ( $yarpp->diagnostic_happy() ) ? 'spin' : null;

		$out =
		'<ul class="yarpp_contacts">' .
			'<li>' .
				'<a href="https://wordpress.org/support/plugin/yet-another-related-posts-plugin/" target="_blank">' .
					'<span class="icon icon-wordpress"></span> ' . __( 'YARPP Forum', 'yet-another-related-posts-plugin' ) .
				'</a>' .
			'</li>' .
			'<li>' .
				'<a href="https://twitter.com/yarpp" target="_blank">' .
					'<span class="icon icon-twitter"></span> ' . __( 'YARPP on Twitter', 'yet-another-related-posts-plugin' ) .
				'</a>' .
			'</li>' .
			'<li>' .
				'<a href="https://www.facebook.com/groups/357562101611506/" target="_blank">' .
					'<span class="icon icon-facebook"></span> ' . __( 'YARPP User Group on Facebook', 'yet-another-related-posts-plugin' ) .
				'</a>' .
			'</li>' .
			'<li>' .
				'<a href="https://wordpress.org/support/plugin/yet-another-related-posts-plugin/reviews/?rate=5#new-post" target="_blank">' .
					'<span class="icon icon-star ' . $happy . '"></span> ' . __( 'Review YARPP on WordPress.org', 'yet-another-related-posts-plugin' ) .
				'</a>' .
			'</li>' .
		'</ul>';

		echo $out;
	}
}
