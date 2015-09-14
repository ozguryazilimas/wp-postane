<?php

$postane_threads = $wpdb->prefix . "postane_threads";
$postane_messages = $wpdb->prefix . "postane_messages";
$postane_user_message = $wpdb->prefix . "postane_user_message";
$postane_user_thread = $wpdb->prefix . "postane_user_thread";

function postane_get_user_id($display_name) {
  global $wpdb;
  $users = $wpdb->users;
  $sql = "SELECT ID FROM $users WHERE display_name = '%s'";
  $u_id = $wpdb->get_row($wpdb->prepare($sql,$display_name), "ARRAY_A");

  if ($u_id) {
    return $u_id['ID'];
  }

  return false;
}

function postane_autocomplete($input) {
  global $wpdb;
  $users = $wpdb->users;
  $sql = "SELECT display_name FROM $users WHERE display_name LIKE '%s'";
  $results = $wpdb->get_results($wpdb->prepare($sql, $input . '%'), 'ARRAY_A');
  $ret_array = array();

  foreach ($results as $res) {
    $ret_array[] = array(value => $res['display_name']);
  }

  return $ret_array;
}

function postane_delete_message($user_id, $message_id) {
  global $wpdb;
  global $postane_user_message;
  global $postane_messages;

  $sql = "SELECT COUNT(*) FROM $postane_user_message WHERE $postane_user_message.user_id = $user_id AND $postane_user_message.message_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $message_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION);
  }

  $sql = "DELETE FROM $postane_user_message WHERE $postane_user_message.message_id = %d AND $postane_user_message.user_id = $user_id";
  $wpdb->query($wpdb->prepare($sql, $message_id));

  $sql = "SELECT COUNT(*) FROM $postane_user_message WHERE $postane_user_message.message_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $message_id));

  if ($res == 0) {
    $sql = "DELETE FROM $postane_messages WHERE $postane_messages.id = %d";
    $wpdb->query($wpdb->prepare($sql, $message_id));
  }

  return array("success" => true);
}

