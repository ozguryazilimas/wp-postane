<?php

function peyton_list_get_time() {
  global $wp_query, $wpdb, $user_ID;

  // date_default_timezone_set('Europe/Istanbul');
  // $now = current_time('mysql', 1);

  $date = new DateTime("now", new DateTimeZone('Europe/Istanbul'));
  $now = $date->format('Y-m-d H:i:s');
  return $now;
}

function peyton_list_user_has_permission() {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $has_perm = $user_ID && $user_ID != '' && (current_user_can('edit_others_posts') || current_user_can('edit_published_pages'));

  return $has_perm;
}

function peyton_list_similar_record($title) {
  global $wpdb, $peyton_list_db_main;

  $similar_sql = $wpdb->prepare("SELECT * FROM $peyton_list_db_main WHERE title = %s", $title);
  $ret = $wpdb->get_row($similar_sql);

  return $ret;
}

function peyton_list_initial_data() {
  return array(
      'title' => '',
      'category' => 1,
      'status' => 1,
      'link' => ''
  );
}

function peyton_list_process_post() {
  $error_msg = '';
  $data = peyton_list_initial_data();

  if (isset($_POST['peyton_list_submit']) && isset($_POST['peyton_list_add_entry'])) {
    $data = $_POST['peyton_list_add_entry'];
    $error_msg = peyton_list_insert_entry($data);
  }

  $ret = array(
    'error_msg' => $error_msg,
    'data' => $data
  );

  return $ret;
}

function peyton_list_insert_entry($data, $admin_override = false) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $error_msg = '';
  $title = stripslashes_deep($data['title']);
  $category = stripslashes_deep($data['category']);
  $status = stripslashes_deep($data['status']);
  $link = stripslashes_deep($data['link']);
  $current_time = peyton_list_get_time();
  $insert_user_id = $admin_override ? 1 : $user_ID;

  if ($title == '') {
    $error_msg = __('Title can not be blank', 'peyton_list');
  } else {
    $similar_record = peyton_list_similar_record($title);

    if ($similar_record) {
      $error_msg = __('This record was already added', 'peyton_list');
    }
  }

  if ($link == '') {
    $link = '/dizi-listesi/';
  }

  if ($error_msg == '') {
    $success = $wpdb->insert(
      $peyton_list_db_main,
      array(
        'title' => $title,
        'category' => $category,
        'status' => $status,
        'link' => $link,
        'created_by' => $insert_user_id,
        'created_at' => $current_time,
        'updated_by' => $insert_user_id,
        'updated_at' => $current_time
      )
    );
  }

  return $error_msg;
}

function peyton_list_update_entry($data) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $entry_id = stripslashes_deep($data['id']);
  $title = stripslashes_deep($data['title']);
  $category = stripslashes_deep($data['category']);
  $status = stripslashes_deep($data['status']);
  $link = stripslashes_deep($data['link']);
  $current_time = peyton_list_get_time();

  $success = $wpdb->update(
    $peyton_list_db_main,
    array(
      'title' => $title,
      'category' => $category,
      'status' => $status,
      'link' => $link,
      'updated_by' => $user_ID,
      'updated_at' => $current_time
    ),
    array(
      'id' => $entry_id
    )
  );

  return $success;
}

function peyton_list_delete_entry($link_id) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  $success = $wpdb->delete($peyton_list_db_main, array('id' => $link_id), array('%d'));

  return $success;
}

function peyton_list_get_entries_raw($entry_id = false) {
  global $wp_query, $wpdb, $user_ID, $peyton_list_db_main;

  if (peyton_list_user_has_permission()) {
    $can_edit_sql = "SELECT 1";
  } else {
    $can_edit_sql = 'SELECT 0';
  }

  if ($entry_id) {
    $where_statement = 'WHERE PL.id = ' . $entry_id;
  } else {
    $where_statement = '';
  }

  $sql_str = "SELECT
              PL.id AS id,
              PL.title AS title,
              PL.category AS category,
              PL.status AS status,
              PL.link AS link,
              PL.created_by AS created_by,
              PL.created_at AS created_at,
              PL.updated_by AS updated_by,
              PL.updated_at AS updated_at,
              WPU1.user_nicename AS created_by_humanized,
              WPU2.user_nicename AS updated_by_humanized,
              ($can_edit_sql) AS can_edit
              FROM $peyton_list_db_main PL
              JOIN $wpdb->users WPU1
                ON WPU1.ID = PL.created_by
              JOIN $wpdb->users WPU2
                ON WPU2.ID = PL.updated_by
              $where_statement
              ORDER BY PL.title";

  $sql_statement = $wpdb->prepare($sql_str);
  return $wpdb->get_results($sql_statement);
}

