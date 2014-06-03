<?php
/*
Plugin Name: Detailed-Search v1
Plugin URI: http://ozguryazilim.com.tr
Description: Detailed Search plugin For 22dakika.org project
Version: 1.0
Author: Kivilcim Eray
Author URI: http://github.com/kivicko
License: GPL
*/


/******************
* NOTUM

Şu kullanıcının yazdığı
bu tarih aralığında yazılan
şu kadar kişinin tuttuğu
içinde şu kelimeler geçmeyen
şu kelimelerin tümünün geçtiği
şu kelimelerin yanyana geçtiği
şu kelimelerin dağınık geçtiği
tarihe göre sondan başa sırala
tarihe göre baştan sona sırala
yazara göre alfabetik sıralama

********************/

function yazar_id_return_et($yolla){
	global $wpdb;
	$gecici_isim = $yolla;
	$benim_sql = "SELECT ID from wp_users WHERE user_login = '$gecici_isim'";
	$kullanici_id = $wpdb->get_var($benim_sql);
	return $kullanici_id;
}

// $ara_sql_ekleme yi döngü başına +1 yapacak şekilde ilerlet, en az 3 olmazsa ara sql i false döndür.
// döndüğü yerde kontrol ettir, false geldiyse işlem yapmadan ekrana 3 naz 3 deger gir hatası bassın.

function sql_sorgusu_uret_yazi($ara_yazar_id, $ara_tarih_ilk1, $ara_tarih_son1, $ara_kelime_gecen, $ara_kelime_sirali,  $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala, $ara_tutma_gelen, $ara_yazar_isim){



	global $ara_onyazi;
	$ara_tutma_gelen != NULL ? $ara_tutma = $ara_tutma_gelen : $ara_tutma = 0 ;

	// tarihlerin database aramasına uygun formda kalması gerek
	$ara_tarih_ilk = $ara_tarih_ilk1;
	$ara_tarih_son = $ara_tarih_son1;
	
	$ara_tarih_son = date('Y-m-d',strtotime($ara_tarih_son . "+1 days"));

	$ara_tarih_ilk = str_replace("-", "", $ara_tarih_ilk);
	$ara_tarih_son = str_replace("-", "", $ara_tarih_son);


	// site üzerinde ‘ karakteri var, escape stringten kurtuluyor. mysql e de ' karakteri olarak kayıtlı.
	// O sebepten bu dönüşümü yapıp escape string ile doğruluyorum.

/*
	$ara_kelime_sirali = str_replace("‘", "'", $ara_kelime_sirali);
	$ara_kelime_gecmeyen = str_replace("‘", "'", $ara_kelime_gecmeyen);
	$ara_kelime_gecen = str_replace("‘", "'", $ara_kelime_gecen);
	$ara_kelime_daginik = str_replace("‘", "'", $ara_kelime_daginik);
	$ara_kelime_sirali = str_replace("’", "'", $ara_kelime_sirali);
	$ara_kelime_gecmeyen = str_replace("’", "'", $ara_kelime_gecmeyen);
	$ara_kelime_gecen = str_replace("’", "'", $ara_kelime_gecen);
	$ara_kelime_daginik = str_replace("’", "'", $ara_kelime_daginik);

	$ara_kelime_sirali = mysql_escape_string($ara_kelime_sirali);
	$ara_kelime_gecmeyen = mysql_escape_string($ara_kelime_gecmeyen);
	$ara_kelime_gecen = mysql_escape_string($ara_kelime_gecen);
	$ara_kelime_daginik = mysql_escape_string($ara_kelime_daginik);
*/

	// Gelen yazıları string -> array explode yapmamız gerek, foreach ile döndürebilmek için.
	if($ara_kelime_gecen != NULL){
		$ara_kelime_gecen = explode(" ",$ara_kelime_gecen);
	}
	if($ara_kelime_daginik != NULL){
		$ara_kelime_daginik = explode(" ",$ara_kelime_daginik);
	}
	if($ara_kelime_gecmeyen != NULL){
		$ara_kelime_gecmeyen = explode(" ",$ara_kelime_gecmeyen);
	}

	$ara_onyazi = " <br> Arama sonucu ";
	$ara_sql["query"] = "SELECT * FROM wp_posts WHERE post_status = %s AND post_type = %s ";
	$ara_sql["variables"] = array("publish","post");

	if($ara_yazar_id != NULL){
		$ara_sql["query"] .= " AND post_author = %d ";
		array_push($ara_sql["variables"],$ara_yazar_id);
		$ara_onyazi .= "'" . $ara_yazar_isim . "' üyesine ait olan ";
	}

	if($ara_tarih_ilk != NULL && $ara_tarih_son != NULL){
		$ara_sql["query"] .= " AND post_date >= %d AND post_date <= %d ";
		array_push($ara_sql["variables"],$ara_tarih_ilk,$ara_tarih_son);
		$ara_onyazi .= "'" . $ara_tarih_ilk1 . "' ile '" . $ara_tarih_son1 . "' tarihleri arasında yazılmış ";
	}

	if($ara_kelime_gecen != NULL){
		$ara_onyazi .= "' ";
		foreach ($ara_kelime_gecen as $key ){
			$ara_sql["query"] .= " AND post_content LIKE '%%s%' ";
			array_push($ara_sql["variables"],$key);
			$ara_onyazi .= $key . " ";
		}
			$ara_onyazi .= "' ifadesi gecen";
	}

	if($ara_kelime_daginik != NULL){
		$ara_sql["query"] .= "AND ( 1=0";
		$ara_onyazi .= "' ";
		foreach ($ara_kelime_daginik as $key ){
			$ara_sql["query"].= " OR post_content LIKE '%%s%' ";
			array_push($ara_sql["variables"],$key);
			$ara_onyazi .= $key . " ";
		}
		$ara_sql["query"].= ")";
		$ara_onyazi .= "' kelimelerine sahip olan ";
	}

	if($ara_kelime_sirali != NULL){
		$ara_sql["query"].= " AND post_content LIKE '%%s%' ";
		array_push($ara_sql["variables"],$ara_kelime_sirali);
		$ara_onyazi .= "'" . $ara_kelime_sirali . "' kelimeleri sıralı olan ";
	}

	if($ara_kelime_gecmeyen != NULL){
		foreach ($ara_kelime_gecmeyen as $key ){
			$ara_sql["query"].= "AND post_content NOT LIKE '%%s%' ";
			array_push($ara_sql["variables"],$key);
			$ara_onyazi .= "'" . $key ."' ,";
		}
			$ara_onyazi .= " kelimelerini bulundurmayan";
	}
	if($ara_tarih_sirala == "artan"){
		$ara_sql["query"].= "order by post_date asc";
	}else{
		$ara_sql["query"].= "order by post_date desc";
	}

//	döndürülen yazı ile stringin komutu aynı olduğundan, içinden / karakterlerini silmek caizdir.
	$ara_onyazi = str_replace("\\","",$ara_onyazi);
	return $ara_sql;
}