function postane_delete_all_messages($user_id, $thread_id) {
  global $wpdb;
  global $postane_user_thread;
  global $postane_user_message;
  global $postane_messages;

  $sql = "SELECT COUNT(*) FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "DELETE FROM $postane_user_message WHERE $postane_user_message.user_id = $user_id AND $postane_user_message.message_id IN (SELECT id FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  $sql = "SELECT COUNT(*) FROM $postane_user_message WHERE $postane_user_message.message_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $message_id));

  if ($res == 0) {
    $sql = "DELETE FROM $postane_messages WHERE $postane_messages.id = %d";
    $wpdb->query($wpdb->prepare($sql, $message_id));
  }

  return array("success" => true);
}

function postane_create_thread($user_id, $thread_title, $first_message, $participants) {
  if (!is_array($participants)) {
    return array("error" => PostaneLang::NO_PARTICIPANTS_ERROR);
  }

  $participant_ids = array();

  foreach ($participants as $participant) {
    $ID = postane_get_user_id($participant);

    if ($ID && $ID != $user_id && !in_array($ID, $participant_ids)) {
      $participant_ids[] = $ID;
    }
  }

  if (empty($participant_ids)) {
    return array("error" => PostaneLang::NO_LEGIT_PARTICIPANTS);
  }

  global $wpdb;
  global $postane_threads;
  global $postane_messages;
  global $postane_user_message;
  global $postane_user_thread;

  $sql = "INSERT INTO $postane_threads (thread_title) VALUES ('%s')";
  $wpdb->query($wpdb->prepare($sql, $thread_title));

  $thread_id = $wpdb->insert_id;

  $sql = "INSERT INTO $postane_user_thread (user_id,thread_id,is_admin) VALUES ($user_id,$thread_id,1)";
  $wpdb->query($sql);

  $sql = "INSERT INTO $postane_messages (thread_id,user_id,message_content) VALUES ($thread_id,$user_id,'%s')";
  $wpdb->query($wpdb->prepare($sql,$first_message));

  $message_id = $wpdb->insert_id;

  $sql = "INSERT INTO $postane_user_message (user_id,message_id,$postane_user_message.read) VALUES ($user_id,$message_id,1)";
  $wpdb->query($sql);

  $headers = 'From: 22dakika.org <otomatikmesaj@22dakika.org>' . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=UTF-8\r\n";
  $username = get_userdata($user_id)->display_name;
  $subject = "$username diyor ki: '" . mb_substr($thread_title, 0, min(25, mb_strlen($thread_title))) . (mb_strlen($thread_title) > 25 ? "...'" : "'");
  $postane_url = get_site_url() . '/postane';
  $thread_title = apply_filters('the_title', $thread_title);

  foreach ($participant_ids as $p_id) {
    $sql = "INSERT INTO $postane_user_thread (user_id,thread_id) VALUES ($p_id, $thread_id)";
    $wpdb->query($sql);

    $sql = "INSERT INTO $postane_user_message (user_id,message_id) VALUES ($p_id,$message_id)";
    $wpdb->query($sql);

    $recip_userdata = get_userdata($p_id);
    $recip_username = $recip_userdata->display_name;
    $recip_email = $recip_userdata->user_email;
    $content = "Merhaba $recip_username,<br/><br/>22dakika.org sitesinden $username size '$thread_title' konulu bir mesaj göndermiş.<br/><br/>Okumak için lütfen aşağıdaki linki takip edin:<br/><a href='$postane_url/?postane_thread_id=$thread_id'>$postane_url/postane_thread_id=$thread_id</a>";
    wp_mail($recip_email, $subject, $content, $headers);
  }

    $sql = "UPDATE $postane_threads SET $postane_threads.last_message_time = (SELECT message_creation_time FROM $postane_messages WHERE $postane_messages.id = $message_id) WHERE $postane_threads.id = (SELECT thread_id FROM $postane_messages WHERE $postane_messages.id = $message_id)";
  $wpdb->query($sql);

  return array("success" => true);
}

function postane_edit_message($user_id, $message_id, $new_content) {
  global $wpdb;
  global $postane_threads;
  global $postane_messages;
  global $postane_user_message;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*) as c FROM $postane_messages WHERE user_id = $user_id AND id = %d";
  $count = $wpdb->get_row($wpdb->prepare($sql, $message_id),"ARRAY_A")->c;

  if ($count === 0) {
    return array("error" => PostaneLang::UNAUTHORIZED_MESSAGE_EDIT);
  }

  $sql = "UPDATE $postane_messages SET message_content = '%s',edited = 1,edit_time = CURRENT_TIMESTAMP WHERE id = %d AND user_id = $user_id";
  $wpdb->query($wpdb->prepare($sql,array($new_content, $message_id)));

  $content = apply_filters('the_content', $new_content);
  $dt = new DateTime();
  $dt->setTimezone(new DateTimeZone('Europe/Istanbul'));
  $edit_time = $dt->format("Y-m-d H:i:s");

  return array('success' => array('message_content' => $content, 'edit_time' => $edit_time));
}

function postane_mark_thread_read($user_id, $thread_id) {
  global $wpdb;
  global $postane_user_thread;
  global $postane_user_message;
  global $postane_messages;

  $sql = "SELECT COUNT(*) as C FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION);
  }

  $sql = "UPDATE $postane_user_message SET $postane_user_message.read = 1 WHERE $postane_user_message.user_id = $user_id AND $postane_user_message.message_id IN (SELECT id FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  $sql = "UPDATE $postane_user_thread SET last_read_time = CURRENT_TIMESTAMP WHERE thread_id = %d AND user_id = $user_id";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  return array("success" => true);
}

function postane_mark_message_read($user_id, $message_id) {
  global $wpdb;
  global $postane_user_message;

  $sql = "UPDATE $postane_user_message SET $postane_user_message.read = 1 WHERE user_id = $user_id AND message_id = %d";

  $wpdb->query($wpdb->prepare($sql, $message_id));
  return array("success" => true);
}

function postane_quit_thread($user_id, $thread_id) {
  global $wpdb;
  global $postane_threads;
  global $postane_messages;
  global $postane_user_message;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*) as c FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "DELETE FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  $sql = "DELETE FROM $postane_user_message WHERE $postane_user_message.user_id = $user_id AND $postane_user_message.message_id IN (SELECT id FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  $sql = "SELECT COUNT(*) FROM $postane_user_thread WHERE thread_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    $sql = "DELETE FROM $postane_user_message WHERE $postane_user_message.message_id IN (SELECT $postane_messages.id FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
    $wpdb->query($wpdb->prepare($sql, $thread_id));
    $sql = "DELETE FROM $postane_messages WHERE $postane_messages.thread_id = %d";
    $wpdb->query($wpdb->prepare($sql, $thread_id));
    $sql = "DELETE FROM $postane_threads WHERE $postane_threads.id = %d";
    $wpdb->query($wpdb->prepare($sql, $thread_id));
  }

  return array("success" => true);
}

