 <?php
get_header();

	// Sayfa, fonksiyonlarını plugins/oy-detailed-search/oy-detailed-search.php  sayfasından çekiyor.  
	// Plugini aktif tutmak farz.

	echo "<div class='leftpane person-page'>";
	echo " <h1> Ayrıntılı Arama </h1> ";

	echo "<form name='input' id='input' action='/ayrintili-ara/' method='post' onsubmit='return validateSearch()'>";

	echo " Hangi yazar :  <input type='username' name='yazar_isim' /><br /> <br />";
	echo 'Tarihleri arasında yazılan : <input type="text" class="custom_date" name="tarih_ilk" value=""/>
			<input type="text" class="custom_date" name="tarih_son" value=""/> <br> <br>';
	echo " İçinde mutlaka olsun kelimeleri : <input type='text' name='kelime_gecen' /> <br> <br>";
	echo " İçinde mutlaka olsun, yanyana olsun kelimeleri : <input type='text' name='kelime_sirali' /> <br> <br>";
	echo " Dağınık olarak içinde bulunsun kelimeleri : <input type='text' name='kelime_daginik' /> <br> <br>";
	echo " İçinde geçmesin kelimeleri: <input type='text' name='kelime_gecmeyen' /> <br> <br>";
	echo " En az şu kadar kişi tutmuş olsun : <input type='text' name='tutma' pattern='\d*' /> <br> <br>";
	echo " Tarihe göre sıralama : <br> <input type='radio' name='tarih_sirala' value='artan'> Artan <br>
			<input type='radio' name='tarih_sirala' value='azalan' checked> Azalan <br> <br>";
	echo " Neye göre arayayım : <br> <input type='radio' name='arama_turu' value='arama_yazi' checked> Yazıya göre <br>
			<input type='radio' name='arama_turu' value='arama_yorum'> Yoruma göre <br> <br>";
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

		echo "<br>";
		
		echo sonucu_ekrana_bas_yazi($sonuc);

	}

	if($ara_ozellik == "arama_yorum"){
		// Yorumlarda tutma özelliği yok.
		$sql_sorgu = sql_sorgusu_uret_yorum($ara_yazar_isim, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali, $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala);

		$sonuc = sql_sonuc_getir($sql_sorgu);
	
		echo "<br>";

		echo sonucu_ekrana_bas_yorum($sonuc);

	}

	echo "</div>";

get_sidebar();
get_footer();

?>