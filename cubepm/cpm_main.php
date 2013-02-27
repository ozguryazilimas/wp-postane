<?php
/**
 * CubePM Main Functions
 * Contains all the main functions and logics.
 * 
 * @package cubepm
 */

/**
 * Shortcode hook to display output HTML from CubePM
 * 
 * @todo Add support for blogs without permalink enabled
 * 
 * @param array $atts An associative array of attributes
 * @return string HTML output of CubePM
 */
function cpm_shortcode($atts){
	$html = '<div class="cubepm">';
	if(is_user_logged_in()){
		$html .= cpm_header();
		$cpm_action = $_GET['cpm_action'];
		switch($cpm_action){
			default:
			case 'inbox':
				$html .= cpm_page_inbox();
				break;
			case 'new':
				$html .= cpm_page_new();
				break;
			case 'read':
				$html .= cpm_page_read();
				break;
			case 'admin-inbox':
				if(current_user_can('administrator')){
					$html .= cpm_page_admin_inbox();
				}
				else{
					$html .= cpm_page_inbox();
				}
		}
	}
	else{
		$html .= '<p>' . __('You have to be logged in to use Private Messaging.', 'cubepm') . '<br /><a href="' . wp_login_url( site_url($_SERVER["REQUEST_URI"]) ) .  '">' . __('Click here to login', 'cubepm') . ' &raquo;</a></p>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * HTML error messages
 * 
 * @param object $wp_error
 * @return string
 */
function cpm_htmlError($wp_error){
	if(!is_wp_error($wp_error)){
		return '';
	}
	if(count($wp_error->get_error_messages())==0){
		return '';
	}
	$errors = $wp_error->get_error_messages();
	$html = '<div class="cpm_error">';
	foreach($errors as $error){
		$html .= '<p><strong>' . __('Error', 'cubepm') . ':</strong> ' . $error . '</p>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * HTML updated messages
 * 
 * @param string $message
 * @param string $note
 * @return string
 */
function cpm_htmlMessage($message, $note = NULL){
	$html = '<div class="cpm_updated">';
	$html .= '<p>';
	if($note!=NULL){
		$html .= '<strong>' . $note . ':</strong> ';
	}
	$html .= $message;
	$html .= '</p>';
	$html .= '</div>';
	return $html;
}

/**
 * Equeues CubePM's CSS
 * 
 * @return null
 */
function cpm_enqueue_styles(){
	wp_register_style('cubepm', CPM_PATH . 'css/cubepm.css');
	wp_enqueue_style('cubepm');
}

/**
 * Equeues CubePM's JavaScript
 * 
 * @return null
 */
function cpm_enqueue_scripts(){
	wp_register_script('autocomplete', CPM_PATH . 'js/jquery.autocomplete.pack.js', array('jquery'));
	wp_enqueue_script('autocomplete');
	wp_register_script('cubepm', CPM_PATH . 'js/cubepm.js', array('jquery', 'autocomplete'));
	wp_enqueue_script('cubepm');
	wp_localize_script('cubepm', 'cubepm', array(
	'ajax_url' => get_bloginfo('url') . '/wp-admin/admin-ajax.php'
	));
}

/**
 * CubePM ajax handle for autocompleting recipients
 * 
 * @global object $wpdb
 * @return null
 */
function cpm_ajax_recipient(){
	header( "Content-Type: application/json" );
	global $wpdb;
	$limit = '';
	if(isset($_REQUEST['limit'])){
		if($_REQUEST['limit']>0){
			$limit = ' LIMIT ' . (int) $_REQUEST['limit'];
		}
	}
	$users = $wpdb->get_results('SELECT * from ' . $wpdb->prefix . 'users WHERE user_login LIKE \''.$_REQUEST['q'].'%\'' . $limit, ARRAY_A);
	$response = array();
	foreach($users as $user){
		$response[] = implode("|", array($user['user_login'], md5(trim(strtolower($user['user_email'])))));
	}
	$response = json_encode( implode("\n", $response) );
	echo $response;
	exit();
}

/**
 * CubePM HTML header
 * 
 * @todo Improve interface
 * 
 * @global $current_user;
 * @return string
 */
function cpm_header(){
	$html = '<div class="cpm-header">';
	$html .= '<a class="cpm-button" href="' . cpm_buildURL(array('cpm_action'=>'inbox')) . '">' . __('Gelen Kutusu', 'cubepm') . ' (' . cpm_inboxCount() . ')</a> ';
	if(cpm_currentUserCanStartThread()){
		$html .= '<a class="cpm-button" href="' . cpm_buildURL(array('cpm_action'=>'new')) . '">' . __('Yeni özel mesaj yaz', 'cubepm') . '</a> ';
	}
	if(current_user_can('administrator')){
		$html .= '<a class="cpm-button" href="' . cpm_buildURL(array('cpm_action'=>'admin-inbox')) . '">' . __('Tüm özel mesajları göster', 'cubepm') . '</a> ';
	}
	$html .= '</div>';
	return $html;
}

?>