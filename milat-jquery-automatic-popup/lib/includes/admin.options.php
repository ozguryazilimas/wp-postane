<?php
/*
 * Bismillahirrahmanirrahim
 * @jQuery Popup
 * @since 1.3.1
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }
function milatAyarGuncelle() {
	if(isset($_POST['gonder'])) {
		if($_POST['gonder'] == 'milat_ayarlari_guncelle') {
			update_option('milat_baslik', $_POST['milat_baslik']);
			update_option('milat_yazi', $_POST['milat_yazi']);
			update_option('milat_tur', $_POST['milat_tur']);
			update_option('milat_tur_icerik', $_POST['milat_tur_icerik']);
			update_option('milat_youtube', $_POST['milat_youtube']);
			update_option('milat_dailymotion', $_POST['milat_dailymotion']);
			update_option('milat_vimeo', $_POST['milat_vimeo']);
			update_option('milat_swf', $_POST['milat_swf']);
			update_option('milat_resim', $_POST['milat_resim']);
			update_option('milat_resim_link', $_POST['milat_resim_link']);
			update_option('milat_anasayfa', $_POST['milat_anasayfa']);
			update_option('milat_heryer', $_POST['milat_heryer']);
			update_option('milat_buton_stil', $_POST['milat_buton_stil']);
     		update_option('milat_cookie_saat', $_POST['milat_cookie_saat']);
			update_option('milat_genislik', $_POST['milat_genislik']);
			update_option('milat_yukseklik', $_POST['milat_yukseklik']);
            update_option('milat_arkaplan', $_POST['milat_arkaplan']);
			update_option('milat_border', $_POST['milat_border']);
			update_option('milat_border_genislik', $_POST['milat_border_genislik']);
//			update_option('milat_arkakapat', $_POST['arkaplan']);
//          update_option('milat_esckapat', $_POST['esckapat']);
        }
	}
}



function milatTinyMCE() {

  add_filter('wp_default_editor', create_function('', 'return "html";'));

	if (get_bloginfo('version') < "3.2") {
	 	add_filter('wp_default_editor', create_function('', 'return "html";'));
         echo '<div id="poststuff"><div id="postdivrich" class="postarea">';
             the_editor(stripcslashes(get_option('milat_yazi')), "milat_yazi",false,false);
         echo '</div></div>';

	}
     else if (get_bloginfo('version') < "3.3") {

         echo '<div id="poststuff"><div id="postdivrich" class="postarea">';
             the_editor(stripcslashes(get_option('milat_yazi')), "milat_yazi",false,false);
         echo '</div></div>';

	}

	else {
		wp_editor(stripcslashes(get_option('milat_yazi')),'milat_yazi',
          array(
            'media_buttons' => false,
            'tinymce' => false,
            'quicktags' => true,
			'textarea_rows' => 7,
          )
        );
	}



	function _r($str, $bak) {
		if($str == $bak) {
			return 'checked="checked"';
		}
	}

}
	function milatMesajSonuc() {
		if(isset($_POST['gonder'])) {
			echo '<div class="guncel"><p align="center"><font color="#FF0000" size="4"><b>';
             _e("Updated",MILAT_MILAT);
            echo'</b></font></p></div>';
		}
	}

	function aktif($secim,$tur,$yazdir){

		if(get_option($secim)==$tur){
			echo $yazdir;
		}

	}