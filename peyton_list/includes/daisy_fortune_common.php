<?php

function daisy_fortune_get_time() {
  global $wp_query, $wpdb, $user_ID;

  // date_default_timezone_set('Europe/Istanbul');
  // $now = current_time('mysql', 1);

  $date = new DateTime("now", new DateTimeZone('Europe/Istanbul'));
  $now = $date->format('Y-m-d H:i:s');
  return $now;
}

function daisy_fortune_user_has_permission() {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $has_perm = $user_ID && $user_ID != '' && (current_user_can('edit_others_posts') || current_user_can('edit_published_pages'));

  return $has_perm;
}

function daisy_fortune_similar_record($title) {
  global $wpdb, $peyton_list_db_main;

  $ret = $wpdb->get_row("SELECT * FROM $peyton_list_db_main WHERE title = '$title'");

  return $ret;
}

function daisy_fortune_initial_data() {
  return array(
    'title' => '',
    'onair' => 1,
    'link' => '',
    'comment' => ''
  );
}

function daisy_fortune_process_post() {
  $error_msg = '';
  $data = daisy_fortune_initial_data();

  if (isset($_POST['daisy_fortune_submit']) && isset($_POST['daisy_fortune_add_entry'])) {
    $data = $_POST['daisy_fortune_add_entry'];
    $error_msg = daisy_fortune_insert_entry($data);
  }

  $ret = array(
    'error_msg' => $error_msg,
    'data' => $data
  );

  return $ret;
}

function daisy_fortune_insert_entry($data, $admin_override = false) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $error_msg = '';
  $title = stripslashes_deep($data['title']);
  $onair = stripslashes_deep($data['onair']);
  $link = stripslashes_deep($data['link']);
  $comment = stripslashes_deep($data['comment']);
  $current_time = daisy_fortune_get_time();
  $insert_user_id = $admin_override ? 1 : $user_ID;

  if ($title == '') {
    $error_msg = __('Title can not be blank', 'peyton_list');
  } else {
    $similar_record = daisy_fortune_similar_record($title);

    if ($similar_record) {
      $error_msg = __('This record was already added', 'peyton_list');
    }
  }

  if ($link == '') {
    $link = '/iptaldevam/';
  }

  if ($error_msg == '') {
    $success = $wpdb->insert(
      $peyton_list_db_main,
      array(
        'title' => $title,
        'onair' => $onair,
        'category' => 1,
        'status' => 3,
        'link' => $link,
        'comment' => $comment,
        'created_by' => $insert_user_id,
        'created_at' => $current_time,
        'updated_by' => $insert_user_id,
        'updated_at' => $current_time
      )
    );
  }

  return $error_msg;
}

function daisy_fortune_update_entry($data) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $entry_id = stripslashes_deep($data['id']);
  $title = stripslashes_deep($data['title']);
  $onair = stripslashes_deep($data['onair']);
  $link = stripslashes_deep($data['link']);
  $comment = stripslashes_deep($data['comment']);
  $current_time = daisy_fortune_get_time();

  $success = $wpdb->update(
    $peyton_list_db_main,
    array(
      'title' => $title,
      'onair' => $onair,
      'link' => $link,
      'comment' => $comment,
      'updated_by' => $user_ID,
      'updated_at' => $current_time
    ),
    array(
      'id' => $entry_id
    )
  );

  return $success;
}

function daisy_fortune_delete_entry($link_id) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $success = $wpdb->delete($peyton_list_db_main, array('id' => $link_id), array('%d'));

  return $success;
}

function daisy_fortune_get_entries_raw($entry_id = false) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  if (daisy_fortune_user_has_permission()) {
    $can_edit_sql = "SELECT 1";
  } else {
    $can_edit_sql = 'SELECT 0';
  }

  if ($entry_id) {
    $where_statement = 'WHERE DF.id = ' . $entry_id;
  } else {
    $where_statement = '';
  }

  $sql_str = "SELECT
              DF.id AS id,
              DF.title AS title,
              DF.onair AS onair,
              DF.link AS link,
              DF.comment AS comment,
              DF.created_by AS created_by,
              DF.created_at AS created_at,
              DF.updated_by AS updated_by,
              DF.updated_at AS updated_at,
              WPU1.user_nicename AS created_by_humanized,
              WPU2.user_nicename AS updated_by_humanized,
              ($can_edit_sql) AS can_edit
              FROM $peyton_list_db_main DF
              JOIN $wpdb->users WPU1
                ON WPU1.ID = DF.created_by
              JOIN $wpdb->users WPU2
                ON WPU2.ID = DF.updated_by
              $where_statement
              ORDER BY DF.title";

  return $wpdb->get_results($sql_str);
}

