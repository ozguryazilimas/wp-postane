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
  if($u_id) {
    return $u_id['ID'];
  }
  return false;
}

function postane_delete_message($user_id, $message_id) {
  global $wpdb;
  global $postane_user_message;

  $sql = "SELECT COUNT(*) FROM $postane_user_message WHERE $postane_user_message.user_id = $user_id AND $postane_user_message.message_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $message_id));

  if($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION);
  }

  $sql = "UPDATE $postane_user_message SET $postane_user_message.visible = 0 WHERE $postane_user_message.message_id = %d AND $postane_user_message.user_id = $user_id";
  $wpdb->query($wpdb->prepare($sql, $message_id));
  return array("success" => true);
}

function postane_delete_all_messages($user_id, $thread_id) {
  global $wpdb;
  global $postane_user_thread;
  global $postane_user_message;
  global $postane_messages;

  $sql = "SELECT COUNT(*) FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));
  if($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "UPDATE $postane_user_message SET $postane_user_message.visible = 0 WHERE $postane_user_message.user_id = $user_id AND $postane_user_message.message_id IN (SELECT id FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $wpdb->query($wpdb->prepare($sql, $thread_id));
  return array("success" => true);
}
function postane_create_thread($user_id, $thread_title, $first_message, $participants) {
  if(!is_array($participants)) {
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

  foreach ($participant_ids as $p_id) {
    $sql = "INSERT INTO $postane_user_thread (user_id,thread_id) VALUES ($p_id, $thread_id)";
    $wpdb->query($sql);

    $sql = "INSERT INTO $postane_user_message (user_id,message_id) VALUES ($p_id,$message_id)";
    $wpdb->query($sql);
  }
  return true;
}

function postane_edit_message($user_id, $message_id, $new_content) {
  global $wpdb;
  global $postane_threads;
  global $postane_messages;
  global $postane_user_message;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*) as c FROM $postane_messages WHERE user_id = $user_id AND id = %d";
  $count = $wpdb->get_row($wpdb->prepare($sql, $message_id),"ARRAY_A")->c;
  if($count === 0) {
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
  if($res == 0) {
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

  if($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "DELETE FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  $sql = "DELETE FROM $postane_user_message WHERE $postane_user_message.user_id = $user_id AND $postane_user_message.message_id IN (SELECT id FROM $postane_messages WHERE $postane_messages.thread_id = %d)";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

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
  if($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "SELECT COUNT(*) FROM $postane_threads WHERE id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));
  if($res == 0) {
    return array("error" => PostaneLang::NO_SUCH_THREAD_ERROR);
  }

  $sql = "UPDATE $postane_user_thread SET last_read_time = CURRENT_TIMESTAMP WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));

  $users = $wpdb->users;
  $sql = "SELECT * FROM $postane_threads WHERE $postane_threads.id = %d";
  $thread_info = $wpdb->get_row($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_user_thread.*, $users.display_name FROM $postane_user_thread INNER JOIN $users ON $postane_user_thread.user_id = $users.ID WHERE $postane_user_thread.thread_id = %d";
  $participant_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_messages.*, $postane_user_message.read FROM $postane_messages INNER JOIN $postane_user_message ON $postane_user_message.message_id = $postane_messages.ID WHERE $postane_user_message.user_id= %d AND $postane_messages.thread_id = %d AND $postane_user_message.visible = 1 AND $postane_messages.id NOT IN (";

  $arr_len = count($exclusion_list);
  for($i = 0; $i < $arr_len; $i++) {
    $sql .= "%d";
    if($i != $arr_len - 1) {
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
  foreach($participant_info as $info) {
    $is_current_user_admin |= ($info['is_admin'] == 1 && $info['user_id'] == $user_id);
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_info[$info['user_id']] = $info;
  }

  for($i=0; $i < count($messages); $i++) {
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

  $thread_info['thread_title'] = apply_filters('the_title', $thread_info['thread_title']);

  return array("success" => array("is_current_user_admin" => $is_current_user_admin, 'thread_info' => $thread_info, 'participant_info' => $participants_info, 'message_info' => $messages, 'participants_for_message_info' => $participants_for_message_info));
}


function postane_get_messages_async($user_id, $thread_id, $exclusion_list, $min_time) {
  global $wpdb;
  global $postane_user_message;
  global $postane_messages;
  global $postane_threads;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*)  FROM $postane_user_thread WHERE thread_id = %d AND user_id = $user_id";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));
  if($res == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION_FOR_THREAD);
  }

  $sql = "SELECT COUNT(*) FROM $postane_threads WHERE id = %d";
  $res = $wpdb->get_var($wpdb->prepare($sql, $thread_id));
  if($res == 0) {
    return array("error" => PostaneLang::NO_SUCH_THREAD_ERROR);
  }

  $sql = "UPDATE $postane_user_thread SET last_read_time = CURRENT_TIMESTAMP WHERE user_id = $user_id AND thread_id = %d";
  $wpdb->query($wpdb->prepare($sql, $thread_id));


  $users = $wpdb->users;

  $sql = "SELECT $postane_user_thread.*, $users.display_name FROM $postane_user_thread INNER JOIN $users ON $postane_user_thread.user_id = $users.ID WHERE $postane_user_thread.thread_id = %d";
  $participant_info = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');

  $sql = "SELECT $postane_messages.*, $postane_user_message.read FROM $postane_messages INNER JOIN $postane_user_message ON $postane_user_message.message_id = $postane_messages.ID WHERE $postane_user_message.user_id= %d AND $postane_messages.thread_id = %d AND $postane_user_message.visible = 1 AND $postane_messages.id NOT IN (";

  $arr_len = count($exclusion_list);
  for($i = 0; $i < $arr_len; $i++) {
    $sql .= "%d";
    if($i != $arr_len - 1) {
      $sql .= ",";
    }
  }

  $sql .= ") AND $postane_messages.message_creation_time > '%s' ORDER BY $postane_messages.message_creation_time ASC";


  $prep_array = array($user_id, $thread_id);
  $prep_array = array_merge($prep_array, $exclusion_list);
  $prep_array[] = $min_time;

  $messages = $wpdb->get_results($wpdb->prepare($sql, $prep_array), 'ARRAY_A');

  $participants_info = array();
  foreach($participant_info as $info) {
    $avatar_url = get_avatar($info['user_id']);
    $author_url = get_site_url() . '/?author=' . $info['user_id'];
    $info['avatar'] = $avatar_url;
    $info['author_url'] = $author_url;
    $participants_info[$info['user_id']] = $info;
  }

  for($i=0; $i < count($messages); $i++) {
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

  $thread_info['thread_title'] = apply_filters('the_title', $thread_info['thread_title']);

  return array("success" => array('message_info' => $messages, 'participants_for_message_info' => $participants_for_message_info));
}


function postane_get_threads($user_id, $exclusion_list, $step) {
  global $wpdb;
  global $postane_threads;
  global $postane_user_thread;
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
    $ret_array[] = array("thread_id" => $result->thread_id, "thread_title" => $thread_title, "thread_last_message_time" => $result->thread_last_message_time, "thread_last_read_time" => $result->thread_last_read_time);
  }
  return $ret_array;
}

function postane_god_mode_get_messages($user_id, $thread_id, $start_idx, $end_idx) {

}

function postane_god_mode_get_threads($user_id, $start_idx, $end_idx) {

}

function postane_add_participants($user_id, $thread_id, $participants) {
  global $wpdb;
  global $postane_user_thread;

  $sql = "SELECT COUNT(*) as c FROM $postane_user_thread WHERE user_id = $user_id AND thread_id = %d AND is_admin = 1";
  $ret = $wpdb->get_var($wpdb->prepare($sql, $thread_id));

  if($ret == 0) {
    return array("error" => PostaneLang::NO_AUTHORIZATION);
  }

  $participant_ids = array();
  foreach ($participants as $participant) {
    $ID = postane_get_user_id($participant);
    if ($ID && $ID != $user_id && !in_array($ID, $participant_ids)) {
      $participant_ids[] = $ID;
    }
  }

  if(empty($participant_ids)) {
    return array("error" => PostaneLang::NO_LEGIT_PARTICIPANTS);
  }

  foreach($participant_ids as $p_id) {
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
  if($res == 0) {
    return array("error" => PostaneLang::NO_SUCH_THREAD_ERROR);
  }

  $sql = "INSERT INTO $postane_messages (thread_id,user_id,message_content) VALUES (%d, $user_id, '%s')";
  $wpdb->query($wpdb->prepare($sql, array($thread_id, $message_content)));

  $message_id = $wpdb->insert_id;

  $sql = "SELECT user_id FROM $postane_user_thread WHERE thread_id = %d";
  $participants = $wpdb->get_results($wpdb->prepare($sql, $thread_id), 'ARRAY_A');


  $sql = "INSERT INTO $postane_user_message (user_id, message_id, $postane_user_message.read) VALUES ($user_id, $message_id, 1)";
  $wpdb->query($sql);

  foreach ($participants as $p_id) {
    $p_id = $p_id['user_id'];
    if($p_id != $user_id) {
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
  return array("success" => array('username' => $username, 'message_content' => $print_content, 'avatar' => $avatar, 'author_url' => $author_url, 'message_time' => $message_time, 'message_id' => $message_id));
}

function postane_user_exists($user_name) {
  global $wpdb;
  $users = $wpdb->users;
  $sql = "SELECT COUNT(*) as c FROM $users WHERE display_name = '%s'";
  $res = $wpdb->get_row($wpdb->prepare($sql, $user_name))->c;
  return $res != 0;
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
                            'postane_min_time' => array('type' => 'datetime')
                          );
  $query_vars = array();
  foreach ($arr as $key => $value) {
    if (array_key_exists($key, $query_var_types)) {
      switch ($query_var_types[$key]['type']) {
        case 'datetime':
          $dt = new DateTime($value);
          if($value == null) {
            $dt->setTimezone(new DateTimeZone('Europe/Istanbul'));
          }
          $query_vars[$key] = $dt->format("Y-m-d H:i:s");
          break;
        case 'int':
          $query_vars[$key] = (int)$value;
          break;
        case 'string':
          $query_vars[$key] = (string)$value;
          break;
        case 'string_array':
          $arr = array();
          if (is_array($value)) {
            foreach ($value as $val) {
              $arr[] = (string)$val;
            }
          } else {
            $arr[] = (string)$value;
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
