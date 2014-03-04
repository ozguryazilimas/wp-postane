jQuery(function($) {
	$('#build-cache-button').click(function() {
		$('#yarpp-cache-message, #yarpp-latest, #build-cache-button').hide();
		$('#build-display').css('display','block');
		$('#yarpp-time').show().html('&nbsp;');
		yarppBuildRequest();
	});
	$('#flush-cache-button').click(function() {
		window.location = window.location + (window.location.search.length ? '&' : '?') + 'action=flush&_ajax_nonce=' + $('#yarpp_cache_flush-nonce').val();
	});
});
var time = 0,
	i = 0,
	m = 0,
	timeout = 10000,
	lastid = 0;
function yarppBuildRequest() {
	var start_time = Date.now();
	jQuery.ajax({
		url: ajaxurl,
		type: 'post',
		data: {action:'yarpp_build_cache',i:i,m:m,lastid:lastid},
		dataType: 'json',
		timeout: timeout,
		success: function (json) {
			if (json.result == 'success') {
				i = json.i;
				m = json.m;
				lastid = json.id;
				time += Date.now() - start_time;
				var remaining = Math.floor( (m-i) * (time/i) / 1000 );
				var min = Math.floor(remaining/60);
				var sec = Math.floor(remaining - 60*min);
				if (i < m) {
					jQuery('#yarpp-bar').css('width',(json.status * 100)+'%');
					jQuery('#yarpp-percentage').html(Math.round(json.status * 1000)/10);
					if (min > 0) {
						jQuery('#yarpp-time').html(min + ' minute(s) and ' + sec + ' second(s) remaining');
					} else {
						jQuery('#yarpp-time').html(sec + ' second(s) remaining');
					}
					yarppBuildRequest();
				} else {
					jQuery('#build-display').html('Your related posts cache is now complete. Reload for updated cache stats.<br/><small>The SQL queries took ' + (Math.floor(time*10/1000)/10) + ' seconds </small>');
				}
			} else if (json.result == 'error') {
				i = json.i + 1; // bump it up to try to skip this item for now.
				m = json.m;
				jQuery('#yarpp-latest').show().html('There was an error while constructing the related posts for ' + json.title );
				jQuery('#build-cache-button').show().val('Try to continue...');
				jQuery('#yarpp-time').hide();
			} else if (json.result == 'premature') {
				jQuery('#yarpp-latest').show().html('Cache computation ended prematurely. Try reloading the page and continuing.');
				jQuery('#build-cache-button, #yarpp-time').hide();
			} else {
				jQuery('#yarpp-latest').show().html('Constructing the related posts timed out.');
				timeout += 5000;
				jQuery('#build-cache-button').show().val('Try to continue...');
				jQuery('#yarpp-time').hide();
			}
		},
		error: function(json) {
			jQuery('#yarpp-latest').html('Constructing the related posts timed out.');
			timeout += 5000;
			jQuery('#build-cache-button').show().val('Try to continue...');
			jQuery('#yarpp-time').hide();
		}
	});
	return false;
}