function daisy_fortune_prepare_for_dt($data) {
  global $daisy_fortune_onair, $daisy_fortune_onair_image;

  $ret = array(
    'id' => $data->id,
    'can_edit' => $data->can_edit == '1',
    'title' => $data->title,
    'title_humanized' => '<a href="' . $data->link . '">' . $data->title . '</a>',
    'onair' => $data->onair,
    'onair_humanized' => $daisy_fortune_onair[$data->onair],
    'onair_image' => $daisy_fortune_onair_image[$data->onair],
    'link' => $data->link,
    'comment' => $data->comment,
    'created_by' => $data->created_by,
    'created_by_humanized' => $data->created_by_humanized,
    'created_at' => date('H:i Y/m/d', strtotime($data->created_at)),
    'updated_by' => $data->updated_by,
    'updated_by_humanized' => $data->updated_by_humanized,
    'updated_at' => date('H:i Y/m/d', strtotime($data->updated_at))
  );

  return $ret;
}

function daisy_fortune_get_entries($entry_id = false) {
  $raw_data = daisy_fortune_get_entries_raw($entry_id);
  return array_map('daisy_fortune_prepare_for_dt', $raw_data);
}

function daisy_fortune_get_single_entry($entry_id) {
  $results = daisy_fortune_get_entries($entry_id);
  return $results[0];
}

function daisy_fortune_insert_form($formdata) {
  global $daisy_fortune_onair, $daisy_fortune_onair_image;

  $error_msg = $formdata['error_msg'];
  $data = ($error_msg == '' ? daisy_fortune_initial_data() : $formdata['data']);

  $output = '
    <div id="daisy_fortune_toggle_link_form_wrapper">
      <a href="#" id="daisy_fortune_toggle_link_form">' . __('Add New Entry', 'peyton_list') . '</a>
    </div>
    <div id="daisy_fortune_add_entry_wrapper">
      <form id="daisy_fortune_add_entry" name="daisy_fortune_add_entry" method="post">
        <input type="hidden" name="daisy_fortune_add_entry[add_entry]" value="Y" />
  ';

  if ($error_msg != '') {
    $output .= '
        <span id="daisy_fortune_link_form_error">'
          . $error_msg .
        '</span>';
  }

  $output .= '
        <table id="daisy_fortune_submit">
          <tr>
            <td>
              <label for="daisy_fortune_add_entry[title]">' .  __('Title', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="daisy_fortune_add_entry[title]" size="50" required="true" value="' . $data['title'] . '" />
            </td>
          </tr>

          <tr>
            <td>
              <label for="daisy_fortune_add_entry[onair]">' .  __('Onair', 'peyton_list') . '</label>
            </td>
            <td>
              <select name="daisy_fortune_add_entry[onair]">
  ';

  foreach($daisy_fortune_onair as $value => $name) {
    $selected = '';

    if ($error_msg != '' && $value == $data['onair']) {
      $selected = 'selected="selected"';
    }

    $output .= '<option value="' . $value . '" ' . $selected . '>' . __($name, 'peyton_list') . '</option>';
  }

  $output .= '
              </select>
            </td>
          </tr>

          <tr>
            <td>
              <label for="daisy_fortune_add_entry[link]">' .  __('Link', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="daisy_fortune_add_entry[link]" size="50" value="' . $data['link'] . '" />
            </td>
          </tr>

          <tr>
            <td>
              <label for="daisy_fortune_add_entry[comment]">' .  __('Comment', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="daisy_fortune_add_entry[comment]" size="50" value="' . $data['comment'] . '" />
            </td>
          </tr>

          <tr>
            <td>
            </td>
            <td>
              <input type="submit" name="daisy_fortune_submit" value="' . __('Add', 'peyton_list') . '"/>
            </td>
          </tr>
        </table>
      </form>
    </div>';

  echo $output;

  return ($error_msg != '');
}

