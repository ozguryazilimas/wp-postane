<?php
/*
 * Bismillahirrahmanirrahim
 * Ana Kurulum Fonksiyonu
 * @name milat Kurulum
 * @since 1.3.1
*/                   
 if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }

	// Kurulum bölümü
   	if(get_option('milat_kurulum') != '1') {
	   $ayarlar = array(
      	'milat_kurulum' => '1',
      	'milat_baslik' => '',
      	'milat_yazi' => 'jQuery Milat Popup',
		'milat_tur' => 'html',
		'milat_tur_icerik' => 'youtube',
		'milat_youtube' => '',
		'milat_dailymotion' => '',
		'milat_vimeo' => '',
		'milat_swf' => '',
		'milat_resim' => '',
		'milat_border_genislik' => '5',
		'milat_border' => 'dddddd',
		'milat_buton_stil' => 'white',
		'milat_resim_link' => '',
      	'milat_anasayfa' => 'yes',
      	'milat_heryer' => 'yes',
      	'milat_cookie_saat' => '1',
      	'milat_genislik' => '325',
      	'milat_yukseklik' => '200',
        'milat_arkaplan' => '000000',
 //      'milat_arkakapat' => '',
 //       'milat_esckapat' => ''
        );

    	foreach($ayarlar as $key=>$value) {
    		add_option($key, $value);
    	}
	}

