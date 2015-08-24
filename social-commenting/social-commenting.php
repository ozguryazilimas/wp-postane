<?php
/*
Plugin Name: Social Commenting
Plugin URI: http://ozguryazilim.com.tr
Description: Social commenting plugin for 22dakika.org
Version: 1.0
Author: Baskın Burak Şenbaşlar
Author URI: http://ceng.metu.edu.tr/~e1942697
License: GPL
*/


ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
function sc_activate_plugin() {
  global $wpdb;
  $table_name = $wpdb->prefix . "sc_subscribe";
  $users_table = $wpdb->prefix . "users";
  $posts_table = $wpdb->prefix . "posts";
  $sql="CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        send_mail BOOLEAN DEFAULT FALSE,
        last_read_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP) 
        CHARACTER SET utf8";
  $wpdb->query($sql);
}
register_activation_hook(__FILE__,'sc_activate_plugin');




function sc_uninstall_plugin() {
  global $wpdb;
  $table_name = $wpdb->prefix . "sc_subscribe";
  $sql="DROP TABLE $table_name";
  $wpdb->query($sql);
}
register_uninstall_hook(__FILE__, "sc_uninstall_plugin");





function sc_mention_mail_profile_setting() {
  $single = true;
  $checked = get_user_meta(get_current_user_id(), "sc_mention_mail",$single);
  ?>
        <table class="form-table">
          <tbody>
            <tr class="user-description-wrap">
	            <th><label for="description">Yorumlarda mention aldığımda mail gelsin.</label></th>
	            <td><input <?php if($checked == "true") echo "checked"; ?> name="sc_mention" id="sc_mention" type="checkbox" value="ok"/></td>
            </tr>
          </tbody>
        </table>
  <?php
}
add_action('show_user_profile','sc_mention_mail_profile_setting');
add_action('edit_user_profile','sc_mention_mail_profile_setting');



function sc_profile_update($user_id) {
  $new_status = $_POST['sc_mention'];
  if ($new_status == "ok") {
    update_user_meta($user_id, "sc_mention_mail", "true");
  } else {
    delete_user_meta($user_id, "sc_mention_mail");
  }
}
add_action('profile_update','sc_profile_update');







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

function sc_get_mention_list($content) {
  $initiator = '@';
  $max_number_of_words = sc_username_max_words();

  $mention_list = array();
  
  $content_length = strlen($content);
  $loc = stripos($content, $initiator);

  while($loc !== false) {
    $current_user = null;
    if($loc == 0 || $content[$loc-1] == ' ' || ctype_punct($content[$loc-1])) {
      $word_count=1;
      $last_word = '';
      $s_index = $loc;
      while($word_count <= $max_number_of_words) {
        if ($content[$s_index] == $initiator) {
          $s_index++;
          for(; $s_index < $content_length && $content[$s_index] != ' '; $s_index++) {
            $last_word .= $content[$s_index];
          } 
        } else if ($content[$s_index] == ' ') {
          $s_index++;
          $last_word .= ' ';
          for(; $s_index < $content_length && $content[$s_index] != ' '; $s_index++) {
            $last_word .= $content[$s_index];
          }
        } else {
          break;
        }
        if(sc_user_exists($last_word)) {
          $current_user = $last_word;
        }
      }
      $word_count++;
    }
    if($current_user !== null) {
      $mention_list[] = $current_user;
    }
    $loc = stripos($content, $initiator,$loc+1);
  }

  return $mention_list;
}

function sc_get_userid($display_name) {
  global $wpdb;
  $users_table = $wpdb->prefix . "users";
  $sql = "SELECT ID FROM $users_table WHERE display_name = '%s'";
  $res = $wpdb->get_row($wpdb->prepare($sql,$display_name))->ID;
  return $res;
}

