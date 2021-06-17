jQuery(document).ready(function ($) {
	var loaded_metabox = false;
	var display = $('#yarpp-related-posts');

	/*
	 * Populates Metabox initially
	 */
	function yarpp_metabox_initial_display() {
		if (!$('#yarpp_relatedposts') || !display.length || !$('#post_ID').val()) return;

		if (!loaded_metabox) {
			loaded_metabox = true;
			yarpp_metabox_populate(false);
		}
	}

	/*
	 * Populates Metabox
	 * @param bool refresh
	 */
	function yarpp_metabox_populate(refresh) {
		var data = {
			action: 'yarpp_display',
			domain: 'metabox',
			ID: parseInt($('#post_ID').val()),
			_ajax_nonce: $('#yarpp_display-nonce').val(),
		};
		if (typeof refresh !== 'undefined' && refresh) {
			data['refresh'] = true;
		}
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			error: function () {
				display.html('Error');
			},
			success: function (html) {
				display.html(html);
			},
			dataType: 'html',
		});
	}

	$('#yarpp_relatedposts .handlediv, #yarpp_relatedposts-hide').click(function () {
		setTimeout(yarpp_metabox_initial_display, 0);
	});

	/*
	 * Metabox Actions
	 */
	$(document).on('touchstart mouseenter', '#yarpp-list li', function () {
		$(this).children('.yarpp-related-action').css('visibility', 'visible');
	});

	$(document).on('touchend mouseleave', '#yarpp-list li', function () {
		$(this).children('.yarpp-related-action').css('visibility', 'hidden');
	});

	/*
	 * Metabox Refresh Button
	 */
	$(document).on('click', '#yarpp-refresh', function (e) {
		e.preventDefault();

		var display = $('#yarpp-related-posts');

		if ($(this).hasClass('disabled')) return false;

		const refresh_button = $(this);
		const spinner = refresh_button.siblings('.spinner');

		refresh_button.addClass('yarpp-disabled');
		spinner.css('visibility', 'visible');

		$('#yarpp-list').css('opacity', 0.6);
		yarpp_metabox_populate(true);
	});

	/*
	 * Initial Load
	 */
	yarpp_metabox_initial_display();
});
