<?php
/*
Plugin Name: Auto Featured Image (Auto Post Thumbnail)
Plugin URI: https://cm-wp.com/apt
Description: Automatically generate the Featured Image from the first image in post or any custom post type only if Featured Image is not set manually. Featured Image Generation From Title. Native image search for Elementor, Gutenberg, Classic Editor.
Version: 3.7.6
Author: Creativemotion <support@cm-wp.com>
Author URI: cm-wp.com
Text Domain: apt
Domain Path: /languages
*/

/*  Copyright 2019  Creativemotion

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * -----------------------------------------------------------------------------
 * CHECK REQUIREMENTS
 * Check compatibility with php and wp version of the user's site. As well as checking
 * compatibility with other plugins from Creativemotion.
 * -----------------------------------------------------------------------------
 */
// @formatter:off
// Подключаем класс проверки совместимости
require_once( dirname( __FILE__ ) . '/libs/factory/core/includes/class-factory-requirements.php' );

$plugin_info = array(
	'prefix'               => 'wapt_',
	// Префикс для базы данных и полей формы. Строка должна соответствовать условию [A-z0-9_].
	'plugin_name'          => 'wbcr_apt',
	// Кодовое название плагина, используется как уникальный идентификатор. Строка должна соответствовать условию [A-z0-9_].
	'plugin_title'         => __( 'Auto Featured Image', 'apt' ),
	// Название плагина. То же что и Plugin Name. Используется в интерфейсе и сообщениях.

	// Служба поддержки
	// Указываем ссылки и имена страниц сайта плагина, чтобы иметь к ним доступ внутри плагина.
	'support_details'      => array(
		'url'       => 'https://cm-wp.com',// Ссылка на сайт плагина
		'pages_map' => array(
			'features' => 'features', // {site}/premium-features "страница возможности"
			'pricing'  => 'pricing', // {site}/prices страница "цены"
			'support'  => '', // {site}/support страница "служба поддержки"
			'docs'     => 'docs' // {site}/docs страница "документация"
		)
	),

	// Настройка обновлений плагина
	// Имеется ввиду настройка обновлений из удаленного репозитория. Это может быть wordpress.org, freemius.com, codecanyon.com
	'has_updates'          => true,
	// Нужно ли проверять обновления для этого плагина
	'updates_settings'     => array(
		'repository'        => 'wordpress',
		// Тип репозитория из которого получаем обновления. Может быть wordpress, freemius
		'slug'              => 'auto-post-thumbnail',
		// Слаг плагина в удаленном репозитории
		'maybe_rollback'    => true,
		// Можно ли делать откат к предыдущей версии плагина?
		'rollback_settings' => array(
			'prev_stable_version' => '0.0.0'
			// Нужно указать предыдущую стабильную версию, к которой нужно сделать откат.
		)
	),

	// Настройка премиум плагина
	// Сюда входят настройки лицензирования и премиум обновлений плагина и его надстройки
	'has_premium'          => true,
	// Есть ли у текущего плагина премиум? Если false, премиум модуль загружен не будет
	'license_settings'     => array(
		'has_updates'      => true,
		'provider'         => 'freemius',
		// Тип лицензионного поставщика, может быть freemius, codecanyon, templatemonster
		'slug'             => 'auto-post-thumbnail-premium',
		// Слаг плагина в выбранном поставщике лицензий и обновлений
		'plugin_id'        => '4146',
		// ID плагина в freemius.com
		'public_key'       => 'pk_5e3ec7615d3abb543e25ee6eb2fc7',
		// Публичный ключ плагина в freemius.com
		'price'            => 29,
		// Минимальная цена плагина, выводится в рекламных блоках
		// Настройка обновлений премиум плагина
		'updates_settings' => array(
			'maybe_rollback'    => true, // Можно ли делать откат к предыдущей версии плагина?
			'rollback_settings' => array(
				'prev_stable_version' => '0.0.0'
				// Нужно указать предыдущую стабильную версию, к которой нужно сделать откат.
			)
		)
	),

	// Настройки рекламы от CreativeMotion
	'render_adverts'       => true,
	// Показывать рекламу CreativeMotion в админке Wordpress?
	'adverts_settings'     => array(
		'dashboard_widget' => true,
		// если true, показывать виджет новостей на страницу Dashboard
		'right_sidebar'    => true,
		// если true, показывать виджет в правом сайбаре интерфейса плагина
		'notice'           => true,
		// если true, показывать сквозное уведомление на всех страницах админ панели Wordpress
	),

	// Подключаемые модуль фреймворка
	// Необходимые для ускоренной разработки продуктов Webcrfatic
	'load_factory_modules' => array(
		array( 'libs/factory/bootstrap', 'factory_bootstrap_433', 'admin' ),
		// Модуль позволяет использовать различные js виджеты и стили оформление форм.
		array( 'libs/factory/forms', 'factory_forms_430', 'admin' ),
		// Модуль позволяет быстро создавать формы и готовые поля настроек
		array( 'libs/factory/pages', 'factory_pages_432', 'admin' ),
		// Модуль позволяет создавать страницы плагина, в том числе шаблонизированные страницы
		array( 'libs/factory/freemius', 'factory_freemius_120', 'all' ),
		// Модуль для работы с freemius.com, содержит api библиотеку и провайдеры для премиум менеджера
		array( 'libs/factory/adverts', 'factory_adverts_112', 'admin' ),
		// Модуль для показа рекламы в админпанели Wordpress, вся реклама вытягивается через API Creative Motion
		array( 'libs/factory/feedback', 'factory_feedback_106', 'admin' ),
	)
);

