<?php
/*
 * Bismillahirrahmanirrahim
 * @jQuery Popup
 * @since 1.3.1
*/ 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }

 function MilatJS() {
  //  wp_deregister_script('jquery');
    wp_enqueue_script('jquery1.7.1',MILAT_PLUGIN_URL . 'lib/js/jquery.1.7.1.js');
	wp_enqueue_script('milat',  MILAT_PLUGIN_URL . 'lib/js/jquery-milat.js');
 }

 function MilatCSS() {
 	wp_enqueue_style('milat',  MILAT_PLUGIN_URL . 'lib/css/style.css');
 }

 function MilatPopupHTML() {
	$genislik   = get_option('milat_genislik');
	$yukseklik  = get_option('milat_yukseklik');
	$milat_tur = get_option('milat_tur') ;
	
		if($milat_tur=='html'){
			$icerik = do_shortcode(stripcslashes(get_option('milat_yazi')));
			$baslik = stripcslashes(get_option('milat_baslik'));
			if(!empty($baslik)){
				$icerik = '<div id="baslik"><h2>'.$baslik.'</h2></div>'.
				$icerik;
			}
		}
		elseif($milat_tur=='resim'){
				$res_link = get_option('milat_resim_link');
				if(empty($res_link)){
					$icerik = "<img src=\"".stripcslashes(get_option('milat_resim'))."\"  width=\"$genislik\" height=\"$yukseklik\"  />";
				}else{
					$icerik = "<a href=\"".stripcslashes(get_option('milat_resim_link'))."\"><img src=\"".stripcslashes(get_option('milat_resim'))."\"  width=\"$genislik\" height=\"$yukseklik\"  /></a>";
				}	
		}elseif($milat_tur=='video'){
			$milat_tur_icerik = get_option('milat_tur_icerik') ;
			if($milat_tur_icerik=='youtube'){
				$icerik = "<iframe width=\"$genislik\" height=\"$yukseklik\" src=\"http://www.youtube.com/embed/".stripcslashes(get_option('milat_youtube'))."\" frameborder=\"0\" allowfullscreen></iframe>";
			}elseif($milat_tur_icerik=='daily'){
				$icerik = "<iframe width=\"$genislik\" height=\"$yukseklik\" src=\"http://www.dailymotion.com/embed/video/".stripcslashes(get_option('milat_dailymotion'))."\" frameborder=\"0\"></iframe>";
			}elseif($milat_tur_icerik=='vimeo'){
				$icerik = "<iframe src=\"http://player.vimeo.com/video/".stripcslashes(get_option('milat_vimeo'))."?title=0&amp;byline=0&amp;portrait=0\" width=\"$genislik\" height=\"$yukseklik\" frameborder=\"0\" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>";
			}elseif($milat_tur_icerik=='swf'){
				$icerik = "<embed quality=\"high\" width=\"$genislik\" height=\"$yukseklik\" src=\"".stripcslashes(get_option('milat_swf'))."\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></embed>";
			}
		}
	
 	$html = <<<HTML
 	<div id="kutu">
		<div style="display:none;" id="pencere" class="window">
				$icerik
			<a href="#" class="close"></a>
		</div>
		<div id="karartma"></div>
	</div>
HTML;

 echo $html;
 }


function milatInit() {
 	MilatJS();
 	MilatCSS();
 }

function MilatPopupJS() {
	
	$genislik   = get_option('milat_genislik');
	$yukseklik  = get_option('milat_yukseklik');
 	$zaman      = get_option('milat_cookie_saat');
	if($zaman=="0"){
	 $zaman = "jmil.cookie('milat', null, {expires: -1});";
	}else{
	 $zaman = "jmil.cookie('milat', {expires: $zaman});";
	}
	$baslikcss = "#baslik{ background-color:#".get_option('milat_border').";  margin: -".get_option('milat_border_genislik')."px -".get_option('milat_border_genislik')."px 0px -".get_option('milat_border_genislik')."px; border-bottom: #".get_option('milat_border')."; }";
	$buton     = "#kutu .close { background: url(".MILAT_PLUGIN_URL."lib/css/button_".get_option('milat_buton_stil')."_close.png); }";
    $arkaplan  = "#karartma {	background-color: #".get_option('milat_arkaplan')."; }";
	$border    = "#kutu .window { border:".get_option('milat_border_genislik')."px solid #".get_option('milat_border')."; }";
    if(!empty($genislik)){
    $genislik =  "#pencere {	width: ".$genislik."px;  }";
    }
    if(!empty($yukseklik)){
      $yukseklik =  "#pencere {	height: ".$yukseklik."px; }";
    }

 	$js = <<<JS
    <style type="text/css">
	  $baslikcss
      $arkaplan
      $genislik
      $yukseklik
      $border
	  $buton
    </style>
 	<script type="text/javascript">
	 $zaman
	</script>
JS;

 	echo $js;
 }

 function milatPopup() {
 	$incele = false;
 	$milat_anasayfa = get_option('milat_anasayfa');
 	$milat_heryer = get_option('milat_heryer');

	 	if(is_home()) {
	 		if($milat_anasayfa == 'yes') {
	 			$incele = true;
	 		}
	 	}
	 	elseif((is_page()) or (is_single()) ) {
	 		if($milat_heryer == 'yes') {
	 			$incele = true;
	 		}
	 	}
 	if($incele) {
 		add_action('wp_footer', 'MilatPopupHTML');
 		add_action('wp_head', 'MilatPopupJS');
 	}

 }

 ?>