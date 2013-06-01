<?php
/*
 * Bismillahirrahmanirrahim
 * @jQuery Popup
 * @since 1.3.1
*/ 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }
function milatAyarSayfa() {
	add_options_page( 'Milat jQuery Popup', 'Milat Popup', 'manage_options', 'milat-jquery-automatic-popup', 'milatAyarSayfaGetir');
}

function milatAyarSayfaGetir() {
	include 'milat-ayar.php';
}

function milatAdminJS() {

    wp_enqueue_style('thickbox');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('media-upload');

  }

function milatToggleEditorJS() {
	$js = <<<JS
	<script type="text/javascript">
function toggleVisibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
</script>
JS;

	echo $js;
}

?>