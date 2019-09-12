<?php

/**
 * Class AutoPostThumbnails
 *
 * @author        Alexander Teshabaev <sasha.tesh@gmail.com>
 * @copyright (c) 2018, Webcraftic Ltd
 */
class AutoPostThumbnails {

	/**
	 * @var AutoPostThumbnails
	 */
	public static $instance;

	/**
	 * После какой по счёту колонки вставлять новую (если 0, то в самом начале)
	 *
	 * @var AutoPostThumbnails
	 */
	public $numberOfColumn;

	/**
	 * После какой по счёту колонки вставлять новую (если 0, то в самом начале)
	 *
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

	/**
	 * AutoPostThumbnails constructor.
	 */
	public function __construct() {
		$this->numberOfColumn = 4;

		$this->sources = [
			'google'   => WAPT_PLUGIN_SLUG,
			'pixabay'  => '',
			'unsplash' => '',
		];

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
		add_action( 'admin_menu', [ $this, 'my_custom_submenu_page' ] );

		$apt_ag = WAPT_Plugin::app()->getOption( 'auto_generation' );

		if ( $apt_ag ) {
			add_action( 'publish_post', [ $this, 'publish_post' ], 10, 1 );

			// This hook will now handle all sort publishing including posts, custom types, scheduled posts, etc.
			add_action( 'transition_post_status', [ $this, 'check_required_transition' ], 10, 3 );
		}

		add_action( 'admin_notices', [ $this, 'check_perms' ] );
		//add_action( 'admin_menu', [ $this, 'init_admin_menu' ] );

		// Plugin hook for adding CSS and JS files required for this plugin
		add_action( 'admin_enqueue_scripts', [
			$this,
			'enqueue_assets',
		] );

		add_action( 'wp_enqueue_media', [
			$this,
			'enqueue_media',
		] );

		//Hook to adding "image" column in Posts table
		add_filter( 'manage_post_posts_columns', [ $this, 'add_image_column' ], 4 );

		//Hook to filling "image" column in Posts table
		add_action( 'manage_post_posts_custom_column', [ $this, 'fill_image_column' ], 5, 2 );

		//ADD tab and button to medialibrary
		add_filter( "media_upload_tabs", [ $this, "addTab" ] );
		add_action( "media_upload_apttab", [ $this, "aptTabHandle" ] );

		//AJAX actions
		add_action( 'wp_ajax_generatepostthumbnail', [
			$this,
			'ajax_process_post',
		] );
		add_action( 'wp_ajax_get-posts-ids', [ $this, 'get_posts_ids' ] );
		add_action( 'wp_ajax_apt_replace_thumbnail', [ $this, 'apt_replace_thumbnail' ] );
		add_action( 'wp_ajax_apt_get_thumbnail', [ $this, 'apt_get_thumbnail' ] );
		add_action( 'wp_ajax_source_content', [ $this, 'source_content' ] );
		add_action( 'wp_ajax_upload_to_library', [ $this, 'upload_to_library' ] );
	}

	/**
	 * Register the management page
	 */
	public function init_admin_menu() {

		//add_options_page(
		add_menu_page( 'Auto Post Thumbnail', 'Auto Post Thumbnail', 'manage_options', 'generate-post-thumbnails', [
			$this,
			'render'
		] );
	}

	public function enqueue_media() {
		global $post;

		if ( is_plugin_active( 'dreamstime-stock-photos/dreamstime.php' ) && ! ( isset( $_GET['action'] ) && $_GET['action'] == 'elementor' ) ) {
			wp_deregister_script( 'dreamstime-media-views' );
			wp_enqueue_script( 'dreamstime-media-views', WAPT_PLUGIN_URL . '/admin/assets/js/dreamstime-media-views.js', [ 'jquery' ], false, true );
			$handler = 'dreamstime-media-views';
		} else {
			wp_enqueue_script( 'apt-media-views', WAPT_PLUGIN_URL . '/admin/assets/js/media-views.js', [ 'jquery' ], false, true );
			$handler = 'apt-media-views';
		}

		$apt_media_iframe_src = ! empty( $post ) ? get_admin_url( get_current_blog_id(), 'media-upload.php?chromeless=1&post_id=' . $post->ID . '&tab=apttab' ) : "";
		wp_localize_script( $handler, 'apt_media_iframe', [ 'src' => esc_url( $apt_media_iframe_src ) ] );
	}

