<?php

namespace WBCR\APT;

use Exception, WP_Query, WP_Error;

/**
 * Class AutoPostThumbnails
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>, Github: https://github.com/temyk
 * @copyright (c) 2019, Webcraftic Ltd
 */
class AutoPostThumbnails {

	/**
	 * @var self
	 */
	public static $instance;

	/**
	 * @var \WAPT_Plugin
	 */
	private $plugin;

	/**
	 * После какой по счёту колонки вставлять новую (если 0, то в самом начале)
	 *
	 * @var integer
	 */
	public $numberOfColumn;

	/**
	 * @var AutoPostThumbnails
	 */
	private $nonce;

	/**
	 * Массив с параметрами сервисов
	 *
	 * @var array(string)
	 */
	public $sources;

	/**
	 * Открывается в медиабиблиотеке?
	 *
	 * @var bool
	 */
	public $is_in_medialibrary = false;

	public $allowed_generate_post_types;

	/**
	 * AutoPostThumbnails constructor.
	 */
	public function __construct() {
		$this->numberOfColumn = 4;
		$this->plugin         = \WAPT_Plugin::app();

		$this->sources = [
			'google'    => WAPT_PLUGIN_SLUG,
			'recommend' => '',
			'pixabay'   => '',
			'unsplash'  => '',
		];
		if ( \WAPT_Plugin::app()->is_premium() ) {
			$this->sources = [
				'recommend' => '',
				'google'    => WAPT_PLUGIN_SLUG,
				'pixabay'   => '',
				'unsplash'  => '',
			];
		}

		$this->init_includes();
		$this->init();
	}

	/**
	 * Get existing instance or create new one.
	 *
	 * @return AutoPostThumbnails
	 */
	public static function instance() {
		if ( static::$instance === null ) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	/**
	 * Init includes.
	 */
	private function init_includes() {
		//require __DIR__ . '/src/class.template.php';
	}

	/**
	 * Initiate all required hooks.
	 */
	private function init() {
		$is_auto_generate          = \WAPT_Plugin::app()->getOption( 'auto_generation', true );
		$is_auto_upload            = \WAPT_Plugin::app()->getOption( 'auto_upload_images' );
		$allowed_import_post_types = explode( ',', \WAPT_Plugin::app()->getOption( 'import_post_types', 'post' ) );

		$this->allowed_generate_post_types = explode( ',', \WAPT_Plugin::app()->getOption( 'auto_post_types', 'post,page' ) );

		add_filter( 'mime_types', [ $this, 'allow_upload_webp' ] );

		if ( $is_auto_upload && \WAPT_Plugin::app()->is_premium() ) {
			add_filter( 'wp_insert_post_data', [ $this, 'auto_upload' ], 10, 2 );

			// This hook handle update post via rest api. for example Wordpress mobile apps
			foreach ( $allowed_import_post_types as $post_type ) {
				add_action( "rest_after_insert_{$post_type}", [ $this, 'auto_upload' ], 10, 2 );
			}
		}

		if ( $is_auto_generate ) {
			//add_action( 'publish_post', [ $this, 'publish_post' ], 10, 1 );
			add_action( 'save_post', [ $this, 'publish_post' ], 10, 3 );

			// This hook handle update post via rest api. for example Wordpress mobile apps
			foreach ( $this->allowed_generate_post_types as $post_type ) {
				add_action( "rest_after_insert_{$post_type}", [ $this, 'rest_after_insert' ], 10, 3 );
			}
			// This hook will now handle all sort publishing including posts, custom types, scheduled posts, etc.
			add_action( 'transition_post_status', [ $this, 'check_required_transition' ], 10, 3 );
		} else {
			if ( \WAPT_Plugin::app()->getOption( 'auto_generation_notice', 1 ) ) {
				add_action( 'admin_notices', [ $this, 'notice_auto_generation' ] );
			}
		}

		add_action( 'wbcr/factory/admin_notices', [ $this, 'check_api_notice' ], 10, 2 );

		$this->ajax_actions();
	}

	private function ajax_actions() {
		//AJAX actions
		add_action( 'wp_ajax_generatepostthumbnail', [ $this, 'ajax_process_post', ] );
		add_action( 'wp_ajax_delete_post_thumbnails', [ $this, 'ajax_delete_post_thumbnails', ] );
		add_action( 'wp_ajax_get-posts-ids', [ $this, 'get_posts_ids' ] );
		add_action( 'wp_ajax_apt_replace_thumbnail', [ $this, 'apt_replace_thumbnail' ] );
		add_action( 'wp_ajax_apt_get_thumbnail', [ $this, 'apt_get_thumbnail' ] );
		add_action( 'wp_ajax_source_content', [ $this, 'source_content' ] );
		add_action( 'wp_ajax_upload_to_library', [ $this, 'upload_to_library' ] );
		add_action( 'wp_ajax_wapt_upload_font', [ $this, 'upload_font' ] );

		//APIs
		add_action( 'wp_ajax_apt_api_google', [ $this, 'apt_api_google' ] );
		add_action( 'wp_ajax_apt_check_api_key', [ $this, 'apt_check_api_key' ] );
		add_action( 'wp_ajax_hide_notice_auto_generation', [ $this, 'hide_notice_auto_generation' ] );
	}

	public function allow_upload_webp( $existing_mimes ) {
		$existing_mimes['webp'] = 'image/webp';

		return $existing_mimes;
	}

	/**
	 * Get posts id's
	 *
	 * @return void
	 */
	public function get_posts_ids() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( '-1' );
		}
		check_ajax_referer( 'get-posts' );

		$generate = \WAPT_Plugin::app()->getOption( "generate_autoimage", 'find' );

