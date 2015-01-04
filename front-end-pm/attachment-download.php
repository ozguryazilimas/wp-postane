<?php
require_once('../../../wp-load.php');

global $wpdb;
$id = preg_replace('/\D/', '',$_GET['attachment_id']);
$userid = get_current_user_id();

$msgsMeta = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fep_meta WHERE id = %d", $id));

if (!$msgsMeta)
wp_die('No attachment found');

$message_id = $msgsMeta->message_id;
$attachment_type = $msgsMeta->attachment_type;
$attachment_url = $msgsMeta->attachment_url;
$attachment_name = basename($attachment_url);

$msgsInfo = $wpdb->get_row($wpdb->prepare("SELECT from_user, to_user FROM {$wpdb->prefix}fep_messages WHERE id = %d", $message_id));

if (!$msgsInfo)
wp_die('Message already deleted');

if ( $msgsInfo->from_user != $userid && $msgsInfo->to_user != $userid && !current_user_can('manage_options') )
wp_die('No permission');

		
		header("Content-Type: $attachment_type", true, 200);
		header("Content-Disposition: attachment; filename=\"$attachment_name\"");
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		readfile($attachment_url);
		
		exit();
		?>