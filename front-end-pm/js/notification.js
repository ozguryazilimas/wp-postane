var $ = jQuery; //if you use google CDN version of jQuery comment this line.
$(document).ready(function(){
		var data = {
					action: 'fep_notification_ajax',
					token: fep_notification_script.nonce
					};
        var fep_ajax_call = function(){
		$.post(fep_notification_script.ajaxurl, data, function(results) {
			$('#fep-notification-bar').html(results);
			if (results=='')
			{document.getElementById('fep-notification-bar').style.display="none";}
			else 
			{document.getElementById('fep-notification-bar').style.display="block";}
												  });
        }
        setInterval(fep_ajax_call,5000);
      });