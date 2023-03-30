<?php

use WBCR\APT\AutoPostThumbnails;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once WAPT_PLUGIN_DIR . '/admin/class-page.php';

/**
 * The page Settings.
 *
 * @since 1.0.0
 */
class WAPT_ImageSettings extends WAPT_Page
{

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
    public $page_menu_dashicon = 'dashicons-cover-image';

    /**
     * {@inheritdoc}
     */
    public $show_menu_tab = true;

    /**
     * {@inheritdoc}
     */
    public $show_right_sidebar_in_options = true;

    /**
     * @var array
     */
    public $post_types;

    /**
     * @param WAPT_Plugin $plugin
     */
    public function __construct($plugin)
    {
        $this->id = $plugin->getPrefix() . 'image';
        $this->menu_target = $plugin->getPrefix() . 'generate-' . $plugin->getPluginName();
        $this->page_title = __('Image generation settings', 'apt');
        $this->menu_title = __('Image', 'apt');
        $this->capabilitiy = 'manage_options';
        $this->template_name = 'settings';

        add_action('wbcr_factory_forms_463_register_controls', function () {
            $colorControls = [
                [
                    'type' => 'wapt-color',
                    'class' => 'Wapt_FactoryForms_ColorControl',
                    'include' => WAPT_PLUGIN_DIR . '/includes/controls/class.color.php',
                ],
                [
                    'type' => 'wapt-mediabutton',
                    'class' => 'Wapt_FactoryForms_MediaButtonControl',
                    'include' => WAPT_PLUGIN_DIR . '/includes/controls/class.mediabutton.php',
                ],
                [
                    'type' => 'wapt-fonts',
                    'class' => 'Wapt_FactoryForms_FontsControl',
                    'include' => WAPT_PLUGIN_DIR . '/includes/controls/class.fonts.php',
                ],
            ];
            $this->plugin->forms->registerControls($colorControls);
        });

        //add_filter( 'wbcr/factory/pages/impressive-lite/widgets', [ $this, '' ], 10, 4 );

        $this->plugin = $plugin;
        $this->post_types = $this->getPostTypes();

        parent::__construct($plugin);
    }

    /**
     * Enqueue page assets
     *
     * @return void
     * @since 3.8.1
     * @see   Wbcr_FactoryPages466_AdminPage
     */
    public function assets($scripts, $styles)
    {
        parent::assets($scripts, $styles);

        $this->scripts->request([
            'control.list',
            'control.color',
            'plugin.color',
            'plugin.iris',
        ], 'bootstrap');

        $this->styles->request([
            'control.list',
            'control.color',
        ], 'bootstrap');

        $this->scripts->add(WAPT_PLUGIN_URL . '/admin/assets/js/jscolor.js', ['jquery'], 'wapt-color-control', WAPT_PLUGIN_VERSION);
        $this->scripts->add(WAPT_PLUGIN_URL . '/admin/assets/js/settings.js', ['jquery'], 'wapt-settings-script', WAPT_PLUGIN_VERSION);
        $this->styles->add(WAPT_PLUGIN_URL . '/admin/assets/css/settings.css', [], 'wapt-settings-style', WAPT_PLUGIN_VERSION);
    }

    public function isShowRightSidebar()
    {
        return $this->show_right_sidebar_in_options;
    }

    public function showRightSidebar()
    {
        ?>
        <div id="wapt-image-preview" class="wapt-image-preview">
            <div class="wapt-image-preview-title"><h3><?php esc_html_e('Post thumbnail preview', 'apt'); ?></h3></div>
            <?php
            $format = WAPT_Plugin::app()->getPopulateOption('image-type', 'jpg');
            switch ($format) {
                case 'png':
                    $format = 'png';
                    break;
                case 'jpg':
                case 'jpeg':
                default:
                    $format = 'jpg';
                    break;
            }

            $posts = get_posts(['numberposts' => 0]);
            $id = rand(0, count($posts) - 1);
            if (count($posts) !== 0) {
                $txt = $posts[$id]->post_title;
            } else {
                $txt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas aliquet turpis quis ex elementum malesuada';
            }

            $image = apply_filters('wapt/generate/image', AutoPostThumbnails::generate_image_with_text($txt), $txt);

            $image->save(WAPT_PLUGIN_DIR . "/preview.{$format}", 100, $format);
            ?>
            <img src="<?php echo esc_url_raw(WAPT_PLUGIN_URL . "/preview.{$format}?" . time()); ?>"
                 width="100%" alt="">
        </div>
        <?php
    }

