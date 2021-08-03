<?php
/**
 * This Class is used to preview demo related posts returning dummy content and none of this data is related to the Database.
 * This is not meant to be used on production environments, unless you want to use it to show examples of related posts with dummy content.
 *
 * @since 5.26.0
 */
class YARPP_Cache_Demo_Bypass extends YARPP_Cache {

	public $name      = 'bypass';
	public $demo_time = false;

	private $demo_limit          = 0;
	private $demo_order          = 'score DESC';
	private $demo_thumbnail_size = 'thumbnail';

	/**
	 * SETUP/STATUS
	 */
	public function __construct( $core ) {
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
	 * For demo will always have thumbnails since are going to be generated on the fly. User by filter "post_thumbnail_id"
	 *
	 * @param array $arg
	 * @return int Must be negative to avoid clashes with existing ids
	 */
	public function demo_post_thumbnail_id() {
		return -1;
	}


	/**
	 * For demo will always have thumbnails since are going to be generated on the fly. User by filter "post_thumbnail_id"
	 *
	 * @param array $arg
	 * @return boolean
	 */
	public function demo_has_thumbnails_filter( $arg ) {
		return true;
	}

	/**
	 * Ignoring this filter since we are hardcoding the src of the default thumbnail
	 *
	 * @return array
	 */
	public function demo_image_downsize_filter() {
		return [false, false, false];
	}


	/**
	 * Update thumbnails sizes for demo thumbnails. user on Filter "post_thumbnail_size"
	 *
	 * Will return the demo_thumbnail_size.
	 *
	 * @return string
	 */
	public function demo_thumbnails_size_filter() {
		if ( empty($this->demo_thumbnail_size) ) {
			$this->demo_thumbnail_size = 'thumbnail';
		}
		return $this->demo_thumbnail_size;
	}

	/**
	 * Fills the metadata for the thumbnail image, this includes the sizes on the image
	 *
	 * @return array
	 */
	public function demo_image_metadata_filter() {
		$size = yarpp_get_image_sizes($this->demo_thumbnail_size);
		return array(
			'width' => $size['width'],
			'height' => $size['height'],
			'file' => '/images/preview_thumbnail_example.png',
			'sizes' => yarpp_get_image_sizes(),
			'image_meta' => []
		);
	}

	/**
	 * Generates the default thumbnail src. Uses filter "wp_get_attachment_image_src"
	 *
	 * @return array
	 */
	public function demo_thumbnails_src_filter() {
		$size = yarpp_get_image_sizes($this->demo_thumbnail_size);

		return array(
			plugins_url('/images/preview_thumbnail_example.png', YARPP_MAIN_FILE),
			$size['width'],
			$size['height']
		);
	}

	/**
	 * Overwrites the default thumbnail html. Uses filter "post_thumbnail_html"
	 *
	 * @return string html of the image
	 */
	public function demo_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		$size = apply_filters( 'post_thumbnail_size', $size, -1 );
		$html = wp_get_attachment_image( -1, $size, false, $attr );
		return $html;
	}

