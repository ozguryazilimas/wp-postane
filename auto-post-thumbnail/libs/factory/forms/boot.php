<?php
/**
 * Factory Forms
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.1
 * @package       factory-forms
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

// the module provides function for the admin area only

if( !is_admin() ) {
	return;
}

// checks if the module is already loaded in order to
// prevent loading the same version of the module twice.
if( defined('FACTORY_FORMS_430_LOADED') ) {
	return;
}

define('FACTORY_FORMS_430_LOADED', true);

define('FACTORY_FORMS_430_VERSION', '4.3.0');

// absolute path and URL to the files and resources of the module.
define('FACTORY_FORMS_430_DIR', dirname(__FILE__));
define('FACTORY_FORMS_430_URL', plugins_url(null, __FILE__));

#comp merge
require_once(FACTORY_FORMS_430_DIR . '/includes/providers/value-provider.interface.php');
require_once(FACTORY_FORMS_430_DIR . '/includes/providers/meta-value-provider.class.php');
require_once(FACTORY_FORMS_430_DIR . '/includes/providers/options-value-provider.class.php');

require_once(FACTORY_FORMS_430_DIR . '/includes/form.class.php');
#endcomp

load_plugin_textdomain('wbcr_factory_forms_430', false, dirname(plugin_basename(__FILE__)) . '/langs');

/**
 * We add this code into the hook because all these controls quite heavy. So in order to get better perfomance,
 * we load the form controls only on pages where the forms are created.
 *
 * @since 3.0.7
 * @see   the 'wbcr_factory_forms_430_register_controls' hook
 *
 */
