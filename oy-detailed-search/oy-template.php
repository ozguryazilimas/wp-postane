 <?php
get_header();
get_sidebar();

	// Sayfa, fonksiyonlarını plugins/oy-detailed-search/oy-detailed-search.php  sayfasından çekiyor.  
	// Plugini aktif tutmak farz.

	echo "<div class='leftpane person-page'>";

	echo "<form name='input' action='/ayrintili-ara/' method='post'>";

	echo " <h1> Ayrıntılı Arama </h1> ";

	echo " Hangi yazar :  <input type='username' name='yazar_isim' /><br /> <br />";
	echo " Tarihleri arasında yazılan : <input type='date' name='tarih_ilk' /> 
			<input type='date' name='tarih_son' /><br /> <br />";
	echo " İçinde mutlaka olsun kelimeleri : <input type='text' name='kelime_gecen' /> <br> <br>";
	echo " İçinde mutlaka olsun, yanyana olsun kelimeleri : <input type='text' name='kelime_sirali' /> <br> <br>";
	echo " Dağınık olarak içinde bulunsun kelimeleri : <input type='text' name='kelime_daginik' /> <br> <br>";
	echo " İçinde geçmesin kelimeleri: <input type='text' name='kelime_gecmeyen' /> <br> <br>";
	echo " Tarihe göre sıralama : <br> <input type='radio' name='tarih_sirala' value='artan'> Artan <br>
			<input type='radio' name='tarih' value='azalan'> Azalan <br> <br>";


	echo "<input type='submit' value='Ara'>";
	echo "</form>";

// POST Verileri değişkenlere atandı.
	$ara_yazar_isim = $_POST["username"];
	$ara_tarih_ilk = $_POST["tarih_ilk"];
	$ara_tarih_son = $_POST["tarih_son"];
	$ara_kelime_gecen = $_POST["kelime_gecen"];
	$ara_kelime_sirali = $_POST["kelime_sirali"];
	$ara_kelime_daginik = $_POST["kelime_daginik"];
	$ara_kelime_gecmeyen = $_POST["kelime_gecmeyen"];
	$ara_tarih_sirala = $_POST["tarih_sirala"];

// BU noktadan itibaren, güvenlik önlemi olarak preg_match.

	//yazar isminden id elde edildi 
	if( $_POST["yazar_isim"] != NULL ){
		$yolla = $_POST["yazar_isim"];
		$ara_yazar_id = yazar_id_return_et($yolla);
	}

	// preg_match('/[^A-Za-z0-9_ -]/', $str)


	//sql e sorgu gönderildi.
	$sql_sorgu = sql_sorgusu_uret($ara_yazar_id, $ara_tarih_ilk, $ara_tarih_son, $ara_kelime_gecen, $ara_kelime_sirali, $ara_kelime_daginik, $ara_kelime_gecmeyen, $ara_tarih_sirala);

	
	$sonuc = sql_sonuc_getir($sql_sorgu);
	
	echo "<br>";
	echo sonucu_ekrana_bas($sonuc);

	echo "</div>";



get_footer();

?>