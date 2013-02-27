jQuery(document).ready(function($){
	$(".spoiler").each(function(){
		sb_replace_content(this);
	});
	$(".spoiler").click(function(){
		if($(this).hasClass("spoiler")){
			$(this).fadeOut("slow", function(){
				$(this).removeClass("spoiler").addClass("spoiler-open");
				var message = $(this).html();
				$(this).html($(this).attr("rel")).fadeIn("slow");
				$(this).attr("rel", message);
			});
		}
	});
	$(".spoiler-open").live('click', function(){
		if($(this).hasClass("spoiler-open")){
			$(this).fadeIn("slow", function(){
				$(this).removeClass("spoiler-open").addClass("spoiler");
				var message = $(this).attr("rel");
				$(this).attr("rel", $(this).html());
				$(this).html(message).fadeIn("slow");
			});
		}
	})

});

function sb_replace_content(element){
	// Esse if se mantem pois as versões anteriores a 1.6.4 não tinham o span escondido.
	var content = (jQuery(element).find(".hidden-content").html() == null) ? jQuery(element).html() : jQuery(element).find(".hidden-content").html() ;
		
	jQuery(element).attr("rel", content);
	jQuery(element).html(spoiler_message);
}