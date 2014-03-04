jQuery(function($) {
	function throttle_update() {
		var percent = 1 / Math.pow(10, parseInt($('#throttle').val()) / 4);
		if ( percent == 1 )
			$('#throttle_percent').html( '<strong>always (no throttling)</strong>' );
		else if ( percent < 1 )
			$('#throttle_percent').html( '<strong>' + (Math.floor(percent * 10000) / 100) + '%</strong> of the time' );	
	}

	$("#throttle")
	.rangeinput({
		progress: false
	})
	.change(throttle_update);
	
	throttle_update();
});