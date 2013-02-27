<?php
/**
 * CubePM Email Subscription Functions.
 * Handles sending of emails.
 * @package cubepm
 */

/**
 * Sends a message to subscribed users informing them of a new PM
 * 
 * @global $current_user
 * @param $message_id
 */
function cpm_mail_new($message_id){
	global $current_user;
	get_currentuserinfo();
	$message = cpm_getMessageInfo($message_id);
	foreach($message['users'] as $user_id){
		if($current_user->ID != $user_id && cpm_userCheckSubscription($user_id, $message['thread_id'])){
			$user = get_user_by('id', $user_id);
			if($user){
				$to = $user->user_email;
				//$to = 'jon7715@mailinator.com'; /** @todo Remove this: DEBUG */
				$from_name = get_option('cpm_email_from_name');
				$from_email = get_option('cpm_email_from_email');
				$subject = get_option('cpm_email_subject');
				$contents = nl2br(get_option('cpm_email_body'));
				$replace['%sender%'] = $current_user->display_name;
				$replace['%subject%'] = $message['subject'];
				$replace['%recipient%'] = $user->display_name;
				$replace['%blog_name%'] = get_bloginfo('name');
				$replace['%blog_email%'] = get_bloginfo('admin_email');
				$replace['%pm_link%'] = cpm_buildURL(array('cpm_action'=>'read', 'cpm_id'=>$message['thread_id'])) . '#cpm-message-' . $message['id'];
				$replace['%message%'] = $message['message'];
				list($from_name, $from_email, $subject, $contents) = str_replace(array_keys($replace), $replace, array($from_name, $from_email, $subject, $contents));
				$headers= "MIME-Version: 1.0\n" .
				'From: '.$from_name.' <'.$from_email.'>' . "\n" .
				"Content-Type: text/html; charset=\"" .
				get_option('blog_charset') . "\"\n";
				wp_mail($to, $subject, $contents, $headers);
				do_action('cpm_mail', $message_id, $user->ID);
			}
		}
	}
}

add_action('cpm_newThread', 'cpm_mail_new', 10, 1);
add_action('cpm_newReply', 'cpm_mail_new', 10, 1);