function daisy_fortune_datatable($has_perm, $open_form) {
  global $daisy_fortune_onair, $daisy_fortune_onair_image;

  $translated = array();

  foreach($daisy_fortune_onair as $value => $name) {
    $translated[$value] =  __($name, 'peyton_list');
  }

  $output = '
    <div id="daisy_fortune_main_list_selector">
      <label for="daisy_fortune_main_list_selector_onair">' . __('Onair', 'peyton_list') . '</label>
      <select name="daisy_fortune_main_list_selector_onair">
        <option value="-1" selected="selected">' . __('All', 'peyton_list') . '</option>';

        foreach($daisy_fortune_onair as $value => $name) {
          $output .= '<option value="' . $value . '">' . __($name, 'peyton_list') . '</option>';
        }

        $output .= '
      </select>
    </div>

    <table id="daisy_fortune_main_list">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
          <th>' . __('Title', 'peyton_list') . '</th>
          <th>&nbsp;</th>
          <th>' . __('Onair', 'peyton_list') . '</th>
          <th>' . __('Comment', 'peyton_list') . '</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>

    <script language="javascript" type="text/javascript">
    //<![CDATA[

      var dt_data;
      var daisy_fortune_ajax_url = "' . admin_url('admin-ajax.php') . '";
      var daisy_fortune_table;
      var daisy_fortune_open_insert_form = ' . ($open_form ? 1 : 0) . ';
      var daisy_fortune_user_has_permission = ' . ($has_perm ? 1 : 0) . ';
      var daisy_fortune_onair = ' . json_encode($daisy_fortune_onair) . ';
      var daisy_fortune_onair_translated = ' . json_encode($translated) . ';
      var daisy_fortune_onair_image = ' . json_encode($daisy_fortune_onair_image) . ';
      var daisy_fortune_default_edit_str = daisy_fortune_user_has_permission ? "+" : "&nbsp;";

      var dt_str = {
        all: "' . __('All', 'peyton_list') . '",
        empty_table: "' . __('No results found', 'peyton_list') . '",
        search: "' . __('Search', 'peyton_list') . '",
        first: "' . __('First', 'peyton_list') . '",
        last: "' . __('Last', 'peyton_list') . '",
        next: "' . __('Next', 'peyton_list') . '",
        previous: "' . __('Previous', 'peyton_list') . '",
        info: "' . __('_TOTAL_ total', 'peyton_list') . '",
        form_title: "' . __('Title', 'peyton_list') . '",
        form_onair: "' . __('Onair', 'peyton_list') . '",
        form_link: "' . __('Link', 'peyton_list') . '",
        form_comment: "' . __('Comment', 'peyton_list') . '",
        form_add: "' .  __('Add', 'peyton_list') . '",
        form_update: "' .  __('Update', 'peyton_list') . '",
        form_delete: "' .  __('Delete', 'peyton_list') . '",
        form_created_by: "' .  __('Created by', 'peyton_list') . '",
        form_created_by_humanized: "' .  __('Created by', 'peyton_list') . '",
        form_created_at: "' .  __('Created at', 'peyton_list') . '",
        form_updated_by: "' .  __('Updated by', 'peyton_list') . '",
        form_updated_by_humanized: "' .  __('Updated by', 'peyton_list') . '",
        form_updated_at: "' .  __('Updated at', 'peyton_list') . '",
        update_failed: "' .  __('Update failed', 'peyton_list') . '",
        delete_failed: "' .  __('Delete failed', 'peyton_list') . '",
        connection_problem: "' .  __('Connection problem', 'peyton_list') . '"
      };

      dt_data = ' . json_encode(daisy_fortune_get_entries()) . ';

    //]]>
    </script>
  ';

  echo $output;
}

function daisy_fortune_main() {
  // var_dump($_POST);
  $has_perm = daisy_fortune_user_has_permission();
  $open_form = false;

  if ($has_perm) {
    $formdata = daisy_fortune_process_post();
    $open_form = daisy_fortune_insert_form($formdata);
  }

  daisy_fortune_datatable($has_perm, $open_form);
}


?>
