<?php

/**
 * Dropdown List of fonts and upload Control
 *
 * Main options:
 *  name            => a name of the control
 *  value           => a value to show in the control
 *  default         => a default value of the control if the "value" option is not specified
 *  items           => a callback to return items or an array of items to select
 *
 * @author Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020, Webcraftic Ltd
 *
 * @package factory-forms
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wapt_FactoryForms_FontsControl' ) ) {

	class Wapt_FactoryForms_FontsControl extends Wbcr_FactoryForms463_DropdownControl {

		public $type = 'wapt-fonts';

		/**
		 * Shows the assets
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function assets() {
			wp_enqueue_script( 'apt-upload-font', WAPT_PLUGIN_URL . '/admin/assets/js/upload-font.js', [], WAPT_PLUGIN_VERSION, true );
			wp_localize_script( 'apt-upload-font', 'wapt_upload_font', [ 'nonce' => wp_create_nonce( 'wapt_upload_font' ) ] );
			?>
			<style>
				.wapt-upload-div
				{
					margin: 10px 0px;
					display: inline;
					vertical-align: middle;
					margin-left: -5px !important;
				}

				.wapt-upload-button
				{
					height: 34px;
					box-shadow: 1px 1px 5px -2px #8e8d8d;
					border: 1px solid #ccc;
					border-radius: 0px 3px 3px 0px;
				}

				.wapt-upload-loader
				{
					height: 34px;
					display: inline;
					margin-left: 5px !important;
				}

				.wapt-loader-invisible
				{
					display: none !important;
				}


			</style>
			<?php
		}

		/**
		 * Shows the html markup of the control.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function html() {

			parent::addCssClass( 'factory-hidden' );
			parent::addCssClass( 'wapt-form-control' );
			parent::html();

			$this->assets();
			?>
			<div class="wapt-upload-div">
				<input type="file" accept=".ttf" id="wapt-font-file" style="display: none;">
				<button id="wapt-upload-button" class="wapt-upload-button">Upload custom font</button>
				<div id="wapt-upload-loader" class="wapt-upload-loader wapt-loader-invisible">
					<img src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/ajax-loader.gif' ); ?>"
					     alt="" height="34">
				</div>
			</div>
			<?php

		}

	}
}