if( !function_exists('wbcr_factory_forms_430_register_default_controls') ) {

	/**
	 * @param Wbcr_Factory432_Plugin $plugin
	 *
	 * @throws Exception
	 */
	function wbcr_factory_forms_430_register_default_controls(Wbcr_Factory432_Plugin $plugin)
	{

		if( $plugin && !isset($plugin->forms) ) {
			throw new Exception("The module Factory Forms is not loaded for the plugin '{$plugin->getPluginName()}'.");
		}

		require_once(FACTORY_FORMS_430_DIR . '/includes/html-builder.class.php');
		require_once(FACTORY_FORMS_430_DIR . '/includes/form-element.class.php');
		require_once(FACTORY_FORMS_430_DIR . '/includes/control.class.php');
		require_once(FACTORY_FORMS_430_DIR . '/includes/complex-control.class.php');
		require_once(FACTORY_FORMS_430_DIR . '/includes/holder.class.php');
		require_once(FACTORY_FORMS_430_DIR . '/includes/control-holder.class.php');
		require_once(FACTORY_FORMS_430_DIR . '/includes/custom-element.class.php');
		require_once(FACTORY_FORMS_430_DIR . '/includes/form-layout.class.php');

		// registration of controls
		$plugin->forms->registerControls([
			[
				'type' => 'checkbox',
				'class' => 'Wbcr_FactoryForms430_CheckboxControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/checkbox.php'
			],
			[
				'type' => 'list',
				'class' => 'Wbcr_FactoryForms430_ListControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/list.php'
			],
			[
				'type' => 'dropdown',
				'class' => 'Wbcr_FactoryForms430_DropdownControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/dropdown.php'
			],
			[
				'type' => 'dropdown-and-colors',
				'class' => 'Wbcr_FactoryForms430_DropdownAndColorsControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/dropdown-and-colors.php'
			],
			[
				'type' => 'hidden',
				'class' => 'Wbcr_FactoryForms430_HiddenControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/hidden.php'
			],
			[
				'type' => 'hidden',
				'class' => 'Wbcr_FactoryForms430_HiddenControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/hidden.php'
			],
			[
				'type' => 'radio',
				'class' => 'Wbcr_FactoryForms430_RadioControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/radio.php'
			],
			[
				'type' => 'radio-colors',
				'class' => 'Wbcr_FactoryForms430_RadioColorsControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/radio-colors.php'
			],
			[
				'type' => 'textarea',
				'class' => 'Wbcr_FactoryForms430_TextareaControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/textarea.php'
			],
			[
				'type' => 'textbox',
				'class' => 'Wbcr_FactoryForms430_TextboxControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/textbox.php'
			],
			[
				'type' => 'multiple-textbox',
				'class' => 'Wbcr_FactoryForms430_MultipleTextboxControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/multiple-textbox.php'
			],
			[
				'type' => 'datetimepicker-range',
				'class' => 'Wbcr_FactoryForms430_DatepickerRangeControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/datepicker-range.php'
			],
			[
				'type' => 'url',
				'class' => 'Wbcr_FactoryForms430_UrlControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/url.php'
			],
			[
				'type' => 'wp-editor',
				'class' => 'Wbcr_FactoryForms430_WpEditorControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/wp-editor.php'
			],
			[
				'type' => 'color',
				'class' => 'Wbcr_FactoryForms430_ColorControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/color.php'
			],
			[
				'type' => 'color-and-opacity',
				'class' => 'Wbcr_FactoryForms430_ColorAndOpacityControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/color-and-opacity.php'
			],
			[
				'type' => 'gradient',
				'class' => 'Wbcr_FactoryForms430_GradientControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/gradient.php'
			],
			[
				'type' => 'font',
				'class' => 'Wbcr_FactoryForms430_FontControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/font.php'
			],
			[
				'type' => 'google-font',
				'class' => 'Wbcr_FactoryForms430_GoogleFontControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/google-font.php'
			],
			[
				'type' => 'pattern',
				'class' => 'Wbcr_FactoryForms430_PatternControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/pattern.php'
			],
			[
				'type' => 'integer',
				'class' => 'Wbcr_FactoryForms430_IntegerControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/integer.php'
			],
			[
				'type' => 'control-group',
				'class' => 'Wbcr_FactoryForms430_ControlGroupHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/control-group.php'
			],
			[
				'type' => 'paddings-editor',
				'class' => 'Wbcr_FactoryForms430_PaddingsEditorControl',
				'include' => FACTORY_FORMS_430_DIR . '/controls/paddings-editor.php'
			],
		]);

		// registration of control holders
		$plugin->forms->registerHolders([
			[
				'type' => 'tab',
				'class' => 'Wbcr_FactoryForms430_TabHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/tab.php'
			],
			[
				'type' => 'tab-item',
				'class' => 'Wbcr_FactoryForms430_TabItemHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/tab-item.php'
			],
			[
				'type' => 'accordion',
				'class' => 'Wbcr_FactoryForms430_AccordionHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/accordion.php'
			],
			[
				'type' => 'accordion-item',
				'class' => 'Wbcr_FactoryForms430_AccordionItemHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/accordion-item.php'
			],
			[
				'type' => 'control-group',
				'class' => 'Wbcr_FactoryForms430_ControlGroupHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/control-group.php'
			],
			[
				'type' => 'control-group-item',
				'class' => 'Wbcr_FactoryForms430_ControlGroupItem',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/control-group-item.php'
			],
			[
				'type' => 'form-group',
				'class' => 'Wbcr_FactoryForms430_FormGroupHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/form-group.php'
			],
			[
				'type' => 'more-link',
				'class' => 'Wbcr_FactoryForms430_MoreLinkHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/more-link.php'
			],
			[
				'type' => 'div',
				'class' => 'Wbcr_FactoryForms430_DivHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/div.php'
			],
			[
				'type' => 'columns',
				'class' => 'Wbcr_FactoryForms430_ColumnsHolder',
				'include' => FACTORY_FORMS_430_DIR . '/controls/holders/columns.php'
			]
		]);

		// registration custom form elements
		$plugin->forms->registerCustomElements([
			[
				'type' => 'html',
				'class' => 'Wbcr_FactoryForms430_Html',
				'include' => FACTORY_FORMS_430_DIR . '/controls/customs/html.php',
			],
			[
				'type' => 'separator',
				'class' => 'Wbcr_FactoryForms430_Separator',
				'include' => FACTORY_FORMS_430_DIR . '/controls/customs/separator.php',
			],
		]);

		// registration of form layouts
		$plugin->forms->registerFormLayout([
			'name' => 'bootstrap-3',
			'class' => 'Wbcr_FactoryForms430_Bootstrap3FormLayout',
			'include' => FACTORY_FORMS_430_DIR . '/layouts/bootstrap-3/bootstrap-3.php'
		]);
	}

	add_action('wbcr_factory_forms_430_register_controls', 'wbcr_factory_forms_430_register_default_controls');
}