<?php
get_header();
get_sidebar();

?>

<div class="leftpane article-page content">
  <article class="post-page cl">
    <div class="article-body">
      <div class="sc_mark_as_read_container">
        <div class="sc_mark_as_read">
          Hepsini okundu olarak işaretle
        </div>
      </div>
      <br>
<?php

global $wpdb;
$plugin_table = $wpdb->prefix . "sc_subscribe";
$posts_table = $wpdb->prefix . "posts";
$comments_table = $wpdb->prefix . "comments";

$user_id = get_current_user_id();

$sql = "(SELECT DISTINCT(A.ID) as ID FROM (SELECT $posts_table.ID as ID FROM $posts_table INNER JOIN $comments_table ON $comments_table.comment_post_ID = $posts_table.ID WHERE $posts_table.ID IN (SELECT post_id FROM $plugin_table WHERE user_id=$user_id) ORDER BY $comments_table.comment_date DESC) as A) UNION (SELECT DISTINCT(post_id) AS ID FROM $plugin_table WHERE post_id NOT IN (SELECT DISTINCT($posts_table.ID) as ID FROM $posts_table INNER JOIN $comments_table ON $comments_table.comment_post_ID = $posts_table.ID WHERE $posts_table.ID IN (SELECT post_id FROM $plugin_table WHERE user_id=$user_id)) AND user_id=$user_id)";


$res = $wpdb->get_results($sql,"ARRAY_A");

echo '<div class="sc_subscribe_list"/>';

echo "<div class='sc_h_subscribe_list_element'>
          <a><div class='sc_h_subscribe_list_element_title'>
            Yazı başlığı
          </div></a>
          <!--<div class='sc_h_subscribe_list_comment_count'>
            Yeni yorum
          </div>-->
          <div class='sc_h_subscribe_list_mail'>E-posta bildirimleri</div>
          <div class='sc_h_subscribe_list_mail' style='display:none'>E-posta</div>

        </div>
        ";

foreach ($res as $key) {
  $post = get_post($key['ID']);
  $post_id = $key['ID'];
  $post_title = mb_strlen($post->post_title) > 28 ? mb_substr($post->post_title,0,28) : $post->post_title;
  $email_subscribed = sc_user_email($user_id, $post_id);
  $sql = "SELECT COUNT(*) as c FROM $posts_table INNER JOIN $comments_table ON $posts_table.ID = $comments_table.comment_post_ID WHERE $posts_table.ID = $post_id AND $comments_table.comment_date > (SELECT last_read_time FROM $plugin_table WHERE $plugin_table.user_id=$user_id AND $plugin_table.post_id=$post_id)";
  $comment_count=$wpdb->get_row($sql)->c;

  $sql = "SELECT comment_ID as ID FROM $comments_table WHERE comment_post_ID=" . $key['ID'] . " AND comment_date > (SELECT last_read_time FROM $plugin_table WHERE $plugin_table.user_id = $user_id AND $plugin_table.post_id=".$key['ID'].") ORDER BY comment_date ASC LIMIT 1";
  $first_unread_comment_id = $wpdb->get_row($sql)->ID;
  $first_unread_comment_link = null;

  if ($first_unread_comment_id != NULL) {
    $first_unread_comment_link = get_comment_link($first_unread_comment_id);
  } else {
    $first_unread_comment_link = get_permalink($key['ID']);
  }

  echo "<div class='sc_subscribe_list_element'>
          <a href='".$first_unread_comment_link."'>
          <div class='sc_subscribe_list_element_title'>
            $post_title
            <div class='sc_subscribe_list_comment_count'>
              $comment_count
            </div>
          </div>
          </a>
           <div title='Yazıya yeni yorum geldiğinde e-posta almak için tıklayınız.' data-postid='$post_id' class='sc_subscribe_list_email_ok ".($email_subscribed ? "" : "sc_subscribe_list_display" )."'>kapalı</div>
           <div title='Yazıya yeni yorum geldiğinde e-posta almak istemiyorsanız tıklayınız.' data-postid='$post_id' class='sc_subscribe_list_email_no ".($email_subscribed ? "sc_subscribe_list_display" : "" )."'>açık</div>
           <div title='Yazıyı takibi bırakmak için tıklayınız.' class='sc_subscribe_list_unsubscribe' data-postid='$post_id'>
            takibi bırak.
          </div>
        </div>
    ";


}
echo '</div>';

?>

    </div>
  </article>
</div>
<?php

get_footer();
?>