		$this->plugin->logger->info( "START generate in mode:  {$generate}" );

		switch ( $generate ) {
			case 'generate':
			case 'both':
			case 'google':
			case 'find_google':
				$auto_generate = true;
				break;
			default:
				$auto_generate = false;
				break;
		}

		$has_thumb = (bool) $_POST['withThumb'];
		$type      = $_POST['posttype'];
		if ( \WAPT_Plugin::app()->is_premium() ) {
			$status     = $_POST['poststatus'];
			$category   = $_POST['category'];
			$date_start = $_POST['date_start'] ? \DateTime::createFromFormat( 'd.m.Y', $_POST['date_start'] )->format( 'd.m.Y' ) : 0;
			$date_end   = $_POST['date_end'] ? \DateTime::createFromFormat( 'd.m.Y', $_POST['date_end'] )->setTime( 23, 59 )->format( 'd.m.Y H:i' ) : 0;
			// Get id's of the posts that satisfy the filters
			$query = $this->get_posts_query( $has_thumb, $type, $status, $category, $date_start, $date_end );
		} else {
			// Get id's of all the published posts for which post thumbnails exist or does not exist
			$query = $this->get_posts_query( $has_thumb, $type );
		}

		if ( ! empty( $query->posts ) ) {
			// Generate the list of IDs
			$ids = [];
			foreach ( $query->posts as $post ) {
				$ids[] = $post->ID;
				//если запрошены посты без тамбнеила, значит пользователь хочет сгенерировать их
				/*if ( ! $has_thumb ) {
					$images = new \WBCR\APT\PostImages( $post->ID );
					if ( ( $images->is_images() && $images->count_images() ) || $auto_generate ) {
						$ids[] = $post->ID;
					}
				} else //иначе он хочет удалить тамбнэйлы
				{
					$ids[] = $post->ID;
				}*/
			}
			$ids = implode( ',', $ids );

		} else {
			$ids = "0";
		}
		echo $ids;

		$this->plugin->logger->info( "Queried posts IDs:  {$ids}" );

