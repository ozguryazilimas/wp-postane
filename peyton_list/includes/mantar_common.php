<?php

function mantar_get_time() {
  global $wp_query, $wpdb, $user_ID;

  // date_default_timezone_set('Europe/Istanbul');
  // $now = current_time('mysql', 1);

  $date = new DateTime("now", new DateTimeZone('Europe/Istanbul'));
  $now = $date->format('Y-m-d H:i:s');
  return $now;
}

function mantar_user_has_permission() {
  global $wp_query, $wpdb, $user_ID;

  $has_perm = $user_ID && $user_ID != '' && (current_user_can('edit_others_posts') || current_user_can('edit_published_pages'));

  return $has_perm;
}

function mantar_user_is_admin() {
  global $wp_query, $wpdb, $user_ID;

  $has_perm = $user_ID && $user_ID != '' && current_user_can('administrator');

  return $has_perm;
}

function mantar_similar_record($peyton_list_id, $mantar_category_id) {
  global $wpdb, $mantar_db_main, $mantar_db_categories;

  $ret = $wpdb->get_row("SELECT * FROM $mantar_db_main WHERE peyton_list_id = $peyton_list_id AND mantar_category_id = $mantar_category_id");

  return $ret;
}

function mantar_initial_data() {
  return array(
    'title' => '',
    'link' => '',
    'season' => ''
  );
}

function mantar_process_post() {
  $error_msg = '';
  $error_category_msg = '';
  $data = mantar_initial_data();

  if (isset($_POST['mantar_submit']) && isset($_POST['mantar_add_entry'])) {
    $data = $_POST['mantar_add_entry'];
    $error_msg = mantar_insert_entry($data);
  } else if (isset($_POST['mantar_category_submit']) && isset($_POST['mantar_category_update_entry'])) {
    $data = $_POST['mantar_category_update_entry'];
    $error_category_msg = mantar_category_update_entry($data);
  } else if (isset($_POST['mantar_category_submit_delete']) && isset($_POST['mantar_category_update_entry'])) {
    $data = $_POST['mantar_category_update_entry'];
    $error_category_msg = mantar_category_delete_entry($data['id']);
  } else if (isset($_POST['mantar_category_submit_add']) && isset($_POST['mantar_category_add_entry'])) {
    $data = $_POST['mantar_category_add_entry'];
    $error_category_msg = mantar_category_insert_entry($data);
  }


  $ret = array(
    'error_msg' => $error_msg,
    'error_category_msg' => $error_category_msg,
    'data' => $data
  );

  return $ret;
}

function mantar_insert_entry($data, $admin_override = false) {
  global $wp_query, $wpdb, $user_ID, $mantar_db_main;

  $error_msg = '';
  $mantar_category_id = stripslashes_deep($data['mantar_category_id']);
  $peyton_list_id = stripslashes_deep($data['peyton_list_id']);
  $link = stripslashes_deep($data['link']);
  $date = stripslashes_deep($data['date']);
  $without_day = $data['without_day'] ? 1 : 0;
  $season = stripslashes_deep($data['season']);
  $current_time = mantar_get_time();
  $insert_user_id = $admin_override ? 1 : $user_ID;

  $similar_record = mantar_similar_record($peyton_list_id, $mantar_category_id);

  if ($similar_record) {
    $error_msg = __('This record was already added', 'peyton_list');
  }

  if ($error_msg == '') {
    $success = $wpdb->insert(
      $mantar_db_main,

      array(
        'mantar_category_id' => $mantar_category_id,
        'peyton_list_id' => $peyton_list_id,
        'link' => $link,
        'date' => $date,
        'without_day' => $without_day,
        'season' => $season,
        'created_by' => $insert_user_id,
        'created_at' => $current_time,
        'updated_by' => $insert_user_id,
        'updated_at' => $current_time
      )
    );
  }

  return $error_msg;
}

