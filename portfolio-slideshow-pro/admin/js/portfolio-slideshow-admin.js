$=jQuery.noConflict();
this.vtip=function(){this.xOffset=-10;this.yOffset=10;$(".vtip").unbind().hover(function(e){this.t=this.title;this.title='';this.top=(e.pageY+yOffset);this.left=(e.pageX+xOffset);$('body').append('<p id="vtip"><img id="vtipArrow" />'+this.t+'</p>');$('p#vtip').css("top",this.top+"px").css("left",this.left+"px").fadeIn("slow");},function(){this.title=this.t;$("p#vtip").fadeOut("slow").remove();}).mousemove(function(e){this.top=(e.pageY+yOffset);this.left=(e.pageX+xOffset);$("p#vtip").css("top",this.top+"px").css("left",this.left+"px");});};jQuery(document).ready(function($){vtip();})

$(function() {
	$( "#tabs" ).tabs();

	$('#portfolio_slideshow_options_click').parent().parent().next().attr('id', 'click-target'); 
	$('#portfolio_slideshow_options_size').parent().parent().next().attr('id', 'custom-size');
	$('#portfolio_slideshow_options_pagerstyle').parent().parent().nextAll('tr:lt('+4+')').addClass('thumbs');
	$('#carousel-thumbsize, #carouselsize, #carousel-thumbnailmargin').parent().parent().addClass('carousel');
	
	pagerStyleVal = $('select#portfolio_slideshow_options_pagerstyle').val();
	if ( pagerStyleVal != "thumbs" ) { $('.thumbs').addClass("hidden"); }
	
	$('select#portfolio_slideshow_options_pagerstyle').change(function() {
		pagerStyleVal = $('select#portfolio_slideshow_options_pagerstyle').val();
  		if ( pagerStyleVal != "thumbs" ) { $('.thumbs').addClass("hidden"); } else { $('.thumbs').removeClass("hidden"); }
	});


pagerStyleVal = $('select#portfolio_slideshow_options_pagerstyle').val();
if ( pagerStyleVal != "carousel" ) { $('.carousel').addClass("hidden"); }

$('select#portfolio_slideshow_options_pagerstyle').change(function() {
	pagerStyleVal = $('select#portfolio_slideshow_options_pagerstyle').val();
		if ( pagerStyleVal != "carousel" ) { $('.carousel').addClass("hidden"); } else { $('.carousel').removeClass("hidden"); }
});



	clickVal = $('select#portfolio_slideshow_options_click').val();
	if ( clickVal != "openurl" ) { $('#click-target').addClass("hidden"); }

	$('select#portfolio_slideshow_options_click').change(function() {
		clickVal = $('select#portfolio_slideshow_options_click').val();
		if ( clickVal != "openurl" ) { $('#click-target').addClass("hidden"); } else { $('#click-target').removeClass("hidden"); }		
	});

		customSizeVal = $('select#portfolio_slideshow_options_size').val();
	if ( customSizeVal != "custom" ) { $('#custom-size').addClass("hidden");}
	
	$('select#portfolio_slideshow_options_size').change(function() {
		customSizeVal = $('select#portfolio_slideshow_options_size').val();
  		if ( customSizeVal != "custom" ) { $('#custom-size').addClass("hidden"); } else { $('#custom-size').removeClass("hidden"); }
  		
	});
	
	
});