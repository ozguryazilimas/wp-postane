<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WAPT_PLUGIN_DIR . '/admin/class-wapt-page.php';

/**
 * The page Settings.
 *
 * @since 1.0.0
 */
class WAPT_Settings extends WAPT_Page {

	/**
	 * Тип страницы
	 * options - предназначена для создании страниц с набором опций и настроек.
	 * page - произвольный контент, любой html код
	 *
	 * @var string
	 */
	public $type = 'options';

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
	public $page_menu_dashicon = '';

	/**
	 * @param WAPT_Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->id            = "wapt_settings";
		$this->menu_target   = $plugin->getPrefix() . "generate-" . $plugin->getPluginName();
		$this->page_title    = __( 'Settings of APT', 'apt' );
		$this->menu_title    = __( 'Settings', 'apt' );
		$this->capabilitiy   = "manage_options";
		$this->template_name = "settings";

		parent::__construct( $plugin );

		$this->plugin = $plugin;
	}

	/**
	 * Returns options for the Basic Settings screen.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function getOptions() {

		$options = [];

		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">General</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'auto_generation',
			'title'   => __( 'Enable automatic post thumbnail generation', 'apt' ),
			'default' => false,
			'hint'    => __( 'Enable automatic post thumbnail generation', 'apt' )
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'delete_settings',
			'title'   => __( 'Delete settings when removing the plugin', 'apt' ),
			'default' => false,
			'hint'    => __( 'Delete settings when removing the plugin', 'apt' )
		];
		/* GOOGLE */
		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">Google API</h3><p><a href="https://www.youtube.com/watch?v=Bxy8Yqp5XX0" target="_blank" rel="noopener">' . __( 'How to get google api key & custom search engine id', 'apt' ) . '</a></p>'
		];

		$options[] = [
			'type' => 'separator'
		];

		// Текстовое поле
		$options[] = [
			'type'    => 'textbox',
			'name'    => 'google_apikey',
			'title'   => __( 'API key for Google', 'apt' ),
			'hint'    => __( 'You can get API key after registration on the site' ) . ' <a href="https://developers.google.com/custom-search/v1/overview" target="_blank" rel="noopener">https://developers.google.com/custom-search/v1/overview</a>',
			'default' => ''
		];

		$options[] = [
			'type'    => 'textbox',
			'name'    => 'google_cse',
			'title'   => __( 'Google Custom Search Engine ID', 'apt' ),
			'hint'    => __( 'You can get API key after registration on the site', 'apt' ) . ' <a href="https://cse.google.com/cse/all" target="_blank" rel="noopener">https://cse.google.com/cse/all</a>',
			'default' => ''
		];

		$options = apply_filters( 'wapt/settings/form_options', $options );

		$options[] = [
			'type' => 'separator'
		];

		return $options;
	}

	public function indexAction() {

		// creating a form
		global $form;
		$form = new Wbcr_FactoryForms419_Form( [
			'scope' => substr( $this->plugin->getPrefix(), 0, - 1 ),
			'name'  => 'setting'
		], $this->plugin );

		$form->setProvider( new Wbcr_FactoryForms419_OptionsValueProvider( $this->plugin ) );

		$form->add( $this->getOptions() );

		$wapt_saved = WAPT_Plugin::app()->request->post( $this->plugin->getPrefix() . 'saved', '' );
		if ( ! empty( $wapt_saved ) ) {
			$wapt_nonce = WAPT_Plugin::app()->request->post( $this->plugin->getPrefix() . 'nonce', '' );
			if ( ! wp_verify_nonce( $wapt_nonce, $this->plugin->getPrefix() . 'settings_form' ) ) {
				wp_die( 'Permission error. You can not edit this page.' );
			}
			$form->save();

			do_action( 'wapt/settings/after_form_save' );
		}

		parent::indexAction();
	}
}