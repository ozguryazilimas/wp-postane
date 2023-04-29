<?php

class YARPP_Cache_Bypass extends YARPP_Cache {

	public $name = 'bypass';

	private $related_postdata = array();
	private $related_IDs      = array();

	/**
	 * SETUP/STATUS
	 */
	function __construct( &$core ) {
		parent::__construct( $core );
	}

	public function is_enabled() {
		return true; // always enabled.
	}

	public function cache_status() {
		return 0; // always uncached.
	}

	public function stats() {
		return array(); // always unknown.
	}

	public function uncached( $limit = 20, $offset = 0 ) {
		return array(); // nothing to cache.
	}

	/**
	 * MAGIC FILTERS
	 */
	public function where_filter( $arg ) {
		global $wpdb;
		
		// modify the where clause to use the related ID list.
		if ( ! count( $this->related_IDs ) ) {
			$this->related_IDs = array( 0 );
		}

		$arg = preg_replace( "!{$wpdb->posts}.ID = \d+!", "{$wpdb->posts}.ID in (" . join( ',', $this->related_IDs ) . ')', $arg );
		
		// if we have recent set, add an additional condition.
		if ( (bool) $this->args['recent'] ) {
			$recent = $this->args['recent'];
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
				$arg .= $wpdb->prepare(
					" AND post_date > date_sub(now(), INTERVAL %d {$recent_unit}) ",
					$recent_number
				);
			}
		}

		return $arg;
	}

	public function orderby_filter( $arg ) {
		/*
		 * Only order by score if the score function is added in fields_filter,
		 * which only happens if there are related posts in the post-data.
		 * If ordering by score also order by post ID to keep them consistent in cases where the score is the same
		 * for multiple posts.
		 */
		if ( $this->score_override && is_array( $this->related_postdata ) && count( $this->related_postdata ) ) {
			$arg = $this->orderby_score( $arg );
		}

		return $arg;
	}

	public function fields_filter( $arg ) {
		global $wpdb;

		if ( is_array( $this->related_postdata ) && count( $this->related_postdata ) ) {
			$scores = array();
			foreach ( $this->related_postdata as $related_entry ) {
				$scores[] = " WHEN {$related_entry['ID']} THEN {$related_entry['score']}";
			}
			$arg .= ", CASE {$wpdb->posts}.ID" . join( '', $scores ) . ' END as score';
		}
		return $arg;
	}

	public function demo_request_filter( $arg ) {
		global $yarpp;
		_deprecated_function( 'YARPP_Cache_Bypass::demo_request_filter', '5.26.0', 'YARPP_Cache_Demo_Bypass::demo_request_filter' );

		return $yarpp->demo_cache_bypass->demo_request_filter($arg);
	}

	public function limit_filter( $arg ) {
		global $wpdb;
		return ( $this->online_limit ) ? " LIMIT {$this->online_limit} " : $arg;
	}

	/**
	 * RELATEDNESS CACHE CONTROL
	 */
	public function begin_yarpp_time( $reference_ID, $args ) {
		global $wpdb;

		$this->yarpp_time = true;
		$options          = array(
			'threshold',
			'show_pass_post',
			'past_only',
			'weight',
			'require_tax',
			'exclude',
			'recent',
			'limit',
			'include_sticky_posts',
			'show_sticky_posts'
		);
		$this->args       = $this->core->parse_args( $args, $options );

		$this->related_postdata = $wpdb->get_results( $this->sql( $reference_ID, $args ), ARRAY_A );
		$this->related_IDs      = wp_list_pluck( $this->related_postdata, 'ID' );

		add_filter( 'posts_where', array( &$this, 'where_filter' ) );
		add_filter( 'posts_orderby', array( &$this, 'orderby_filter' ) );
		add_filter( 'posts_fields', array( &$this, 'fields_filter' ) );
		add_filter( 'post_limits', array( &$this, 'limit_filter' ) );

		add_action( 'pre_get_posts', array( &$this, 'add_signature' ) );
		add_action( 'parse_query', array( &$this, 'set_score_override_flag' ) ); // sets the score override flag.
	}

	public function begin_demo_time( $limit, $order = 'score DESC', $thumbnail = '', $size = '' ) {
		global $yarpp;
		_deprecated_function( 'YARPP_Cache_Bypass::begin_demo_time', '5.26.0', 'YARPP_Cache_Demo_Bypass::begin_demo_time' );

		return $yarpp->demo_cache_bypass->begin_demo_time($limit, $order, $thumbnail, $size);
	}

	public function end_yarpp_time() {
		$this->yarpp_time = false;

		remove_filter( 'posts_where', array( &$this, 'where_filter' ) );
		remove_filter( 'posts_orderby', array( &$this, 'orderby_filter' ) );
		remove_filter( 'posts_fields', array( &$this, 'fields_filter' ) );
		remove_filter( 'post_limits', array( &$this, 'limit_filter' ) );

		remove_action( 'pre_get_posts', array( &$this, 'add_signature' ) );
		remove_action( 'parse_query', array( &$this, 'set_score_override_flag' ) );
	}

	public function end_demo_time() {
		global $yarpp;
		_deprecated_function( 'YARPP_Cache_Bypass::end_demo_time', '5.26.0', 'YARPP_Cache_Demo_Bypass::end_demo_time' );

		return $yarpp->demo_cache_bypass->end_demo_time();
	}

	public function related( $reference_ID = null, $related_ID = null ) {
		global $wpdb;

		if ( ! is_int( $reference_ID ) && ! is_int( $related_ID ) ) {
			_doing_it_wrong( __METHOD__, 'reference ID and/or related ID must be set', '3.4' );
			return;
		}

		// reverse lookup
		if ( is_int( $related_ID ) && is_null( $reference_ID ) ) {
			_doing_it_wrong( __METHOD__, 'YARPP_Cache_Bypass::related cannot do a reverse lookup', '3.4' );
			return;
		}

		$results = $this->query_safely(
			'get_results',
			array(
				$this->sql( $reference_ID ),
				ARRAY_A,
			)
		);
		if ( ! $results || ! count( $results ) || $results instanceof WP_Error ) {
			return false;
		}

		$results_ids = wp_list_pluck( $results, 'ID' );
		if ( is_null( $related_ID ) ) {
			return $results_ids;
		} else {
			return in_array( $related_ID, $results_ids );
		}
	}
}
