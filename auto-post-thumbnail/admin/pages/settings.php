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
		$this->id            = $plugin->getPrefix(). "settings";
		$this->menu_target   = $plugin->getPrefix() . "generate-" . $plugin->getPluginName();
		$this->page_title    = __( 'Settings of APT', 'apt' );
		$this->menu_title    = __( 'Settings', 'apt' );
		$this->capabilitiy   = "manage_options";
		$this->template_name = "settings";

		add_action( 'wbcr_factory_forms_430_register_controls', function () {
			$colorControls = array(
				[
					'type'    => 'wapt-color',
					'class'   => 'Wapt_FactoryForms_ColorControl',
					'include' => WAPT_PLUGIN_DIR . '/includes/controls/class.color.php'
				],
				[
					'type'    => 'wapt-mediabutton',
					'class'   => 'Wapt_FactoryForms_MediaButtonControl',
					'include' => WAPT_PLUGIN_DIR . '/includes/controls/class.mediabutton.php'
				],
				[
					'type'    => 'wapt-fonts',
					'class'   => 'Wapt_FactoryForms_FontsControl',
					'include' => WAPT_PLUGIN_DIR . '/includes/controls/class.fonts.php'
				],
			);
			$this->plugin->forms->registerControls( $colorControls );
		} );

		$this->plugin = $plugin;

		parent::__construct( $plugin );
	}

	/**
	 * Returns options for the Basic Settings screen.
	 *
	 * @return array
	 * @since 3.6.2
	 */
	public function getOptions_general() {

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
			'title'   => __( 'Add featured image when saving a post', 'apt' ),
			'default' => true,
			'hint'    => __( 'Automatically add featured image when saving a post', 'apt' )
		];

		$options[] = [
			'type'    => 'dropdown',
			'way'     => 'buttons',
			'name'    => 'generate_autoimage',
			'data'    => [
				[ 'find', __( 'Find in post', 'apt' ) ],
				[ 'generate', __( 'Generate from title', 'apt' ) ],
				[ 'both', __( 'Both', 'apt' ) ],
			],
			'default' => 'find',
			'title'   => __( 'Featured image', 'apt' ),
			'hint'    => __( "How to generate featured image:
							<br> <b>Find in post:</b> search for the first image in the post text
							<br> <b>Generate from title:</b> created from the title on a colored background
							<br> <b>Both:</b> find an image in the post text, if it is not present, generate it from the title", 'apt' ),
		];

		$options[] = [
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'delete_settings',
			'title'   => __( 'Delete settings when removing the plugin', 'apt' ),
			'default' => false,
			'hint'    => __( 'Delete settings when removing the plugin', 'apt' )
		];

		return $options;
	}

	/**
	 * Returns options for the Basic Settings screen.
	 *
	 * @return array
	 * @since 3.6.2
	 */
	public function getOptions_image() {

		$is_premium = AutoPostThumbnails::instance()->is_premium();
		$pro        = $is_premium ? '' : "<br><span class='wapt-icon-pro wapt-icon-pro-span'>PRO</span>";

		$options = [];

		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">' . __( 'Background settings', 'apt' ) . '</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'     => 'dropdown',
			'way'      => 'buttons',
			'name'     => 'background-type',
			'data'     => [
				[ 'color', __( 'Color', 'apt' ) ],
				[ 'image', __( 'Image', 'apt' ) ],
			],
			'default'  => 'color',
			'title'    => __( 'Background type', 'apt' ),
			'hint'     => __( 'Select the background type for the featured image', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		$options[] = [
			'type'    => 'wapt-color',
			'name'    => 'background-color',
			'default' => '#ff6262',
			'title'   => __( 'Background color for the image', 'apt' ),
			'hint'    => __( 'Set the background color for the featured image', 'apt' )
		];

		if ( $is_premium ) {
			$options[] = [
				'type'     => 'wapt-mediabutton',
				'name'     => 'background-image',
				'text'     => __( 'Select image', 'apt' ),
				'title'    => __( 'Background image', 'apt' ),
				'hint'     => __( 'Set the background image. Only JPG or PNG', 'apt' ),
				'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
			];
		}

		$options[] = [
			'type'    => 'dropdown',
			'way'     => 'buttons',
			'name'    => 'image-type',
			'data'    => [
				[ 'jpg', __( 'JPEG', 'apt' ) ],
				[ 'png', __( 'PNG', 'apt' ) ],
			],
			'default' => 'jpg',
			'title'   => __( 'Image format', 'apt' ),
			'hint'    => __( 'Set format to save images', 'apt' ),
		];
		//----------------------------------------------------------------------
		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">' . __( 'Font settings', 'apt' ) . '</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'     => 'wapt-fonts',
			'name'     => 'font',
			'data'     => AutoPostThumbnails::get_fonts(),
			'empty'    => '',
			'title'    => __( 'Font name', 'apt' ),
			'hint'     => __( 'Select a font for the text in the featured image', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		$options[] = [
			'type'    => 'integer',
			'way'     => 'text',
			'name'    => 'font-size',
			'units'   => 'pt',
			'default' => 25,
			'title'   => __( 'Font size', 'apt' ),
			'hint'    => __( 'Set the font size for the featured image', 'apt' )
		];

		$options[] = [
			'type'  => 'wapt-color',
			'name'  => 'font-color',
			'title' => __( 'Font color', 'apt' ),
			'hint'  => __( 'Set the font color for the featured image', 'apt' )
		];

		//----------------------------------------------------------------------
		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">' . __( 'Text settings', 'apt' ) . '</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'      => 'checkbox',
			'way'       => 'buttons',
			'name'      => 'shadow',
			'default'   => '0',
			'title'     => __( 'Text shadow', 'apt' ),
			'hint'      => __( 'Use text shadow?', 'apt' ),
			'eventsOn'  => [
				'show' => '.factory-control-shadow-color'
			],
			'eventsOff' => [
				'hide' => '.factory-control-shadow-color'
			],
		];

		$options[] = [
			'type'  => 'wapt-color',
			'name'  => 'shadow-color',
			'title' => __( 'Shadow color', 'apt' ),
			'hint'  => __( 'Set the shadow color for the text', 'apt' )
		];

		$options[] = [
			'type'    => 'dropdown',
			'way'     => 'buttons',
			'name'    => 'text-transform',
			'data'    => [
				[ 'no', __( 'No transform', 'apt' ) ],
				[ 'upper', __( 'Uppercase', 'apt' ) ],
				[ 'lower', __( 'Lowercase', 'apt' ) ],
			],
			'default' => 'no',
			'title'   => __( 'Text transform', 'apt' ),
			'hint'    => __( 'Select type of text transformation', 'apt' )
		];

		$options[] = [
			'type'    => 'integer',
			'way'     => 'text',
			'name'    => 'text-crop',
			'units'   => __( 'chars', 'apt' ),
			'default' => 50,
			'title'   => __( 'Text length', 'apt' ),
			'hint'    => __( 'Set the maximum text length', 'apt' )
		];

		$options[] = [
			'type'    => 'integer',
			'way'     => 'text',
			'name'    => 'text-line-spacing',
			'range'   => array( 0, 3 ),
			'default' => 1.5,
			'title'   => __( 'Line spacing', 'apt' ),
			'hint'    => __( 'Set the line spacing', 'apt' )
		];

		//----------------------------------------------------------------------
		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">' . __( 'Alignment', 'apt' ) . '</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'     => 'dropdown',
			'way'      => 'buttons',
			'name'     => 'text-align-horizontal',
			'data'     => [
				[ 'left', __( 'Left', 'apt' ) ],
				[ 'center', __( 'Center', 'apt' ) ],
				[ 'right', __( 'Right', 'apt' ) ],
			],
			'default'  => 'center',
			'title'    => __( 'Horizontal text alignment', 'apt' ) . $pro,
			'hint'     => __( 'Select how to horizontally align the text on the image', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		$options[] = [
			'type'     => 'dropdown',
			'way'      => 'buttons',
			'name'     => 'text-align-vertical',
			'data'     => [
				[ 'top', __( 'Top', 'apt' ) ],
				[ 'center', __( 'Center', 'apt' ) ],
				[ 'bottom', __( 'Bottom', 'apt' ) ],
			],
			'default'  => 'center',
			'title'    => __( 'Vertical text alignment', 'apt' ) . $pro,
			'hint'     => __( 'Select how to vertically align the text on the image', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		//----------------------------------------------------------------------
		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">' . __( 'Padding', 'apt' ) . '</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'     => 'integer',
			'way'      => 'text',
			'name'     => 'text-padding-tb',
			'units'    => __( 'px', 'apt' ),
			'default'  => 15,
			'title'    => __( 'Top/bottom text padding', 'apt' ) . $pro,
			'hint'     => __( 'Padding at the top and bottom of the text', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		$options[] = [
			'type'     => 'integer',
			'way'      => 'text',
			'name'     => 'text-padding-lr',
			'units'    => __( 'px', 'apt' ),
			'default'  => 15,
			'title'    => __( 'Left/right text padding', 'apt' ) . $pro,
			'hint'     => __( 'Padding at the left and right of the text', 'apt' ),
			'cssClass' => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
		];

		//----------------------------------------------------------------------
		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">' . __( 'Addition of text', 'apt' ) . '</h3>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'      => 'textbox',
			'name'      => 'before-text',
			'default'   => '',
			'title'     => __( 'String before text', 'apt' ) . $pro,
			'hint'      => __( 'Additional string before text. For a line break, use <b>[br]</b>', 'apt' ),
			'cssClass'  => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
			'htmlAttrs' => ( ! $is_premium ) ? [ 'disabled' => 'disabled' ] : [],
		];

		$options[] = [
			'type'      => 'textbox',
			'name'      => 'after-text',
			'default'   => '',
			'title'     => __( 'String after text', 'apt' ) . $pro,
			'hint'      => __( 'Additional string after text. For a line break, use <b>[br]</b>', 'apt' ),
			'cssClass'  => ( ! $is_premium ) ? [ 'wapt-icon-pro' ] : [],
			'htmlAttrs' => ( ! $is_premium ) ? [ 'disabled' => 'disabled' ] : [],
		];

		return $options;
	}

	/**
	 * Returns options for the Basic Settings screen.
	 *
	 * @return array
	 * @since 3.6.2
	 */
	public function getOptions_api() {

		$options = [];

		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">API Settings</h3>'
		];

		/* GOOGLE */
		$options[] = [
			'type' => 'html',
			'html' => '<h3 style="margin-left:0">Google API</h3><p><a href="https://www.youtube.com/watch?v=Bxy8Yqp5XX0" target="_blank" rel="noopener">' . __( 'How to get google api key & custom search engine id', 'apt' ) . '</a></p>'
		];

		$options[] = [
			'type' => 'separator'
		];

		$options[] = [
			'type'  => 'hidden',
			'name'  => 'ajax_nonce',
			'value' => wp_create_nonce( 'check-api-key' )
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
		wp_enqueue_style( 'wapt-tabs-style', WAPT_PLUGIN_URL . '/admin/assets/css/tabs.css', array(), WAPT_PLUGIN_VERSION );
		wp_enqueue_style( 'wapt-settings-style', WAPT_PLUGIN_URL . '/admin/assets/css/settings.css', array(), WAPT_PLUGIN_VERSION );
		wp_enqueue_script( 'wapt-settings-script', WAPT_PLUGIN_URL . '/admin/assets/js/settings.js', [], WAPT_PLUGIN_VERSION, true );
		// creating a form
		global $form;
		$form = new Wbcr_FactoryForms430_Form( [
			'scope' => substr( $this->plugin->getPrefix(), 0, - 1 ),
			'name'  => 'setting'
		], $this->plugin );

		$form->setProvider( new Wbcr_FactoryForms430_OptionsValueProvider( $this->plugin ) );

		$wapt_tab = WAPT_Plugin::app()->request->get( 'apt_tab', '' );
		switch ( $wapt_tab ) {
			case 'general':
				$form->add( $this->getOptions_general() );
				break;
			case 'img_generation':
				$form->add( $this->getOptions_image() );
				break;
			case 'api':
				$form->add( $this->getOptions_api() );
				break;
			default:
				$form->add( $this->getOptions_general() );
				break;
		}

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