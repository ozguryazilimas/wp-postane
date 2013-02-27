( function( $ ){
	
	$( function() {
	
		// Hide the meta box by default...
		$( '#custompermalinkdiv' ).hide();
		
		// Enable the highlighted part of the permalink to be clickable...
		makeCustomSlugeditClickable = function() {
			
			// The default edit permalink button...
			var button = $( '#edit-slug-buttons .edit-slug' );
			
			// Set to customise button if there is no original edit button...
			if ( $( '#edit-slug-buttons .edit-slug' ).length == 0 ) {
				button = $( '#customise-permalink-buttons .customise-permalink' );
			}
			
			// Add event to capture the click and display the required edit input...
			$( '#editable-post-name' ).click( function() {
				button.click();
			} );
			
		};
		
		// Wrapper for editing the original permalink...
		editOriginalPermalink = function( post_id ) {
			
			// Hide the customise button...
			var b = $ ( '#customise-permalink-buttons' ).hide();
			
			// Run the original function...
			editPermalink( post_id );
			
			// Show the customise button again...
			$( '#edit-slug-buttons a' ).click( function( e ) {
				// When saving the default, ensure the custom is unset...
				if ( $( e.target ).hasClass( 'save' ) ) {
					$( '#custom_permalink' ).val( '' );
				}
				b.show();
			} );
			
			return false;
			
		};
		
		// Replicate the functionality for editing a permalink (/wp-admin/js/post.js) ...
		editCustomPermalink = function( post_id ) {
			
			// Hide view and original edit buttons...
			$( '#view-post-btn, #edit-slug-buttons' ).hide();
			
			// Define elements and revert values...
			var s = $( '#sample-permalink' ), revert_s = s.html(), real_slug = $( '#custom_permalink' ), revert_slug = real_slug.val(), b = $( '#customise-permalink-buttons' ), revert_b = b.html();
			
			// Add the Save and Cancel buttons...
			b.html( '<a href="#" class="save button">' + postL10n.ok + '</a> <a class="cancel" href="#">' + postL10n.cancel + '</a>' );
			
			// Save the custom permalink and update the output...
			$( '.save', b ).click( function() {
				var permalink = s.children( 'input' ).val();
				$.post( ajaxurl, {
					action: 'sample-permalink',
					post_id: post_id,
					new_slug: $( '#post_name' ).val(),
					custom_slug: permalink,
					new_title: $( '#title' ).val(),
					samplepermalinknonce: $( '#samplepermalinknonce' ).val()
				}, function( data ) {
					$( '#edit-slug-box' ).html( data );
					b.html( revert_b );
					real_slug.val( permalink == '' ? '' : $( '#sample-permalink' ).text() );
					$( '#view-post-btn' ).show();
					makeCustomSlugeditClickable();
				} );
				return false;
			});
			
			// Cancel customisation and revert to previous values...
			$( '.cancel', b ).click( function() {
				$( '#view-post-btn, #edit-slug-buttons' ).show();
				s.html( revert_s );
				b.html( revert_b );
				real_slug.val( revert_slug );
				makeCustomSlugeditClickable();
				return false;
			});
			
			// Append the custom permalink input...
			s.html( '<input type="text" id="new-custom-permalink" size="40" value="' + ( revert_slug == '' ? s.text() : revert_slug ) + '" />' )
				.children( 'input' )
				.keypress( function( e ) {
					var key = e.keyCode || 0;
					if ( 13 == key ) {
						$( '.save', b ).click();
						return false;
					}
					if ( 27 == key ) {
						$( '.cancel', b ).click();
						return false;
					}
					real_slug.val( this.value );
				} ).focus();
			
		};
		
		makeCustomSlugeditClickable();
		
	} );
	
} )( jQuery );