function mantar_update_entry($data) {
  global $wp_query, $wpdb, $user_ID, $mantar_db_main;

  $entry_id = stripslashes_deep($data['id']);
  $mantar_category_id = stripslashes_deep($data['mantar_category_id']);
  $peyton_list_id = stripslashes_deep($data['peyton_list_id']);
  $link = stripslashes_deep($data['link']);
  $date = stripslashes_deep($data['date']);
  $without_day = stripslashes_deep($data['without_day']);
  $season = stripslashes_deep($data['season']);
  $current_time = mantar_get_time();

  $success = $wpdb->update(
    $mantar_db_main,
    array(
      'mantar_category_id' => $mantar_category_id,
      'peyton_list_id' => $peyton_list_id,
      'link' => $link,
      'date' => $date,
      'without_day' => $without_day,
      'season' => $season,
      'updated_by' => $user_ID,
      'updated_at' => $current_time
    ),
    array(
      'id' => $entry_id
    )
  );

  return $success;
}

function mantar_delete_entry($link_id) {
  global $wp_query, $wpdb, $user_ID, $mantar_db_main;

  $success = $wpdb->delete($mantar_db_main, array('id' => $link_id), array('%d'));

  return $success;
}

function mantar_category_insert_entry($data) {
  global $wp_query, $wpdb, $user_ID, $mantar_db_categories;

  $title = stripslashes_deep($data['title']);
  $background_color_1 = stripslashes_deep($data['background_color_1']);
  $background_color_2 = stripslashes_deep($data['background_color_2']);
  $current_time = mantar_get_time();

  $success = $wpdb->insert(
    $mantar_db_categories,
    array(
      'title' => $title,
      'background_color_1' => $background_color_1,
      'background_color_2' => $background_color_2,
      'created_by' => $user_ID,
      'created_at' => $current_time,
      'updated_by' => $user_ID,
      'updated_at' => $current_time
    )
  );

  return $success;
}

function mantar_category_update_entry($data) {
  global $wp_query, $wpdb, $user_ID, $mantar_db_categories;

  $entry_id = stripslashes_deep($data['id']);
  $title = stripslashes_deep($data['title']);
  $background_color_1 = stripslashes_deep($data['background_color_1']);
  $background_color_2 = stripslashes_deep($data['background_color_2']);
  $current_time = mantar_get_time();

  $success = $wpdb->update(
    $mantar_db_categories,
    array(
      'title' => $title,
      'background_color_1' => $background_color_1,
      'background_color_2' => $background_color_2,
      'updated_by' => $user_ID,
      'updated_at' => $current_time
    ),
    array(
      'id' => $entry_id
    )
  );

  return $success;
}

function mantar_category_delete_entry($category_id) {
  global $wp_query, $wpdb, $user_ID, $mantar_db_categories;

  $success = $wpdb->delete($mantar_db_categories, array('id' => $category_id), array('%d'));

  return $success;
}

function mantar_get_mantar_categories() {
  global $wpdb, $mantar_db_categories;
  $sql_str = "SELECT id, title FROM $mantar_db_categories ORDER BY title";

  return $wpdb->get_results($sql_str);
}

function mantar_get_peyton_list() {
  global $wpdb, $peyton_list_db_main;
  $sql_str = "SELECT id, title FROM $peyton_list_db_main ORDER BY title";

  return $wpdb->get_results($sql_str);
}

