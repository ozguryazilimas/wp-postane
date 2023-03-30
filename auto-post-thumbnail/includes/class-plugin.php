<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Основной класс плагина Auto Featured Image
 *
 * @version       1.0
 */
class WAPT_Plugin extends Wbcr_Factory466_Plugin {

	/**
	 * @see self::app()
	 * @var Wbcr_Factory466_Plugin
	 */
	private static $app;

	/**
	 * @var WBCR\APT\AutoPostThumbnails
	 */
	public $apt;

	/**
	 * @var integer
	 */
	public $numberOfColumn;

	/**
	 * Конструктор
	 *
	 * Применяет конструктор родительского класса и записывает экземпляр текущего класса в свойство $app.
	 * Подробнее о свойстве $app см. self::app()
	 *
	 * @param string $plugin_path
	 * @param array $data
	 *
	 * @throws Exception
	 */
	public function __construct( $plugin_path, $data ) {
		parent::__construct( $plugin_path, $data );

		self::$app = $this;
		$this->apt = \WBCR\APT\AutoPostThumbnails::instance();

		if ( is_admin() ) {
			// Регистрации класса активации/деактивации плагина
			$this->initActivation();

			$this->numberOfColumn = 4;

			require WAPT_PLUGIN_DIR . '/admin/ajax/check-license.php';

			// Инициализация бэкенда
			$this->admin_scripts();
		}

		$this->global_scripts();
	}

