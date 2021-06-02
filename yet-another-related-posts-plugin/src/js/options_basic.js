jQuery(function ($) {
  // since 3.3: add screen option toggles
  postboxes.add_postbox_toggles(pagenow);

  function template() {
    var metabox = $(this).closest('#yarpp_display_web, #yarpp_display_rss');
    if (!metabox.length) return;

    value = metabox.find('.use_template').val();

    metabox.find('.yarpp_subbox').hide();
    metabox.find('.template_options_' + value).show();

    var no_results_area = metabox.find('.yarpp_no_results');
    // The "no_results" input is special. Its used by the non-custom templates.
    if (value === 'custom') {
      no_results_area.hide();
    } else {
      no_results_area.show();
    }
    excerpt.apply(metabox);
  }
  $('.use_template').each(template).change(template);

  function excerpt() {
    var metabox = $(this).closest('#yarpp_display_web, #yarpp_display_rss');
    metabox
      .find('.excerpted')
      .toggle(
        !!(
          metabox.find('.use_template').val() === 'builtin' &&
          metabox.find('.show_excerpt input').prop('checked')
        ),
      );
  }
  $('.show_excerpt, .use_template, #yarpp-rss_display').click(excerpt);

  var loaded_demo_web = false;
  function display() {
    if (!$('#yarpp_display_web .inside').is(':visible')) return;

    $('.yarpp_code_display').toggle($('#yarpp_display_code').is(':checked'));
    if ($('#yarpp_display_web .yarpp_code_display').is(':visible') && !loaded_demo_web) {
      loaded_demo_web = true;
      var demo_web = $('#display_demo_web');
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'yarpp_display_demo',
          domain: 'website',
          _ajax_nonce: $('#yarpp_display_demo-nonce').val(),
        },
        beforeSend: function () {
          demo_web.html(loading);
        },
        success: function (html) {
          demo_web.html('<pre>' + html + '</pre>');
        },
        dataType: 'html',
      });
    }
  }
  $('#yarpp_display_web .handlediv, #yarpp_display_web-hide').click(display);
  display();

  var loaded_demo_rss = false;
  function rss_display() {
    if (!$('#yarpp_display_rss .inside').is(':visible')) return;
    if ($('#yarpp-rss_display').is(':checked')) {
      $('.rss_displayed').show();
      $('.yarpp_code_display').toggle($('#yarpp_display_code').is(':checked'));
      if (
        $('#yarpp_display_rss .yarpp_code_display').is(':visible') &&
        !loaded_demo_rss
      ) {
        loaded_demo_rss = true;
        var demo_rss = $('#display_demo_rss');
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'yarpp_display_demo',
            domain: 'rss',
            _ajax_nonce: $('#yarpp_display_demo-nonce').val(),
          },
          beforeSend: function () {
            demo_rss.html(loading);
          },
          success: function (html) {
            demo_rss.html('<pre>' + html + '</pre>');
          },
          dataType: 'html',
        });
      }
      $('#yarpp_display_rss').each(template);
    } else {
      $('.rss_displayed').hide();
    }
  }
  $('#yarpp-rss_display, #yarpp_display_rss .handlediv, #yarpp_display_rss-hide').click(
    rss_display,
  );
  rss_display();

  function yarpp_rest_display() {
    if (!$('#yarpp_display_api .inside').is(':visible')) return;
    if ($('#yarpp-rest_api_display').is(':checked')) {
      $('.yarpp_rest_displayed').show();
    } else {
      $('.yarpp_rest_displayed').hide();
    }
  }
  $('#yarpp-rest_api_display').click(yarpp_rest_display);
  yarpp_rest_display();

  function yarpp_rest_cache_display() {
    if ($('#yarpp-rest_api_client_side_caching').is(':checked')) {
      $('.yarpp_rest_browser_cache_displayed').show();
    } else {
      $('.yarpp_rest_browser_cache_displayed').hide();
    }
  }
  $('#yarpp-rest_api_client_side_caching').click(yarpp_rest_cache_display);
  yarpp_rest_cache_display();

  var loaded_disallows = false;
  function load_disallows() {
    if (loaded_disallows || !$('#yarpp_pool .inside').is(':visible')) return;
    loaded_disallows = true;

    var finished_taxonomies = {},
      term_indices = {};
    function load_disallow(taxonomy) {
      if (taxonomy in finished_taxonomies) return;
      var display = $('#exclude_' + taxonomy);
      // only do one query at a time:
      if (display.find('.loading').length) return;

      if (taxonomy in term_indices) term_indices[taxonomy] = term_indices[taxonomy] + 100;
      else term_indices[taxonomy] = 0;
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'yarpp_display_exclude_terms',
          taxonomy: taxonomy,
          offset: term_indices[taxonomy],
          _ajax_nonce: $('#yarpp_display_exclude_terms-nonce').val(),
        },
        beforeSend: function () {
          display.append(loading);
        },
        success: function (html) {
          display.find('.loading').remove();
          if (':(' == html) {
            // no more :(
            finished_taxonomies[taxonomy] = true;
            display.append('-');
            return;
          }
          display.append(html);
        },
        dataType: 'html',
      });
    }

    $('.exclude_terms').each(function () {
      var id = jQuery(this).attr('id'),
        taxonomy;
      if (!id) return;

      taxonomy = id.replace('exclude_', '');

      load_disallow(taxonomy);
      $('#exclude_' + taxonomy)
        .parent('.yarpp_scroll_wrapper')
        .scroll(function () {
          var parent = $(this),
            content = parent.children('div');
          if (parent.scrollTop() + parent.height() > content.height() - 10)
            load_disallow(taxonomy);
        });
    });
  }
  $('#yarpp_pool .handlediv, #yarpp_pool-hide').click(load_disallows);
  load_disallows();

  function show_help(section) {
    $('#tab-link-' + section + ' a').click();
    $('#contextual-help-link').click();
  }
  $('#yarpp-optin-learnmore').click(function () {
    show_help('optin');
  });
  $('#yarpp-help-cpt').click(function () {
    show_help('dev');
  });
  if (location.hash == '#help-optin')
    setTimeout(function () {
      show_help('optin');
    });

  $('.yarpp_help[data-help]').hover(function () {
    var that = $(this),
      help = '<p>' + that.attr('data-help') + '</p>',
      options = {
        content: help,
        position: {
          edge: isRtl ? 'right' : 'left',
          align: 'center',
          of: that,
        },
        document: { body: that },
      };

    var pointer = that.pointer(options).pointer('open');
    that.closest('.yarpp_form_row, p').mouseleave(function () {
      pointer.pointer('close');
    });
  });

  $('.yarpp_template_button[data-help]').hover(function () {
    var that = $(this),
      help = '<p>' + that.attr('data-help') + '</p>',
      options = {
        content: help,
        position: {
          edge: 'bottom',
          //        align: 'center',
          of: that,
        },
        document: { body: that },
      };

    var pointer = that.pointer(options).pointer('open');
    that.mouseleave(function () {
      pointer.pointer('close');
    });

    // Only setup the copy templates button once it exists.
    $('.yarpp_copy_templates_button').on('click', function () {
      const copy_templates_button = $(this);
      const spinner = copy_templates_button.siblings('.spinner');

      copy_templates_button.addClass('yarpp-disabled');
      spinner.addClass('is-active');

      window.location =
        window.location +
        (window.location.search.length ? '&' : '?') +
        'action=copy_templates&_ajax_nonce=' +
        $('#yarpp_copy_templates-nonce').val();
    });
  });

  $('.yarpp_spin_on_click').on('click', function () {
    const button = $(this);
    const spinner = button.siblings('.spinner');

    button.addClass('yarpp-disabled');
    spinner.addClass('is-active');
  });

  $('.yarpp_template_button:not(.disabled)').click(function () {
    $(this).siblings('input').val($(this).attr('data-value')).change();
    $(this).siblings().removeClass('active');
    $(this).addClass('active');
  });

  function template_info() {
    var template = $(this).find('option:selected'),
      row = template.closest('.yarpp_form_row');
    if (!!template.attr('data-url')) {
      row
        .find('.template_author_wrap')
        .toggle(!!template.attr('data-author'))
        .find('span')
        .empty()
        .append('<a>' + template.attr('data-author') + '</a>')
        .attr('href', template.attr('data-url'));
    } else {
      row
        .find('.template_author_wrap')
        .toggle(!!template.attr('data-author'))
        .find('span')
        .text(template.attr('data-author'));
    }
    row
      .find('.template_description_wrap')
      .toggle(!!template.attr('data-description'))
      .find('span')
      .text(template.attr('data-description'));
    row
      .find('.template_file_wrap')
      .toggle(!!template.attr('data-basename'))
      .find('span')
      .text(template.attr('data-basename'));
  }
  $('#template_file, #rss_template_file').each(template_info).change(template_info);

  var loaded_optin_data = false;
  function _display_optin_data() {
    if (!$('#optin_data_frame').is(':visible') || loaded_optin_data) return;
    loaded_optin_data = true;
    var frame = $('#optin_data_frame');
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'yarpp_optin_data',
        _ajax_nonce: $('#yarpp_optin_data-nonce').val(),
      },
      beforeSend: function () {
        frame.html(loading);
      },
      success: function (html) {
        frame.html('<pre>' + html + '</pre>');
      },
      dataType: 'html',
    });
  }
  function display_optin_data() {
    setTimeout(_display_optin_data, 0);
  }
  $('#yarpp-optin-learnmore, a[aria-controls=tab-panel-optin]').bind(
    'click focus',
    display_optin_data,
  );
  display_optin_data();

  function sync_no_results() {
    var value = $(this).find('input').attr('value');
    if ($(this).hasClass('sync_no_results'))
      $('.sync_no_results input').attr('value', value);
    if ($(this).hasClass('sync_rss_no_results'))
      $('.sync_rss_no_results input').attr('value', value);
  }
  $('.sync_no_results, .sync_rss_no_results').change(sync_no_results);

  $('#yarpp_display_code').click(function () {
    var args = {
      action: 'yarpp_set_display_code',
      _ajax_nonce: $('#yarpp_set_display_code-nonce').val(),
    };
    if ($(this).is(':checked')) args.checked = true;
    $.ajax({ type: 'POST', url: ajaxurl, data: args });
    display();
    rss_display();
  });

  function auto_display_archive() {
    var available = $('.yarpp_form_post_types').is(':has(input[type=checkbox]:checked)');
    $('#yarpp-auto_display_archive').attr('disabled', !available);
    if (!available) $('#yarpp-auto_display_archive').prop('checked', false);
  }

  $('.yarpp_form_post_types input[type=checkbox]').change(auto_display_archive);
  auto_display_archive();

  $('#yarpp_fulltext_expand').click(function (e) {
    e.preventDefault();
    var details = $('#yarpp_fulltext_details');

    details.slideToggle();

    if (details.hasClass('hidden')) {
      details.removeClass('hidden');
      $(this).text('Hide Details [-]');
    } else {
      details.addClass('hidden');
      $(this).text('Show Details [+]');
    }
  });
  $('.include_post_type input[type=checkbox]').change(function (e) {
    var get_attr = $(this).attr('data-post-type');
    if ($('#yarpp-same_post_type').is(':checked')) {
      yarpp_enable_disabel_checkbox($(this).is(':checked'), get_attr);
    } else {
      $('.yarpp_form_post_types #yarpp_post_type_' + get_attr).prop('disabled', false);
    }
  });
  $('#yarpp-same_post_type').change(function (e) {
    var get_checkboxes = '.include_post_type input[type=checkbox]';
    if ($(this).is(':checked')) {
      $(get_checkboxes).each(function () {
        var get_attr = $(this).attr('data-post-type');
        yarpp_enable_disabel_checkbox($(this).is(':checked'), get_attr);
      });
    } else {
      $('.yarpp_form_post_types input[type=checkbox]').prop('disabled', false);
      $('.yarpp_form_post_types input[type=checkbox]').siblings().hide();
    }
  });
  function yarpp_enable_disabel_checkbox(checked, get_attr) {
    if (checked) {
      $('.yarpp_form_post_types #yarpp_post_type_' + get_attr).prop('disabled', false);
      $('.yarpp_form_post_types #yarpp_post_type_' + get_attr)
        .siblings()
        .hide();
    } else {
      $('.yarpp_form_post_types #yarpp_post_type_' + get_attr).prop('disabled', true);
      $('.yarpp_form_post_types #yarpp_post_type_' + get_attr)
        .siblings()
        .show();
    }
  }
  var yarpp_model = $(
    '\
			<div id="shareaholic-deactivate-dialog" class="shareaholic-deactivate-dialog" data-remodal-id="">\
				<div class="shareaholic-deactivate-header" style="background-image: url(' +
      yarpp_messages.logo +
      '); background-color: ' +
      yarpp_messages.bgcolor +
      ';"><div class="shareaholic-deactivate-text"><h2>' +
      yarpp_messages.model_title +
      '</h2></div></div>\
				<div class="shareaholic-deactivate-body">\
					<div class="shareaholic-deactivate-body-foreword">' +
      yarpp_messages.alert_message +
      '</div>\
					<div class="shareaholic-deactivate-dialog-footer">\
                    <input type="submit" class="button confirm button-secondary" id="yarpp-clear-cache-submit" value="Delete"/>\
						<button data-remodal-action="cancel" class="button button-secondary">Cancel</button>\
						</div>\
				</div>\
			</div>\
		',
  )[0];
  $('#yarpp-clear-cache').click(function () {
    var inst = $(yarpp_model).remodal({
      hashTracking: false,
      closeOnOutsideClick: false,
    });
    inst.open();
    event.preventDefault();
  });
  $(document.body).on('click', '#yarpp-clear-cache-submit', function () {
    var inst = $(yarpp_model).remodal();
    /**
     * Closes the modal window
     */
    inst.close();
    var cache_button = '#yarpp-clear-cache';
    var display_notices = '#display_notices';
    var notice_class = 'notice notice-error is-dismissible';
    $(cache_button).prop('disabled', true);
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'yarpp_clear_cache',
        _ajax_nonce: $('#clear_cache-nonce').val(),
      },
      beforeSend: function () {
        $(cache_button).siblings('.spinner').addClass('is-active');
      },
      success: function (data) {
        $(cache_button).siblings('.spinner').removeClass('is-active');
        $(display_notices).show();
        if ('success' == data) {
          var message = yarpp_messages.success;
          notice_class = 'notice notice-success is-dismissible';
          $(cache_button).prop('disabled', false);
        } else if ('forbidden' == data) {
          var message = yarpp_messages.forbidden;
        } else if ('nonce_fail' == data) {
          var message = yarpp_messages.nonce_fail;
        } else {
          var message = yarpp_messages.error;
        }
        $(display_notices).addClass(notice_class);
        $(display_notices).html('<p>' + message + '</p>');
      },
      error: function (data) {
        $(display_notices).show();
        $(display_notices).addClass(notice_class);
        $(cache_button).siblings('.spinner').removeClass('is-active');
        $(display_notices).html('<p>' + yarpp_messages.error + '</p>');
      },
    });
    $(display_notices).delay(5000).fadeOut(1000);
  });
});
