<?php
ob_start();
require_once('../../../wp-load.php');

global $wpdb;
$id = preg_replace('/\D/', '',$_GET['attachment_id']);
$userid = get_current_user_id();

$msgsMeta = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fep_meta WHERE meta_id = %d", $id));
if (!$msgsMeta)
wp_die('No attachment found');

$message_id = $msgsMeta->message_id;

$unserialized_file = maybe_unserialize( $msgsMeta->field_value );
		  
if ( $msgsMeta->field_name != 'attachment' || !$unserialized_file['type'] || !$unserialized_file['url'] || !$unserialized_file['file'] )
wp_die('Invalid Attachment');

$attachment_type = $unserialized_file['type'];
$attachment_url = $unserialized_file['url'];
$attachment_path = $unserialized_file['file'];
$attachment_name = basename($attachment_url);

$msgsInfo = $wpdb->get_row($wpdb->prepare("SELECT from_user, to_user, status FROM {$wpdb->prefix}fep_messages WHERE id = %d", $message_id));

if (!$msgsInfo)
wp_die('Message already deleted');

if ( $msgsInfo->from_user != $userid && $msgsInfo->to_user != $userid && $msgsInfo->status != 2 && !current_user_can('manage_options') )
wp_die('No permission');

if(!file_exists($attachment_path)){
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}fep_meta WHERE meta_id = %d", $id));
wp_die('Attachment already deleted');
}
	
		header("Content-Description: File Transfer");
		header("Content-Transfer-Encoding: binary");
		header("Content-Type: $attachment_type", true, 200);
		header("Content-Disposition: attachment; filename=\"$attachment_name\"");
		header("Content-Length: " . filesize($attachment_path));
		nocache_headers();
		
		//clean all levels of output buffering
		while (ob_get_level()) {
    		ob_end_clean();
		}
		
		readfile($attachment_path);
		
		exit();