function mantar_get_entries_raw($mantar_category_id = null, $mantar_id = null) {
  global $wp_query, $wpdb, $user_ID, $mantar_db_main, $mantar_db_categories, $peyton_list_db_main;

  if (mantar_user_has_permission()) {
    $can_edit_sql = "SELECT 1";
  } else {
    $can_edit_sql = 'SELECT 0';
  }

  if ($mantar_category_id) {
    $where_statement = 'WHERE M.mantar_category_id = ' . $mantar_category_id;
  } else {
    $where_statement = '';
  }

  if ($mantar_id) {
    $where_statement .= ' WHERE M.id = ' . $mantar_id;
  }

  $sql_str = "SELECT
              M.id AS id,
              PL.title AS title,
              PL.link AS peyton_link,
              MC.title AS category_title,
              M.mantar_category_id AS category_id,
              MC.background_color_1 AS background_color_1,
              MC.background_color_2 AS background_color_2,
              M.link AS link,
              M.date AS date,
              M.without_day AS without_day,
              M.season AS season,
              M.created_by AS created_by,
              M.created_at AS created_at,
              M.updated_by AS updated_by,
              M.updated_at AS updated_at,
              (YEAR(M.date) * 10000 + MONTH(M.date) * 100 + DAY(M.date) + M.without_day * 50) AS real_order,
              WPU1.user_nicename AS created_by_humanized,
              WPU2.user_nicename AS updated_by_humanized,
              ($can_edit_sql) AS can_edit
              FROM $mantar_db_main M
              JOIN $wpdb->users WPU1
                ON WPU1.ID = M.created_by
              JOIN $wpdb->users WPU2
                ON WPU2.ID = M.updated_by
              JOIN $mantar_db_categories MC
                ON MC.id = M.mantar_category_id
              JOIN $peyton_list_db_main PL
                ON PL.id = M.peyton_list_id
              $where_statement
              ORDER BY real_order, PL.title";

  return $wpdb->get_results($sql_str);
}

function mantar_prepare_for_dt($data) {
  global $mantar_month_names;

  # $date_formatter = $data->without_day ? 'Y-m' : 'Y-m-d';
  # $formatted_date = date($date_formatter, strtotime($data->date));

  $entry_date = explode('-', $data->date, 3);
  $year = intval($entry_date[0]);
  $month = intval($entry_date[1]);
  $day = intval($entry_date[2]);

  if ($data->without_day) {
    $formatted_date = '';
  } else {
    $formatted_date = $day . ' ';
  }

  $formatted_date .= $mantar_month_names[$month] . ' ' . $year;


  if (empty($data->link)) {
    $date_humanized = $formatted_date;
  } else {
    $date_humanized = '<a href="' . $data->link . '">' . $formatted_date . '</a>';
  }

  $ret = array(
    'id' => $data->id,
    'can_edit' => $data->can_edit == '1',
    'title' => $data->title,
    'category_id' => $data->category_id,
    'category_title' => $data->category_title,
    'title_humanized' => '<a href="' . $data->peyton_link . '">' . $data->title . '</a>',
    'link' => $data->link,
    'date' => $data->date,
    'date_humanized' => $date_humanized,
    'without_day' => $data->without_day,
    'season' => $data->season,
    'created_by' => $data->created_by,
    'created_by_humanized' => $data->created_by_humanized,
    'created_at' => date('H:i Y-m-d', strtotime($data->created_at)),
    'updated_by' => $data->updated_by,
    'updated_by_humanized' => $data->updated_by_humanized,
    'updated_at' => date('H:i Y-m-d', strtotime($data->updated_at))
  );

  return $ret;
}

function mantar_get_entries($mantar_category_id = null, $mantar_id = null) {
  $raw_data = mantar_get_entries_raw($mantar_category_id, $mantar_id);
  return array_map('mantar_prepare_for_dt', $raw_data);
}

function mantar_get_single_entry($mantar_id) {
  $results = mantar_get_entries(null, $mantar_id);
  return $results[0];
}

