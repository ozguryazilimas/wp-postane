<?php
/**
 * CubePM Page - Read
 * Handles reading of threads.
 * 
 * @package cubepm
 */

/**
 * The HTML to display PM threads
 * 
 * @global bool $cpm_currentThread_isParticipant
 * @global array $current_user
 * @return string
 */
function cpm_page_read(){
	global $cpm_currentThread_isParticipant;
	global $current_user;
	get_currentuserinfo();
	$cpm_errors = cpm_page_read_check($_GET['cpm_id'], $current_user->ID);
	if(count($cpm_errors->get_error_messages())>0){
		return cpm_htmlError($cpm_errors);
	}
	$thread_id = (int)$_GET['cpm_id'];
	$user_id = $current_user->ID;
	cpm_userSetOpenedThread($user_id, $thread_id, true);
	if($cpm_currentThread_isParticipant){
		if(get_option('cpm_email_enabled')){
			cpm_page_read_emailProcess($thread_id, $user_id);
		}
		if($_POST['cpm_form_submit']!=''){
			$cpm_reply_errors = cpm_page_read_process($thread_id);
		}
		else{
			$cpm_reply_errors = null;
		}
	}
	$html = '';
	$html .= cpm_page_read_buildInfo($thread_id, $user_id);
	$html .= cpm_page_read_buildThread($thread_id, $user_id);
	if($cpm_currentThread_isParticipant){
		$html .= cpm_page_read_buildReply($thread_id, $user_id, $cpm_reply_errors);
	}
	return $html;
}

/**
 * Hook to mark topic as read on init
 * 
 * @global $current_user
 * @return null
 */
function cpm_page_read_init(){
	if($_GET['cpm_action']!='read'){
		return;
	}
	global $current_user;
	get_currentuserinfo();
	$cpm_errors = cpm_page_read_check($_GET['cpm_id'], $current_user->ID);
	if(count($cpm_errors->get_error_messages())>0){
		return;
	}
	$thread_id = (int)$_GET['cpm_id'];
	$user_id = $current_user->ID;
	cpm_userSetOpenedThread($user_id, $thread_id, true);
}

/** Adds the hook to mark topic as read on init */
add_action('init', 'cpm_page_read_init');

/**
 * Processes email subscription form
 * 
 * @return null
 */
function cpm_page_read_emailProcess($thread_id, $user_id){
	if(isset($_POST['cpm_subscribe'])){
		cpm_userSetSubscription($user_id, $thread_id, (bool) $_POST['cpm_subscribe_value']);
	}
}

/**
 * Checks if a user is authorized to view a thread
 * 
 * @global bool $cpm_currentThread_isParticipant
 * @global array $current_user
 * @global object $wpdb
 * @param int $thread_id
 * @param int $user_id
 * @return object WP_ERROR
 */
function cpm_page_read_check($thread_id, $user_id){
	global $cpm_currentThread_isParticipant;
	$cpm_errors = new WP_Error();
	if($thread_id=='' || !is_numeric($thread_id) || strstr($thread_id, '.')){
		$cpm_errors->add('invalidID', __('Invalid message ID.', 'cubepm'));
		return $cpm_errors;
	}
	global $wpdb;
	$query = 'SELECT COUNT(*) FROM ' . CPM_DB_META . ' WHERE user_id = ' . $user_id . ' AND thread_id = ' . (int) $thread_id;
	if($wpdb->get_var($query)==0){
		if(current_user_can('administrator')){
			$query = 'SELECT COUNT(*) FROM ' . CPM_DB_META . ' WHERE thread_id = ' . (int) $thread_id;
			if($wpdb->get_var($query)==0){
				$cpm_errors->add('noAuth', __('The message you are trying to view may have been deleted.', 'cubepm'));
			}
			else{
				$cpm_currentThread_isParticipant = false;
			}
		}
		else{
			$cpm_errors->add('noAuth', __('The message you are trying to view may have been deleted.', 'cubepm'));
		}
		return $cpm_errors;
	}	
	$cpm_currentThread_isParticipant = true;
	return $cpm_errors;
}

/**
 * Build HTML to show thread info
 * 
 * @todo Add thread info here
 * 
 * @param bool $cpm_currentThread_isParticipant
 * @param int $thread_id
 * @param int $user_id
 * @return string
 */