function postane_get_messages($user_id, $thread_id, $exclusion_list, $step, $max_time) {
  global $wpdb;
  global $postane_user_message;
  global $postane_messages;
  global $postane_threads;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*)  FROM $postane_user_thread WHERE thread_id = %d AND user_id = $user_id";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "SELECT COUNT(*) FROM $postane_threads WHERE id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_SUCH_THREAD_ERROR);
  }

  $sql = "UPDATE $postane_user_thread SET last_read_time = CURRENT_TIMESTAMP WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  $users = $wpdb->users;
  $sql = "SELECT * FROM $postane_threads WHERE $postane_threads.id = %d";
  $thread_info = $wpdb->get_row($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_user_thread.*, $users.display_name FROM $postane_user_thread INNER JOIN $users ON $postane_user_thread.user_id = $users.ID WHERE $postane_user_thread.thread_id = %d";
  $participant_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_messages.*, $postane_user_message.read FROM $postane_messages INNER JOIN $postane_user_message ON $postane_user_message.message_id = $postane_messages.ID WHERE $postane_user_message.user_id= %d AND $postane_messages.thread_id = %d AND $postane_messages.id NOT IN (";

  $arr_len = count($exclusion_list);

  for ($i = 0; $i < $arr_len; $i++) {
    $sql .= "%d";

    if ($i != $arr_len - 1) {
      $sql .= ",";
    }
  }

  $sql .= ") AND $postane_messages.message_creation_time < '%s' ORDER BY $postane_messages.message_creation_time DESC LIMIT %d";

  $prep_array = array($user_id, $thread_id);
  $prep_array = array_merge($prep_array, $exclusion_list);
  $prep_array[] = $max_time;
  $prep_array[] = $step;

  $messages = $wpdb->get_results($wpdb->prepare($sql, $prep_array), 'ARRAY_A');

  $is_current_user_admin = false;
  $participants_info = array();

  foreach ($participant_info as $info) {
    $is_current_user_admin |= ($info['is_admin'] == 1 && $info['user_id'] == $user_id);
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_info[$info['user_id']] = $info;
  }

  for ($i=0; $i < count($messages); $i++) {
    $messages[$i]['message_content'] = apply_filters('the_content', $messages[$i]['message_content']);
    $messages[$i]['can_edit'] = ($messages[$i]['user_id'] == $user_id);
  }
  $messages = array_values($messages);

  $sql = "SELECT $users.display_name, $users.ID as user_id FROM $users WHERE $users.ID IN (SELECT DISTINCT(user_id) FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $participant_for_message_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');
  $participants_for_message_info = array();

  foreach($participant_for_message_info as $info) {
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_for_message_info[$info['user_id']] = $info;
  }

  $send_mail = $participants_info[$user_id]['send_email'] == 1;

  $thread_info['thread_title'] = apply_filters('the_title', $thread_info['thread_title']);

  return array("success" => array("send_email" => $send_mail, "is_current_user_admin" => $is_current_user_admin, 'thread_info' => $thread_info, 'participant_info' => $participants_info, 'message_info' => $messages, 'participants_for_message_info' => $participants_for_message_info));
}


function postane_get_messages_async($user_id, $thread_id, $exclusion_list, $min_time) {
  global $wpdb;
  global $postane_user_message;
  global $postane_messages;
  global $postane_threads;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*)  FROM $postane_user_thread WHERE thread_id = %d AND user_id = $user_id";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "SELECT COUNT(*) FROM $postane_threads WHERE id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_SUCH_THREAD_ERROR);
  }

  $sql = "UPDATE $postane_user_thread SET last_read_time = CURRENT_TIMESTAMP WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));


  $users = $wpdb->users;

  $sql = "SELECT $postane_user_thread.*, $users.display_name FROM $postane_user_thread INNER JOIN $users ON $postane_user_thread.user_id = $users.ID WHERE $postane_user_thread.thread_id = %d";
  $participant_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_messages.*, $postane_user_message.read FROM $postane_messages INNER JOIN $postane_user_message ON $postane_user_message.message_id = $postane_messages.ID WHERE $postane_user_message.user_id= %d AND $postane_messages.thread_id = %d AND $postane_messages.id NOT IN (";

  $arr_len = count($exclusion_list);

  for ($i = 0; $i < $arr_len; $i++) {
    $sql .= "%d";

    if ($i != $arr_len - 1) {
      $sql .= ",";
    }
  }

  $sql .= ") AND $postane_messages.message_creation_time > '%s' ORDER BY $postane_messages.message_creation_time ASC";

  $prep_array = array($user_id, $thread_id);
  $prep_array = array_merge($prep_array, $exclusion_list);
  $prep_array[] = $min_time;

  $messages = $wpdb->get_results($wpdb->prepare($sql, $prep_array), 'ARRAY_A');

  $participants_info = array();

  foreach ($participant_info as $info) {
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_info[$info['user_id']] = $info;
  }

  for ($i=0; $i < count($messages); $i++) {
     $messages[$i]['message_content'] = apply_filters('the_content', $messages[$i]['message_content']);
     $messages[$i]['can_edit'] = ($messages[$i]['user_id'] == $user_id);
  }
  $messages = array_values($messages);

  $sql = "SELECT $users.display_name, $users.ID as user_id FROM $users WHERE $users.ID IN (SELECT DISTINCT(user_id) FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $participant_for_message_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');
  $participants_for_message_info = array();

  foreach ($participant_for_message_info as $info) {
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_for_message_info[$info['user_id']] = $info;
  }

  $thread_info['thread_title'] = apply_filters('the_title', $thread_info['thread_title']);

  return array("success" => array('message_info' => $messages, 'participants_for_message_info' => $participants_for_message_info));
}


