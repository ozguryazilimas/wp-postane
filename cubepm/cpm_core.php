<?php
/**
 * CubePM Core Functions.
 * Contains all the core functions of CubePM.
 * @package cubepm
 */

/**
 * Get number of unread PM threads in inbox for a user
 * 
 * @global object $wpdb
 * @param int $user_id Defaults to current user.
 * @return int
 */
function cpm_inboxCount($user_id = null){
	if($user_id == null){
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
	}
	global $wpdb;
	return $wpdb->get_var('SELECT COUNT(*) FROM ' . CPM_DB_META . ' WHERE user_id = ' . $user_id . ' AND opened = false');
}

/**
 * Check if a thread is accessable by a user
 *
 * @global object $wpdb
 * @param int $user_id
 * @param int $thread_id
 * @return bool
 */
function cpm_userCanViewThread($user_id, $thread_id){
	global $wpdb;
	return ($wpdb->get_var('SELECT COUNT(*) FROM ' . CPM_DB_META . ' WHERE user_id = ' . $user_id . ' AND thread_id = ' . $thread_id) > 0 ) ? true : false;
}

/**
 * Get all users who can access a thread
 *
 * @global object $wpdb
 * @param int $thread_id
 * @return array
 */
function cpm_threadUsers($thread_id){
	global $wpdb;
	return $wpdb->get_col('SELECT user_id FROM ' . CPM_DB_META . ' WHERE thread_id = ' . $thread_id);
}

/**
 * Adds a thread to the list of accessable threads for a user
 * 
 * @global object $wpdb
 * @param int|array $user_id Either the ID of a user or an array of IDs.
 * @param int $thread_id
 * @param bool $opened Set whether the thread has been read. Default: false.
 * @param bool subscribe Set whether this thread is subscribed to. Default: true.
 * @return null
 */
function cpm_userAddAccessToThread($user_id, $thread_id, $opened = false, $subscribe = true){
	if(is_array($user_id)){
		foreach($user_id as $uid){
			cpm_userAddAccessToThread($uid, $thread_id, $opened, $subscribe);
		}
		return;	
	}
	if(!cpm_userCanViewThread($user_id, $thread_id)){
		global $wpdb;
		$wpdb->query('INSERT INTO ' . CPM_DB_META . ' ( thread_id, user_id, opened, subscribe ) VALUES ( ' . $thread_id . ', ' . $user_id . ', ' . ($opened?1:0) . ', ' . ($subscribe?1:0) . ' )');
	}
}

/**
 * Removes a thread from the list of accessable threads for a user
 * 
 * @global object $wpdb
 * @param int $user_id
 * @param int $thread_id
 * @return null
 */
function cpm_userRemoveAccessToThread($user_id, $thread_id){
	if(is_array($user_id)){
		foreach($user_id as $uid){
			cpm_userRemoveAccessToThread($uid, $thread_id);
		}
		return;	
	}
	global $wpdb;
	$wpdb->query('DELETE FROM ' . CPM_DB_META . ' WHERE user_id = ' . $user_id . ' AND thread_id = ' . $thread_id);
}

/**
 * Set whether a topic has been read by users
 * 
 * @global object $wpdb
 * @param int|array $user_id Either the ID of a user or an array of IDs.
 * @param int $thread_id
 * @param bool $opened
 * @return null
 */
function cpm_userSetOpenedThread($user_id, $thread_id, $opened){
	if(is_array($user_id)){
		foreach($user_id as $uid){
			cpm_userOpenedThread($uid, $thread_id);
		}
		return;	
	}
	global $wpdb;
	$wpdb->query('UPDATE ' . CPM_DB_META . ' SET opened = ' . ($opened?'true':'false') . ' WHERE user_id = ' . $user_id . ' AND thread_id = ' . $thread_id);
}

/**
 * Check whether a topic has been read by a user
 * 
 * @global object $wpdb
 * @param int $user_id
 * @param int $thread_id
 * @return bool
 */
function cpm_userCheckOpenedThread($user_id, $thread_id){
	global $wpdb;
	return $wpdb->get_var('SELECT opened FROM ' . CPM_DB_META . ' WHERE user_id = ' . $user_id . ' AND thread_id = ' . $thread_id);
}

/**
 * Set whether a topic is subscribed
 * 
 * @global object $wpdb
 * @param int|array $user_id Either the ID of a user or an array of IDs.
 * @param int $thread_id
 * @param bool $subscribed
 * @return null
 */