function sc_get_post_subscribers_for_email($post_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . "sc_subscribe";
  $sql = "SELECT user_id FROM $table_name WHERE post_id=%d AND send_mail=1";
  $res = $wpdb->get_results($wpdb->prepare($sql,$post_id),"ARRAY_N");
  $ret_arr = array();
  foreach($res as $key) {
    $ret_arr[] = $key[0];
  }
  return $ret_arr;
}

function sc_new_comment($comment_id) {
  $comment = get_comment($comment_id);
  $post = get_post($comment->comment_post_ID);
  $mention_mail_list = array();

  $mention_list = sc_get_mention_list($comment->comment_content);

  $current_user_id = get_current_user_id();


  $comment_link = get_comment_link($comment_id);
  echo $comment_link;
  foreach($mention_list as $key) {
    $user_id = sc_get_userid($key);
    $single = true;
    $applicable = get_user_meta($user_id, "sc_mention_mail",$single);

    if($applicable == "true" && ($user_id != $current_user_id)) {
      $mention_mail_list[] = $user_id;
    }
  }

  $subscriber_list = sc_get_post_subscribers_for_email($post->ID);

  $subscriber_mail_list = array();

  foreach($subscriber_list as $key) {
    if ($key != $current_user_id) {
      $subscriber_mail_list[] = $key;
    }  
  }

  if(!empty($subscriber_mail_list)) {
    $headers = 'From: 22dakika.org <noreply@22dakika.org>' . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $subject = 'Takip ettiğiniz yazıya yorum yazıldı.';
    $content = '" '.$post->post_title.' " başlıklı yazıya '.$comment->comment_author." cevap yazdı.\n\nYazıya gitmek için tıklayınız: ".$comment_link;
    //echo $content;
    foreach($subscriber_mail_list as $u_id) {
      $udata=get_userdata($u_id);
      $email=$udata->user_email;
      $uname=$udata->display_name;
      wp_mail( $email, $subject, 'Merhaba '.$uname.",\n\n".$content, $headers);
    }
  }

  if(!empty($mention_mail_list)) {
    $headers = 'From: 22dakika.org <noreply@22dakika.org>' . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $subject = 'Bir yazıda adınız anıldı.';
    $content = '" '.$post->post_title.' " başlıklı yazıda '.$comment->comment_author." sizi andı.\n\nYazıya gitmek için tıklayınız: ".$comment_link;
    //echo $content;
    foreach($mention_mail_list as $u_id) {
      $udata=get_userdata($u_id);
      $email=$udata->user_email;
      $uname=$udata->display_name;
      wp_mail( $email, $subject, 'Merhaba '.$uname.",\n\n".$content, $headers);
    }
  }
//echo $comment_link;
  //print_r($mention_mail_list);
  //print_r($subscriber_mail_list);die();
  //print_r($mail_list);
//  die();
}
add_action("comment_post","sc_new_comment");

function sc_user_subscribed($user_id,$post_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . "sc_subscribe";
  $sql="SELECT count(*) as c FROM $table_name WHERE user_id=$user_id AND post_id=%d";
  $count = $wpdb->get_row($wpdb->prepare($sql,$post_id))->c;
  return $count > 0;
}

function sc_load_subscribe_button() {
  $plugin_dir = plugin_dir_url(__FILE__);
  wp_enqueue_script("sc_subscribe_button_js",$plugin_dir . "js/subscribe.php?sc_url=" . admin_url('admin-ajax.php'));
  wp_enqueue_style("sc_subscribe_button_css",$plugin_dir . "css/subscribe.css");

  $post_id = get_the_ID();
  $current_user = get_current_user_id();

  $subscribed = sc_user_subscribed($current_user,$post_id);
  if ($subscribed) {
    echo "<div class='sc_subscribe_button sc_subscribed' data-postid='$post_id'>
            <div class='sc_minus sc_display'/>
              <img src='$plugin_dir/img/minus.png'/>
              Bu yazıyı takip etme
            </div>
            <div class='sc_plus'>
              <img src='$plugin_dir/img/plus.png'/>
              Bu yazıyı takip et
              </div>
          </div>";
  } else {
    echo "<div class='sc_subscribe_button' data-postid='$post_id'>
            <div class='sc_minus'/>
              <img src='$plugin_dir/img/minus.png'/>
              Bu yazıyı takip etme
            </div>
            <div class='sc_plus sc_display'>
              <img src='$plugin_dir/img/plus.png'/>
              Bu yazıyı takip et
              </div>
          </div>";
  }
}

