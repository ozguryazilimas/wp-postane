<?php
/*
 *
 * Copyright (C) 2015, Onur Küçük <onur@ozguryazilim.com.tr>
 * Licensed under GNU GPLv2
 *
 */

define('WP_USE_THEMES', false);
$wp_blog_path = array(
  '../../../../../wp-blog-header.php',
  '../../../../wp-blog-header.php',
  '/srv/www/vhosts/22dk/wp-blog-header.php',
  '/var/www/22dakika.org/wp-blog-header.php',
  '/var/www/test.22dakika.org/wp-blog-header.php'
);

foreach($wp_blog_path as $incpath) {
  if (file_exists($incpath)) {
    require($incpath);
    echo "including " . $incpath . "\n";
    break;
  }
}

global $wpdb;
$pre_clean = true;
# $pre_clean = false;

$db_cpm_meta = 'wp_cpm_meta';
$db_cpm_msg = 'wp_cpm_msg';
$db_fep_messages = 'wp_fep_messages';
$db_fep_meta = 'wp_fep_meta';

$old_meta = array();
$old_messages = array();
$thread_parent = array();
$thread_subject = array();

if ($pre_clean) {
  $cleanup_fep_query = "DELETE FROM $db_fep_messages";
  $cleanup_fep_response = $wpdb->get_results($wpdb->prepare($cleanup_fep_query));
}

$old_messages_query = "SELECT * FROM $db_cpm_msg ORDER BY thread_id,timestamp";
$old_messages_response = $wpdb->get_results($wpdb->prepare($old_messages_query));

$old_meta_query = "SELECT * FROM $db_cpm_meta ORDER BY thread_id";
$old_meta_response = $wpdb->get_results($wpdb->prepare($old_meta_query));


foreach($old_meta_response as $k) {
  if (!isset($old_meta[$k->thread_id])) {
    $old_meta[$k->thread_id] = array();
  }

  array_push($old_meta[$k->thread_id], $k->user_id);
}


foreach($old_messages_response as $k) {
  // does not work, fpm does not care who the receiver is
  $sender_saw_it = 0;

  foreach($old_meta[$k->thread_id] as $recipient) {
    if (isset($thread_parent[$k->thread_id])) {
      $parent_id = $thread_parent[$k->thread_id];
      $subject = $thread_subject[$k->thread_id];
    } else {
      // $thread_parent[$k->thread_id] = 0;
      $parent_id = 0;
      $subject = $k->subject;
    }

    if ($recipient != $k->sender_id) {
      $new_data = array(
        'from_user' => $k->sender_id,
        'to_user' => $recipient,
        'message_title' => $subject,
        'message_contents' => wp_strip_all_tags($k->message),
        'last_sender' => $k->sender_id, // TODO
        'send_date' => date("Y-m-d H:i:s", $k->timestamp),
        'last_date' => date("Y-m-d H:i:s", $k->timestamp), // TODO
        'parent_id' => $parent_id,
        'status' => 0, // TODO
        'to_del' => 0,
        'from_del' => $sender_saw_it
      );

      $sender_saw_it = 1;
      $wpdb->insert($db_fep_messages, $new_data);

      if ($parent_id == 0) {
        $thread_parent[$k->thread_id] = $wpdb->insert_id;
        $thread_subject[$k->thread_id] = $subject;
      }
    }
  }
}



echo "\n";
// var_dump($old_messages);
// var_dump($old_meta[810]);
echo "\n";

?>
