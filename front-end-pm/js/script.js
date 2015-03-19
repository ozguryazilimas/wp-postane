var $ = jQuery; //if you use google CDN version of jQuery comment this line.
$( document ).on( "keyup", "#fep-message-top", function() {	
				document.getElementById('fep-result').style.display="none";
				$('.fep-ajax-img').show();
					var display_name=$('#fep-message-top').val();
					var data = {
									action: 'fep_autosuggestion_ajax',
									searchBy: display_name,
									token: fep_script.nonce
									};
									
		$.post(fep_script.ajaxurl, data, function(results) {
			$('.fep-ajax-img').hide();
			$('#fep-result').html(results);
			document.getElementById('fep-result').style.display="block";
			if (results=='')
			{document.getElementById('fep-result').style.display="none";}
			
			});
				});

function fep_fill_autosuggestion(login, display) {
	
	document.getElementById('fep-message-to').value=login;
	document.getElementById('fep-message-top').value=display;
	document.getElementById('fep-result').style.display="none";
}