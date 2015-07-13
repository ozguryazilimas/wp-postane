<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WM_Simple_Captcha_Admin' ) ) {
	class WM_Simple_Captcha_Admin{
		
		const TD = 'wmsimplecaptchakey'; 
		
		var $options_page = NULL;
		var $notice		  = '';
		
		function __construct($filename) {
			global $wmsc_options;
			if (is_admin()) {	
				
				$this->plugin_name = "Simple Captcha";			
				
				
				add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
				
				if((isset($_GET['page']) && $_GET['page'] == "wmsimplecaptcha_settings_page") || (isset($_REQUEST['option_page']) && $_REQUEST['option_page'] == "wmsimplecaptcha")){
					add_action( 'admin_init',			array( &$this, 'font_exist' ) ); 
					add_action( 'admin_notices',		array( &$this, 'admin_notices' ) ); 
					add_action( 'admin_init',			array( &$this, 'init_settings' ) ); // Registers settings
					
					add_action('admin_print_scripts', 	array( $this, 'wp_gear_manager_admin_scripts'));
					add_action('admin_print_styles', 	array( $this, 'wp_gear_manager_admin_styles'));				
				}
												
				// Settings Link for Plugin page
				add_filter( 'plugin_action_links_' . WM_SIMPLE_CAPTCHA_NAME, array( $this, 'wmsimplecaptcha_add_action_link' ), 9, 4 );
				add_action( "after_plugin_row_" . WM_SIMPLE_CAPTCHA_NAME, array($this, 'wmsimplecaptcha_plugin_row'), 1, 2 );
			}
		}
		
		function wp_gear_manager_admin_scripts() {
		   	wp_register_script('wmsimplecaptcha_scripts_admin', WM_SIMPLE_CAPTCHA_URL.'/assets/js/wmsimplecaptcha_scripts_admin.js',  array('jquery'));		  
		   	wp_enqueue_script('wmsimplecaptcha_scripts_admin');			
		}
		
		function wp_gear_manager_admin_styles() {
			//wp_enqueue_style('thickbox');
		}		
		
		public function init_settings() {
			
			$option = 'wmsimplecaptcha';		
			// Create option in wp_options.
			if ( false == get_option( $option ) ) {
				add_option( $option );
			}
			
			$adj = array(
						"0.1"=>"0.1"
						,"0.2"=>"0.2"
						,"0.3"=>"0.3"
						,"0.4"=>"0.4"
						,"0.5"=>"0.5"
						,"0.6"=>"0.6"
						,"0.7"=>"0.7"
						,"0.8"=>"0.8"
						,"0.9"=>"0.9"
						,"1.0"=>"1.0"
						,"1.1"=>"1.1"
						,"1.2"=>"1.2"
						,"1.3"=>"1.3"
						,"1.4"=>"1.4"
						,"1.5"=>"1.5"
						,"1.6"=>"1.6"
						,"1.7"=>"1.7"
						,"1.8"=>"1.8"
						,"1.9"=>"1.9"
						,"2.0"=>"2.0"
						);
			
			$border_style = array("none"=>"None", "hidden"=>"Hidden", "dotted"=>"Dotted", "dashed"=>"Dashed", "solid"=>"Solid", "double"=>"Double", "groove"=>"Groove", "ridge"=>"Ridge", "inset"=>"Inset", "outset"=>"Outset", "initial"=>"Initial", "inherit"=>"Inherit");			
			// Section.
			
			add_settings_section('captcha_preview_settings',			__( 'Captcha Preview:', 'wmsimplecaptcha' ),				array( &$this, 'section_options_callback' ),	$option);		
			add_settings_field('captcha_preview',						__( 'Captcha Preview:', 'wmsimplecaptcha' ),				array( &$this, 'captcha_preview_callback' ),	$option, 'captcha_preview_settings', array('menu'=> $option));
			
			add_settings_section('plugin_settings',						__( 'Captcha Image Settings:', 'wmsimplecaptcha' ),				array( &$this, 'section_options_callback' ),	$option);		
			add_settings_field('captcha_enable_registration',			__( 'Enable Captcha Registraion Page:', 'wmsimplecaptcha' ),	array( &$this, 'checkbox_element_callback' ),	$option, 'plugin_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'0',	'label_for'=>'captcha_enable_registration',		'id'=> 'captcha_enable_registration',	'default'=>0));
			add_settings_field('captcha_image_width',					__( 'Captcha Width:', 'wmsimplecaptcha' ),						array( &$this, 'select_element_callback' ),		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'3',	'label_for'=>'captcha_image_width',				'id'=> 'captcha_image_width',			'default'=>30,		'options'=>$this->number_array(20,750),'first_option'=>"Select Width"));
			add_settings_field('captcha_image_height',					__( 'Captcha Height:', 'wmsimplecaptcha' ),						array( &$this, 'select_element_callback' ),		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'2',	'label_for'=>'captcha_image_height',			'id'=> 'captcha_image_height',			'default'=>25,		'options'=>$this->number_array(20,250),'first_option'=>"Select Height"));
			add_settings_field('captcha_image_characters',				__( 'Captcha Characters:', 'wmsimplecaptcha' ),					array( &$this, 'select_element_callback' ),		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'1',	'label_for'=>'captcha_image_characters',		'id'=> 'captcha_image_characters',		'default'=>1,		'options'=>$this->number_array(1,20),'first_option'=>"Select Characters"));
			add_settings_field('captcha_image_font_adj',				__( 'Captcha Font Adjustment:', 'wmsimplecaptcha' ),			array( &$this, 'select_element_callback' ),		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'1',	'label_for'=>'captcha_image_font_adj',			'id'=> 'captcha_image_font_adj',		'default'=>0.6,		'options'=>$adj,'first_option'=>"Select Adjustment"));
			add_settings_field('captcha_enable_space',					__( 'Enable Space:', 'wmsimplecaptcha' ),						array( &$this, 'checkbox_element_callback' ),	$option, 'plugin_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'0',	'label_for'=>'captcha_enable_space',			'id'=> 'captcha_enable_space',			'default'=>0));
			add_settings_field('captcha_image_font',					__( 'Captcha Font:', 'wmsimplecaptcha' ),						array( &$this, 'select_element_callback' ), 	$option, 'plugin_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'20',	'label_for'=>'captcha_image_font',				'id'=> 'captcha_image_font',			'default'=>'arial.ttf',	'options'=>$this->fonts()));
		  	add_settings_field('captcha_possible_letters',				__( 'Captcha Possible Letters:', 'wmsimplecaptcha' ),			array( &$this, 'select_element_callback' ),		$option, 'plugin_settings', array('menu'=> $option,	'size'=>80, 'class'=>'', 'maxlength'=>'70',	'label_for'=>'captcha_possible_letters',		'id'=> 'captcha_possible_letters',		'default'=>'23456789',	'options'=>$this->captcha_possible_letters()));
			add_settings_field('captcha_random_dots',					__( 'Captcha Random Dots:', 'wmsimplecaptcha' ),				array( &$this, 'select_element_callback' ),		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15,	'class'=>'', 'maxlength'=>'1',	'label_for'=>'captcha_random_dots',				'id'=> 'captcha_random_dots',			'default'=>0,		'options'=>$this->number_array(0,999),'first_option'=>"Select Dots"));
			add_settings_field('captcha_random_lines',					__( 'Captcha Random Lines:', 'wmsimplecaptcha' ),				array( &$this, 'select_element_callback' ),		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15,	'class'=>'', 'maxlength'=>'1',	'label_for'=>'captcha_random_lines',			'id'=> 'captcha_random_lines',			'default'=>0,		'options'=>$this->number_array(0,999),'first_option'=>"Select line"));
			add_settings_field('captcha_text_color',					__( 'Captcha Text Color:', 'wmsimplecaptcha' ),					array( &$this, 'color_picker_callback' ), 		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15,	'class'=>'color_picker_callback', 			'maxlength'=>'7',	'label_for'=>'captcha_text_color',				'id'=> 'captcha_text_color',			'default'=>'#ffffff'));
			add_settings_field('captcha_dots_color',					__( 'Captcha Dots Color:', 'wmsimplecaptcha' ),					array( &$this, 'color_picker_callback' ), 		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15,	'class'=>'color_picker_callback',			'maxlength'=>'7',	'label_for'=>'captcha_dots_color',				'id'=> 'captcha_dots_color',			'default'=>'#27d141'));
			add_settings_field('captcha_line_color',					__( 'Captcha Lines Color:', 'wmsimplecaptcha' ),				array( &$this, 'color_picker_callback' ), 		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15,	'class'=>'color_picker_callback',			'maxlength'=>'7',	'label_for'=>'captcha_line_color',				'id'=> 'captcha_line_color',			'default'=>'#ff2d2d'));
			add_settings_field('captcha_background_color',				__( 'Captcha Background Color:', 'wmsimplecaptcha' ),			array( &$this, 'color_picker_callback' ), 		$option, 'plugin_settings', array('menu'=> $option,	'size'=>15,	'class'=>'color_picker_callback', 			'maxlength'=>'7',	'label_for'=>'captcha_background_color',		'id'=> 'captcha_background_color',		'default'=>'#f4f4f4'));
			
			add_settings_section('captcha_form_settings',				__( 'Form Settings:', 'wmsimplecaptcha' ),						array( &$this, 'section_options_callback' ),	$option);				
			add_settings_field('captcha_label',							__( 'Captcha Label:', 'wmsimplecaptcha' ),						array( &$this, 'text_element_callback' ), 		$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>48, 'class'=>'',						'maxlength'=>'100',	'label_for'=>'captcha_label',					'id'=> 'captcha_label',					'default'=>'Security Code'));
			
			
			add_settings_field('captcha_enable_border',					__( 'Enable Border:', 'wmsimplecaptcha' ),						array( &$this, 'checkbox_element_callback' ),	$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>15, 									'maxlength'=>'0',	'label_for'=>'captcha_enable_border',			'id'=> 'captcha_enable_border',			'default'=>0));
			add_settings_field('captcha_border_width',					__( 'Captcha Border width:', 'wmsimplecaptcha' ),				array( &$this, 'select_element_callback' ),		$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 						'maxlength'=>'1',	'label_for'=>'captcha_border_width',			'id'=> 'captcha_border_width',			'default'=>0,		'options'=>$this->number_array(0,10),'first_option'=>"Select Characters"));
			
			add_settings_field('captcha_border_type',					__( 'Captcha Border type:', 'wmsimplecaptcha' ),				array( &$this, 'select_element_callback' ),		$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 						'maxlength'=>'1',	'label_for'=>'captcha_border_type',				'id'=> 'captcha_border_type',			'default'=>0,		'options'=>$border_style,'first_option'=>"Select Border Type"));
			add_settings_field('captcha_border_color',					__( 'Captcha Border Color:', 'wmsimplecaptcha' ),				array( &$this, 'color_picker_callback' ), 		$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>15, 'class'=>'color_picker_callback', 	'maxlength'=>'7',	'label_for'=>'captcha_border_color',			'id'=> 'captcha_border_color',			'default'=>'#c4c4c4'));
			
			add_settings_field('captcha_enable_refresh_image',			__( 'Enable Refresh Button on image:', 'wmsimplecaptcha' ),		array( &$this, 'checkbox_element_callback' ),	$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>15, 'class'=>'',						'maxlength'=>'0',	'label_for'=>'captcha_enable_refresh_image',	'id'=> 'captcha_enable_refresh_image',	'default'=>0));
			add_settings_field('captcha_enable_refresh',				__( 'Enable Refresh Button:', 'wmsimplecaptcha' ),				array( &$this, 'checkbox_element_callback' ),	$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 						'maxlength'=>'0',	'label_for'=>'captcha_enable_refresh',			'id'=> 'captcha_enable_refresh',		'default'=>0));
			add_settings_field('captcha_refresh_image',					__( 'Captcha Refresh Image:', 'wmsimplecaptcha' ),				array( &$this, 'choose_image_callback' ), 		$option, 'captcha_form_settings', array('menu'=> $option,	'size'=>48, 'class'=>'', 						'maxlength'=>'100',	'label_for'=>'captcha_refresh_image',			'id'=> 'captcha_refresh_image',			'default'=>'', 'choose_id'=>'upload_refresh_image_button'));
	
			add_settings_section('captcha_error_settings',				__( 'Form Error:', 'wmsimplecaptcha' ),							array( &$this, 'section_options_callback' ),	$option);
			add_settings_field('captcha_empty',							__( 'Captcha Empty:', 'wmsimplecaptcha' ),						array( &$this, 'text_element_callback' ), 		$option, 'captcha_error_settings', array('menu'=> $option,	'size'=>48, 'class'=>'',						'maxlength'=>'200',	'label_for'=>'captcha_empty',					'id'=> 'captcha_empty',					'default'=>'Please enter security code.'));
			add_settings_field('captcha_invalid',						__( 'Captcha Invalid:', 'wmsimplecaptcha' ),					array( &$this, 'text_element_callback' ), 		$option, 'captcha_error_settings', array('menu'=> $option,	'size'=>48, 'class'=>'', 						'maxlength'=>'200',	'label_for'=>'captcha_invalid',					'id'=> 'captcha_invalid',				'default'=>'Please enter valid security code.'));
			
			
			
			add_settings_section('custom_css_settings',				__( 'CSS Settings:', 'wmsimplecaptcha' ),							array( &$this, 'section_options_callback' ),	$option);
			add_settings_field('captcha_enable_css',				__( 'Enable Plugin External CSS:', 'wmsimplecaptcha' ),				array( &$this, 'checkbox_element_callback' ),	$option, 'custom_css_settings', array('menu'=> $option,	'size'=>15, 'class'=>'', 'maxlength'=>'0',	'label_for'=>'captcha_enable_css',		'id'=> 'captcha_enable_css',								'default'=>0));
			add_settings_field('captcha_custom_css',				__( 'Custom CSS:', 'wmsimplecaptcha' ),								array( &$this, 'textarea_element_callback' ), 	$option, 'custom_css_settings', 	array('menu'=> $option,	'cols'=>'45', 'class'=>'', 'rows'=>5,	'label_for'=>'captcha_custom_css',			'id'=> 'captcha_custom_css',								'default'=>''));
			
			// Register settings.
			register_setting( $option, $option, array( &$this, 'wmsimplecaptcha_options_validate' ) );
	   }
	   
	   /**
		 * Section null callback.
		 *
		 * @return void.
		 */
		public function section_options_callback(){}
		
		/**
		 * Validate/sanitize options input
		 */
		public function wmsimplecaptcha_options_validate( $input ) {
			// Create our array for storing the validated options.
			$output = array();
	
			// Loop through each of the incoming options.
			foreach ( $input as $key => $value ) {
	
				// Check to see if the current option has a value. If so, process it.
				if ( isset( $input[$key] ) ) {
	
					// Strip all HTML and PHP tags and properly handle quoted strings.
					$output[$key] = strip_tags( stripslashes( $input[$key] ) );
				}
			}
	
			// Return the array processing any additional functions filtered by this action.
			return apply_filters( 'wmsimplecaptcha_validate_input', $output, $input );
		}
		
		/**
		 * Text field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string      Text field.
		 */
		public function text_element_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s%6$s%7$s />', $id, $menu, $current, $size, $disabled, $class, $maxlength);
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html;
		}
		
		public function textarea_element_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$cols 		= isset( $args['cols'] ) 		? $args['cols'] : '10';
			$rows 		= isset( $args['rows'] ) 		? $args['rows'] : '5';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			//$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s%6$s%7$s />', $id, $menu, $current, $size, $disabled, $class, $maxlength);
			$html = sprintf( '<textarea id="%1$s" name="%2$s[%1$s]" cols="%3$s" rows="%4$s" %5$s>%6$s</textarea>', $id, $menu, $cols, $rows, $class, $current);
		
		
			
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html;
		}
		
		
		
		/**
		 * Checkbox field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string      Checkbox field.
		 */
		public function checkbox_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s/>', $id, $menu, checked( 1, $current, false ), $disabled );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
				
			echo $html;
		}
		
		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		public function select_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			
			$options = get_option( $menu );
			
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
			
			$first_option	= isset( $args['first_option'] ) ? $args['first_option']: '';
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			
			$html = sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled );
			//$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), $first_option );
			
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
			}
			$html .= sprintf( '</select>' );
	
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
	
			
			echo $html;
		}
		
		
		public function captcha_preview_callback($args){
			//$this->print_array($_SESSION['wmsc_options']);
			echo '<img src="'.WM_SIMPLE_CAPTCHA_CODE_URL."?rand=".rand(2,5).'" class="captcha_code_img" id="captcha_code_file"  />';
		}
		
	
		public function color_picker_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="_'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';
			$options	= get_option( $menu );
			$default 	= isset( $args['default'] ) ? $args['default'] : '';
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = $default;
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s"  size="%4$s"%5$s%6$s%7$s data-default-color="%8$s" />', $id, $menu, $current, $size, $disabled, $class, $maxlength, $default);
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html;
		}
		
		public function choose_image_callback( $args ) {
			$menu 		= $args['menu'];
			$id 		= $args['id'];
			$size 		= isset( $args['size'] ) 		? $args['size'] : '25';
			$class 		= isset( $args['class'] ) 		? ' class="'.$args['class'] .'"': '';
			$maxlength	= isset( $args['maxlength'] ) 	? ' maxlength="'.$args['maxlength'] .'"': '';
			
			
			
			$choose_id 		= isset( $args['choose_id'] ) 			? $args['choose_id'] 	: $id;
			$choose_class	= isset( $args['choose_class'] ) 		? ' '.$args['choose_class']: '';
			$choose_data	= isset( $args['choose_data'] ) 		? $args['choose_data'] 	: 'Choose a Image';
			$choose_update 	= isset( $args['choose_update'] ) 		? $args['choose_update'] 	: 'Set as Refresh image';
			$choose_label	= isset( $args['choose_label'] ) 		? $args['choose_label'] 	: 'Choose Image';
			
			$options	= get_option( $menu );
					
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = "";
			$html .= sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s"  size="%4$s"%5$s%6$s%7$s />', $id, $menu, $current, $size, $disabled, $class, $maxlength);			
			$html .= sprintf( '<a id="%1$s" class="button button-primary%2$s" data-choose="%3$s" data-update="%4$s">%5$s</a>',$choose_id,$choose_class,$choose_data, $choose_update, $choose_label);

			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}	
		
			echo $html;
		}
		
		
		
		
		function number_array($from = 1, $to = 9999, $inc = 1){
			$array = array();
			for($i=$from;$i<=($to);$i++){
				$array[$i] = $i;
			}
			return $array; 
		}
		
		//1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
		function captcha_possible_letters($array = array()){
			$n 					= apply_filters("wmsimplecaptcha_possible_number",	"23456789");
			$s 					= apply_filters("wmsimplecaptcha_possible_letters_small",	"abcdefghjkmnpqrstuvwxyz");
			$c 					= apply_filters("wmsimplecaptcha_possible_letters_capital",	"ABCDEFGHJKLMNPQRSTUVWXYZ");
			$array[$n] 			= "Number Only";
			$array[$n.$s] 		= "Number and Small Letters";
			$array[$n.$c] 		= "Number and Capital Letters";
			$array[$n.$s.$c] 	= "Number, Small and Capital Letters";			
			$array[$s] 			= "Small Letters Only";
			$array[$c] 			= "Capital Letters Only";
			$array[$c.$c] 		= "Small and Capital Letters";
			return $array; 
		}
		
		
		public function fonts(){
			$fontspath = glob(WM_SIMPLE_CAPTCHA_NEW_FONT_PATH."*.ttf");
			$fonts = array();
			if(count($fontspath)>0)
			foreach($fontspath as $font):
				$font_name =  str_replace(WM_SIMPLE_CAPTCHA_NEW_FONT_PATH,"",$font);
				$fonts[$font_name] = $font_name;
			endforeach;
			return $fonts;
		}
		
		
		public function admin_menu(){			
			$this->options_page = $page = $this->add_options_page(__( $this->plugin_name.' Settings', self::TD ),	__( 'WM Simple Captcha', self::TD ),'manage_options','wmsimplecaptcha_settings_page',array( $this, 'wmsimplecaptcha_settings_page' ));
			add_action("load-$page", array($this, 'admin_load'));
			add_action("load-$page", array($this, 'handle_upload'), 50);
			add_action("load-$page", array($this, 'handle_feedback_form'), 55);
		}
		
		function add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
			return add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $menu_slug, $function );
		}
		
		//function wmsimplecaptcha_add_action_link($plugin_links, $file){
		function wmsimplecaptcha_add_action_link($actions, $plugin_file, $plugin_data, $context){
		
				if ( $plugin_file == WM_SIMPLE_CAPTCHA_NAME) {
					// Remove edit link. if you want to remove selective use if statement
					//if ( array_key_exists( 'edit', $actions ) )	unset( $actions['edit'] );					
					
					$settings_link = array();
					$settings_link[] = '<a href="'.admin_url('admin.php?page=wmsimplecaptcha_settings_page').'" title="'.__('WM Captcha Settings', self::TD).'">'.__('Settings', self::TD).'</a>';
					if(!get_option('users_can_register')){
						$settings_link[] = '<a href="'.admin_url('options-general.php').'" title="'.__('Allow Membership Anyone can register', self::TD).'">'.__('Allow Membership Anyone can register', self::TD).'</a>';
					}
					return array_merge( $actions, $settings_link );
				}		
				return $plugin_links;
		}
		
		public function wmsimplecaptcha_plugin_row( $file, $plugin_data){
			
			$msg = get_option( 'plugin_err_' . WM_SIMPLE_CAPTCHA_NAME, false );
						
			$wp_list_table = _get_list_table('WP_Plugins_List_Table');
				
			if ( !empty( $msg ) ) {		
				echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message" style="border:1px solid #cf0000">';		
				echo $msg;		
				echo '</div></td></tr>';
			}
			
			if(!get_option('users_can_register')){
				echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message" style="border-color:#cf0000"><span style="color:#cf0000">';
				echo sprintf( __('Signup has been disabled. There is such an option, just change your settings on the <strong><a href="'.admin_url('options-general.php').'">Settings</strong></a> » <a href="'.admin_url('options-general.php').'"><strong>General</strong></a> » <strong>Membership</strong>: [ ] Anyone can register. If ticked([✓]), anybody can become a user, preferably with the subscriber role' ));				
				echo '</span></div></td></tr>';	
				return;
			}
		}		
		
		public function wmsimplecaptcha_settings_page(){
			global $wmsc_options;
			$upload_fonts	= apply_filters('wmsimplecaptcha_enable_upload_fonts', true);
			$tab = isset($_GET['tab']) ? $_GET['tab'] : 'captcha_setting';
			
			?>
            	<div class="wrap wmsimplecaptcha">
                	<?php
                    	echo "<h2>".$this->plugin_name.' settings'."</h2>";
						$this->admin_tabs($tab);
						switch ($tab) {
							case 'captcha_setting':
								$this->captcha_setting();
								break;
							case 'font_upload':
								$this->font_upload();
								break;
							case 'feedback_form':
								$this->feedback_form();
								break;
						}
					?>
                </div>
            <?php
		}
		
		function captcha_setting(){
			?>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'wmsimplecaptcha' );
                    do_settings_sections( 'wmsimplecaptcha' );
                    submit_button();
                ?>
            </form>
            <?php
			//unset($_SESSION['wmsc_options']);
		}
		
		function font_upload(){
			?>
             <form enctype="multipart/form-data" id="upload-form" class="wp-upload-form" method="post">
                 <p>
                    <label for="upload"><?php _e( 'Choose an font from your computer:' ); ?></label><br />
                    <input type="file" id="upload" name="import" class="" />
                    <input type="hidden" name="action" value="save" />
                    <?php wp_nonce_field( 'custom-font-upload', '_wpnonce-custom-font-upload' ); ?>
                    <?php submit_button( __( 'Upload' ), 'primary', 'submit', false ); ?>
                </p>                    
            </form>
            <?php
		}
		
		function feedback_form(){
			
			?>
             <form method="post">
             	<?php wp_nonce_field( 'custom_feedback_form', '_wpnonce-custom_feedback_form' ); ?>
                <input type="hidden" name="action" value="send_feed_back" />
             	<table class="form-table">
                	<tr>
                    	<th><label for="note"><?php _e( 'Note:' ); ?></label></th>
                        <td><textarea name="txt_feed_back" cols="35" rows="5"></textarea></td>
                    </tr>
                    <tr>
                    	<td colspan="2">
                        	 <?php submit_button(); ?>
                        </td>
                    </tr>
                </table>
            </form>
            <?php
		}
		
		function handle_feedback_form(){
			$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
			
			if($action != 'send_feed_back') return;
			global $current_user;
			
			
			$http_user_agent 	= "";
			$txt_feed_back 	= "";
			if(isset($_SERVER['HTTP_USER_AGENT'])){
				$http_user_agent 	= $_SERVER['HTTP_USER_AGENT'];
			}
			
			if(isset($_REQUEST['txt_feed_back'])){
				$txt_feed_back 	= $_REQUEST['txt_feed_back'];
			}
			
			$args = array(
				'request' 			=> 'feedback_form',
				'plugin_name' 		=> 'WM Simple Captcha', 
				'version' 			=> '1.2',
				'user_email' 		=> $current_user->data->user_email,
				'home_url' 			=> trailingslashit( esc_url( home_url() ) )
				,'http_user_agent' 	=> $http_user_agent 
				,'txt_feed_back' 	=> $txt_feed_back 
			);
			
			$response = wp_remote_post( 'http://localhost/wordpress_38/', array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $args,
				'cookies' => array(),
				'sslverify' => false
			) );
			
			
			
			if( is_wp_error( $response ) || empty( $response['body'] ) || ( false !== strpos( $response['body'], 'Fatal' ) ) ) {
				$data['error'] = true;
				$data['error_msg'] = __( 'Request Error', 'wmsimplecaptcha' );
				$error = esc_html( $data['error_msg'] );
				$this->notice =  '<div class="update-nag">'.$error.'</div>';
			} else {
				$data = $response['body'];
				
				$data =  json_decode( $data ); // json_decode( $data );	
				$this->print_array($data);			
				if(isset($data->error) and isset($data->error_msg) and $data->error == true and strlen($data->error_msg) > 0){
					$this->notice =  '<div class="update-nag">'.$data->error_msg.'</div>';
					return false;
				}
				
				if(isset($data->success) and isset($data->success_msg) and $data->success == true and strlen($data->success_msg) > 0){
					$this->notice =  '<div class="update-nag">'.$data->success_msg.'</div>';
					return false;
				}
				
				
			}
			
			
			
		}
		
		function admin_tabs( $current = 'homepage' ) {
			//$tabs = array( 'captcha_setting' => 'Captcha Settings', 'font_upload' => 'Font Upload', 'feedback_form' => 'Feed Back' );
			$tabs = array( 'captcha_setting' => 'Captcha Settings', 'font_upload' => 'Font Upload');
			echo screen_icon( 'options-general' );
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			echo '<h2 class="nav-tab-wrapper">';
			foreach( $tabs as $tab => $name ){
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?page=$page&tab=$tab'>$name</a>";
		
			}
			echo '</h2>';
		}
		
		function wmcaptcha_display_after_comment($field){
			//$this->wmcaptcha_display();
		}
		
		function print_array($ar = NULL,$display = true){
			if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
			}
		}
		
		function isset_option($name = NULL,$data = NULL, $detault = ""){
			$r = $detault;
			if($data && isset($data[$name])){
				$r = $data[$name];
			}
			
			return $r;
		}
		
		/**
		 * Set up the enqueue for the CSS & JavaScript files.
		 *
		 * @since 3.0.0
		 */
		function admin_load() {
			get_current_screen()->add_help_tab( array(
				'id'      => 'overview',
				'title'   => __('Overview'),
				'content' =>
					'<p>' . __( 'You can customize the look of Captcha without touching any of your theme&#8217;s code by using a WM Simple Captcha.' ) . '</p>' .
					'<p>' . __( 'You can also choose a background color by clicking the Select Color button and either typing in a legitimate HTML hex value, e.g. &#8220;#ff0000&#8221; for red, or by choosing a color using the color picker.' ) . '</p>' .				
					
					'<p>' . __( 'avoided confusing characters (l 1 and i for example).' ) . '</p>'.
					'<p></p>'.
					'<p>' . __( 'Don&#8217;t forget to click on the Save Changes button when you are finished.' ) . '</p>'
			) );
	
			get_current_screen()->set_help_sidebar(
				'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
				'<p>' . __( '<a href="http://www.web-mumbai.com/wm_simple_captcha.php" target="_blank">WM Simple Captcha</a>' ) . '</p>'
			);
	
			wp_enqueue_media();
			wp_enqueue_script('custom-background');
			wp_enqueue_style('wp-color-picker');			
			
		}
		
		function font_exist(){
			
			$font_exists = true;
			$fontspath = glob(WM_SIMPLE_CAPTCHA_NEW_FONT_PATH."*.ttf");
			//$this->print_array($fontspath);				
			if(count($fontspath)<=0){
				//echo count($fontspath);
				$font_exists = false;
				$dir = trailingslashit(WM_SIMPLE_CAPTCHA_NEW_FONT_PATH);
				if (!wp_mkdir_p($dir)){$font_exists = false;};
				
				$fontspath = glob(WM_SIMPLE_CAPTCHA_NEW_FONT_PATH."*.ttf");
				
				//$this->print_array($fontspath);
				
				if(count($fontspath)<=0):
					//if(file_exists(WM_SIMPLE_CAPTCHA_FONT_PATH."arial.ttf")){					
						$fontspath = glob(WM_SIMPLE_CAPTCHA_FONT_PATH."*.ttf");	
						if(count($fontspath)>0){
							foreach($fontspath as $font):
								$new_font =  str_replace(WM_SIMPLE_CAPTCHA_FONT_PATH,"",$font);
								$new_file = WM_SIMPLE_CAPTCHA_NEW_FONT_PATH.$new_font;
								if ( false === @copy($font, $new_file ) ) {
									
								}else{
									if(!$font_exists) $font_exists = true;
								}
							endforeach;
						}else{
							$font_exists = false;
						}
					//}
				endif;
			}
			
			if(!$font_exists):
				$this->notice =  '<div class="update-nag">Please upload fonts.</div>';
			endif;
			//$this->notice =  '<div class="update-nag">Please upload fonts.</div>';
		}//font_exist
		
		function admin_notices(){
			echo $this->notice;
			if(!get_option('users_can_register')){
				echo '<div class="update-nag">Signup has been disabled. There is such an option, just change your settings on the <strong><a href="'.admin_url('options-general.php').'">Settings</strong></a> » <a href="'.admin_url('options-general.php').'"><strong>General</strong></a> » <strong>Membership</strong>: [ ] Anyone can register. If ticked([✓]), anybody can become a user, preferably with the subscriber role</div>';
			}
			
			$activated_plugin_error = get_option('wmsimplecaptcha_activated_plugin_error');
			
			if($activated_plugin_error){
				echo $activated_plugin_error;
			}
		}
		
		/**
		 * Handle an upload for the font.
		 *
		 * @since 3.0.0
		 */
		function handle_upload() {
	
			if ( empty($_FILES) )
				return;
	
			check_admin_referer('custom-font-upload', '_wpnonce-custom-font-upload');
			$overrides = array('test_form' => false);
			
			$uploaded_file = $_FILES['import'];
			
			$dir = trailingslashit(WM_SIMPLE_CAPTCHA_NEW_FONT_PATH);
			if ( ! wp_mkdir_p( $dir ) )	return false;
			
			// Move the file to the plugn fots dir
			$new_file = WM_SIMPLE_CAPTCHA_NEW_FONT_PATH.$uploaded_file['name'];
			$new_file = strtolower($new_file);
			if ( false === @move_uploaded_file( $uploaded_file['tmp_name'], $new_file ) ) {
				//wp_die( sprintf( __('The uploaded file could not be moved' ), '/fonts/' )  );
				$this->notice =  '<div class="The uploaded file could not be moved.</div>';
			}else{
				$this->notice =  '<div class="update-nag">Font file uploded.</div>';
			}
			
			
			
		}// End function handle_upload()
		
		
	}	
}