	/**
	 * Статический метод для быстрого доступа к интерфейсу плагина.
	 *
	 * @return Wbcr_Factory466_Plugin
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * Метод проверяет активацию премиум плагина и наличие действующего лицензионного ключа
	 *
	 * @return bool
	 */
	public function is_premium() {
		if ( $this->premium->is_active() && $this->premium->is_activate() //&& $this->premium->is_install_package()
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Регистрации класса активации/деактивации плагина
	 */
	protected function initActivation() {
		include_once WAPT_PLUGIN_DIR . '/admin/class-wapt-activation.php';
		$this->registerActivation( 'WAPT_Activation' );
	}

	/**
	 * Регистрирует классы страниц в плагине
	 */
	private function register_pages() {
		self::app()->registerPage( 'WAPT_Generate', WAPT_PLUGIN_DIR . '/admin/pages/generate.php' );
		self::app()->registerPage( 'WAPT_Settings', WAPT_PLUGIN_DIR . '/admin/pages/settings.php' );
		self::app()->registerPage( 'WAPT_ImageSettings', WAPT_PLUGIN_DIR . '/admin/pages/image.php' );
		self::app()->registerPage( 'WAPT_License', WAPT_PLUGIN_DIR . '/admin/pages/license.php' );
		self::app()->registerPage( 'WAPT_Log', WAPT_PLUGIN_DIR . '/admin/pages/log.php' );
		self::app()->registerPage( 'WAPT_About', WAPT_PLUGIN_DIR . '/admin/pages/about.php' );
	}

	/**
	 */
	private function admin_scripts() {
		//$this->register_pages();

		//------ ACTIONS ------
		add_action( 'admin_init', [ $this, 'redirect_to_about_page' ] );
		add_action( 'admin_menu', [ $this, 'my_custom_submenu_page' ] );

		add_action( 'admin_notices', [ $this, 'check_perms' ] );
		add_action( 'wbcr/factory/admin_notices', [ $this, 'show_about_notice' ], 10, 2 );

		// Plugin hook for adding CSS and JS files required for this plugin
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_enqueue_media', [ $this, 'enqueue_media' ] );

		//Hook to adding "image" column in Posts table
		add_filter( 'manage_post_posts_columns', [ $this, 'add_image_column' ], 4 );
		//Hook to filling "image" column in Posts table
		add_action( 'manage_post_posts_custom_column', [ $this, 'fill_image_column' ], 5, 2 );

		//ADD tab and button to medialibrary
		add_filter( 'media_upload_tabs', [ $this, 'addTab' ] );
		add_action( 'media_upload_apttab', [ $this, 'aptTabHandle' ] );

		// filter posts
		add_action( 'restrict_manage_posts', [ $this, 'add_posts_filters' ] );
		add_action( 'pre_get_posts', [ $this, 'posts_filter' ], 10, 1 );
		add_filter( 'views_edit-post', [ $this, 'add_filter_link' ], 10, 1 );
		// bulk actions
		add_filter( 'bulk_actions-edit-post', [ $this, 'register_bulk_action_generate' ] );
		add_filter( 'handle_bulk_actions-edit-post', [ $this, 'bulk_action_generate_handler' ], 10, 3 );
		add_action( 'admin_notices', [ $this, 'apt_bulk_action_admin_notice' ] );
		add_action( 'admin_notices', [ $this, 'update_admin_notice' ] );

		add_filter( 'plugin_action_links_' . WAPT_PLUGIN_BASENAME, [ $this, 'plugin_action_link' ] );

		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
	}

	/**
	 * Выполняет php сценарии, когда все WordPress плагины будут загружены
	 *
	 * @throws \Exception
	 * @since  1.0.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function plugins_loaded() {
		if ( is_admin() ) {
			$this->register_pages();
		}
	}

	/**
	 */
	private function global_scripts() {
		require_once WAPT_PLUGIN_DIR . '/includes/class.generate-result.php';
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
			$pid = intval( $_REQUEST['post'] );
		} else {
			$pid = 0;
		}
		$action_column_get_thumbnails = apply_filters( 'wapt/get-thumbnails/action', 'apt_get_thumbnail' );

		$localize = [
			'postid'                       => $pid,
			'action_column_get_thumbnails' => $action_column_get_thumbnails,
		];

		if ( is_admin() ) {
			wp_enqueue_script( 'jquery-autocolumnlist', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery.autocolumnlist.js', [], false, true );
			wp_enqueue_script( 'jquery-flex-images', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery.flex-images.min.js', [ 'jquery' ], false, true );
			wp_enqueue_style( 'style', WAPT_PLUGIN_URL . '/admin/assets/css/style.css' );
			wp_enqueue_style( 'flex-images', WAPT_PLUGIN_URL . '/admin/assets/css/jquery.flex-images.css' );

			$localize['button_text'] = __( 'Use as thumbnail', 'apt' );
			$localize['modal_title'] = __( 'Change featured image', 'apt' );
		}

		wp_enqueue_script( 'apt-admin-check_api', WAPT_PLUGIN_URL . '/admin/assets/js/check-api.js', [], false, true );
		wp_enqueue_script( 'apt-admin-search-page', WAPT_PLUGIN_URL . '/admin/assets/js/search-page.js', [], false, true );

		wp_localize_script( 'apt-admin-script-thumbnail', 'apt', $localize );
		//-----------------------------------
		if ( 'settings_page_generate-post-thumbnails' != $hook_suffix ) {
			return;
		}
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

		$apt_media_iframe_src = ! empty( $post ) ? get_admin_url( get_current_blog_id(), 'media-upload.php?chromeless=1&post_id=' . $post->ID . '&tab=apttab' ) : '';
		wp_localize_script( $handler, 'apt_media_iframe', [ 'src' => esc_url( $apt_media_iframe_src ) ] );
	}

	/**
	 * Этот хук реализует условную логику, при которой пользователь периодически будет
	 * видеть страницу "О плагине", а конкретно при активации и обновлении плагина.
	 */
	public function redirect_to_about_page() {
		// If the user has updated the plugin or activated it for the first time,
		// you need to show the page "What's new?"
		if ( ! $this->isNetworkAdmin() ) {
			$about_page_viewed = $this->request->get( 'wapt_about_page_viewed', null );
			$need_show_about   = get_option( $this->getOptionName( 'whats_new_v360' ) );
			if ( is_null( $about_page_viewed ) ) {
				if ( $need_show_about && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
					try {
						$redirect_url = '';
						if ( class_exists( 'Wbcr_FactoryPages466' ) ) {
							$redirect_url = admin_url( 'admin.php?page=wapt_about-wbcr_apt&wapt_about_page_viewed=1' );
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
					delete_option( $this->getOptionName( 'whats_new_v360' ) );
				}
			}
		}
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
			$this->apt,
			'addToMediaFromApt',
		] );
	}

	/**
	 * Check whether the required directory structure is available so that the plugin can create thumbnails if needed.
	 * If not, don't allow plugin activation.
	 */
	public function check_perms() {
		$uploads = wp_upload_dir( current_time( 'mysql' ) );

		if ( $uploads['error'] ) {
			echo '<div class="updated"><p>';
			echo esc_html( $uploads['error'] );

			if ( function_exists( 'deactivate_plugins' ) ) {
				deactivate_plugins( 'auto-post-thumbnail/auto-post-thumbnail.php', 'auto-post-thumbnail.php' );
				echo '<br /> ' . esc_html__( 'This plugin has been automatically deactivated.', 'apt' );
			}

			echo '</p></div>';
		}
	}

	/**
	 * Show about notice
	 *
	 * @param array $notices Notices list
	 * @param string $plugin_name Plugin name
	 *
	 * @return array
	 */
	public function show_about_notice( $notices, $plugin_name ) {
		// Если экшен вызывал не этот плагин, то не выводим это уведомления
		if ( $plugin_name !== $this->getPluginName() ) {
			return $notices;
		}
		// Получаем заголовок плагина
		$plugin_title = $this->getPluginTitle();

		$notice_text = '<p><b>' . $plugin_title . ':</b> ' . sprintf( __( "What's new in version 3.7.0? Find out from <a href='%s'>the article</a> on our website.", 'apt' ), 'https://cm-wp.com/auto-featured-image-from-title/' ) . '</p>';
		$notices[]   = [
			'id'              => 'apt_show_about_370',
			//error, success, warning
			'type'            => 'info',
			'dismissible'     => true,
			// На каких страницах показывать уведомление ('plugins', 'dashboard', 'edit')
			'where'           => [ 'plugins', 'dashboard', 'edit' ],
			// Через какое время уведомление снова появится?
			'dismiss_expires' => 0,
			'text'            => $notice_text,
			'classes'         => [],
		];

		return $notices;
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

		$new_columns = [ 'apt-image' => __( 'Image', 'apt' ) . $pro ];

		return array_slice( $columns, 0, $this->numberOfColumn ) + $new_columns + array_slice( $columns, $this->numberOfColumn );
	}

	/**
	 * Function to filling "image" column in Posts table
	 *
	 * @param string $colname
	 * @param int $post_id
	 */
	public function fill_image_column( $colname, $post_id ) {
		if ( 'apt-image' === $colname ) {
			$thumb_id = get_post_thumbnail_id( $post_id );
			echo $this->apt->apt_getThumbHtml( $post_id, $thumb_id ); // phpcs:ignore
		}
	}

	/**
	 * Добавляет вкладку в медиабиблиотеку
	 *
	 * @param $tabs
	 *
	 * @return array
	 */
	public function addTab( $tabs ) {
		$tabs['apttab'] = __( 'Auto Featured Image', 'apt' );

		return ( $tabs );
	}

	/**
	 * Обработчик вывода во вкладку
	 */
	public function aptTabHandle() {
		// wp_iframe() adds css for "media" when callback function has "media_" as prefix
		wp_iframe( [ $this->apt, 'media_AptTabContent' ] );
	}

	/**
	 * Register bulk option for posts
	 *
	 * @return array(string)
	 */
	public function register_bulk_action_generate( $bulk_actions ) {
		$bulk_actions['apt_generate_thumb'] = __( 'Generate featured image', 'apt' );
		$bulk_actions['apt_delete_thumb']   = __( 'Unset featured image', 'apt' );
		$bulk_actions['apt_add_images']     = __( 'Upload post images', 'apt' );

		return $bulk_actions;
	}

	/**
	 * Handler of bulk option for posts
	 *
	 * @return string
	 */
	public function bulk_action_generate_handler( $redirect_to, $doaction, $post_ids ) {

		foreach ( $post_ids as $post_id ) {
			switch ( $doaction ) {
				case 'apt_add_images':
					do_action( 'wapt/upload_and_replace_post_images', $post_id );
					break;
				case 'apt_generate_thumb':
					$this->apt->publish_post( $post_id );
					break;
				case 'apt_delete_thumb':
					delete_post_thumbnail( $post_id );
					break;
				default:
					return $redirect_to;
			}
		}

		$redirect_to = add_query_arg( [
			'apt_bulk_action' => count( $post_ids ),
		], $redirect_to );

		return $redirect_to;
	}

	/**
	 * Admin notice after bulk action
	 */
	public function apt_bulk_action_admin_notice() {
		if ( empty( $_GET['apt_bulk_action'] ) ) {
			return;
		}

		$data = intval( $_GET['apt_bulk_action'] );
		$msg  = __( 'Processed posts: ', 'apt' ) . $data;
		echo '<div id="message" class="updated"><p>' . wp_kses_post( $msg ) . '</p></div>';
	}

	/**
	 * Admin notice
	 */
	public function update_admin_notice() {
		if ( defined( 'WAPTP_PLUGIN_VERSION' ) && str_replace( '.', '', WAPTP_PLUGIN_VERSION ) < 130 ) {
			$msg = __( 'To use premium features, update the <b>Auto Featured Image Premium</b> plugin!', 'apt' );
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>' . wp_kses_post( $msg ) . '</p></div>';
		}
	}

	/**
	 * Add filter on the Posts list tables.
	 */
	public function add_posts_filters() {
		$screen = get_current_screen();

		if ( ! empty( $screen ) && 'post' === $screen->post_type ) {
			$apt_is_image = false;
			if ( isset( $_GET['apt_is_image'] ) ) {
				$apt_is_image = absint( $_GET['apt_is_image'] );
			}

			echo '<select name="apt_is_image"><option value="-1">' . esc_html__( 'Featured Image', 'apt' ) . '</option><option value="1" ' . selected( 1, $apt_is_image, 0 ) . '>' . esc_html__( 'With image', 'apt' ) . '</option><option value="0" ' . selected( 0, $apt_is_image, 0 ) . '>' . esc_html__( 'Without image', 'apt' ) . '</option></select>';
		}
	}

	/**
	 * Filter the Posts list tables.
	 *
	 * @param $query \WP_Query
	 */
	public function posts_filter( $query ) {
		if ( ! is_admin() ) {
			return;
		} // выходим если не админка

		// убедимся что мы на нужной странице админки
		require_once ABSPATH . 'wp-admin/includes/screen.php';
		$cs = get_current_screen();
		if ( empty( $cs->post_type ) || 'post' !== $cs->post_type || 'edit-post' !== $cs->id ) {
			return;
		}

		if ( isset( $_GET['apt_is_image'] ) && $_GET['apt_is_image'] != - 1 ) {
			if ( (int) $_GET['apt_is_image'] == 1 ) {
				$compare = 'EXISTS';
			} else {
				$compare = 'NOT EXISTS';
			}
			$query->set( 'meta_query', [
				[
					'key'     => '_thumbnail_id',
					'compare' => $compare,
				],
			] );
		}
	}

	/**
	 * Add filter on the Posts list tables.
	 */
	public function add_filter_link( $views ) {
		//$posts = $this->apt->get_posts_count( false, 'post');

		$q = add_query_arg( [
			'apt_is_image' => '0',
			'post_type'    => 'post',
		], 'edit.php' );

		//$views['apt_filter'] = '<a href="' . $q . '">' . __( 'Without featured image', 'apt' ) . '</a> (' . $posts . ')';
		$views['apt_filter'] = '<a href="' . $q . '">' . __( 'Without featured image', 'apt' ) . '</a>';
		unset( $my );

		return $views;
	}

	/**
	 * Adds the plugin action link on Plugins table
	 *
	 * @param array $links links array
	 *
	 * @return array
	 */
	public function plugin_action_link( $links ) {
		$link_generate = '<a href="' . esc_url( $this->getPluginPageUrl( $this->getPrefix() . 'generate' ) ) . '">' . esc_html__( 'Generate', 'apt' ) . '</a>';
		array_unshift( $links, $link_generate );

		return $links;
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @author matzeeable https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist
	 * @return boolean
	 */
	public function doing_rest_api() {
		$prefix     = rest_get_url_prefix();
		$rest_route = $this->request->get( 'rest_route', null );
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST // (#1)
			 || ! is_null( $rest_route ) // (#2)
				&& strpos( trim( $rest_route, '\\/' ), $prefix, 0 ) === 0 ) {
			return true;
		}

		// (#3)
		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( [] ) );

		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}
}
