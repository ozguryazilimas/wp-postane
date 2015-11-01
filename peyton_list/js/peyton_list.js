
jQuery.fn.dataTable.ext.search.push(
  function(settings, data, dataIndex) {
    var category_matching =  true;
    var status_matching = true;
    var category_val = jQuery('select[name=peyton_list_main_list_selector_category]').val();
    var status_val = jQuery('select[name=peyton_list_main_list_selector_status]').val();

    if (category_val !== '0') {
      category_matching = peyton_list_category[category_val] === data[2];
    }

    if (status_val !== '0') {
      status_matching = peyton_list_status[status_val] === data[3];
    }

    return (category_matching && status_matching);
  }
);


function peyton_list_format_peyton_list_table_form(d) {
  var ret = '' +
    '<div class="peyton_list_inner_update">' +
      '<form id="peyton_list_inner_update_form" name="peyton_list_inner_update" method="post">' +
        '<input type="hidden" name="peyton_list_inner_update[id]" value="' + d.id + '" />' +
        '<table id="peyton_list_inner_update_table">' +
          '<tr>' +
            '<td><label for="peyton_list_inner_update[title]">' + dt_str['form_title'] + '</label></td>' +
            '<td><input type="text" name="peyton_list_inner_update[title]" size="50" required="true" value="' + d.title + '" /></td>' +
          '</tr>' +
          '<tr>' +
            '<td><label for="peyton_list_inner_update[category]">' + dt_str['form_category'] + '</label></td>' +
            '<td>' +
              '<select name="peyton_list_inner_update[category]">';

  jQuery.each(peyton_list_category, function(value, name) {
    var selected = '';

    if (d.category == value) {
      selected = 'selected="selected"';
    }

    ret += '<option value="' + value + '" ' + selected + '>' + name + '</option>';
  });

  ret +=      '</select>' +
            '</td>' +
          '</tr>' +
          '<tr>' +
            '<td><label for="peyton_list_inner_update[status]">' + dt_str['form_status'] + '</label></td>' +
            '<td>' +
              '<select name="peyton_list_inner_update[status]">';

  jQuery.each(peyton_list_status, function(value, name) {
    var selected = '';

    if (d.status == value) {
      selected = 'selected="selected"';
    }

    ret += '<option value="' + value + '" ' + selected + '>' + name + '</option>';
  });

  ret +=      '</select>' +
            '</td>' +
          '</tr>' +
          '<tr>' +
            '<td><label for="peyton_list_inner_update[link]">' + dt_str['form_link'] + '</label></td>' +
            '<td><input type="text" name="peyton_list_inner_update[link]" size="50" value="' + d.link + '" /></td>' +
          '</tr>';

  jQuery.each(['created_by_humanized', 'created_at', 'updated_by_humanized', 'updated_at'], function(ix, k) {
    ret += '<tr>' +
             '<td><label for="' + k + '">' + dt_str['form_' + k] + '</label></td>' +
             '<td>' + d[k] + '</td>' +
           '</tr>';
  });

  ret +=  '<tr>' +
            '<td><input type="submit" name="peyton_list_inner_update_delete" value="' + dt_str['form_delete'] + '"/></td>' +
            '<td><input type="submit" name="peyton_list_inner_update_update" value="' + dt_str['form_update'] + '"/></td>' +
          '</tr>' +
        '</table>' +
      '</form>' +
    '</div>';

  return ret;
}

jQuery(document).ready(function() {

  peyton_list_table = jQuery('table#peyton_list_main_list').DataTable({
    iDisplayLength: 50,
    lengthMenu: [[20, 50, 100, 250, -1], [20, 50, 100, 250, dt_str.all]],
    bPaginate: true,
    bSearchable: true,
    order: [[1, 'asc']],
    aaSorting: [],
    columns: [
      {
        data: null,
        defaultContent: peyton_list_default_edit_str,
        width: "5%",
        orderable: false,
        class: "peyton_list_table_edit",
      },
      {
        data: "title",
        width: "30%",
        render: function(data, type, row, meta) {
          return '<a href="' + row.link + '">' + data + '</a>';
        }
      },
      {
        data: "category_humanized",
        width: "33%"
      },
      {
        data: "status_humanized",
        width: "33%"
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
        jQuery(nRow).children('td.peyton_list_table_edit').addClass('peyton_list_can_edit');
      }
    }
  });

  jQuery('.dataTables_filter input').attr("placeholder", dt_str.search);
  peyton_list_table.rows.add(dt_data).draw();

  if (peyton_list_user_has_permission) {
    jQuery('a#peyton_list_toggle_link_form').on('click', function() {
      var add_entry_form_wrapper = jQuery('#peyton_list_add_entry_wrapper');
      var toggle_button = jQuery('a#peyton_list_toggle_link_form');

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

  peyton_list_table.on('click', 'td.peyton_list_table_edit.peyton_list_can_edit', function() {
    var tr = jQuery(this).closest('tr');
    var td = jQuery(this);
    var row = peyton_list_table.row(tr);

    if (row.child.isShown()) {
      jQuery('div.peyton_list_inner_update', row.child()).slideUp(function () {
        row.child.hide();
        // tr.removeClass('shown');
        td.removeClass('shown');
      });
    } else {
      row.child(peyton_list_format_peyton_list_table_form(row.data()), 'no_padding').show();
      // tr.addClass('shown');
      td.addClass('shown');

      jQuery('div.peyton_list_inner_update', row.child()).slideDown();
    }
  });

  jQuery('select[name=peyton_list_main_list_selector_category], ' +
         'select[name=peyton_list_main_list_selector_status]').on('change', function(k) {

    peyton_list_table.draw();
  });

});