function sql_sorgusu_uret_yorum($ara_yazar_isim, $ara_tarih_ilk1, $ara_tarih_son1, $ara_kelime_gecen, $ara_kelime_sirali, $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala){

	global $ara_onyazi;

	// tarihlerin database aramasına uygun formda kalması gerek
	$ara_tarih_ilk = $ara_tarih_ilk1;
	$ara_tarih_son = $ara_tarih_son1;

	$ara_tarih_son = date('Y-m-d',strtotime($ara_tarih_son . "+1 days"));

	$ara_tarih_ilk = str_replace("-", "", $ara_tarih_ilk);
	$ara_tarih_son = str_replace("-", "", $ara_tarih_son);

	// site üzerinde ‘ karakteri var, escape stringten kurtuluyor. mysql e de ' karakteri olarak kayıtlı.
	// O sebepten bu dönüşümü yapıp escape string ile doğruluyorum.

	// Gelen yazıları string -> array explode yapmamız gerek, foreach ile döndürebilmek için.
	if($ara_kelime_gecen != NULL){
		$ara_kelime_gecen = explode(" ",$ara_kelime_gecen);
	}
	if($ara_kelime_daginik != NULL){
		$ara_kelime_daginik = explode(" ",$ara_kelime_daginik);
	}
	if($ara_kelime_gecmeyen != NULL){
		$ara_kelime_gecmeyen = explode(" ",$ara_kelime_gecmeyen);
	}
	$ara_sql["query"] = "SELECT * FROM wp_comments WHERE comment_approved= %d ";
	$ara_sql["variables"] = array("1");
	$ara_onyazi = "<br> Arama sonucu ";
	if($ara_yazar_isim != NULL){
		$ara_sql["query"].= " AND comment_author = %s ";
		array_push($ara_sql["variables"], $ara_yazar_isim);
		$ara_onyazi .= "'" . $ara_yazar_isim . "' üyesine ait olan ";
	}

	if($ara_tarih_ilk != NULL && $ara_tarih_son != NULL){
		$ara_sql["query"].= " AND comment_date >= %d AND comment_date <= %d ";
		array_push($ara_sql["variables"], $ara_tarih_ilk, $ara_tarih_son);
		$ara_onyazi .= "'" . $ara_tarih_ilk1 . "' ile '" . $ara_tarih_son1 . "' tarihleri arasında ";
	}

	if($ara_kelime_gecen != NULL){
			$ara_onyazi .= "' ";
		foreach ($ara_kelime_gecen as $key ){
			$ara_sql["query"].= " AND comment_content LIKE '%s' ";
			array_push($ara_sql["variables"],"%".$key."%");
			$ara_onyazi .= " " . $key ;
		}
			$ara_onyazi .= "' ifadesi geçen";
	}

	if($ara_kelime_daginik != NULL){
		foreach ($ara_kelime_daginik as $key ){
			$ara_sql["query"].= " OR comment_content LIKE '%s' ";
			array_push($ara_sql["variables"],"%".$key."%");
			$ara_onyazi .= $key . ", ";
		}
		$ara_onyazi .= " kelime grubuna sahip olan";
	}

	if($ara_kelime_sirali != NULL){
		$ara_sql["query"].= " AND comment_content LIKE '%s' ";
		array_push($ara_sql["variables"],"%".$ara_kelime_sirali."%");
		$ara_onyazi .= "'" . $ara_kelime_sirali . "' kelimeleri sıralı olan ";
	}

	if($ara_kelime_gecmeyen != NULL){
		foreach ($ara_kelime_gecmeyen as $key ){
			$ara_sql["query"].= "AND comment_content NOT LIKE '%s' ";
			array_push($ara_sql["variables"],"%".$key."%");
			$ara_onyazi .= "'" . $key ."' ,";
		}
		$ara_onyazi .= " kelimelerini bulundurmayan";
	}

	if($ara_tarih_sirala == "artan"){
		$ara_sql["query"].= " order by comment_date asc";
	}else{
		$ara_sql["query"].= " order by comment_date desc";
	}

//	döndürülen yazı ile stringin komutu aynı olduğundan, içinden / karakterlerini silmek caizdir.
	$ara_onyazi = str_replace("\\","",$ara_onyazi);
	return $ara_sql;
}

