<?php

function ugurcum_display_media_links() {
  $ret = '';

  $ret .= '
    <tr>
      <td>' . 'diziler' . '</td>
      <td>
        <a href="' . 'http://www.youtube.com/watch?v=EAzYZ-E4l30' . '">'
          . 'diziye neler oldu neler' .
        '</a>
      </td>
      <td>' . 'yazar ne yazar' . '</td>
      <td>' . 'gece' . '</td>
    </tr>';


  return $ret;
}

function ugurcum_get_time() {
  global $wp_query, $wpdb,$user_ID;
  return current_time('mysql', 1);
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
