<?php
abstract class YARPP_Cache {
	/**
	 * @var YARPP
	 */
	protected $core;
	/**
	 * During "YARPP Time", we add a bunch of filters to modify WP_Query
	 *
	 * @var bool
	 */
	protected $yarpp_time = false;

	/**
	 * Keep track of when we're calculating, so YARPP core can know when to back off from initiating calculating
	 * related again.
	 *
	 * @var bool
	 */
	protected $discovering_keywords = false;
	public $score_override          = false;
	public $online_limit            = false;
	public $last_sql;
	function __construct( &$core ) {
		$this->core = &$core;
		$this->name = $this->name;
	}

	function add_signature( $query ) {
		$query->yarpp_cache_type = $this->name;
	}

	/**
	 * GENERAL CACHE CONTROL
	 */
	public function is_yarpp_time() {
		return $this->yarpp_time;
	}
	public function flush() {
	}

	public function setup() {
	}

	public function upgrade( $last_version ) {
	}

	/*
	 * POST CACHE CONTROL
	 */

	/**
	 * Ensures the YARPP cache is primed (if not, primes it).
	 * Can return early if YARPP shouldn't run, for some reason.
	 *
	 * @param int   $reference_ID post ID to which we're finding related content
	 * @param bool  $force forces refreshing the cache
	 * @param array $args @see YARPP::display_related()
	 *
	 * @return bool|string (YARPP_NO_RELATED | YARPP_RELATED | YARPP_DONT_RUN | false if no good input)
	 */
	function enforce( $reference_ID, $force = false, $args = array() ) {
		/**
		 * @since 3.5.3 Don't compute on revisions.
		 * wp_is_post_revision will return the id of the revision parent instead.
		 */
		if ( $the_post = wp_is_post_revision( $reference_ID ) ) {
			$reference_ID = $the_post;
		}
		if ( ! is_int( $reference_ID ) ) {
			return false;
		}

		$status = $this->is_cached( $reference_ID );
		$status = apply_filters( 'yarpp_cache_enforce_status', $status, $reference_ID );

		// There's a stop signal:
		if ( $status === YARPP_DONT_RUN ) {
			return YARPP_DONT_RUN;
		}

		// If not cached, process now:
		if ( $status === YARPP_NOT_CACHED || $force ) {
			$status = $this->update( (int) $reference_ID, $args );
		}
		// Despite our earlier check, somehow the database doesn't seem to be setup properly
		if ( $status === YARPP_DONT_RUN ) {
			return YARPP_DONT_RUN;
		}
		// There are no related posts
		if ( $status === YARPP_NO_RELATED ) {
			return YARPP_NO_RELATED;
		}

		// There are results
		return YARPP_RELATED;
	}

	/**
	 * @param int $reference_ID
	 * @return string YARPP_NO_RELATED | YARPP_RELATED | YARPP_NOT_CACHED
	 */
	public function is_cached( $reference_ID ) {
		return YARPP_NOT_CACHED;
	}
	public function clear( $reference_ID ) {
	}

	/*
	 * POST STATUS INTERACTIONS
	 */
	/**
	 * Clear the cache for this entry and for all posts which are "related" to it.
	 *
	 * @since 3.2 This is called when a post is deleted.
	 */
	function delete_post( $post_ID ) {
		// Clear the cache for this post.
		$this->clear( (int) $post_ID );

		// Find all "peers" which list this post as a related post and clear their caches
		if ( $peers = $this->related( null, (int) $post_ID ) ) {
			$this->clear( $peers );
		}
	}

	/**
	 * @since 3.2.1 Handle various post_status transitions
	 */
	function transition_post_status( $new_status, $old_status, $post ) {
		$post_ID = $post->ID;
		/**
		 * @since 3.4 Don't compute on revisions
		 * @since 3.5 Compute on the parent instead
		 */
		if ( $the_post = wp_is_post_revision( $post_ID ) ) {
			$post_ID = $the_post;
		}
		// Un-publish
		if ( $old_status === 'publish' && $new_status !== 'publish' ) {
			// Find all "peers" which list this post as a related post and clear their caches
			if ( $peers = $this->related( null, (int) $post_ID ) ) {
				$this->clear( $peers );
			}
		}

		// Publish
		if ( $new_status === 'publish' ) {
			/*
			 * Find everything which is related to this post, and clear them,
			 * so that this post might show up as related to them.
			 */
			if ( $related = $this->related( $post_ID, null ) ) {
				$this->clear( $related );
			}
		}
		/**
		 * @since 3.4 Simply clear the cache on save; don't recompute.
		 */
		$this->clear( (int) $post_ID );
	}

