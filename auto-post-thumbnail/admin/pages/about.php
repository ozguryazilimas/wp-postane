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
class WAPT_About extends WAPT_Page {

	/**
	 * Тип страницы
	 * options - предназначена для создании страниц с набором опций и настроек.
	 * page - произвольный контент, любой html код
	 *
	 * @var string
	 */
	public $type = 'page';

	/**
	 * @var int
	 */
	public $page_menu_position = 1000;

	/**
	 * @var bool
	 */
	public $internal = false;

	/**
	 * Menu icon (only if a page is placed as a main menu).
	 * For example: '~/assets/img/menu-icon.png'
	 * For example dashicons: '\f321'
     *
	 * @var string
	 */
	public $menu_icon = '';

	/**
	 * @var string
	 */
	public $page_menu_dashicon = 'dashicons-info-outline';

	/**
	 * {@inheritdoc}
	 */
	public $show_menu_tab = false;

	/**
	 * @param WAPT_Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->id            = 'wapt_about';
		$this->menu_target   = $plugin->getPrefix() . 'generate-' . $plugin->getPluginName();
		$this->page_title    = __( 'About APT', 'apt' );
		$this->menu_title    = __( 'About', 'apt' );
		$this->template_name = 'about';

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}
}
