( function( $ ) {
	$( document ).ready( function() {
		cptch_init();
	});
})(jQuery);

/**
 * Add custom events for captcha`s elevents
 * @param  void
 * @return void
 */
function cptch_init() {
	(function($) {
		/* reload captcha */
		$( '.cptch_reload_button' ).click( function( event ) {
			event.preventDefault();
			var block_class =  $( this ).attr( 'class' ), 
				captcha = $( this ).parent().parent( '.cptch_wrap' );
			$( this ).addClass( 'cptch_active' );
			if ( captcha.length ) {
				$.ajax({
					type: 'POST',
					url: cptch_vars.ajaxurl,
					data: {
						action:      'cptch_reload',
						cptch_nonce: cptch_vars.nonce
					},
					success: function( result ) {
						captcha.replaceWith( result );
						cptch_init();
					},
					error : function ( xhr, ajaxOptions, thrownError ) {
						alert( xhr.status );
						alert( thrownError );
					}
				});
			}
		});

		/* enlarge images by click on mobile devices */
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Windows Phone|Opera Mini/i.test( navigator.userAgent ) && cptch_vars.enlarge == '1' ) {
			$( '.cptch_img' ).click( function( event ) {
				event.preventDefault();
				var reduce = $( this ).hasClass( 'cptch_reduce' );
				if ( $( this ).hasClass( 'cptch_reduce' ) )
					$( this ).css({ zIndex: 1 }).animate({ width: $( this ).width() / 2 + 'px' }, 800 ).toggleClass( 'cptch_reduce' );
				else
					$( this ).css({ position: 'absolute', zIndex: 2 }).animate({ width: $( this ).width() * 2 + 'px' }, 800 ).toggleClass( 'cptch_reduce' );
				$( '.cptch_span' ).children( '.cptch_reduce' ).not( this ).each( function() {
					$( this ).css({ zIndex: 1 }).animate({ width: $( this ).width() / 2 + 'px' }, 800 ).toggleClass( 'cptch_reduce' );
				});
			}).parent( '.cptch_span' ).each( function() {
				var image = $( this ).children( 'img' );
				if ( image.length ) {
					$( this ).css({ 
						width: image.width() + 'px',
						height: image.height() + 'px',
					});
				}
			});
		}
	})(jQuery);
}