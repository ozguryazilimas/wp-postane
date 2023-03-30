<?php

use WBCR\APT\AutoPostThumbnails;

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
	 * @var bool
	 */
	public $internal = false;

	/**
	 * @var int
	 */
	public $page_menu_position = 200;

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
	public $page_menu_dashicon = 'dashicons-admin-settings';

	/**
	 * {@inheritdoc}
	 */
	public $show_menu_tab = true;

	/**
	 * @var array
	 */
	public $post_types;

	/**
	 * @param WAPT_Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->id                          = $plugin->getPrefix() . 'settings';
		$this->menu_target                 = $plugin->getPrefix() . 'generate-' . $plugin->getPluginName();
		$this->page_title                  = __( 'Settings of APT', 'apt' );
		$this->menu_title                  = __( 'Settings', 'apt' );
		$this->page_menu_short_description = __( 'General settings', 'apt' );
		$this->capabilitiy                 = 'manage_options';
		$this->template_name               = 'settings';

		$this->plugin     = $plugin;
		$this->post_types = $this->getPostTypes();

		parent::__construct( $plugin );
	}

	/**
	 * Enqueue page assets
	 *
	 * @return void
	 * @since 3.8.1
	 * @see   Wbcr_FactoryPages466_AdminPage
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->scripts->request( [
			'control.list',
			'bootstrap.accordion',
			'bootstrap.tab',
			'plugin.ddslick',
		], 'bootstrap' );

		$this->styles->request( [
			'control.list',
			'bootstrap.accordion',
			'bootstrap.tab',
			'plugin.ddslick',
		], 'bootstrap' );

		$this->scripts->add( WAPT_PLUGIN_URL . '/admin/assets/js/settings.js', [ 'jquery' ], 'wapt-settings-script', WAPT_PLUGIN_VERSION );
		$this->styles->add( WAPT_PLUGIN_URL . '/admin/assets/css/settings.css', [], 'wapt-settings-style', WAPT_PLUGIN_VERSION );
	}

	/**
	 * Returns options for the Basic Settings screen.
	 *
	 * @return array
	 * @since 3.6.2
	 */
	public function getPageOptions() {
		$is_premium = WAPT_Plugin::app()->is_premium();
		$pro        = $is_premium ? '' : "<br><span class='wapt-icon-pro wapt-icon-pro-span'>PRO</span>";

		$options = [];

		$options[] = [
			'type' => 'html',
			'html' => $this->group_header( __( 'General', 'apt' ), __( 'Basic plugin settings', 'apt' ) ),
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'auto_generation',
			'title'   => __( 'Add featured image when saving a post', 'apt' ),
			'default' => true,
			'hint'    => __( 'Automatically add featured image when saving a post', 'apt' ),
		];

		$options[] = [
			'type'      => 'checkbox',
			'way'       => 'buttons',
			'name'      => 'scheduled_generation',
			'title'     => __( 'Add featured image on a schedule', 'apt' ),
			'default'   => false,
			'hint'      => __( 'Automatically add featured image according to a CRON schedule', 'apt' ),
			'eventsOn'  => [
				'show' => '.factory-control-auto_generation_schedule',
			],
			'eventsOff' => [
				'hide' => '.factory-control-auto_generation_schedule',
			],
			'cssClass'  => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		$options[] = [
			'type'    => 'dropdown',
			'way'     => 'ddslick',
			'name'    => 'auto_generation_schedule',
			'title'   => __( 'Image generation schedule', 'apt' ),
			'data'    => [
				[
					'title' => __( 'Once an hour', 'apt' ),
					'value' => 'hourly',
				],
				[
					'title' => __( 'Once a day', 'apt' ),
					'value' => 'daily',
				],
				[
					'title' => __( 'Twice a day', 'apt' ),
					'value' => 'twicedaily',
				],
				[
					'title' => __( 'Once a week', 'apt' ),
					'value' => 'weekly',
				],
				[
					'title' => __( 'Once a month', 'apt' ),
					'value' => 'monthly',
				],
			],
			'default' => 'daily',
			'hint'    => __( 'How often to run image generation', 'apt' ),
		];

		$options[] = [
			'type'     => 'dropdown',
			'way'      => 'ddslick',
			'name'     => 'generate_autoimage',
			'data'     => self::get_generate_options(),
			'default'  => 'find',
			'title'    => __( 'Generation method', 'apt' ),
			'hint'     => __( 'How to generate featured image', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro-item' ] : [],
		];

		$options[] = [
			'type'    => 'list',
			'way'     => 'checklist',
			'name'    => 'auto_post_types',
			'data'    => $is_premium ? $this->getPostTypes() : $this->post_types,
			'default' => 'post,page',
			'title'   => __( 'Generate for post types', 'apt' ),
			'hint'    => __( 'What types of posts to generate images for', 'apt' ),
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'delete_settings',
			'title'   => __( 'Delete settings when removing the plugin', 'apt' ),
			'default' => false,
			'hint'    => __( 'Delete settings when removing the plugin', 'apt' ),
		];

		/* ------------------ IMPORT SETTINGS -----------------------*/
		$options[] = [
			'type' => 'html',
			'html' => $this->group_header( __( 'Import', 'apt' ), __( 'Images import settings', 'apt' ) ),
		];

		$options[] = [
			'type'     => 'checkbox',
			'way'      => 'buttons',
			'name'     => 'auto_upload_images',
			'title'    => __( 'Auto images import', 'apt' ),
			'default'  => false,
			'hint'     => __( 'Import post images to the media library and replacing them in the text when saving the post', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		$options[] = [
			'type'      => 'list',
			'way'       => 'checklist',
			'name'      => 'import_post_types',
			'data'      => $is_premium ? $this->getPostTypes() : $this->post_types,
			'default'   => '',
			'title'     => __( 'Import for post types', 'apt' ) . $pro,
			'hint'      => __( 'What types of posts to import images for', 'apt' ),
			'cssClass'  => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
			'htmlAttrs' => ( ! $is_premium ) ? [ 'disabled' => 'disabled' ] : [],
		];

		/* ------------------ API SETTINGS -----------------------*/
		$options[] = [
			'type' => 'html',
			'html' => $this->group_header( __( 'Google API', 'apt' ), __( 'Settings connecting to the Google API service', 'apt' ) ),
		];

		/* GOOGLE */
		$options[] = [
			'type' => 'html',
			'html' => $this->instruction( __( 'Google API', 'apt' ), '<a href="https://www.youtube.com/watch?v=Bxy8Yqp5XX0" target="_blank" rel="noopener">' . __( 'How to get google api key & custom search engine id', 'apt' ) . '</a>' ),
		];

		$options[] = [
			'type'  => 'hidden',
			'name'  => 'ajax_nonce',
			'value' => '', //wp_create_nonce( 'check-api-key' )
		];

		// Текстовое поле
		$options[] = [
			'type'    => 'textbox',
			'name'    => 'google_apikey',
			'title'   => __( 'API key for Google', 'apt' ),
			'hint'    => __( 'You can get API key after registration on the site' ) . ' <a href="https://developers.google.com/custom-search/v1/overview" target="_blank" rel="noopener">https://developers.google.com/custom-search/v1/overview</a>',
			'default' => '',
		];

		$options[] = [
			'type'    => 'textbox',
			'name'    => 'google_cse',
			'title'   => __( 'Google Custom Search Engine ID', 'apt' ),
			'hint'    => __( 'You can get API key after registration on the site', 'apt' ) . ' <a href="https://cse.google.com/cse/all" target="_blank" rel="noopener">https://cse.google.com/cse/all</a>',
			'default' => '',
		];

		$options = apply_filters( 'wapt/settings/form_options', $options );

		$form_options[] = [
			'type'  => 'form-group',
			'items' => $options,
			//'cssClass' => 'postbox'
		];

		return $form_options;
	}

	public static function get_generate_options() {
		return [
			'find'        => [
				'title' => __( 'Find in post', 'apt' ),
				'value' => 'find',
				'hint'  => __( 'Search for the first image in the post text', 'apt' ),
			],
			'generate'    => [
				'title' => __( 'Generate from title', 'apt' ),
				'value' => 'generate',
				'hint'  => __( 'Create from the title on a colored background', 'apt' ),
			],
			'both'        => [
				'title' => __( 'Find or generate', 'apt' ),
				'value' => 'both',
				'hint'  => __( 'Find an image in the post text, if it is not present, generate it from the title', 'apt' ),
			],
			'google'      => [
				'title' => __( 'Google', 'apt' ),
				'value' => 'google',
				'hint'  => __( 'Search for an image by title of the post in Google', 'apt' ),
			],
			'find_google' => [
				'title' => __( 'Find or Google', 'apt' ),
				'value' => 'find_google',
				'hint'  => __( 'Find an image in the post text, if it is not present, search for an image by title of the post in Google', 'apt' ),
			],
			'use_default' => [
				'title' => __( 'Find or use default image', 'apt' ),
				'value' => 'use_default',
				'hint'  => __( 'Find an image in the post text, if it is not present, use default image for posts', 'apt' ),
			],
		];
	}

}