	function set_score_override_flag( $q ) {
		if ( $this->is_yarpp_time() ) {
			$this->score_override = ( isset( $q->query_vars['orderby'] ) && $q->query_vars['orderby'] === 'score' );

			if ( ! empty( $q->query_vars['showposts'] ) ) {
				$this->online_limit = $q->query_vars['showposts'];
			} else {
				$this->online_limit = false;
			}
		} else {
			$this->score_override = false;
			$this->online_limit   = false;
		}
	}
	/**
	 * SQL!
	 */
	protected function sql( $reference_ID = false, $args = array() ) {
		global $wpdb, $post;

		if ( is_object( $post ) && ! $reference_ID ) {
			$reference_ID = $post->ID;
		}

		if ( ! is_object( $post ) || $reference_ID != $post->ID ) {
			$reference_post = get_post( $reference_ID );
		} else {
			$reference_post = $post;
		}

		$options = array(
			'threshold',
			'show_pass_post',
			'past_only',
			'weight',
			'require_tax',
			'exclude',
			'recent',
			'limit',
			'include_sticky_posts',
			'show_sticky_posts',
		);
		extract( $this->core->parse_args( $args, $options ) );

		// The maximum number of items we'll ever want to cache
		$limit = max( $limit, $this->core->get_option( 'rss_limit' ) );

		// Fetch keywords
		$keywords = $this->get_keywords( $reference_ID );

		// SELECT
		$newsql  = $wpdb->prepare(
			'SELECT %d AS reference_ID, ID, ',
			$reference_ID
		);
		$newsql .= 'ROUND(0';
		if ( isset( $weight ) && is_array( $weight ) ) {
			if ( isset( $weight['body'] ) && (int) $weight['body'] ) {
				$newsql .= $wpdb->prepare(
					' + (MATCH (post_content) AGAINST (%s)) * %d',
					$keywords['body'],
					$weight['body']
				);
			}
			if ( isset( $weight['title'] ) && (int) $weight['title'] ) {
				$newsql .= $wpdb->prepare(
					' + (MATCH (post_title) AGAINST (%s)) * %d',
					$keywords['title'],
					$weight['title']
				);
			}

			// Build tax criteria query parts based on the weights
			if ( isset( $weight['tax'] ) && is_array( $weight['tax'] ) ) {
				foreach ( (array) $weight['tax'] as $tax => $tax_weight ) {
					$newsql .= ' + ' . $this->tax_criteria( $reference_ID, $tax ) . ' * ' . intval( $tax_weight );
				}
			}
		}

		$newsql .= ',4) AS score';

		$newsql .= "\n FROM $wpdb->posts \n";

		$exclude_tt_ids = wp_parse_id_list( $exclude );
		if ( count( $exclude_tt_ids ) || ( isset( $weight ) && isset( $weight['tax'] ) && count( (array) $weight['tax'] ) ) || count( $require_tax ) ) {
			$newsql .= "left join $wpdb->term_relationships as terms on ( terms.object_id = {$wpdb->posts}.ID ) \n";
		}

		/*
		 * Where
		 */

		$newsql .= " WHERE post_status IN ( 'publish', 'static' )";
		/**
		 * @since 3.1.8 Revised $past_only option
		 */
		if ( $past_only && ! is_null( $reference_post ) ) {
			$newsql .= $wpdb->prepare(
				' AND post_date <= %s ',
				$reference_post->post_date
			);
		}
		if ( ! $show_pass_post ) {
			$newsql .= " AND post_password ='' ";
		}
		if ( (bool) $recent ) {
			$recent_parts = explode( ' ', $recent );
			if ( count( $recent_parts ) === 2 && isset( $recent_parts[0], $recent_parts[1] ) ) {
				$recent_number = $recent_parts[0];
				if ( in_array(
					$recent_parts[1],
					array_keys(
						$this->core->recent_units()
					)
				) ) {
					$recent_unit = $recent_parts[1];
				} else {
					$recent_unit = 'day';
				}
				$newsql .= $wpdb->prepare(
					" AND post_date > date_sub(now(), INTERVAL %d {$recent_unit}) ",
					$recent_number
				);
			}
		}

		$post_types           = $this->core->get_query_post_types( $reference_post, $args );
		$sanitized_post_types = (array) array_map(
			function ( $item ) {
				global $wpdb;
				return $wpdb->prepare( '%s', $item );
			},
			$post_types
		);
		$newsql              .= ' AND post_type IN (' . implode( ',', $sanitized_post_types ) . ')';
		$post_ids_to_exclude  = array( (int) $reference_ID );
		// Check if include_sticky_posts or show_sticky_posts is being passed in args.
		$include_sticky_posts = isset($show_sticky_posts) ? $show_sticky_posts : $include_sticky_posts;
		$include_sticky_posts = ( isset( $include_sticky_posts ) ) ? $include_sticky_posts : $this->core->get_option( 'include_sticky_posts' );
		if ( ! $include_sticky_posts ) {
			$get_sticky_posts    = get_option( 'sticky_posts' );
			$post_ids_to_exclude = wp_parse_args( $get_sticky_posts, $post_ids_to_exclude );
		}
		// Allow to filter the exluded post ids.
		$post_ids_to_exclude = apply_filters( 'yarpp_post_ids_to_exclude', $post_ids_to_exclude, $reference_ID );
		$post__not_in        = implode( ',', array_map( 'absint', $post_ids_to_exclude ) );
		$newsql             .= " AND {$wpdb->posts}.ID NOT IN ($post__not_in)";
		// GROUP BY
		$newsql .= "\n GROUP BY ID \n";

		// HAVING
		// number_format fix suggested by vkovalcik! :)
		$safethreshold = number_format( max( $threshold, 0.1 ), 2, '.', '' );
		/**
		 * @since 3.5.3: ID=0 is a special value; never save such a result.
		 */
		$newsql .= $wpdb->prepare(
			' HAVING score >= %f AND ID != 0',
			$safethreshold
		);
		if ( count( $exclude_tt_ids ) ) {
			// $exclude_tt_ids already ran through wp_parse_id_list
			$newsql .= ' AND bit_or(terms.term_taxonomy_id IN (' . join( ',', $exclude_tt_ids ) . ')) = 0';
		}

		$post_type_taxonomies = ! is_null( $reference_post ) ? get_object_taxonomies( $reference_post->post_type, 'names' ) : array();
		foreach ( (array) $require_tax as $tax => $number ) {
			// Double-check the reference post's type actually supports this taxonomy. If not,
			// we'll never find any related posts, as the reference post can't be assigned any terms in this taxonomy.
			// See https://wordpress.org/support/topic/require-at-least-one-taxonomy-limited-to-taxonomies-available-the-post-type/
			if ( in_array( $tax, $post_type_taxonomies ) ) {
				$newsql .= $wpdb->prepare(
					' and ' . $this->tax_criteria( $reference_ID, $tax ) . ' >= %d',
					$number
				);
			}
		}

		$newsql .= $wpdb->prepare(
			' ORDER BY score DESC LIMIT %d',
			$limit
		);

		if ( $this->core->debug ) {
			echo "<!-- $newsql -->";
		}

		$this->last_sql = $newsql;

		return $newsql;
	}