	/**
	 * Enqueue assets.
	 *
	 * @param $hook_suffix
	 *
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		//Подключаем стили и скрипты всегда в админке
		add_thickbox();
		wp_enqueue_media();
		wp_enqueue_script( 'apt-admin-script-thumbnail', WAPT_PLUGIN_URL . '/admin/assets/js/admin-thumbnail.js', [], false, true );
		if ( isset( $_REQUEST['post'] ) ) {
			$pid = $_REQUEST['post'];
		} else {
			$pid = '0';
		}
		wp_localize_script( 'apt-admin-script-thumbnail', 'apt_postid', $pid );

		$action_column_get_thumbnails = "apt_get_thumbnail";
		$action_column_get_thumbnails = apply_filters( 'wapt/get-thumbnails/action', $action_column_get_thumbnails );
		wp_localize_script( 'apt-admin-script-thumbnail', 'action_column_get_thumbnails', $action_column_get_thumbnails );

		/*
		wp_enqueue_script(
			'apt-media-views',
			plugins_url( 'admin/assets/js/media.js', __FILE__ ),
			array( 'jquery' ),
			false,
			true
		);
		*/
		/*
		//global $post;
		$aptIframeSrc = get_admin_url(get_current_blog_id(), 'media-upload.php?chromeless=1&post_id=' . $_REQUEST['post'] . '&tab=apttab');
		wp_localize_script('apt-media-views', 'aptIframeSrc', $aptIframeSrc);
		*/
		if ( is_admin() ) {
			wp_enqueue_script( 'jquery-progress', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery-ui.progressbar.min.js', [], false, true );
			wp_enqueue_script( 'jquery-autocolumnlist', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery.autocolumnlist.js', [], false, true );
			wp_enqueue_script( 'jquery-flex-images', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery.flex-images.min.js', [ 'jquery' ], false, true );
			wp_enqueue_style( 'style', WAPT_PLUGIN_URL . '/admin/assets/css/style.css' );
			wp_enqueue_style( 'flex-images', WAPT_PLUGIN_URL . '/admin/assets/css/jquery.flex-images.css' );
			wp_localize_script( 'apt-admin-script-thumbnail', 'apt_thumb', [
				'button_text' => __( 'Use as thumbnail', 'apt' ),
				'modal_title' => __( 'Change featured image', 'apt' ),
			] );
			wp_enqueue_style( 'jquery-ui-genpostthumbs', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery-ui.min.css', [], '1.7.2' );
			//wp_enqueue_style( 'jquery-ui-genpostthumbs', plugins_url( 'admin/assets/jquery-ui/redmond/jquery-ui-1.7.2.custom.css', __FILE__ ), array(), '1.7.2' );
		}

		//-----------------------------------
		if ( 'settings_page_generate-post-thumbnails' != $hook_suffix ) {
			return;
		}
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

		global $wpdb;

		// Get id's of all the published posts for which post thumbnails does not exist.
		$query = $this->get_posts_query();
		$posts = $wpdb->get_results( $query );

		if ( ! empty( $posts ) ) {
			// Generate the list of IDs
			$ids = [];
			foreach ( $posts as $post ) {
				$ids[] = $post->ID;
			}
			$ids = implode( ',', $ids );
			echo $ids;
		} else {
			echo "0";
			//esc_html_e( 'Currently there are no published posts available to generate thumbnails.', 'apt' );
		}
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
				die( '-1' );
			}

			set_time_limit( 60 );

			// Pass on the id to our 'publish' callback function.
			echo (int) $this->publish_post( $id );

			die( - 1 );
		}
		die( - 1 );
	}

	/**
	 * Check whether the required directory structure is available so that the plugin can create thumbnails if needed.
	 * If not, don't allow plugin activation.
	 */
	public function check_perms() {
		$uploads = wp_upload_dir( current_time( 'mysql' ) );

		if ( $uploads['error'] ) {
			echo '<div class="updated"><p>';
			echo $uploads['error'];

			if ( function_exists( 'deactivate_plugins' ) ) {
				deactivate_plugins( 'auto-post-thumbnail/auto-post-thumbnail.php', 'auto-post-thumbnail.php' );
				echo '<br /> ' . esc_html__( 'This plugin has been automatically deactivated.', 'apt' );
			}

			echo '</p></div>';
		}
	}

	/**
	 * Function to check whether scheduled post is being published. If so, apt_publish_post should be called.
	 *
	 * @param         $new_status
	 * @param         $old_status
	 * @param WP_Post $post   Instance of post.
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
	 * @return string
	 */
	public function get_posts_query() {
		global $wpdb;

		return "SELECT * FROM {$wpdb->posts} p WHERE p.post_status = 'publish' AND p.post_type = 'post' AND (
        p.ID NOT IN (
			SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_thumbnail_id', 'skip_post_thumb')
		) OR
		NOT EXISTS (SELECT p2.ID FROM {$wpdb->posts} p2 WHERE p2.ID = (SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND post_id = p.ID) AND p2.post_type = 'attachment')
	)";
	}

