jQuery(function ($) {
	var chart = $('#wapt-main-chart');
	var ctx = document.getElementById('wapt-main-chart');
	window.wapt_chart = new window.apthumb.Chart(ctx, {
		type: 'doughnut',
		data: {
			datasets: [
				{
					data: [
						chart.attr('data-no_featured_image'),
						chart.attr('data-w_featured_image'),
						chart.attr('data-errors'),
					],
					backgroundColor: [
						'#d6d6d6',
						'#8bc34a',
						'#f1b1b6',
					],
					spacing: 0,
					borderWidth: 0,
					label: 'Dataset 1'
				}
			]
		},
		options: {
			legend: {
				display: false
			},
			events: [],
			animation: {
				easing: 'easeOutBounce'
			},
			responsive: false,
			cutoutPercentage: 80
		}
	});

	var bulkGeneration = {
		inprogress: false,
		rt_images: [],
		rt_total: 0,
		rt_count: 1,
		rt_percent: 0,
		posted_count: 0,
		genpostthumbsbar: $("#genpostthumbsbar"),

		init: function () {
			this.startGenButton = $('#generate-post-thumbnails');
			this.startUnsetButton = $('#delete-post-thumbnails');

			this.registerEvents();
		},

		registerEvents: function () {
			var self = this;

			this.startGenButton.on('click', function () {
				self.generate();

				return false;
			});
			this.startUnsetButton.on('click', function () {
				self.unSetImages();

				return false;
			});
		},

		button_running: function (selector) {
			if (selector.hasClass('wapt-running')) {
				selector.removeClass('wapt-running');
				this.startGenButton.removeAttr('disabled');
				this.startUnsetButton.removeAttr('disabled');
			} else {
				selector.addClass('wapt-running');
				this.startGenButton.attr('disabled', 'disabled');
				this.startUnsetButton.attr('disabled', 'disabled');
			}
		},

		/**
		 * Start generation
		 */
		generate: function () {
			var self = this;
			this.inprogress = true;

			self.rt_images = [];
			this.button_running(this.startGenButton);

			this.genpostthumbsbar.show();
			progressbar = this.genpostthumbsbar.progressbar();
			progressbar.find(".ui-progressbar-value").css({
				"background": '#c9deb2',
			});
			progressbar.find(".ui-widget-content").css({
				"border": '0',
			});

			$("#genpostthumbsbar-percent").html("0%");

			var get_posts_data = {
				action: "get-posts-ids",
				withThumb: 0,
				_ajax_nonce: wapt.nonce_get_posts
			};

			if (wapt.is_premium) {
				$.extend(get_posts_data, {
					poststatus: $("#filter_poststatus").val(),
					posttype: $("#filter_posttype").val(),
					date_start: $("#filter_startdate").val(),
					date_end: $("#filter_enddate").val(),
					category: $("#filter_postcategory").val()
				});
			}


			$.post("admin-ajax.php", get_posts_data, function (result) {
				if (result.success && result.data !== '' || result.data !== 0) {
					//var ids = result.data;

					self.rt_images = result.data;
					self.rt_total = self.rt_images.length;
					self.rt_count = 1;
					self.rt_percent = 0;
					self.posted_count = 0;

					self.genPostThumb(self.rt_images.shift());
				} else {
					setTimeout(function () {
						self.genpostthumbsbar.hide();
						self.genpostthumbsbar.progressbar("value", 0);
						self.button_running(self.startGenButton);
						noticeId = $.wbcr_factory_templates_116.app.showNotice("<p><strong>" + wapt.i8n_processed_posts + " 0</strong></p>", 'success');
					}, 500);
				}
			});

		},

		/**
		 * Start unset images
		 */
		unSetImages: function () {
			var self = this;
			this.inprogress = true;

			if (!confirm('Are sure to delete thumbnails from posts?'))
				return;

			self.rt_images = [];

			this.button_running(this.startUnsetButton);

			this.genpostthumbsbar.show();
			this.genpostthumbsbar.progressbar();
			$("#genpostthumbsbar-percent").html("1%");

			var get_posts_ids_data = {
				action: "get-posts-ids",
				withThumb: 1,
				_ajax_nonce: wapt.nonce_get_posts
			};
			if (wapt.is_premium) {
				$.extend(get_posts_ids_data, {
					poststatus: $("#filter_poststatus").val(),
					posttype: $("#filter_posttype").val(),
					date_start: $("#filter_startdate").val(),
					date_end: $("#filter_enddate").val(),
					category: $("#filter_postcategory").val()
				});
			}


			$.post("admin-ajax.php", get_posts_ids_data, function (result) {
				if (result.success && result.data !== '' || result.data !== 0) {
					self.rt_images = result.data;
					self.rt_total = self.rt_images.length;
					self.rt_count = 1;
					self.rt_percent = 0;
					self.posted_count = 0;

					self.delPostThumb(self.rt_images.shift());
				}
			});

		},

		delPostThumb: function (id) {
			var self = this;
			$.post("admin-ajax.php", {
				action: "delete_post_thumbnails",
				id: id,
				_ajax_nonce: wapt.nonce_del_post_thumbs
			}, function (posted) {
				if (Boolean(posted)) {
					self.posted_count++;
				}
				self.rt_percent = (self.rt_count / self.rt_total) * 100;
				self.genpostthumbsbar.progressbar("value", self.rt_percent);
				$("#genpostthumbsbar-percent").html(Math.round(self.rt_percent) + "%");
				self.rt_count++;

				if (self.rt_images.length) {
					self.delPostThumb(self.rt_images.shift());
				} else {
					setTimeout(function () {
						self.genpostthumbsbar.hide();
						self.genpostthumbsbar.progressbar("value", 0);
						self.button_running(self.startUnsetButton);

						noticeId = $.wbcr_factory_templates_116.app.showNotice(wapt.i8n_processed_posts + self.rt_total + "<br>" + wapt.i8n_del_images + self.posted_count, 'success');
					}, 500);
				}
			});
		},

		genPostThumb: function (id) {
			var self = this;

			$.post("admin-ajax.php", {
				action: "generatepostthumbnail",
				id: id,
				_ajax_nonce: wapt.nonce_gen_post_thumbs
			}, function (response) {
				if (response.success) {
					self.posted_count++;
				}

				self.rt_percent = (self.rt_count / self.rt_total) * 100;
				self.genpostthumbsbar.progressbar("value", self.rt_percent);
				$("#genpostthumbsbar-percent").html(Math.round(self.rt_percent) + "% (" + self.rt_count + "/" + self.rt_total + ")");
				self.rt_count++;

				if (response.data) {
					self.updateLog(response.data);
				}

				if (self.rt_images.length) {
					self.genPostThumb(self.rt_images.shift());
				} else {
					setTimeout(function () {
						//self.genpostthumbsbar.hide();
						//self.genpostthumbsbar.progressbar("value", 0);
						self.button_running(self.startGenButton);

						noticeId = $.wbcr_factory_templates_116.app.showNotice(wapt.i8n_processed_posts + self.rt_total + "<br>" + wapt.i8n_set_images + self.posted_count, 'success');
					}, 500);
				}
			});
		},

		showMessage: function (text) {
			var contanier = $('.wapt-page-statistic'),
				message;

			if (contanier.find('.wapt-statistic-message').length) {
				message = contanier.find('.wapt-statistic-message');
			} else {
				message = $('<div>');
				message.addClass('wapt-statistic-message');
				contanier.append(message);
			}

			message.html(text);
		},

		destroyMessages: function () {
			$('.wapt-page-statistic').find('.wapt-statistic-message').empty();
		},

		updateLog: function (new_item_data) {
			var self = this;

			var limit = 100,
				tableEl = $('.wapt-generation-progress .wapt-table');

			if (!tableEl.length || !new_item_data) {
				return;
			}

			// если таблица была пустая
			if ($('.wapt-table-container-empty').length) {
				$('.wapt-table-container-empty').addClass('wapt-table-container').removeClass('wapt-table-container-empty');
				if (tableEl.find('tbody').length) {
					tableEl.find('tbody').empty();
				}
			}

			$.each(new_item_data, function (index, value) {
				var trEl = $('<tr>'),
					tdEl = $('<td>');

				if (tableEl.find('.wapt-row-id-' + value.post_id).length) {
					tableEl.find('.wapt-row-id-' + value.post_id).remove();
				}

				trEl.addClass('flash').addClass('wapt-table-item').addClass('wapt-row-id-' + value.post_id);

				if ('error' === value.type) {
					trEl.addClass('wapt-error');
				}

				var preview = $('<img height="50" src="' + value.thumbnail_url + '" alt="">'),
					previewUrl = $('<a href="' + value.url + '" target="_blank">' + value.title + '</a>');

				tableEl.prepend(trEl);

				if (value.error_msg) {
					var colspan = '3';
					trEl.append(tdEl.clone().addClass('wapt-image-td').append(''));
					trEl.append(tdEl.clone().addClass('wapt-title-td').append(previewUrl));
					trEl.append(tdEl.clone().text(''));
					trEl.append(tdEl.clone().text(value.type));
					trEl.addClass('wapt-error').append(tdEl.clone().text(value.error_msg));
				} else {
					trEl.append(tdEl.clone().addClass('wapt-image-td').append(preview));
					trEl.append(tdEl.clone().addClass('wapt-title-td').append(previewUrl));
					trEl.append(tdEl.clone().text(value.image_size));
					trEl.append(tdEl.clone().text(value.type));
					trEl.append(tdEl.clone().text(value.status));
				}
			});

			if (tableEl.find('tr').length > limit) {
				var diff = tableEl.find('tr').length - limit;

				for (var i = 0; i < diff; i++) {
					tableEl.find('tr:last').remove();
				}
			}
		}

	};

	$(document).ready(function () {
		bulkGeneration.init();
		$('[data-toggle="tooltip"]').tooltip();
	});

	var ajaxUrl = ajaxurl;
	var ai_data;

	function redraw_statistics(statistic) {
		$('#wapt-main-chart').attr('data-unoptimized', statistic.unoptimized)
			.attr('data-optimized', statistic.optimized)
			.attr('data-errors', statistic.error);
		$('#wapt-total-optimized-attachments').text(statistic.optimized); // optimized
		$('#wapt-original-size').text(bytesToSize(statistic.original_size));
		$('#wapt-optimized-size').text(bytesToSize(statistic.optimized_size));
		$('#wapt-total-optimized-attachments-pct').text(statistic.save_size_percent + '%');
		$('#wapt-overview-chart-percent').html(statistic.optimized_percent + '<span>%</span>');
		$('.wapt-total-percent').text(statistic.optimized_percent + '%');
		$('#wapt-optimized-bar').css('width', statistic.percent_line + '%');

		$('#wapt-unoptimized-num').text(statistic.unoptimized);
		$('#wapt-optimized-num').text(statistic.optimized);
		$('#wapt-error-num').text(statistic.error);

		var credits = $('.wapt-premium-user-balance');
		if (credits.attr('data-server') !== "server_5") {
			credits.text(statistic.credits);
		}

		if ($('.wapt-statistic-nav li.active').length) {
			$('.wapt-statistic-nav li.active').find('span.wapt-statistic-tab-percent').text(statistic.optimized_percent + '%');
		}

		window.wio_chart.data.datasets[0].data[0] = statistic.unoptimized; // unoptimized
		window.wio_chart.data.datasets[0].data[1] = statistic.optimized; // optimized
		window.wio_chart.data.datasets[0].data[2] = statistic.error; // errors
		window.wio_chart.update();
		if ($('#wapt-overview-chart-percent').text() == '100%') {
			window.onbeforeunload = null;
		}
	}

})
;