function mantar_insert_form($formdata) {
  $error_msg = $formdata['error_msg'];
  $data = ($error_msg == '' ? mantar_initial_data() : $formdata['data']);
  $mantar_categories = mantar_get_mantar_categories();
  $mantar_peyton_list = mantar_get_peyton_list();

  $output = '
    <div id="mantar_toggle_link_form_wrapper">
      <a href="#" id="mantar_toggle_link_form">' . __('Add New Entry', 'peyton_list') . '</a>
    </div>
    <div id="mantar_add_entry_wrapper">
      <form id="mantar_add_entry" name="mantar_add_entry" method="post">
        <input type="hidden" name="mantar_add_entry[add_entry]" value="Y" />
  ';

  if ($error_msg != '') {
    $output .= '
        <span id="mantar_link_form_error">'
          . $error_msg .
        '</span>';
  }

  $output .= '
        <table id="mantar_submit">
          <tr>
            <td>
              <label for="mantar_add_entry[mantar_category_id]">' .  __('Category', 'peyton_list') . '</label>
            </td>
            <td>
              <select name="mantar_add_entry[mantar_category_id]" class="mantar_select2">
  ';

  foreach($mantar_categories as $mc_data) {
    $selected = '';
    $mantar_category_id = $mc_data->id;
    $mantar_category_title = $mc_data->title;

    if ($error_msg != '' && $mantar_category_id == $data['mantar_category_id']) {
      $selected = 'selected="selected"';
    }

    $output .= '<option value="' . $mantar_category_id . '" ' . $selected . '>' . $mantar_category_title . '</option>';
  }

  $output .= '
              </select>
            </td>
          </tr>

         <tr>
            <td>
              <label for="mantar_add_entry[peyton_list_id]">' .  __('Title', 'peyton_list') . '</label>
            </td>
            <td>
              <select name="mantar_add_entry[peyton_list_id]" class="mantar_select2">
  ';

  foreach($mantar_peyton_list as $pl_data) {
    $selected = '';
    $mantar_peyton_list_id = $pl_data->id;
    $mantar_peyton_list_title = $pl_data->title;

    if ($error_msg != '' && $mantar_peyton_list_id == $data['peyton_list_id']) {
      $selected = 'selected="selected"';
    }

    $output .= '<option value="' . $mantar_peyton_list_id . '" ' . $selected . '>' . $mantar_peyton_list_title . '</option>';
  }

  if ($data['without_day']) {
    $without_day_checked = ' checked="checked"';
  } else {
    $without_day_checked = '';
  }

  $output .= '
              </select>
            </td>
          </tr>

          <tr>
            <td>
              <label for="mantar_add_entry[link]">' .  __('Link', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="mantar_add_entry[link]" size="50" value="' . $data['link'] . '" required="required" />
            </td>
          </tr>

          <tr>
            <td>
              <label for="mantar_add_entry[date]">' .  __('Date', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="mantar_add_entry[date]" class="mantar_datepicker" size="50" value="' . $data['date'] . '" placeholder="' . date('Y-m-d') . '"
                     pattern="20[0-9]{2}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" required="required" />
            </td>
          </tr>

          <tr>
            <td>
              <label for="mantar_add_entry[without_day]">' .  __('Without Day', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="checkbox" name="mantar_add_entry[without_day]" value="1"' . $without_day_checked . ' />
            </td>
          </tr>

          <tr>
            <td>
              <label for="mantar_add_entry[season]">' .  __('Season', 'peyton_list') . '</label>
            </td>
            <td>
              <input type="text" name="mantar_add_entry[season]" size="50" value="' . $data['season'] . '" required="required" />
            </td>
          </tr>

          <tr>
            <td>
            </td>
            <td>
              <input type="submit" name="mantar_submit" value="' . __('Add', 'peyton_list') . '"/>
            </td>
          </tr>
        </table>
      </form>
    </div>';

  return array(
    $output,
    ($error_msg != '')
  );
}