add_action("comment_form","sc_load_subscribe_button");


function sc_subscribe($user_id, $post_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . "sc_subscribe";
  $sql = "INSERT INTO $table_name (user_id,post_id) VALUES ($user_id,%d)";
  $wpdb->query($wpdb->prepare($sql, $post_id));
}

function sc_subscribe_ajax() {
  $post_id = (int)$_POST['post_id'];
  $user_id = get_current_user_id();
  if (!sc_user_subscribed($user_id, $post_id)){
    sc_subscribe($user_id, $post_id);
  }
  echo "done";
  wp_die();
}
add_action('wp_ajax_sc_subscribe', 'sc_subscribe_ajax');
add_action('wp_ajax_nopriv_sc_subscibe', 'sc_subscribe_ajax');

function sc_unsubscribe($user_id, $post_id) {
  global $wpdb;
  $table_name = $wpdb->prefix . "sc_subscribe";
  $sql="DELETE FROM $table_name WHERE user_id=$user_id AND post_id=%d";
  $wpdb->query($wpdb->prepare($sql, $post_id));
}

function sc_unsubscribe_ajax() {
  $post_id = (int)$_POST['post_id'];
  $user_id = get_current_user_id();
  if (sc_user_subscribed($user_id, $post_id)) {
    sc_unsubscribe($user_id, $post_id);
  }
  echo "done";
  wp_die();
}
add_action('wp_ajax_sc_unsubscribe','sc_unsubscribe_ajax');
add_action('wp_ajax_nopriv_sc_unsubscribe','sb_unsubscribe_ajax');


function sc_user_email($user_id, $post_id) {
  global $wpdb;
  $plugin_table = $wpdb->prefix . "sc_subscribe";
  $sql = "SELECT COUNT(*) as c FROM $plugin_table WHERE send_mail=1 AND user_id=$user_id AND post_id=$post_id";
  $res = $wpdb->get_row($sql)->c;
  return $res > 0;
}

