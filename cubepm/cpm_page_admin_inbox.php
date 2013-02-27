<?php
/**
 * CubePM Page - Admin Inbox
 * Handles output for displaying the PMs from all users.
 * 
 * @package cubepm
 */

/**
 * The HTML form to display the inbox from all users
 * 
 * @return string
 */
function cpm_page_admin_inbox(){
	$html = '<h2>' . __('Tüm özel mesajları göster', 'cubepm') . '</h2>';
	$user_id = $current_user->ID;
	$thread_ids = cpm_getThreads($user_id);
	if(count($thread_ids)>0){
		$html .= '<table class="cpm-inbox">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="cpm-inbox-subject">' . __('Konu', 'cubepm') . '</th>';
		$html .= '<th class="cpm-inbox-from">' . __('Kim kime?', 'cubepm') . '</th>';
		$html .= '<th class="cpm-inbox-received">' . __('Ne zaman?', 'cubepm') . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		$count = 0;
		foreach($thread_ids as $thread_id){
			$count++;
			$thread = cpm_getThreadInfo($thread_id);
			$from = array();
			foreach($thread['users'] as $thread_user_id){
				$user = get_user_by('id', $thread_user_id);
				if($user){
					$from[] = '<span class="cpm-user" style="background-image:url(https://secure.gravatar.com/avatar/'. md5(strtolower(trim($user->user_email))) .'?s=16);"><a href="' . apply_filters('cpm_user_link', '#', $user) . '">' . $user->display_name . '</a></span>';
				}
			}
			if(count($from)>0){
				$from = implode('<span class="cpm-user-separator">, </span>', $from);
			}
			else{
				$from = '<i>' . __('Nobody', 'cubepm') . '</i>';
			}
			$html .= '<tr class="' . (($count%2==0)?'even':'odd') . '">';
			$html .= '<td class="cpm-inbox-subject"><a href="' . cpm_buildURL(array('cpm_action'=>'read', 'cpm_id'=>$thread['thread_id'])) . '">' . $thread['subject'] . '</a></td>';
			$html .= '<td class="cpm-inbox-from">' . $from . '</td>';
			$html .= '<td class="cpm-inbox-received">' . cpm_relativeTime($thread['freshness']) . '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
	}
	else{
		$html .= cpm_htmlMessage(__('You do not have any messages in your inbox!', 'cubepm'));
	}
	return $html;
}