function mantar_datatable($has_perm, $open_form) {
  $mantar_categories_raw = mantar_get_mantar_categories();
  $mantar_categories = array();
  $mantar_peyton_list_raw = mantar_get_peyton_list();
  $mantar_peyton_list = array();

  foreach($mantar_peyton_list_raw as $pl_data) {
    $mantar_peyton_list[$pl_data->id] = $pl_data->title;
  }

  $output = '
    <div id="mantar_main_list_selector">
      <label for="mantar_main_list_selector_category">' . __('Category', 'peyton_list') . '</label>
      <select name="mantar_main_list_selector_category" class="mantar_select2">
        <option value="-1" selected="selected">' . __('All', 'peyton_list') . '</option>';

        foreach($mantar_categories_raw as $mc_data) {
          $mantar_category_id = $mc_data->id;
          $mantar_category_title = $mc_data->title;
          $mantar_categories[$mantar_category_id] = $mantar_category_title;

          $output .= '<option value="' . $mantar_category_id . '">' . $mantar_category_title . '</option>';
        }

        $output .= '
      </select>
    </div>

    <table id="mantar_main_list">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th>&nbsp;</th>
          <th>' . __('Title', 'peyton_list') . '</th>
          <th>&nbsp;</th>
          <th>' . __('Category', 'peyton_list') . '</th>
          <th>' . __('Date', 'peyton_list') . '</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>

    <script language="javascript" type="text/javascript">
    //<![CDATA[

      var dt_data;
      var mantar_ajax_url = "' . admin_url('admin-ajax.php') . '";
      var mantar_table;
      var mantar_open_insert_form = ' . ($open_form ? 1 : 0) . ';
      var mantar_user_has_permission = ' . ($has_perm ? 1 : 0) . ';
      var mantar_categories = ' . json_encode($mantar_categories) . ';
      var mantar_peyton_list = ' . json_encode($mantar_peyton_list) . ';
      var mantar_default_edit_str = mantar_user_has_permission ? "+" : "&nbsp;";

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
        form_category_title: "' . __('Category', 'peyton_list') . '",
        form_link: "' . __('Link', 'peyton_list') . '",
        form_date: "' . __('Date', 'peyton_list') . '",
        form_without_day: "' . __('Without Day', 'peyton_list') . '",
        form_season: "' . __('Season', 'peyton_list') . '",
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

      dt_data = ' . json_encode(mantar_get_entries()) . ';

    //]]>
    </script>
  ';

  return $output;
}

function mantar_entries_toggle_button($year, $month) {
  global $mantar_month_names;

  $target_id = 'mantar_display_container_' . $year . '_' . $month;
  $monthname = $mantar_month_names[$month];

  return '<a class="kabartmatozu kabartmatozu_color_dark_blue mantar_display_toggle" ' .
            "onclick=\"jQuery('#" . $target_id . "').slideToggle()\">" .
            '<strong>' . sprintf(__('Click for %s', 'peyton_list'), $monthname . ' - ' . $year) . '</strong>' .
            '</a><br><br>';
}

function mantar_display_container($color, $year, $month, $now_year, $now_month) {
  $output = '';
  $hiding_style = '';
  $show_button = ($year * 12 + $month + 1) < ($now_year * 12 + $now_month);

  if ($show_button) {
    $output .= mantar_entries_toggle_button($year, $month);
    $hiding_style = 'display: none;';
  }

  $output .= '<div id="mantar_display_container_' . $year . '_' . $month .
    '" class="mantar_display_container" style="padding: 4px; background-color: ' . $color . '; ' . $hiding_style . '" >';

  return $output;
}

function mantar_format_entries_for_display($entries, $color_1, $color_2) {
  $output = '';
  $color_toggle = 0;
  $current_month = -1;
  $current_year = -1;
  $now_is = explode('-', mantar_get_time(), 3);
  $now_year = intval($now_is[0]);
  $now_month = intval($now_is[1]);

  foreach($entries as $ix => $entry) {
    $entry_date = explode('-', $entry['date'], 3);
    $year = intval($entry_date[0]);
    $month = intval($entry_date[1]);
    $day = intval($entry_date[2]);

    if ($current_month === -1) {
      $output .= mantar_display_container($color_1, $year, $month, $now_year, $now_month);
    }

    if ($current_year !== $year || $current_month !== $month) {
      if ($color_toggle === 0) {
        $color_toggle = 1;
        $color_to_use = $color_1;
      } else {
        $color_toggle = 0;
        $color_to_use = $color_2;
      }

      if ($current_month !== -1) {
        $output .= '</div>' . "\n" . mantar_display_container($color_to_use, $year, $month, $now_year, $now_month);
      }

      $current_month = $month;
      $current_year = $year;
    }

    $output .= "\n". $entry['date_humanized'] . ' - ' . $entry['title_humanized'];

    if (!empty($entry['season'])) {
      $output .= " (" . $entry['season'] . ")\n";
    }
    $output .= "\n<br>";
  }

  $output .= '</div>';

  return $output;
}

