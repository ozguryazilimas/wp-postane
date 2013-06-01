<?php
/*
 * Bismillahirrahmanirrahim
 * @jQuery Popup
 * @since 1.3.1
*/                             
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }
include_once 'lib/includes/admin.common.php'; // Common Functions


add_action('admin_init', 'milatAdminJS');
add_action('admin_menu', 'milatAyarSayfa');
add_action('admin_footer', 'milatToggleEditorJS');
?>
