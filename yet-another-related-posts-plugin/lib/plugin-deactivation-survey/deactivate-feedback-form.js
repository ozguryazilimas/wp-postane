(function($) {

	if ( ! window.shareaholic) {
		window.shareaholic = {};
	}

	if (shareaholic.DeactivateFeedbackForm) {
		return;
	}

	shareaholic.DeactivateFeedbackForm = function(plugin)
	{
		var self    = this;
		var strings = shareaholic_deactivate_feedback_form_strings;

		this.plugin = plugin;

		// Dialog HTML
		var element  = $(
			'\
			<div id="shareaholic-deactivate-dialog" class="shareaholic-deactivate-dialog" data-remodal-id="' + plugin.basename + '">\
				<div class="shareaholic-deactivate-header" style="background-image: url(' + plugin.logo + '); background-color: ' + plugin.bgcolor + ';"><div class="shareaholic-deactivate-text"><h2>' + strings.quick_feedback + '</h2></div></div>\
				<div class="shareaholic-deactivate-body">\
				<form>\
					<input type="hidden" name="plugin"/>\
					<div class="shareaholic-deactivate-body-foreword">\
						' + strings.foreword + '\
					</div>\
					<ul class="shareaholic-deactivate-reasons"></ul>\
					<div style="display:none;" id="shareaholic-deactivate-comment-area">\
						<textarea class="shareaholic-deactivate-text-area" name="comment" rows="3" id="shareaholic-deactivate-comment" placeholder="' + strings.please_tell_us + '"/></textarea>\
            <p class="shareaholic-deactivate-help">' + strings.ask_for_support + '</p>\
					</div>\
					<div class="shareaholic-deactivate-contact">\
					' + strings.email_request + '\
					</div>\
					<input type="email" name="email" class="shareaholic-deactivate-input" value="' + strings.email + '">\
          <div class="shareaholic-deactivate-divider"></div>\
					<div class="shareaholic-deactivate-dialog-footer">\
            <span class="spinner" style="float: none;"></span> \
						<input type="submit" class="button confirm button-secondary" id="shareaholic-deactivate-submit" value="' + strings.skip_and_deactivate + '"/>\
						<button data-remodal-action="cancel" class="button button-secondary">' + strings.cancel + '</button>\
					</div>\
				</form>\
				</div>\
			</div>\
		'
		)[0];
		this.element = element;

		$( element ).find( "input[name='plugin']" ).val( JSON.stringify( plugin ) );

		$( element ).on(
			"click",
			"input[name='reason']",
			function(event) {
				var submit_input     = $( element ).find( "input[type='submit']" );
				var comment_textarea = $( element ).find( '#shareaholic-deactivate-comment-area' );
				if (self.plugin.reasons_needing_comment.indexOf( event.target.value ) > -1) {
					comment_textarea.appendTo( $( event.target ).parent().parent() );
					comment_textarea.show();
				} else {
					comment_textarea.hide();
				}
				submit_input.val(
					strings.submit_and_deactivate
				);
				self.maybeDisableSubmit();
			}
		);

		$( element ).find( 'textarea#shareaholic-deactivate-comment' ).keyup(
			function(){
				self.maybeDisableSubmit();
			}
		);

		$( element ).find( "form" ).on(
			"submit",
			function(event) {
				self.onSubmit( event );
			}
		);

		// Reasons list
		var ul = $( element ).find( "ul.shareaholic-deactivate-reasons" );
		for (var key in plugin.reasons) {
			var li = $( "<li><label><input type='radio' name='reason'/> <span></span></label></li>" );

			$( li ).find( "input" ).val( key );
			$( li ).find( "span" ).html( plugin.reasons[key] );

			$( ul ).append( li );
		}

		// Listen for deactivate
		$( "#the-list [data-plugin='" + plugin.basename + "'] .deactivate>a" ).on(
			"click",
			function(event) {
				self.onDeactivateClicked( event );
			}
		);
	}

	shareaholic.DeactivateFeedbackForm.prototype.maybeDisableSubmit = function (){
		var submit_input     = $( "#shareaholic-deactivate-submit" );
		var comment_textarea = $( 'textarea#shareaholic-deactivate-comment' );
		var reason_value     = $( 'input[name="reason"]:checked' ).val();
		if (this.plugin.reasons_needing_comment.indexOf( reason_value ) > -1
			&& comment_textarea.val().replace( /\s/g, '' ) === '') {
			submit_input.prop( 'disabled',true );
			submit_input.prop( 'title',shareaholic_deactivate_feedback_form_strings.please_tell_us );
		} else {
			submit_input.prop( 'disabled',false );
			submit_input.prop( 'title','' );
		}
	}

	shareaholic.DeactivateFeedbackForm.prototype.onDeactivateClicked = function(event)
	{
		this.deactivateURL = event.target.href;

		if ( ! this.dialog) {
			this.dialog = $( this.element ).remodal();
		}
		this.dialog.open();
		event.preventDefault();
	}

	shareaholic.DeactivateFeedbackForm.prototype.onSubmit = function(event)
	{
		var element          = this.element;
		var strings          = shareaholic_deactivate_feedback_form_strings;
		var self             = this;
		var data             = this.plugin.send;
		data.survey_response = {
			contact:{
				email: $( element ).find( "input[name='email']" ).val()
			},
			type:'uninstall',
			data:{
				reason_id: $( element ).find( "input[name='reason']:checked" ).val(),
				reason_comment: $( element ).find( "textarea[name='comment']" ).val()
			}
		};

		$( element ).find( "button, input[type='submit']" ).prop( "disabled", true );
		$( element ).find( "input[type='submit']" ).val( strings.please_wait );
		$( element ).find( "button, input[type='submit']" ).siblings( '.spinner' ).addClass( 'is-active' );

		if ($( element ).find( "input[name='reason']:checked" ).length) {
			$.ajax(
				{
					type:		"POST",
					url:		"https://" + this.plugin.api_server + "/api/plugin_surveys",
					data:		JSON.stringify( data ),
					contentType: 'application/json',
					dataType: 'json',
					success:	function(response, textStatus) {
						window.location.href = self.deactivateURL;
					},
					error:	function(response, textStatus, errorThrown) {
						window.location.href = self.deactivateURL;
					},
				}
			);
		} else {
			window.location.href = self.deactivateURL;
		}
		event.preventDefault();
		return false;
	}

	$( document ).ready(
		function() {

			for (var i = 0; i < shareaholic_deactivate_feedback_form_plugins.length; i++) {
				var plugin = shareaholic_deactivate_feedback_form_plugins[i];
				new shareaholic.DeactivateFeedbackForm( plugin );
			}

		}
	);

})( jQuery );