    /**
     * Returns options for the Basic Settings screen.
     *
     * @return array
     * @since 3.6.2
     */
    public function getPageOptions()
    {

        $is_premium = WAPT_Plugin::app()->is_premium();
        $pro = $is_premium ? '' : "<br><span class='wapt-icon-pro wapt-icon-pro-span'>PRO</span>";
        $layout_columns = [
            'column-left' => 4,
            'column-right' => 8,
        ];

        $options = [];

        $options[] = [
            'type' => 'html',
            'html' => $this->group_header(__('Background settings', 'apt'), ''),
        ];

        $options[] = [
            'type' => 'dropdown',
            'way' => 'buttons',
            'name' => 'background-type',
            'data' => [
                ['color', __('Color', 'apt')],
                ['image', __('Image', 'apt')],
            ],
            'default' => 'color',
            'title' => __('Background type', 'apt'),
            'hint' => __('Select the background type for the featured image', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
        ];

        $options[] = [
            'type' => 'color',
            'name' => 'background-color',
            'default' => '#ff6262',
            'title' => __('Background color for the image', 'apt'),
            'hint' => __('Set the background color for the featured image', 'apt'),
            'layout' => $layout_columns,
        ];

        if ($is_premium) {
            $options[] = [
                'type' => 'wapt-mediabutton',
                'name' => 'background-image',
                'text' => __('Select image', 'apt'),
                'title' => __('Background image', 'apt'),
                'hint' => __('Set the background image. Only JPG or PNG', 'apt'),
                'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
                'layout' => $layout_columns,
            ];
        }

        if ($is_premium) {
            $options[] = [
                'type' => 'wapt-mediabutton',
                'name' => 'default-background',
                'text' => __('Select image', 'apt'),
                'title' => __('Default image', 'apt'),
                'hint' => __('Choose a default image for posts. JPG or PNG only.', 'apt'),
                'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
                'layout' => $layout_columns,
            ];
        }

        $options[] = [
            'type' => 'dropdown',
            'way' => 'buttons',
            'name' => 'image-type',
            'data' => [
                ['jpg', __('JPEG', 'apt')],
                ['png', __('PNG', 'apt')],
            ],
            'default' => 'jpg',
            'title' => __('Image format', 'apt'),
            'hint' => __('Set format to save images', 'apt'),
        ];

        $options[] = [
            'type' => 'integer',
            'way' => 'text',
            'name' => 'image-width',
            'units' => 'px',
            'default' => 800,
            'title' => __('Image size: width', 'apt'),
            'hint' => __('Set width of the image for the featured image', 'apt'),
        ];

        $options[] = [
            'type' => 'integer',
            'way' => 'text',
            'name' => 'image-height',
            'units' => 'px',
            'default' => 600,
            'title' => __('Image size: height', 'apt'),
            'hint' => __('Set height of the image for the featured image', 'apt'),
        ];

        //----------------------------------------------------------------------
        $options[] = [
            'type' => 'html',
            'html' => $this->group_header(__('Font settings', 'apt'), ''),
        ];

        $options[] = [
            'type' => 'wapt-fonts',
            'name' => 'font',
            'data' => AutoPostThumbnails::get_fonts(),
            'empty' => '',
            'title' => __('Font name', 'apt'),
            'hint' => __('Select a font for the text in the featured image', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
            'layout' => $layout_columns,
        ];

        $options[] = [
            'type' => 'integer',
            'way' => 'text',
            'name' => 'font-size',
            'units' => 'pt',
            'default' => 25,
            'title' => __('Font size', 'apt'),
            'hint' => __('Set the font size for the featured image', 'apt'),
        ];

        $options[] = [
            'type' => 'wapt-color',
            'name' => 'font-color',
            'title' => __('Font color', 'apt'),
            'hint' => __('Set the font color for the featured image', 'apt'),
            'layout' => $layout_columns,
        ];

        //----------------------------------------------------------------------
        $options[] = [
            'type' => 'html',
            'html' => $this->group_header(__('Text settings', 'apt'), ''),
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'shadow',
            'default' => '0',
            'title' => __('Text shadow', 'apt'),
            'hint' => __('Use text shadow?', 'apt'),
            'eventsOn' => [
                'show' => '.factory-control-shadow-color',
            ],
            'eventsOff' => [
                'hide' => '.factory-control-shadow-color',
            ],
        ];

        $options[] = [
            'type' => 'wapt-color',
            'name' => 'shadow-color',
            'title' => __('Shadow color', 'apt'),
            'hint' => __('Set the shadow color for the text', 'apt'),
            'layout' => $layout_columns,
        ];

        $options[] = [
            'type' => 'dropdown',
            'way' => 'buttons',
            'name' => 'text-transform',
            'data' => [
                ['no', __('No transform', 'apt')],
                ['upper', __('Uppercase', 'apt')],
                ['lower', __('Lowercase', 'apt')],
            ],
            'default' => 'no',
            'title' => __('Text transform', 'apt'),
            'hint' => __('Select type of text transformation', 'apt'),
        ];

        $options[] = [
            'type' => 'integer',
            'way' => 'text',
            'name' => 'text-crop',
            'units' => __('chars', 'apt'),
            'default' => 50,
            'title' => __('Text length', 'apt'),
            'hint' => __('Set the maximum text length', 'apt'),
        ];

        $options[] = [
            'type' => 'integer',
            'way' => 'text',
            'name' => 'text-line-spacing',
            'range' => [0, 3],
            'default' => 1.5,
            'title' => __('Line spacing', 'apt'),
            'hint' => __('Set the line spacing', 'apt'),
        ];

        //----------------------------------------------------------------------
        $options[] = [
            'type' => 'html',
            'html' => $this->group_header(__('Alignment', 'apt'), ''),
        ];

        $options[] = [
            'type' => 'dropdown',
            'way' => 'buttons',
            'name' => 'text-align-horizontal',
            'data' => [
                ['left', __('Left', 'apt')],
                ['center', __('Center', 'apt')],
                ['right', __('Right', 'apt')],
            ],
            'default' => 'center',
            'title' => __('Horizontal text alignment', 'apt') . $pro,
            'hint' => __('Select how to horizontally align the text on the image', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
        ];

        $options[] = [
            'type' => 'dropdown',
            'way' => 'buttons',
            'name' => 'text-align-vertical',
            'data' => [
                ['top', __('Top', 'apt')],
                ['center', __('Center', 'apt')],
                ['bottom', __('Bottom', 'apt')],
            ],
            'default' => 'center',
            'title' => __('Vertical text alignment', 'apt') . $pro,
            'hint' => __('Select how to vertically align the text on the image', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
        ];

        //----------------------------------------------------------------------
        $options[] = [
            'type' => 'html',
            'html' => $this->group_header(__('Padding', 'apt'), ''),
        ];

        $options[] = [
            'type' => 'integer',
            'way' => 'text',
            'name' => 'text-padding-tb',
            'units' => __('px', 'apt'),
            'default' => 15,
            'title' => __('Top/bottom text padding', 'apt') . $pro,
            'hint' => __('Padding at the top and bottom of the text', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
        ];

        $options[] = [
            'type' => 'integer',
            'way' => 'text',
            'name' => 'text-padding-lr',
            'units' => __('px', 'apt'),
            'default' => 15,
            'title' => __('Left/right text padding', 'apt') . $pro,
            'hint' => __('Padding at the left and right of the text', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
        ];

        //----------------------------------------------------------------------
        $options[] = [
            'type' => 'html',
            'html' => $this->group_header(__('Addition of text', 'apt'), ''),
        ];

        $options[] = [
            'type' => 'textbox',
            'name' => 'before-text',
            'default' => '',
            'title' => __('String before text', 'apt') . $pro,
            'hint' => __('Additional string before text. For a line break, use <b>[br]</b>', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
            'htmlAttrs' => (!$is_premium) ? ['disabled' => 'disabled'] : [],
        ];

        $options[] = [
            'type' => 'textbox',
            'name' => 'after-text',
            'default' => '',
            'title' => __('String after text', 'apt') . $pro,
            'hint' => __('Additional string after text. For a line break, use <b>[br]</b>', 'apt'),
            'cssClass' => (!$is_premium) ? ['wapt-icon-pro'] : [],
            'htmlAttrs' => (!$is_premium) ? ['disabled' => 'disabled'] : [],
        ];

        $form_options[] = [
            'type' => 'form-group',
            'items' => $options,
            //'cssClass' => 'postbox'
        ];

        return $form_options;
    }

}
