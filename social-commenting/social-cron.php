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

foreach ($wp_blog_path as $incpath) {
  if (file_exists($incpath)) {
    require($incpath);
    break;
  }
}

function sc_username_max_words() {
  global $wpdb;
  $users_table = $wpdb->prefix."users";
  $sql = "SELECT display_name,MAX(LENGTH(display_name)-LENGTH(REPLACE(display_name,' ',''))) as val FROM $users_table";
  $res = $wpdb->get_row($sql)->val;
  return $res+1;
}

function sc_user_exists($username) {
  global $wpdb;
  $users_table = $wpdb->prefix."users";
  $sql = "SELECT count(*) as c from $users_table where display_name = '%s'";
  $res = $wpdb->get_row($wpdb->prepare($sql,$username))->c;
  return $res==1;
}

function sc_get_userid($display_name) {
  global $wpdb;
  $users_table = $wpdb->prefix . "users";
  $sql = "SELECT ID FROM $users_table WHERE display_name = '%s'";
  $res = $wpdb->get_row($wpdb->prepare($sql,$display_name))->ID;
  return $res;
}

function sc_get_mention_list($content) {
  $initiator = '@';
  $max_number_of_words = sc_username_max_words();

  $mention_list = array();

  $content_length = strlen($content);
  $loc = stripos($content, $initiator);

  while ($loc !== false) {
    $current_user = null;

    if ($loc == 0 || $content[$loc-1] == ' ' || ctype_punct($content[$loc-1])) {
      $word_count=1;
      $last_word = '';
      $s_index = $loc;

      while ($word_count <= $max_number_of_words) {
        if ($content[$s_index] == $initiator) {
          $s_index++;

          for (; $s_index < $content_length && $content[$s_index] != ' '; $s_index++) {
            $last_word .= $content[$s_index];
          } 
        } else if ($content[$s_index] == ' ') {
          $s_index++;
          $last_word .= ' ';

          for (; $s_index < $content_length && $content[$s_index] != ' '; $s_index++) {
            $last_word .= $content[$s_index];
          }
        } else {
          break;
        }

        if (sc_user_exists($last_word)) {
          $current_user = $last_word;
        }
      }
      $word_count++;
    }

    if ($current_user !== null) {
      $mention_list[] = $current_user;
    }
    $loc = stripos($content, $initiator,$loc+1);
  }

  return $mention_list;
}

function sc_get_post_subscribers_for_email($post_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . "sc_subscribe";
  $sql = "SELECT user_id FROM $table_name WHERE post_id=%d AND send_mail=1";
  $res = $wpdb->get_results($wpdb->prepare($sql,$post_id),"ARRAY_N");
  $ret_arr = array();

  foreach ($res as $key) {
    $ret_arr[] = $key[0];
  }

  return $ret_arr;
}

global $wpdb;
$plugin_table = $wpdb->prefix . "sc_mail_queue";
$sql = "SELECT * FROM $plugin_table";
$results = $wpdb->get_results($sql);

$mail_contents = array();
$subscriber_mail_array = array();

foreach ($results as $res) {
  $comment_id = $res->comment_id;
  $comment = get_comment($comment_id);
  $post = get_post($comment->comment_post_ID);
  $mention_mail_list = array();
  $comment_link = get_comment_link($comment_id);

  if (isset($subscriber_mail_array[$post->ID])) {
    $subscriber_mail_array[$post->ID]['comment_count']++;
  } else {
    $subscriber_mail_array[$post->ID] = array('comment_count' => 1, 'post_title' => $post->post_title, 'comment_link' => $comment_link, 'comment_author' => $comment->user_id);
  }
  $mention_list = sc_get_mention_list($comment->comment_content);

  foreach ($mention_list as $key) {
    $user_id = sc_get_userid($key);
    $single = true;
    $applicable = get_user_meta($user_id, "sc_mention_mail",$single);

    if ($applicable == "true" && ($user_id != $current_user_id)) {
      $mention_mail_list[] = $user_id;
    }
  }


  if (!empty($mention_mail_list)) {
    $content = '" '.$post->post_title.' " başlıklı yazının yorumlarından birinde '.$comment->comment_author." sizi andı.<br/>Yoruma gitmek için tıklayınız: <a href='$comment_link'>".$comment_link."</a>";

    foreach ($mention_mail_list as $u_id) {
      if (!isset($mail_contents[$u_id])) {
        $mail_contents[$u_id] = array($content);
      } else {
        $mail_contents[$u_id][] = $content;
      }
    }
  }
}

foreach ($subscriber_mail_array as $post_id => $r_array) {
  $subscriber_list = sc_get_post_subscribers_for_email($post_id);
  $content = '" '.$r_array['post_title'].' " başlıklı yazının altında '.$r_array['comment_count'].' yeni yorum var.<br/>Okumadığınız yorumlara gitmek için tıklayınız: <a href="'.$r_array['comment_link'].'">'.$r_array['comment_link'].'</a>';

  foreach ($subscriber_list as $u_id) {
    if ($u_id != $r_array['comment_author']) {
      if (!isset($mail_contents[$u_id])) {
        $mail_contents[$u_id] = array($content);
      } else {
        $mail_contents[$u_id][] = $content;
      }
    }
  }
}

$headers = 'From: 22dakika.org <noreply@22dakika.org>' . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$subject = '22dakika.org Günlük Takip Ettiğiniz Yazı Raporu';

foreach ($mail_contents as $u_id => $content_array) {
  $udata=get_userdata($u_id);
  $email=$udata->user_email;
  $uname=$udata->display_name;
  $content = "Merhaba $uname,<br/><br/>";

  foreach ($content_array as $cont) {
    $content .= $cont;
    $content .= "<br/><br/>";
  }
  wp_mail($email, $subject, $content, $headers);
}

$wpdb->query("DELETE FROM $plugin_table");

?>
