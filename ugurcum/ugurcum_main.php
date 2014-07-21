<?php
global $wp_query, $current_user;

$output = '';
$user_logged_in = $current_user->ID != '';

get_header();
get_sidebar();

// $hede = $_POST["add_video"];
// print_r($_POST);

$output .='
  <div class="leftpane article-page content">
    <article class="post-page cl">
      <div class="article-body">
        <hgroup>
          <div class="ucm_title">
            <h3 class="ugurcum_page_title">' . __('Videos', 'ugurcum') . '</h3>
          </div>
        </hgroup>';

if (false && $user_logged_in) {
  $output .= '
    <form name="ugurcum_add_video">
      <input type="hidden" name="add_video" value="Y" />
      <input type="text" name="series" />
      <input type="text" name="description" />
      <input type="submit" name="Submit" />
    </form>';
};

$output .='
        <table id="ugurcum_media_link_list">
          <thead>
            <tr>
              <th>' . __('Series', 'ugurcum') . '</th>
              <th>' . __('Video', 'ugurcum') . '</th>
              <th>' . __('Author', 'ugurcum') . '</th>
              <th>' . __('Addition time', 'ugurcum') . '</th>
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
        };'
?>

  jQuery('table#ugurcum_media_link_list').dataTable({
    "iDisplayLength": 25,
    "bPaginate": true,
    "bSearchable": true,
    "language": {
      "search": '',
      "lengthMenu": "_MENU_",
      "emptyTable": dt_str['empty_table'],
      "zeroRecords": dt_str['empty_table'],
      "paginate": {
        "first": dt_str['first'],
        "last": dt_str['last'],
        "next": dt_str['next'],
        "previous": dt_str['previous']
      },
    }
  });

  jQuery('.dataTables_filter input').attr("placeholder", dt_str.search);

});

//]]>
</script>