	/**
	 * Get an array of images url, contained in the post
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_images_from_post( $post_id ) {
		$post = get_post( $post_id );

		// Initialize variable used to store list of matched images as per provided regular expression
		$matches = [];
		$images  = [];

		// Get all images from post's body
		preg_match_all( '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*).*?>/i', $post->post_content, $matches );

		if ( count( $matches ) ) {
			foreach ( $matches[0] as $key => $image ) {
				// Make sure to assign correct title to the image. Extract it from img tag
				preg_match_all( '/<\s*img [^\>]*title\s*=\s*[\""\']?([^\""\'>]*)/i', $image, $matchesTitle );

				if ( count( $matchesTitle ) && isset( $matchesTitle[1] ) && isset( $matchesTitle[1][ $key ] ) ) {
					$images['titles'][] = $matches[1][ $key ];
				}

				$images['tags'][] = htmlspecialchars( $image );
				$images['urls'][] = $matches[1][ $key ];
			}
		}

		return $images;
	}

	/**
	 * Get thumbnail id for image
	 *
	 * @param       $post_id
	 * @param       $image
	 * @param       $key
	 * @param array $images_urls
	 *
	 * @return bool|int
	 */
	public function get_thumbnail_id( $image, $key ) {
		global $wpdb;

		/**
		 * If the image is from the WordPress own media gallery, then it appends the thumbnail id to a css class.
		 * Look for this id in the IMG tag.
		 */
		preg_match( '/wp-image-([\d]*)/i', $image, $thumb_id );

		if ( $thumb_id ) {
			$thumb_id = $thumb_id[1];
		}

		if ( ! get_post( $thumb_id ) ) {
			$thumb_id = false;
		}

		// If thumb id is not found, try to look for the image in DB. Thanks to "Erwin Vrolijk" for providing this code.
		if ( ! $thumb_id ) {
			$image  = substr( $image, strpos( $image, '"' ) + 1 );
			$result = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE guid = '" . $image . "'" );
			if ( $result ) {
				$thumb_id = $result[0]->ID;
			}
		}

		// Still no id found? Try found by post_name
		if ( ! $thumb_id ) {
			if ( isset( $images['tags'][ $key ] ) && ! empty( $images['tags'][ $key ] ) ) {
				$image_url = trim( $images['tags'][ $key ] );
				$_parts    = explode( '/', $image_url );
				$image_url = array_pop( $_parts );
				$_parts    = explode( '.', $image_url );
				$image_url = array_shift( $_parts );

				if ( $image_url ) {
					$result = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_name = '" . $image_url . "' AND post_type = 'attachment'" );
					if ( $result ) {
						$thumb_id = $result[0]->ID;
					}
				}
			}
		}

		return is_numeric( $thumb_id ) ? $thumb_id : false;
	}

