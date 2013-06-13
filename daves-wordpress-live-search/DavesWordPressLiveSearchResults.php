<?php

/**
 * Value object class
 */
class DavesWordPressLiveSearchResults {

	// Search sources
	const SEARCH_CONTENT = 0;
	const SEARCH_WPCOMMERCE = 1;

	public $searchTerms;
	public $results;
	public $displayPostMeta;

	/**
	 * Constructor
	 *
	 * @param string  $searchTerms
	 * @param boolean $displayPostMeta Show author & date for each post. Defaults to TRUE to keep original bahavior from before I added this flag
	 */
	function DavesWordPressLiveSearchResults( $searchTerms, $displayPostMeta = true ) {

		$this->results = array();
		$this->populate( $searchTerms, $displayPostMeta );
		$this->displayPostMeta = $displayPostMeta;

	}

	/**
	 * Run the query and build an array of results
	 *
	 * @global type $wp_locale
	 * @global type $wp_query
	 * @param type    $wpQueryResults
	 * @param type    $displayPostMeta
	 */
	private function populate( $wpQueryResults, $displayPostMeta ) {

		global $wp_locale;
		global $wp_query;
		global $post;

		$dateFormat = get_option( 'date_format' );

		// Get the search terms to include in the AJAX response
		$this->searchTerms = $_GET['s'];

		$wpQueryResults = $wp_query->get_posts();
		$wpQueryResults = apply_filters( 'dwls_alter_results', $wpQueryResults, -1, $this );

		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();

			// Add author names & permalinks
			if ( $displayPostMeta ) {
				$authorName = get_the_author_meta( 'user_nicename', $post->post_author );
				$authorName = apply_filters( 'dwls_author_name', $authorName );
				$post->post_author_nicename = $authorName;
			}

			$post->permalink = get_permalink( $post->ID );

			if ( function_exists( 'get_post_thumbnail_id' ) ) {
				// Support for WP 2.9 post thumbnails
				$postImageID = get_post_thumbnail_id( $post->ID );
				$postImageData = wp_get_attachment_image_src( $postImageID, apply_filters( 'post_image_size', 'thumbnail' ) );
				$hasThumbnailSet = ( $postImageData !== false );
			}
			else {
				// No support for post thumbnails
				$hasThumbnailSet = false;
			}

			if ( $hasThumbnailSet ) {
				$post->attachment_thumbnail = $postImageData[0];
			} else {
				// If no post thumbnail, grab the first image from the post
				$applyContentFilter = get_option( 'daves-wordpress-live-search_apply_content_filter', false );
				$content = $post->post_content;
				if ( $applyContentFilter ) {
					$content = apply_filters( 'the_content', $content );
				}
				$content = str_replace( ']]>', ']]&gt;', $content );
				$post->attachment_thumbnail = $this->firstImg( $content );
			}

			$post->attachment_thumbnail = apply_filters( 'dwls_attachment_thumbnail', $post->attachment_thumbnail );

			$post->post_excerpt = $this->excerpt( $post );

			$post->post_date = date_i18n( $dateFormat, strtotime( $post->post_date ) );
			$post->post_date = apply_filters( 'dwls_post_date', $post->post_date );

			// We don't want to send all this content to the browser
			unset( $post->post_content );

			// xLocalization
			$post->post_title = apply_filters( "localization", $post->post_title );

			$post->post_title = apply_filters( 'dwls_post_title', $post->post_title );

			$post->show_more = true;

			$this->results[] = $post;

		}
	}

	private function excerpt( $result ) {

		static $excerptLength = null;
		// Only grab this value once
		if ( null == $excerptLength ) {
			$excerptLength = intval( get_option( 'daves-wordpress-live-search_excerpt_length' ) );
		}
		// Default value
		if ( 0 == $excerptLength ) {
			$excerptLength = 100;
		}

		if ( empty( $result->post_excerpt ) ) {
			$content = apply_filters( "localization", $result->post_content );
			$excerpt = explode( " ", strrev( substr( strip_tags( $content ), 0, $excerptLength ) ), 2 );
			$excerpt = strrev( $excerpt[1] );
			$excerpt .= " [...]";
		} else {
			$excerpt = apply_filters( "localization", $result->post_excerpt );
		}

		$excerpt = apply_filters( 'the_excerpt', $excerpt );
		$excerpt = apply_filters( 'dwls_the_excerpt', $excerpt );

		return $excerpt;
	}


	public function firstImg( $post_content ) {
		$matches = array();
		$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches );
		if ( isset( $matches[1][0] ) ) {
			$first_img = $matches[1][0];
		}

		if ( empty( $first_img ) ) {
			return '';
		}
		return $first_img;
	}

	public function ajaxSearch() {
		global $wp_query;

		$cacheLifetime = intval( get_option( 'daves-wordpress-live-search_cache_lifetime' ) );
		if ( !is_user_logged_in() && 0 < $cacheLifetime ) {
			$doCache = TRUE;
		} else {
			$doCache = FALSE;
		}

		if ( $doCache ) {
			$cachedResults = DWLSTransients::get( $_REQUEST['s'] );
		}

		if ( ( !$doCache ) || ( FALSE === $cachedResults ) ) {

			$displayPostMeta = (bool) get_option( 'daves-wordpress-live-search_display_post_meta' );

			$results = new DavesWordPressLiveSearchResults( $_GET['s'], $displayPostMeta );

			if ( $doCache ) {
				DWLSTransients::set( $_REQUEST['s'], $results, $cacheLifetime );
			}

		} else {

			// Found it in the cache. Return the results.
			$results = $cachedResults;

		}

		wp_send_json($results);
	}

	public static function pre_get_posts( $query ) {

		// These fields don't seem to be getting set right during an AJAX call
		$query->parse_query( http_build_query( $_GET ) );

		if ( array_key_exists( 'search_source', $_REQUEST ) ) {
			$searchSource = $_GET['search_source'];
		} else {
			$searchSource = intval( get_option( 'daves-wordpress-live-search_source' ) );
		}

		$maxResults = intval( get_option( 'daves-wordpress-live-search_max_results' ) );
		if ( $maxResults === 0 ) {
			$maxResults = -1;
		}

		if ( function_exists( 'relevanssi_do_query' ) ) {
			// Relevanssi isn't treating 0 as "unlimited" results
			// like WordPress's native search does. So we'll replace
			// $maxResults with a really big number, the biggest one
			// PHP knows how to represent, if $maxResults == -1
			// (unlimited)
			if ( -1 == $maxResults ) {
				$maxResults = PHP_INT_MAX;
			}
		}

		$query->set( 'posts_per_page', $maxResults );

		// Override post_type if none provided
		if ( !isset( $_GET['post_type'] ) ) {
			if ( self::SEARCH_WPCOMMERCE === $searchSource ) {
				$query->set( 'post_type', 'wpsc-product' );
			}
		}
	}
}

// Set up the AJAX hooks
add_action( "wp_ajax_dwls_search", array( "DavesWordPressLiveSearchResults", "ajaxSearch" ) );
add_action( "wp_ajax_nopriv_dwls_search", array( "DavesWordPressLiveSearchResults", "ajaxSearch" ) );
add_action( 'pre_get_posts', array( "DavesWordPressLiveSearchResults", "pre_get_posts" ) );