		die( - 1 );
	}

	/**
	 * Process single post to generate the post thumbnail
	 *
	 * @return void
	 */
	public function ajax_process_post() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( '-1' );
		}
		check_ajax_referer( 'generate-post-thumbnails' );
		if ( isset( $_POST['id'] ) && ! empty( $_POST['id'] ) ) {
			$id = intval( $_POST['id'] );
			if ( empty( $id ) ) {
				die( '-2' );
			}
			set_time_limit( 60 );

			$this->plugin->logger->info( "--Start processing post ID = {$id}" );
			$result = $this->publish_post( $id );
			$this->plugin->logger->info( "--End processing post ID = {$id}" );

			$thumb_id = $result->thumbnail_id;

			if ( $thumb_id ) {
				wp_send_json_success( $result->getData( true ) );
			} else {
				wp_send_json_error( $result->getData( true ) );
			}
		}

		die( '-3' );
	}

	/**
	 * Process single post to delete the post thumbnail
	 *
	 * @return void
	 */
	public function ajax_delete_post_thumbnails() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( '-1' );
		}
		check_ajax_referer( 'delete-post-thumbnails' );
		if ( isset( $_POST['id'] ) && ! empty( $_POST['id'] ) ) {
			$id = intval( $_POST['id'] );

			if ( empty( $id ) ) {
				die( '-1' );
			}

			set_time_limit( 60 );

			// Pass on the id to our 'publish' callback function.
			echo delete_post_thumbnail( $id );

			die( - 1 );
		}
		die( - 1 );
	}

	/**
	 *
	 */
	public function notice_auto_generation() {
		?>
        <div class="notice notice-warning is-dismissible" id="notice_auto_generation">
            <p><b>Auto Featured Image:</b> Do you want to enable automatic post thumbnail generation? Enable this option
                in
                <a href="<?php echo admin_url( 'admin.php?page=wapt_settings-wbcr_apt&tab=general' ); ?>">settings</a><br>
                <a href="#" id="hide_notice_auto_generation">Don't ask again</a>
            </p>
        </div>
		<?php
	}

	/**
	 *
	 */
	public function hide_notice_auto_generation() {
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'hide_notice_auto_generation' ) {
			\WAPT_Plugin::app()->updateOption( 'auto_generation_notice', 0 );
		}
	}

	/**
	 * Function to check whether scheduled post is being published. If so, apt_publish_post should be called.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post Instance of post.
	 *
	 * @return void
	 */
	public function check_required_transition( $new_status = '', $old_status = '', $post = '' ) {

		if ( 'publish' == $new_status ) {
			$this->publish_post( $post->ID );
		}
	}

	/**
	 * Return sql query, which allows to receive all the posts without thumbnails
	 *
	 * @return WP_Query
	 */
	public function get_posts_query( $has_thumb = false, $type = 'post', $status = 'publish', $category = 0, $date_start = 0, $date_end = 0, $is_log = true ) {

		if ( $is_log ) {
			$this->plugin->logger->info( "Posts query: " . var_export( [
					'has_thumb'  => $has_thumb,
					'type'       => $type,
					'status'     => $status,
					'category'   => $category,
					'date_start' => $date_start,
					'date_end'   => $date_end,
				], true ) );
		}

		$q_status    = $status ? $status : 'any';
		$q_type      = $type ? $type : 'any';
		$q_has_thumb = $has_thumb ? "EXISTS" : "NOT EXISTS";

		$args = array(
			'posts_per_page' => - 1,
			'post_status'    => $q_status,
			'post_type'      => $q_type,
			'meta_query'     => array(
				'relation' => 'AND',
				array( 'key' => '_thumbnail_id', 'compare' => $q_has_thumb ),
				array( 'key' => 'skip_post_thumb', 'compare' => 'NOT EXISTS' ),
			),
		);
		if ( $category ) {
			$args['cat'] = $category;
		}
		if ( $date_start && $date_end ) {
			$args['date_query'][] = array( 'after' => $date_start, 'before' => $date_end, 'inclusive' => true, );
		}
		$query = new WP_Query( $args );

		//$this->plugin->logger->debug( "Posts SQL: " . $query->request );

		return $query;
	}

	/**
	 * Return sql query, which allows to receive all the posts without thumbnails
	 *
	 * @return int
	 */
	public function get_posts_count( $has_thumb = false, $type = 'post', $status = 'publish', $category = 0, $date_start = 0, $date_end = 0 ) {
		$query = $this->get_posts_query( $has_thumb, $type, $status, $category, $date_start, $date_end, false );

		return $query->found_posts;
	}

	/**
	 * Get thumbnail id for image
	 *
	 * @param array $image
	 *
	 * @return bool|int
	 */
	public function get_thumbnail_id( $image ) {
		global $wpdb;
		$thumb_id = 0;

		/**
		 * If the image is from the WordPress own media gallery, then it appends the thumbnail id to a css class.
		 * Look for this id in the IMG tag.
		 */
		if ( isset( $image['tag'] ) && ! empty( $image['tag'] ) ) {
			preg_match( '/wp-image-([\d]*)/i', $image['tag'], $thumb_id );

			if ( $thumb_id ) {
				$thumb_id = $thumb_id[1];

				if ( ! get_post( $thumb_id ) ) {
					$thumb_id = false;
				}
			}
		}

		if ( ! $thumb_id ) {
			// If thumb id is not found, try to look for the image in DB.
			if ( isset( $image['url'] ) && ! empty( $image['url'] ) ) {
				$image_url = $image['url'];
				//если ссылка на миниатюру, то регулярка сделает ссылку на оригинал. убирает в конце названия файла -150x150
				$image_url = preg_replace( '/-[0-9]{1,}x[0-9]{1,}\./', '.', $image_url );
				$thumb_id  = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '%" . esc_sql( $image_url ) . "%'" );
			}
		}

		return is_numeric( $thumb_id ) ? $thumb_id : false;
	}

	/**
	 * @param \WP_Post $post
	 * @param \WP_REST_Request $request
	 * @param bool $is_insert
	 *
	 * @throws Exception
	 */
	public function rest_after_insert( $post, $request, $is_insert ) {
		$this->publish_post( $post->ID, $post, ! $is_insert );
	}

	/**
	 * Function to save first image in post as post thumbnail.
	 *
	 * @param int $post_id Post ID.
	 * @param \WP_Post $post
	 * @param bool $update
	 *
	 * @return GenerateResult
	 * @throws Exception
	 */
	public function publish_post( $post_id, $post = null, $update = true ) {
		global $wpdb;

		$autoimage  = \WAPT_Plugin::app()->getOption( "generate_autoimage", 'find' );
		$generation = new GenerateResult( $post_id, $autoimage );

		if ( ! $post ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				$this->plugin->logger->warning( "The post was not found (post ID = {$post_id})" );

				return $generation->result( __( "The post was not found", 'apt' ) );
			}
		}

		if ( ! $update ) {
			return $generation->result();

		}

		if ( ! in_array( $post->post_type, $this->allowed_generate_post_types ) ) {
			$this->plugin->logger->warning( "The post type ({$post->post_type}) is not allowed for generation in settings" );

			return $generation->result( __( "The post type is not allowed for generation in settings", 'apt' ) );
		}

		// First check whether Post Thumbnail is already set for this post.
		$_thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
		if ( $_thumbnail_id && $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE id = '" . esc_sql( $_thumbnail_id ) . "' AND post_type = 'attachment'" ) || get_post_meta( $post_id, 'skip_post_thumb', true ) ) {
			$this->plugin->logger->warning( "The post ({$post_id}) has already been assigned a featured image" );

			return $generation->result( __( "The post has already been assigned a featured image", 'apt' ) );
		}

		$thumb_id = 0;

		$images = new \WBCR\APT\PostImages( $post_id );
		if ( ( $images->is_images() && $images->count_images() ) && $autoimage !== 'generate' && $autoimage !== 'google' ) {

			foreach ( $images->get_images() as $image ) {
				$thumb_id = $this->get_thumbnail_id( $image );
				// If we succeed in generating thumb, let's update post meta
				if ( $thumb_id ) {
					$this->plugin->logger->info( "An attachment ({$thumb_id}) was found in the text of the post." );
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
					$this->plugin->logger->info( "Featured image ($thumb_id) is set for post ($post_id)" );

				} else {
					if ( \WAPT_Plugin::app()->is_premium() ) {
						$thumb_id = apply_filters( 'wapt/generate_post_thumb', $image, $post_id );

						if ( $thumb_id ) {
							update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
						}
					}
				}

				return $generation->result( '', $thumb_id );

			}
		} else {
			// создаём свою картинку с заголовком на цветном фоне
			if ( $autoimage == 'generate' || $autoimage == 'both' ) {

				$thumb_id = $this->generate_and_attachment( $post_id );
				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );

					return $generation->result( '', $thumb_id );
				}
			} else if ( $autoimage == 'google' || $autoimage == 'find_google' ) {
				$response = ( new GoogleImages() )->search( $post->post_title, 1 );
				if ( ! empty( $response->images ) ) {
					$this->plugin->logger->info( "Google image search results = " . var_export( $response->images, true ) );
					$thumb_id = apply_filters( 'wapt/download_from_google', 0, $response->images, $post_id );
				}
				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
					$this->plugin->logger->info( "Successful download from google. Attachment ID = {$thumb_id}" );

					return $generation->result( '', $thumb_id );
				}
				$this->plugin->logger->error( "Error download from google. " . var_export( $thumb_id, true ) );
			}
		}

		return $generation->result( __( "No images found or generated", 'apt' ) );
	}

	/**
	 * Function to save first image in post as post thumbnail.
	 *
	 * @param \WP_Post|array $post
	 * @param array $postarr
	 */
	public function auto_upload( $data, $postarr = [] ) {
		$allowed_post_types = explode( ',', \WAPT_Plugin::app()->getOption( 'import_post_types', '' ) );

		if ( $data instanceof \WP_Post ) {
			$post_type = $data->post_type ?? '';
		} else {
			$post_type = $data['post_type'] ?? '';
		}
		if ( $post_type && in_array( $post_type, $allowed_post_types ) ) {
			$data = apply_filters( 'wapt/upload_and_replace_post_images', $data );
		}

		return $data;
	}

	/**
	 * Search through an array for a matching key.
	 *
	 * Examples:
	 * <code>
	 *      $array = array(
	 *          "database.name" => "my_db_name",
	 *          "database.host" => "myhost.com",
	 *          "database.user" => "admin",
	 *          "database.pass" => "a secret."
	 *      );
	 *
	 *      $search = array_contains_key($array, "database");
	 *      var_dump($search);
	 *
	 *      Result:
	 *      array (size=4)
	 *          'database.name' => string 'my_db_name' (length=10)
	 *          'database.host' => string 'myhost.com' (length=10)
	 *          'database.user' => string 'admin' (length=5)
	 *          'database.pass' => string 'a secret.' (length=9)
	 * </code>
	 *
	 * https://gist.github.com/steve-todorov/3671626
	 *
	 * @param array $input_array
	 * @param string $search_value
	 * @param bool $case_sensitive
	 *
	 * @return array
	 */
	function array_contains_key( array $input_array, $search_value, $case_sensitive = false ) {
		if ( $case_sensitive ) {
			$preg_match = '/' . $search_value . '/';
		} else {
			$preg_match = '/' . $search_value . '/i';
		}
		$return_array = [];
		$keys         = array_keys( $input_array );
		foreach ( $keys as $k ) {
			if ( preg_match( $preg_match, $k ) ) {
				$return_array[ $k ] = $input_array[ $k ];
			}
		}

		return $return_array;
	}

	/**
	 * Fetch image from URL and generate required thumbnails.
	 *
	 *
	 * @param string $image
	 * @param string $title
	 * @param int $post_id
	 *
	 * @return int|WP_Error|null
	 */
	public function generate_post_thumb( $image, $title, $post_id ) {
		// Get the URL now for further processing
		$imageUrl = $image;
		if ( $imageUrl === wp_make_link_relative( $imageUrl ) ) {
			$imageUrl = home_url( $imageUrl );
		}
		$imageTitle = $title;

		// Get the file name
		$filename = substr( $imageUrl, ( strrpos( $imageUrl, '/' ) ) + 1 );
		//исключаем параметры после имени файла
		if ( strrpos( $filename, '?' ) ) {
			$filename = substr( $filename, 0, strrpos( $filename, '?' ) );
		}

		if ( ! ( ( $uploads = wp_upload_dir( current_time( 'mysql' ) ) ) && false === $uploads['error'] ) ) {
			return null;
		}

		// Generate unique file name
		$filename = wp_unique_filename( $uploads['path'], $filename );

		$new_file = $uploads['path'] . "/$filename";
		$ext      = pathinfo( $new_file, PATHINFO_EXTENSION );
		if ( empty( $ext ) ) {
			$ext      = "jpg";
			$filename .= ".{$ext}";
			$new_file .= ".{$ext}";
		}

		// Move the file to the uploads dir
		if ( ! ini_get( 'allow_url_fopen' ) ) {
			$file_data = $this->curl_get_file_contents( $imageUrl );
		} else {
			$arrContextOptions = array( "ssl" => array( "verify_peer" => false, "verify_peer_name" => false, ), );
			$file_data         = file_get_contents( $imageUrl, false, stream_context_create( $arrContextOptions ) );
		}


		if ( ! $file_data ) {
			return null;
		}

		//Fix for checking file extensions
		$exts = explode( ".", $filename );
		if ( count( $exts ) > 2 ) {
			//return null;
		}

		$allowed = get_allowed_mime_types();
		if ( ! $this->array_contains_key( $allowed, $ext ) ) {
			return null;
		}

		file_put_contents( $new_file, $file_data );

		// Set correct file permissions
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		$mimes = $type = $file = null;

		// Get the file type. Must to use it as a post thumbnail.
		$wp_filetype = wp_check_filetype( $filename, $mimes );

		extract( $wp_filetype );

		// No file type! No point to proceed further
		if ( ( ! $type || ! $ext ) && ! current_user_can( 'unfiltered_upload' ) ) {
			return null;
		}

		// Compute the URL
		$url = $uploads['url'] . "/$filename";

		// Construct the attachment array
		$attachment = [
			'post_mime_type' => $type,
			'guid'           => $url,
			'post_parent'    => null,
			'post_title'     => $imageTitle,
			'post_content'   => '',
		];

		$thumb_id = wp_insert_attachment( $attachment, $file, $post_id );
		if ( ! is_wp_error( $thumb_id ) ) {
			require_once( ABSPATH . '/wp-admin/includes/image.php' );

			// Added fix by misthero as suggested
			wp_update_attachment_metadata( $thumb_id, wp_generate_attachment_metadata( $thumb_id, $new_file ) );
			update_attached_file( $thumb_id, $new_file );

			return $thumb_id;
		}

		return null;
	}

	/**
	 * Function to fetch the contents of URL using curl in absence of allow_url_fopen.
	 *
	 * Copied from user comment on php.net (http://in.php.net/manual/en/function.file-get-contents.php#82255)
	 */
	public function curl_get_file_contents( $URL ) {
		$c = curl_init();
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $c, CURLOPT_URL, $URL );
		curl_setopt( $c, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
		$contents = curl_exec( $c );
		curl_close( $c );

		if ( $contents ) {
			return $contents;
		}

		return false;
	}

	/**
	 * Используется для динамического обновления столбца "Image" после выбора изображения в общем списке постов
	 *
	 * @return array|bool
	 *
	 * @uses apt_thumb
	 */
	public function apt_replace_thumbnail() {

		if ( isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) {
			$post_id = intval( $_POST['post_id'] );
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_die( - 1 );
			} else {
				check_ajax_referer( 'set_post_thumbnail-' . $post_id );
				if ( isset( $_POST['thumbnail_id'] ) && ! empty( $_POST['thumbnail_id'] ) ) {
					$thumb_id = intval( $_POST['thumbnail_id'] );

					if ( $thumb_id == - 1 ) {
						//generate image
						$thumb_id = $this->generate_and_attachment( $post_id );
					}
				} else if ( isset( $_POST['image'] ) && ! empty( $_POST['image'] ) ) {
					$img = $_POST['image'];

					//Совместимость с NextGen
					$img = preg_replace( '/(thumbs\/thumbs_)/', '.', $img );

					//Find image in medialibrary
					$thumb_id = $this->get_thumbnail_id( [ 'url' => $img ] );

					if ( ! $thumb_id ) {
						$thumb_id = $this->generate_post_thumb( $img, '', $post_id );
					}
				} else {
					$thumb_id = 0;
				}
				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
				}

				echo $this->apt_getThumbHtml( $post_id, $thumb_id );
			}
		}
		die();
	}

	/**
	 * Используется для динамической загрузки изображений поста в окно выбора
	 *
	 * @return array|bool
	 * @uses apt_thumb
	 */
	public function apt_get_thumbnail() {
		include WAPT_ABSPATH . "/admin/views/pro_column.php";
		die();
	}

	/**
	 * Формирует HTML конструкцию для вывода картинки поста в общей таблице постов
	 *
	 * @param $post_id
	 * @param $thumb_id
	 *
	 * @return string HTML конструкция готовая для вывода
	 */
	public function apt_getThumbHtml( $post_id, $thumb_id ) {
		$imgTag = get_the_post_thumbnail( $post_id, [ 100, 0 ], [ 'class' => 'img' ] );
		if ( empty( $imgTag ) ) {
			$imgTag = __( 'No image', 'apt' );
		}

		$title      = __( 'Change featured image', 'apt' );
		$wpnonce    = wp_create_nonce( 'set_post_thumbnail-' . $post_id );
		$ajaxloader = WAPT_PLUGIN_URL . "/admin/assets/img/ajax-loader.gif";
		$content    = "";
		$html       = "<a title='{$title}' href='#' class='modal-init-js' id='modal-init-js_{$post_id}' " . "onclick='return window.aptModalShow(this, {$post_id}, \"$wpnonce\");'>{$imgTag}</a>" . "<span id='loader_{$post_id}' style='display:none;'><img src='{$ajaxloader}' width='100px' alt=''></span>" . "<div id='post_imgs_{$post_id}' class='imgs' style='display:none;'>" . "<span style='display:none;'><img src='{$ajaxloader}' alt=''></span><div>{$content}</div></div>";

		return $html;
	}

	/**
	 * Контент подпункта меню в Медиафайлы
	 *
	 */
	public function addToMediaFromApt() {
		//media_upload_header();
		$this->is_in_medialibrary = true;
		$this->sources            = apply_filters( 'wapt/sources', $this->sources, 'add_to_media_from_apt' );
		require_once WAPT_ABSPATH . "/admin/views/media-library.php";
	}

	/**
	 * Контент вкладки
	 *
	 */
	public function media_AptTabContent() {
		media_upload_header();
		$this->sources = apply_filters( 'wapt/sources', $this->sources, 'tab_content' );
		require_once WAPT_ABSPATH . "/admin/views/media-library.php";
	}

	/**
	 * AJAX вывод содержимого вкладки сервиса
	 *
	 */
	public function source_content() {
		if ( ! wp_verify_nonce( $_POST['wpnonce'], 'apt_content' ) ) {
			die( 'Error: Invalid request.' );
		}
		$this->sources = apply_filters( 'wapt/sources', $this->sources, 'source_content' );
		if ( isset( $_POST['source'] ) && ! empty( $_POST['source'] ) ) {
			$source = str_replace( "tab-", "", sanitize_text_field( $_POST['source'] ) );

			//if( empty($this->sources[$source]) && !\WAPT_Plugin::app()->premium->is_activate() )
			if ( empty( $this->sources[ $source ] ) ) {
				require_once WAPT_PLUGIN_DIR . '/admin/views/pro.php';
			} else {
				require_once WP_PLUGIN_DIR . '/' . $this->sources[ $source ] . '/admin/views/sources/' . $source . '.php';
			}
		}
		die();
	}

	/**
	 * AJAX загрузка выбранного изображения
	 *
	 */
	public function upload_to_library() {

		if ( ! wp_verify_nonce( $_POST['wpnonce'], 'apt_api' ) ) {
			die( 'Error: Invalid request.' );
		}
		if ( isset( $_POST['is_upload'] ) ) {

			$postid = $_POST['postid'];

			// get image file
			$response = wp_remote_get( $_POST['image_url'], [ 'timeout' => 100 ] );
			if ( is_wp_error( $response ) ) {
				die( 'Error: ' . $response->get_error_message() );
			}

			$file_ext = '';
			switch ( $_POST['service'] ) {
				case 'pixabay':
					$path_info   = pathinfo( $_POST['image_url'] );
					$file_ext    = $path_info['extension'];
					$image_title = sanitize_text_field( $_POST['q'] );
					break;
				case 'unsplash':
					parse_str( parse_url( $_POST['image_url'], PHP_URL_QUERY ), $url_query );
					$file_ext = $url_query['fm'];
					if ( ! $file_ext ) {
						$file_ext = 'jpg';
					}

					$image_title = sanitize_text_field( $_POST['title'] );
					break;
				case 'google':
					$path_info = pathinfo( $_POST['image_url'] );
					$file_ext  = $path_info['extension'];
					if ( $file_ext !== 'jpg' && $file_ext !== 'jpeg' && $file_ext !== 'png' && $file_ext !== 'gif' ) {
						$file_ext = 'jpg';
					}
					if ( empty( $file_ext ) ) {
						$file_ext = 'jpg';
					}
					$image_title = sanitize_text_field( $_POST['title'] );
					break;
			}

			$file_name         = sanitize_file_name( implode( '_', explode( ' ', $_POST['q'] ) ) . '_' . time() . '.' . $file_ext );
			$wp_upload_dir     = wp_upload_dir();
			$image_upload_path = $wp_upload_dir['path'];

			if ( ! is_dir( $image_upload_path ) ) {
				if ( ! @mkdir( $image_upload_path, 0777, true ) ) {
					die( 'Error: Failed to create upload folder ' . $image_upload_path );
				}
			}

			$target_file_name = $image_upload_path . '/' . $file_name;
			$result           = @file_put_contents( $target_file_name, $response['body'] );
			unset( $response['body'] );
			if ( $result === false ) {
				die( 'Error: Failed to write file ' . $target_file_name );
			}

			// are we dealing with an image
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			if ( ! wp_read_image_metadata( $target_file_name ) ) {
				unlink( $target_file_name );
				die( 'Error: File is not an image.' );
			}

			$attachment_caption = '';

			// insert attachment
			$wp_filetype = wp_check_filetype( basename( $target_file_name ), null );
			$attachment  = [
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $target_file_name ),
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $image_title ),
				'post_status'    => 'inherit'
			];

			$attach_id = wp_insert_attachment( $attachment, $target_file_name, $postid );
			if ( $attach_id == 0 ) {
				die( 'Error: File attachment error' );
			}

			$attach_data = wp_generate_attachment_metadata( $attach_id, $target_file_name );
			$result      = wp_update_attachment_metadata( $attach_id, $attach_data );
			if ( ! $result ) {
				//die( 'Error: File attachment metadata error' );
			}

			$image_data                 = [];
			$image_data['ID']           = $attach_id;
			$image_data['post_excerpt'] = $_POST['excerpt'];
			wp_update_post( $image_data );

			echo $attach_id;

			exit;
		}
	}

	/**
	 * AJAX загрузка шрифта
	 *
	 */
	public function upload_font() {
		if ( ! wp_verify_nonce( $_POST['wpnonce'], 'wapt_upload_font' ) ) {
			die( 'Error: Invalid request.' );
		}

		if ( isset( $_POST['is_font_upload'] ) && count( $_FILES ) > 0 ) {
			$file       = $_FILES[0];
			$upload_dir = wp_upload_dir();
			$upload_dir = $upload_dir['basedir'] . "/apt_fonts";
			if ( ! is_dir( $upload_dir ) ) {
				mkdir( $upload_dir, 0777 );
			}

			$done_files = array();
			$file_name  = $file['name'];

			// Проверка, что файл является шрифтом TrueType
			$header = file_get_contents( $file['tmp_name'], false, null, null, 4 );
			if ( $header !== "\x00\x01\x00\x00" && $header !== "true" && $header !== "typ1" ) {
				die( json_encode( array( 'error' => "The uploaded file is not a TrueType font" ) ) );
			}
			//-----
			$path = pathinfo( $file['tmp_name'] );
			if ( $path['extension'] == 'php' || $path['extension'] == 'js' ) {
				die( json_encode( array( 'error' => "The uploaded file is not a TrueType font." ) ) );
			}

			if ( move_uploaded_file( $file['tmp_name'], "$upload_dir/$file_name" ) ) {
				if ( realpath( "$upload_dir/$file_name" ) ) {
					$data = array( 'files' => $file );
				} else {
					$data = array( 'error' => "Unable to copy the file to the font folder: $upload_dir" );
				}
			}

			die( json_encode( $data ) );
		}
	}

	/**
	 * AJAX загрузка google
	 *
	 */
	public function apt_api_google() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'apt_api' ) ) {
			die( 'Error: Invalid request.' );
		}
		if ( isset( $_POST['query'] ) ) {
			if ( isset( $_POST['page'] ) ) {
				$page = $_POST['page'];
			} else {
				$page = 1;
			}

			$post_title = '';
			if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
				$post = get_post( (int) $_POST['post_id'] );
				if ( is_object( $post ) ) {
					$post_title = $post->post_title;
				}
			}

			if ( isset( $_POST['watson'] ) ) {
				$query = isset( $_POST['query'] ) && ! empty( $_POST['query'] ) && (bool) (int) $_POST['watson'] ? $_POST['query'] : $post_title;
			} else {
				$query = isset( $_POST['query'] ) ? $_POST['query'] : '';
			}

			try {
				$response = ( new GoogleImages() )->search( $query, $query == $post_title ? $page + 1 : $page );

				if ( isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
					$response->limit( (int) $_POST['limit'] );
				}

				if ( ! $response->is_error() && isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
					$post = get_post( (int) $_POST['post_id'] );
					if ( $post ) {
						$response2 = ( new GoogleImages() )->search( $post->post_title, $page );

						if ( isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
							$response2->limit( (int) $_POST['limit'] );
						}

						$response->images = array_merge( $response2->images, $response->images );
					}
				}
			} catch ( Exception $e ) {
				die( $e->getMessage() );
			}

			if ( $response->is_error() ) {
				wp_send_json_error( $response );
			}

			wp_send_json_success( $response );
		}
	}

	/**
	 * Проверка API ключей
	 *
	 */
	public function apt_check_api_key() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'check-api-key' ) ) {
			die( 'Error: Invalid request.' );
		}
		if ( isset( $_POST['provider'] ) && isset( $_POST['key'] ) && isset( $_POST['key2'] ) ) {
			$provider = $_POST['provider'];
			$key      = $_POST['key'];
			$cx       = $_POST['key2'];
			switch ( $provider ) {
				case "google":
					$url = "https://www.googleapis.com/customsearch/v1?q=cat&key={$key}&cx={$cx}";

					$response = wp_remote_get( $url, [ 'timeout' => 100 ] );
					if ( is_wp_error( $response ) ) {
						die( 'Error: ' . $response->get_error_message() );
					}
					$result = json_decode( $response['body'] );
					echo ! isset( $result->error->errors ) ? true : false;
					break;
			}
			exit;
		}
	}

	public function check_api_notice( $notices, $plugin_name ) {
		// Если экшен вызывал не этот плагин, то не выводим это уведомления
		if ( $plugin_name != \WAPT_Plugin::app()->getPluginName() ) {
			return $notices;
		}
		// Получаем заголовок плагина
		$plugin_title = \WAPT_Plugin::app()->getPluginTitle();

		if ( ! \WAPT_Plugin::app()->getOption( 'google_apikey' ) && ! \WAPT_Plugin::app()->getOption( 'google_cse' ) ) {
			// Задаем текст уведомления
			$notice_text = '<p><b>' . $plugin_title . ':</b> <br>' . sprintf( __( "To download images from Google, specify Google API keys in the <a href='%s'>settings</a>.", 'apt' ), admin_url( 'admin.php?page=wapt_settings-wbcr_apt' ) ) . "</p>";

			// Задаем настройки уведомления
			$notices[] = [
				'id'              => 'apt_check_api',
				//error, success, warning
				'type'            => 'warning',
				'dismissible'     => true,
				// На каких страницах показывать уведомление ('plugins', 'dashboard', 'edit')
				'where'           => array( 'plugins', 'dashboard', 'edit' ),
				// Через какое время уведомление снова появится?
				'dismiss_expires' => 0,
				'text'            => $notice_text,
				'classes'         => array()
			];
		}

		return $notices;
	}

	/**
	 * Получение списка шрифтов из папок
	 *
	 * @return array
	 */
	public static function get_fonts() {
		$upload_dir       = wp_upload_dir();
		$upload_dir_fonts = $upload_dir['basedir'] . "/apt_fonts";
		$plugin_dir_fonts = WAPT_PLUGIN_DIR . "/fonts";
		$fonts            = array();

		$fonts[] = array( 'title' => __( 'Standard', 'apt' ), 'type' => 'group' );
		$files   = scandir( $plugin_dir_fonts );
		foreach ( $files as $file ) {
			if ( $file == '.' || $file == '..' ) {
				continue;
			}
			$name    = pathinfo( $plugin_dir_fonts . '/' . $file );
			$name    = $name['filename'];
			$fonts[] = array( 'value' => $file, 'title' => $name );
		}

		if ( is_dir( $upload_dir_fonts ) ) {
			$files = scandir( $upload_dir_fonts );
		}
		if ( count( $files ) && \WAPT_Plugin::app()->is_premium() ) {
			$fonts[] = array( 'title' => __( 'Uploaded', 'apt' ), 'type' => 'group' );
			foreach ( $files as $file ) {
				if ( $file == '.' || $file == '..' ) {
					continue;
				}
				$name    = pathinfo( $upload_dir_fonts . '/' . $file );
				$name    = $name['filename'];
				$fonts[] = array( 'value' => $file, 'title' => $name );
			}
		}

		return $fonts;

	}

	/**
	 * Генерация изображения с текстом.
	 * Если $pathToSave задан, то файл сохранится по этому пути.
	 *
	 * @param string $text
	 * @param string $pathToSave
	 * @param string $format
	 * @param int $width
	 * @param int $height
	 *
	 * @return Image
	 */
	public static function generate_image_with_text( $text, $pathToSave = '', $format = 'jpg', $width = 0, $height = 0 ) {
		$font       = WAPT_PLUGIN_DIR . "/fonts/Arial.ttf";
		$font_size  = \WAPT_Plugin::app()->getOption( 'font-size', 25 );
		$font_color = \WAPT_Plugin::app()->getOption( 'font-color', "#ffffff" );
		if ( $width == 0 ) {
			$width = (int) \WAPT_Plugin::app()->getOption( 'image-width', 800 );
		}
		if ( $height == 0 ) {
			$height = (int) \WAPT_Plugin::app()->getOption( 'image-height', 600 );
		}
		$before_text = '';
		$after_text  = '';
		$shadow      = \WAPT_Plugin::app()->getOption( 'shadow', 0 );
		if ( ! $shadow ) {
			$shadow_color = '';
		} else {
			$shadow_color = \WAPT_Plugin::app()->getOption( 'shadow-color', "#ffffff" );
		}

		$background_type = "color";
		$background      = \WAPT_Plugin::app()->getOption( 'background-color', "#ff6262" );

		$text_transform = \WAPT_Plugin::app()->getOption( 'text-transform', "no" );
		switch ( $text_transform ) {
			case 'upper':
				$text = mb_strtoupper( $text );
				break;
			case 'lower':
				$text = mb_strtolower( $text );
				break;
		}

		$text_crop = \WAPT_Plugin::app()->getOption( 'text-crop', 100 );
		if ( $text_crop > 0 ) {
			if ( strlen( $text ) > $text_crop ) {
				$temp = substr( $text, 0, $text_crop );
				$text = substr( $temp, 0, strrpos( $temp, ' ' ) );
			}

		}

		$align        = 'center';
		$valign       = 'center';
		$padding_tb   = 15;
		$padding_lr   = 15;
		$line_spacing = \WAPT_Plugin::app()->getOption( 'text-line-spacing', 1.5 );

		$params        = array(
			'text'       => $text,
			'pathToSave' => $pathToSave,
			'format'     => $format,
			'width'      => $width,
			'height'     => $height,
		);
		$image         = new Image( $width, $height, $background, $font, $font_size, $font_color );
		$image->params = $params;
		$image->setPadding( $padding_lr, $padding_tb );
		$image->write_text( $before_text . $text . $after_text, '', '', '', $align, $valign, $line_spacing, $shadow_color );
		if ( ! empty( $pathToSave ) ) {
			$image->save( $pathToSave, 100, $format );
		}

		return $image;

	}

	public function find_from_text_category( $post_id ) {
		$post = get_post( $post_id );

		$response = ( new \WAPT_IBMWatson( strip_tags( $post->post_content ) ) )->categories()->analyze();


	}

	/**
	 * Генерация изображения с текстом.
	 *
	 * @param integer $post_id
	 *
	 * @return integer $thumb_id
	 */
	public function generate_and_attachment( $post_id ) {
		$this->plugin->logger->info( "Start generate attachment for post ID = {$post_id}" );

		$format = \WAPT_Plugin::app()->getOption( "image-type", "jpg" );
		switch ( $format ) {
			case 'png':
				$extension = 'png';
				$mime_type = "image/png";
				break;
			case 'jpg':
			case 'jpeg':
			default:
				$extension = 'jpg';
				$mime_type = "image/jpeg";
				break;
		}
		$post    = get_post( $post_id, 'OBJECT' );
		$uploads = wp_upload_dir( current_time( 'mysql' ) );
		$title   = apply_filters( 'wapt/generate/title', $post->post_title, $post_id );

		// Generate unique file name
		$slug      = wp_unique_post_slug( sanitize_title( $title ), $post->ID, $post->post_status, $post->post_type, $post->post_parent );
		$filename  = wp_unique_filename( $uploads['path'], "{$slug}_{$post_id}.{$extension}" );
		$file_path = "{$uploads['path']}/{$filename}";

		$this->plugin->logger->info( "Generated file path = {$file_path}" );

		// Move the file to the uploads dir
		$image = apply_filters( 'wapt/generate/image', false, $title, $uploads['path'] . "/$filename", $extension );
		if ( ! $image ) {
			$image = apply_filters( 'wapt/generate/image', $this->generate_image_with_text( $title, $uploads['path'] . "/$filename", $extension ), $title, $uploads['path'] . "/$filename", $extension );
		}

		$thumb_id = self::insert_attachment( $post, $file_path, $mime_type );

		if ( ! is_wp_error( $thumb_id ) ) {
			$this->plugin->logger->info( "Successful generate attachment ID = {$thumb_id}" );
			$this->plugin->logger->info( "End generate attachment for post ID = {$post_id}" );

			return $thumb_id;
		} else {
			$this->plugin->logger->error( "Error generate attachment: " . var_export( $thumb_id, true ) );
		}

		return 0;
	}

	/**
	 * Insert WP attachment
	 *
	 * @param \WP_Post|int $post
	 * @param string $file_path
	 * @param string $mime_type
	 *
	 * @return int|WP_Error
	 */
	public static function insert_attachment( $post, $file_path, $mime_type = '' ) {
		if ( is_int( $post ) ) {
			$post = get_post( $post, 'OBJECT' );
		}

		if ( ! $post ) {
			return new WP_Error( 'apt_attachment', 'Post not found (insert_attachment)' );
		}

		if ( empty( $mime_type ) ) {
			$mime_type = wp_get_image_mime( $file_path );
			if ( ! $mime_type ) {
				$mime_type = 'image/jpeg';
			}
		}

		$file_url = str_replace( wp_get_upload_dir()['basedir'], wp_get_upload_dir()['baseurl'], $file_path );
		if ( file_exists( $file_path ) ) {
			$attachment = [
				'post_mime_type' => $mime_type,
				'guid'           => $file_url,
				'post_parent'    => $post->ID,
				'post_title'     => $post->post_title,
				'post_content'   => '',
			];

			$thumb_id = wp_insert_attachment( $attachment, $file_path, $post->ID );

			if ( ! is_wp_error( $thumb_id ) ) {
				require_once( ABSPATH . '/wp-admin/includes/image.php' );

				// Added fix by misthero as suggested
				wp_update_attachment_metadata( $thumb_id, wp_generate_attachment_metadata( $thumb_id, $file_path ) );
				update_attached_file( $thumb_id, $file_path );

				return $thumb_id;
			}
		}

		return new WP_Error( 'apt_attachment', 'File not exists (insert_attachment)' );
	}
}
