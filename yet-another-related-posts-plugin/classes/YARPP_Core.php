<?php

/**
 * @since 3.4 Put everything YARPP into an object, expected to be a singleton global $yarpp.
 */
class YARPP {

	/**
	 * Here's a list of all the options YARPP uses (except version), as well as their default values,
	 * sans the yarpp_ prefix, split up into binary options and value options. These arrays are used in updating
	 * settings (yarpp_options.php) and other tasks.
	 */
	public $default_options             = array();
	public $pro_default_options         = array();
	public $default_hidden_metaboxes    = array();
	public $debug                       = false;
	public $yarppPro                    = null;
	public $generate_missing_thumbnails = null;

	/**
	 * @var bool
	 */
	public $is_custom_template;
	/**
	 * @var YARPP_DB_Options
	 */
	public $db_options;

	/**
	 * @var YARPP_Cache_Bypass
	 */
	public $cache_bypass;
	/**
	 * @var YARPP_Cache_Demo_Bypass
	 */
	public $demo_cache_bypass;
	/**
	 * @var YARPP_Cache
	 */
	public $cache;
	public $admin;
	/**
	 * @var YARPP_DB_Schema
	 */
	public $db_schema;

	/**
	 * @var YARPP_Cache
	 */
	private $active_cache;
	private $storage_class;
	private $default_dimensions = array(
		'width'    => 120,
		'height'   => 120,
		'crop'     => false,
		'size'     => '120x120',
		'_default' => true,
	);
	/**
	 * @var bool Set to true while YARPP is rendering related posts (a very bad time to start looking for related
	 * content, and start infintely recursing !)
	 */
	private $rendering_related_content;

	public function __construct() {
		$this->is_custom_template = false;
		$this->load_default_options();
		$this->yarppPro = $this->get_pro_options();

		/* Loads the plugin's translated strings. */
		load_plugin_textdomain( 'yet-another-related-posts-plugin', false, plugin_basename( YARPP_DIR ) . '/lang' );

		/* Load cache object. */
		$this->storage_class     = 'YARPP_Cache_' . ucfirst( YARPP_CACHE_TYPE );
		$this->cache             = new $this->storage_class( $this );
		$this->cache_bypass      = new YARPP_Cache_Bypass( $this );
		$this->demo_cache_bypass = new YARPP_Cache_Demo_Bypass( $this );
		$this->db_schema         = new YARPP_DB_Schema();
		$this->db_options        = new YARPP_DB_Options();

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		/**
		 * @since 3.2 Update cache on delete.
		 */
		add_action( 'delete_post', array( $this->cache, 'delete_post' ), 10, 1 );

		/**
		 * @since 3.5.3 Use transition_post_status instead of save_post hook.
		 * @since 3.2.1 Handle post_status transitions.
		 */
		add_action( 'transition_post_status', array( $this->cache, 'transition_post_status' ), 10, 3 );

		/**
		 * Initializes yarpp rest routes
		 */
		if ( apply_filters( 'rest_enabled', true ) && class_exists( 'WP_REST_Controller' ) && class_exists( 'WP_REST_Posts_Controller' ) ) {
			include_once YARPP_DIR . '/classes/YARPP_Rest_Api.php';
			new YARPP_Rest_Api();
		}

		/* Automatic display hooks: */
		/**
		 * Allow filtering the priority of YARPP's placement.
		 */
		$content_priority     = apply_filters( 'yarpp_content_priority', 1200 );
		$feed_priority        = apply_filters( 'yarpp_feed_priority', 600 );
		$excerpt_rss_priority = apply_filters( 'yarpp_excerpt_rss_priority', 600 );

		add_filter( 'the_content', array( $this, 'the_content' ), $content_priority );
		add_action( 'bbp_template_after_single_topic', array( $this, 'add_to_bbpress' ) );
		add_filter( 'the_content_feed', array( $this, 'the_content_feed' ), $feed_priority );
		add_filter( 'the_excerpt_rss', array( $this, 'the_excerpt_rss' ), $excerpt_rss_priority );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_thumbnails_stylesheet' ) );
		add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 3 );

		/**
		 * If we're using thumbnails, register yarpp-thumbnail size, if theme has not already.
		 * Note: see FAQ in the readme if you would like to change the YARPP thumbnail size.
		 * If theme has already yarpp-thumbnail size registered and we also try to register yarpp-thumbnail then it will throw a fatal error. So it is necessary to check if yarpp-thumbnail size is not registered.
		 */
		global $add_image_size_by_yarpp;

		/**
		 * Filters whether or not to register YARPP's image size "yarpp-thumbnail". Defaults to registering it
		 * if it wasn't already by the theme or some other plugin. But if you don't want yarpp-thumbnail sizes being
		 * generated at all, have it always return false.
		 */
		if ( apply_filters('yarpp_add_image_size', false === yarpp_get_image_sizes( 'yarpp-thumbnail' ) ) ) {
			$width  = 120;
			$height = 120;
			$crop   = true;
			add_image_size( 'yarpp-thumbnail', $width, $height, $crop );
			$add_image_size_by_yarpp = true;
		} else {
			$add_image_size_by_yarpp = false;
		}

		if ( isset( $_REQUEST['yarpp_debug'] ) ) {
			$this->debug = true;
		}

		if ( ! $this->db_options->plugin_version_in_db() ) {
			$this->db_options->add_upgrade_flag();
		}

