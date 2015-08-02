(function($) {
	$(document).ready( function() {
		/* add notice about changing in the settings page */
		$( '#cptch_settings_form input' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#cptch_settings_notice' ).css( 'display', 'block' );
			};
		});
	});
})(jQuery);