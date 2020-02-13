jQuery( document ).ready( function( $ ) {
	/**
	* Dismissable Notices
	* - Sends an AJAX request to mark the notice as dismissed
	*/
	$( 'div.exactmetrics-notice' ).on( 'click', 'button.notice-dismiss', function( e ) {
		e.preventDefault();
		$( this ).closest( 'div.exactmetrics-notice' ).fadeOut();

		// If this is a dismissible notice, it means we need to send an AJAX request
		if ( $( this ).parent().hasClass( 'is-dismissible' ) ) {
			$.post(
				exactmetrics_admin_common.ajax,
				{
					action: 'exactmetrics_ajax_dismiss_notice',
					nonce:  exactmetrics_admin_common.dismiss_notice_nonce,
					notice: $( this ).parent().data( 'notice' )
				},
				function( response ) {},
				'json'
			);
		}

	} );
});