
var mantar_latest_clicked;

function mantar_format_mantar_table_form(row) {
  var d = row.data();
  var row_index = row.index();

  var ret = '' +
    '<div class="mantar_inner_update">' +
      '<form id="mantar_inner_update_form" name="mantar_inner_update" method="post">' +
        '<input type="hidden" name="id" value="' + d.id + '" />' +
        '<table id="mantar_inner_update_table">' +
          '<tr>' +
            '<td><label for="title">' + dt_str['form_title'] + '</label></td>' +
            '<td>' +
              '<select name="peyton_list_id" class="mantar_select2">';

  jQuery.each(mantar_peyton_list, function(value, name) {
    var selected = '';

    if (d.title == name) {
      selected = 'selected="selected"';
    }

    ret += '<option value="' + value + '" ' + selected + '>' + name + '</option>';
  });

  ret +=      '</select>' +
            '</td>' +
          '</tr>' +

          '<tr>' +
            '<td><label for="category">' + dt_str['form_category_title'] + '</label></td>' +
            '<td>' +
              '<select name="mantar_category_id" class="mantar_select2">';

  jQuery.each(mantar_categories, function(value, name) {
    var selected = '';

    if (d.category_title == name) {
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
            '<td><label for="date">' + dt_str['form_date'] + '</label></td>' +
            '<td><input type="text" name="date" class="mantar_datepicker" size="50" value="' + d.date + '" /></td>' +
          '</tr>'+
          '<tr>' +
            '<td><label for="without_day">' + dt_str['form_without_day'] + '</label></td>' +
            '<td><input type="checkbox" name="without_day" size="50" value="1" ' + (d.without_day == '1' ? 'checked="checked" ' : '') + ' /></td>' +
          '</tr>' +
          '<tr>' +
            '<td><label for="season">' + dt_str['form_season'] + '</label></td>' +
            '<td><input type="text" name="season" size="50" value="' + d.season + '" /></td>' +
          '</tr>';

  jQuery.each(['created_by_humanized', 'created_at', 'updated_by_humanized', 'updated_at'], function(ix, k) {
    ret += '<tr>' +
             '<td><label for="' + k + '">' + dt_str['form_' + k] + '</label></td>' +
             '<td>' + d[k] + '</td>' +
           '</tr>';
  });

  ret +=  '<tr>' +
            '<td><input type="submit" name="mantar_inner_update_delete" data-row_index="' + row_index + '" value="' + dt_str['form_delete'] + '"/></td>' +
            '<td><input type="submit" name="mantar_inner_update_update" data-row_index="' + row_index + '" value="' + dt_str['form_update'] + '"/></td>' +
          '</tr>' +
        '</table>' +
      '</form>' +
    '</div>';

  return ret;
}

function mantar_bind_datepicker() {
  jQuery('.mantar_datepicker').datepicker({
    numberOfMonths: 1,
    dateFormat : 'yy-mm-dd',
  });
}

function mantar_bind_select2() {
  jQuery('.mantar_select2').select2();
}

function mantar_bind_after_show() {
  mantar_bind_datepicker();
  mantar_bind_select2();
}

function initialize_mantar() {
  if (jQuery('table#mantar_main_list').length === 0) {
    return;
  }

  // add external filter
  jQuery.fn.dataTable.ext.search.push(
    function(settings, data, dataIndex) {
      var category_matching = true;
      var category_val = jQuery('select[name=mantar_main_list_selector_category]').val();

      if (category_val !== '-1') {
        category_matching = category_val === data[3];
      }

      return category_matching;
    }
  );

  mantar_table = jQuery('table#mantar_main_list').DataTable({
    iDisplayLength: 100,
    lengthMenu: [[20, 50, 100, 250, -1], [20, 50, 100, 250, dt_str.all]],
    bPaginate: true,
    bSearchable: true,
    order: [[2, 'asc']],
    aaSorting: [],
    columns: [
      {
        data: null,
        defaultContent: mantar_default_edit_str,
        // width: "5%",
        orderable: false,
        class: "mantar_table_edit",
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
        data: "category_id",
        visible: false
      },
      {
        data: "category_title",
        sType: "turkish"
      },
      {
        data: "date_humanized",
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
        jQuery(nRow).children('td.mantar_table_edit').addClass('mantar_can_edit');
      }
    }
  });

  jQuery('.dataTables_filter input').attr("placeholder", dt_str.search);
  mantar_table.rows.add(dt_data).columns.adjust().draw();

  if (mantar_user_has_permission) {
    jQuery('a#mantar_toggle_link_form').on('click', function() {
      var add_entry_form_wrapper = jQuery('#mantar_add_entry_wrapper');
      var toggle_button = jQuery('a#mantar_toggle_link_form');

      if (toggle_button.hasClass('active')) {
        // add_entry_form_wrapper.fadeOut();
        add_entry_form_wrapper.slideUp();
        toggle_button.removeClass('active');
      } else {
        // add_entry_form_wrapper.fadeIn();
        add_entry_form_wrapper.slideDown();
        toggle_button.addClass('active');
        mantar_bind_after_show();
      }

      return false;
    });
  }

  jQuery('a#mantar_category_toggle_link_form').on('click', function() {
    var add_entry_form_wrapper = jQuery('#mantar_category_entry_wrapper');
    var toggle_button = jQuery('a#mantar_category_toggle_link_form');

    if (toggle_button.hasClass('active')) {
      // add_entry_form_wrapper.fadeOut();
      add_entry_form_wrapper.slideUp();
      toggle_button.removeClass('active');
    } else {
      // add_entry_form_wrapper.fadeIn();
      add_entry_form_wrapper.slideDown();
      toggle_button.addClass('active');
      mantar_bind_after_show();
    }

    return false;
  });


  mantar_table.on('click', 'td.mantar_table_edit.mantar_can_edit', function() {
    var tr = jQuery(this).closest('tr');
    var td = jQuery(this);
    var row = mantar_table.row(tr);

    if (row.child.isShown()) {
      jQuery('div.mantar_inner_update', row.child()).slideUp(function () {
        row.child.hide();
        // tr.removeClass('shown');
        td.removeClass('shown');
        td.html(mantar_default_edit_str);
      });
    } else {
      row.child(mantar_format_mantar_table_form(row), 'no_padding').show();
      // tr.addClass('shown');
      td.addClass('shown');
      td.html("-");

      jQuery('div.mantar_inner_update', row.child()).slideDown();

      mantar_bind_after_show();
    }
  });

  mantar_table.on('click', 'input[name=mantar_inner_update_update], input[name=mantar_inner_update_delete]', function() {
    var current = jQuery(this);
    mantar_latest_clicked = current;
    var row_index = current.data('row_index');
    var row = mantar_table.row(row_index);
    var tr = jQuery(row.node());
    var td = jQuery(tr.find('td.shown'));
    var form = current.closest('form');
    var form_data = form.serializeArray();
    var ajax_data = {
      action: 'mantar',
      entry: {}
    };
    var failed_str;
    var is_update = current.attr('name') === 'mantar_inner_update_update';

    jQuery.each(form_data, function(ix, k) {
      ajax_data.entry[k.name] = k.value;
    });

    if (is_update) {
      ajax_data.mantar_action = 'update';
      failed_str = dt_str['update_failed'];
    } else {
      ajax_data.mantar_action = 'delete';
      failed_str = dt_str['delete_failed'];
    }

    jQuery.ajax({
      url: mantar_ajax_url,
      type: 'post',
      dataType: 'json',
      data: ajax_data,
      success: function(data) {
        if (typeof data !== 'undefined') {
          if (data.success) {
            if (row.child.isShown()) {
              jQuery('div.mantar_inner_update', row.child()).slideUp(function () {
                row.child.hide();
                td.removeClass('shown');
                td.html(mantar_default_edit_str);
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

  jQuery('select[name=mantar_main_list_selector_category]').on('change', function(k) {
    mantar_table.columns.adjust().draw();
  });

  jQuery.datepicker.regional['tr'] = {
    closeText: 'kapat',
    prevText: '&#x3C;geri',
    nextText: 'ileri&#x3e',
    currentText: 'bugün',
    monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
    monthNamesShort: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
    dayNames: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
    dayNamesShort: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
    dayNamesMin: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
    weekHeader: 'Hf',
    dateFormat: 'dd.mm.yy',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''
  };

  jQuery.datepicker.setDefaults(jQuery.datepicker.regional['tr']);


  if (mantar_open_insert_form) {
    jQuery('a#mantar_toggle_link_form').click();
  }

  mantar_bind_after_show();
}

jQuery(document).ready(function() {
  initialize_mantar();
});

