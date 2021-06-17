<?php
global $yarpp;

$yarpp->cache->enforce( (int) $reference_ID, false ); // enforce the cache, but don't force it

if ( $yarpp->debug ) {
	$keywords = $yarpp->cache->get_keywords( $reference_ID );
	$output  .= "<p>body keywords: {$keywords['body']}</p>";
	$output  .= "<p>title keywords: {$keywords['title']}</p>";
}

$output .= '<p><strong>' . __( 'Related Posts:', 'yarpp' ) . '</strong></p>';

if ( $yarpp->debug ) {
	$output .= '<p>last updated: ' . $wpdb->get_var( "select max(date) as updated from {$wpdb->prefix}yarpp_related_cache where reference_ID = '$reference_ID'" ) . '</p>';
}

if ( have_posts() ) {
	$output .= '<style>#yarpp-related-posts ol li { list-style-type: decimal; margin: 10px 0;} #yarpp-related-posts ol li a {text-decoration: none;} .yarpp-related-action {visibility: hidden;}</style>';
	$output .= '<ol id="yarpp-list">';
	while ( have_posts() ) {
		the_post();
		$output .= "<li id='yarpp-related-" . get_the_ID() . "'><a class='row-title' href='post.php?action=edit&post=" . get_the_ID() . "'>" . get_the_title() . '</a>';
		$output .= ' (' . round( get_the_score(), 3 ) . ')';
		$output .= " <span class='yarpp-related-action' id=yarpp-related-" . get_the_ID() . "-action'><span class='edit'><a href='post.php?action=edit&post=" . get_the_ID() . "'>" . __( 'Edit', 'yarpp' ) . "</a></span> | <span class='view'><a href='" . get_permalink() . "' target='_blank'>" . __( 'View', 'yarpp' ) . '</a></span></span>';
		$output .= '</li>';
	}
	$output .= '</ol>';
	$output .= '<p>' . __( 'Whether all matches are actually displayed and how they are displayed depends on your YARPP display options.', 'yarpp' ) . ' ' . __( 'Updating the post may change the matches.', 'yarpp' ) . '</p>';
} else {
	$output .= '<p><em>' . __( 'No related posts matched.', 'yarpp' ) . ' ' . __( 'Updating the post may change the matches.', 'yarpp' ) . '</em></p>';
}

$output .= '<p class="yarpp-metabox-options"><a href="' . esc_url( admin_url( 'options-general.php?page=yarpp' ) ) . '" class="button-secondary">' . __( 'Configure Options', 'yarpp' ) . '</a> <a id="yarpp-refresh" href="#" class="button-secondary">' . __( 'Refresh', 'yarpp' ) . '</a><span class="spinner"></span></p>';