function cpm_userSetSubscription($user_id, $thread_id, $subscribed){
	if(is_array($user_id)){
		foreach($user_id as $uid){
			cpm_userSetSubscribed($uid, $thread_id);
		}
		return;	
	}
	global $wpdb;
	$wpdb->query('UPDATE ' . CPM_DB_META . ' SET subscribe = ' . ($subscribed?'true':'false') . ' WHERE user_id = ' . $user_id . ' AND thread_id = ' . $thread_id);
	do_action('cpm_setSubsciption', $subscribed, $user_id);
}

/**
 * Check whether a topic has been subscribed by a user
 * 
 * @global object $wpdb
 * @param int $user_id
 * @param int $thread_id
 * @return bool
 */
function cpm_userCheckSubscription($user_id, $thread_id){
	global $wpdb;
	return $wpdb->get_var('SELECT subscribe FROM ' . CPM_DB_META . ' WHERE user_id = ' . $user_id . ' AND thread_id = ' . $thread_id);
}

/**
 * Check if a user can start a thread
 *
 * @todo Manage user permissions
 *
 * @return bool
 */
function cpm_currentUserCanStartThread(){
	$permissions = get_option('cpm_permission_newtopic');
	foreach($permissions as $permission){
		if(current_user_can($permission)){
			return true;
		}
	}
	return false;
}

/**
 * Check if a user can reply to a thread
 *
 * @todo Manage user permissions
 *
 * @param int $user_id
 * @param int $thread_id
 * @return bool
 */
function cpm_userCanReplyToThread($user_id, $thread_id){
	return cpm_userCanViewThread($user_id, $thread_id);
}

/**
 * Creates a new PM thread
 * 
 * @global object $wpdb
 * @param int $sender ID of sender
 * @param array $accessable_users IDs of users that can access this thread.
 * @param string $subject Subject of the thread.
 * @param string $message Message contents.
 * @return array Contains the message id and thread id.
 */
function cpm_new_thread($sender, $accessable_users, $subject, $message){
	global $wpdb;
	$thread_id = $wpdb->get_var('SELECT thread_id FROM ' . CPM_DB_MSG . ' ORDER BY thread_id DESC LIMIT 1') + 1;
	$query = 'INSERT INTO ' . CPM_DB_MSG . ' (thread_id, sender_id, message, subject, timestamp) VALUES ('. $thread_id .', '. $sender .', \''. $wpdb->escape($message) .'\', \''. $wpdb->escape($subject) .'\', '. time() .')';
	$wpdb->show_errors();
	$wpdb->query($query);
	$message_id = $wpdb->insert_id;
	cpm_userAddAccessToThread($accessable_users, $thread_id, false);
	cpm_userAddAccessToThread($sender, $thread_id, true);
	do_action('cpm_newThread', $message_id, $thread_id, $sender, $accessable_users, $subject, $message);
	return array($message_id, $thread_id);
}

/**
 * Creates a new PM reply
 * 
 * @global object $wpdb
 * @param int $thread_id ID of the thread which the reply goes to
 * @param int $sender ID of sender
 * @param string $message Message contents
 * @return array Contains the message id and thread id
 */
function cpm_new_reply($thread_id, $sender, $message){
	global $wpdb;
	$query = 'INSERT INTO ' . CPM_DB_MSG . ' (thread_id, sender_id, message, timestamp) VALUES ('. $thread_id .', '. $sender .', \''. $wpdb->escape($message) .'\', '. time() .')';
	$wpdb->query($query);
	$message_id = $wpdb->insert_id;
	$wpdb->query('UPDATE ' . CPM_DB_META . ' SET opened = 0 WHERE NOT(user_id = ' . $sender . ') AND thread_id = ' . $thread_id);
	do_action('cpm_newReply', $message_id, $thread_id, $sender, $message);
	return array($message_id, $thread_id);
}

/**
 * Get info of a thread
 * 
 * @global object $wpdb
 * @param int $thread_id
 * @return array
 */
function cpm_getThreadInfo($thread_id){
	global $wpdb;
	$query = 'SELECT * FROM ' . CPM_DB_MSG . ' WHERE thread_id = ' . $thread_id;
	$thread = $wpdb->get_row($query, ARRAY_A);
	$thread['users'] = cpm_threadUsers($thread_id);
	$thread['freshness'] = $wpdb->get_var('SELECT MAX(timestamp) FROM ' . CPM_DB_MSG . ' WHERE thread_id = ' . $thread_id);
	return $thread;
}