function postane_get_threads($user_id, $exclusion_list, $step) {
  global $wpdb;
  global $postane_threads;
  global $postane_user_thread;

  $users = $wpdb->users;
  $sql = "SELECT $postane_threads.id as thread_id,$postane_threads.thread_title as thread_title,$postane_threads.last_message_time as thread_last_message_time,$postane_user_thread.last_read_time as thread_last_read_time FROM $postane_threads INNER JOIN $postane_user_thread ON $postane_user_thread.thread_id = $postane_threads.id WHERE $postane_user_thread.user_id = $user_id AND $postane_threads.id NOT IN (";

  $arr_len = count($exclusion_list);
  for($i = 0; $i < $arr_len; $i++) {
    $sql .= "%d";
    if($i != $arr_len-1) {
      $sql .= ",";
    }
  }

  $sql .= ") ORDER BY $postane_threads.last_message_time DESC LIMIT %d";
  $exclusion_list[] = $step;
  $results = $wpdb->get_results($wpdb->prepare($sql, $exclusion_list));
  $ret_array = array();

  foreach ($results as $result) {
    $thread_title = apply_filters('the_title', $result->thread_title);
    $t_id = $result->thread_id;
    $sql = "SELECT $users.ID as user_id, $users.display_name as display_name FROM $postane_user_thread INNER JOIN $users ON $postane_user_thread.user_id = $users.ID WHERE $users.ID != $user_id AND $postane_user_thread.thread_id = $t_id";
    $parts = $wpdb->get_results($sql, 'ARRAY_A');
    $participants = array();

    foreach ($parts as $part) {
      $avatar = get_avatar($part['user_id']);
      $link = get_site_url() . '?author=' . $part['user_id'];
      $part['avatar'] = $avatar;
      $part['link'] = $link;
      $participants[] = $part;
    }

    $ret_array[] = array("thread_id" => $result->thread_id, "thread_title" => $thread_title, "thread_last_message_time" => $result->thread_last_message_time, "thread_last_read_time" => $result->thread_last_read_time, "participants" => $participants);
  }
  return $ret_array;
}

function postane_add_participants($user_id, $thread_id, $participants) {
  global $wpdb;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*) as c FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d AND is_admin = 1";
  $ret = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($ret == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION);
  }

  $participant_ids = array();

  foreach ($participants as $participant) {
    $ID = postane_get_user_id($participant);

    if ($ID && $ID != $user_id && !in_array($ID, $participant_ids)) {
      $participant_ids[] = $ID;
    }
  }

  if (empty($participant_ids)) {
    return array("error" => PostaneLang::NO_LEGIT_PARTICIPANTS);
  }

  foreach ($participant_ids as $p_id) {
    $sql = "INSERT INTO $postane_user_thread (user_id, thread_id) VALUES ($p_id, %d)";
    $wpdb->query($wpdb->prepare($sql, $thread_id));
  }

  return array("success" => true);
}