	private function tax_criteria( $reference_ID, $taxonomy ) {
		$terms = get_the_terms( $reference_ID, $taxonomy );
		// if there are no terms of that tax or WP error.
		if ( is_wp_error( $terms ) || false === $terms ) {
			return '(1 = 0)';
		}
		$make_term_object_to_array = wp_list_pluck( $terms, 'term_taxonomy_id' );
		// If empty then return.
		if ( empty( $make_term_object_to_array ) ) {
			return '(1 = 0)';
		}
		$tt_ids = join( ',', $make_term_object_to_array );
		return 'COUNT(DISTINCT IF( terms.term_taxonomy_id IN (' . $tt_ids . '), terms.term_taxonomy_id, null ))';
	}
	/*
	 * KEYWORDS
	 */
	/**
	 * @param int    $ID
	 * @param string $type body | title | all
	 * @return string|array depending on whether "all" were requested or not
	 */
	public function get_keywords( $ID, $type = 'all' ) {
		if ( ! $ID = absint( $ID ) ) {
			return false;
		}
		$keywords = array(
			'body'  => $this->body_keywords( $ID ),
			'title' => $this->title_keywords( $ID ),
		);
		if ( empty( $keywords ) ) {
			return false;
		}

		if ( $type === 'all' ) {
			return $keywords;
		}
		return $keywords[ $type ];
	}

	protected function title_keywords( $ID, $max = 20 ) {
		return apply_filters( 'yarpp_title_keywords', $this->extract_keywords( get_the_title( $ID ), $max, $ID ), $max, $ID );
	}
	protected function body_keywords( $ID, $max = 20 ) {
		$post = get_post( $ID );
		if ( empty( $post ) ) {
			return '';
		}
		$this->discovering_keywords = true;
		$body_content               = apply_filters( 'the_content', $post->post_content );
		$this->discovering_keywords = false;
		$keywords                   = apply_filters( 'yarpp_body_keywords', $this->extract_keywords( $body_content, $max, $ID ), $max, $ID );

		return $keywords;
	}