	/**
	 * Function to save first image in post as post thumbnail.
	 *
	 * @param int $post_id   Post ID.
	 *
	 * @return int
	 */
	public function publish_post( $post_id ) {
		global $wpdb;

		// First check whether Post Thumbnail is already set for this post.
		$_thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
		if ( $_thumbnail_id && $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE id = '$_thumbnail_id' AND post_type = 'attachment'" ) || get_post_meta( $post_id, 'skip_post_thumb', true ) ) {
			return 0;
		}

		$thumb_id = 0;

		$images = $this->get_images_from_post( $post_id );
		if ( isset( $images['tags'] ) && count( $images['tags'] ) ) {
			foreach ( $images['tags'] as $key => $image ) {
				$thumb_id = $this->get_thumbnail_id( $image, $key );
				// If we succeed in generating thumb, let's update post meta
				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );

					return $thumb_id;
				} else {
					$thumb_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '" . $images['urls'][ $key ] . "'" );
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );

					return $thumb_id ? $thumb_id : 0;
				}
			}
		}

		return $thumb_id;
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
	 * @param array  $input_array
	 * @param string $search_value
	 * @param bool   $case_sensitive
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
	 * @param $matches
	 * @param $key
	 * @param $post_content
	 * @param $post_id
	 *
	 * @return int|WP_Error|null
	 */
	public function generate_post_thumb( $matches, $titles, $key, $post_id ) {
		// Get the URL now for further processing
		//$imageUrl = $matches[1][ $key ];
		$imageUrl = $matches[ $key ];
		if ( ! empty( $titles ) ) {
			$imageTitle = $titles[ $key ];
		} else {
			$imageTitle = '';
		}

		// Get the file name
		$filename = substr( $imageUrl, ( strrpos( $imageUrl, '/' ) ) + 1 );

		if ( ! ( ( $uploads = wp_upload_dir( current_time( 'mysql' ) ) ) && false === $uploads['error'] ) ) {
			return null;
		}

		// Generate unique file name
		$filename = wp_unique_filename( $uploads['path'], $filename );

		// Move the file to the uploads dir
		$new_file = $uploads['path'] . "/$filename";

		if ( ! ini_get( 'allow_url_fopen' ) ) {
			$file_data = $this->curl_get_file_contents( $imageUrl );
		} else {
			$file_data = @file_get_contents( $imageUrl );
		}

		if ( ! $file_data ) {
			return null;
		}

		//Fix for checking file extensions
		$exts = explode( ".", $filename );
		if ( count( $exts ) > 2 ) {
			return null;
		}

		$allowed = get_allowed_mime_types();
		$ext     = pathinfo( $new_file, PATHINFO_EXTENSION );
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
		$contents = curl_exec( $c );
		curl_close( $c );

		if ( $contents ) {
			return $contents;
		}

		return false;
	}

	/**
	 * Function for adding "image" column in Posts table
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_image_column( $columns ) {
		$new_columns = [
			'apt-image' => __( 'Image', 'apt' ) . ' <sup class="wapt-sup-pro">(PRO)<sup>',
		];

		return array_slice( $columns, 0, $this->numberOfColumn ) + $new_columns + array_slice( $columns, $this->numberOfColumn );
	}

	/**
	 * Function to filling "image" column in Posts table
	 *
	 * @param string $colname
	 * @param int    $post_id
	 */
	public function fill_image_column( $colname, $post_id ) {
		if ( $colname === 'apt-image' ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			//$this->nonce = wp_create_nonce( 'set_post_thumbnail-' . $post_id );
			echo $this->apt_getThumbHtml( $post_id, $thumb_id );
		}
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
				} else {
					if ( isset( $_POST['image'] ) && ! empty( $_POST['image'] ) ) {
						$thumb_id = $this->generate_post_thumb( [ 0 => $_POST['image'] ], [], 0, $post_id );
					}
				}

				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
				} else {
					global $wpdb;
					$thumb_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '" . $_POST['image'] . "'" );
					if ( $thumb_id ) {
						update_post_meta( $post_id, '_thumbnail_id', $thumb_id );
					} else {
						$thumb_id = 0;
					}
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
		$html       = "<a title='{$title}' href='#' class='modal-init-js' id='modal-init-js_{$post_id}' " . "onclick='return window.aptModalShow(this, {$post_id}, \"$wpnonce\");'>{$imgTag}</a>" . "<span id='loader_{$post_id}' style='display:none;'><img src='{$ajaxloader}' width='100px' alt=''></span>" . "<div id='post_imgs_{$post_id}' class='imgs' style='display:none;'>" . "<span style='display:none;'><img src='{$ajaxloader}' alt=''></span><p>{$content}</p></div>";

		return $html;
	}

	/**
	 * Add subpage to media menu
	 *
	 * @param $hook_suffix
	 *
	 * @return void
	 */
	public function my_custom_submenu_page() {
		add_media_page( __( 'Auto Post Thumbnails', 'apt' ), __( 'Add from APT', 'apt' ), 'manage_options', 'menu-media-apt', [
			$this,
			'addToMediaFromApt'
		] );
	}

	/**
	 * Контент подпункта меню в Медиафайлы
	 *
	 */
	public function addToMediaFromApt() {
		//media_upload_header();
		$this->is_in_medialibrary = true;
		$this->sources            = apply_filters( 'wapt/sources', $this->sources );
		require_once WAPT_ABSPATH . "/admin/views/media-library.php";
	}


	/**
	 * Добавляет вкладку в медиабиблиотеку
	 *
	 * @param $tabs
	 *
	 * @return array
	 */
	public function addTab( $tabs ) {
		$tabs['apttab'] = __( "Auto Post Thumbnail", "apt" );

		return ( $tabs );
	}

	/**
	 * Обработчик вывода во вкладку
	 *
	 */
	public function aptTabHandle() {
		// wp_iframe() adds css for "media" when callback function has "media_" as prefix
		wp_iframe( [ $this, "media_AptTabContent" ] );
	}

	/**
	 * Контент вкладки
	 *
	 */
	public function media_AptTabContent() {
		media_upload_header();
		$this->sources = apply_filters( 'wapt/sources', $this->sources );
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
		$this->sources = apply_filters( 'wapt/sources', $this->sources );
		if ( isset( $_POST['source'] ) && ! empty( $_POST['source'] ) ) {
			$source = str_replace( "tab-", "", sanitize_text_field( $_POST['source'] ) );

			//if( empty($this->sources[$source]) && !WAPT_Plugin::app()->premium->is_activate() )
			if ( empty( $this->sources[ $source ] ) ) {
				require_once WAPT_PLUGIN_DIR . '/admin/views/pro.php';
			} else {
				require_once WP_PLUGIN_DIR . '/' . $this->sources[ $source ] . '/admin/views/sources/' . $source . '.php';
			}
		}
		die();
	}

	/**
	 * AJAX загрузка выбраного изображения
	 *
	 */
	public function upload_to_library() {

		if ( ! wp_verify_nonce( $_POST['wpnonce'], 'apt_upload' ) ) {
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
					$file_ext    = $url_query['fm'];
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
			if ( $result === false ) {
				die( 'Error: File attachment metadata error' );
			}

			$image_data                 = [];
			$image_data['ID']           = $attach_id;
			$image_data['post_excerpt'] = $_POST['excerpt'];
			wp_update_post( $image_data );

			echo $attach_id;

			exit;
		}
	}
}
