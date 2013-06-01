<?php
/*
 * Bismillahirrahmanirrahim
 * @jQuery Popup
 * @since 1.3.1
*/                                  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }
include_once 'lib/includes/common.php';

add_action('init', 'milatInit');
add_action('wp', 'milatPopup');
?>