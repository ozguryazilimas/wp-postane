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

function sql_sorgusu_uret($ara_yazar_id, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali,  $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala){
	global $wpdb;

	// $ara_kelime_gecmeyen değişkeninin boş gelme ihtimaline göre:
	if($ara_kelime_gecmeyen == NULL){
		$ara_kelime_gecmeyen = "asd123dsa123sadaqwe"; // -> Random, hiçbir postun içinde geçemez.
	}

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

	if($ara_yazar_id != NULL){
		//yazar ismi verildiyse
		if($ara_tarih_ilk != NULL && $ara_tarih_son != NULL){
			//yazar+ Tarih değerleri verildiyse
			if($ara_kelime_gecen != NULL){
				// yazar+ tarih + Mutlaka kelimeleri varsa
				if($ara_kelime_sirali != NULL){
					// yazar+ tarih + mutlaka+ Sıralı geçen kelime varsa
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih + mutlaka+ sıralı+ Dağınık geçen kelime varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key ){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih + mutlaka+ sıralı+ Dağınık geçen kelime yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}else{
					// yazar+ tarih + mutlaka+ Sıralı geçen kelime yoksa
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih + mutlaka+ sıralı- Dağınık geçen kelime varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih + mutlaka+ sıralı- Dağınık geçen kelime yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}
			}else{
				// yazar+ tarih+ Mutlaka kelimeleri yoksa
				if($ara_kelime_sirali != NULL){
					//yazar+ tarih+ mutlaka- Sıralı geçen kelime varsa 
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih+ mutlaka- Sıralı+ Dağınık geçen kelime varsa 
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih+ mutlaka- Sıralı+ Dağınık geçen kelime yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}else{
					//yazar+ tarih+ mutlaka- Sıralı geçen kelime yoksa
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih+ mutlaka- Sıralı- Dağınık geçen kelime varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih+ mutlaka- Sıralı- Dağınık geçen kelime yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									AND post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}
			}
		}else{
			//yazar+ Tarih değerleri verilmediyse
			if($ara_kelime_gecen != NULL){
				//yazar+ tarih- Mutlaka kelimeleri varsa
				if($ara_kelime_sirali != NULL){
					//yazar+ tarih- mutlaka+ Sıralı kelimeleri varsa
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih- mutlaka+ Sıralı+ Dağınık kelimeleri varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih- mutlaka+ Sıralı+ Dağınık kelimeleri yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}else{
					//yazar+ tarih- mutlaka+ Sıralı kelimeleri yoksa
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih- mutlaka+ Sıralı- Dağınık kelimeleri varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih- mutlaka+ Sıralı- Dağınık kelimeleri yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}
			}else{
				//yazar+ tarih- Mutlaka kelimeleri yoksa
				if($ara_kelime_sirali != NULL){
					//yazar+ tarih- Mutlaka- Sıralı kelimeleri varsa
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih- Mutlaka- Sıralı+ Dağınık kelimeleri varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih- Mutlaka- Sıralı+ Dağınık kelimeleri yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}else{
					//yazar+ tarih- Mutlaka- Sıralı kelimeleri yoksa
					if($ara_kelime_daginik != NULL){
						//yazar+ tarih- Mutlaka- Sıralı- Dağınık kelimeleri varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}

									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar+ tarih- Mutlaka- Sıralı- Dağınık kelimeleri yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE post_author = $ara_yazar_id 
									"; 
					}
				}
					
			}
		}

	}else{
		// yazar ismi verilmediyse
		if($ara_tarih_ilk != NULL && $ara_tarih_son != NULL){
			//yazar- Tarih değerleri verildiyse
			if($ara_kelime_gecen){
				//yazar- Tarih+ Mutlaka kelimesi varsa
				if($ara_kelime_sirali != NULL){
					//yazar- Tarih+ Mutlaka+ Sıralı geçen kelime varsa
					if($ara_kelime_daginik != NULL){
						//yazar- Tarih+ Mutlaka+ Sıralı+ Dağınık geçen kelime varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar- Tarih+ Mutlaka+ Sıralı+ Dağınık geçen kelime yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									 post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}else{
					//yazar- Tarih+ Mutlaka+ Sıralı geçen kelime yoksa
					if($ara_kelime_daginik != NULL){
						//yazar- tarih+ mutlaka+ Sıralı- Dağınık geçen kelime varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar- tarih+ mutlaka+ Sıralı- Dağınık geçen kelime yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}
			}else{
				//yazar- Tarih+ Mutlaka kelimesi yoksa
				if($ara_kelime_sirali != NULL){
					//yazar- Tarih+ Mutlaka- Sıralı geçen kelimesi varsa
					if($ara_kelime_daginik != NULL){
						//yazar- Tarih+ Mutlaka- Sıralı+ Dağınık geçen kelimesi varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar- Tarih+ Mutlaka- Sıralı+ Dağınık geçen kelimesi yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									 post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}else{
					//yazar- Tarih+ Mutlaka- Sıralı geçen kelimesi yoksa
					if($ara_kelime_daginik != NULL){
						//yazar- Tarih+ Mutlaka- Sıralı- Dağınık geçen kelimesi varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									 post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar- Tarih+ Mutlaka- Sıralı- Dağınık geçen kelimesi yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE 
									 post_date >= '$ara_tarih_ilk'
									AND post_date <= '$ara_tarih_son' 
									"; 
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= " AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}
			}
		}else{
			//yazar- Tarih değerleri verilmediyse
			if($ara_kelime_gecen != NULL){
				//yazar- Tarih- Mutlaka kelimesi varsa
				if($ara_kelime_sirali != NULL){
					//yazar- Tarih- Mutlaka+ Sıralı geçen kelimesi varsa
					if($ara_kelime_daginik != NULL){
						//yazar- Tarih- Mutlaka+ Sıralı+ Dağınık geçen kelimesi varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 1=1
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}

					}else{
						//yazar- Tarih- Mutlaka+ Sıralı+ Dağınık geçen kelimesi yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE 1=1
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 

						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key ){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}

					}
				}else{
					//yazar- Tarih- Mutlaka+ Sıralı geçen kelimesi yoksa
					if($ara_kelime_daginik != NULL){
						//yazar- Tarih- Mutlaka+ Sıralı- Dağınık geçen kelimesi varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 1=1
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
									foreach ($ara_kelime_gecmeyen as $key ){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar- Tarih- Mutlaka+ Sıralı- Dağınık geçen kelimesi yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE 1=1
									"; 
									foreach ($ara_kelime_gecen as $key ){
										$ara_sql .= " AND post_excerpt LIKE '%$key%' ";
									} 
									 
									foreach ($ara_kelime_gecmeyen as $key ){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}
			}else{
				//yazar- Tarih- Mutlaka kelimesi yoksa
				if($ara_kelime_sirali != NULL){
					//yazar- Tarih- Mutlaka- Sıralı geçen kelimesi varsa
					if($ara_kelime_daginik != NULL){
						//yazar- Tarih- Mutlaka- Sıralı+ Dağınık geçen kelimesi varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 1=1  
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
						$ara_sql .= " AND post_excerpt LIKE '%$ara_kelime_sirali%' ";
									foreach ($ara_kelime_gecmeyen as $key ){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar- Tarih- Mutlaka- Sıralı+ Dağınık geçen kelimesi yoksa
						$ara_sql = "SELECT * FROM wp_posts WHERE 1=1 
									"; 
									
									foreach ($ara_kelime_gecmeyen as $key ){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}
				}else{
					//yazar- Tarih- Mutlaka- Sıralı geçen kelimesi yoksa
					if($ara_kelime_daginik != NULL){
						//yazar- Tarih- Mutlaka- Sıralı- Dağınık geçen kelimesi varsa
						$ara_sql = "SELECT * FROM wp_posts WHERE 1=1 
									"; 
									foreach ($ara_kelime_daginik as $key ){
										$ara_sql .= " OR post_excerpt LIKE '%key%' ";
									}
									 
									foreach ($ara_kelime_gecmeyen as $key ){
										$ara_sql .= "AND post_excerpt NOT LIKE '%$key%' ";
									}
					}else{
						//yazar- Tarih- Mutlaka- Sıralı- Dağınık geçen kelimesi yoksa
						$ara_sql = "";
					}
				}
			}
		}
	}
	if($ara_tarih_sirala == "artan"){
		$ara_sql .= "order by post_date asc";
	}else{
		$ara_sql .= "order by post_date desc";
	}

	return $ara_sql;
}

function sql_sonuc_getir($sql_sorgu){
	global $wpdb;
	return $wpdb->get_results($sql_sorgu);
}
function sonucu_ekrana_bas($sonuc){
	foreach ($sonuc as $key ) {
		$page = $key->ID;
		$page_data = get_page($page);
		$bas_content = $page_data->post_content;
		$bas_title = $page_data->post_title;
		$bas_time = $page_data->post_date;
		$bas_link = get_post_permalink( $page );
		echo "<a href='" . $bas_link . "' > <h2>" . $bas_title . "</h2> </a>";
		echo $bas_time;
		
	
		echo "<br><br>";

	}

}

    add_action('template_redirect', 'oy_custom_page_template_redirect');
    function oy_custom_page_template_redirect() {
        global $wp_query;

        if ($wp_query->query_vars['name'] == 'ayrintili-ara') {
            $wp_query->is_404 = false;
            include(ABSPATH . 'wp-content/plugins/oy-detailed-search/oy-template.php');
            exit;
        }
    }


	
?>