		/**
		 * @since 3.4 Only load UI if we're in the admin.
		 */
		if ( is_admin() ) {
			require_once YARPP_DIR . '/classes/YARPP_Admin.php';
			$this->admin = new YARPP_Admin( $this );
			if ( ! defined( 'DOING_AJAX' ) ) {
				$this->enforce();
			}
		}
		$shortcode = new YARPP_Shortcode();
		$shortcode->register();
	}

	/**
	 * Add yarpp_meta key to protected list.
	 *
	 * @since 5.19
	 *
	 * @param bool   $protected Whether the key is considered protected.
	 * @param string $meta_key  Metadata key.
	 * @param string $meta_type Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user',
	 *                          or any other object type with an associated meta table.
	 */
	public function is_protected_meta( $protected, $meta_key, $meta_type ) {
		if ( 'yarpp_meta' === $meta_key ) {
			return true;
		}
		return $protected;
	}

	/**
	 * OPTIONS
	 */
	private function load_pro_default_options() {
		return array(
			'active'                  => '0',
			'aid'                     => null,
			'st'                      => null,
			'v'                       => null,
			'dpid'                    => null,
			'optin'                   => false,
			'auto_display_post_types' => array( 'post' ),
		);
	}

	private function load_default_options() {
		$this->default_options = array(
			'threshold'                           => 1,
			'limit'                               => 4,
			'excerpt_length'                      => 10,
			'recent'                              => false,
			'before_title'                        => '<li>',
			'after_title'                         => '</li>',
			'before_post'                         => ' <small>',
			'after_post'                          => '</small>',
			'before_related'                      => '<h3>' . __( 'Related posts:', 'yet-another-related-posts-plugin' ) . '</h3><ol>',
			'after_related'                       => '</ol>',
			'no_results'                          => '<p>' . __( 'No related posts.', 'yet-another-related-posts-plugin' ) . '</p>',
			'order'                               => 'score DESC',
			'rss_limit'                           => 3,
			'rss_excerpt_length'                  => 10,
			'rss_before_title'                    => '<li>',
			'rss_after_title'                     => '</li>',
			'rss_before_post'                     => ' <small>',
			'rss_after_post'                      => '</small>',
			'rss_before_related'                  => '<h3>' . __( 'Related posts:', 'yet-another-related-posts-plugin' ) . '</h3><ol>',
			'rss_after_related'                   => '</ol>',
			'rss_no_results'                      => '<p>' . __( 'No related posts.', 'yet-another-related-posts-plugin' ) . '</p>',
			'rss_order'                           => 'score DESC',
			'past_only'                           => false,
			'show_excerpt'                        => false,
			'rss_show_excerpt'                    => false,
			'template'                            => false,
			'rss_template'                        => false,
			'show_pass_post'                      => false,
			'cross_relate'                        => false,
			'include_sticky_posts'                => true,
			'generate_missing_thumbnails'         => false,
			'rss_display'                         => false,
			'rss_excerpt_display'                 => true,
			'promote_yarpp'                       => false,
			'rss_promote_yarpp'                   => false,
			'myisam_override'                     => false,
			'exclude'                             => '',
			'include_post_type'                   => get_post_types( array() ),
			'weight'                              => array(
				'title' => 0,
				'body'  => 0,
				'tax'   => array(
					'category' => 1,
					'post_tag' => 1,
				),
			),
			'require_tax'                         => array(),
			'optin'                               => false,
			'thumbnails_heading'                  => __( 'Related posts:', 'yet-another-related-posts-plugin' ),
			'thumbnails_default'                  => plugins_url( 'images/default.png', __DIR__ ),
			'rss_thumbnails_heading'              => __( 'Related posts:', 'yet-another-related-posts-plugin' ),
			'rss_thumbnails_default'              => plugins_url( 'images/default.png', __DIR__ ),
			'auto_display_archive'                => false,
			'auto_display_post_types'             => array( 'post' ),
			'pools'                               => array(),
			'manually_using_thumbnails'           => false,
			'rest_api_display'                    => true,
			'thumbnail_size_display'              => 0,
			'custom_theme_thumbnail_size_display' => 0,
			'thumbnail_size_feed_display'         => 0,
			'rest_api_client_side_caching'        => false,
			'yarpp_rest_api_cache_time'           => 15,
		);
	}

	public function set_option( $options, $value = null ) {
		$current_options = $this->get_option();

		/* We can call yarpp_set_option(key,value) if we like. */
		if ( ! is_array( $options ) ) {
			if ( isset( $value ) ) {
				$options = array( $options => $value );
			} else {
				return false;
			}
		}

		$new_options = array_merge( $current_options, $options );
		$this->db_options->set_yarpp_options( $new_options );

		// new in 3.1: clear cache when updating certain settings.
		$clear_cache_options = array(
			'show_pass_post'       => 1,
			'recent'               => 1,
			'threshold'            => 1,
			'past_only'            => 1,
			'include_sticky_posts' => 1,
			'cross_relate'         => 1,
		);

		$relevant_options                = array_intersect_key( $options, $clear_cache_options );
		$relevant_current_options        = array_intersect_key( $current_options, $clear_cache_options );
		$new_options_which_require_flush = array_diff_assoc( $relevant_options, $relevant_current_options );

		if ( count( $new_options_which_require_flush )
			|| ( $new_options['limit'] > $current_options['limit'] )
			|| ( $new_options['weight'] != $current_options['weight'] )
			|| ( $new_options['exclude'] != $current_options['exclude'] )
			|| ( $new_options['require_tax'] != $current_options['require_tax'] )
						|| ( $new_options['include_post_type'] != $current_options['include_post_type'] )
		) {
			$this->cache->flush();
		}
	}

	/**
	 * @since 3.4b8 $option can be a path, of the query_str variety, i.e. "option[suboption][subsuboption]"
	 */
	public function get_option( $option = null ) {
		$options = $this->db_options->get_yarpp_options();

		// ensure defaults if not set:
		$options = array_merge( $this->default_options, $options );

		if ( is_null( $option ) ) {
			return $options;
		}

		$optionpath    = array();
		$parsed_option = array();
		wp_parse_str( $option, $parsed_option );
		$optionpath = $this->array_flatten( $parsed_option );

		$current = $options;
		foreach ( $optionpath as $optionpart ) {
			if ( ! is_array( $current ) || ! isset( $current[ $optionpart ] ) ) {
				return null;
			}
			$current = $current[ $optionpart ];
		}

		return $current;
	}

	private function get_pro_options() {
		$current  = get_option( 'yarpp_pro' );
		$defaults = $this->load_pro_default_options();

		if ( $current ) {
			$out = array_merge( $defaults, $current );
			update_option( 'yarpp_pro', $out );
		} else {
			$out = $defaults;
			add_option( 'yarpp_pro', $out );
		}

		return $out;
	}

	private function array_flatten( $array, $given = array() ) {
		foreach ( $array as $key => $val ) {
			$given[] = $key;
			if ( is_array( $val ) ) {
				$given = $this->array_flatten( $val, $given );
			}
		}
		return $given;
	}

	/*
	 * INFRASTRUCTURE
	 */

	/**
	 * @since 3.5.2 Function to enforce YARPP setup if not ready, activate; else upgrade.
	 */
	public function enforce() {
		if ( ! $this->enabled() ) {
			$this->activate(); // activate calls upgrade later, so it's covered.
		} else {
			$this->upgrade();
		}
		if ( $this->get_option( 'optin' ) ) {
			$this->optin_ping();
		}
	}

	public function enabled() {
		if ( ! (bool) $this->cache->is_enabled() ) {
			return false;
		} else {
			return $this->diagnostic_fulltext_indices();
		}
	}

	public function activate() {
		if ( (bool) $this->cache->is_enabled() === false ) {
			$this->cache->setup();
		}

		/* If we're not enabled, give up. */
		if ( ! $this->enabled() ) {
			return false;
		}

		if ( ! $this->db_options->plugin_version_in_db() ) {
			$this->db_options->update_plugin_version_in_db();
			$this->version_info( true );
		} else {
			$this->upgrade();
		}

		return true;
	}

	/**
	 * DIAGNOSTICS
	 *
	 * @since 4.0 Moved into separate functions. Note return value types can differ.
	 * @since 5.2.0 consider using $this->db_schema->posts_table_database_engine() or
	 *        $this->db_schema->database_supports_fulltext_indexes() instead
	 */
	public function diagnostic_myisam_posts() {
		$engine = $this->db_schema->posts_table_database_engine();
		switch ( $engine ) {
			case 'MyISAM':
				return true;
			case null:
				return 'UNKNOWN';
			default:
				return $engine;
		}
	}

	/**
	 * @deprecated in 5.14.0 we just always enable fulltext indexes, or keep checking for it, so this should never need
	 * to be called.
	 * @return bool
	 */
	function diagnostic_fulltext_disabled() {
		return $this->db_options->is_fulltext_disabled();
	}

	/**
	 * Attempts to add the fulltext indexes on the posts table.
	 *
	 * @since 5.1.8
	 * @deprecated use YARPP::enable_fulltext_titles() and YARPP::enable_fulltext_contents() instead
	 * @return bool
	 */
	public function enable_fulltext() {
		_deprecated_function( 'YARPP::enable_fulltext', '5.15.0' );
		if ( ! $this->db_supports_fulltext() ) {
			return false;
		}
		if ( ! $this->enable_fulltext_titles() ) {
			return false;
		}
		if ( ! $this->enable_fulltext_contents() ) {
			return false;
		}
		return true;
	}

	protected function db_supports_fulltext() {
		/*
		 * If we haven't already re-attempted creating the database indexes and the database doesn't support adding
		 * those indexes, disable it.
		 */
		if ( ! (bool) $this->get_option( YARPP_DB_Options::YARPP_MYISAM_OVERRIDE ) &&
			! $this->db_schema->database_supports_fulltext_indexes() ) {
			$this->disable_fulltext();
			return false;
		}
		return true;
	}
	public function enable_fulltext_titles() {
		if ( ! $this->db_schema->title_column_has_index() ) {
			if ( $this->db_schema->add_title_index() ) {
				$this->db_options->delete_fulltext_db_error_record();
			} else {
				$this->db_options->update_fulltext_db_record();
				$this->disable_fulltext();
				return false;
			}
		}
		return true;
	}

	public function enable_fulltext_contents() {
		if ( ! $this->db_schema->content_column_has_index() ) {
			if ( $this->db_schema->add_content_index() ) {
				$this->db_options->delete_fulltext_db_error_record();
			} else {
				$this->disable_fulltext();
				$this->db_options->update_fulltext_db_record();
				return false;
			}
		}
		return true;
	}

	/**
	 * Stop considering post title and body in relatedness criteria.
	 */
	public function disable_fulltext() {
		if ( $this->db_options->is_fulltext_disabled() ) {
			return;
		}

		/* Remove title and body weights: */
		$weight = $this->get_option( 'weight' );
		unset( $weight['title'] );
		unset( $weight['body'] );
		$this->set_option( array( 'weight' => $weight ) );

		/* cut threshold by half: */
		$threshold = (float) $this->get_option( 'threshold' );
		$this->set_option( array( 'threshold' => round( $threshold / 2 ) ) );
	}

	/**
	 * Returns true if we consider this to be a big database (based on posts records); false otherwise.
	 * Uses the constants YARPP_BIG_DB
	 *
	 * @return bool
	 */
	public function diagnostic_big_db() {
		global $wpdb;
		if ( ! defined( 'YARPP_BIG_DB' ) ) {
			define( 'YARPP_BIG_DB', 5000 );
		}
		$sql = 'SELECT COUNT(*) FROM ' . $wpdb->posts;
		// Note: count includes drafts, revisions, etc.
		$posts_count = $wpdb->get_var( $sql );
		return (int) $posts_count > YARPP_BIG_DB;
	}

	/**
	 * Try to retrieve fulltext index from database.
	 *
	 * @return bool
	 */
	public function diagnostic_fulltext_indices() {
		return $this->db_schema->title_column_has_index() && $this->db_schema->content_column_has_index();
	}

	public function diagnostic_hidden_metaboxes() {
		global $wpdb;
		$raw = $wpdb->get_var(
			"SELECT meta_value FROM $wpdb->usermeta " .
			"WHERE meta_key = 'metaboxhidden_settings_page_yarpp' " .
			'ORDER BY length(meta_value) ASC LIMIT 1'
		);

		if ( ! $raw ) {
			return $this->default_hidden_metaboxes;
		}

		$list = maybe_unserialize( $raw );
		if ( ! is_array( $list ) ) {
			return $this->default_hidden_metaboxes;
		}

		return implode( '|', $list );
	}

	public function diagnostic_post_thumbnails() {
		return current_theme_supports( 'post-thumbnails', 'post' );
	}

	public function diagnostic_custom_templates() {
		return count( $this->get_templates() );
	}

	public function diagnostic_happy() {
		$stats = $this->cache->stats();

		if ( ! ( array_sum( $stats ) > 0 ) ) {
			return false;
		}

		$sum = array_sum( (array) array_map( 'array_product', array_map( null, array_values( $stats ), array_keys( $stats ) ) ) );
		$avg = $sum / array_sum( $stats );

		return ( $this->cache->cache_status() > 0.1 && $avg > 2 );
	}

	public function diagnostic_generate_thumbnails() {
		if ( is_bool( $this->generate_missing_thumbnails ) ) {
			return $this->generate_missing_thumbnails;
		}
		return ( defined( 'YARPP_GENERATE_THUMBNAILS' ) && YARPP_GENERATE_THUMBNAILS ) || (bool) $this->get_option( 'generate_missing_thumbnails' );
	}

	public function diagnostic_using_thumbnails() {
		if ( $this->get_option( 'manually_using_thumbnails' ) ) {
			return true;
		}
		if ( $this->get_option( 'template' ) === 'thumbnails' ) {
			return true;
		}
		if ( $this->get_option( 'rss_template' ) === 'thumbnails' && $this->get_option( 'rss_display' ) ) {
			return true;
		}
		return false;
	}
	public function get_thumbnail_option_name() {
		if ( is_feed() ) {
			return 'thumbnail_size_feed_display';
		}
		$chosen_template = yarpp_get_option( 'template' );
		// check if they're using a custom template
		if ( 'thumbnails' === $chosen_template ) {
			return 'thumbnail_size_display';
		}
		return 'custom_theme_thumbnail_size_display';
	}
	public function thumbnail_dimensions() {
		global $_wp_additional_image_sizes;
		if ( ! isset( $_wp_additional_image_sizes['yarpp-thumbnail'] ) ) {
			return $this->default_dimensions;
		}

		// get user selected thumbnail size.
		$dimensions = yarpp_get_thumbnail_image_dimensions( $this->get_thumbnail_option_name() );
		if ( empty( $dimensions ) ) {
			$dimensions         = $_wp_additional_image_sizes['yarpp-thumbnail'];
			$dimensions['size'] = 'yarpp-thumbnail';
		}

		/* Ensure YARPP dimensions format: */
		$dimensions['width']  = (int) $dimensions['width'];
		$dimensions['height'] = (int) $dimensions['height'];
		return $dimensions;
	}

	/**
	 * @deprecated 5.11.0
	 * @see \YARPP::maybe_enqueue_thumbnails_stylesheet
	 */
	public function maybe_enqueue_thumbnails() {
		_deprecated_function( 'YARPP::maybe_enqueue_thumbnails', '5.11.0', 'YARPP::maybe_enqueue_thumbnails_stylesheet' );
		return $this->maybe_enqueue_thumbnails_stylesheet();
	}

	public function maybe_enqueue_thumbnails_stylesheet() {
		if ( is_feed() ) {
			return;
		}

		$auto_display_post_types = $this->get_option( 'auto_display_post_types' );

		/* If it's not an auto-display post type, return. */
		if ( ! in_array( get_post_type(), $auto_display_post_types ) ) {
			return;
		}

		if ( ! is_singular() && ! ( $this->get_option( 'auto_display_archive' ) && ( is_archive() || is_home() ) ) ) {
			return;
		}

		if ( $this->get_option( 'template' ) !== 'thumbnails' ) {
			return;
		}

		$this->enqueue_thumbnails_stylesheet( $this->thumbnail_dimensions() );
	}

	/**
	 * @deprecated 5.11.0
	 * @see YARPP::enqueue_thumbnails_stylesheet()
	 * @param $dimensions
	 */
	public function enqueue_thumbnails( $dimensions ) {
		_deprecated_function( 'YARPP::enqueue_thumbnails', '5.11.0', 'YARPP::enqueue_thumbnails_stylesheet' );
		return $this->enqueue_thumbnails_stylesheet( $dimensions );
	}

	/**
	 * @param $dimensions
	 */
	public function enqueue_thumbnails_stylesheet( $dimensions ) {

		wp_register_style( 'yarpp-thumbnails', plugins_url( '/style/styles_thumbnails.css', YARPP_MAIN_FILE ), array(), YARPP_VERSION );
		/**
		 * Filter to allow dequeing of styles_thumbnails.css.
		 *
		 * @param boolean default true
		 */
		$enqueue_yarpp_thumbnails = apply_filters( 'yarpp_enqueue_thumbnails_style', true );
		if ( true === $enqueue_yarpp_thumbnails ) {
			$yarpp_custom_css = yarpp_thumbnail_inline_css( $dimensions );
			wp_enqueue_style( 'yarpp-thumbnails' );
			wp_add_inline_style( 'yarpp-thumbnails', $yarpp_custom_css );
		}
	}

	/**
	 * Code based on Viper's Regenerate Thumbnails plugin '$dimensions' must be an array with size, crop, height, width attributes.
	 */
	public function ensure_resized_post_thumbnail( $post_id, $dimensions ) {

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$downsized    = image_downsize( $thumbnail_id, $dimensions['size'] );

		if ( $dimensions['crop'] && $downsized[1] && $downsized[2]
			&& ( $downsized[1] != $dimensions['width'] || $downsized[2] != $dimensions['height'] )
		) {
			/*
			 * We want to trigger re-computation of the thumbnail here.
			* (only if downsized width and height are specified, for Photon behavior)
			*/
			$fullSizePath = get_attached_file( $thumbnail_id );
			if ( $fullSizePath !== false && file_exists( $fullSizePath ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$metadata = wp_generate_attachment_metadata( $thumbnail_id, $fullSizePath );
				if ( ! is_wp_error( $metadata ) ) {
					wp_update_attachment_metadata( $thumbnail_id, $metadata );
				}
			}
		}
	}

	private $templates = null;
	/**
	 * Returns an Array of all the custom templates
	 *
	 * @return array
	 */
	public function get_templates() {
		if ( is_null( $this->templates ) ) {
			$this->templates = glob( STYLESHEETPATH . '/yarpp-template-*.php' );

			// if glob hits an error, it returns false.
			if ( $this->templates === false ) {
				$this->templates = array();
			}

			// get basenames only.
			$this->templates = (array) array_map( array( $this, 'get_template_data' ), $this->templates );
		}
		return (array) $this->templates;
	}

	/**
	 * Get all the available templates
	 *
	 * @since 5.27.2
	 * @return array
	 */
	public function get_all_templates() {
		$templates       = $this->get_templates();
		$block_templates = array(
			esc_attr( 'builtin' )    => esc_html__( 'List', 'yet-another-related-posts-plugin' ),
			esc_attr( 'thumbnails' ) => esc_html__( 'Thumbnail', 'yet-another-related-posts-plugin' ),
		);
		foreach ( $templates as $template ) {
			$block_templates[ esc_attr( $template['basename'] ) ] = sprintf(
				/* translators: %s: yarpp template name */
				esc_html__( 'Custom: %s', 'yet-another-related-posts-plugin' ),
				$template['name']
			);
		}

		return $block_templates;
	}

	public function get_template_data( $file ) {
		$headers          = array(
			'name'        => 'YARPP Template',
			'description' => 'Description',
			'author'      => 'Author',
			'uri'         => 'Author URI',
		);
		$data             = get_file_data( $file, $headers );
		$data['file']     = $file;
		$data['basename'] = basename( $file );

		if ( empty( $data['name'] ) ) {
			$data['name'] = $data['basename'];
		}

		return $data;
	}

	/**
	 * UPGRADE ROUTINES
	 */

	public function upgrade() {
		$last_version = $this->db_options->plugin_version_in_db();

		if ( version_compare( YARPP_VERSION, $last_version ) === 0 ) {
			return;
		}
		if ( $last_version && version_compare( '3.4b2', $last_version ) > 0 ) {
			$this->upgrade_3_4b2();
		}
		if ( $last_version && version_compare( '3.4b5', $last_version ) > 0 ) {
			$this->upgrade_3_4b5();
		}
		if ( $last_version && version_compare( '3.4b8', $last_version ) > 0 ) {
			$this->upgrade_3_4b8();
		}
		if ( $last_version && version_compare( '3.4.4b2', $last_version ) > 0 ) {
			$this->upgrade_3_4_4b2();
		}
		if ( $last_version && version_compare( '3.4.4b3', $last_version ) > 0 ) {
			$this->upgrade_3_4_4b3();
		}
		if ( $last_version && version_compare( '3.4.4b4', $last_version ) > 0 ) {
			$this->upgrade_3_4_4b4();
		}
		if ( $last_version && version_compare( '3.5.2b2', $last_version ) > 0 ) {
			$this->upgrade_3_5_2b2();
		}
		if ( $last_version && version_compare( '3.6b7', $last_version ) > 0 ) {
			$this->upgrade_3_6b7();
		}
		if ( $last_version && version_compare( '4.0.1', $last_version ) > 0 ) {
			$this->upgrade_4_0_1();
		}

		$this->cache->upgrade( $last_version );
		/* flush cache in 3.4.1b5 as 3.4 messed up calculations. */
		if ( $last_version && version_compare( '3.4.1b5', $last_version ) > 0 ) {
			$this->cache->flush();
		}

		$this->version_info( true );

		$this->db_options->update_plugin_version_in_db();
		$this->db_options->add_upgrade_flag();
		$this->delete_transient( 'yarpp_optin' );
	}

	public function upgrade_3_4b2() {
		global $wpdb;

		$yarpp_3_3_options = array(
			'threshold'           => 4,
			'limit'               => 4,
			'template_file'       => '',
			'excerpt_length'      => 10,
			'recent_number'       => 12,
			'recent_units'        => 'month',
			'before_title'        => '<li>',
			'after_title'         => '</li>',
			'before_post'         => ' <small>',
			'after_post'          => '</small>',
			'before_related'      => '<h3>' . __( 'Related posts:', 'yet-another-related-posts-plugin' ) . '</h3><ol>',
			'after_related'       => '</ol>',
			'no_results'          => '<p>' . __( 'No related posts.', 'yet-another-related-posts-plugin' ) . '</p>',
			'order'               => 'score DESC',
			'rss_limit'           => 3,
			'rss_template_file'   => '',
			'rss_excerpt_length'  => 10,
			'rss_before_title'    => '<li>',
			'rss_after_title'     => '</li>',
			'rss_before_post'     => ' <small>',
			'rss_after_post'      => '</small>',
			'rss_before_related'  => '<h3>' . __( 'Related posts:', 'yet-another-related-posts-plugin' ) . '</h3><ol>',
			'rss_after_related'   => '</ol>',
			'rss_no_results'      => '<p>' . __( 'No related posts.', 'yet-another-related-posts-plugin' ) . '</p>',
			'rss_order'           => 'score DESC',
			'title'               => '2',
			'body'                => '2',
			'categories'          => '1',
			'tags'                => '2',
			'distags'             => '',
			'discats'             => '',
			'past_only'           => false,
			'show_excerpt'        => false,
			'recent_only'         => false,
			'use_template'        => false,
			'rss_show_excerpt'    => false,
			'rss_use_template'    => false,
			'show_pass_post'      => false,
			'cross_relate'        => false,
			'auto_display'        => true,
			'rss_display'         => false,
			'rss_excerpt_display' => true,
			'promote_yarpp'       => false,
			'rss_promote_yarpp'   => false,
		);

		$yarpp_options = array();
		foreach ( $yarpp_3_3_options as $key => $default ) {
			$value = get_option( "yarpp_$key", null );
			if ( is_null( $value ) ) {
				continue;
			}

			if ( is_bool( $default ) ) {
				$yarpp_options[ $key ] = (bool) $value;
				continue;
			}

			// value options used to be stored with a bajillion slashes...
			$value = stripslashes( stripslashes( $value ) );
			// value options used to be stored with a blank space at the end... don't ask.
			$value = rtrim( $value, ' ' );

			if ( is_int( $default ) ) {
				$yarpp_options[ $key ] = absint( $value );
			} else {
				$yarpp_options[ $key ] = $value;
			}
		}

		// add the options directly first, then call set_option which will ensure defaults,
		// in case any new options have been added.
		update_option( 'yarpp', $yarpp_options );
		$this->set_option( $yarpp_options );

		$option_keys = array_keys( $yarpp_options );
		// append some keys for options which are long deprecated:
		$option_keys[] = 'ad_hoc_caching';
		$option_keys[] = 'excerpt_len';
		$option_keys[] = 'show_score';
		if ( count( $option_keys ) ) {
			// This sanitization is sufficient because $option_keys are hardcoded above.
			$in = "('yarpp_" . join( "', 'yarpp_", $option_keys ) . "')";
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN {$in}" );
		}
	}

	public function upgrade_3_4b5() {
		$options            = $this->get_option();
		$options['exclude'] = array(
			'post_tag' => $options['distags'],
			'category' => $options['discats'],
		);
		unset( $options['distags'] );
		unset( $options['discats'] );
		update_option( 'yarpp', $options );
	}

	public function upgrade_3_4b8() {
		$options           = $this->get_option();
		$options['weight'] = array(
			'title' => (int) @$options['title'],
			'body'  => (int) @$options['body'],
			'tax'   => array(
				'post_tag' => (int) @$options['tags'],
				'category' => (int) @$options['categories'],
			),
		);

		// ensure that we consider something.
		if ( $options['weight']['title'] < 2
			&& $options['weight']['body'] < 2
			&& $options['weight']['tax']['post_tag'] < 2
			&& $options['weight']['tax']['category'] < 2
		) {
			$options['weight'] = $this->default_options['weight'];
		}

		unset( $options['title'] );
		unset( $options['body'] );
		unset( $options['tags'] );
		unset( $options['categories'] );

		update_option( 'yarpp', $options );
	}

	public function upgrade_3_4_4b2() {
		$options = $this->get_option();

		// update weight values; split out tax weights into weight[tax] and require_tax
		$weight_map = array(
			2 => 1,
			3 => YARPP_EXTRA_WEIGHT,
		);

		if ( (int) $options['weight']['title'] == 1 ) {
			unset( $options['weight']['title'] );
		} else {
			$options['weight']['title'] = $weight_map[ (int) $options['weight']['title'] ];
		}

		if ( (int) $options['weight']['body'] == 1 ) {
			unset( $options['weight']['body'] );
		} else {
			$options['weight']['body'] = $weight_map[ (int) $options['weight']['body'] ];
		}

		$options['require_tax'] = array();
		foreach ( $options['weight']['tax'] as $tax => $value ) {
			if ( $value == 3 ) {
				$options['require_tax'][ $tax ] = 1;
			}
			if ( $value == 4 ) {
				$options['require_tax'][ $tax ] = 2;
			}

			if ( $value > 1 ) {
				$options['weight']['tax'][ $tax ] = 1;
			} else {
				unset( $options['weight']['tax'][ $tax ] );
			}
		}

		// consolidate excludes, using tt_ids.
		$exclude_tt_ids = array();
		if ( isset( $options['exclude'] ) && is_array( $options['exclude'] ) ) {
			foreach ( $options['exclude'] as $tax => $term_ids ) {
				if ( ! empty( $term_ids ) ) {
					$lp_tmp         = wp_list_pluck( get_terms( $tax, array( 'include' => $term_ids ) ), 'term_taxonomy_id' );
					$exclude_tt_ids = array_merge( $lp_tmp, $exclude_tt_ids );
				}
			}
		}
		$options['exclude'] = join( ',', $exclude_tt_ids );

		update_option( 'yarpp', $options );
	}

	public function upgrade_3_4_4b3() {
		$options = $this->get_option();

		$options['template']     = ( $options['use_template'] ) ? $options['template_file'] : false;
		$options['rss_template'] = ( $options['rss_use_template'] ) ? $options['rss_template_file'] : false;

		unset( $options['use_template'] );
		unset( $options['template_file'] );
		unset( $options['rss_use_template'] );
		unset( $options['rss_template_file'] );

		update_option( 'yarpp', $options );
	}

	public function upgrade_3_4_4b4() {
		$options = $this->get_option();

		$options['recent'] = ( $options['recent_only'] ) ? $options['recent_number'] . ' ' . $options['recent_units'] : false;

		unset( $options['recent_only'] );
		unset( $options['recent_number'] );
		unset( $options['recent_units'] );

		update_option( 'yarpp', $options );
	}

	public function upgrade_3_5_2b2() {
		// fixing the effects of a previous bug affecting non-MyISAM users
		if ( is_null( $this->get_option( 'weight' ) ) || ! is_array( $this->get_option( 'weight' ) ) ) {
			$weight = $this->default_options['weight'];

			// if we're still not using MyISAM
			if ( ! $this->get_option( YARPP_DB_Options::YARPP_MYISAM_OVERRIDE ) &&
				! $this->db_schema->database_supports_fulltext_indexes() ) {
				unset( $weight['title'] );
				unset( $weight['body'] );
			}

			$this->set_option( array( 'weight' => $weight ) );
		}
	}

	public function upgrade_3_6b7() {
		// migrate auto_display setting to auto_display_post_types
		$options = $this->get_option();

		$options['auto_display_post_types'] = ( $options['auto_display'] ) ? array( 'post' ) : array();

		unset( $options['auto_display'] );

		update_option( 'yarpp', $options );
	}

	public function upgrade_4_0_1() {
		delete_transient( 'yarpp_version_info' );
	}

	public function upgrade_4_2() {
		$this->load_pro_default_options();
		$new = array_merge( $this->pro_default_options, $this->yarppPro );
		update_option( 'yarpp_pro', $new );
	}

	/**
	 * UTILITIES
	 */
	private $current_post;
	private $current_query;
	private $current_pagenow;
	// so we can return to normal later.
	public function save_post_context() {
		global $wp_query, $pagenow, $post;

		$this->current_query   = $wp_query;
		$this->current_pagenow = $pagenow;
		$this->current_post    = $post;
	}

	public function restore_post_context() {
		global $wp_query, $pagenow, $post;

		$wp_query = $this->current_query;
		unset( $this->current_query );

		$pagenow = $this->current_pagenow;
		unset( $this->current_pagenow );

		if ( isset( $this->current_post ) ) {
			$post = $this->current_post;
			setup_postdata( $post );
			unset( $this->current_post );
		}
	}

	private $post_types = null;

	/**
	 * Gets all the post types YARPP can add related content to, and the post types YARPP can include in
	 * "the pool"
	 *
	 * @param string $field 'objects', or any property on WP_Post_Type, like 'name'. Defaults to 'name'.
	 *
	 * @return array|null
	 */
	public function get_post_types( $field = 'name' ) {
		if ( is_null( $this->post_types ) ) {
			$this->post_types = get_post_types( array(), 'objects' );
			$this->post_types = array_filter( $this->post_types, array( $this, 'post_type_filter' ) );
		}

		if ( $field === 'objects' ) {
			return $this->post_types;
		}

		return wp_list_pluck( $this->post_types, $field );
	}

	/**
	 * Gets the post types to use for the current YARPP query
	 *
	 * @param string|WP_Post $reference_ID
	 * @param array          $args
	 * @return string[]
	 */
	public function get_query_post_types( $reference_ID = null, $args = array() ) {
		$include_post_type = yarpp_get_option( 'include_post_type' );
		$include_post_type = wp_parse_list( $include_post_type );
		if ( isset( $args['post_type'] ) ) {
			$post_types = wp_parse_list( $args['post_type'] );
		} elseif ( ! $this->get_option( 'cross_relate' ) ) {
			$current_post_type = get_post_type( $reference_ID );
			$post_types        = array( $current_post_type );
			if ( ! in_array( $current_post_type, $include_post_type ) ) {
				$post_types = array( '' );
			}
		} elseif ( ! empty( $include_post_type ) ) {
			$post_types = $include_post_type;
		} elseif ( $this->get_option( 'cross_relate' ) ) {
			$post_types = $this->get_post_types();
		} else {
			$post_types = array( get_post_type( $reference_ID ) );
		}
		return apply_filters(
			'yarpp_map_post_types',
			$post_types,
			is_array( $args ) && isset( $args['domain'] ) ? $args['domain'] : null
		);
	}

	private function post_type_filter( $post_type ) {
		// Remove blacklisted post types.
		if ( class_exists( 'bbPress' ) && in_array(
			$post_type->name,
			array(
				'forum', // bbPress forums (ie, group of topics).
				'reply', // bbPress replies to topics
			)
		) ) {
			return false;
		}
		if ( $post_type->public ) {
			return true;
		}
		if ( isset( $post_type->yarpp_support ) ) {
			return $post_type->yarpp_support;
		}
		return false;
	}

	private $taxonomies = null;
	function get_taxonomies( $field = false ) {
		if ( is_null( $this->taxonomies ) ) {
			$this->taxonomies = get_taxonomies( array(), 'objects' );
			$this->taxonomies = array_filter( $this->taxonomies, array( $this, 'taxonomy_filter' ) );
		}

		if ( $field ) {
			return wp_list_pluck( $this->taxonomies, $field );
		}

		return $this->taxonomies;
	}

	private function taxonomy_filter( $taxonomy ) {
		if ( ! count( array_intersect( $taxonomy->object_type, $this->get_post_types() ) ) ) {
			return false;
		}

		// if yarpp_support is set, follow that; otherwise include if show_ui is true.
		if ( isset( $taxonomy->yarpp_support ) ) {
			return $taxonomy->yarpp_support;
		}

		return $taxonomy->show_ui;
	}

	/**
	 * Gather optin data.
	 *
	 * @return array
	 */
	public function optin_data() {
		global $wpdb;

		$comments = wp_count_comments();
		$users    = $wpdb->get_var( 'SELECT COUNT(ID) FROM ' . $wpdb->users ); // count_users();
		$posts    = $wpdb->get_var( 'SELECT COUNT(ID) FROM ' . $wpdb->posts . " WHERE post_type = 'post' AND comment_count > 0" );
		$settings = $this->get_option();

		$collect = array_flip(
			array(
				'threshold',
				'limit',
				'excerpt_length',
				'recent',
				'rss_limit',
				'rss_excerpt_length',
				'past_only',
				'show_excerpt',
				'rss_show_excerpt',
				'template',
				'rss_template',
				'show_pass_post',
				'cross_relate',
				'generate_missing_thumbnails',
				'include_sticky_posts',
				'rss_display',
				'rss_excerpt_display',
				'promote_yarpp',
				'rss_promote_yarpp',
				'myisam_override',
				'weight',
				'require_tax',
				'auto_display_archive',
				'exclude',
				'include_post_type',
			)
		);

		$check_changed = array(
			'before_title',
			'after_title',
			'before_post',
			'after_post',
			'after_related',
			'no_results',
			'order',
			'rss_before_title',
			'rss_after_title',
			'rss_before_post',
			'rss_after_post',
			'rss_after_related',
			'rss_no_results',
			'rss_order',
			'exclude',
			'thumbnails_heading',
			'thumbnails_default',
			'rss_thumbnails_heading',
			'rss_thumbnails_default',
		);

		$data = array(
			'versions'    => array(
				'yarpp' => YARPP_VERSION,
				'wp'    => get_bloginfo( 'version' ),
				'php'   => phpversion(),
			),
			'yarpp'       => array(
				'settings'     => array_intersect_key( $settings, $collect ),
				'cache_engine' => YARPP_CACHE_TYPE,
			),
			'diagnostics' => array(
				'myisam_posts'        => $this->diagnostic_myisam_posts(),
				'fulltext_indices'    => $this->diagnostic_fulltext_indices(),
				'hidden_metaboxes'    => $this->diagnostic_hidden_metaboxes(),
				'post_thumbnails'     => $this->diagnostic_post_thumbnails(),
				'happy'               => $this->diagnostic_happy(),
				'using_thumbnails'    => $this->diagnostic_using_thumbnails(),
				'generate_thumbnails' => $this->diagnostic_generate_thumbnails(),
			),
			'stats'       => array(
				'counts'   => array(),
				'terms'    => array(),
				'comments' => array(
					'moderated' => $comments->moderated,
					'approved'  => $comments->approved,
					'total'     => $comments->total_comments,
					'posts'     => $posts,
				),
				'users'    => $users,
			),
			'locale'      => get_bloginfo( 'language' ),
			'url'         => get_bloginfo( 'url' ),
			'plugins'     => array(
				'active'   => implode( '|', get_option( 'active_plugins', array() ) ),
				'sitewide' => implode( '|', array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) ),
			),
			'pools'       => $settings['pools'],
		);

		$data['yarpp']['settings']['auto_display_post_types'] = implode( '|', $settings['auto_display_post_types'] );

		$changed = array();
		foreach ( $check_changed as $key ) {
			if ( $this->default_options[ $key ] !== $settings[ $key ] ) {
				$changed[] = $key;
			}
		}

		foreach ( array( 'before_related', 'rss_before_related' ) as $key ) {
			if ( $settings[ $key ] !== '<p>' . __( 'Related posts:', 'yet-another-related-posts-plugin' ) . '</p><ol>'
				&& $settings[ $key ] !== $this->default_options[ $key ]
			) {
				$changed[] = $key;
			}
		}

		$data['yarpp']['changed_settings'] = implode( '|', $changed );

		if ( method_exists( $this->cache, 'cache_status' ) ) {
			$data['yarpp']['cache_status'] = $this->cache->cache_status();
		}

		if ( method_exists( $this->cache, 'stats' ) ) {
			$stats     = $this->cache->stats();
			$flattened = array();

			foreach ( $stats as $key => $value ) {
				$flattened[] = "$key:$value";
			}
			$data['yarpp']['stats'] = implode( '|', $flattened );
		}

		if ( method_exists( $wpdb, 'db_version' ) ) {
			$data['versions']['mysql'] = preg_replace( '/[^0-9.].*/', '', $wpdb->db_version() );
		}

		$counts = array();
		foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
			$counts[ $post_type ] = wp_count_posts( $post_type );
		}

		$data['stats']['counts'] = wp_list_pluck( $counts, 'publish' );

		foreach ( get_taxonomies( array( 'public' => true ) ) as $taxonomy ) {
			$data['stats']['terms'][ $taxonomy ] = wp_count_terms( $taxonomy );
		}

		if ( is_multisite() ) {
			$data['multisite'] = array(
				'url'   => network_site_url(),
				'users' => get_user_count(),
				'sites' => get_blog_count(),
			);
		}

		return $data;
	}

	public function pretty_echo( $data ) {
		echo '<pre>';
		$formatted = print_r( $data, true );
		$formatted = str_replace( array( 'Array', '(', ')', "\n    " ), array( '', '', '', "\n" ), $formatted );
		echo preg_replace( "/\n\s*\n/u", "\n", $formatted );
		echo '</pre>';
	}

	/**
	 * CORE LOOKUP + DISPLAY FUNCTIONS
	 */
	protected function display_basic() {
		/* if it's not an auto-display post type, return */
		if ( ! in_array( get_post_type(), $this->get_option( 'auto_display_post_types' ) ) ) {
			return null;
		}

		if ( ! is_singular() && ! ( $this->get_option( 'auto_display_archive' ) && ( is_archive() || is_home() ) ) ) {
			return null;
		}
		// If we're only viewing a single post with page breaks, only show YARPP its the last page.
		global $page, $pages;
		if ( is_singular() && is_int( $page ) && is_array( $pages ) && $page < count( $pages ) ) {
			return null;
		}

		return $this->display_related(
			null,
			array(
				'domain' => 'website',
			),
			false
		);
	}

	public function display_pro( $domain ) {
		if ( ( is_archive() || is_home() || $domain !== 'website' ) ) {
			return null;
		}
		if ( ! in_array( get_post_type(), $this->yarppPro['auto_display_post_types'] ) ) {
			return null;
		}
		if ( ! ( isset( $this->yarppPro['active'] ) && $this->yarppPro['active'] ) ) {
			return null;
		}
		if ( ! ( isset( $this->yarppPro['aid'] ) && isset( $this->yarppPro['v'] ) ) ||
			! ( $this->yarppPro['aid'] && $this->yarppPro['v'] ) ) {
			return null;
		}

		$output = null;
		$aid    = $this->yarppPro['aid'];
		$v      = $this->yarppPro['v'];
		$dpid   = ( isset( $this->yarppPro['dpid'] ) ) ? $this->yarppPro['dpid'] : null;
		$ru     = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$ssp    = ( $dpid ) ? '_ssp' : null;

		ob_start();
		include YARPP_DIR . '/includes/phtmls/yarpp_pro_tag' . $ssp . '.phtml';
		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Display related posts
	 *
	 * @since 2.1 The domain global refers to {website, widget, rss, metabox}
	 * @since 3.0 New query-based approach: EXTREMELY HACKY!
	 *
	 * @param integer $reference_ID
	 * @param array   $args see readme.txt's installation tab's  "YARPP functions()" section
	 * @param bool    $echo
	 * @return string
	 */
	public function display_related( $reference_ID = null, $args = array(), $echo = true ) {
		// Avoid infinite recursion here.
		if ( $this->do_not_query_for_related() ) {
			return false;
		}
		$this->parse_json_arg($args, 'weight');
		$this->parse_json_arg($args, 'require_tax');
		// Custom templates require .php extension.
		if ( isset( $args['template'] ) && $args['template'] ) {
			// Normalize parameter.
			if ( ( strpos( $args['template'], 'yarpp-template-' ) === 0 ) && ( strpos( $args['template'], '.php' ) === false ) ) {
				$args['template'] .= '.php';
			}
		}
		wp_register_style( 'yarppRelatedCss', plugins_url( '/style/related.css', YARPP_MAIN_FILE ), array(), YARPP_VERSION );
		/**
		 * Filter to allow dequeing of related.css.
		 *
		 * @param boolean default true
		 */
		$enqueue_related_style = apply_filters( 'yarpp_enqueue_related_style', true );
		if ( true === $enqueue_related_style ) {
			wp_enqueue_style( 'yarppRelatedCss' );
		}

		if ( is_numeric( $reference_ID ) ) {
			$reference_ID = (int) $reference_ID;
		} else {
			$reference_ID = get_the_ID();
		}

		/**
		 * @since 3.5.3 don't compute on revisions.
		 */
		if ( $the_post = wp_is_post_revision( $reference_ID ) ) {
			$reference_ID = $the_post;
		}

		$this->setup_active_cache( $args );

		$options = array(
			'limit',
			'order',
			'optin',
		);

		extract( $this->parse_args( $args, $options ) );

		$cache_status = $this->active_cache->enforce( $reference_ID, false, $args );
		if ( $cache_status === YARPP_DONT_RUN ) {
			return;
		}
		if ( $cache_status !== YARPP_NO_RELATED ) {
			$this->active_cache->begin_yarpp_time( $reference_ID, $args );
		}

		$this->save_post_context();

		global $wp_query;
		$wp_query = new WP_Query();

		if ( $cache_status !== YARPP_NO_RELATED ) {
			$orders  = explode( ' ', $order );
			$orderby = $orders[0];

			$query_args = array(
				'p'         => $reference_ID,
				'orderby'   => $orderby,
				'showposts' => $limit,
				'post_type' => $this->get_query_post_types( $reference_ID, $args ),
			);

			// Validate "order" arg. Use only if present.
			if ( isset($orders[1]) ) { // rand doesn't have ASC/DESC
				$query_args['order'] = $orders[1];
			}

			$wp_query->query($query_args);
		}

		$this->prep_query( $this->current_query->is_feed );

		$wp_query->posts = apply_filters(
			'yarpp_results',
			$wp_query->posts,
			array(
				'function'   => 'display_related',
				'args'       => $args,
				'related_ID' => $reference_ID,
			)
		);

		$related_query = $wp_query; // backwards compatibility

		if ( $cache_status !== YARPP_NO_RELATED ) {
			$this->active_cache->end_yarpp_time();
		}
		if ( isset( $args['generate_missing_thumbnails'] ) ) {
			$this->generate_missing_thumbnails = $args['generate_missing_thumbnails'];
		}
		// Be careful to avoid infinite recursion, because those templates might show each related posts' body or
		// excerpt, which would trigger finding its related posts, which would show its related posts body or excerpt...
		$this->rendering_related_content = true;

		$output = $this->get_template_content($reference_ID, $args);

		$this->rendering_related_content = false;

		unset( $related_query );
		$this->restore_post_context();

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}

	/**
	 * Handles in case JSON was provided for this argument.
	 *
	 * If the argument specified is a string, it is expected to be a string of JSON, otherwise an error is logged.
	 *
	 * Nothing is returned, modifies the $args passed in.
	 *
	 * @param array  $args
	 * @param string $key
	 *
	 * @return null but modifies the $args array provided
	 */
	protected function parse_json_arg( &$args, $key ) {
		if ( isset( $args[$key] ) && ! empty( $args[$key] ) && is_string($args[$key]) ) {
			$decoded_json = json_decode( $args[$key], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$args[$key] = $decoded_json;
			} else {
				error_log(sprintf('Error parsing JSON in YARPP argument "%s". JSON was "%s" and JSON error was "%s"', $key, $args[$key], function_exists('json_last_error_msg') ? json_last_error_msg() : json_last_error()));
			}
		}
	}
	/**
	 * Returns the YARPP template html data.
	 *
	 * @param int   $reference_ID reference id.
	 * @param array $args see readme.txt installation tab's  "YARPP functions()" section.
	 * @param bool  $is_demo whether to add yarpp-demo-related class to div or not.
	 * @return string return html data.
	 */
	protected function get_template_content( $reference_ID = null, $args = array(), $is_demo = false ) {
		// make $related_query available to custom templates. It may be in use by old custom templates
		global $wp_query;
		$related_query = $wp_query;
		$related_count = $wp_query->post_count;

		$options = array(
			'domain',
			'template',
			'promote_yarpp',
			'extra_css_class',
		);

		extract( $this->parse_args( $args, $options ) );

		// CSS class "yarpp-related" exists for backwards compatibility in-case older custom themes are dependent on it.
		$output = "<div class='yarpp yarpp-related";

		if ( $is_demo ) {
			$output .= ' yarpp-demo-related';
		}

		// Add CSS class to identify domain.
		if ( isset( $domain ) && $domain ) {
			$domain  = esc_attr($domain);
			$output .= " yarpp-related-{$domain}";
		}

		// Add CSS class to identify no results.
		if ( $related_count < 1 ) {
			$output .= ' yarpp-related-none';
		}

		// Add CSS class to identify template.
		if ( isset( $template ) && $template ) {

			// Normalize "thumbnail" and "thumbnails" to reference the same inbuilt template
			if ( $template === 'thumbnail' ) {
				$template = 'thumbnails';
			}
			// Sanitize template name; remove file extension if exists

			// avoid any monkeying around where someone could try a custom template like a template name like
			// "yarpp-template-;../../wp-config.php". YARPP custom templates are only supported in the theme's root folder.
			$template = sanitize_file_name($template);

			if ( strpos( $template, '.php' ) ) {
				$template_css_class_suffix = preg_replace( '/' . preg_quote( '.php', '/' ) . '$/', '', $template );
			} else {
				$template_css_class_suffix = $template;
			}
			$output .= " yarpp-template-$template_css_class_suffix";
		} else {
			// fallback to default template ("list")
			$output .= ' yarpp-template-list';
		}

		// Add any extra CSS classes specified (blocks)
		if ( isset( $extra_css_class ) && $extra_css_class ) {
			$extra_css_class = esc_attr($extra_css_class);
			$output         .= " $extra_css_class";
		}

		$output .= "'>\n";

		if ( $domain === 'metabox' ) {
			include YARPP_DIR . '/includes/template_metabox.php';
		} elseif ( (bool) $template && $template === 'thumbnails' ) {
			include YARPP_DIR . '/includes/template_thumbnails.php';
		} elseif ( (bool) $template && $template === 'list' ) {
			include YARPP_DIR . '/includes/template_builtin.php';
		} elseif ( (bool) $template ) {
			$named_properly  = strpos( $template, 'yarpp-template-' ) === 0;
			$template_exists = file_exists( STYLESHEETPATH . '/' . $template );
			if ( $named_properly && $template_exists ) {
				global $post;
				add_action( 'begin_fetch_post_thumbnail_html', array( $this, 'maybe_regenerate_thumbnails' ), 10, 3 );
				ob_start();
				include STYLESHEETPATH . '/' . $template;
				$output .= ob_get_contents();
				remove_action( 'begin_fetch_post_thumbnail_html', array( $this, 'maybe_regenerate_thumbnails' ), 10 );
				ob_end_clean();
			} else {
				error_log( 'YARPP Plugin: Could not load template "' . $template . '". ' . ( $named_properly ? 'It is named properly.' : 'It is NOT named properly' ) . ' ' . ( $template_exists ? 'It exists' : 'It does NOT exist' ) . '. Falling back to default template.' );
				include YARPP_DIR . '/includes/template_builtin.php';
			}
		} elseif ( $domain === 'widget' ) {
			include YARPP_DIR . '/includes/template_widget.php';
		} else {
			include YARPP_DIR . '/includes/template_builtin.php';
		}

		$output = trim( $output ) . "\n";

		if ( $related_count > 0 && $promote_yarpp && $domain != 'metabox' ) {
			$output .=
				'<p>' .
					sprintf(
						__(
							"Powered by <a href='%s' title='WordPress Related Posts' target='_blank'>YARPP</a>.",
							'yet-another-related-posts-plugin'
						),
						'https://yarpp.com'
					) .
				"</p>\n";
		}

		$output .= "</div>\n";

		return $output;
	}

	/**
	 * @param (int)   $reference_ID
	 * @param (array) $args see readme.txt installation tab's  "YARPP functions()" section
	 */
	public function get_related( $reference_ID = null, $args = array() ) {
		// Avoid infinite recursion here.
		if ( $this->do_not_query_for_related() ) {
			return false;
		}

		if ( is_numeric( $reference_ID ) ) {
			$reference_ID = (int) $reference_ID;
		} else {
			$reference_ID = get_the_ID();
		}

		/**
		 * @since 3.5.3: don't compute on revisions.
		 */
		if ( $the_post = wp_is_post_revision( $reference_ID ) ) {
			$reference_ID = $the_post;
		}

		$this->setup_active_cache( $args );

		$options = array( 'limit', 'order' );
		extract( $this->parse_args( $args, $options ) );

		$cache_status = $this->active_cache->enforce( $reference_ID, false, $args );
		if ( in_array( $cache_status, array( YARPP_DONT_RUN, YARPP_NO_RELATED ), true ) ) {
			return array();
		}

		/* Get ready for YARPP TIME! */
		$this->active_cache->begin_yarpp_time( $reference_ID, $args );

		$related_query = new WP_Query();
		$orders        = explode( ' ', $order );
		$orderby       = $orders[0];

		$query_args = array(
			'p'         => $reference_ID,
			'orderby'   => $orderby,
			'showposts' => $limit,
			'post_type' => $this->get_query_post_types( $reference_ID, $args ),
		);

		// Validate "order" arg. Use only if present.
		if ( isset($orders[1]) ) { // rand doesn't have ASC/DESC
			$query_args['order'] = $orders[1];
		}

		$related_query->query($query_args);

		$related_query->posts = apply_filters(
			'yarpp_results',
			$related_query->posts,
			array(
				'function'   => 'get_related',
				'args'       => $args,
				'related_ID' => $reference_ID,
			)
		);

		$this->active_cache->end_yarpp_time();
		return $related_query->posts;
	}

	/**
	 * @param (int)   $reference_ID
	 * @param (array) $args see readme.txt installation tab's  "YARPP functions()" section
	 */
	public function related_exist( $reference_ID = null, $args = array() ) {
		// Avoid infinite recursion here.
		if ( $this->do_not_query_for_related() ) {
			return false;
		}

		if ( is_numeric( $reference_ID ) ) {
			$reference_ID = (int) $reference_ID;
		} else {
			$reference_ID = get_the_ID();
		}

		/** @since 3.5.3: don't compute on revisions */
		if ( $the_post = wp_is_post_revision( $reference_ID ) ) {
			$reference_ID = $the_post;
		}

		$this->setup_active_cache( $args );

		$cache_status = $this->active_cache->enforce( $reference_ID, false, $args );

		if ( in_array( $cache_status, array( YARPP_DONT_RUN, YARPP_NO_RELATED ), true ) ) {
			return false;
		}

		/* Get ready for YARPP TIME! */
		$this->active_cache->begin_yarpp_time( $reference_ID, $args );
		$related_query = new WP_Query();
		$related_query->query(
			array(
				'p'         => $reference_ID,
				'showposts' => 1,
				'post_type' => $this->get_query_post_types( $reference_ID, $args ),
			)
		);

		$related_query->posts = apply_filters(
			'yarpp_results',
			$related_query->posts,
			array(
				'function'   => 'related_exist',
				'args'       => $args,
				'related_ID' => $reference_ID,
			)
		);

		$return = $related_query->have_posts();
		unset( $related_query );

		$this->active_cache->end_yarpp_time();
		return $return;
	}

	/**
	 * @param array $args
	 * @param bool  $echo
	 * @return string
	 */
	public function display_demo_related( $args = array(), $echo = true ) {
		// If YARPP cache is already finding the current post's content, don't ask it to do it again.
		// Avoid infinite recursion here.
		if ( $this->demo_cache_bypass->demo_time ) {
			return false;
		}

		$options = array(
			'domain',
			'limit',
			'order',
			'size',
		);
		extract( $this->parse_args( $args, $options ) );
		$this->demo_cache_bypass->begin_demo_time( $limit, $order, $size );

		global $wp_query;
		$wp_query = new WP_Query();

		$wp_query->query( array(
			'showposts' => $limit,
			'ignore_sticky_posts' => true,
		) );

		$this->prep_query( $domain === 'rss' );

		$output = $this->get_template_content(null, $args, true);

		$this->demo_cache_bypass->end_demo_time();

		if ( $echo ) {
			echo $output;
		}
		return $output;
	}

	/**
	 * Create an array whose keys come from $options, and whose values are either their values in $args or the option's
	 * default value.
	 * Any keys from $args that aren't in $options are ignored and not included in the returned result.
	 *
	 * @param array $args inputted arguments
	 * @param array $options names of arguments to consider
	 *
	 * @return array with all the keys from the list of $options, with their values
	 * from $args or the options' default values.
	 */
	public function parse_args( $args, $options ) {
		$options_with_rss_variants = array(
			'limit',
			'template',
			'excerpt_length',
			'before_title',
			'after_title',
			'before_post',
			'after_post',
			'before_related',
			'after_related',
			'no_results',
			'order',
			'promote_yarpp',
			'thumbnails_heading',
			'thumbnails_default',
		);

		if ( ! isset( $args['domain'] ) ) {
			$args['domain'] = 'website';
		}

		// Validate "limit" arg; Use only if numeric value, otherwise use default value.
		if ( isset(  $args['limit'] ) && $args['limit'] ) {
			if ( filter_var( $args['limit'], FILTER_VALIDATE_INT) !== false ) {
				$args['limit'] = (int) $args['limit'];
			} else {
				unset($args['limit']);
			}
		}

		$r = array();
		foreach ( $options as $option ) {
			if ( $args['domain'] === 'rss'
				&& in_array( $option, $options_with_rss_variants )
			) {
				$default = $this->get_option( 'rss_' . $option );
			} else {
				$default = $this->get_option( $option );
			}

			if ( isset( $args[ $option ] ) && $args[ $option ] !== $default ) {
				$r[ $option ] = $args[ $option ];
			} else {
				$r[ $option ] = $default;
			}

			if ( $option === 'weight' && ! isset( $r[ $option ]['tax'] ) ) {
				$r[ $option ]['tax'] = array();
			}
		}
		return $r;
	}

	private function setup_active_cache( $args ) {
		/* the options which the main sql query cares about: */
		$magic_options = array(
			'limit',
			'threshold',
			'show_pass_post',
			'past_only',
			'weight',
			'exclude',
			'require_tax',
			'recent',
		);

		$defaults = $this->get_option();
		foreach ( $magic_options as $option ) {
			if ( ! isset( $args[ $option ] ) ) {
				continue;
			}

			/*
			 * limit is a little different... if it's less than what we cache, let it go.
			 */
			if ( $option === 'limit' && $args[ $option ] <= max( $defaults['limit'], $defaults['rss_limit'] ) ) {
				continue;
			}

			if ( $args[ $option ] !== $defaults[ $option ] ) {
				$this->active_cache = $this->cache_bypass;
				return;
			}
		}

		$this->active_cache = $this->cache;
	}

	private function prep_query( $is_feed = false ) {
		global $wp_query;
		$wp_query->in_the_loop = true;
		$wp_query->is_feed     = $is_feed;

		/*
		 * Make sure we get the right is_single value (see http://wordpress.org/support/topic/288230)
		 */
		$wp_query->is_single = false;
	}

	/**
	 * We're regenerating thumbnails when get_the_post_thumbnail is called
	 * directly from one of our custom templates because many folks have already
	 * coded their custom templates but may also want them to automatically regenerate thumbnails..
	 *
	 * @since 5.22.1
	 *
	 * @param int          $post_id           The post ID.
	 * @param int          $post_thumbnail_id The post thumbnail ID.
	 * @param string|int[] $size              Requested image size. Can be any registered image size name, or
	 *                                        an array of width and height values in pixels (in that order).
	 */
	public function maybe_regenerate_thumbnails( $post_id, $post_thumbnail_id, $size ) {
		$dimensions = $this->thumbnail_dimensions();
		if ( $this->diagnostic_generate_thumbnails() ) {
			$this->ensure_resized_post_thumbnail( $post_id, $dimensions );
		}
	}

	/**
	 * Return true if user disabled the YARPP related post for the current post, false otherwise.
	 *
	 * @return bool
	 */
	public function yarpp_disabled_for_this_post() {
		global $post;

		if ( $post instanceof WP_Post ) {
			$yarpp_meta = get_post_meta( $post->ID, 'yarpp_meta', true );
			if ( isset( $yarpp_meta['yarpp_display_for_this_post'] ) && 0 === (int) $yarpp_meta['yarpp_display_for_this_post'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Return true if Automatic Display should be disabled
	 *
	 * @return bool
	 * @since 5.24.0
	 */
	public function is_noyarpp( $content ) {
		/*
		 * Before automatically adding YARPP's related content onto a post's content, checks the value of the
		 * "yarpp_meta" postmeta's key "yarpp_display_for_this_post", and whether the post's content contains
		 * the magic comment "<!--noyarpp-->"`. If either one of those is true `$noyarpp` will be true, otherwise false.
		 */
		$noyarpp = $this->yarpp_disabled_for_this_post();   // post meta flag.
		if ( strpos( $content, '<!--noyarpp-->' ) !== false ) {
			$noyarpp = true;    // does content includes <!--noyarpp--> ?
		}
		/**
		 * Filters whether or not to disable adding YARPP's related posts on the current post.
		 *
		 * Note: the global `$post` will be populated with the current post, if needed.
		 *
		 * @param bool $noyarpp true indicates that YARPP should be disabled on the post; false indicates it should be shown.
		 * @param string $content post's content
		 * @since 5.24.0
		 */
		$noyarpp = apply_filters( 'noyarpp', $noyarpp, $content );

		return $noyarpp;
	}

	/**
	 * DEFAULT CONTENT FILTERS
	 */
	public function the_content( $content ) {
		// Avoid infinite recursion.
		if ( is_feed() || $this->do_not_query_for_related() ) {
			return $content;
		}

		// Disable Automatic Display?
		if ( true === $this->is_noyarpp( $content ) ) {
			return $content;
		}

		$content .= $this->display_basic();
		$content .= $this->display_pro( 'website' );
		return $content;
	}

	public function the_content_feed( $content ) {
		if ( ! $this->get_option( 'rss_display' ) ) {
			return $content;
		}

		// Disable Automatic Display?
		if ( true === $this->is_noyarpp( $content ) ) {
			return $content;
		}

		return $content . $this->display_related(
			null,
			array(
				'domain' => 'rss',
			),
			false
		);
	}

	public function the_excerpt_rss( $content ) {
		if ( ! $this->get_option( 'rss_excerpt_display' ) || ! $this->get_option( 'rss_display' ) ) {
			return $content;
		}

		// Disable Automatic Display?
		if ( true === $this->is_noyarpp( $content ) ) {
			return $content;
		}

		return $content . $this->clean_pre( $this->display_related( null, array( 'domain' => 'rss' ), false ) );
	}

	/*
	 * UTILS
	 */

	/**
	 * @since 3.3  Use PHP serialized format instead of JSON.
	 */
	public function version_info( $enforce_cache = false ) {
		if ( ! $enforce_cache && false !== ( $result = $this->get_transient( 'yarpp_version_info' ) ) ) {
			return $result;
		}

		$version = YARPP_VERSION;
		$remote  = wp_remote_post( "https://yarpp.org/checkversion.php?format=php&version={$version}", array( 'sslverify' => false ) );

		if ( is_wp_error( $remote ) || wp_remote_retrieve_response_code( $remote ) != 200 || ! isset( $remote['body'] ) || ! is_array( $remote['body'] ) ) {
			$this->set_transient( 'yarpp_version_info', null, 60 * 60 );
			return false;
		}

		if ( $result = @unserialize( $remote['body'] ) ) {
			$this->set_transient( 'yarpp_version_info', $result, 60 * 60 * 24 );
		}

		return $result;
	}

	/**
	 * @since 4.0 Optional data collection (default off)
	 */
	public function optin_ping() {
		if ( $this->get_transient( 'yarpp_optin' ) ) {
			return true;
		}

		$remote = wp_remote_post( 'https://yarpp.org/optin/2/', array( 'body' => $this->optin_data(), 'sslverify' => false ) );

		if ( is_wp_error( $remote )
			|| wp_remote_retrieve_response_code( $remote ) != 200
			|| ! isset( $remote['body'] )
			|| $remote['body'] !== 'ok'
		) {
			/* try again later */
			$this->set_transient( 'yarpp_optin', null, 60 * 60 );
			return false;
		}

		$this->set_transient( 'yarpp_optin', null, 60 * 60 * 24 * 7 );

		return true;
	}

	/**
	 * A version of the transient functions which is unaffected by caching plugin behavior.
	 * We want to control the lifetime of data.
	 *
	 * @param int $transient
	 * @return bool
	 */
	private function get_transient( $transient ) {
		$transient_timeout = $transient . '_timeout';

		if ( intval( get_option( $transient_timeout ) ) < time() ) {
			delete_option( $transient_timeout );
			return false; // timed out.
		}

		return get_option( $transient, true ); // still ok.
	}

	private function set_transient( $transient, $data = null, $expiration = 0 ) {
		$transient_timeout = $transient . '_timeout';

		if ( get_option( $transient_timeout ) === false ) {

			add_option( $transient_timeout, time() + $expiration, '', 'no' );
			if ( ! is_null( $data ) ) {
				add_option( $transient, $data, '', 'no' );
			}
		} else {

			update_option( $transient_timeout, time() + $expiration );
			if ( ! is_null( $data ) ) {
				update_option( $transient, $data );
			}
		}

		$this->kick_other_caches();
	}

	private function delete_transient( $transient ) {
		delete_option( $transient );
		delete_option( $transient . '_timeout' );
	}

	/**
	 * @since 4.0.4  Helper function to force other caching systems which are too aggressive.
	 * <cough>DB Cache Reloaded (Fix)</cough> to flush when YARPP transients are set.
	 */
	private function kick_other_caches() {
		if ( class_exists( 'DBCacheReloaded' ) ) {
			global $wp_db_cache_reloaded;
			if ( is_object( $wp_db_cache_reloaded ) && is_a( $wp_db_cache_reloaded, 'DBCacheReloaded' ) ) {
				// if DBCR offered a more granualar way of just flushing options, I'd love that.
				$wp_db_cache_reloaded->dbcr_clear();
			}
		}
	}

	/**
	 * @since 3.5.2  Clean_pre is deprecated in WP 3.4, so implement here.
	 */
	function clean_pre( $text ) {
		$text = str_replace( array( '<br />', '<br/>', '<br>' ), array( '', '', '' ), $text );
		$text = str_replace( '<p>', "\n", $text );
		$text = str_replace( '</p>', '', $text );
		return $text;
	}

	/**
	 * Gets the list of valid interval units used by YARPP and MySQL interval statements.
	 *
	 * @return array keys are valid values for recent units, and for MySQL interval
	 * (see https://www.mysqltutorial.org/mysql-interval/), values are translated strings
	 */
	public function recent_units() {
		return array(
			'day'   => __( 'day(s)', 'yet-another-related-posts-plugin' ),
			'week'  => __( 'week(s)', 'yet-another-related-posts-plugin' ),
			'month' => __( 'month(s)', 'yet-another-related-posts-plugin' ),
		);
	}

	/**
	 * Adds YARPP's content to bbPress topics.
	 */
	public function add_to_bbpress() {
		echo $this->display_basic();
	}

	/**
	 * Checks if it's an appropriate time to look for related posts, or if we should skip that.
	 *
	 * There are two contrary indicators:
	 * 1. if the active cache is currently discovering post keywords. Finding related posts at this time causes
	 * infinite recursion because: in order to discover keywords, you need to get the post's FILTERED content, which
	 * would trigger adding related content to the post's body, which requires discovering its keywords, etc.
	 * 2. if YARPP is currently adding related content. Finding related posts at this time can cause infinite recursion
	 * because: the template file might show a posts't content or excerpt, which would cause adding related content
	 * to that post body or excerpt, which would start adding related content to it too, etc.    *
	 *
	 * @return bool
	 */
	protected function do_not_query_for_related() {
		return $this->rendering_related_content ||
				( $this->active_cache instanceof YARPP_Cache && $this->active_cache->discovering_keywords() );
	}
}
