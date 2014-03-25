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

function sql_sorgusu_uret_yazi($ara_yazar_id, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali,  $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala, $ara_tutma_gelen){
	global $ara_tutma ;
	$ara_tutma_gelen != NULL ? $ara_tutma = $ara_tutma_gelen : $ara_tutma = 0 ;

	// tarihlerin database aramasına uygun formda kalması gerek
	$ara_tarih_ilk = str_replace("-", "", $ara_tarih_ilk);
	$ara_tarih_son = str_replace("-", "", $ara_tarih_son);

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

	$ara_sql = "SELECT * FROM wp_posts WHERE 1=1 ";
	if($ara_yazar_id != NULL){
		$ara_sql .= " AND post_author = $ara_yazar_id ";
	}

	if($ara_tarih_ilk != NULL && $ara_tarih_son != NULL){
		$ara_sql .= " AND post_date >= $ara_tarih_ilk AND post_date <= $ara_tarih_son ";
	}

	if($ara_kelime_gecen != NULL){
		foreach ($ara_kelime_gecen as $key ){
			$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
		}
	}

	if($ara_kelime_daginik != NULL){
		foreach ($ara_kelime_daginik as $key ){
			$ara_sql .= " OR post_excerpt LIKE '%$key%' ";
		}
	}

	if($ara_kelime_sirali != NULL){
		$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
	}

	if($ara_kelime_gecmeyen != NULL){
		foreach ($ara_kelime_gecmeyen as $key ){
			$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
		}
	}

	if($ara_tarih_sirala == "artan"){
		$ara_sql .= "order by post_date asc";
	}else{
		$ara_sql .= "order by post_date desc";
	}
	var_dump($ara_sql);
	return $ara_sql;

}

function sql_sorgusu_uret_yorum($ara_yazar_isim, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali, $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala){

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
	$ara_sql = "SELECT * FROM wp_comments WHERE 1=1 ";


	if($ara_yazar_isim != NULL){
		$ara_sql .= " AND comment_author = '$ara_yazar_isim' ";

	}

	if($ara_tarih_ilk != NULL && $ara_tarih_son != NULL){

		$ara_sql .= " AND comment_date >= $ara_tarih_ilk AND comment_date <= $ara_tarih_son ";
	}

	if($ara_kelime_gecen != NULL){
		foreach ($ara_kelime_gecen as $key ){
			$ara_sql .= " AND comment_content LIKE '%$key%' ";
		}
	}

	if($ara_kelime_daginik != NULL){
		foreach ($ara_kelime_daginik as $key ){
			$ara_sql .= " OR comment_content LIKE '%$key%' ";
		}
	}

	if($ara_kelime_sirali != NULL){
		$ara_sql .= " AND comment_content LIKE '%$ara_kelime_sirali%' ";
	}

	if($ara_kelime_gecmeyen != NULL){
		foreach ($ara_kelime_gecmeyen as $key ){
			$ara_sql .= "AND comment_content NOT LIKE '%$key%' ";
		}
	}

	if($ara_tarih_sirala == "artan"){
		$ara_sql .= " order by comment_date asc";
	}else{
		$ara_sql .= " order by comment_date desc";
	}
	return $ara_sql;	
}

function sql_sonuc_getir($sql_sorgu){
	global $wpdb;
	return $wpdb->get_results($sql_sorgu);
}
// BU FONKSIYONUN ICINDE TUTMALARA DA BAKIYOR, EGER SAYFA ISTENEN TUTMA SAYISINA SAHIP DEGILSE EKRANA BASILMIYOR.
function sonucu_ekrana_bas_yazi($sonuc){
	global $wpdb;
	global $ara_tutma;
	foreach ($sonuc as $key ) {
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
		$page_data = get_page($page);
		$bas_title = $page_data->post_title;
		$bas_time = $page_data->post_date;
		$bas_link = get_post_permalink( $page );

		echo "<a href='" . $bas_link . "' > <h2>" . $bas_title . "</h2> </a>";
		echo $bas_time;
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

