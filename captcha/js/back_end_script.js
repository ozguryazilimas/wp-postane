(function( $ ) {
	$(document).ready(function() {
		$( 'input.insert-tag-captcha' ).click( function() {
			var $form = $( this ).closest( 'form.tag-generator-panel' );
			var tag = $form.find( 'input.captcha' ).val();
			wpcf7.taggen.insert( tag );
			tb_remove(); // close thickbox
			return false;
		} );
	});
})(jQuery);