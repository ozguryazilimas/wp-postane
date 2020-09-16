<?php

/**
 * Class AutoPostThumbnails
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>, Github: https://github.com/temyk
 * @copyright (c) 2019, Webcraftic Ltd
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
			'google'    => WAPT_PLUGIN_SLUG,
			'recommend' => '',
			'pixabay'   => '',
			'unsplash'  => '',
		];
		if ( WAPT_Plugin::app()->is_premium() ) {
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
		add_action( 'admin_init', [ $this, 'redirect_to_about_page' ] );

		add_action( 'admin_menu', [ $this, 'my_custom_submenu_page' ] );

		$apt_ag = WAPT_Plugin::app()->getOption( 'auto_generation' );

		if ( $apt_ag ) {
			//add_action( 'publish_post', [ $this, 'publish_post' ], 10, 1 );
			add_action( 'save_post', [ $this, 'publish_post' ], 10, 1 );
			// This hook handle update post via rest api. for example Wordpress mobile apps
			add_action( 'rest_api_inserted_post', [ $this, 'publish_post' ], 10, 1 );
			// This hook will now handle all sort publishing including posts, custom types, scheduled posts, etc.
			add_action( 'transition_post_status', [ $this, 'check_required_transition' ], 10, 3 );
		} else {
			if ( WAPT_Plugin::app()->getOption( 'auto_generation_notice', 1 ) ) {
				add_action( 'admin_notices', [ $this, 'notice_auto_generation' ] );
			}
		}

		add_action( 'admin_notices', [ $this, 'check_perms' ] );
		add_action( 'wbcr/factory/admin_notices', [ $this, 'check_api_notice' ], 10, 2 );
		add_action( 'wbcr/factory/admin_notices', [ $this, 'show_about_notice' ], 10, 2 );
		//add_action( 'admin_menu', [ $this, 'init_admin_menu' ] );

		// Plugin hook for adding CSS and JS files required for this plugin
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets', ] );

		add_action( 'wp_enqueue_media', [ $this, 'enqueue_media', ] );

		//Hook to adding "image" column in Posts table
		add_filter( 'manage_post_posts_columns', [ $this, 'add_image_column' ], 4 );

		//Hook to filling "image" column in Posts table
		add_action( 'manage_post_posts_custom_column', [ $this, 'fill_image_column' ], 5, 2 );

		//ADD tab and button to medialibrary
		add_filter( "media_upload_tabs", [ $this, "addTab" ] );
		add_action( "media_upload_apttab", [ $this, "aptTabHandle" ] );

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

	/**
	 * Register the management page
	 */
	public function init_admin_menu() {

		//add_options_page(
		add_menu_page( 'Auto Featured Image', 'Auto Featured Image', 'manage_options', 'generate-post-thumbnails', [
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

		if ( is_admin() ) {
			wp_enqueue_script( 'jquery-autocolumnlist', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery.autocolumnlist.js', [], false, true );
			wp_enqueue_script( 'jquery-flex-images', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery.flex-images.min.js', [ 'jquery' ], false, true );
			wp_enqueue_style( 'style', WAPT_PLUGIN_URL . '/admin/assets/css/style.css' );
			wp_enqueue_style( 'flex-images', WAPT_PLUGIN_URL . '/admin/assets/css/jquery.flex-images.css' );
			wp_localize_script( 'apt-admin-script-thumbnail', 'apt_thumb', [
				'button_text' => __( 'Use as thumbnail', 'apt' ),
				'modal_title' => __( 'Change featured image', 'apt' ),
			] );

		}

		wp_enqueue_script( 'apt-admin-check_api', WAPT_PLUGIN_URL . '/admin/assets/js/check-api.js', array(), false, true );

		//-----------------------------------
		if ( 'settings_page_generate-post-thumbnails' != $hook_suffix ) {
			return;
		}
	}

	/**
	 * Этот хук реализует условную логику, при которой пользователь переодически будет
	 * видет страницу "О плагине", а конкретно при активации и обновлении плагина.
	 */
	public function redirect_to_about_page() {
		$plugin = WAPT_Plugin::app();

		// If the user has updated the plugin or activated it for the first time,
		// you need to show the page "What's new?"
		if ( ! $plugin->isNetworkAdmin() ) {
			$about_page_viewed = $plugin->request->get( 'wapt_about_page_viewed', null );
			$need_show_about   = get_option( $plugin->getOptionName( 'whats_new_v360' ) );
			if ( is_null( $about_page_viewed ) ) {
				if ( $need_show_about && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
					try {
						$redirect_url = '';
						if ( class_exists( 'Wbcr_FactoryPages432' ) ) {
							$redirect_url = admin_url( "admin.php?page=wapt_about-wbcr_apt&wapt_about_page_viewed=1" );
						}
						if ( $redirect_url ) {
							wp_safe_redirect( $redirect_url );
							die();
						}
					} catch ( Exception $e ) {
					}
				}
			} else {
				if ( $need_show_about && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
					delete_option( $plugin->getOptionName( 'whats_new_v360' ) );
				}
			}
		}
	}

	/**
	 * Метод проверяет активацию премиум плагина и наличие действующего лицензионнного ключа
	 */
	public function is_premium() {
		return WAPT_Plugin::app()->is_premium();
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

		$generate = WAPT_Plugin::app()->getOption( "generate_autoimage", 'find' );
		if ( $generate == 'find' ) {
			$auto_generate = false;
		} else if ( $generate == 'generate' || $generate == 'both' ) {
			$auto_generate = true;
		} else {
			$auto_generate = false;
		}


		$has_thumb = (bool) $_POST['withThumb'];
		$type      = $_POST['posttype'];
		if ( auto_post_thumbnails()->is_premium() ) {
			$status     = $_POST['poststatus'];
			$category   = $_POST['category'];
			$date_start = $_POST['date_start'] ? DateTime::createFromFormat( 'd.m.Y', $_POST['date_start'] )->format( 'd.m.Y' ) : 0;
			$date_end   = $_POST['date_end'] ? DateTime::createFromFormat( 'd.m.Y', $_POST['date_end'] )->format( 'd.m.Y' ) : 0;
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
				//если запрошены посты без тамбнеила, значит пользователь хочет сгенерировать их
				if ( ! $has_thumb ) {
					$images = $this->get_images_from_post( $post->ID );
					if ( ( isset( $images['urls'] ) && count( $images['urls'] ) ) || $auto_generate ) {
						$ids[] = $post->ID;
					}
				} else //иначе он хочет удалить тамбнэйлы
				{
					$ids[] = $post->ID;
				}
			}
			$ids = implode( ',', $ids );
			echo $ids;
		} else {
			echo "0";
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
			WAPT_Plugin::app()->updateOption( 'auto_generation_notice', 0 );
		}
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
	 * @param WP_Post $post Instance of post.
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
	public function get_posts_query( $has_thumb = false, $type = 'post', $status = 'publish', $category = 0, $date_start = 0, $date_end = 0 ) {

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
		if ( $date_start && $date_start ) {
			$args['date_query'][] = array( 'after' => $date_start, 'before' => $date_end, 'inclusive' => true, );
		}
		$query = new WP_Query( $args );

//		$query = "SELECT * FROM {$wpdb->posts} p WHERE {$q_status_type}
//        {$q_date} AND (
//        p.ID NOT IN (
//		SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_thumbnail_id', 'skip_post_thumb')
//		) OR {$q_without_thumb} EXISTS (SELECT p2.ID FROM {$wpdb->posts} p2 WHERE p2.ID = (SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND post_id = p.ID) AND p2.post_type = 'attachment'))";

		return $query;
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

		//do shortcodes before search images
		$post_content = do_shortcode( $post->post_content );

		// Get all images from post's body
		preg_match_all( '/<\s*img .*src\s*=\s*[\""\']?([^\""\'>]*).*?>/i', $post_content, $matches );

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
	 * @param string $image
	 * @param string $url
	 *
	 * @return bool|int
	 */
	public function get_thumbnail_id( $image, $url ) {
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
			//$image  = substr( $image, strpos( $image, '"' ) + 1 );
			$result = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE guid = '" . $url . "'" );
			if ( $result ) {
				$thumb_id = $result[0]->ID;
			}
		}

		// Still no id found? Try found by post_name
		if ( ! $thumb_id ) {
			if ( isset( $image ) && ! empty( $image ) ) {
				$image_url = trim( $image );
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
	 * @param int $post_id Post ID.
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

		$thumb_id  = 0;
		$autoimage = WAPT_Plugin::app()->getOption( "generate_autoimage", 'find' );
		$images    = $this->get_images_from_post( $post_id );
		if ( ( isset( $images['tags'] ) && count( $images['tags'] ) ) && $autoimage !== 'generate' ) {

			foreach ( $images['tags'] as $key => $image ) {
				$thumb_id = $this->get_thumbnail_id( $image, $images['urls'][ $key ] );
				// If we succeed in generating thumb, let's update post meta
				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );

					return $thumb_id;
				} else {
					$thumb_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '" . $images['urls'][ $key ] . "'" );
					if ( $thumb_id ) {
						update_post_meta( $post_id, '_thumbnail_id', $thumb_id );

						return $thumb_id ? $thumb_id : 0;
					} else {
						if ( auto_post_thumbnails()->is_premium() ) {
							$thumb_id = apply_filters( 'wapt/generate_post_thumb', $images['urls'][ $key ], $post_id );
						}
						if ( $thumb_id ) {
							update_post_meta( $post_id, '_thumbnail_id', $thumb_id );

							return $thumb_id;
						}
					}
				}
			}
		} else {
			// создаём свою картинку с заголовком на цветном фоне
			if ( $autoimage == 'generate' || $autoimage == 'both' ) {

				$thumb_id = $this->generate_and_attachment( $post_id );
				if ( $thumb_id ) {
					update_post_meta( $post_id, '_thumbnail_id', $thumb_id );

					return $thumb_id;
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
	 * @param $matches
	 * @param $key
	 * @param $post_content
	 * @param $post_id
	 *
	 * @return int|WP_Error|null
	 */
	public function generate_post_thumb( $image_url, $title, $post_id ) {
		// Get the URL now for further processing
		//$imageUrl = $matches[1][ $key ];
		$imageUrl   = $image_url;
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
	 * Function for adding "image" column in Posts table
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_image_column( $columns ) {
		$pro = $this->is_premium() ? '' : ' <sup class="wapt-sup-pro">(PRO)<sup>';

		$new_columns = [ 'apt-image' => __( 'Image', 'apt' ) . $pro, ];

		return array_slice( $columns, 0, $this->numberOfColumn ) + $new_columns + array_slice( $columns, $this->numberOfColumn );
	}

	/**
	 * Function to filling "image" column in Posts table
	 *
	 * @param string $colname
	 * @param int $post_id
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

					if ( $thumb_id == - 1 ) //generate image
					{
						switch ( $_POST['feature'] ) {

							case 'from_meaning':

								break;

							default:
								$thumb_id = $this->generate_and_attachment( $post_id );

						}
					}
				} else if ( isset( $_POST['image'] ) && ! empty( $_POST['image'] ) ) {
					$img = $_POST['image'];

					//Совместимость с NexGen
					$img = preg_replace( '/(thumbs\/thumbs_)/', '.', $img );

					global $wpdb;
					$thumb_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '" . $img . "'" );
					if ( ! $thumb_id ) {
						//если ссылка на миниатюру, то регулярка сделает ссылку на оригинал. убирает в конце названия файла -150x150
						$img      = preg_replace( '/-[0-9]{1,}x[0-9]{1,}\./', '.', $img );
						$thumb_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '" . $img . "'" );
					}
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
		add_media_page( __( 'Auto Featured Images', 'apt' ), __( 'Add from APT', 'apt' ), 'manage_options', 'menu-media-apt', [
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
		$this->sources            = apply_filters( 'wapt/sources', $this->sources, 'add_to_media_from_apt' );
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
		$tabs['apttab'] = __( "Auto Featured Image", "apt" );

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
				$response = ( new WAPT_GoogleImages() )->search( $query, $query == $post_title ? $page + 1 : $page );

				if ( isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
					$response->limit( (int) $_POST['limit'] );
				}

				if ( ! $response->is_error() && isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
					$post = get_post( (int) $_POST['post_id'] );
					if ( $post ) {
						$response2 = ( new WAPT_GoogleImages() )->search( $post->post_title, $page );

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
		if ( $plugin_name != WAPT_Plugin::app()->getPluginName() ) {
			return $notices;
		}
		// Получаем заголовок плагина
		$plugin_title = WAPT_Plugin::app()->getPluginTitle();

		if ( ! WAPT_Plugin::app()->getOption( 'google_apikey' ) && ! WAPT_Plugin::app()->getOption( 'google_cse' ) ) {
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

	public function show_about_notice( $notices, $plugin_name ) {
		// Если экшен вызывал не этот плагин, то не выводим это уведомления
		if ( $plugin_name != WAPT_Plugin::app()->getPluginName() ) {
			return $notices;
		}
		// Получаем заголовок плагина
		$plugin_title = WAPT_Plugin::app()->getPluginTitle();

		$notice_text = '<p><b>' . $plugin_title . ':</b> ' . sprintf( __( "What's new in version 3.7.0? Find out from <a href='%s'>the article</a> on our website.", 'apt' ), 'https://cm-wp.com/auto-featured-image-from-title/' ) . "</p>";
		$notices[]   = [
			'id'              => 'apt_show_about_370',
			//error, success, warning
			'type'            => 'info',
			'dismissible'     => true,
			// На каких страницах показывать уведомление ('plugins', 'dashboard', 'edit')
			'where'           => array( 'plugins', 'dashboard', 'edit' ),
			// Через какое время уведомление снова появится?
			'dismiss_expires' => 0,
			'text'            => $notice_text,
			'classes'         => array()
		];

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
		if ( count( $files ) && AutoPostThumbnails::instance()->is_premium() ) {
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
	 * @return WAPT_Image
	 */
	public static function generate_image_with_text( $text, $pathToSave = '', $format = 'jpg', $width = 800, $height = 600 ) {
		$font        = WAPT_PLUGIN_DIR . "/fonts/Arial.ttf";
		$font_size   = WAPT_Plugin::app()->getOption( 'font-size', 25 );
		$font_color  = WAPT_Plugin::app()->getOption( 'font-color', "#ffffff" );
		$before_text = '';
		$after_text  = '';
		$shadow      = WAPT_Plugin::app()->getOption( 'shadow', 0 );
		if ( ! $shadow ) {
			$shadow_color = '';
		} else {
			$shadow_color = WAPT_Plugin::app()->getOption( 'shadow-color', "#ffffff" );
		}

		$background_type = "color";
		$background      = WAPT_Plugin::app()->getOption( 'background-color', "#ff6262" );

		$text_transform = WAPT_Plugin::app()->getOption( 'text-transform', "no" );
		switch ( $text_transform ) {
			case 'upper':
				$text = strtoupper( $text );
				break;
			case 'lower':
				$text = strtolower( $text );
				break;
		}

		$text_crop = WAPT_Plugin::app()->getOption( 'text-crop', 100 );
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
		$line_spacing = WAPT_Plugin::app()->getOption( 'text-line-spacing', 1.5 );

		$params        = array(
			'text'       => $text,
			'pathToSave' => $pathToSave,
			'format'     => $format,
			'width'      => $width,
			'height'     => $height,
		);
		$image         = new WAPT_Image( $width, $height, $background, $font, $font_size, $font_color );
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

		$response = ( new WAPT_IBMWatson( strip_tags( $post->post_content ) ) )->categories()->analyze();


	}

	/**
	 * Генерация изображения с текстом.
	 * Если $pathToSave задан, то файл сохранится по этому пути.
	 *
	 * @param integer $post_id
	 *
	 * @return integer $thumb_id
	 */
	public function generate_and_attachment( $post_id ) {
		$format = WAPT_Plugin::app()->getOption( "image-type", "jpg" );
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
		$post = get_post( $post_id, 'OBJECT' );

		$uploads = wp_upload_dir( current_time( 'mysql' ) );

		// Generate unique file name
		$filename = "wapt_image_{$post_id}.{$extension}";
		$filename = wp_unique_filename( $uploads['path'], $filename );

		// Move the file to the uploads dir
		$image = apply_filters( 'wapt/generate/image', $this->generate_image_with_text( $post->post_title, $uploads['path'] . "/$filename", $extension ), $post->post_title, $uploads['path'] . "/$filename", $extension );

		if ( file_exists( $uploads['path'] . "/$filename" ) ) {
			// Compute the URL
			$file_url  = $uploads['url'] . "/$filename";
			$file_path = $uploads['path'] . "/$filename";

			// Construct the attachment array
			$attachment = [
				'post_mime_type' => $mime_type,
				'guid'           => $file_url,
				'post_parent'    => $post_id,
				'post_title'     => $post->post_title,
				'post_content'   => '',
			];

			$thumb_id = wp_insert_attachment( $attachment, $file_path, $post_id );
			if ( ! is_wp_error( $thumb_id ) ) {
				require_once( ABSPATH . '/wp-admin/includes/image.php' );

				// Added fix by misthero as suggested
				wp_update_attachment_metadata( $thumb_id, wp_generate_attachment_metadata( $thumb_id, $file_path ) );
				update_attached_file( $thumb_id, $file_path );

				return $thumb_id;
			}
		}

		return 0;

	}
}
