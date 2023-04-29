<?php

class YARPP_Cache_Tables extends YARPP_Cache {
	public $name = 'custom tables';

	/**
	 * SETUP/STATUS
	 */
	function __construct( &$core ) {
		parent::__construct( $core );
	}

	public function is_enabled() {
		return $this->core->db_schema->cache_table_exists();
	}

	public function setup() {
		$this->core->db_schema->create_cache_table();
	}

	public function upgrade( $last_version ) {
		global $wpdb;
		if ( $last_version && version_compare( '3.2.1b4', $last_version ) > 0 ) {
			// Change primary key to be (reference_ID, ID) to ensure that we don't
			// get duplicates.
			// We unfortunately have to clear the cache first here, to ensure that there
			// are no duplicates.
			$this->flush();
			$wpdb->query(
				'ALTER TABLE ' . $wpdb->prefix . YARPP_TABLES_RELATED_TABLE .
				' DROP PRIMARY KEY ,' .
				' ADD PRIMARY KEY ( `reference_ID` , `ID` ),' .
				' ADD INDEX (`score`), ADD INDEX (`ID`)'
			);
		}
		if ( $last_version && version_compare( '3.5.2b3', $last_version ) > 0 ) {
			// flush object cache, as bad is_cached_* values were stored before
			wp_cache_flush();
		}
		if ( $last_version && version_compare( '3.6b1', $last_version ) > 0 ) {
			// remove keywords table
			if ( defined( 'YARPP_TABLES_KEYWORDS_TABLE' ) ) {
				$old_keywords_table = $wpdb->prefix . YARPP_TABLES_KEYWORDS_TABLE;
			} else {
				$old_keywords_table = $wpdb->prefix . 'yarpp_keyword_cache';
			}

			$wpdb->query( "drop table if exists `$old_keywords_table`" );
		}
	}

	public function cache_status() {
		global $wpdb;
		return $wpdb->get_var(
			"select (COUNT(p.ID)-sum(c.ID IS NULL))/COUNT(p.ID)
			FROM `{$wpdb->posts}` as p
			LEFT JOIN `{$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . "` as c ON (p.ID = c.reference_ID)
			WHERE p.post_status = 'publish' "
		);
	}

