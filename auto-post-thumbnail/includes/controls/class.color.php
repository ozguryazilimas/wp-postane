<?php
/**
 * Color
 *
 * Main options:
 *  name            => a name of the control
 *  value           => a value to show in the control
 *  default         => a default value of the control if the "value" option is not specified
 *
 * @author Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Wapt_FactoryForms_ColorControl' ) ) {

	class Wapt_FactoryForms_ColorControl extends Wbcr_FactoryForms430_Control {

		public $type = 'wapt-color';

		/**
		 * Shows the html markup of the control.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function html() {
			$name  = $this->getNameOnForm();
			$value = esc_attr( $this->getValue() );

			if ( ! $value ) {
				$value = '#ffffff';
			}
			?>
            <script src="<?php echo WAPT_PLUGIN_URL; ?>/admin/assets/js/jscolor.js"></script>
            <style>
                .wapt-jscolor {
                    padding: 15px !important;
                    font-size: 16px !important;
                    cursor: pointer;
                }
            </style>
            <div <?php $this->attrs() ?>>
                <input type="text" readonly id="<?php echo $name; ?>" name="<?php echo $name; ?>"
                       class="factory-input-text jscolor wapt-jscolor" value="<?php echo $value; ?>">
            </div>
			<?php
		}
	}
}