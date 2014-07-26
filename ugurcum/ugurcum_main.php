<?php
global $wp_query, $current_user;

$output = '';
$user_logged_in = $current_user->ID != '';

get_header();
get_sidebar();

$error_msg = '';
$submit_series = '';
$submit_description = '';
$submit_media_link = '';

// $hede = $_POST["add_media"];
// print_r($_POST);
if (isset($_POST['ugurcum_submit'])) {
  $submit_series = $_POST['series'];
  $submit_description = $_POST['description'];
  $submit_media_link = $_POST['media_link'];

  $submit_series = trim($submit_series);
  $submit_description = trim($submit_description);
  $submit_media_link = trim($submit_media_link);

  if ($submit_series == '') {
    $error_msg = __('Please enter valid series', 'ugurcum');
  } else if ($submit_media_link == '') {
    $error_msg = __('Please enter valid video link', 'ugurcum');
  } else if ($submit_description == '') {
    $error_msg = __('Please enter valid description', 'ugurcum');
  } else {
    $similar_record = ugurcum_find_similar_links($submit_media_link);

    if ($similar_record) {
      $error_msg = __("This link was already added", 'ugurcum');
    }
  }

  if ($error_msg == '') {
    ugurcum_insert_media_link($submit_series, $submit_description, $submit_media_link);
  }
}


$output .='
  <div class="leftpane article-page content">
    <article class="post-page cl">
      <div class="article-body">
        <hgroup>
          <div class="ucm_title">
            <div id="ugurcum_logo">
              <img src="' . get_option('siteurl') . '/wp-content/plugins/ugurcum/images/ugurcum.png"
                width="120" height="120" title="' . __('Play it Sam', 'ugurcum') . '">
            </div>
            <div id="ugurcum_page_title_wrapper">
              <h3 class="ugurcum_page_title">' . __('Videos', 'ugurcum') . '</h3>
              <h3 class="ugurcum_page_title_secondary">' . __('Play it Sam', 'ugurcum') . '</h3>
            </div>
          </div>
        </hgroup>';

if ($user_logged_in) {
  $output .= '
    <div>
      <a href="#" id="ugurcum_toggle_medialink_form">' . __('Add New Video', 'ugurcum') . '</a>
    </div>
    <div>
      <form id="ugurcum_add_media_link" name="ugurcum_add_media_link" method="post">
        <table id="ugurcum_submit">
          <tr>
            <td>&nbsp;</td>
            <td>
              <input type="hidden" name="add_media" value="Y" />
            </td>
          </tr>
  ';

  if ($error_msg != '') {
    $output .= '
      <tr>
        <td>&nbsp;</td>
        <td id="ugurcum_medialink_form_error">'
          . $error_msg .
        '</td>
      </tr>';
  }

  $output .= '
          <tr>
            <td>
              <label for="series">' .  __('Series', 'ugurcum') . '</label>
            </td>
            <td>
              <input type="text" name="series" size="50" required="true" value="' . ($error_msg == '' ? '' : $submit_series) . '" />
            </td>
          </tr>
          <tr>
            <td>
              <label for="description">' .  __('About Video', 'ugurcum') . '</label>
            </td>
            <td>
              <input type="text" name="description" size="50" required="true" value="' . ($error_msg == '' ? '' : $submit_description) . '"/>
            </td>
          </tr>
          <tr>
            <td>
              <label for="media_link">' .  __('Link', 'ugurcum') . '</label>
            </td>
            <td>
              <input type="text" name="media_link" size="50" required="true" value="' . ($error_msg == '' ? '' : $submit_media_link) . '" />
            </td>
          </tr>
          <tr>
            <td>
            </td>
            <td>
              <input type="submit" name="ugurcum_submit" value="' . __('Add', 'ugurcum') . '"/>
            </td>
          </tr>
        </table>
      </form>
    </div>';
}

$output .='
        <table id="ugurcum_media_link_list">
          <thead>
            <tr>
              <th>' . __('Series', 'ugurcum') . '</th>
              <th>' . __('About Video', 'ugurcum') . '</th>
              <th>' . __('Author', 'ugurcum') . '</th>
              <th>' . __('Date', 'ugurcum') . '</th>
            </tr>
          </thead>
          <tbody>
  ';

$output .= ugurcum_display_media_links();
$output .= '
          </tbody>
        </table>
      </div>
    </article>
  </div>';

echo $output;

ugurcum_set_time();

get_footer();
?>


<script language="javascript" type="text/javascript">
//<![CDATA[

jQuery(document).ready(function() {
<?php
  echo 'var dt_str = {
          empty_table: "' . __('No results found', 'ugurcum') . '",
          search: "' . __('Search', 'ugurcum') . '",
          first: "' . __('First', 'ugurcum') . '",
          last: "' . __('Last', 'ugurcum') . '",
          next: "' . __('Next', 'ugurcum') . '",
          previous: "' . __('Previous', 'ugurcum') . '",
          info: "' . __('_TOTAL_ total', 'ugurcum') . '"
        };'
?>

  jQuery('table#ugurcum_media_link_list').dataTable({
    "iDisplayLength": 25,
    "bPaginate": true,
    "bSearchable": true,
    "aaSorting": [],
    "language": {
      "search": '',
      "lengthMenu": "_MENU_",
      "emptyTable": dt_str['empty_table'],
      "zeroRecords": dt_str['empty_table'],
      "info": dt_str['info'],
      "infoEmpty": '',
      "infoFiltered": '',
      "paginate": {
        "first": dt_str['first'],
        "last": dt_str['last'],
        "next": dt_str['next'],
        "previous": dt_str['previous']
      },
    }
  });

  jQuery('.dataTables_filter input').attr("placeholder", dt_str.search);

  jQuery('a#ugurcum_toggle_medialink_form').on('click', function() {
    var media_link_form = jQuery('form#ugurcum_add_media_link');
    var toggle_button = jQuery('a#ugurcum_toggle_medialink_form');

    if (toggle_button.hasClass('active')) {
      media_link_form.fadeOut();
      toggle_button.removeClass('active');

      setTimeout(function() {
        toggle_button.css('margin-bottom', '30px');
      }, 450);
    } else {
      media_link_form.fadeIn();
      toggle_button.addClass('active');
      toggle_button.css('margin-bottom', '0');
    }

    return false;
  });

  jQuery('table#ugurcum_media_link_list tbody').on('click', 'a', function () {
    jQuery(this).closest('tr').removeClass('unread');
  });

<?php if ($error_msg != '') { ?>
  jQuery('a#ugurcum_toggle_medialink_form').click();
<?php } ?>

});

//]]>
</script>
