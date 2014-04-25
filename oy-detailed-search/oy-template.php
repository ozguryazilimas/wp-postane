 <?php
get_header();
	global $ara_onyazi;
	// Sayfa, fonksiyonlarını plugins/oy-detailed-search/oy-detailed-search.php  sayfasından çekiyor.  
	// Plugini aktif tutmak farz.

	echo "<div class='leftpane person-page'>";
	echo " <h1> Ayrıntılı Arama </h1> ";

	echo "<form name='input' id='input' action='/ayrintili-ara/' method='post' onsubmit='return validateSearch()'>";

	echo " <input type='username' name='yazar_isim' /> İsimli yazara ait olan <br /> <br />";
	echo ' <input type="text" class="custom_date" name="tarih_ilk" value=""/> ile 
			<input type="text" class="custom_date" name="tarih_son" value=""/> tarihleri arasında yazılan<br> <br>';
	echo " İçinde <input type='text' name='kelime_gecen' /> kelimelerinin tümünün geçtiği (yanyana olması şart değil)<br> <br>";
	echo " İçinde <input type='text' name='kelime_sirali' /> kelimeleri yanyana geçen<br> <br>";
	echo " İçinde <input type='text' name='kelime_daginik' /> kelimelerindan en az birinin geçtiği<br> <br>";
	echo " İçinde <input type='text' name='kelime_gecmeyen' /> kelimeleri geçmeyen<br> <br>";
	echo " En az <input type='text' name='tutma' pattern='\d*' /> sayıda kişinin tuttuğu <br> <br> yazıları ara.<br><br>";
	echo " Bu arama <br><br> <input type='radio' style='margin-left:40px;' name='arama_turu' value='arama_yazi' checked> yazılarda <br>
			<input type='radio' name='arama_turu'  style='margin-left:40px;'value='arama_yorum'> yorumlarda <br><br>yapılsın. <br><br> ";
	echo " Sonuçlar tarihe göre <br><br> <input type='radio' style='margin-left:40px;'name='tarih_sirala' value='azalan' checked> yeniden eskiye  <br>
			 <input type='radio' name='tarih_sirala' style='margin-left:40px;'value='artan'> eskiden yeniye<br><br>sırala.<br><br>";
	echo "<input type='submit' value='Ara'>";

	echo "</form>";

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

	//yazar isminden id elde edildi 
	if( $_POST["yazar_isim"] != NULL ){
		$yolla = $_POST["yazar_isim"];
		$ara_yazar_id = yazar_id_return_et($yolla);
	}

	// preg_match('/[^A-Za-z0-9_ -]/', $str)

	if($ara_ozellik == "arama_yazi"){
		//sql e sorgu gönderildi.
		$sql_sorgu = sql_sorgusu_uret_yazi($ara_yazar_id, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali, $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala, $ara_tutmalar, $ara_yazar_isim);
		
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

get_sidebar();
get_footer();

?>