function sql_sonuc_getir($sql_sorgu){
	global $wpdb;
	//return $wpdb->query($wpdb->prepare($sql_sorgu ));
	return $wpdb->get_results(
		$wpdb->prepare($sql_sorgu["query"],$sql_sorgu["variables"] ));
}
// BU FONKSIYONUN ICINDE TUTMALARA DA BAKIYOR, EGER SAYFA ISTENEN TUTMA SAYISINA SAHIP DEGILSE EKRANA BASILMIYOR.
function sonucu_ekrana_bas_yazi($sonuc){
	global $wpdb;
	global $ara_tutma;
	foreach ($sonuc as $key ){
		$page = $key->ID;
		$tutma_sql = "SELECT COUNT(value) FROM wp_wti_like_post WHERE post_id = $page";
		$tutma_sayisi = $wpdb->get_var($tutma_sql);

		if($tutma_sayisi >= $ara_tutma){
			$page_data = get_page($page);
			$bas_title = $page_data->post_title;
			$bas_time = $page_data->post_date;
			$bas_link = get_post_permalink( $page );
			echo "<a href='" . $bas_link . "' > <h2>" . $bas_title . "</h2> </a>";
			echo $bas_time;
			echo "<br><br>";
		}
	}
}

function sonucu_ekrana_bas_yorum($sonuc){
	global $wpdb;
	foreach ($sonuc as $key ) {
		$page = $key->comment_post_ID;
		$comment_date = $key->comment_date;
		$page_data = get_page($page);
		$bas_title = $page_data->post_title;
		$bas_link = get_post_permalink( $page );
		$comment_id = $key->comment_ID;

		echo "<a href='" . $bas_link . "#comment-" . $comment_id . "'> <h2>" . $bas_title . "</h2> </a>";
		echo $comment_date;
		echo "<br><br>";
		
	}
}

// Bu hook, tarayıcımızdan 22dakika.org/ayrintili-ara dedigimizde, fonksiyonun calismasını saglıyor.
    add_action('template_redirect', 'oy_custom_page_template_redirect');
    function oy_custom_page_template_redirect() {
        global $wp_query;

        if ($wp_query->query_vars['name'] == 'ayrintili-ara') {
            $wp_query->is_404 = false;
            include(ABSPATH . 'wp-content/plugins/oy-detailed-search/oy-template.php');
            exit;
        }
    }

    // bu hook, Arama sayfasındaki tarihleri seçmek için kullandığımız scripti yüklüyor.
    add_action ('init','date_picker_icin_gerekli');
    function date_picker_icin_gerekli() {
    	wp_enqueue_script('jquery-ui-datepicker');
    	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    	wp_enqueue_script('date-picker',get_template_directory_uri() .'/js/date-picker.js');
    }
?>