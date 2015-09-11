<?php

if(php_sapi_name()!="cli") die();

define('WP_USE_THEMES', false);
$wp_blog_path = array(
  '../../../../../wp-blog-header.php',
  '../../../../wp-blog-header.php',
  '../../../wp-blog-header.php',
  '/srv/www/vhosts/22dk/wp-blog-header.php',
  '/var/www/22dakika.org/wp-blog-header.php',
  '/var/www/test.22dakika.org/wp-blog-header.php'
);

foreach($wp_blog_path as $incpath) {
  if (file_exists($incpath)) {
  require($incpath);
  break;
  }
}

$cpm_threads = $wpdb->get_results("SELECT * FROM wp_cpm_msg WHERE subject IS NOT NULL", 'ARRAY_A');

$cpm_messages = $wpdb->get_results("SELECT * FROM wp_cpm_msg WHERE subject IS NULL", 'ARRAY_A');

$cpm_meta = $wpdb->get_results("SELECT * FROM wp_cpm_meta", 'ARRAY_A');
$meta_info = array();

foreach($cpm_meta as $info) {
  if(!is_array($meta_info[$info['thread_id']])) {
    $meta_info[$info['thread_id']] = array();
  }
  $meta_info[$info['thread_id']][$info['user_id']] = array('read' => $info['opened'], 'send_mail' => $info['subscribe']);
}

$cpm_meta = $meta_info;

$postane_threads = "wp_postane_threads";
$postane_user_thread = "wp_postane_user_thread";
$postane_messages = "wp_postane_messages";
$postane_user_message = "wp_postane_user_message";

function add_message($user_id, $thread_id, $message_content, $time) {
  global $wpdb;
  global $postane_threads;
  global $postane_user_thread;
  global $postane_messages;
  global $postane_user_message;

  $sql = "SELECT COUNT(*) FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));
  if ($res == 0) {
    return "\t $thread_id " . PostaneLang::NO_AUTHORIZATION_FOR_THREAD;
  }

  $sql = "SELECT COUNT(*) FROM $postane_threads WHERE id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));
  if($res == 0) {
    return "\t $thread_id " . PostaneLang::NO_SUCH_THREAD_ERROR;
  }

  $sql = "INSERT INTO $postane_messages (thread_id,user_id,message_content,message_creation_time) VALUES (%d, $user_id, '%s',from_unixtime($time))";
  $wpdb->query($wpdb->prepare($sql, array($thread_id, $message_content)));

  $message_id = $wpdb->insert_id;

  $sql = "UPDATE $postane_threads SET $postane_threads.last_message_time = (SELECT message_creation_time FROM $postane_messages WHERE $postane_messages.id = $message_id) WHERE $postane_threads.id = (SELECT thread_id FROM $postane_messages WHERE $postane_messages.id = $message_id)";
  $wpdb->query($sql);

  $sql = "SELECT user_id FROM $postane_user_thread WHERE thread_id = %d";
  $participants = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');


  $sql = "INSERT INTO $postane_user_message (user_id, message_id, $postane_user_message.read) VALUES ($user_id, $message_id, 1)";
  $wpdb->query($sql);

  foreach ($participants as $p_id) {
    $p_id = $p_id['user_id'];
    if($p_id != $user_id) {
      $sql = "INSERT INTO $postane_user_message (user_id, message_id, $postane_user_message.read) VALUES ($p_id, $message_id,1)";
      $wpdb->query($sql);
    }
  }

  return "\t Message added with ID: " . $message_id . " to thread with ID: ".$thread_id;
}
$mess_array = array();

foreach($cpm_messages as $mess) {
  if(!is_array($mess_array[$mess['thread_id']])) {
    $mess_array[$mess['thread_id']] = array();
  }
  $mess_array[$mess['thread_id']][] = $mess;
}

$cpm_messages = $mess_array;

foreach($cpm_threads as $thread) {
  $cpm_thread_id = $thread['thread_id'];
  $sender_id = $thread['sender_id'];
  $content = $thread['message'];
  $title = $thread['subject'];
  $time = $thread['timestamp'];

  echo "Starting cpm_thread: ".$cpm_thread_id . " --------";
  echo "\n";
  flush();
  ob_flush();

  $sql = "INSERT INTO $postane_threads (thread_title,thread_creation_time) VALUES ('%s', from_unixtime($time))";
  $wpdb->query($wpdb->prepare($sql,$title));

  $postane_thread_id = $wpdb->insert_id;
  $sql = "INSERT INTO $postane_user_thread (user_id, thread_id, is_admin, join_time, last_read_time, send_email) VALUES ($sender_id, $postane_thread_id, 1, from_unixtime($time), CURRENT_TIMESTAMP, 0)";
  $wpdb->query($sql);

  foreach($cpm_meta[$cpm_thread_id] as $user_id => $info) {
    if($user_id != $sender_id) {
      $sql = "INSERT INTO $postane_user_thread (user_id, thread_id, is_admin, join_time, last_read_time, send_email) VALUES ($user_id, $postane_thread_id, 0, from_unixtime($time), CURRENT_TIMESTAMP, 0)";
      $wpdb->query($sql);
    }
  }
  echo add_message($sender_id, $postane_thread_id, $content, $time);
  echo "\n";
  flush();
  ob_flush();
  foreach($cpm_messages[$cpm_thread_id] as $msg) {
    echo add_message($msg['sender_id'], $postane_thread_id, $msg['message'], $msg['timestamp']);
    echo "\n";
    flush();
    ob_flush();
  }
}
?>
