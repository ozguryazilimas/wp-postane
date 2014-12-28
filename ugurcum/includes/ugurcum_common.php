<?php


class WP_Ugurcum_Widget extends WP_Widget {
  function WP_Ugurcum_Widget() {
    $widget_ops = array('classname' => 'wp_uw_widget', 'description' => __('Play it Sam', 'ugurcum'));
    $control_ops = array('width' => 350);
    $this->WP_Widget('ugurcum', __('Ugurcum', 'ugurcum'), $widget_ops, $control_ops);
  }

  function widget($args, $instance) {
    global $wpdb, $user_ID;

    $cache = wp_cache_get('widget_ugurcum', 'widget');

    if (!is_array($cache)) {
      $cache = array();
    }

    if (!isset($args['widget_id'])) {
      $args['widget_id'] = $this->id;
    }

    if (isset($cache[$args['widget_id']])) {
      echo $cache[$args['widget_id']];
      return;
    }

    extract($args, EXTR_SKIP);

    $output = '';
    $title = apply_filters('widget_title', empty($instance['title']) ?  '' : $instance['title'], $instance, $this->id_base);

    $output .= $before_widget;

    if ($title) {
      $output .= $before_title . $title . $after_title;
    }

    $output .= display_new_video_count();
    $output .= $after_widget;

    echo $output;
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

  function form($instance) {
    global $wp_uw;

    $instance_name = strip_tags($instance['instance']);
    $title = (isset($instance['title'])) ? strip_tags($instance['title']) : __('Play it Sam', 'ugurcum');

?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'ugurcum'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
<?php
  }
}


function ugurcum_get_time() {
  global $wp_query, $wpdb, $user_ID;

  // date_default_timezone_set('Europe/Istanbul');
  // $now = current_time('mysql', 1);

  $date = new DateTime("now", new DateTimeZone('Europe/Istanbul') );
  $now = $date->format('Y-m-d H:i:s');
  return $now;
}

function prepare_for_dt($medialink) {
  $ret = array(
    'id' => $medialink->id,
    'can_edit' => $medialink->can_edit == '1',
    'title' => $medialink->title,
    'description' => $medialink->description,
    'user' => $medialink->user_login,
    'medialink' => $medialink->medialink,
    'created_at' => date('H:i Y/m/d', strtotime($medialink->created_at)),
    'unread' => $medialink->unread == '1'
  );

  return $ret;
}

function ugurcum_get_media_links_json() {
  $raw_data = ugurcum_get_media_links();
  return array_map('prepare_for_dt', $raw_data);
}

function ugurcum_get_media_links() {
  global $wp_query, $wpdb, $user_ID, $ugurcum_db_main;

  if ($user_ID != '') {
    $last_read_time = ugurcum_get_last_read_time();
    $last_read_time_sql = "SELECT '$last_read_time' < um.updated_at";

    if (current_user_can('edit_others_posts')) {
      $can_edit_sql = "SELECT 1";
    } else {
      $can_edit_sql = "SELECT $user_ID = user_id";
    }
  } else {
    $last_read_time_sql = 'SELECT 0';
    $can_edit_sql = 'SELECT 0';
  }

  $get_media_links_sql = $wpdb->prepare("SELECT
                                           um.id as id,
                                           um.title as title,
                                           um.description as description,
                                           um.medialink as medialink,
                                           um.visible as visible,
                                           um.updated_at as updated_at,
                                           um.created_at as created_at,
                                           um.user_id as user_id,
                                           wpu.user_login as user_login,
                                           ($can_edit_sql) as can_edit,
                                           ($last_read_time_sql) as unread
                                         FROM $ugurcum_db_main as um
                                         JOIN
                                           $wpdb->users as wpu
                                         ON um.user_id = wpu.ID
                                         ORDER BY um.created_at desc
                                         ");

  return $wpdb->get_results($get_media_links_sql);
}

function ugurcum_display_media_links() {
  global $user_ID;
  $ret = '';

  $medialinks = ugurcum_get_media_links();
  $last_read_time = ugurcum_get_last_read_time();
  $current_time = ugurcum_get_time();
  $timeobj_last_read_time = strtotime($last_read_time);

  foreach($medialinks as $medialink) {
    $updated_at = strtotime($medialink->updated_at);

    if (($user_ID != '') && ($updated_at > $timeobj_last_read_time)) {
      $trclass = ' class="unread"';
    } else {
      $trclass = '';
    }

    $ret .= '
      <tr' . $trclass . '>
        <td>
          <a href="' . $medialink->medialink . '">'
            . $medialink->title .
          '</a>
        </td>
        <td>
          <a href="' . $medialink->medialink . '">'
            . $medialink->description .
          '</a>
        </td>
        <td>' . $medialink->user_login . '</td>
        <td>' . date('H:i Y/m/d', $updated_at) . '</td>
      </tr>';
  }

  return $ret;
}

function ugurcum_insert_media_link($series, $description, $media_link) {
  global $wp_query, $wpdb, $user_ID, $ugurcum_db_main;

  $current_time = ugurcum_get_time();

  $add_media_link_sql = "INSERT INTO $ugurcum_db_main
    (title, description, medialink, user_id, visible, created_at, updated_at)
    VALUES ('$series', '$description', '$media_link', $user_ID, true, '$current_time', '$current_time');";

  $success = $wpdb->query($add_media_link_sql);
}

function ugurcum_update_media_link($media_link_id, $title, $description, $media_link) {
  global $wp_query, $wpdb, $user_ID, $ugurcum_db_main;

  $user_check_sql = "AND user_id = $user_ID";
  $current_time = ugurcum_get_time();

  if (current_user_can('edit_others_posts')) {
    $user_check_sql = '';
  }

  $update_media_link_sql = $wpdb->prepare("
                            UPDATE $ugurcum_db_main
                            SET
                                title = '$title',
                                description = '$description',
                                medialink = '$media_link',
                                updated_at = '$current_time'
                            WHERE
                                id = $media_link_id
                                $user_check_sql
                            ");

  $success = $wpdb->query($update_media_link_sql);
}

function ugurcum_delete_media_link($media_link_id) {
  global $wp_query, $wpdb, $user_ID, $ugurcum_db_main;

  $user_check_sql = "AND user_id = $user_ID";
  if (current_user_can('edit_others_posts')) {
    $user_check_sql = '';
  }

  $delete_media_link_sql = $wpdb->prepare("DELETE FROM $ugurcum_db_main
                                           WHERE id = $media_link_id $user_check_sql");

  $success = $wpdb->query($delete_media_link_sql);
}

function ugurcum_get_last_read_time() {
  global $wpdb, $user_ID, $ugurcum_db_user_reads;

  if ($user_ID != '') {
    $user_last_read_sql = $wpdb->prepare("SELECT read_time
                                          FROM $ugurcum_db_user_reads
                                          WHERE
                                            user_id = $user_ID
                                            LIMIT 1;");

    $result = $wpdb->get_row($user_last_read_sql);
    $read_time = $result->read_time;
  } else {
    $read_time = date();
  }

  return $read_time;
}

// Record user read time
function ugurcum_set_time() {
  global $wpdb, $user_ID, $ugurcum_db_user_reads;

  if ($user_ID != '') {
    $read_time = ugurcum_get_time();

    $update_read_time_sql = "INSERT INTO $ugurcum_db_user_reads
      (user_id, read_time)
      VALUES ($user_ID, '$read_time')
      ON DUPLICATE KEY UPDATE read_time='$read_time';";

    $success = $wpdb->query($update_read_time_sql);
  }
}

function display_new_video_count() {
  global $wpdb, $user_ID, $ugurcum_db_user_reads, $ugurcum_db_main;

  $ret = '';
  $video_count_sql = "SELECT count(*) FROM $ugurcum_db_main ";

  if ($user_ID != '') {
    $last_read_time = ugurcum_get_last_read_time();
    $video_count_sql .= "WHERE updated_at > '$last_read_time'";
  }

  $new_video_count = $wpdb->get_var($video_count_sql);
  $video_count_str = sprintf(__("There are %s new videos you have not watched", 'ugurcum'), $new_video_count);

  $ret .= '<div id="ugurcum_widget_wrapper"><a href="/ugurcum">';
  $ret .=  $video_count_str;
  $ret .= '</a></div>';

  return $ret;
}

function ugurcum_get_youtube_video_id($youtube_url) {
  $video_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i';

  $video_id = preg_replace($video_pattern, '$1', $youtube_url);

  return $video_id;
}

function ugurcum_get_youtube_playlist_id($youtube_url) {
  $playlist_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i';

  $playlist_id = preg_replace($playlist_pattern, '$1', $youtube_url);

  return $playlist_id;
}

function ugurcum_get_dailymotion_video_id($dailymotion_url) {
  $video_id = strtok(basename($dailymotion_url), '_');

  return $video_id;
}

function ugurcum_get_vimeo_video_id($vimeo_url) {
  $video_id = strtok(basename($vimeo_url), '_');

  return $video_id;
}

function ugurcum_similar_youtube_sql($target_url) {
  $video_id = ugurcum_get_youtube_video_id($target_url);
  $ret = "medialink LIKE '%youtube.com/watch%v=$video_id%' OR medialink LIKE '%youtu.be/$video_id%'";

  return $ret;
}

function ugurcum_similar_dailymotion_sql($target_url) {
  $video_id = ugurcum_get_dailymotion_video_id($target_url);
  $ret = "medialink LIKE '%dailymotion.com/embed/video/$video_id%' OR medialink LIKE '%dailymotion.com/video/$video_id%'";

  return $ret;
}

function ugurcum_similar_vimeo_sql($target_url) {
  $video_id = ugurcum_get_vimeo_video_id($target_url);
  $ret = "medialink LIKE '%vimeo.com/video/$video_id%' OR medialink LIKE '%vimeo.com/$video_id%'";

  return $ret;
}

function ugurcum_find_similar_links($target_url) {
  global $wpdb, $ugurcum_db_main;

  if (strstr($target_url, 'youtube.com/watch') || strstr($target_url, 'youtu.be/')) {
    $where_clause = ugurcum_similar_youtube_sql($target_url);
  } else if (strstr($target_url, 'dailymotion.com/')) {
    $where_clause = ugurcum_similar_dailymotion_sql($target_url);
  } else if (strstr($target_url, 'vimeo.com/')) {
    $where_clause = ugurcum_similar_vimeo_sql($target_url);
  } else {
    $where_clause = "medialink LIKE '%" . $target_url . "%'";
  }

  $similar_sql = "SELECT * from $ugurcum_db_main WHERE $where_clause LIMIT 1";
  $ret = $wpdb->get_row($similar_sql);

  return $ret;
}


?>
