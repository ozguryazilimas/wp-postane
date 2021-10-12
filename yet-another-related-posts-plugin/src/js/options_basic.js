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
		var generate_missing_thumbnails = metabox.find('.generate_missing_thumbnails');
		if (value === 'builtin') {
			generate_missing_thumbnails.hide();
		} else {
			generate_missing_thumbnails.show();
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

	function rss_display() {
		if (!$('#yarpp_display_rss .inside').is(':visible')) return;
		if ($('#yarpp-rss_display').is(':checked')) {
			$('.rss_displayed').show();
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

			if (taxonomy in term_indices)
				term_indices[taxonomy] = term_indices[taxonomy] + 100;
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

	const show_code_button = document.getElementById('yarpp-preview-show-code');
	const code_container = document.getElementById('yarpp-display-code-preview');

	function toggle_code_container(force_show = false) {
		if (code_container.style.display === 'none' || force_show) {
			code_container.style.display = 'block';

			show_code_button.innerText = yarpp_messages.hide_code;
		} else {
			code_container.style.display = 'none';

			show_code_button.innerText = yarpp_messages.show_code;
		}
	}

	if (show_code_button) {
		show_code_button.addEventListener('click', () => toggle_code_container());
	}

	const drag_container = document.querySelector(
		'#yarpp-display-html-preview .yarpp-preview__iframe__container',
	);

	if (drag_container) {
		let mouse_position;
		function resize_html_preview(event) {
			const dx = event.x - mouse_position;
			mouse_position = event.x;
			drag_container.style.width =
				parseInt(getComputedStyle(drag_container, '').width) + dx + 'px';
			update_preview_width_ui();
		}

		drag_container.addEventListener('mousedown', function (e) {
			const dragger_position = e.layerX - e.offsetX;
			if (dragger_position < 50) {
				mouse_position = e.x;
				document.addEventListener('mousemove', resize_html_preview, false);
			}
		});
		document.addEventListener('mouseup', function () {
			document.removeEventListener('mousemove', resize_html_preview, false);
		});
	}

	let preview_body = {};

	function handle_change(field, event, target_value = 'value') {
		const new_value = event.target[target_value];
		if (target_value === 'checked') {
			preview_body[field] = new_value ? 1 : 0;
		} else {
			preview_body[field] = new_value;
		}
		show_display_preview();
	}

	const limit_input = document.querySelector('#limit[name=limit]');
	if (limit_input) {
		limit_input.addEventListener('blur', (e) => handle_change('limit', e));
	}

	const promote_yarpp_checkbox = document.querySelector(
		'#yarpp-promote_yarpp[name=promote_yarpp]',
	);
	if (promote_yarpp_checkbox) {
		promote_yarpp_checkbox.addEventListener('change', (e) =>
			handle_change('promote_yarpp', e, 'checked'),
		);
	}

	const order_select = document.querySelector('#order[name=order]');
	if (order_select) {
		order_select.addEventListener('change', (e) => handle_change('order', e));
	}

	const custom_template_select = document.querySelector(
		'#template_file[name=template_file]',
	);
	if (custom_template_select) {
		custom_template_select.addEventListener('change', (e) =>
			handle_change('template', e),
		);
	}

	const thumbnails_template_size_select = document.querySelector(
		'#thumbnail_size_display[name=thumbnail_size_display]',
	);
	if (thumbnails_template_size_select) {
		thumbnails_template_size_select.addEventListener('change', (e) =>
			handle_change('size', e),
		);
	}

	const custom_template_size_select = document.querySelector(
		'#custom_theme_thumbnail_size_display[name=custom_theme_thumbnail_size_display]',
	);
	if (custom_template_size_select) {
		custom_template_size_select.addEventListener('change', (e) =>
			handle_change('size', e),
		);
	}

	const thumbnails_heading_input = document.querySelector(
		'#thumbnails_heading[name=thumbnails_heading]',
	);
	if (thumbnails_heading_input) {
		thumbnails_heading_input.addEventListener('blur', (e) =>
			handle_change('thumbnails_heading', e),
		);
	}

	const thumbnails_default_input = document.querySelector(
		'#thumbnails_default[name=thumbnails_default]',
	);
	if (thumbnails_default_input) {
		thumbnails_default_input.addEventListener('blur', (e) =>
			handle_change('thumbnails_default', e),
		);
	}

	const list_before_related_input = document.querySelector(
		'#before_related[name=before_related]',
	);
	if (list_before_related_input) {
		list_before_related_input.addEventListener('blur', (e) =>
			handle_change('before_related', e),
		);
	}

	const list_after_related_input = document.querySelector(
		'#after_related[name=after_related]',
	);
	if (list_after_related_input) {
		list_after_related_input.addEventListener('blur', (e) =>
			handle_change('after_related', e),
		);
	}

	const list_before_title_input = document.querySelector(
		'#before_title[name=before_title]',
	);
	if (list_before_title_input) {
		list_before_title_input.addEventListener('blur', (e) =>
			handle_change('before_title', e),
		);
	}

	const list_after_title_input = document.querySelector(
		'#after_title[name=after_title]',
	);
	if (list_after_title_input) {
		list_after_title_input.addEventListener('blur', (e) =>
			handle_change('after_title', e),
		);
	}

	const list_show_excerpt = document.querySelector(
		'#yarpp-show_excerpt[name=show_excerpt]',
	);
	if (list_show_excerpt) {
		list_show_excerpt.addEventListener('change', (e) =>
			handle_change('show_excerpt', e, 'checked'),
		);
	}

	const list_excerpt_length = document.querySelector(
		'#excerpt_length[name=excerpt_length]',
	);
	if (list_excerpt_length) {
		list_excerpt_length.addEventListener('blur', (e) =>
			handle_change('excerpt_length', e),
		);
	}

	const list_before_post_input = document.querySelector(
		'#before_post[name=before_post]',
	);
	if (list_before_post_input) {
		list_before_post_input.addEventListener('blur', (e) =>
			handle_change('before_post', e),
		);
	}

	const list_after_post_input = document.querySelector('#after_post[name=after_post]');
	if (list_after_post_input) {
		list_after_post_input.addEventListener('blur', (e) =>
			handle_change('after_post', e),
		);
	}

	function set_preview_width(width) {
		drag_container.style.width = width + 'px';
		update_preview_width_ui();
	}

	function update_preview_width_ui() {
		const backdrop = document.querySelector('.yarpp-preview__iframe__backdrop');
		// Compute and display template preview iframe width in UI
		if (backdrop) {
			document.querySelector(
				'#yarpp-preview__show-preview-width__value',
			).innerText = backdrop.offsetWidth - 20 + 'px';
		}
	}

	// If the browser window is resized, update template preview iframe width displayed in UI
	window.addEventListener('resize', update_preview_width_ui);

	const preview_mobile_button = document.querySelector('#yarpp-preview-mobile');
	if (preview_mobile_button) {
		preview_mobile_button.addEventListener('click', (e) => set_preview_width(320));
	}

	const preview_tablet_button = document.querySelector('#yarpp-preview-tablet');
	if (preview_tablet_button) {
		preview_tablet_button.addEventListener('click', (e) => set_preview_width(768));
	}

	const preview_desktop_button = document.querySelector('#yarpp-preview-desktop');
	if (preview_desktop_button) {
		preview_desktop_button.addEventListener('click', (e) => set_preview_width(1200));
	}

	function show_display_preview(template = null) {
		// disables the Show Button and waits until it's available to enable it again
		show_code_button.setAttribute('disabled', 'disabled');
		var html_frame = $(
			'#yarpp-display-html-preview .yarpp-preview__iframe__container',
		);
		var code_frame = $('#yarpp-display-code-preview');
		const backdrop = document.querySelector(
			'#yarpp-display-html-preview div.yarpp-preview__iframe__backdrop',
		);

		// displays preview width in ui
		update_preview_width_ui();

		if (template) {
			// if the template is custom, checks the custom select template and changes it
			if (template === 'custom') {
				preview_body.template = custom_template_select.value;

				if (custom_template_size_select.value) {
					preview_body.size = custom_template_size_select.value;
				}
			} else {
				preview_body.template = template;
			}

			if (template === 'thumbnails') {
				if (thumbnails_template_size_select.value) {
					preview_body.size = thumbnails_template_size_select.value;
				}
			}
		}

		console.log('Rendering Template Preview: ', preview_body);

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'yarpp_display_preview',
				_ajax_nonce: $('#yarpp_display_preview-nonce').val(),
				...preview_body,
			},
			beforeSend: function () {
				html_frame.html(loading);
			},
			success: function (response) {
				const json = JSON.parse(response);
				const inline_css = document.createElement('style');
				inline_css.appendChild(document.createTextNode(json.styles));
				const preview_iframe = document.createElement('iframe');
				preview_iframe.width = '100%';
				preview_iframe.height = '100%';
				preview_iframe.style =
					'overflow: hidden; user-select: none; outline: 0; background-color: white;';
				html_frame.html(preview_iframe);
				html_frame.prepend(backdrop);

				preview_iframe.onload = (event) => {
					const iframe_document = event.target.contentWindow.document;
					iframe_document
						.getElementsByTagName('head')[0]
						.appendChild(inline_css);
					iframe_document.body.innerHTML = json.html;
					iframe_document.body.style = 'overflow:hidden;';
				};

				if ('codeEditor' in wp) {
					toggle_code_container(true);
					const settings = {
						...wp.codeEditor.defaultSettings,
						type: 'text/html',
						codemirror: {
							...wp.codeEditor.defaultSettings.codemirror,
							indentUnit: 2,
							tabSize: 2,
							readOnly: true,
						},
					};
					code_frame.html(
						'<textarea id="yarpp-preview-code-textarea">' +
							json.code +
							'</textarea>',
					);
					wp.codeEditor.initialize(
						document.getElementById('yarpp-preview-code-textarea'),
						settings,
					);
					toggle_code_container();
				} else {
					code_frame.html('<pre><code>' + json.code + '</code></pre>');
				}
				// removes the disabled when it's ready
				show_code_button.removeAttribute('disabled');
			},
			dataType: 'html',
		});
	}

	show_display_preview();

	$('.yarpp_template_button:not(.disabled)').click(function () {
		const current_template = $(this).attr('data-value');
		$(this).siblings('input').val(current_template).change();
		$(this).siblings().removeClass('active');
		$(this).addClass('active');
		if ($(this).parents('#yarpp_display_web').length) {
			show_display_preview(current_template);
		}
	});

	function template_info() {
		var template = $(this).find('option:selected'),
			row = template.closest('.yarpp_form_row');
		if (!!template.attr('data-url')) {
			row.find('.template_author_wrap')
				.toggle(!!template.attr('data-author'))
				.find('span')
				.empty()
				.append('<a>' + template.attr('data-author') + '</a>')
				.attr('href', template.attr('data-url'));
		} else {
			row.find('.template_author_wrap')
				.toggle(!!template.attr('data-author'))
				.find('span')
				.text(template.attr('data-author'));
		}
		row.find('.template_description_wrap')
			.toggle(!!template.attr('data-description'))
			.find('span')
			.text(template.attr('data-description'));
		row.find('.template_file_wrap')
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

	function auto_display_archive() {
		var available = $('.yarpp_form_post_types').is(
			':has(input[type=checkbox]:checked)',
		);
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
			$('.yarpp_form_post_types #yarpp_post_type_' + get_attr).prop(
				'disabled',
				false,
			);
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
			$('.yarpp_form_post_types #yarpp_post_type_' + get_attr).prop(
				'disabled',
				false,
			);
			$('.yarpp_form_post_types #yarpp_post_type_' + get_attr)
				.siblings()
				.hide();
		} else {
			$('.yarpp_form_post_types #yarpp_post_type_' + get_attr).prop(
				'disabled',
				true,
			);
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