	private function extract_keywords( $html, $max = 20, $ID = 0 ) {

		/**
		 * @filter yarpp_extract_keywords
		 *
		 * Use this filter to override YARPP's built-in keyword computation
		 * Return values should be a string of space-delimited words
		 *
		 * @param $keywords
		 * @param $html unfiltered HTML content
		 * @param (int) $max maximum number of keywords
		 * @param (int) $ID
		 */
		if ( $keywords = apply_filters( 'yarpp_extract_keywords', false, $html, $max, $ID ) ) {
			return $keywords;
		}
		if ( defined( 'WPLANG' ) ) {
			switch ( substr( WPLANG, 0, 2 ) ) {
				case 'de':
					$lang = 'de_DE';
					break;
				case 'it':
					$lang = 'it_IT';
					break;
				case 'pl':
					$lang = 'pl_PL';
					break;
				case 'bg':
					$lang = 'bg_BG';
					break;
				case 'fr':
					$lang = 'fr_FR';
					break;
				case 'cs':
					$lang = 'cs_CZ';
					break;
				case 'nl':
					$lang = 'nl_NL';
					break;
				default:
					$lang = 'en_US';
					break;
			}
		} else {
			$lang = 'en_US';
		}

		$words_file = YARPP_DIR . '/lang/words-' . $lang . '.php';
		if ( file_exists( $words_file ) ) {
			include $words_file;
		}
		if ( ! isset( $overusedwords ) ) {
			$overusedwords = array();
		}

		// strip tags and html entities
		$text = preg_replace( '/&(#x[0-9a-f]+|#[0-9]+|[a-zA-Z]+);/', '', strip_tags( $html ) );

		// 3.2.2: ignore soft hyphens
		// Requires PHP 5: http://bugs.php.net/bug.php?id=25670
		$softhyphen = html_entity_decode( '&#173;', ENT_NOQUOTES, 'UTF-8' );
		$text       = str_replace( $softhyphen, '', $text );

		$charset = get_option( 'blog_charset' );
		if ( function_exists( 'mb_split' ) && ! empty( $charset ) ) {
			mb_regex_encoding( $charset );
			$wordlist = mb_split( '\s*\W+\s*', mb_strtolower( $text, $charset ) );
		} else {
			$wordlist = preg_split( '%\s*\W+\s*%', strtolower( $text ) );
		}

		// Build an array of the unique words and number of times they occur.
		$tokens = array_count_values( $wordlist );

		// Remove the stop words from the list.
		$overusedwords = apply_filters( 'yarpp_keywords_overused_words', $overusedwords );
		if ( is_array( $overusedwords ) ) {
			foreach ( $overusedwords as $word ) {
				unset( $tokens[ $word ] );
			}
		}
		// Remove words which are only a letter
		foreach ( array_keys( $tokens ) as $word ) {
			if ( function_exists( 'mb_strlen' ) ) {
				if ( mb_strlen( $word ) < 2 ) {
					unset( $tokens[ $word ] );
				} elseif ( strlen( $word ) < 2 ) {
					unset( $tokens[ $word ] );
				}
			}
		}

		arsort( $tokens, SORT_NUMERIC );

		$types = array_keys( $tokens );

		if ( count( $types ) > $max ) {
			$types = array_slice( $types, 0, $max );
		}
		return implode( ' ', $types );
	}

	/**
	 * Does a database query without emitting any warnings if there's an SQL error. (Although they will still show up
	 * in the Query Monitor plugin, which is a feature.)
	 * Throws an exception if there is an error.
	 *
	 * @param string $wpdb_method method on WPDB to call
	 * @param array  $args array of arguments to pass it.
	 *
	 * @return mixed|WP_Error
	 */
	protected function query_safely( $wpdb_method, $args ) {
		global $wpdb;
		$value = call_user_func_array(
			array( $wpdb, $wpdb_method ),
			$args
		);
		if ( $wpdb->last_error ) {
			return new WP_Error( 'yarpp_bad_db', $wpdb->last_error );
		}

		return $value;
	}

	/**
	 * Returns whether or not we're currently discovering the keywords on a reference post.
	 * (This is a very bad time to start looking for related posts! So YARPP core should be able to detect this.)
	 *
	 * @return bool
	 */
	public function discovering_keywords() {
		return $this->discovering_keywords;
	}

	/**
	 * Replaces the WP_Query's orderby clause (which normally orders by date) to orderby score instead
	 *
	 * @param string $sql
	 * @return string
	 */
	protected function orderby_score( $sql ) {
		global $wpdb;
		return str_replace(
			array(
				"$wpdb->posts.post_date ASC",
				"$wpdb->posts.post_date DESC",
			),
			array(
				"score ASC, {$wpdb->posts}.ID ASC",
				"score DESC, {$wpdb->posts}.ID ASC",
			),
			$sql
		);
	}

	/**
	 * Updates the cache.
	 *
	 * @param int   $reference_ID post ID to which we're finding related posts
	 * @param array $args @see YARPP::display_related()
	 * @return string (YARPP_NO_RELATED | YARPP_RELATED | YARPP_DONT_RUN)
	 */
	protected function update( $reference_ID, $args = array() ) {
		return YARPP_RELATED;
	}
}
