var wmcaptcha_fieldbox_a_clicked = false;
function wmcaptcha_captcha_ajax(){
	wmcaptcha_fieldbox_a_clicked = true;
	jQuery(".wmcaptcha_fieldbox_a").animate({opacity: 0.25}, 300, function() {});
	var r = {"action":"wmsimplecaptcha_action"}
	jQuery.ajax({
		type: "POST",
		url: ajax_object.ajax_url,
		data:  r,
		//dataType: "json",
		success:function(data) {
			//alert(JSON.stringify(data))
			//alert(data);
			wmcaptcha_fieldbox_a_clicked = false;
			jQuery(".wmcaptcha_fieldbox_img").html(data);
			jQuery(".wmcaptcha_fieldbox_a").animate({opacity: 1}, 500, function() {});
		},
		error: function(jqxhr, textStatus, error ){
			wmcaptcha_fieldbox_a_clicked = false;
			alert(jqxhr + "  - " + textStatus + "  - " + error + " --- " + jqxhr.responseText);
		}
	});
}
jQuery( document ).ready(function( $ ) {
	$(".wmcaptcha_fieldbox_a.refresh_button, .wmcaptcha_fieldbox_img.refresh_image").click(function(){	
		if(wmcaptcha_fieldbox_a_clicked) return false;
		wmcaptcha_captcha_ajax();
		return false;
	});
});    