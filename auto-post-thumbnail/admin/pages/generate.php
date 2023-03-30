<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WAPT_PLUGIN_DIR . '/admin/class-page.php';

/**
 * The page Settings.
 *
 * @since 1.0.0
 */
class WAPT_Generate extends WAPT_Page {

	/**
	 * The id of the page in the admin menu.
	 *
	 * Mainly used to navigate between pages.
	 *
	 * @since 1.0.0
	 * @see   FactoryPages466_AdminPage
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Тип страницы
	 * options - предназначена для создании страниц с набором опций и настроек.
	 * page - произвольный контент, любой html код
	 *
	 * @var string
	 */
	public $type = 'page';

	/**
	 * Menu icon (only if a page is placed as a main menu).
	 * For example: '~/assets/img/menu-icon.png'
	 * For example dashicons: '\f321'
	 *
	 * @var string
	 */
	public $menu_icon;

	/**
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-performance';

	/**
	 * Menu position (only if a page is placed as a main menu).
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_menu_page
	 * @var string
	 */
	public $menu_position = 58;

	/**
	 * @var bool
	 */
	public $internal = false;

	/**
	 * Menu type. Set it to add the page to the specified type menu.
	 * For example: 'post'
	 *
	 * @var string
	 */
	public $menu_post_type = null;

	/**
	 * Visible page title.
	 * For example: 'License Manager'
	 *
	 * @var string
	 */
	public $page_title;

	/**
	 * Visible title in menu.
	 * For example: 'License Manager'
	 *
	 * @var string
	 */
	public $menu_title;

	/**
	 *
	 */
	public $page_menu_short_description;

	/**
	 * Заголовок страницы, также использует в меню, как название закладки
	 *
	 * @var bool
	 */
	public $show_page_title = true;

	/**
	 * @var int
	 */
	public $page_menu_position = 100;


	/**
	 * @param WAPT_Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->id         = $plugin->getPrefix() . 'generate';
		$this->menu_title = __( 'Auto Featured Image', 'apt' );

		$this->menu_sub_title = __( 'Generate images', 'apt' );
		$this->menu_tab_title = __( 'Generate images', 'apt' );
		$this->page_title     = __( 'Generate images', 'apt' );

		$this->menu_icon     = WAPT_PLUGIN_URL . '/admin/assets/img/apt.png';
		$this->template_name = 'generate';

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	/**
	 * Requests assets (js and css) for the page.
	 *
	 * @return void
	 * @since 1.0.0
	 * @see   FactoryPages466_AdminPage
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		wp_enqueue_style( 'jquery-ui-genpostthumbs', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery-ui.min.css', [ 'jquery' ], WAPT_PLUGIN_VERSION );
		wp_enqueue_style( 'wapt-generate', WAPT_PLUGIN_URL . '/admin/assets/css/generate.css', [], WAPT_PLUGIN_VERSION );
		wp_enqueue_script( 'jquery-progress', WAPT_PLUGIN_URL . '/admin/assets/jquery-ui/jquery-ui.progressbar.min.js', [ 'jquery' ], WAPT_PLUGIN_VERSION, true );
		wp_enqueue_script( 'wapt-chart', WAPT_PLUGIN_URL . '/admin/assets/js/Chart.min.js', [ 'jquery' ], WAPT_PLUGIN_VERSION, true );
		wp_enqueue_script( 'wapt-generate', WAPT_PLUGIN_URL . '/admin/assets/js/generate.js', [ 'jquery' ], WAPT_PLUGIN_VERSION, true );
		wp_localize_script( 'wapt-generate', 'wapt', [
			'is_premium'            => $this->plugin->is_premium(),
			'nonce_get_posts'       => wp_create_nonce( 'get-posts' ),
			'nonce_gen_post_thumbs' => wp_create_nonce( 'generate-post-thumbnails' ),
			'nonce_del_post_thumbs' => wp_create_nonce( 'delete-post-thumbnails' ),
			'i8n_processed_posts'   => esc_html__( 'All done! Processed posts: ', 'apt' ),
			'i8n_set_images'        => esc_html__( 'Set featured image in posts: ', 'apt' ),
			'i8n_del_images'        => esc_html__( 'Unset featured image in posts: ', 'apt' ),
			'i8n_delete_images'     => esc_html__( 'Delete featured image in posts: ', 'apt' ),
		] );

	}

	/**
	 * Show rendered template - $template_name
	 */
	public function showPageContent() {
		$no_featured = $this->plugin->apt->get_posts_count();
		$w_featured  = $this->plugin->apt->get_posts_count( true );
		$percent     = ( $no_featured + $w_featured === 0 ) ? 0 : ceil( $w_featured / ( $no_featured + $w_featured ) * 100 );

		$generate        = $this->plugin->getPopulateOption( 'generate_autoimage', 'find' );
		$generate_option = WAPT_Settings::get_generate_options();
		$generate_option = $generate_option[ $generate ] ?? [
			'title' => '',
			'value' => $generate,
			'hint'  => '',
		];

		$data = [
			'stats'           => [
				'no_featured_image'      => $no_featured,
				'w_featured_image'       => $w_featured,
				'featured_image_percent' => $percent,
				'error'                  => 0,
			],
			'generate_option' => $generate_option,
			'log'             => $this->plugin->getPopulateOption( 'generation_log', [] ),
		];
		echo $this->render( $this->template_name, $data ); // phpcs:ignore
	}
}
