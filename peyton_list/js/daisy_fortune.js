
var daisy_fortune_latest_clicked;

function daisy_fortune_format_daisy_fortune_table_form(row) {
  var d = row.data();
  var row_index = row.index();

  var ret = '' +
    '<div class="daisy_fortune_inner_update">' +
      '<form id="daisy_fortune_inner_update_form" name="daisy_fortune_inner_update" method="post">' +
        '<input type="hidden" name="id" value="' + d.id + '" />' +
        '<table id="daisy_fortune_inner_update_table">' +
          '<tr>' +
            '<td><label for="title">' + dt_str['form_title'] + '</label></td>' +
            '<td><input type="text" name="title" size="50" required="true" value="' + d.title + '" /></td>' +
          '</tr>' +

          '<tr>' +
            '<td><label for="onair">' + dt_str['form_onair'] + '</label></td>' +
            '<td>' +
              '<select name="onair">';

  jQuery.each(daisy_fortune_onair_translated, function(value, name) {
    var selected = '';

    if (d.onair == value) {
      selected = 'selected="selected"';
    }

    ret += '<option value="' + value + '" ' + selected + '>' + name + '</option>';
  });

  ret +=      '</select>' +
            '</td>' +
          '</tr>' +
          '<tr>' +
            '<td><label for="link">' + dt_str['form_link'] + '</label></td>' +
            '<td><input type="text" name="link" size="50" value="' + d.link + '" /></td>' +
          '</tr>' +
          '<tr>' +
            '<td><label for="comment">' + dt_str['form_comment'] + '</label></td>' +
            '<td><input type="text" name="comment" size="50" value="' + d.comment + '" /></td>' +
          '</tr>';

  jQuery.each(['created_by_humanized', 'created_at', 'updated_by_humanized', 'updated_at'], function(ix, k) {
    ret += '<tr>' +
             '<td><label for="' + k + '">' + dt_str['form_' + k] + '</label></td>' +
             '<td>' + d[k] + '</td>' +
           '</tr>';
  });

  ret +=  '<tr>' +
            '<td><input type="submit" name="daisy_fortune_inner_update_delete" data-row_index="' + row_index + '" value="' + dt_str['form_delete'] + '"/></td>' +
            '<td><input type="submit" name="daisy_fortune_inner_update_update" data-row_index="' + row_index + '" value="' + dt_str['form_update'] + '"/></td>' +
          '</tr>' +
        '</table>' +
      '</form>' +
    '</div>';

  return ret;
}

