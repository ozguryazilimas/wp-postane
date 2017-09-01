/*!
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/
 */
(function( $ ) {
	$(document).ready(function() {

		/**
		 * Handle the styling of the "Settings" tab on the plugin settings page
		 * @since 4.2.3
		 */
		var limitOption	= $( '#hctpc_enable_time_limit' ),
			imageFormat	= $( '#hctpc_operand_format_images' );

		/*
		* Hide "time limit thershold" field under unchecked "time limit" field
		*/
		if ( ! $( limitOption ).is( ':checked' ) ) {
			$( limitOption ).closest( 'tr' ).nextAll( '.hctpc_time_limit' ).hide();
		}

		$( limitOption ).click( function() {
			$( limitOption ).closest( 'tr' ).nextAll( '.hctpc_time_limit' ).toggle();
		});

		/*
		 * Hide all unused related forms on settings page
		 */
		$.each( $( "input[name*='[enable]']" ), function() {
			var formName       = '.' + $( this ).attr( 'id' ).replace( 'enable', 'related_form' ),
				formBlock      = $( formName );

			$( this ).is( ':checked' ) ? formBlock.show() : formBlock.hide();

			$( this ).click( function() {
				if ( $( this ).is( ':checked' ) ) {
					formBlock.show();
				} else {
					formBlock.hide();
				}
			});
		});

		/* Handle the displaying of notice message above lists of image packages */
		function hctpcImageOptions() {
			var isChecked = imageFormat.is( ':checked' );
			if ( isChecked ){
				$( '.hctpc_images_options' ).show();
			} else {
				$( '.hctpc_images_options' ).hide();
			}
		}
		hctpcImageOptions()
		imageFormat.click( function() { hctpcImageOptions(); } );

		function hctpc_type() {
			if ( 'recognition' == $( 'input[name="hctpc_type"]:checked' ).val() ) {
				$( '.hctpc_for_recognition' ).show();
				$( '.hctpc_for_math_actions' ).hide();
				imageFormat.attr( 'checked', 'checked' );
				hctpcImageOptions();
			} else {
				$( '.hctpc_for_recognition' ).hide();
				$( '.hctpc_for_math_actions' ).show();
			}
		}
		hctpc_type();
		$( 'input[name="hctpc_type"]' ).click( function() { hctpc_type(); } );

		/**
		 * Hide/show whitelist "add new form"
		 */
		$( 'button[name="hctpc_show_whitelist_form"]' ).click( function() {
			$( this ).parent( 'form' ).hide();
			$( '.hctpc_whitelist_form' ).show();
			return false;
		});

		/*  add to whitelist my ip */
		$( 'input[name="hctpc_add_to_whitelist_my_ip"]' ).change( function() {
			if ( $( this ).is( ':checked' ) ) {
				var my_ip = $( 'input[name="hctpc_add_to_whitelist_my_ip_value"]' ).val();
				$( 'input[name="hctpc_add_to_whitelist"]' ).val( my_ip ).attr( 'readonly', 'readonly' );
			} else {
				$( 'input[name="hctpc_add_to_whitelist"]' ).val( '' ).removeAttr( 'readonly' );
			}
		});

		/**
		 * Handle the styling of the "Settings" tab on the plugin settings page
		 */
		var tabs = $( '#hctpc_settings_tabs_wrapper' );
		if ( tabs.length ) {
			var current_tab_field = $( 'input[name="hctpc_active_tab"]' ),
				prevent_tabs_change = false,
				active_tab = current_tab_field.val();
			if ( '' == active_tab ) {
				var active_tab_index = 0;
			} else {
				var active_tab_index = $( '#hctpc_settings_tabs li[data-slug=' + active_tab + ']' ).index();
			}

			$( '.hctpc_tab' ).css( 'min-height', $( '#hctpc_settings_tabs' ).css( 'height' ) );

			/* jQuery tabs initialization */
			tabs.tabs({
				active: active_tab_index
			}).on( "tabsactivate", function( event, ui ) {
				if ( ! prevent_tabs_change ) {
					active_tab = ui.newTab.data( 'slug' );
					current_tab_field.val( active_tab );
				}
				prevent_tabs_change = false;
			});
			$( '.hctpc_trigger_tab_click' ).on( 'click', function () {
				$( '#hctpc_settings_tabs a[href="' + $( this ).attr( 'href' ) + '"]' ).click();
			});
		}
	});
})(jQuery);