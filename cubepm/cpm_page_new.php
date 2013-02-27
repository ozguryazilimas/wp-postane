<?php
/**
 * CubePM Page - New
 * Handles posting of new threads.
 * 
 * @package cubepm
 */

/**
 * The HTML form to post a new thread
 * 
 * @return string
 */
function cpm_page_new(){
	$html = '<h2>' . __('Özel mesaj gönder', 'cubepm') . '</h2>';
	if($_POST['cpm_form_submit']!=''){
		$cpm_errors = cpm_page_new_process();
		if(count($cpm_errors->get_error_messages())>0){
			$html .= cpm_htmlError($cpm_errors);
			$html .= cpm_page_new_form();
		}
		else{
			$html .= cpm_htmlMessage(__('Özel mesajınız gönderildi.', 'cubepm'), __('Tebrikler!', 'cubepm'));
		}
	}
	else{
		$html .= cpm_page_new_form();
	}
	return $html;
}

/**
 * The HTML form to post a new thread
 * 
 * @return string HTML output of CubePM
 */
function cpm_page_new_form(){
	if(isset($_REQUEST['cpm_recipient'])){
		$cpm_recipient = $_REQUEST['cpm_recipient'];
	}
	else{
		$cpm_recipient = '';
	}
	if(isset($_REQUEST['cpm_subject'])){
		$cpm_subject = $_REQUEST['cpm_subject'];
	}
	else{
		$cpm_subject = '';
	}
	if(isset($_REQUEST['cpm_message'])){
		$cpm_message = $_REQUEST['cpm_message'];
	}
	else{
		$cpm_message = '';
	}
	$html = '<form method="post" action="?cpm_action=new">';
	$html .= '<input type="hidden" name="cpm_form_submit" value="1" />';
	$html .= '<p><label for="cpm_recipient">'.__('Kime', 'cubepm').':</label><br /><input type="text" name="cpm_recipient" id="cpm_recipient" value="'.$cpm_recipient.'" /></p>';
	$html .= '<p><label for="cpm_subject">'.__('Başlık', 'cubepm').':</label><br /><input type="text" name="cpm_subject" id="cpm_subject" value="'.$cpm_subject.'" /></p>';
	$html .= '<p><label for="cpm_message">'.__('Mesaj', 'cubepm').':</label><br /><textarea name="cpm_message" id="cpm_message">'.$cpm_message.'</textarea></p>';
	$html .= '<p><input type="submit" name="cpm_submit" id="cpm_submit" value="'.__('Mesajı gönder', 'cubepm').' &raquo;" /></p>';
	$html .= '</form>';
	return $html;
}

/**
 * The logic to process and validate form entry
 * 
 * @global array $current_user
 * @return object WP_ERROR
 */
function cpm_page_new_process(){
	global $current_user;
	get_currentuserinfo();
	$cpm_errors = new WP_Error();
	if(!cpm_currentUserCanStartThread()){
		$cpm_errors->add('noPermission', __('You do not have the permission to send new PMs.', 'cubepm'));
		return $cpm_errors;
	}
	$recipients = (array) explode(',', $_POST['cpm_recipient']);
	$valid_recipients = array();
	$invalid_recipients = array();
	foreach($recipients as $recipient){
		$recipient = trim($recipient);
		if($recipient!=''){
			$user = get_user_by('login', $recipient);
			if($user){
				$valid_recipients[] = $user->ID;
			}
			else{
				$invalid_recipients[] = $recipient;
			}
			$valid_recipients = array_unique($valid_recipients);
			$invalid_recipients = array_unique($invalid_recipients);
		}
	}
	if(count($invalid_recipients)>0){
		$cpm_errors->add('invalidRecipient', __('One or more users you entered is invalid.', 'cubepm'));
	}
	else if(count($valid_recipients)==0){
		$cpm_errors->add('emptyRecipient', __('Please enter the user you would like to send your PM to.', 'cubepm'));
	}
	else if(in_array($current_user->ID, $valid_recipients)){
		$cpm_errors->add('selfRecipient', __('You cannot send a PM to yourself!', 'cubepm'));
	}
	$subject = trim($_POST['cpm_subject']);
	if($subject==''){
		$cpm_errors->add('emptySubject', __('Please enter a subject!', 'cubepm'));
	}
	$message = trim($_POST['cpm_message']);
	if($message==''){
		$cpm_errors->add('emptyMessage', __('Please enter a message!', 'cubepm'));
	}
	if(count($cpm_errors->get_error_codes())==0){
		cpm_new_thread($current_user->ID, $valid_recipients, apply_filters('cpm_subject', $subject), apply_filters('cpm_message', $message));
	}
	return $cpm_errors;
}