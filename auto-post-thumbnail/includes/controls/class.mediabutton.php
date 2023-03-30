<?php
/**
 * Media Button
 *
 * Main options:
 *  name            => a name of the control
 *  value           => a value to show in the control
 *  default         => a default value of the control if the "value" option is not specified
 *  text            => a text to button
 *
 * @author Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wapt_FactoryForms_MediaButtonControl' ) ) {

	class Wapt_FactoryForms_MediaButtonControl extends Wbcr_FactoryForms463_Control {

		public $type = 'wapt-mediabutton';

		/**
		 * Shows the html markup of the control.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function html() {
			wp_enqueue_media();

			$name          = $this->getNameOnForm();
			$value         = esc_attr( $this->getValue() );
			$button_text   = esc_attr( $this->options['text'] );
			$thumbnail_url = '';
			$image_class   = 'wapt-invisible';

			if ( ! $value ) {
				$value         = 0;
				$thumbnail_url = '';
			} else {
				$thumbnail = wp_get_attachment_image_src( (int) $value, 'thumbnail' );
				if ( is_array( $thumbnail ) ) {
					$thumbnail_url = $thumbnail[0];
					$image_class   = 'wapt-visible';
				}
			}
			?>
			<style>
				.wapt-bg-image-thumb
				{
					margin: 10px 0;
					border-radius: 10px;
					box-shadow: 2px 2px 5px 0 rgba(0, 0, 0, 0.5);
				}

				.wapt-invisible
				{
					display: none;
				}

				.wapt-visible
				{
					display: block;
				}
			</style>
			<script lang="js">
				jQuery(function ($) {

					var frame;
					$(document).on('click', '#wapt-select-image-<?php echo esc_attr( $name ); ?>', function (event) {
						event.preventDefault();

						let customPreview = document.getElementById('wapt-thumb-preview-input');
						let customPreviewTwo = document.getElementById('wapt-thumb-form-input');

						if (customPreview) {
							customPreview.remove();
							customPreviewTwo.remove();
							$(this).closest('.factory-wapt-mediabutton').prepend('<input type="hidden" id="wapt-thumb-preview-input">');
							$(this).closest('.factory-wapt-mediabutton').append('<input type="hidden" id="wapt-thumb-form-input">');
						} else {
							$(this).closest('.factory-wapt-mediabutton').prepend('<input type="hidden" id="wapt-thumb-preview-input">');
							$(this).closest('.factory-wapt-mediabutton').append('<input type="hidden" id="wapt-thumb-form-input">');
						}

						if (frame) {
							frame.open();
							return;
						}

						frame = wp.media({
							//title   : 'Выберите файл',
							button: {
								//text: 'Использовать этот файл'
							},
							multiple: false
						});

						var inputImg = $('#wapt-thumb-form-input').prev();
						frame.on('open', function () {
							if ($(inputImg).val())
								frame.state().get('selection').add(wp.media.attachment($(inputImg).val()));
						});

						frame.on('select', function () {
							var attachment = frame.state().get('selection').first().toJSON();
							var thumb = $('#wapt-thumb-preview-input').next();

							$(inputImg).val(attachment.id);
							thumb.attr('src', attachment.sizes.thumbnail.url);
							thumb.removeClass('wapt-bg-image-invisible').addClass('wapt-visible');

							frame.close();
						});

						frame.open();
					});
				});
			</script>
			<div <?php $this->attrs(); ?>>
				<img src="<?php echo esc_url_raw( $thumbnail_url ); ?>" alt=""
				     class="wapt-bg-image-thumb <?php echo esc_attr( $image_class ); ?>"
				     id="wapt-bg-image-thumb">
				<button class="button button-primary button-large <?php echo esc_attr( $name ); ?>"
				        id="wapt-select-image-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $button_text ); ?></button>
				<input type="hidden" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>"
				       class="factory-input-text"
				       value="<?php echo esc_html( $value ); ?>">
			</div>
			<?php
		}
	}
}