function peyton_list_prepare_for_dt($data) {
  global $peyton_list_category, $peyton_list_category_color, $peyton_list_status, $peyton_list_status_image;

  $ret = array(
    'id' => $data->id,
    'can_edit' => $data->can_edit == '1',
    'title' => $data->title,
    'title_humanized' => '<a href="' . $data->link . '">' . $data->title . '</a>',
    'category' => $data->category,
    'category_humanized' => $peyton_list_category[$data->category],
    'status' => $data->status,
    'status_humanized' => $peyton_list_status[$data->status],
    'status_image' => $peyton_list_status_image[$data->status],
    'link' => $data->link,
    'created_by' => $data->created_by,
    'created_by_humanized' => $data->created_by_humanized,
    'created_at' => date('H:i Y/m/d', strtotime($data->created_at)),
    'updated_by' => $data->updated_by,
    'updated_by_humanized' => $data->updated_by_humanized,
    'updated_at' => date('H:i Y/m/d', strtotime($data->updated_at))
  );

  return $ret;
}

function peyton_list_get_entries($entry_id = false) {
  $raw_data = peyton_list_get_entries_raw($entry_id);
  return array_map('peyton_list_prepare_for_dt', $raw_data);
}

function peyton_list_get_single_entry($entry_id) {
  $results = peyton_list_get_entries($entry_id);
  return $results[0];
}

function peyton_list_insert_form($formdata) {
  global $peyton_list_category, $peyton_list_category_color, $peyton_list_status, $peyton_list_status_image, $peyton_list_status_editor;

  $error_msg = $formdata['error_msg'];
  $data = ($error_msg == '' ? peyton_list_initial_data() : $formdata['data']);

  $output = '
    <div id="peyton_list_toggle_link_form_wrapper">
      <a href="#" id="peyton_list_toggle_link_form">' . __('Add New Entry', 'peyton_list') . '</a>
    </div>
    <div id="peyton_list_add_entry_wrapper">
      <form id="peyton_list_add_entry" name="peyton_list_add_entry" method="post">
        <input type="hidden" name="peyton_list_add_entry[add_entry]" value="Y" />
  ';

  if ($error_msg != '') {
    $output .= '
        <span id="peyton_list_link_form_error">'
          . $error_msg .
        '</span>';
  }

  $output .= '
        <table id="peyton_list_submit">
          <tr>
            <td>
              <label for="peyton_list_add_entry[title]">' .  __('Title', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="peyton_list_add_entry[title]" size="50" required="true" value="' . $data['title'] . '" />
            </td>
          </tr>

          <tr>
            <td>
              <label for="peyton_list_add_entry[category]">' .  __('Category', 'peyton_list') . '</label>
            </td>
            <td>
              <select name="peyton_list_add_entry[category]">
  ';

  foreach($peyton_list_category as $value => $name) {
    $selected = '';

    if ($error_msg != '' && $value == $data['category']) {
      $selected = 'selected="selected"';
    }

    $output .= '<option value="' . $value . '" ' . $selected . '>' . $name . '</option>';
  }

  $output .= '
              </select>
            </td>
          </tr>

          <tr>
            <td>
              <label for="peyton_list_add_entry[status]">' .  __('Status', 'peyton_list') . '</label>
            </td>
            <td>
              <select name="peyton_list_add_entry[status]">
  ';

  foreach($peyton_list_status_editor as $value => $name) {
    $selected = '';

    if ($error_msg != '' && $value == $data['status']) {
      $selected = 'selected="selected"';
    }

    $output .= '<option value="' . $value . '" ' . $selected . '>' . $name . '</option>';
  }

  $output .= '
              </select>
            </td>
          </tr>

          <tr>
            <td>
              <label for="peyton_list_add_entry[link]">' .  __('Link', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="peyton_list_add_entry[link]" size="50" value="' . $data['link'] . '" />
            </td>
          </tr>

          <tr>
            <td>
            </td>
            <td>
              <input type="submit" name="peyton_list_submit" value="' . __('Add', 'peyton_list') . '"/>
            </td>
          </tr>
        </table>
      </form>
    </div>';

  echo $output;

  return ($error_msg != '');
}

