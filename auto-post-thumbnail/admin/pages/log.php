<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WAPT_PLUGIN_DIR . '/admin/class-page-logger.php';

/**
 * Класс отвечает за работу страницы логов.
 *
 * @author        Eugene Jokerov <jokerov@gmail.com>
 * @author        Alexander Teshabaev <sasha.tesh@gmail.com>
 * @copyright (c) 2018, Webcraftic
 * @version       1.0
 */
class WAPT_Log extends WAPT_Page_Logger {

	/**
	 * {@inheritdoc}
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
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-list-view';

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
	 * Заголовок страницы, также использует в меню, как название закладки
	 *
	 * @var bool
	 */
	public $show_page_title = true;

	/**
	 * @var int
	 */
	public $page_menu_position = 300;

	/**
	 * {@inheritdoc}
	 */
	public $available_for_multisite = false;

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * @param WAPT_Plugin $plugin
	 */
	public function __construct( WAPT_Plugin $plugin ) {
		$this->id          = 'log';
		$this->menu_target = $plugin->getPrefix() . 'generate-' . $plugin->getPluginName();
		$this->page_title  = __( 'Plugin logs', 'apt' );
		$this->menu_title  = $this->getMenuTitle();

		$this->plugin = $plugin;

		parent::__construct( $plugin );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMenuTitle() {
		return __( 'Logs', 'apt' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );
	}
}