	public function uncached( $limit = 20, $offset = 0 ) {
		global $wpdb;
		return $wpdb->get_col(
			$wpdb->prepare(
				"select SQL_CALC_FOUND_ROWS p.ID
				FROM `{$wpdb->posts}` as p
				LEFT JOIN `{$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . "` AS c ON (p.ID = c.reference_ID)
				WHERE p.post_status = 'publish' AND c.ID IS NULL
				LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
	}

	public function stats() {
		global $wpdb;
		return wp_list_pluck( $wpdb->get_results( "select num, count(*) AS ct FROM (select 0 + if(id = 0, 0, COUNT(ID)) AS num FROM {$wpdb->prefix}yarpp_related_cache group by reference_ID) AS t GROUP BY num ORDER BY num ASC", OBJECT_K ), 'ct' );
	}

	public function graph_data( $threshold = 5 ) {
		global $wpdb;

		$threshold = absint( $threshold );
		$results   = $wpdb->get_results(
			"select pair, SUM(score) AS score FROM 
			((SELECT concat(reference_ID, '-', ID) AS pair, score FROM {$wpdb->prefix}yarpp_related_cache WHERE reference_ID < ID)
			union
			(SELECT concat(ID, '-', reference_ID) AS pair, score FROM {$wpdb->prefix}yarpp_related_cache WHERE ID < reference_ID)) AS t
			GROUP BY pair
			HAVING sum(score) > {$threshold}"
		);
		return $results;
	}

	/**
	 * MAGIC FILTERS
	 */
	public function join_filter( $arg ) {
		global $wpdb;
		if ( $this->yarpp_time ) {
			$arg .= " JOIN {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " AS yarpp ON {$wpdb->posts}.ID = yarpp.ID";
		}
		return $arg;
	}

	public function where_filter( $arg ) {
		global $wpdb;
		$threshold = $this->core->get_option( 'threshold' );
		if ( $this->yarpp_time ) {

			$arg = str_replace( "$wpdb->posts.ID = ", "yarpp.score >= $threshold AND yarpp.reference_ID = ", $arg );

			$recent = $this->core->get_option( 'recent' );
			if ( (bool) $recent ) {
				$arg .= " AND post_date > date_sub(now(), INTERVAL {$recent}) ";
			}
		}
		return $arg;
	}

	public function orderby_filter( $arg ) {
		global $wpdb;
		// If ordering by score also order by post ID to keep them consistent in cases where the score is the same
		// for multiple posts.
		if ( $this->yarpp_time and $this->score_override ) {
			$arg = $this->orderby_score( $arg );
		}
		return $arg;
	}

	public function fields_filter( $arg ) {
		global $wpdb;
		if ( $this->yarpp_time ) {
			$arg .= ', yarpp.score';
		}
		return $arg;
	}

	public function limit_filter( $arg ) {
		global $wpdb;
		if ( $this->yarpp_time and $this->online_limit ) {
			return " LIMIT {$this->online_limit} ";
		}
		return $arg;
	}

	/**
	 * RELATEDNESS CACHE CONTROL
	 */
	public function begin_yarpp_time() {
		$this->yarpp_time = true;
		add_filter( 'posts_join', array( &$this, 'join_filter' ) );
		add_filter( 'posts_where', array( &$this, 'where_filter' ) );
		add_filter( 'posts_orderby', array( &$this, 'orderby_filter' ) );
		add_filter( 'posts_fields', array( &$this, 'fields_filter' ) );
		add_filter( 'post_limits', array( &$this, 'limit_filter' ) );
		add_action( 'pre_get_posts', array( &$this, 'add_signature' ) );
		// sets the score override flag.
		add_action( 'parse_query', array( &$this, 'set_score_override_flag' ) );
	}

	public function end_yarpp_time() {
		$this->yarpp_time = false;
		remove_filter( 'posts_join', array( &$this, 'join_filter' ) );
		remove_filter( 'posts_where', array( &$this, 'where_filter' ) );
		remove_filter( 'posts_orderby', array( &$this, 'orderby_filter' ) );
		remove_filter( 'posts_fields', array( &$this, 'fields_filter' ) );
		remove_filter( 'post_limits', array( &$this, 'limit_filter' ) );
		remove_action( 'pre_get_posts', array( &$this, 'add_signature' ) );
		remove_action( 'parse_query', array( &$this, 'set_score_override_flag' ) );
	}

	// @return YARPP_NO_RELATED | YARPP_RELATED | YARPP_NOT_CACHED | YARPP_DONT_RUN
	public function is_cached( $reference_ID ) {
		global $wpdb;

		$result = wp_cache_get( 'is_cached_' . $reference_ID, 'yarpp' );
		if ( false !== $result ) {
			return $result;
		}

		// @since 3.5.3b3: check for max instead of min, so that if ID=0 and ID=X
		// are both saved, we act like there *are* related posts, because there are.

		// Also, if there's a database error, hide it and just ignore it. It's probably a missing table that we'll fix
		// next admin request.
		$max_id = $this->query_safely(
			'get_var',
			array(
				"select max(ID) as max_id from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " where reference_ID = $reference_ID",
			)
		);

		if ( $max_id instanceof WP_Error ) {
			return YARPP_DONT_RUN;
		}

		if ( is_null( $max_id ) ) {
			return YARPP_NOT_CACHED;
		}

		if ( 0 == $max_id ) {
			$result = YARPP_NO_RELATED;
		} else {
			$result = YARPP_RELATED;
		}

		wp_cache_set( 'is_cached_' . $reference_ID, $result, 'yarpp' );

		return $result;
	}

	public function clear( $reference_IDs ) {
		global $wpdb;

		$reference_IDs = wp_parse_id_list( $reference_IDs );

		if ( ! count( $reference_IDs ) ) {
			return;
		}

		$wpdb->query( "delete from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . ' where reference_ID in (' . implode( ',', $reference_IDs ) . ')' );
		// @since 3.5.2: clear is_cached_* values as well
		foreach ( $reference_IDs as $id ) {
			wp_cache_delete( 'is_cached_' . $id, 'yarpp' );
		}
	}

	/**
	 * Primes the YARPP related cache table.
	 *
	 * @param int   $reference_ID post ID to which we will find related content
	 * @param array $args see YARPP::display_related()
	 *
	 * @return string (YARPP_RELATED | YARPP_NO_RELATED | YARPP_DONT_RUN)
	 */
	protected function update( $reference_ID, $args = array() ) {
		global $wpdb;

		$original_related = (array) @$this->related( $reference_ID );

		if ( count( $original_related ) ) {
			// clear out the cruft
			$this->clear( $reference_ID );
		}

		$result = $this->query_safely(
			'query',
			array(
				"insert into {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . ' (reference_ID,ID,score) ' . $this->sql( $reference_ID, $args ) . ' on duplicate key update date = now()',
			)
		);
		if ( $result instanceof WP_Error ) {
			return YARPP_DONT_RUN;
		}

		// If there were related entries saved...
		if ( $wpdb->rows_affected ) {
			$new_related = $this->related( $reference_ID );

			if ( $this->core->debug ) {
				echo "<!--YARPP just set the cache for post $reference_ID-->";
			}

			// Clear the caches of any items which are no longer related or are newly related.
			if ( count( $original_related ) ) {
				$this->clear( array_diff( $original_related, $new_related ) );
				$this->clear( array_diff( $new_related, $original_related ) );
			}

			return YARPP_RELATED;
		} else {
			$wpdb->query(
				$wpdb->prepare(
					'insert into ' . $wpdb->prefix . YARPP_TABLES_RELATED_TABLE . ' (reference_ID,ID,score) values (%d,0,0) on duplicate key update date = now()',
					$reference_ID
				)
			);

			// Clear the caches of those which are no longer related.
			if ( count( $original_related ) ) {
				$this->clear( $original_related );
			}

			return YARPP_NO_RELATED;
		}
	}

	public function flush() {
		global $wpdb;
		$wpdb->query( 'truncate table `' . $wpdb->prefix . YARPP_TABLES_RELATED_TABLE . '`' );
		// @since 3.5.2: clear object cache, used for is_cached_* values
		wp_cache_flush();
	}

	public function related( $reference_ID = null, $related_ID = null ) {
		global $wpdb;

		if ( ! is_int( $reference_ID ) && ! is_int( $related_ID ) ) {
			_doing_it_wrong( __METHOD__, 'reference ID and/or related ID must be set', '3.4' );
			return;
		}

		if ( ! is_null( $reference_ID ) && ! is_null( $related_ID ) ) {
			$results = $wpdb->get_col(
				$wpdb->prepare(
					"select ID from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . ' where reference_ID = %d and ID = %d',
					$reference_ID,
					$related_ID
				)
			);
			return count( $results ) > 0;
		}

		// return a list of ID's of "related" entries
		if ( ! is_null( $reference_ID ) ) {
			return $wpdb->get_col(
				$wpdb->prepare(
					"select distinct ID from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . ' where reference_ID = %d and ID != 0',
					$related_ID
				)
			);
		}

		// return a list of entities which list this post as "related"
		if ( ! is_null( $related_ID ) ) {
			return $wpdb->get_col(
				$wpdb->prepare(
					"select distinct reference_ID from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . ' where ID = %d',
					$related_ID
				)
			);
		}

		return false;
	}

	// @param $ID (int)
	// @param $type (string) body | title | all
	// @return (string|array) depending on whether "all" were requested or not
	public function get_keywords( $ID, $type = 'all' ) {
		global $wpdb;

		if ( ! is_int( $ID ) ) {
			return false;
		}

		// @since 4: compute fresh each time, instead of using cache table.
		// the old keyword cache would basically have to be recomputed every time the
		// relatedness cache was recomputed, but no more, so there's no point in keeping
		// these around separately.
		$keywords = array(
			'body'  => $this->body_keywords( $ID ),
			'title' => $this->title_keywords( $ID ),
		);

		if ( empty( $keywords ) ) {
			return false;
		}

		if ( 'all' == $type ) {
			return $keywords;
		}
		return $keywords[ $type ];
	}
}