function cpm_page_read_buildInfo($thread_id, $user_id){
	global $cpm_currentThread_isParticipant;
	$thread = cpm_getThreadInfo($thread_id);
	$html = '<h2>';
	$html .= $thread['subject'];
	$html .= '</h2>';
	$html .= '<div class="cpm-thread-meta">';
	$html .= '<div class="cpm-thread-meta-users">';
	$html .= '<span class="cpm-thread-meta-label">' . __('Kimden kime?', 'cubepm') . ':</span> ';
	$from = array();
	foreach($thread['users'] as $thread_user_id){
		$user = get_user_by('id', $thread_user_id);
		if($user){
			$from[] = '<span class="cpm-user" style="background-image:url(https://secure.gravatar.com/avatar/'. md5(strtolower(trim($user->user_email))) .'?s=16);"><a href="' . apply_filters('cpm_user_link', '#', $user) . '">' . $user->display_name . '</a></span>';
		}
	}
	$html .= implode('<span class="cpm-user-separator">, </span>', $from);
	$html .= '</div>';
	if($cpm_currentThread_isParticipant && get_option('cpm_email_enabled')){
		$html .= '<div class="cpm-thread-meta-subscribe">';
		$html .= '<form method="post" action="' . cpm_buildURL(array('cpm_action'=>'read', 'cpm_id'=>$thread_id)) . '">';
		$html .= '<input type="hidden" name="cpm_subscribe" id="cpm_subscribe" value="1" />';
		$html .= '<input type="checkbox" name="cpm_subscribe_value" id="cpm_subscribe_value" value="1" onclick="this.form.submit();"'. ( cpm_userCheckSubscription($user_id, $thread_id) ? ' checked="yes"' : '' ) .' />';
		$html .= '<label for="cpm_subscribe_value">' . __('Bu yazışmaya yeni mesaj gelince bana e-posta ile haber ver.', 'cubepm') . '</label>';
		$html .= '</form>';
		$html .= '<div class="clear"></div>';
		$html .= '</div>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * Build HTML to show thread
 * 
 * @global object $wpdb
 * @param int $thread_id
 * @param int $user_id
 * @return string
 */
function cpm_page_read_buildThread($thread_id, $user_id){
	global $wpdb;
	$query = 'SELECT * FROM ' . CPM_DB_MSG . ' WHERE thread_id = ' . $thread_id . ' ORDER BY timestamp ASC';
	$messages = $wpdb->get_results($query);
	$html = '<table class="cpm-thread">';
	$html .= '<thead>';
	$html .= '<tr>';
	$html .= '<th class="cpm-thread-from">' . __('Kimden', 'cubepm') . '</th>';
	$html .= '<th class="cpm-thread-message">' . __('Mesaj', 'cubepm') . '</th>';
	$html .= '</tr>';
	$html .= '</thead>';
	$html .= '<tbody>';
	$count = 0;
	foreach($messages as $message){
		$count++;
		$sender = get_user_by('id', $message->sender_id);
		$html .= '<tr class="' . (($count%2==0)?'even':'odd') . '">';
		$html .= '<td class="cpm-thread-from">';
		$html .= '<a name="cpm-message-' . $message->id . '"></a>';
		$html .= '<div class="cpm-thread-from-avatar">' . get_avatar($sender->ID, '80') . '</div>';
		$html .= '<div class="cpm-thread-from-name"><a href="' . apply_filters('cpm_user_link', '#', $sender) . '">' . $sender->display_name . '</a></div>';
		$html .= '<div class="cpm-thread-from-time"><a href="' . cpm_buildURL(array('cpm_action'=>'read', 'cpm_id'=>$message->thread_id)) . '#cpm-message-' . $message->id . '">' . cpm_relativeTime($message->timestamp) . '</a></div>';
		$html .= '</td>';
		$html .= '<td class="cpm-thread-message">';
		$html .= $message->message;
		$html .= '</td>';
	}
	$html .= '</tbody>';
	$html .= '</table>';
	return $html;
}

/**
 * Build HTML to show reply form
 * 
 * @todo Add reply form here
 * 
 * @param int $thread_id
 * @param int $user_id
 * @return string
 */
function cpm_page_read_buildReply($thread_id, $user_id, $cpm_errors = null){
	$html = '<a name="cpm-reply"></a>';
	$html .= '<h3>'. __('Cevapla', 'cubepm') .'</h3>';
	if($cpm_errors != null){
		if(count($cpm_errors->get_error_messages())>0){
			$html .= cpm_htmlError($cpm_errors);
		}
		else{
			$html .= cpm_htmlMessage('Cevabınız gönderildi.', 'Tebrikler!');
		}
	}
	$html .= '<table class="cpm-thread-reply">';
	$html .= '<tbody><tr><td>';
	$html .= '<form method="post" action="' . cpm_buildURL(array('cpm_action'=>'read', 'cpm_id'=>$thread_id)) . '#cpm-reply">';
	$html .= '<input type="hidden" name="cpm_form_submit" value="1" />';
	$html .= '<p><textarea name="cpm_message" id="cpm_message">'.$cpm_message.'</textarea></p>';
	$html .= '<p><input type="submit" name="cpm_submit" id="cpm_submit" value="'.__('Cevapla', 'cubepm').' &raquo;" /></p>';
	$html .= '</form>';
	$html .= '</td></tr></tbody>';
	$html .= '</table>';
	return $html;
}

/**
 * The logic to process and validate thread reply form
 * 
 * @global array $current_user
 * @param int $thread_id
 * @return object WP_ERROR
 */
function cpm_page_read_process($thread_id){
	global $current_user;
	get_currentuserinfo();
	$cpm_errors = new WP_Error();
	if(!cpm_userCanReplyToThread($current_user->ID, $thread_id)){
		$cpm_errors->add('noPermission', __('You do not have the permission to reply to this thread.', 'cubepm'));
		return $cpm_errors;
	}
	$message = trim($_POST['cpm_message']);
	if($message==''){
		$cpm_errors->add('emptyMessage', __('Please enter a message!', 'cubepm'));
	}
	if(count($cpm_errors->get_error_codes())==0){
		cpm_new_reply($thread_id, $current_user->ID, apply_filters('cpm_message', $message));
	}
	return $cpm_errors;
}

?>