	/**
	 * Generates the post pages for the demo preview. Uses filter "posts_request"
	 *
	 * @return array
	 */
	public function demo_request_filter() {
		global $wpdb;

		$order           = $this->demo_order;
		$order           = explode(' ', $order);
		$order_direction = ( isset( $order[1] ) && 'ASC' === trim( $order[1]) ) ? 'ASC' : 'DESC';
		$order_column    = isset( $order[0] ) ? $order[0] : 'score';

		if ( ! in_array($order_column, array('score', 'post_date', 'post_title')) ) {
			$order_column = 'score';
		}

		$wpdb->query( 'set @count = 0;' );

		$post_title = __( 'Example post ', 'yet-another-related-posts-plugin' );
		$loremipsum =
		'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras tincidunt justo a urna. Ut turpis. Phasellus' .
		'convallis, odio sit amet cursus convallis, eros orci scelerisque velit, ut sodales neque nisl at ante. ' .
		'Suspendisse metus. Curabitur auctor pede quis mi. Pellentesque lorem justo, condimentum ac, dapibus sit ' .
		'amet, ornare et, erat. Quisque velit. Etiam sodales dui feugiat neque suscipit bibendum. Integer mattis. ' .
		'Nullam et ante non sem commodo malesuada. Pellentesque ultrices fermentum lectus. Maecenas hendrerit neque ac ' .
		'est. Fusce tortor mi, tristique sed, cursus at, pellentesque non, dui. Suspendisse potenti.';

		return $wpdb->prepare(
			"SELECT
				SQL_CALC_FOUND_ROWS ID + %d as ID,
				post_author,
				CURRENT_TIMESTAMP - INTERVAL FLOOR(RAND() * 30) DAY AS post_date,
				post_date_gmt,
				'{$loremipsum}' as post_content,
				concat(%s, @count:=@count+1) as post_title,
				0 as post_category,
				'' as post_excerpt,
				'publish' as post_status,
				'open' as comment_status,
				'open' as ping_status,
				'' as post_password,
				concat('example-post-',@count) as post_name,
				'' as to_ping,
				'' as pinged,
				post_modified,
				post_modified_gmt,
				'' as post_content_filtered,
				0 as post_parent,
				concat('PERMALINK',@count) as guid,
				0 as menu_order,
				'post' as post_type,
				'' as post_mime_type,
				0 as comment_count,
				ROUND(RAND() * 5, 2) as score
			FROM $wpdb->posts
			ORDER BY {$order_column} {$order_direction} LIMIT 0, %d",
			$this->demo_limit,
			$post_title,
			$this->demo_limit
		);
	}


	/**
	 * Starts Demo preview generation of thumbnails and posts
	 *
	 * @param integer $limit
	 * @param string  $order
	 * @param string  $thumbnail
	 * @param string  $size
	 * @return void
	 */
	public function begin_demo_time( $limit, $order = 'score DESC', $size = '' ) {
		$this->demo_time           = true;
		$this->demo_limit          = $limit;
		$this->demo_order          = $order;
		$this->demo_thumbnail_size = $size;

		add_action( 'pre_get_posts', array( &$this, 'add_signature' ) );
		add_filter( 'posts_request', array( &$this, 'demo_request_filter' ) );
		add_filter( 'has_post_thumbnail', array( &$this, 'demo_has_thumbnails_filter' ) );
		add_filter( 'post_thumbnail_id', array( &$this, 'demo_post_thumbnail_id' ) );
		add_filter( 'image_downsize', array( &$this, 'demo_image_downsize_filter' ) );
		add_filter( 'post_thumbnail_size', array( &$this, 'demo_thumbnails_size_filter' ) );
		add_filter( 'wp_get_attachment_image_src', array(&$this, 'demo_thumbnails_src_filter') );
		add_filter( 'post_thumbnail_html', array(&$this, 'demo_post_thumbnail_html'), 10 , 5 );
		add_filter( 'wp_get_attachment_metadata', array( &$this, 'demo_image_metadata_filter' ) );
	}

	/**
	 * End Demo preview generation of thumbnails and posts
	 *
	 * @return void
	 */
	public function end_demo_time() {
		$this->demo_time = false;

		remove_action( 'pre_get_posts', array( &$this, 'add_signature' ) );
		remove_filter( 'posts_request', array( &$this, 'demo_request_filter' ) );
		remove_filter( 'has_post_thumbnail', array( &$this, 'demo_has_thumbnails_filter' ) );
		remove_filter( 'post_thumbnail_id', array( &$this, 'demo_post_thumbnail_id' ) );
		remove_filter( 'image_downsize', array( &$this, 'demo_image_downsize_filter' ) );
		remove_filter( 'post_thumbnail_size', array( &$this, 'demo_thumbnails_size_filter' ) );
		remove_filter( 'wp_get_attachment_image_src', array(&$this, 'demo_thumbnails_src_filter') );
		remove_filter( 'post_thumbnail_html', array(&$this, 'demo_post_thumbnail_html') );
		remove_filter( 'wp_get_attachment_metadata', array( &$this, 'demo_image_metadata_filter' ) );
	}

	public function related( $reference_ID = null, $related_ID = null ) {
		global $wpdb;

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
