<?php

function ugurcum_get_time() {
  global $wp_query, $wpdb, $user_ID;
  return current_time('mysql', 1);
}

function ugurcum_get_media_links() {
  global $wp_query, $wpdb, $user_ID, $ugurcum_db_main;

  $get_media_links_sql = $wpdb->prepare("SELECT
                                           um.title as title,
                                           um.description as description,
                                           um.medialink as medialink,
                                           um.visible as visible,
                                           um.updated_at as updated_at,
                                           wpu.user_login as user_login
                                         FROM $ugurcum_db_main as um
                                         JOIN
                                           $wpdb->users as wpu
                                         ON um.user_id = wpu.ID
                                         ORDER BY um.updated_at");

  return $wpdb->get_results($get_media_links_sql);
}

function ugurcum_display_media_links() {
  $ret = '';

  $medialinks = ugurcum_get_media_links();

  foreach($medialinks as $medialink) {
    $ret .= '
      <tr>
        <td>' . $medialink->title . '</td>
        <td>
          <a href="' . $medialink->medialink . '">'
            . $medialink->description .
          '</a>
        </td>
        <td>' . $medialink->user_login . '</td>
        <td>' . date('H:i Y-m-d', strtotime($medialink->updated_at)) . '</td>
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

// Record user read time
function ugurcum_set_time() {
  global $wpdb, $user_ID, $ugurcum_db_user_reads;

  if ($user_ID != '') {
    $read_time = current_time('mysql', 1);

    $update_read_time_sql = "INSERT INTO $ugurcum_db_user_reads
      (user_id, read_time)
      VALUES ($user_ID, '$read_time')
      ON DUPLICATE KEY UPDATE read_time='$read_time';";

    $success = $wpdb->query($update_read_time_sql);
  }
}

?>
