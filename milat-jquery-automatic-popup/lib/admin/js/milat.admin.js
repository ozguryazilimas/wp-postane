jQuery(document).ready(function() {
 jQuery("#editor-toolbar").remove();
  jQuery("#quicktags").attr('id', 'benimtags'); 
	jQuery('#milat_resim_button').click(function() {
	 formfield = jQuery('#milat_resim').attr('name');
	 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	 return false;
	});

	window.send_to_editor = function(html) {
	 imgurl = jQuery('img',html).attr('src');
	 jQuery('#milat_resim').val(imgurl);
	 tb_remove();
	}
});

$(document).ready(function() {

    $('.milat_yatay_tabs a').bind('click',function(e) {
		e.preventDefault();
		var threfs = $(this).attr("href").replace(/#/, '');
		$('.milat_yatay_tabs a').removeClass('milat_yatay_tab_active');
		$(this).addClass('milat_yatay_tab_active');
		$('.milat_yatay_view .milat_yatay_tab_view').removeClass('milat_yatay_firmilat_tab_view').slideUp('slow');
		$('#'+threfs).addClass('milat_yatay_firmilat_tab_view').slideDown('slow');
			if ( threfs == "milat_ic_duzen_1"  ) {
				$('#milat_ic_duzen_1 #youtube').attr({   checked: "checked"});
			}
			else if (threfs == "milat_ic_duzen_2"){
				$('#milat_ic_duzen_2 #daily').attr({   checked: "checked"});
			}
			else if (threfs == "milat_ic_duzen_3"){
				$('#milat_ic_duzen_3 #vimeo').attr({   checked: "checked"});
			}
			else{
				$('#milat_ic_duzen_4 #swf').attr({   checked: "checked"});
			}
    });


    $('.milat_tabs a').bind('click',function(e) {
		e.preventDefault();
		var thref = $(this).attr("href").replace(/#/, '');
		$('.milat_tabs a').removeClass('milat_tab_active');
		$(this).addClass('milat_tab_active');
		$('.milat_view .milat_tab_view').removeClass('milat_firmilat_tab_view');
		$('#'+thref).addClass('milat_firmilat_tab_view');
			if ( thref == "milat_icerik_1"  ) {
				$('#milat_icerik_1 #html').attr({   checked: "checked"});
			}
			else if (thref == "milat_icerik_2"){
				$('#milat_icerik_2 #resim').attr({   checked: "checked"});
			}
			else{
				$('#milat_icerik_3 #video').attr({   checked: "checked"});
			}
	});
  	$("input[rel='preview']").click(function(){
          var baslik   		= $("#milat_baslik").val();
          var icerik  		= $("#milat_yazi").val();
		  var milat_resim   = $("#milat_resim").val();
		  var milat_rlink   = $("#milat_resim_link").val();
		  var milat_youtu   = $("#milat_youtube").val();
		  var milat_daily   = $("#milat_dailymotion").val();
		  var milat_vimeo   = $("#milat_vimeo").val();
		  var milat_swf     = $("#milat_swf").val();
  	      var arkaplan 		= $("#colorpickerField1").val();
          var genislik 		= $("#milat_genislik").val();
          var ykseklik 		= $("#milat_yukseklik").val();
          var esctur 		= $("input[name='esckapat']:checked").val();
          var bgturu 		= $("input[name='arkaplan']:checked").val();
		  var border 		= $("#colorpickerField2").val();
		  var border_genis 	= $("#milat_border_genislik").val();
		  var tur 			= $("input[name='milat_tur']:checked").val();		
		  var tur_icerik    = $("input[name='milat_tur_icerik']:checked").val();
		  var milat_buton   = $("input[name='milat_buton_stil']:checked").val();
		  var adres         = $("#adres").val();
	//	  var text = tinyMCE.get('milat_yazi').getContent(); görsel içeriði alýyor


          var id = '#pencere';

				//Get the screen height and width
				var karartmaHeight = $(document).height();
				var karartmaWidth = $(window).width();

				//Set heigth and width to karartma to fill up the whole screen
				$('#karartma').css({'width':karartmaWidth,'height':karartmaHeight});
                $('#karartma').css({'backgroundColor':'#'+arkaplan});
				
					if(tur=="html"){
						if(baslik!==""){ var icerik = '<div id="baslik" style="background-color:#'+border+';  margin: -'+border_genis+'px -'+border_genis+'px 0px -'+border_genis+'px; border-bottom: #'+border+'";" ><h2>'+baslik+'</h2></div>'+icerik};	
						$('#baslik').attr('style','border:'+border_genis+'px solid #'+border+';');						
						$('#pencere').attr('style','height:'+ykseklik+'px; width:'+genislik+'px;  border:'+border_genis+'px solid #'+border+';');
						$('#pencere').html('<a href="#" style="background: url('+adres+'lib/css/button_'+milat_buton+'_close.png)" class="close"></a>'+icerik);
					}

					if(tur=="resim"){
						$('#pencere').attr('style','height:'+ykseklik+'px; width:'+genislik+'px;  border:'+border_genis+'px solid #'+border+';');
							if(milat_rlink==""){
								$('#pencere').html('<a href="#" style="background: url('+adres+'lib/css/button_'+milat_buton+'_close.png)" class="close"></a><img src="'+milat_resim+'"  width="'+genislik+'" height="'+ykseklik+'"  />');
							}else{
								$('#pencere').html('<a href="#" style="background: url('+adres+'lib/css/button_'+milat_buton+'_close.png)" class="close"></a><a href="'+milat_rlink+'"><img src="'+milat_resim+'"  width="'+genislik+'" height="'+ykseklik+'"  /></a>');
							}
					}
					
					if (tur=="video"){
						if(tur_icerik=="youtube"){
							$('#pencere').attr('style','height:'+ykseklik+'px; width:'+genislik+'px;  border:'+border_genis+'px solid #'+border+';');
							$('#pencere').html('<a href="#" style="background: url('+adres+'lib/css/button_'+milat_buton+'_close.png)" class="close"></a><iframe width="'+genislik+'" height="'+ykseklik+'" src="http://www.youtube.com/embed/'+milat_youtu+'" frameborder="0" allowfullscreen></iframe>');
						}
						
						if(tur_icerik=="daily"){
							$('#pencere').attr('style','height:'+ykseklik+'px; width:'+genislik+'px;  border:'+border_genis+'px solid #'+border+';');
							$('#pencere').html('<a href="#" style="background: url('+adres+'lib/css/button_'+milat_buton+'_close.png)" class="close"></a><iframe frameborder="0" width="'+genislik+'" height="'+ykseklik+'" src="http://www.dailymotion.com/embed/video/'+milat_daily+'"></iframe>');
						}

						if(tur_icerik=="vimeo"){
							$('#pencere').attr('style','height:'+ykseklik+'px; width:'+genislik+'px;  border:'+border_genis+'px solid #'+border+';');
							$('#pencere').html('<a href="#" style="background: url('+adres+'lib/css/button_'+milat_buton+'_close.png)" class="close"></a><iframe src="http://player.vimeo.com/video/'+milat_vimeo+'?title=0&amp;byline=0&amp;portrait=0" width="'+genislik+'" height="'+ykseklik+'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');						
						}

						if(tur_icerik=="swf"){
							$('#pencere').attr('style','height:'+ykseklik+'px; width:'+genislik+'px;  border:'+border_genis+'px solid #'+border+';');
							$('#pencere').html('<a href="#" style="background: url('+adres+'lib/css/button_'+milat_buton+'_close.png)" class="close"></a><embed quality="high" height="'+ykseklik+'" width="'+genislik+'" src="'+milat_swf+'" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>');						
						}						
					}
				
				//transition effect
				$('#karartma').fadeTo("slow",0.8);
				$('#karartma').fadeIn(1000);

				//Get the window height and width
				var winH = $(window).height();
				var winW = $(window).width();

				//Set the popup window to center
				$(id).css('top',  winH/2-$(id).height()/2);
				$(id).css('left', winW/2-$(id).width()/2);

				//transition effect
				$(id).show(1000);

			//if close button is clicked
			$('.window .close').click(function (e) {
				//Cancel the link behavior
				e.preventDefault();				
				$('#karartma').fadeOut();
				$('.window').slideUp();
			});        
			
			//if karartma is clicked
			$('#karartma').click(function () {
				$('#karartma').fadeOut();
				$('.window').slideUp();
			});
		
    });
});

    $('#colorpickerField1,#colorpickerField2').ColorPicker({

    	onShow: function (colpkr) {
    		$(colpkr).fadeIn(500);
    		return false;
    	},
    	onHide: function (colpkr) {
    		$(colpkr).fadeOut(500);
    		return false;
    	},
    	onChange: function (hsb, hex, rgb) {
    		$('#colorpickerField1').css('backgroundColor', '#' + hex);
        }
    });
