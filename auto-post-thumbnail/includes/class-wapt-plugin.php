<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Основной класс плагина Auto Featured Image
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 2018 Webraftic Ltd
 * @version       1.0
 */
class WAPT_Plugin extends Wbcr_Factory433_Plugin {

	/**
	 * @see self::app()
	 * @var Wbcr_Factory433_Plugin
	 */
	private static $app;

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

		if ( is_admin() ) {
			// Регистрации класса активации/деактивации плагина
			$this->initActivation();

			require( WAPT_PLUGIN_DIR . '/admin/ajax/check-license.php' );

			// Инициализация скриптов для бэкенда
			$this->admin_scripts();
			//------ ACTIONS ------
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
		}
		$this->global_scripts();
	}

	/**
	 * Статический метод для быстрого доступа к интерфейсу плагина.
	 *
	 * Позволяет разработчику глобально получить доступ к экземпляру класса плагина в любом месте
	 * плагина, но при этом разработчик не может вносить изменения в основной класс плагина.
	 *
	 * Используется для получения настроек плагина, информации о плагине, для доступа к вспомогательным
	 * классам.
	 *
	 * @return Wbcr_Factory433_Plugin
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * Метод проверяет активацию премиум плагина и наличие действующего лицензионнного ключа
	 *
	 * @return bool
	 */
	public function is_premium() {
		if (
			$this->premium->is_active() &&
			$this->premium->is_activate()
			//&& $this->premium->is_install_package()
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
		include_once( WAPT_PLUGIN_DIR . '/admin/class-wapt-activation.php' );
		$this->registerActivation( 'WAPT_Activation' );
	}

	/**
	 * Регистрирует классы страниц в плагине
	 *
	 */
	private function register_pages() {
		self::app()->registerPage( 'WAPT_Generate', WAPT_PLUGIN_DIR . '/admin/pages/generate.php' );
		self::app()->registerPage( 'WAPT_License', WAPT_PLUGIN_DIR . '/admin/pages/license.php' );
		self::app()->registerPage( 'WAPT_Settings', WAPT_PLUGIN_DIR . '/admin/pages/settings.php' );
		self::app()->registerPage( 'WAPT_About', WAPT_PLUGIN_DIR . '/admin/pages/about.php' );
	}

	/**
	 * Код который должен инициализироваться на бэкенде
	 */
	private function admin_scripts() {

		// Регистрация страниц
		$this->register_pages();
	}

	/**
	 * Код который должен инициализироваться на бэкенде и фронтэнде
	 */
	private function global_scripts() {
		// Код который должен инициализироваться на бэкенде и фронтенде
	}

	/**
	 * Register bulk option for posts
	 *
	 * @return array(string)
	 */
	public function register_bulk_action_generate( $bulk_actions ) {
		$bulk_actions['apt_generate_thumb'] = __( 'Generate featured image', 'apt' );
		$bulk_actions['apt_delete_thumb']   = __( 'Unset featured image', 'apt' );

		return $bulk_actions;
	}

	/**
	 * Handler of bulk option for posts
	 *
	 * @return string
	 */
	public function bulk_action_generate_handler( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction !== 'apt_generate_thumb' && $doaction !== 'apt_delete_thumb' ) {
			return $redirect_to;
		}

		foreach ( $post_ids as $post_id ) {
			switch ( $doaction ) {
				case 'apt_generate_thumb':
					$thumb = auto_post_thumbnails()->publish_post( $post_id );
					break;
				case 'apt_delete_thumb':
					delete_post_thumbnail( $post_id );
					break;
			}
		}

		$redirect_to = add_query_arg(
			array(
				'apt_bulk_action' => count( $post_ids ),
			),
			$redirect_to );

		return $redirect_to;
	}

	/**
	 * Admin notice after bulk action
	 *
	 */
	public function apt_bulk_action_admin_notice() {
		if ( empty( $_GET['apt_bulk_action'] ) ) {
			return;
		}

		$data = $_GET['apt_bulk_action'];
		$msg  = __( 'Processed posts: ', 'apt' ) . intval( $data );
		echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
	}

	/**
	 * Admin notice
	 *
	 */
	public function update_admin_notice() {
		if ( defined( 'WAPTP_PLUGIN_VERSION' ) && str_replace( '.', '', WAPTP_PLUGIN_VERSION ) < 130 ) {
			$msg = __( 'To use premium features, update the <b>Auto Featured Image Premium</b> plugin!', 'apt' );
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>' . $msg . '</p></div>';
		}
	}

	/**
	 * Add filter on the Posts list tables.
	 *
	 * @param $post_type string
	 * @param $witch string
	 */
	public function add_posts_filters() {
		$screen = get_current_screen();

		if ( ! empty( $screen ) && "post" == $screen->post_type ) {
			$apt_is_image = false;
			if ( isset( $_GET['apt_is_image'] ) ) {
				$apt_is_image = $_GET['apt_is_image'];
			}

			echo '<select name="apt_is_image">' .
			     '<option value="-1">' . __( 'Featured Image', 'apt' ) . '</option>' .
			     '<option value="1" ' . selected( 1, $apt_is_image, 0 ) . '>' . __( 'With image', 'apt' ) . '</option>' .
			     '<option value="0" ' . selected( 0, $apt_is_image, 0 ) . '>' . __( 'Without image', 'apt' ) . '</option>' .
			     '</select>';
		}
	}

	/**
	 * Filter the Posts list tables.
	 *
	 * @param $query WP_Query
	 *
	 */
	public function posts_filter( $query ) {
		if ( ! is_admin() ) {
			return;
		} // выходим если не админка

		// убедимся что мы на нужной странице админки
		require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		$cs = get_current_screen();
		if ( empty( $cs->post_type ) || $cs->post_type != 'post' || $cs->id != 'edit-post' ) {
			return;
		}

		if ( isset( $_GET['apt_is_image'] ) && $_GET['apt_is_image'] != - 1 ) {
			if ( (int) $_GET['apt_is_image'] == 1 ) {
				$compare = 'EXISTS';
			} else {
				$compare = 'NOT EXISTS';
			}
			$query->set( 'meta_query', array( array( 'key' => '_thumbnail_id', 'compare' => $compare ) ) );
		}
	}

	/**
	 * Add filter on the Posts list tables.
	 *
	 */
	public function add_filter_link( $views ) {
		$query = auto_post_thumbnails()->get_posts_query( false, 'post', 'publish' );
		$posts = $query->post_count;

		$q                   = add_query_arg( array( 'apt_is_image' => '0', 'post_type' => 'post' ), 'edit.php' );
		$views['apt_filter'] = '<a href="' . $q . '">' . __( 'Without featured image', 'apt' ) . '</a> (' . $posts . ')';
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
		$link_generate = '<a href="' . esc_url( $this->getPluginPageUrl( $this->getPrefix() . "generate" ) ) . '">' . esc_html__( 'Generate', 'apt' ) . '</a>';
		array_unshift( $links, $link_generate );

		return $links;
	}

}