class sc_Widget extends WP_Widget {
  public function __construct() {
    parent::__construct('sc_widget',
                          __('Subscribed posts','text-domain'),
                          array("description" => __("Shows the subscribed posts -- social commenting plugin",'text-domain'))
                        );
  }
  public function widget($args, $instance) {
    global $wpdb;

    wp_enqueue_style("sc_widget_style",plugin_dir_url(__FILE__) . "css/widget.css");
    wp_enqueue_script("sc_widget_script",plugin_dir_url(__FILE__) . "js/widget.php?sc_url=" . admin_url('admin-ajax.php'));

    $posts_table = $wpdb->prefix . "posts";
    $comments_table = $wpdb->prefix . "comments";
    $plugin_table = $wpdb->prefix . "sc_subscribe";

    $post_count = $instance['count'];
    

    echo $args['before_widget'];
    echo $args['before_title'];
    echo $instance['title'];
    echo $args['after_title'];


    $user_id = get_current_user_id();
    $post_count++;
    $sql = "(SELECT DISTINCT(A.ID) as ID FROM (SELECT $posts_table.ID as ID FROM $posts_table INNER JOIN $comments_table ON $comments_table.comment_post_ID = $posts_table.ID WHERE $posts_table.ID IN (SELECT post_id FROM $plugin_table WHERE user_id=$user_id) ORDER BY $comments_table.comment_date DESC) as A LIMIT $post_count) UNION (SELECT DISTINCT(post_id) AS ID FROM $plugin_table WHERE post_id NOT IN (SELECT DISTINCT($posts_table.ID) as ID FROM $posts_table INNER JOIN $comments_table ON $comments_table.comment_post_ID = $posts_table.ID WHERE $posts_table.ID IN (SELECT post_id FROM $plugin_table WHERE user_id=$user_id)) AND user_id=$user_id LIMIT $post_count)";
    $post_count--;
    $res = $wpdb->get_results($sql, "ARRAY_A");   //print_r($res); 
    echo "<table class='sc_widget_post_list'>";
    $top=1;
    $need_all_button = false;
    foreach ($res as $key) {
      if($top > $post_count) {
        $need_all_button = true;
        break;
      }
      $post = get_post($key['ID']);
      $sql = "SELECT COUNT(*) as c FROM $posts_table INNER JOIN $comments_table ON $posts_table.ID = $comments_table.comment_post_ID WHERE $posts_table.ID = ".$key['ID']." AND $comments_table.comment_date > (SELECT last_read_time FROM $plugin_table WHERE $plugin_table.user_id=$user_id AND $plugin_table.post_id=".$key['ID'].")";

      $email_subscribed = sc_user_email($user_id, $key['ID']);

      $post_title = mb_substr($post->post_title,0,25);
      $comment_count = $wpdb->get_row($sql)->c;

      $sql = "SELECT comment_ID as ID FROM $comments_table WHERE comment_post_ID=" . $key['ID'] . " AND comment_date > (SELECT last_read_time FROM $plugin_table WHERE $plugin_table.user_id = $user_id AND $plugin_table.post_id=".$key['ID'].") ORDER BY comment_date ASC LIMIT 1";

      $first_unread_comment_id = $wpdb->get_row($sql)->ID;
      $first_unread_comment_link = null;

      if($first_unread_comment_id != NULL)
        $first_unread_comment_link = get_comment_link($first_unread_comment_id);
      else
        $first_unread_comment_link = get_permalink($key['ID']);

      echo "<tr class='sc_widget_post'>
              <td class='sc_widget_post_title'>
                <a href='".$first_unread_comment_link."'><div class='sc_widget_title_div'>$post_title".(mb_strlen($post_title)==mb_strlen($post->post_title)?"":"...")."</div></a>
              </td>
              <td class='sc_widget_comment_count'>
                $comment_count
              </td>
              <td class='sc_widget_email_me' data-post-id='".$key['ID']."'>
                <img  title='Bu yazıya gelen yorumlarda mail almak için tıklayın.' alt='Bu yazıya gelen yorumlarda mail almak için tıklayın.' class='sc_widget_email_ok ". ($email_subscribed ? '':'sc_widget_display') ."' src='".plugin_dir_url(__FILE__)."img/email-ok.png'/>
                <img title='Bu yazıya gelen yorumlarda mail almayı iptal etmek için tıklayın.' alt='Bu yazıya gelen yorumlarda mail almayı iptal etmek için tıklayın.' class='sc_widget_email_no ". ($email_subscribed ? 'sc_widget_display':'') ."' src='".plugin_dir_url(__FILE__)."img/email-no.png'/>
              </td>
            </tr>";
      $top++;
    }
    echo "</table>";
    if($need_all_button) {
      $url = get_site_url() . "/sc_subscribed";
      echo "<a href='$url'>
        <div class='sc_widget_more_button'>
          Devamı...
        </div></a>
        ";
    } else {
       $url = get_site_url() . "/sc_subscribed";
      echo "<a class='sc_widget_more_button_link' href='$url'>
        <div class='sc_widget_more_button'>
          Takip sayfası
        </div></a>
        ";
    }



    echo $args['after_widget'];
  }
  public function form($instance) {
    $title = empty($instance['title']) ? __("Takip ettiğiniz yazılar","text-domain") : $instance['title'];
    $count = empty($instance['count']) ? 10 : $instance['count'];
    ?>
      <p>
		  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Başlık:' ); ?></label> 
		  <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		  </p>
      <p>
		  <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Gösterilecek maximum yazı adedi:' ); ?></label> 
		  <input size="3" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>">
		  </p>
    <?php
  }
  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['title'] = empty($new_instance['title']) ? (empty($old_instance['title']) ? __("Takip ettiğiniz yazılar",'text-domain') : $old_instance['title']) : $new_instance['title'];
    $instance['count'] = empty($new_instance['count']) ? (empty($old_instance['count']) ? 10 : $old_instance['count']) : $new_instance['count'];
    return $instance;
  }
}