function peyton_list_datatable($has_perm, $open_form) {
  global $peyton_list_status, $peyton_list_status_image, $peyton_list_category, $peyton_list_category_color, $peyton_list_status_editor;

  $output = '
    <table id="peyton_list_main_list_selector">
      <tr>
        <td>
          <label for="peyton_list_main_list_selector_category">' . __('Category', 'peyton_list') . '</label>
          <select name="peyton_list_main_list_selector_category">
            <option value="0" selected="selected">' . __('All', 'peyton_list') . '</option>';

      foreach($peyton_list_category as $value => $name) {
        $output .= '<option value="' . $value . '">' . $name . '</option>';
      }

      $output .= '
          </select>
        </td>
        <td>
          <label for="peyton_list_main_list_selector_status">' . __('Status', 'peyton_list') . '</label>
          <select name="peyton_list_main_list_selector_status">
            <option value="0" selected="selected">' . __('All', 'peyton_list') . '</option>';

      foreach($peyton_list_status as $value => $name) {
        $output .= '<option value="' . $value . '">' . $name . '</option>';
      }

      $output .= '
          </select>
        </td>
      </tr>
    </table>

    <table id="peyton_list_main_list">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
          <th>' . __('Title', 'peyton_list') . '</th>
          <th>&nbsp;</th>
          <th>' . __('Category', 'peyton_list') . '</th>
          <th>&nbsp;</th>
          <th>' . __('Status', 'peyton_list') . '</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>

    <script language="javascript" type="text/javascript">
    //<![CDATA[

      var dt_data;
      var peyton_list_ajax_url = "' . admin_url('admin-ajax.php') . '";
      var peyton_list_table;
      var peyton_list_open_insert_form = ' . ($open_form ? 1 : 0) . ';
      var peyton_list_user_has_permission = ' . ($has_perm ? 1 : 0) . ';
      var peyton_list_status = ' . json_encode($peyton_list_status) . ';
      var peyton_list_status_editor = ' . json_encode($peyton_list_status_editor) . ';
      var peyton_list_status_image = ' . json_encode($peyton_list_status_image) . ';
      var peyton_list_category = ' . json_encode($peyton_list_category) . ';
      var peyton_list_category_color = ' . json_encode($peyton_list_category_color) . ';
      var peyton_list_default_edit_str = peyton_list_user_has_permission ? "+" : "&nbsp;";

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
        form_category: "' . __('Category', 'peyton_list') . '",
        form_status: "' . __('Status', 'peyton_list') . '",
        form_link: "' . __('Link', 'peyton_list') . '",
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

      dt_data = ' . json_encode(peyton_list_get_entries()) . ';

    //]]>
    </script>
  ';

  echo $output;
}

function peyton_list_intro() {
  $output = '
    <div id="peyton_list_intro">
      <p>
        Bu bölümde, <strong>22dakika</strong> \'da şimdiye kadar bahsi geçmiş dizileri ve tanıtım, mini tanıtım vb. bağlantılarını bulacaksınız. Yeni diziler zaman içerisinde eklenmektedir. Keyfini çıkarın.
      </p>

      <p>
        Diziler hakkında bilgi almak için isimlerine tıklayınız. Eksik ya da hata gördüğünüzde <a href="/bunlari-biliyor-muydunuz-dizi-listesi-yeni-yuzuyle-karsinizda/">şu başlığın</a> altına yorum bırakabilirsiniz.
      </p>

      <table>
        <tr>
          <td>
            <img src="/wp-content/plugins/peyton_list/images/thumbs_up.svg" class="peyton_list_status_image_intro" />
          </td>
          <td>
            22dakika\'da tanıtımı olanlar
          </td>
          <td>
            <img src="/wp-content/plugins/peyton_list/images/thumbs_side.svg" class="peyton_list_status_image_intro" />
          </td>
          <td>
            22dakika\'daki tanıtımı mini olanlar
          </td>
          <td>
            <img src="/wp-content/plugins/peyton_list/images/thumbs_down.svg" class="peyton_list_status_image_intro" />
          </td>
          <td>
            22dakika\'da tanıtımı olmayanlar
          </td>
        </tr>
      </table>
    </div>
  ';

  echo $output;
};

function peyton_list_main() {
  // var_dump($_POST);
  $has_perm = peyton_list_user_has_permission();
  $open_form = false;

  peyton_list_intro();

  if ($has_perm) {
    $formdata = peyton_list_process_post();
    $open_form = peyton_list_insert_form($formdata);
  }

  peyton_list_datatable($has_perm, $open_form);
}


?>
