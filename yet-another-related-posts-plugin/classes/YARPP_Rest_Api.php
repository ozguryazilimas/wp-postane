<?php

/**
 * YARPP rest api functionality
 */
class YARPP_Rest_Api extends WP_REST_Controller {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );
		add_filter( 'wp_rest_cache/allowed_endpoints', array( $this, 'cache_endpoints' ), 10, 1 );
	}

	/**
	 * @var \WP_REST_Posts_Controller|null
	 */
	protected $posts_controller = null;

	/**
	 * Initializes yarpp rest routes via rest_api_init
	 */
	function register_api_routes() {
		global $yarpp;

		if ( $yarpp->get_option( 'rest_api_display' ) ) {
			$NAMESPACE = 'yarpp/v1';

			/* Register the yarpp rest route */
			register_rest_route(
				$NAMESPACE,
				'/related/(?P<id>[\w-]+)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_related_posts' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => $this->get_related_posts_args(),
					),
					'args'   => array(
						'id' => array(
							'description' => __( 'Unique identifier for the object.' ),
							'type'        => 'integer',
						),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}
	}

	/**
	 * Wraps WP_REST_Posts_Controller's schema, and adds YARPP-specific fields.
	 *
	 * @return array
	 */
	public function get_public_item_schema() {
		$posts_schema                        = $this->get_posts_controller( 'post' )->get_public_item_schema();
		$posts_schema['properties']['score'] = array(
			'description' => __( 'YARPP relatedness score', 'yet-another-related-posts-plugin' ),
			'type'        => 'number',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);
		return $posts_schema;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		$error_response = new WP_Error(
			'rest_forbidden_context',
			__( 'Sorry, you are not allowed to read this post.', 'yet-another-related-posts-plugin' ),
			array( 'status' => rest_authorization_required_code() )
		);
		$post_obj       = get_post( $request->get_param( 'id' ) );
		if ( ! $this->get_posts_controller( $post_obj->post_type )->check_read_permission( $post_obj ) ) {
			return $error_response;
		}

		$core_permissions_check = $this->get_posts_controller()->get_items_permissions_check( $request );
		if ( $core_permissions_check instanceof WP_Error ) {
			return $core_permissions_check;
		}
		// Check for password-protected posts.
		if ( ! empty( $post_obj->post_password ) && ! $this->get_posts_controller()->can_access_password_content( $post_obj, $request ) ) {
			return $error_response;
		}
		return true;
	}


	/**
	 * Gets available arguments for related-posts endpoint.
	 *
	 * @return array
	 */
	public function get_related_posts_args() {
		/**
		 * @var $yarpp YARPRP
		 */
		global $yarpp;

		return array(
			'limit'    => array(
				'description'       => esc_html( 'Number of posts to display', 'yet-another-related-posts-plugin' ),
				'type'              => 'integer',
				'default'           => $yarpp->get_option( 'limit' ),
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'absint',
				'minimum'           => 1,
				'maximum'           => apply_filters(
					'yarpp_rest_api_get_related_posts_args_limit_maximum',
					20
				),
			),
			'context'  => array_replace_recursive(
				$this->get_context_param(),
				array(
					'default' => 'embed',
				)
			),
			'password' => array(
				'description' => __( 'The password for the post if it is password protected.' ),
				'type'        => 'string',
			),
		);
	}

	/**
	 * Gets related posts provided a id param exists.
	 *
	 * @param WP_REST_REQUEST $request Incoming HTTP request data.
	 * @return WP_Error|WP_HTTP_Response
	 */
	public function get_related_posts( $request ) {
		/**
		 * @var $yarpp YARPP
		 */
		global $yarpp;

		$query_params = $request->get_params();
		$id           = $query_params['id'];

		$post_obj = get_post( $id );
		if ( ! $post_obj instanceof WP_Post ) {
			return new WP_Error( 'rest_invalid_id', esc_html__( 'Invalid ID', 'yet-another-related-posts-plugin' ), array( 'status' => 404 ) );
		}
		$allowed_args = array( 'limit' );

		$args          = array_filter(
			$query_params,
			function ( $key ) use ( $allowed_args ) {
				return in_array( $key, $allowed_args );
			},
			ARRAY_FILTER_USE_KEY
		);
		$related_posts = $yarpp->get_related(
			$id,
			$args
		);

		// Great, we have the posts we want. But they're formatted totally differently than the WP REST API endpoints
		// So we use the core WP_RESTS_Posts_Controller to get the response in exactly the same format.
		$ids               = wp_list_pluck( $related_posts, 'ID' );
		$read_controller   = $this->get_posts_controller( $post_obj->post_type );
		$simulated_request = clone $request;
		$simulated_request->set_route( 'wp/v1/posts' );

		$simulated_params = array(
			'include'  => $ids,
			'per_page' => $query_params['limit'],
			// we only get one page at a time. WP page numbering starts at 1.
			'page'     => 1,
		);
		if ( isset( $query_params['context'] ) ) {
			$simulated_params['context'] = $query_params['context'];
		}

		$simulated_request->set_query_params( $simulated_params );

		// Hack the WordPress Posts controller to return posts of all types, so long as they have the IDs we want.
		add_action( 'rest_' . $post_obj->post_type . '_query', array( $this, 'ignore_post_type_filter_callback' ), 10, 2 );
		$read_controller_response = $read_controller->get_items( $simulated_request );
		remove_action( 'rest_' . $post_obj->post_type . '_query', array( $this, 'ignore_post_type_filter_callback' ), 10, 2 );

		if ( is_wp_error( $read_controller_response ) ) {
			return $read_controller_response;
		}
		$read_controller_posts = $read_controller_response->get_data();
		$ordered_rest_results  = array();
		// Reorder the posts in the response according to what they were in the YARPP response.
		foreach ( $related_posts as $related_post ) {
			foreach ( $read_controller_posts as $read_controller_post ) {
				if ( $related_post->ID === $read_controller_post['id'] ) {
					// Add score, but before _links.
					$links = $read_controller_post['_links'];
					unset( $read_controller_post['_links'] );
					$read_controller_post['score']  = (float) $related_post->score;
					$read_controller_post['_links'] = $links;
					$ordered_rest_results[]         = $read_controller_post;
				}
			}
		}
		$read_controller_response->set_data( $ordered_rest_results );
		$this->maybe_set_caching_headers( $read_controller_response );
		return $read_controller_response;
	}

	/**
	 * If enables, sends HTTP headers along with the response that instructs the browser to cache the results.
	 *
	 * @param WP_Rest_Response $response
	 */
	protected function maybe_set_caching_headers( WP_Rest_Response $response ) {
		global $yarpp;
		if ( $yarpp->get_option( 'rest_api_client_side_caching' ) ) {
			$seconds_to_cache = (int) $yarpp->get_option( 'yarpp_rest_api_cache_time' ) * MINUTE_IN_SECONDS;
			$seconds_to_cache = max( $seconds_to_cache, 0 ); // ensure non-negative values.
			$ts               = gmdate( 'D, d M Y H:i:s', time() + $seconds_to_cache ) . ' GMT';
			$response->header( 'Expires', $ts );
			$response->header( 'Cache-Control', "public, max-age=$seconds_to_cache" );
		}
	}

	/**
	 * @param string $post_type
	 *
	 * @return WP_REST_Posts_Controller
	 */
	protected function get_posts_controller( $post_type = null ) {
		if ( ! $this->posts_controller instanceof WP_REST_Posts_Controller ) {
			$this->posts_controller = new WP_REST_Posts_Controller( $post_type );
		}
		return $this->posts_controller;
	}

	/**
	 * Register the /wp-json/yarpp/v1/related for caching with https://wordpress.org/plugins/wp-rest-cache/
	 */
	function cache_endpoints( $allowed_endpoints ) {
		if ( ! isset( $allowed_endpoints['yarpp/v1'] ) || ! in_array( 'related', $allowed_endpoints['yarpp/v1'] ) ) {
			$allowed_endpoints['yarpp/v1'][] = 'related';
		}
		return $allowed_endpoints;
	}

	/**
	 * Filters what post types the WordPress Posts Controller uses when querying.
	 * This way we can ask the posts controller for all posts of any type (remember we're only fetching ones with
	 * IDs that match the results of YARPP's related query.) The results are all formatted like posts, which isn't
	 * stellar, but it's got the important info.
	 *
	 * @param $args
	 * @param $request
	 *
	 * @return mixed
	 */
	public function ignore_post_type_filter_callback( $args, $request ) {
			global $yarpp;
			$args['post_type'] = $yarpp->get_post_types();
			return $args;
	}
}
