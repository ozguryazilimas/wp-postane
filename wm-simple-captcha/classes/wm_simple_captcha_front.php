<?php
session_start();

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WM_Simple_Captcha_Front' ) ) {
	class WM_Simple_Captcha_Front{		
		
		var $options_page = NULL;
		
		public function __construct($filename) {
			global $wmsc_options;
			
			$this->define_constant($filename);
			//delete_option('wmsimplecaptcha');
			$wmsc_options 	= $this->default_values();			
			$_SESSION['wmsc_options'] = $wmsc_options;
			
			if (is_admin()) {	
					$this->include_admin();
					$this->settings = new WM_Simple_Captcha_Admin($filename);
					register_deactivation_hook($filename, 	array(&$this, 'deactivate'));	// deactivation functions
					//register_activation_hook($filename, 	array(&$this, 'activate'));		// Activation functions
					//register_uninstall_hook($filename, 		array('WM_Simple_Captcha_Front', 'uninstall') ); 	// Register an uninstall hook to automatically remove options
			}else{
				if($wmsc_options['captcha_enable_registration']){
					//$this->print_array($_SESSION['wmsc_options']);
					add_action( 'register_form', 			array(&$this, 'wmcaptcha_display'),100,1);		
					add_action( 'registration_errors', 		array(&$this, 'registration_captcha_errors'),20,1);	
					add_action( 'init', 					array(&$this, 'wmsimplecaptcha_scripts_front'),20,1);
					
					
					//For login screen
					//add_action( 'login_form', array(&$this, 'wmcaptcha_display'),100,1);
					//add_filter( 'authenticate', array(&$this, 'registration_captcha_errors'),22,1);	
	
				}
			}
			if($wmsc_options['captcha_enable_registration']){
				add_action( 'init', 	array(&$this, 'wmsimplecaptcha_init_enqueue_scripts'));
				add_action( 'wp_ajax_wmsimplecaptcha_action', 			array(&$this, 'wmsimplecaptcha_action'));
				add_action( 'wp_ajax_nopriv_wmsimplecaptcha_action', 	array(&$this, 'wmsimplecaptcha_action'));
			}
		}// End Function __construct()
		
		public function include_admin() {
			include_once( 'wm_simple_captcha_admin.php' );
		}
		function define_constant($filename){			
			$uploads = $this->font_upload_dir('dir');			
			if(!defined('WM_SIMPLE_CAPTCHA_FILE_PATH')) 		define('WM_SIMPLE_CAPTCHA_FILE_PATH', 		dirname( $filename ) );
			if(!defined('WM_SIMPLE_CAPTCHA_DIR_NAME')) 			define('WM_SIMPLE_CAPTCHA_DIR_NAME', 		basename( WM_SIMPLE_CAPTCHA_FILE_PATH ) );
			if(!defined('WM_SIMPLE_CAPTCHA_NAME')) 				define('WM_SIMPLE_CAPTCHA_NAME', 			plugin_basename($filename));
			if(!defined('WM_SIMPLE_CAPTCHA_FOLDER')) 			define('WM_SIMPLE_CAPTCHA_FOLDER', 			dirname(WM_SIMPLE_CAPTCHA_NAME));
			if(!defined('WM_SIMPLE_CAPTCHA_URL')) 				define('WM_SIMPLE_CAPTCHA_URL', 			WP_PLUGIN_URL ."/". WM_SIMPLE_CAPTCHA_DIR_NAME );
			if(!defined('WM_SIMPLE_CAPTCHA_CODE_URL')) 			define('WM_SIMPLE_CAPTCHA_CODE_URL', 		WM_SIMPLE_CAPTCHA_URL ."/captcha_code/captcha_code.php" );	
			if(!defined('WM_SIMPLE_CAPTCHA_FONT_PATH')) 		define('WM_SIMPLE_CAPTCHA_FONT_PATH',		WM_SIMPLE_CAPTCHA_FILE_PATH . '/fonts/');			
			if(!defined('WM_SIMPLE_CAPTCHA_NEW_FONT_PATH')) 	define('WM_SIMPLE_CAPTCHA_NEW_FONT_PATH',	$uploads  . '/wm_simple_captcha_fonts/');			
		}// End Function define_constant()
		
		function registration_captcha_errors( $errors = NULL ) {
			global $wmsc_options;
			
			$err = $this->captcha_errors();			
			if($err){
				//if($errors == NULL) global $errors;
				if($errors == NULL) $errors = new WP_Error();
				$errors = new WP_Error();
				if($err == "empty"){
					$errors->add( 'recaptcha', __( str_replace("ERROR:","<strong>ERROR: </strong>", $wmsc_options['captcha_empty']), 'wmsimplecaptcha' ), 'invalid-site-private-key' );
				}
				
				if($err == "invalid"){
					$errors->add( 'recaptcha', __( str_replace("ERROR:","<strong>ERROR: </strong>", $wmsc_options['captcha_invalid']), 'wmsimplecaptcha' ), 'invalid-site-private-key' );
				}				
			}
			
			return $errors;
		}// End Function registration_captcha_errors()
		
		function login_captcha_errors( $errors_str = NULL ) {
			global $error;
			$err = $this->captcha_errors();
			if($err){
				if($errors_str == NULL) $errors_str = '';
				$errors_str = '';
				if($err == "empty"){
					$errors_str .= $wmsc_options['captcha_empty'];
				}
				
				if($err == "invalid"){
					$errors_str .= $wmsc_options['captcha_invalid'];	
				}				
			}
			
			
			return $errors_str;
		}// End Function login_captcha_errors()
		
		function captcha_errors($errors = NULL){
			if(isset($_REQUEST['captcha_challenge_field'])){
				// session_start();
				$captcha_challenge_field = strtolower(trim($_REQUEST['captcha_challenge_field']));
			
				if(strlen($captcha_challenge_field)<=0){
					$errors = "empty";
				}
				
				if(strlen($captcha_challenge_field)>0){
					if(isset($_SESSION['6_letters_code'])){
						$code = strtolower($_SESSION['6_letters_code']);
						if($code != $captcha_challenge_field){							
							$errors = "invalid";
						}
					}					
				}
			}
			return $errors;
		}// End Function captcha_errors()
		
		function wmcaptcha_display(){
			
			$new_option 				= $this->wmcaptcha_form_values();
			$captcha_custom_template	= apply_filters('wmsimplecaptcha_captcha_custom_template',NULL, $new_option);
			
			if($captcha_custom_template){
				echo $captcha_custom_template;
			}else{
				?>
				<p class="wmcaptcha_box">
					<?php if($new_option['captcha_label']):?>
					<label for="captcha_challenge_field"><?php _e( $new_option['captcha_label'], 'wmsimplecaptcha' ); ?></label>
					<?php endif;?>
					<span class="wmcaptcha_fieldbox">
						<span class="wmcaptcha_fieldbox_input"><input type="text" name="captcha_challenge_field" id="captcha_challenge_field" class="captcha_challenge_field" maxlength="<?php echo $new_option['captcha_image_characters'];?>" /></span>
						<span class="wmcaptcha_fieldbox_img <?php echo $new_option['captcha_refresh_image_class'];?>"><img src="<?php echo $new_option['captcha_url'];?>" class="captcha_code_img" id="captcha_code_file" alt="" /></span>
						<?php if($new_option['captcha_enable_refresh']):?>
							<span class="wmcaptcha_fieldbox_a refresh_button"><?php echo $new_option['refresh_image'];?></span>
						<?php endif;?>
						<span class="wmcaptcha_fieldbox_clearfix clearfix"></span>
					</span>
					<span class="wmcaptcha_clearfix"></span>
				</p>
				<?php
				//echo $new_option['captcha_url'];
			}			
			add_action('wp_footer', 				array(&$this, 'wmsimplecaptcha_wp_footer'));
			add_action('login_footer', 				array(&$this, 'wmsimplecaptcha_wp_footer'));			
		}// End Function wmcaptcha_display()
		
		function wmcaptcha_form_values($errors = NULL ){
			global $wmsc_options, $new_option;
			if($errors == NULL) $errors = new WP_Error();
					
			$captcha_enable_css 			= $wmsc_options['captcha_enable_css'];
			$captcha_image_characters 		= ($wmsc_options['captcha_image_characters'] + apply_filters( 'wmsimplecaptcha_extra_maxlength',0));
			$captcha_enable_refresh 		= $this->isset_option('captcha_enable_refresh',$wmsc_options,0);
			$captcha_enable_refresh_image 	= $this->isset_option('captcha_enable_refresh_image',$wmsc_options,0);			
			$captcha_label 					= $this->isset_option('captcha_label',$wmsc_options,NULL);
			$captcha_refresh_image 			= $wmsc_options['captcha_refresh_image'];
			$captcha_refresh_image 			= apply_filters( 'wmsimplecaptcha_captcha_refresh_image',$captcha_refresh_image);			
			$captcha_refresh_text 			= apply_filters( 'wmsimplecaptcha_captcha_refresh_text','Refresh');
			$captcha_enable_refresh_text	= apply_filters( 'wmsimplecaptcha_captcha_enable_refresh_text',false);
			$captcha_url					= WM_SIMPLE_CAPTCHA_CODE_URL.'?captcha_code='.rand(1111,9999);
			
			$refresh_image = NULL;
			if($captcha_refresh_image && $captcha_enable_refresh_text == false){				
				$refresh_image = '<img src="'.$captcha_refresh_image.'" alt="'.$captcha_refresh_text.'" />';
			}else{
				$refresh_image = $captcha_refresh_text;
			}
			$captcha_refresh_image_class = "";
			if($captcha_enable_refresh_image) $captcha_refresh_image_class = " refresh_image";
			
			
			$new_option = array();
			$new_option['captcha_image_characters'] 	= $captcha_image_characters;
			$new_option['refresh_button_url'] 			= $captcha_refresh_image;
			$new_option['captcha_enable_css'] 			= $captcha_enable_css;
			$new_option['captcha_enable_refresh'] 		= $captcha_enable_refresh;
			$new_option['captcha_enable_refresh_image'] = $captcha_enable_refresh_image;
			$new_option['refresh_image'] 				= $refresh_image;
			$new_option['captcha_label'] 				= $captcha_label;
			$new_option['captcha_refresh_text']			= $captcha_refresh_text;
			$new_option['captcha_enable_refresh_text']	= $captcha_enable_refresh_text;
			$new_option['captcha_url']					= $captcha_url;
			$new_option['captcha_refresh_image_class']	= $captcha_refresh_image_class;
			return $new_option;
		}// End Function wmcaptcha_form_values()
		
		function wmsimplecaptcha_scripts_front() {
			global $wmsc_options;
			$captcha_enable_css 		= $wmsc_options['captcha_enable_css'];
			if($captcha_enable_css)
			wp_enqueue_style('wmsimplecaptcha_style_front',WM_SIMPLE_CAPTCHA_URL . '/assets/css/wmsimplecaptcha_style.css');			
		}	
		function wmsimplecaptcha_init_enqueue_scripts($hook) {
			if( 'index.php' != $hook ) {
				//return;
			}				
			wp_enqueue_script( 'wmsimplecaptcha_scripts_front', WM_SIMPLE_CAPTCHA_URL.'/assets/js/wmsimplecaptcha_scripts.js', array('jquery') );		
			wp_localize_script('wmsimplecaptcha_scripts_front', 'ajax_object',	array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
		
		
		function wmsimplecaptcha_action() {
			$captcha_code = rand(111,9999);
			echo '<img src="'.WM_SIMPLE_CAPTCHA_CODE_URL.'?captcha_code='.$captcha_code.'" class="captcha_code_img" id="captcha_code_file"  />';
			die();
		}
		
		function wmsimplecaptcha_wp_footer() {
			global $wmsc_options;
			
			$captcha_enable_css 		= $wmsc_options['captcha_enable_css'];
			$captcha_background_color 	= $this->isset_option('captcha_background_color',$wmsc_options,NULL);
			$captcha_image_height 		= $this->isset_option('captcha_image_height',$wmsc_options,NULL);
			$captcha_image_width 		= $this->isset_option('captcha_image_width',$wmsc_options,NULL);
			
			$captcha_enable_border 		= $this->isset_option('captcha_enable_border',$wmsc_options,NULL);
			$captcha_border_width 		= $this->isset_option('captcha_border_width',$wmsc_options,0);
			$captcha_border_type 		= $this->isset_option('captcha_border_type',$wmsc_options,NULL);
			$captcha_border_color 		= $this->isset_option('captcha_border_color',$wmsc_options,NULL);
			
			$captcha_custom_css 		= $this->isset_option('captcha_custom_css',$wmsc_options,NULL);
			
			$output = "";
			if($captcha_enable_css || $captcha_enable_border || $captcha_custom_css){
				$output .= '<style type="text/css">';
				
				if($captcha_enable_css == 1){
					if(!$captcha_enable_border){
						$captcha_border_width = 0;
					}
					$output .= '.wmcaptcha_box .wmcaptcha_fieldbox_img{
						width:'.($captcha_image_width+$captcha_border_width).'px;
						height:'.($captcha_image_height+$captcha_border_width).'px;
						background-color:'.$captcha_background_color.';
					}';
				}
				
				if($captcha_enable_border == 1){
					$output .= '.wmcaptcha_box .wmcaptcha_fieldbox_img img{				
						border-width:'.$captcha_border_width.'px;
						border-style:'.$captcha_border_type.';
						border-color:'.$captcha_border_color.';
					}';
				}
				$output .= $captcha_custom_css;
				$output .= '</style>';
			}
    		echo $output;
		}
		
		
		
		function isset_option($name = NULL,$data = NULL, $detault = ""){
			$r = $detault;
			if($data && isset($data[$name])){
				$r = $data[$name];
			}
			
			return $r;
		}// End Function isset_option()
		
		
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
		}// End Function print_array()
		
		function default_values(){
			
				if(isset($_SESSION['wmsc_options'])){
					//return $_SESSION['wmsc_options'];
				}
				
				$default = array();	
				$default["captcha_enable_registration"] 	= "0";
				$default["captcha_image_width"] 			= "120";
				$default["captcha_image_height"] 			= "40";
				$default["captcha_image_characters"] 		= "4";
				$default["captcha_image_font_adj"]			= "0.6";
				$default["captcha_enable_space"]			= "0";
				$default["captcha_image_font"] 				= "arial.ttf";
				$default["captcha_possible_letters"] 		= "23456789";
				$default["captcha_random_dots"] 			= "392";
				$default["captcha_random_lines"] 			= "286";
				$default["captcha_text_color"] 				= "#ffffff";
				$default["captcha_dots_color"] 				= "#27d141";
				$default["captcha_line_color"]				= "#ff2d2d";
				$default["captcha_background_color"]		= "#f4f4f4";
				$default["captcha_label"] 					= "Security Code";
				$default["captcha_enable_css"] 				= "0";
				$default["captcha_enable_border"] 			= "0";
				$default["captcha_border_width"] 			= "1";
				$default["captcha_border_type"]				= "solid";
				$default["captcha_border_color"] 			= "#c4c4c4";
				$default["captcha_enable_refresh_image"] 	= "0";
				$default["captcha_enable_refresh"] 			= "0";
				$default["captcha_refresh_image"] 			= "";
				$default["captcha_empty"] 					= "ERROR: Please enter security code.";
				$default["captcha_invalid"] 				= "ERROR: Please enter valid security code.";
				$default["captcha_custom_css"] 				= "";
				$default["font_path"] 						= WM_SIMPLE_CAPTCHA_NEW_FONT_PATH;
				
				
				//delete_option('wmsimplecaptcha');
				$wmsc_options 	= get_option('wmsimplecaptcha');
				if(!$wmsc_options){
					$default 		= $this->activate();
					return $default;
				}
				
				$wmsc_options 	= array_merge((array)$default, (array)$wmsc_options);				
				return $wmsc_options;
		}// End Function define_constant()
		
		public static function activate() {
				$default = array();
				$default["captcha_enable_registration"] 	= "1";
				$default["captcha_image_width"] 			= "120";
				$default["captcha_image_height"] 			= "40";
				$default["captcha_image_characters"] 		= "4";
				$default["captcha_image_font_adj"]			= "0.6";
				$default["captcha_enable_space"]			= "0";
				$default["captcha_image_font"] 				= "arial.ttf";
				$default["captcha_possible_letters"] 		= "23456789";
				$default["captcha_random_dots"] 			= "392";
				$default["captcha_random_lines"] 			= "286";
				$default["captcha_text_color"] 				= "#ffffff";
				$default["captcha_dots_color"] 				= "#27d141";
				$default["captcha_line_color"]				= "#ff2d2d";
				$default["captcha_background_color"]		= "#f4f4f4";
				$default["captcha_label"] 					= "Security Code";
				$default["captcha_enable_css"] 				= "1";
				$default["captcha_enable_border"] 			= "0";
				$default["captcha_border_width"] 			= "1";
				$default["captcha_border_type"]				= "solid";
				$default["captcha_border_color"] 			= "#c4c4c4";
				$default["captcha_enable_refresh_image"] 	= "0";
				$default["captcha_enable_refresh"] 			= "0";
				$default["captcha_refresh_image"] 			= "";
				$default["captcha_empty"] 					= "ERROR: Please enter security code.";
				$default["captcha_invalid"] 				= "ERROR: Please enter valid security code.";
				$default["captcha_custom_css"] 				= "";
				//$default["font_path"] 						= WM_SIMPLE_CAPTCHA_NEW_FONT_PATH;			
				add_option( 'wmsimplecaptcha', $default );
				return $default;
		}
		
		/**
		 * Deactivate
		 * @return boolean
		 */
		public static function deactivate() {
			delete_option('wmsimplecaptcha_activated_plugin_error');
		}
		
		/**
		 * Tidy up deleted plugin by removing options
		 */
		public static  function uninstall() {		
			delete_option('wmsimplecaptcha');
			delete_option('wmsimplecaptcha_activated_plugin_error');			
		}
		
		function font_upload_dir( $type = false ) {
			$uploads = wp_upload_dir();
		
			$uploads = apply_filters( 'wm-simple-captcha-dir', array(
				'dir' => $uploads['basedir'],
				'url' => $uploads['baseurl'] ) );
		
			if ( 'dir' == $type ){
				
				
				return $uploads['dir'];
			}if ( 'url' == $type )
				return $uploads['url'];
				
			
		
			return $uploads;
		}
	}// End Class WM_Simple_Captcha_Front()
}