$wapt_compatibility = new Wbcr_Factory433_Requirements( __FILE__, array_merge( $plugin_info, array(
	'plugin_already_activate' => defined( 'WAPT_PLUGIN_ACTIVE' ),
	'required_php_version'    => '5.4',
	'required_wp_version'     => '4.2.0',
	//'required_clearfy_check_component' => false
) ) );

/**
 * If the plugin is compatible, then it will continue its work, otherwise it will be stopped,
 * and the user will throw a warning.
 */
if ( ! $wapt_compatibility->check() ) {
	return;
}

/********************************************/

// Устанавливает статус плагина, как активный
define( 'WAPT_PLUGIN_ACTIVE', true );
// Версия плагина
define( 'WAPT_PLUGIN_VERSION', $wapt_compatibility->get_plugin_version() );

define( 'WAPT_PLUGIN_FILE', __FILE__ );
define( 'WAPT_ABSPATH', dirname( __FILE__ ) );
define( 'WAPT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WAPT_PLUGIN_SLUG', dirname( plugin_basename( __FILE__ ) ) );
// Ссылка к директории плагина
define( 'WAPT_PLUGIN_URL', plugins_url( null, __FILE__ ) );
// Директория плагина
define( 'WAPT_PLUGIN_DIR', dirname( __FILE__ ) );



/**
 * -----------------------------------------------------------------------------
 * PLUGIN INIT
 * -----------------------------------------------------------------------------
 */
require_once( WAPT_PLUGIN_DIR . '/libs/factory/core/boot.php' );
require_once( WAPT_PLUGIN_DIR . '/includes/class-wapt-plugin.php' );
require_once( WAPT_PLUGIN_DIR . '/includes/image-search/boot.php' );

try {
	new WAPT_Plugin( __FILE__, array_merge( $plugin_info, array(
		'plugin_version'     => WAPT_PLUGIN_VERSION,
		'plugin_text_domain' => $wapt_compatibility->get_text_domain()
	) ) );
	auto_post_thumbnails();
} catch ( Exception $e ) {
	global $wapt_exeption;

	$wapt_exeption = $e;
	// Plugin wasn't initialized due to an error
	define( 'WAPT_PLUGIN_THROW_ERROR', true );

	function wapt_exception_notice() {
		global $wapt_exeption;

		$error = sprintf( "The %s plugin has stopped. <b>Error:</b> %s Code: %s", 'Auto Featured Image', $wapt_exeption->getMessage(), $wapt_exeption->getCode() );
		echo '<div class="notice notice-error"><p>' . $error . '</p></div>';
	}

	add_action( 'admin_notices', 'wapt_exception_notice' );
	add_action( 'network_admin_notices', 'wapt_exception_notice' );
}
// @formatter:on

/**
 * Get instance of the core class.
 *
 * @return AutoPostThumbnails
 */
function auto_post_thumbnails() {
	require_once( WAPT_PLUGIN_DIR . '/includes/class-wapt-base.php' );
	require_once( WAPT_PLUGIN_DIR . '/includes/class-wapt-image.php' );

	return AutoPostThumbnails::instance();
}
