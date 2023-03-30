/**
 * This function:
 *
 * Generates a custom image uploader / selector tied to a post where the click action originated
 * Upon clicking "Use as thumbnail" the image selected is set to be the post thumbnail
 * A thumbnail image is then shown in the All Posts / All Pages / All Custom Post types Admin Dashboard view
 *
 * @since 1.0.0
 *
 * global ajaxurl, apt_thumb - language array
 */
(function ($) {
	jQuery(document).ready(
		function ($) {

			jQuery(document).on(
				'click',
				'#hide_notice_auto_generation',
				function (e) {
					e.preventDefault();
					jQuery('#notice_auto_generation').animate({opacity: 'hide', height: 'hide'}, 200);
					jQuery.post(
						ajaxurl,
						{
							action: 'hide_notice_auto_generation',
						}
					).done(
						function (html) {
							console.log('Hided');
						}
					);

				}
			);

			var file_frame;
			jQuery(document).on('click', '#wapt_thumbs div.wapt-image-box-library', function (event) {
				var $el = $(this);
				var $post_id = $el.data('postid');

				event.preventDefault();

				// Create the media frame.
				file_frame = wp.media.frames.media_file = wp.media({
					// Set the title of the modal.
					title: $el.data('choose'),
					button: {
						text: $el.data('update')
					},
					states: [
						new wp.media.controller.Library({
							title: $el.data('choose'),
							library: wp.media.query({type: 'image'})
						})
					]
				});

				// When an image is selected, run a callback.
				file_frame.on('select', function () {
					var attachment = file_frame.state().get('selection').first().toJSON();

					tb_remove();
					// AJAX запрос для обновления картинки поста
					jQuery.post(ajaxurl, {
						action: 'apt_replace_thumbnail',
						post_id: $post_id,
						thumbnail_id: attachment.id,
						_ajax_nonce: $el.data('nonce'),
					}).done(function (thumb_url) {
						window.location.reload();
					});
				});

				// Finally, open the modal.
				file_frame.open();
			});

			//Отображение окна со всеми картинками в тексте поста
			window.aptModalShow = function (that, postid, wpnonce) {

				var $modal = $('#post_imgs_' + postid).find('> div');
				var $ajaximg = $('#post_imgs_' + postid).find('> span');
				//$modal.html('');
				$('.imgs').find('> div').html(''); //очистка всех модальных окон, чтобы исключить конфликты

				tb_show(apt.modal_title, '/?TB_inline&inlineId=post_imgs_' + postid + '&width=650&height=' + (window.innerHeight - 150));
				//tb_show(apt.modal_title, '/?TB_inline&inlineId=post_imgs_' + postid + '&width=650&height=500');

				$ajaximg.show();
				// AJAX запрос для загрузки контента окна
				jQuery.post(
					ajaxurl,
					{
						action: apt.action_column_get_thumbnails,
						post_id: postid,
						_ajax_nonce: wpnonce,
					}
				).done(
					function (html) {
						$ajaximg.hide();
						$modal.html(html);
						/*
						$('#wapt_thumbs').autocolumnlist({
						columns: 3,
						classname: 'wapt-grid-item',
						min: 1
						});
						*/
					}
				);

				//return false; // для ссылки
			}
		}
	);
})(jQuery);
