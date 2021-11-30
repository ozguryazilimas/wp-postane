function findImages(searchEngine, action, nonce, query, page, params, callback) {
	params = params || {};

	params['page'] = page || 1;
	params['query'] = query;
	params['action'] = action
	params['nonce'] = nonce;

	showLoader(searchEngine);

	jQuery.post(ajaxurl, params, function (response) {
		if (!response.success) {
			hideLoader(searchEngine);
			jQuery('#' + searchEngine + '_results').html("API: " + response.data.error);
			return;
		}

		if (response.data.images_count === 0) {
			hideLoader(searchEngine);
			jQuery('#' + searchEngine + '_results').html(window.wapt_no_hits);
		} else {
			hideLoader(searchEngine);
			showFoundedImages(searchEngine, response.data.images, params.page);
		}

		if (typeof callback === 'function') {
			callback(response);
		}
	});
}

function showLoader(searchEngine) {
	jQuery('#loader_flex').show();
	jQuery('#page_num_div').show();
	jQuery('#prev_page').show();
	jQuery('#next_page').show();
	jQuery('#' + searchEngine + '_loader_flex').show();
	jQuery('#' + searchEngine + '_page_num_div').show();
	jQuery('#' + searchEngine + '_prev_page').show();
	jQuery('#' + searchEngine + '_next_page').show();
}

function hideLoader(searchEngine) {
	jQuery('#loader_flex').hide();
	jQuery('#page_num_div').hide();
	jQuery('#prev_page').hide();
	jQuery('#next_page').hide();
	jQuery('#' + searchEngine + '_loader_flex').hide();
	jQuery('#' + searchEngine + '_page_num_div').hide();
	jQuery('#' + searchEngine + '_prev_page').hide();
	jQuery('#' + searchEngine + '_next_page').hide();
}

function showFoundedImages(searchEngine, images, page) {
	var totalhits = 100; //google limit
	if (page > 1) {
		jQuery("#prev_page").show();
	} else {
		jQuery("#prev_page").hide();
	}

	if (page < totalhits / 10) {
		jQuery("#next_page").show();
	} else {
		jQuery("#next_page").hide();
	}

	jQuery('#page_num_div').html(page);
	jQuery('#page_num_div').show();

	var html = '';

	jQuery.each(images, function (key, image) {
		html += '<div class="item upload_' + searchEngine + '" ' +
			'data-service="' + searchEngine + '" ' +
			'data-title="' + (image.title || searchEngine + '_image') + '" ' +
			'data-url="' + image.link + '" ' +
			'data-link="' + image.context_link + '" ' +
			'data-w="' + image.image.width + '" ' +
			'data-h="' + image.image.height + '">' +
			'<img src="' + image.thumbnail_link + '">' +
			'<div class="download"><img src="' + window.wapt_download_svg + '">' +
			'<div>' + image.image.width + '×' + image.image.height + '<br>' +
			'<a href="' + image.context_link + '" target="_blank">' + (image.title || searchEngine + '_image').substr(0, 15) + '</a>' +
			'</div>' +
			'</div>' +
			'</div>';
	});

	var resultBlock = jQuery("#" + searchEngine + "_results");
	resultBlock.html(resultBlock.html() + html);

	jQuery('#loader_flex-' + searchEngine).hide();
	jQuery('#' + searchEngine + '_results.flex-images').flexImages({rowHeight: 160});
}

function downloadMedia(service, url, query, postId, title, excerpt, nonce, _this) {
	jQuery.post(ajaxurl, {
		action: 'upload_to_library',
		is_upload: '1',
		service: service,
		image_url: url,
		q: query,
		postid: postId,
		title: title,
		excerpt: excerpt,
		wpnonce: nonce,
	}, function (data) {
		_this.removeClass('upload_' + service);
		var err_msg;

		if (parseInt(data) == data) {
			jQuery('#apt-button-next').prop('disabled', false);
			err_msg = 'DOWNLOADED';
			if (window.cvapt_media_refresh !== undefined) {
				window.parent.window.cvapt_media_refresh();
			}
		} else {
			alert(data);
			err_msg = 'ERROR';
		}
		_this.removeClass('uploading').find('.download img').replaceWith(err_msg);
	});
}