function postane_add_message($user_id, $thread_id, $message_content) {
  global $wpdb;
  global $postane_threads;
  global $postane_user_thread;
  global $postane_messages;
  global $postane_user_message;

  $sql = "SELECT COUNT(*) FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "SELECT COUNT(*) FROM $postane_threads WHERE id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::NO_SUCH_THREAD_ERROR);
  }

  $sql = "INSERT INTO $postane_messages (thread_id,user_id,message_content) VALUES (%d, $user_id, '%s')";
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

    if ($p_id != $user_id) {
      $sql = "INSERT INTO $postane_user_message (user_id, message_id) VALUES ($p_id, $message_id)";
      $wpdb->query($sql);
    }
  }

  $print_content = apply_filters('the_content', $message_content);
  $avatar = get_avatar($user_id);
  $author_url = get_site_url() . '/?author=' . $info['user_id'];
  $dt = new DateTime($value);
  $dt->setTimezone(new DateTimeZone('Europe/Istanbul'));
  $message_time = $dt->format("Y-m-d H:i:s");
  $username = get_userdata($user_id)->display_name;


  $sql = "SELECT user_id FROM $postane_user_thread WHERE send_email = 1 AND $postane_user_thread.thread_id = %d AND user_id != $user_id";
  $user_ids = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');
  $sql = "SELECT thread_title FROM $postane_threads WHERE id = %d";
  $thread_title = $wpdb->get_var($wpdb->prepare($sql, $thread_id));
  $postane_url = get_site_url() . '/postane';

  $headers = 'From: 22dakika.org <noreply@22dakika.org>' . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=UTF-8\r\n";
  $subject = "$username şu konuşmaya cevap yazdı: '" . mb_substr($thread_title, 0, min(25, mb_strlen($thread_title))) . (mb_strlen($thread_title) > 25 ? "...'" : "'");

  foreach ($user_ids as $u_id) {
    $u_id = $u_id['user_id'];
    $recip_userdata = get_userdata($u_id);
    $recip_username = $recip_userdata->display_name;
    $recip_email = $recip_userdata->user_email;
    $content = "Merhaba $recip_username,<br/><br/>22dakika.org sitesinde $username '$thread_title' başlıklı konuşmaya cevap yazdı.<br/><br/>Okumak için lütfen aşağıdaki linki takip edin:<br/><a href='$postane_url/?postane_thread_id=$thread_id'>$postane_url/?postane_thread_id=$thread_id</a>";
    wp_mail($recip_email, $subject, $content, $headers);
  }

  return array("success" => array('username' => $username, 'message_content' => $print_content, 'avatar' => $avatar, 'author_url' => $author_url, 'message_time' => $message_time, 'message_id' => $message_id));
}

function postane_user_exists($user_name) {
  global $wpdb;
  $users = $wpdb->users;
  $sql = "SELECT COUNT(*) as c FROM $users WHERE display_name = '%s'";
  $res = $wpdb->get_row($wpdb->prepare($sql, $user_name))->c;
  return $res != 0;
}

function postane_send_email($user_id, $thread_id) {
  global $wpdb;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*) FROM $postane_user_thread WHERE thread_id = %d AND user_id = $user_id AND send_email = 0";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::ALREADY_RECEIVING_MAILS);
  }

  $sql = "UPDATE $postane_user_thread SET send_email = 1 WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  return array("success" => true);
}

function postane_unsend_email($user_id, $thread_id) {
  global $wpdb;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*) FROM $postane_user_thread WHERE thread_id = %d AND user_id = $user_id AND send_email = 1";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if ($res == 0) {
    return array("error" => PostaneLang::ALREADY_NOTRECEIVING_MAILS);
  }

  $sql = "UPDATE $postane_user_thread SET send_email = 0 WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  return array("success" => true);
}

function postane_godmode_get_all_threads() {
  global $wpdb;
  global $postane_threads;
  global $postane_user_thread;
  $users = $wpdb->users;

  $sql = "SELECT $postane_threads.id as thread_id,$postane_threads.thread_title as thread_title,$postane_threads.last_message_time as thread_last_message_time FROM $postane_threads ORDER BY $postane_threads.last_message_time DESC";
  $results = $wpdb->get_results($sql, 'ARRAY_A');

  foreach ($results as $result) {
    $thread_title = apply_filters('the_title', $result['thread_title']);
    $thread_id = $result['thread_id'];
    $sql = "SELECT $users.ID as id,$users.display_name as display_name FROM $users INNER JOIN $postane_user_thread ON $users.ID = $postane_user_thread.user_id WHERE $postane_user_thread.thread_id = $thread_id";
    $participant_array = $wpdb->get_results($sql,  'ARRAY_A');
    $participant_ret_array = array();

    foreach ($participant_array as $part) {
      $id = $part['id'];
      $avatar = get_avatar($id);
      $link = get_site_url() . '/?author=' . $id;
      $part['avatar'] = $avatar;
      $part['link'] = $link;
      $participant_ret_array[] = $part;
    }
    $ret_array[] = array("thread_id" => $result['thread_id'], "thread_title" => $thread_title, "thread_last_message_time" => $result['thread_last_message_time'], "participants" => $participant_ret_array);
  }

  return $ret_array;
}

