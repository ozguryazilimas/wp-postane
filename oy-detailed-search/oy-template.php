<?php get_header();
	wp_register_style( 'oy_template_css', WP_PLUGIN_URL.'/oy-detailed-search/oy-css.php' );
	wp_enqueue_style('oy_template_css');
	wp_enqueue_script('oy-js', WP_PLUGIN_URL.'/oy-detailed-search/oy-js.js');
	global $ara_onyazi;
	// Sayfa, fonksiyonlarını plugins/oy-detailed-search/oy-detailed-search.php  sayfasından çekiyor.  
	// Plugini aktif tutmak farz.
	echo "<div id='oy-unique' class='leftpane person-page'>";
	echo '<div id="oy-hide-button" class="oy-rotate"><img src="'.site_url().'/wp-content/plugins/oy-detailed-search/arrow.png"/></div>';
	echo '<div id="oy-hide-tip">Arama kutusunu açmak için tıklayın.</div>';
	echo '<div id="oy-arama-container">
			<h1 class="oy-ayrinti-text"> Ayrıntılı Arama </h1>
			<div class="oy-arama-form">
				<form name="input" action="?name=ayrintili-ara" id="input" method="post" onsubmit="return validateSearch_real()">
					<div class="oy-arama-major-field">
						<h2>Kelimelerinin...</h2>

						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>hepsinin geçtiği:</p></div> 
							<div class="oy-arama-box"> 
								<input class="oy-arama-input-big" name="kelime_gecen" type="text"/>
							</div>
						</div>

						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>yan yana geçtiği:</p></div> 
							<div class="oy-arama-box"> 
								<input class="oy-arama-input-big" name="kelime_sirali" type="text"/>
							</div>
						</div>

						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>en az birinin geçtiği:</p></div> 
							<div class="oy-arama-box"> 
								<input class="oy-arama-input-big" name="kelime_daginik" type="text"/>
							</div>
						</div>

						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>geçmediği:</p></div> 
							<div class="oy-arama-box"> 
								<input class="oy-arama-input-big" name="kelime_gecmeyen" type="text"/>
							</div>
						</div>
					</div>
					<div class="oy-divider"></div>
					<div class="oy-arama-major-field">
						<h2>Sonuçların...</h2>
						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>yazarı:</p></div> 
							<div class="oy-arama-box"> 
								<input class="oy-arama-input-mid" name="yazar_isim" type="username"/>
							</div>
						</div>

						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>yayınlanma tarih aralığı:</p></div> 
							<div class="oy-arama-box"> 
								<input id="txtFromDate" class="custom_date oy-arama-input-small" name="tarih_ilk" value="" type="text"/> - <input id="txtToDate" class="custom_date oy-arama-input-small" name="tarih_son" value="" type="text"/>
							</div>
						</div>

						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>minimum tutulma sayısı:</p></div> 
							<div class="oy-arama-box"> 
								<input class="oy-arama-input-small-small" name="tutma" pattern="\d*" type="number" value="0">
							</div>
						</div>
						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>arama yeri:</p></div> 
							<div class="oy-arama-box"> 
								<select class="oy-arama-select" id="oy-arama-tur-js-icin" name="arama_turu">
									<option value="arama_yazi">Yazılar</option>
									<option value="arama_yorum">Yorumlar</option>
								</select>
							</div>
						</div>

						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>sırası:</p></div> 
							<div class="oy-arama-box"> 
								<select class="oy-arama-select" name="tarih_sirala">
									<option value="azalan">Yeniden eskiye</option>
									<option value="artan">Eskiden yeniye</option>
								</select>
							</div>
						</div>
					</div>
					<div class="oy-divider"></div>
					<div class="oy-arama-major-field" id="oy-etiket-just-for-yazi">
						<h2>Etiketlerinin...</h2>
						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>en az birinin bulunduğu:</p></div> 
							<div class="oy-arama-box"> 
								<input placeholder="(virgülle ayrılmış)" class="oy-arama-big" type="text" name="etiketler"/>
							</div>
						</div>
						<div class="oy-arama-input-field">
							<div class="oy-arama-text"><p>hepsinin bulunduğu:</p></div> 
							<div class="oy-arama-box"> 
								<input placeholder="(virgülle ayrılmış)" class="oy-arama-big" type="text" name="etiketler_hepsi"/>
							</div>
						</div>
					</div>
					<div class="oy-divider-borderless"></div>
					<div class="oy-arama-major-field">
						<div class="oy-arama-input-field">
							<input value="Ara" type="submit">
						</div>
					</div>
				</form>

			</div>
		</div>';


// POST Verileri değişkenlere atandı.
	$ara_yazar_isim = $_POST["yazar_isim"];
	$ara_tarih_ilk = $_POST["tarih_ilk"];
	$ara_tarih_son = $_POST["tarih_son"];
	$ara_kelime_gecen = $_POST["kelime_gecen"];
	$ara_kelime_sirali = $_POST["kelime_sirali"];
	$ara_kelime_daginik = $_POST["kelime_daginik"];
	$ara_kelime_gecmeyen = $_POST["kelime_gecmeyen"];
	$ara_tarih_sirala = $_POST["tarih_sirala"];
	$ara_tutmalar = $_POST["tutma"];
	$ara_ozellik = $_POST["arama_turu"];
	$ara_etiketler = $_POST["etiketler"];
	$ara_etiketler_hepsi=$_POST["etiketler_hepsi"];
	
	//yazar isminden id elde edildi 
	if( $_POST["yazar_isim"] != NULL ){
		$yolla = $_POST["yazar_isim"];
		$ara_yazar_id = yazar_id_return_et($yolla);
	}

	// preg_match('/[^A-Za-z0-9_ -]/', $str)
	echo '<div class="oy-arama-sonuc-container">';
	if($ara_ozellik == "arama_yazi"){
		//sql e sorgu gönderildi.
		$sql_sorgu = sql_sorgusu_uret_yazi($ara_yazar_id, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali, $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala, $ara_tutmalar, $ara_yazar_isim,$ara_etiketler,$ara_etiketler_hepsi);
		
		$sonuc = sql_sonuc_getir($sql_sorgu);

		echo $ara_onyazi ." " . count($sonuc) . " adet yazı bulunmuştur. <br><br> " ;
		echo sonucu_ekrana_bas_yazi($sonuc);

	}

	if($ara_ozellik == "arama_yorum"){
		// Yorumlarda tutma özelliği yok.
		$sql_sorgu = sql_sorgusu_uret_yorum($ara_yazar_isim, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali, $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala);

		$sonuc = sql_sonuc_getir($sql_sorgu);

		echo $ara_onyazi ." " . count($sonuc) . " adet yorum bulunmuştur. <br><br> " ;
		echo sonucu_ekrana_bas_yorum($sonuc);

	}
	echo "</div>";
	echo "</div>";

get_sidebar();
get_footer();
?>