function initialize_daisy_fortune() {
  if (jQuery('table#daisy_fortune_main_list').length === 0) {
    return;
  }

  // add external filter
  jQuery.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex) {
      var onair_matching = true;
      var onair_val = jQuery('select[name=daisy_fortune_main_list_selector_onair]').val();

      if (onair_val !== '-1') {
        onair_matching = onair_val === data[3];
      }

      return onair_matching;
    }
  );

  daisy_fortune_table = jQuery('table#daisy_fortune_main_list').DataTable({
    iDisplayLength: 100,
    lengthMenu: [[20, 50, 100, 250, -1], [20, 50, 100, 250, dt_str.all]],
    bPaginate: true,
    bSearchable: true,
    order: [[2, 'asc']],
    aaSorting: [],
    columns: [
      {
        data: null,
        defaultContent: daisy_fortune_default_edit_str,
        // width: "5%",
        orderable: false,
        class: "daisy_fortune_table_edit",
      },
      {
        data: "title",
        sType: "turkish",
        visible: false
      },
      {
        data: "title_humanized",
        // width: "55%",
        orderData: 1
      },
      {
        data: "onair",
        visible: false
      },
      {
        data: "onair_image",
        // width: "15%",
        orderData: 5,
        render: function(data, type, row, meta) {
          return '<img src="/wp-content/plugins/peyton_list/images/' + daisy_fortune_onair_image[row.onair] + '"' +
            'class="daisy_fortune_onair_image" title="' + daisy_fortune_onair_translated[row.onair] + '" />';
        }
      },
      {
        data: "comment",
        sType: "turkish"
        // visible: false
      }
    ],
    language: {
      search: '',
      lengthMenu: "_MENU_",
      emptyTable: dt_str['empty_table'],
      zeroRecords: dt_str['empty_table'],
      info: dt_str['info'],
      infoEmpty: '',
      infoFiltered: '',
      paginate: {
        first: dt_str['first'],
        last: dt_str['last'],
        next: dt_str['next'],
        previous: dt_str['previous']
      }
    },
    fnCreatedRow: function(nRow, aData, iDataIndex) {
      // console.log(aData);
      if (aData['can_edit']) {
        jQuery(nRow).children('td.daisy_fortune_table_edit').addClass('daisy_fortune_can_edit');
      }
    }
  });

  jQuery('.dataTables_filter input').attr("placeholder", dt_str.search);
  daisy_fortune_table.rows.add(dt_data).columns.adjust().draw();

  if (daisy_fortune_user_has_permission) {
    jQuery('a#daisy_fortune_toggle_link_form').on('click', function() {
      var add_entry_form_wrapper = jQuery('#daisy_fortune_add_entry_wrapper');
      var toggle_button = jQuery('a#daisy_fortune_toggle_link_form');

      if (toggle_button.hasClass('active')) {
        // add_entry_form_wrapper.fadeOut();
        add_entry_form_wrapper.slideUp();
        toggle_button.removeClass('active');

      } else {
        // add_entry_form_wrapper.fadeIn();
        add_entry_form_wrapper.slideDown();
        toggle_button.addClass('active');
      }

      return false;
    });
  }

  daisy_fortune_table.on('click', 'td.daisy_fortune_table_edit.daisy_fortune_can_edit', function() {
    var tr = jQuery(this).closest('tr');
    var td = jQuery(this);
    var row = daisy_fortune_table.row(tr);

    if (row.child.isShown()) {
      jQuery('div.daisy_fortune_inner_update', row.child()).slideUp(function () {
        row.child.hide();
        // tr.removeClass('shown');
        td.removeClass('shown');
        td.html(daisy_fortune_default_edit_str);
      });
    } else {
      row.child(daisy_fortune_format_daisy_fortune_table_form(row), 'no_padding').show();
      // tr.addClass('shown');
      td.addClass('shown');
      td.html("-");

      jQuery('div.daisy_fortune_inner_update', row.child()).slideDown();
    }
  });

  daisy_fortune_table.on('click', 'input[name=daisy_fortune_inner_update_update], input[name=daisy_fortune_inner_update_delete]', function() {
    var current = jQuery(this);
    daisy_fortune_latest_clicked = current;
    var row_index = current.data('row_index');
    var row = daisy_fortune_table.row(row_index);
    var tr = jQuery(row.node());
    var td = jQuery(tr.find('td.shown'));
    var form = current.closest('form');
    var form_data = form.serializeArray();
    var ajax_data = {
      action: 'daisy_fortune',
      entry: {}
    };
    var failed_str;
    var is_update = current.attr('name') === 'daisy_fortune_inner_update_update';

    jQuery.each(form_data, function(ix, k) {
      ajax_data.entry[k.name] = k.value;
    });

    if (is_update) {
      ajax_data.daisy_fortune_action = 'update';
      failed_str = dt_str['update_failed'];
    } else {
      ajax_data.daisy_fortune_action = 'delete';
      failed_str = dt_str['delete_failed'];
    }

    jQuery.ajax({
      url: daisy_fortune_ajax_url,
      type: 'post',
      dataType: 'json',
      data: ajax_data,
      success: function(data) {
        if (typeof data !== 'undefined') {
          if (data.success) {
            if (row.child.isShown()) {
              jQuery('div.daisy_fortune_inner_update', row.child()).slideUp(function () {
                row.child.hide();
                td.removeClass('shown');
                td.html(daisy_fortune_default_edit_str);
              });
            }

            if (is_update) {
              row.data(data.data).draw(false);
            } else {
              row.remove().draw(false);
            }
          } else {
            alert(failed_str);
          }
        }
      },
      error: function(message) {
        console.log(message);
        alert(dt_str['connection_problem']);
      }
    });

    return false;
  });

  jQuery('select[name=daisy_fortune_main_list_selector_onair]').on('change', function(k) {
    daisy_fortune_table.columns.adjust().draw();
  });

  if (daisy_fortune_open_insert_form) {
    jQuery('a#daisy_fortune_toggle_link_form').click();
  }
}

jQuery(document).ready(function() {
  initialize_daisy_fortune();
});