function postane_get_all_messages($thread_id) {
  global $wpdb;
  global $postane_user_message;
  global $postane_messages;
  global $postane_threads;
  global $postane_user_thread;


  $users = $wpdb->users;
  $sql = "SELECT * FROM $postane_threads WHERE $postane_threads.id = %d";
  $thread_info = $wpdb->get_row($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_user_thread.user_id, $users.display_name FROM $postane_user_thread INNER JOIN $users ON $postane_user_thread.user_id = $users.ID WHERE $postane_user_thread.thread_id = %d";
  $participant_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_messages.* FROM $postane_messages WHERE $postane_messages.thread_id = %d ORDER BY $postane_messages.message_creation_time ASC";

  $messages = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $participants_info = array();

  foreach ($participant_info as $info) {
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_info[$info['user_id']] = $info;
  }

  for ($i=0; $i < count($messages); $i++) {
     $messages[$i]['message_content'] = apply_filters('the_content', $messages[$i]['message_content']);
  }
  $messages = array_values($messages);

  $sql = "SELECT $users.display_name, $users.ID as user_id FROM $users WHERE $users.ID IN (SELECT DISTINCT(user_id) FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $participant_for_message_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');
  $participants_for_message_info = array();

  foreach ($participant_for_message_info as $info) {
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_for_message_info[$info['user_id']] = $info;
  }

  $thread_info['thread_title'] = apply_filters('the_title', $thread_info['thread_title']);

  return array('thread_info' => $thread_info, 'participant_info' => $participants_info, 'message_info' => $messages, 'participants_for_message_info' => $participants_for_message_info);
}

function postane_get_unread_thread_count($user_id) {
  global $wpdb;
  $users = $wpdb->users;
  global $postane_threads;
  global $postane_user_thread;
  $res = $wpdb->get_var("SELECT count(*) from $postane_threads INNER JOIN $postane_user_thread ON $postane_user_thread.thread_id=$postane_threads.id where $postane_user_thread.user_id=$user_id AND $postane_user_thread.last_read_time < $postane_threads.last_message_time");

  return array("success" => array("count" => $res));
}

function postane_setup_query($arr) {
  $query_var_types = array(
                            'postane_offset' => array('type' => 'int'), 
                            'postane_step' => array('type' => 'int'),
                            'postane_username' => array('type' => 'string'),
                            'postane_message_content' => array('type' => 'string'),
                            'postane_thread_title' => array('type' => 'string'),
                            'postane_participants' => array('type' => 'string_array'),
                            'postane_thread_id' => array('type' => 'int'),
                            'postane_exclusion_list' => array('type' => 'int_array'),
                            'postane_max_time' => array('type' => 'datetime'),
                            'postane_message_id' => array('type' => 'int'),
                            'postane_min_time' => array('type' => 'datetime'),
                            'postane_autocomplete_input' => array('type' => 'string')
                          );
  $query_vars = array();

  foreach ($arr as $key => $value) {
    if (array_key_exists($key, $query_var_types)) {
      switch ($query_var_types[$key]['type']) {
        case 'datetime':
          $dt = new DateTime($value);

          if ($value == null) {
            $dt->setTimezone(new DateTimeZone('Europe/Istanbul'));
          }

          $query_vars[$key] = $dt->format("Y-m-d H:i:s");
          break;
        case 'int':
          $query_vars[$key] = (int)$value;
          break;
        case 'string':
          $query_vars[$key] = stripslashes(strval($value));
          break;
        case 'string_array':
          $arr = array();

          if (is_array($value)) {
            foreach ($value as $val) {
              $arr[] = stripslashes(strval($val));
            }
          } else {
            $arr[] = stripslashes(strval($value));
          }

          $query_vars[$key] = $arr;
          break;
        case 'int_array':
          $arr = array();

          if (is_array($value)) {
            foreach ($value as $val) {
              $arr[] = (int)$val;
            }
          } else {
            $arr[] = (int)$value;
          }

          $query_vars[$key] = $arr;
          break;
      }
    }
  }

  return $query_vars;
}
?>
