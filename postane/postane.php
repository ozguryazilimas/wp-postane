<?php
/*
Plugin Name: Postane
Plugin URI: http://ozguryazilim.com.tr
Description: Private message plugin for 22dakika.org
Version: 1.0
Author: Baskın Burak Şenbaşlar
Author URI: http://ceng.metu.edu.tr/~e1942697
License: GPL
*/

/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/

/*
drop table wp_postane_user_message;
drop table wp_postane_user_thread;
drop table wp_postane_messages;
drop table wp_postane_threads;
*/

/*
delete from wp_postane_user_message;
delete from wp_postane_user_thread;
delete from wp_postane_messages;
delete from wp_postane_threads;
*/


require_once(plugin_dir_path(__FILE__) . 'postane_functions.php');
require_once(plugin_dir_path(__FILE__) . 'postane_lang_tr.php');
function postane_activate_plugin() {
  global $wpdb;
  $postane_threads = $wpdb->prefix . "postane_threads";
  $postane_messages = $wpdb->prefix . "postane_messages";
  $postane_user_thread = $wpdb->prefix . "postane_user_thread";
  $postane_user_message = $wpdb->prefix . "postane_user_message";
  $users = $wpdb->users;

  $sql = "
    CREATE TABLE IF NOT EXISTS `$postane_threads` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `thread_title` VARCHAR(200) NULL,
      `thread_creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `last_message_time` TIMESTAMP NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `id_UNIQUE` (`id` ASC))
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_turkish_ci;
  ";
  $wpdb->query($sql);
  $sql = "
    CREATE TABLE IF NOT EXISTS `$postane_messages` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `thread_id` INT NOT NULL,
      `user_id` BIGINT(20) UNSIGNED NOT NULL,
      `message_creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `message_content` VARCHAR(5000) NOT NULL,
      `edited` TINYINT NOT NULL DEFAULT 0,
      `edit_time` TIMESTAMP NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `id_UNIQUE` (`id` ASC),
      INDEX `message_to_thread_idx` (`thread_id` ASC),
      INDEX `message_to_user_idx` (`user_id` ASC),
      CONSTRAINT `fk_message_to_thread`
        FOREIGN KEY (`thread_id`)
        REFERENCES `$postane_threads` (`id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_message_to_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `$users` (`ID`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_turkish_ci;
  ";
  $wpdb->query($sql);

  $sql = "
    CREATE TABLE IF NOT EXISTS `$postane_user_thread` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `user_id` BIGINT(20) UNSIGNED NOT NULL,
      `thread_id` INT NOT NULL,
      `is_admin` TINYINT NOT NULL DEFAULT 0,
      `join_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `last_read_time` TIMESTAMP NOT NULL DEFAULT 0,
      `send_email` TINYINT NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `id_UNIQUE` (`id` ASC),
      INDEX `fk_user_thread_to_users_idx` (`user_id` ASC),
      INDEX `fk_user_thread_to_threads_idx` (`thread_id` ASC),
      UNIQUE INDEX `thread_pair_UNIQUE` (`user_id` ASC, `thread_id` ASC),
      CONSTRAINT `fk_user_thread_to_users`
        FOREIGN KEY (`user_id`)
        REFERENCES `$users` (`ID`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_user_thread_to_threads`
        FOREIGN KEY (`thread_id`)
        REFERENCES `$postane_threads` (`id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_turkish_ci;
  ";
  $wpdb->query($sql);

  $sql = "
    CREATE TABLE IF NOT EXISTS `$postane_user_message` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `user_id` BIGINT(20) UNSIGNED NOT NULL,
      `message_id` INT NOT NULL,
      `visible` TINYINT NOT NULL DEFAULT 1,
      `read` TINYINT NOT NULL DEFAULT 0,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `id_UNIQUE` (`id` ASC),
      INDEX `fk_user_message_to_users_idx` (`user_id` ASC),
      INDEX `fk_user_message_to_messages_idx` (`message_id` ASC),
      UNIQUE INDEX `user_message_unique_pair` (`user_id` ASC, `message_id` ASC),
      CONSTRAINT `fk_user_message_to_users`
        FOREIGN KEY (`user_id`)
        REFERENCES `$users` (`ID`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_user_message_to_messages`
        FOREIGN KEY (`message_id`)
        REFERENCES `$postane_messages` (`id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION)
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_turkish_ci;
  ";
  $wpdb->query($sql);
}
register_activation_hook(__FILE__,'postane_activate_plugin');

function postane_ajax() {
  if(!is_user_logged_in()) {
    wp_die();
  }
  $actions = array('', 'add_message', 'create_thread', 'edit_message', 'get_threads', 'get_messages', 'user_exists', 'get_messages_async', 'get_current_time', 'mark_message_read', 'add_participants', 'quit_thread', 'delete_all_messages', 'delete_message', 'mark_thread_read', 'autocomplete_username', 'send_email', 'unsend_email');
  $action = isset($_POST['postane_action']) ? $_POST['postane_action'] : '';

  $query_vars = postane_setup_query($_POST);

  if (!in_array($action, $actions)) {
    return;
  }

  $user_id = get_current_user_id();
  
  switch ($action) {
    case '':
      break;
    case 'send_email':
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      $ret = postane_send_email($user_id, $query_vars['postane_thread_id']);
      echo json_encode($ret);
      break;
    case 'unsend_email':
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      $ret = postane_unsend_email($user_id, $query_vars['postane_thread_id']);
      echo json_encode($ret);
      break;
    case 'autocomplete_username':
      if(!isset($query_vars['postane_autocomplete_input'])) {
        wp_die();
      }
      $ret = postane_autocomplete($query_vars['postane_autocomplete_input']);
      echo json_encode($ret);
      break;
    case 'delete_message': // makes a single message invisible to user.
      if(!isset($query_vars['postane_message_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_MESSAGE_ID_ERROR));
        wp_die();
      }
      $ret = postane_delete_message($user_id, $query_vars['postane_message_id']);
      echo json_encode($ret);
      break;
    case 'delete_all_messages': // makes every message in thread invisible to user.
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      $ret = postane_delete_all_messages($user_id, $query_vars['postane_thread_id']);
      echo json_encode($ret);
      break;
    case 'get_current_time':
      $dt = new DateTime();
      $dt->setTimezone(new DateTimeZone('Europe/Istanbul'));
      $current_time = $dt->format("Y-m-d H:i:s");
      echo json_encode(array("success" => array('current_time' => $current_time)));
      break;
    case 'add_message':
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_message_content']) || empty($query_vars['postane_message_content'])) {
        echo json_encode(array("error" => PostaneLang::NO_MESSAGE_CONTENT_ERROR));
        wp_die();
      }
      $message_content = $query_vars['postane_message_content'];
      $ret = postane_add_message($user_id, $query_vars['postane_thread_id'], $message_content);
      echo json_encode($ret);
      break;
    case 'create_thread': // creates thread
      if(!isset($query_vars['postane_thread_title']) || empty($query_vars['postane_thread_title'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_TITLE_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_message_content']) || empty($query_vars['postane_message_content'])) {
        echo json_encode(array("error" => PostaneLang::NO_MESSAGE_CONTENT_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_participants']) || empty($query_vars['postane_participants'])) {
        echo json_encode(array("error" => PostaneLang::NO_PARTICIPANTS_ERROR));
        wp_die();
      }
      $thread_title = $query_vars['postane_thread_title'];
      $thread_title = str_replace('<br>', '', $thread_title);
      $thread_title = str_replace('<br/>', '', $thread_title);
      $message_content = $query_vars['postane_message_content'];
      $participants = $query_vars['postane_participants'];
      $ret = postane_create_thread($user_id, $thread_title, $message_content, $participants);
      echo json_encode($ret);
      break;
    case 'edit_message': // edit message
      if(!isset($query_vars['postane_message_id']) || empty($query_vars['postane_message_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_MESSAGE_ID_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_message_content']) || empty($query_vars['postane_message_content'])) {
        echo json_encode(array("error" => PostaneLang::NO_MESSAGE_CONTENT_ERROR));
        wp_die();
      }
      $message_content = $query_vars['postane_message_content'];
      $ret = postane_edit_message($user_id, $query_vars['postane_message_id'], $message_content);
      echo json_encode($ret);
      break;
    case 'quit_thread': // deletes user from thread and unrelates every message of thread from user
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      $ret = postane_quit_thread($user_id, $query_vars['postane_thread_id']);
      echo json_encode($ret);
      break;
    case 'mark_message_read': // makes message 'read' for user
      if(!isset($query_vars['postane_message_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_MESSAGE_ID_ERROR));
        wp_die();
      }
      $ret = postane_mark_message_read($user_id, $query_vars['postane_message_id']);
      echo json_encode($ret);
      break;
    case 'mark_thread_read': // makes all messages of thread 'read' for user
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      $ret = postane_mark_thread_read($user_id, $query_vars['postane_thread_id']);
      echo json_encode($ret);
      break;
    case 'get_threads': // gets all threads user can see
      if(!isset($query_vars['postane_step'])) {
        echo json_encode(array("error" => PostaneLang::NO_STEP_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_exclusion_list'])) {
        echo json_encode(array("error" => PostaneLang::NO_EXCLUSION_LIST_ERROR));
        wp_die();
      }
      $ret = postane_get_threads($user_id, $query_vars['postane_exclusion_list'], $query_vars['postane_step']);
      echo json_encode(array("success" => $ret));
      break;
    case 'get_messages': // gets all messages of threads user can see
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_step'])) {
        echo json_encode(array("error" => PostaneLang::NO_STEP_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_exclusion_list'])) {
        echo json_encode(array("error" => PostaneLang::NO_EXCLUSION_LIST_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_max_time'])) {
        echo json_encode(array("error" => PostaneLang::NO_MAX_TIME_ERROR));
        wp_die();
      }
      $ret = postane_get_messages($user_id, $query_vars['postane_thread_id'], $query_vars['postane_exclusion_list'], $query_vars['postane_step'], $query_vars['postane_max_time']);
      echo json_encode($ret);
      break;
    case 'get_messages_async':
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_exclusion_list'])) {
        echo json_encode(array("error" => PostaneLang::NO_EXCLUSION_LIST_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_min_time'])) {
        echo json_encode(array("error" => PostaneLang::NO_MIN_TIME_ERROR));
        wp_die();
      }
      $ret = postane_get_messages_async($user_id, $query_vars['postane_thread_id'], $query_vars['postane_exclusion_list'], $query_vars['postane_min_time']);
      echo json_encode($ret);
      break;
    case 'god_mode_get_threads': // god mode.get all threads (only administrators according to wordpress core can do this)
      break;
    case 'god_mode_get_messages': // god mode.get all messages of any thread (only administrators according to wordpress core can do this)
      break;
    case 'add_participants': // adds participants to existing thread (only admins of thread can do this)
      if(!isset($query_vars['postane_participants']) || empty($query_vars['postane_participants'])) {
        echo json_encode(array("error" => PostaneLang::NO_PARTICIPANTS_ERROR));
        wp_die();
      }
      if(!isset($query_vars['postane_thread_id'])) {
        echo json_encode(array("error" => PostaneLang::NO_THREAD_ID_ERROR));
        wp_die();
      }
      $ret = postane_add_participants($user_id, $query_vars['postane_thread_id'], $query_vars['postane_participants']);
      echo json_encode($ret);
      break;
    case 'user_exists':
      if(!isset($query_vars['postane_username'])) {
        echo json_encode(array("error" => PostaneLang::NO_USERNAME_ERROR));
        wp_die();
      }
      $ret = postane_user_exists($query_vars['postane_username']);
      if ($ret) {
        echo json_encode(array("success" => $ret));
      } else {
        echo json_encode(array("error" => $ret));
      }
      break;
  }

  wp_die();
}
add_action('wp_ajax_postane', 'postane_ajax');


function postane_entry($attr) {
  if(!is_user_logged_in())
    return;
  global $wp_query;
  if(!isset($_GET['postane_god_mode'])) {
    wp_enqueue_script("postane_js", plugin_dir_url(__FILE__) . 'postane.js');
    wp_enqueue_script("jquery-ui-tooltip");
    wp_enqueue_script("jquery-ui-autocomplete");
    wp_enqueue_style("postane_css", plugin_dir_url(__FILE__) . 'postane.css');
    $plugin_dir_url = plugin_dir_url(__FILE__);
    ?>
    <script type="text/javascript">
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var postane_base = '<?php echo $plugin_dir_url; ?>';
    </script>
    <?php
    echo '<div id="postane">
            <div id="postane_notification">
              <div id="postane_loading">
                <img src="' . $plugin_dir_url . 'img/logo.png"/>
              </div>
              <div id="postane_error" style="display:none">
              </div>
              <div id="postane_success" style="display:none">
              </div>
            </div>
            <div id="postane_threads">
              <div id="postane_threads_topmenu">
                <div id="postane_new_thread_toggle">
                  Yeni Özel Mesaj
                </div>
                <div id="postane_new_thread">
                  Başlık:
                  <div id="postane_new_thread_title" contenteditable="true"></div>
                  Kime (enter):
                  <div id="postane_new_thread_participants" contenteditable="true"></div>
                  <div id="postane_new_thread_participant_list"></div>
                  Mesaj:
                  <div id="postane_new_thread_message" contenteditable="true"></div>
                  <div id="postane_new_thread_send">Gönder</div>
                </div>
              </div>
              <div id="postane_thread_container">
                <div id="postane_thread_more_button" style="display:none">
                  daha fazla...
                </div>
              </div>
            </div>
            <div id="postane_messages" style="display:none">
              <div id="postane_messages_topmenu">
                <div id="postane_messages_title">
                </div>
                <div id="postane_messages_buttons">
                  <div id="postane_messages_back_button">
                  </div>
                  <div id="postane_messages_participants_button" title="Katılımcılar">
                    <img src="' . $plugin_dir_url . 'img/people.png"/>
                  </div>
                  <div id="postane_messages_quit_button" title="Konuşmadan ayrıl">
                    <img src="' . $plugin_dir_url . 'img/cross.png"/>
                  </div>
                  <div id="postane_messages_addparticipant_button" style="display:none" title="Katılımcı ekle">
                    <img src="' . $plugin_dir_url . 'img/happy_user.png"/>
                  </div>
                </div>
                <div id="postane_message_email">
                  Bu konuşmaya yeni mesaj geldiğinde bana e-posta gönder.<input id="postane_email_checkbox" type="checkbox"/>
                </div>
              </div>
              <div id="postane_participants_container" style="display:none">
              </div>
              <div id="postane_add_participant_container" style="display:none">
                Kişi (enter): 
                <div id="postane_new_participant" contenteditable="true"></div>
                <div id="postane_new_participant_list"></div>
                <div id="postane_new_participants_send">Ekle</div>
              </div>
              <div id="postane_message_container">
                <div id="postane_message_more_button" style="display:none">
                  daha fazla...
                </div>
              </div>
              <div id="postane_new_message" contenteditable="true"></div>
              <div id="postane_new_message_send">Gönder</div>
            </div>
          <div id="postane_back" style="display:none" title="Konuşma listesine geri dön.">
             <img src="' . $plugin_dir_url . 'img/back.png"/>
          </div>
    ';
    if(current_user_can('administrator')) {
      echo '
        <div id="postane_godmode_button">
          <a href="' . get_permalink($post->ID) . '?postane_god_mode">GODMODE</a>
        </div>
      ';
    }
    echo ' 
        </div>
    ';
  } else {
    if(!current_user_can('administrator')) {
      return;
    }
    $godmode_actions = array('postane_list_threads', 'postane_show_thread');
    if(!isset($_GET['postane_action'])){
      $_GET['postane_action'] = 'postane_list_threads';
    }
    if(!in_array($_GET['postane_action'], $godmode_actions)) {
      return;
    }
    wp_enqueue_style("postane_godmode_style", plugin_dir_url(__FILE__) . "postane_godmode.css");
    if($_GET['postane_action'] == 'postane_list_threads') {
      $all_threads = postane_godmode_get_all_threads();
      $page_link = get_permalink($post->ID);
      echo "<div id='postane_godmode_return_postane'>
              <a href='$page_link'>Postane'ye geri dön</a>
            </div>";
      if(!empty($all_threads)) {
        echo "<table id='postane_godmode_thread_table'>";
        echo "<tr><th>Başlık</th><th>Kim kime?</th><th>Son mesaj zamanı</th>";
        $godmode_thread_base = $page_link . "?postane_god_mode&postane_action=postane_show_thread&postane_thread_id=";
        foreach($all_threads as $thread) {
          $title = $thread['thread_title'];
          $participants = $thread['participants'];
          $time = $thread['thread_last_message_time'];
          $thread_id = $thread['thread_id'];
          $thread_link = $godmode_thread_base . $thread_id;
          echo "<tr>";
          echo "<td><a href='$thread_link'>$title</a></td>";
          echo "<td>";
          $p_count = count($participants);
          for($i=0; $i<$p_count; $i++) {
            $link = $participants[$i]['link'];
            echo "<div class='postane_godmode_thread_table_participant'>";
            echo "<a href= '$link'>";
            echo "<div class='postane_godmode_thread_table_avatar'>";
            echo $participants[$i]['avatar'];
            echo "</div>";
            echo $participants[$i]['display_name'];
            echo "</a>";
            echo "</div>";
            if($i != $p_count-1) {
              echo ",";
            }
          }
          echo "</td>";
          echo "<td class='postane_godmode_time_column'>";
          echo $time;
          echo "</td>";
          echo "</tr>";
        }
        echo "</table>";
      }
    } else if ($_GET['postane_action'] == 'postane_show_thread') {
      $thread_id = (int)$_GET['postane_thread_id'];
      $mass_info = postane_get_all_messages($thread_id);
      $thread_info = $mass_info['thread_info'];
      $thread_title = $thread_info['thread_title'];
      $participants = $mass_info['participant_info'];
      $message_writers = $mass_info['participants_for_message_info'];
      $messages = $mass_info['message_info'];
      $page_link = get_permalink($post->ID);
      echo "<div id='postane_godmode_return_postane'>
              <a href='$page_link?postane_god_mode'>Konuşma listesine geri dön.</a>
            </div>";
      echo "<div id='postane_godmode_thread_info'>
              <div id='postane_godmode_thread_info_title'>
               Başlık: $thread_title
              </div>
              <div id='postane_godmode_thread_info_participants'>Kim kime: ";
      $p_count = count($participants);
      $i=0;
      foreach($participants as $key => $part) {
        $name = $part['display_name'];
        $avatar = $part['avatar'];
        $link = $part['author_url'];
        echo "
          <div class='postane_godmode_thread_info_participant'>
            <a href='$link'>
              <div class='postane_godmode_thread_table_participant'>
                <div class='postane_godmode_thread_table_avatar'>
                  $avatar
                </div>
                <span class='postane_godmode_hover_underline'>
                  $name
                </span>
              </div>
            </a>
          </div>
        ";
        if($i != $p_count-1) {
          echo ',';
        }
        $i++;
      }
      echo   "</div>";
      echo "<div id='postane_godmode_thread_info_messages'>";
      foreach($messages as $message) {
        $content = $message['message_content'];
        $author = $message_writers[$message['user_id']];
        $name = $author['display_name'];
        $avatar = $author['avatar'];
        $time = ($message['edited']==1 ? ("düzenlendi, ".$message['edit_time']) : $message['message_creation_time']);
        echo "<div class='postane_godmode_thread_info_message'>
                <div class='postane_godmode_thread_info_author'>
                  <div class='postane_godmode_thread_info_author_avatar'>
                    $avatar
                  </div>
                  <div class='postane_godmode_thread_info_author_name'>
                    $name
                  </div>
                </div>
                <div class='postane_godmode_thread_info_message_content'>
                  <div class='postane_godmode_thread_info_message_content_content'>
                    $content
                  </div>
                  <div class='postane_godmode_thread_info_message_content_time'>
                    $time
                  </div>
                </div>
              </div>";
      }
      echo "</div>";
      echo "
            </div>";
    }
  }
}
add_shortcode('postane', 'postane_entry');


?>