function mantar_category_form() {
  global $wpdb, $mantar_db_categories;

  $sql_str = "SELECT * FROM $mantar_db_categories ORDER BY title";
  $categories = $wpdb->get_results($sql_str);

  $output = '
    <div id="mantar_category_toggle_link_form_wrapper">
      <a href="#" id="mantar_category_toggle_link_form">' . __('Category', 'peyton_list') . '</a>
    </div>
    <div id="mantar_category_entry_wrapper">
    <table id="mantar_category_entry_table">';

  foreach($categories as $category) {
    $id = $category->id;
    $title = $category->title;
    $color_1 = $category->background_color_1;
    $color_2 = $category->background_color_2;

    $output .= '
      <form id="mantar_category_update_entry_' . $id . '" name="mantar_category_update_entry" method="post">
        <tr>
          <input type="hidden" name="mantar_category_update_entry[update_entry]" value="Y" />
          <input type="hidden" name="mantar_category_update_entry[id]" value="' . $id . '" />
          <td>
            <input type="text" size="40" name="mantar_category_update_entry[title]" value="' . $title . '" required="required" />
          </td>
          <td>
            <input type="text" size="5" name="mantar_category_update_entry[background_color_1]" value="' . $color_1 . '" required="required" />
          </td>
          <td>
            <input type="text" size="5" name="mantar_category_update_entry[background_color_2]" value="' . $color_2 . '" required="required" />
          </td>
        </tr>

        <tr>
          <td></td>
          <td colspan="2">
            <input type="submit" name="mantar_category_submit" value="' . __('Update', 'peyton_list') . '" style="float: left;" />
            <input type="submit" name="mantar_category_submit_delete" value="' . __('Delete', 'peyton_list') . '"/>
          </td>
        </tr>
      </form>
      ';
  }

  $output .= '
        <form id="mantar_category_add_entry" name="mantar_category_add_entry" method="post">
          <tr>
            <input type="hidden" name="mantar_category_add_entry[add_entry]" value="Y" />
            <td>
              <input type="text" size="40" name="mantar_category_add_entry[title]" value="" required="required" />
            </td>
            <td>
              <input type="text" size="5" name="mantar_category_add_entry[background_color_1]" value="#F9FFEE" required="required" />
            </td>
            <td>
              <input type="text" size="5" name="mantar_category_add_entry[background_color_2]" value="#EEFFF9" required="required" />
            </td>
          </tr>

          <tr>
            <td></td>
            <td colspan="2">
              <input type="submit" name="mantar_category_submit_add" value="' . __('Add', 'peyton_list') . '"/>
            </td>
          </tr>

        </form>
      </table>
    </div>
    <br>';

  return $output;
}

function mantar_main() {
  // var_dump($_POST);
  $has_perm = mantar_user_has_permission();
  $open_form = false;
  $category_output = '';
  $form_output = '';
  $table_output = '';

  if ($has_perm) {
    if (mantar_user_is_admin()) {
      $category_output = mantar_category_form();
    }

    $formdata = mantar_process_post();
    $ret = mantar_insert_form($formdata);
    $form_output = $ret[0];
    $open_form = $ret[1];
    $table_output = mantar_datatable($has_perm, $open_form);
  } else {
    $table_output = '<div style="font-weight: bold;color: gray;text-align: center;">No soup for you</div>';
  }

  return "\n" . $category_output . "\n" . $form_output . "\n" . $table_output . "\n";
}

function mantar_page_display($atts = [], $content = null, $tag = '') {
  global $wpdb, $mantar_db_categories;

  $output = '';
  $mantar_category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $mantar_db_categories WHERE title = %s", $content));
  $mantar_category_id = $mantar_category->id;
  $color_1 = $mantar_category->background_color_1;
  $color_2 = $mantar_category->background_color_2;

  if ($mantar_category_id) {
    $entries = mantar_get_entries($mantar_category_id);
    $output = mantar_format_entries_for_display($entries, $color_1, $color_2);
  }

  return $output;
}


?>