function sc_init_widget() {
  register_widget("sc_Widget");
}

add_action("widgets_init","sc_init_widget");

function sc_update_last_read_time($post_id) {
  global $wpdb;
  $user_id = get_current_user_id();
  if(sc_user_subscribed($user_id, $post_id)) {
    $plugin_table = $wpdb->prefix . "sc_subscribe";
    $sql = "UPDATE $plugin_table SET last_read_time=NOW() WHERE user_id=$user_id AND post_id=$post_id";
    $wpdb->query($sql);
  }
}
add_action("comment_form","sc_update_last_read_time");

function sc_get_email_ajax() {
  global $wpdb;
  $post_id = (int) $_POST['post_id'];
  $user_id = get_current_user_id();
  if(sc_user_subscribed($user_id,$post_id)) {
    $plugin_table = $wpdb->prefix . "sc_subscribe";
    $sql = "UPDATE $plugin_table SET send_mail=1 WHERE user_id=$user_id AND post_id=$post_id";
    $wpdb->query($sql);
  }
  echo "done";
  wp_die();
}

add_action('wp_ajax_sc_get_email','sc_get_email_ajax');
add_action('wp_ajax_nopriv_sc_get_email','sb_get_email_ajax');

function sc_dont_get_email_ajax() {
  global $wpdb;
  $post_id = (int) $_POST['post_id'];
  $user_id = get_current_user_id();
  if(sc_user_subscribed($user_id,$post_id)) {
    $plugin_table = $wpdb->prefix . "sc_subscribe";
    $sql = "UPDATE $plugin_table SET send_mail=0 WHERE user_id=$user_id AND post_id=$post_id";
    $wpdb->query($sql);
  }
  echo "done";
  wp_die();
}

add_action('wp_ajax_sc_dont_get_email','sc_dont_get_email_ajax');
add_action('wp_ajax_nopriv_sc_dont_get_email','sb_dont_get_email_ajax');

function sc_mark_as_read_ajax() {
  global $wpdb;
  $user_id = get_current_user_id();
  $plugin_table = $wpdb->prefix . "sc_subscribe";

  $sql = "UPDATE $plugin_table SET last_read_time = CURRENT_TIMESTAMP WHERE user_id=$user_id";
  $wpdb->query($sql);
  echo "done";
  wp_die();
}

add_action("wp_ajax_sc_mark_as_read","sc_mark_as_read_ajax");
add_action("wp_ajax_nopriv_sc_mark_as_read","sc_mark_as_read_ajax");

function sc_page_load() {
  global $wp_query;
  if ($wp_query->query_vars['name'] == 'sc_subscribed') {
    wp_enqueue_style("sc_list_page_css",plugin_dir_url(__FILE__) . "css/list.css");
    wp_enqueue_script("sc_list_page_js",plugin_dir_url(__FILE__) . "js/list.php?sc_url=" . admin_url('admin-ajax.php'));
    $wp_query->is_404 = false;
    status_header(200);
    include(plugin_dir_path(__FILE__) . "subscribe-list.php");
    exit;
  }
}

add_action("template_redirect","sc_page_load");

?>