/**
 * Get info of a message
 * 
 * @global object $wpdb
 * @param int $message_id
 * @return array
 */
function cpm_getMessageInfo($message_id){
	global $wpdb;
	$query = 'SELECT * FROM ' . CPM_DB_MSG . ' WHERE id = ' . $message_id;
	$message = $wpdb->get_row($query, ARRAY_A);
	$thread = cpm_getThreadInfo($message['thread_id']);
	$message['subject'] = $thread['subject'];
	$message['freshness'] = $thread['freshness'];
	$message['users'] = $thread['users'];
	return $message;
}

/**
 * Get topics to be shown in the inbox of a user
 * 
 * @global object $wpdb
 * @param int $user_id
 * @return array
 */
function cpm_getUserInboxThreads($user_id){
	global $wpdb;
	$query = 'SELECT DISTINCT ' . CPM_DB_META . '.thread_id FROM ' . CPM_DB_MSG . ' LEFT JOIN ' . CPM_DB_META . ' ON ' . CPM_DB_META . '.thread_id = ' . CPM_DB_MSG . '.thread_id WHERE ' . CPM_DB_META . '.user_id = ' . $user_id . ' ORDER BY ' . CPM_DB_META . '.opened ASC, ' . CPM_DB_MSG . '.timestamp DESC';
	$thread_ids = $wpdb->get_col($query);
	return $thread_ids;
}

/**
 * Get all threads
 * 
 * @global object $wpdb
 * @return array
 */
function cpm_getThreads(){
	global $wpdb;
	$query = 'SELECT DISTINCT ' . CPM_DB_META . '.thread_id FROM ' . CPM_DB_MSG . ' LEFT JOIN ' . CPM_DB_META . ' ON ' . CPM_DB_META . '.thread_id = ' . CPM_DB_MSG . '.thread_id ORDER BY ' . CPM_DB_MSG . '.timestamp DESC';
	$thread_ids = $wpdb->get_col($query);
	return $thread_ids;
}

/**
 * Returns the relative time
 * 
 * @param int $timestamp
 * @param bool $show_ending Adds "to go" or "ago" after the relative time if true.
 * @param string|bool $title_date_format Date format for alt tag or false for none.
 * @return string
 */
function cpm_relativeTime($timestamp, $show_ending = true, 
$title_date_format = "j F Y, g:i a"){
     $difference = time() - $timestamp;
     $periods = array(__('sn','cubepm'), __('dk','cubepm'), 
__('saat','cubepm'), __('gün','cubepm'), __('hafta','cubepm'), 
__('ay','cubepm'), __('yıl','cubepm'));
     $periods_plural = array(__('sn','cubepm'), __('dk','cubepm'), 
__('saat','cubepm'), __('gün','cubepm'), __('hafta','cubepm'), 
__('ay','cubepm'), __('yıl','cubepm'));
     $lengths = array("60","60","24","7","4.35","12");
     if ($difference >= 0) { // this was in the past
         $ending = __('önce','cp');
     } else { // this was in the future
         $difference = -$difference;
         $ending = __('daha var','cp');
     }
     for($j = 0; $difference >= $lengths[$j] && isset($lengths[$j]); $j++)
         $difference /= $lengths[$j];
     $difference = round($difference);
     if($difference != 1 && $difference != 0) $periods[$j] = 
$periods_plural[$j];
     $text = "$difference $periods[$j] $ending";
     if($title_date_format){
         $text = '<span title="' . date($title_date_format, $timestamp + 
( get_option( 'gmt_offset' ) * 3600 ) ) . '">' . $text . '</span>';
     }
     return $text;
}

/**
 * Checks if custom permalink is enabled on WordPress
 * 
 * @return bool
 */
function cpm_usingCustomPermalink(){ 
	return (get_option('permalink_structure')!='') ? true : false;
}

/**
 * Builds URL with queries from array and includes page id if custom permalinks disabled
 * 
 * @param array $query
 * @return bool
 */
function cpm_buildURL($query = array()){
	if(!cpm_usingCustomPermalink()){
		if(isset($_GET['page_id'])){
			$query['page_id'] = $_GET['page_id'];
		}
		if(isset($_GET['p'])){
			$query['p'] = $_GET['p'];
		}
	}
	return 'http' . ( ($_SERVER["HTTPS"]=='on') ? 's' : '' ) . '://' . $_SERVER["SERVER_NAME"] . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . ( (count($query)>0) ? ( '?' . http_build_query($query) ) : '' );
}

?>