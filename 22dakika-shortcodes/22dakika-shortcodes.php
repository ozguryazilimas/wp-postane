<?php
/*
Plugin Name: Shortcodes for 22dakika
Plugin URI: https://ozguryazilim.com.tr
Description: Shortcode plugin for 22dakika.org
Version: 1.5.1
Author: Baskın Burak Şenbaşlar, Onur Küçük
Author URI: https://www.ozguryazilim.com.tr
License: GPL2
*/

function dizi_oyuncu_shortcode_button() {
  if (wp_script_is('quicktags')) {
    ?>
    <script type="text/javascript">
      QTags.addButton('dizi_qtag','dizi','[dizi]','[/dizi]');
      QTags.addButton('oyuncu_qtag','oyuncu','[oyuncu]','[/oyuncu]');
    </script>
    <?php
  }
}
add_action('admin_print_footer_scripts', 'dizi_oyuncu_shortcode_button');

function dizi_shortcode_replace($content) {
  $dizi_list = json_decode(file_get_contents(plugin_dir_path(__FILE__) . "/dizi_listesi.json"), true);
  $start_pos = strpos($content, "[dizi]");
  $end_pos = strpos($content, "[/dizi]");

  while ($start_pos !== false && $end_pos !== false) {
    $text_start = $start_pos + 6;
    $text_len = $end_pos - $text_start;

    $text = substr($content, $text_start, $text_len);
    $index_text = yirmiiki_shortcode_json_key($text);

    if (isset($dizi_list[$index_text])) {
      $content = substr($content,0,$start_pos) . "<a href='" . $dizi_list[$index_text]['link'] . "'>" . $text."</a>" . substr($content, $end_pos + 7);
    } else {
      $content = substr($content,0,$start_pos) . "<a href='" . get_site_url() . "/peyton'>" . $text . "</a>" . substr($content, $end_pos + 7);
    }

    $start_pos = strpos($content,"[dizi]", $start_pos + 1);
    $end_pos = strpos($content,"[/dizi]", $start_pos + 1);
  }
  return $content;
}
add_filter('the_content', 'dizi_shortcode_replace');

function oyuncu_shortcode_replace($content) {
  $oyuncu_list = json_decode(file_get_contents(plugin_dir_path(__FILE__) . "/oyuncu_listesi.json"), true);
  $start_pos = strpos($content,"[oyuncu]");
  $end_pos = strpos($content,"[/oyuncu]");

  while ($start_pos !== false && $end_pos !== false) {
    $text_start = $start_pos + 8;
    $text_len = $end_pos - $text_start;

    $text = substr($content, $text_start, $text_len);
    $index_text = yirmiiki_shortcode_json_key($text);

    if (isset($oyuncu_list[$index_text])) {
      $content = substr($content,0,$start_pos) . "<a href='".$oyuncu_list[$index_text]['link']."'>".$text."</a>" . substr($content,$end_pos + 9);
    } else {
      $content = substr($content,0,$start_pos) . "<a href='".get_site_url()."'>".$text."</a>" . substr($content,$end_pos + 9);
    }
    $start_pos = strpos($content,"[oyuncu]", $start_pos + 1);
    $end_pos = strpos($content,"[/oyuncu]", $start_pos + 1);
  }
  return $content;
}
add_filter('the_content', 'oyuncu_shortcode_replace');

add_action('init', 'yirmiiki_shortcode_button_init');
function yirmiiki_shortcode_button_init() {
  if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
    return;
  }
  add_filter("mce_external_plugins", "yirmiiki_register_tinymce_plugin");
  add_filter('mce_buttons', 'yirmiiki_add_tinymce_button');
}

function yirmiiki_register_tinymce_plugin($plugin_array) {
  $plugin_array['dizi_button'] = plugin_dir_url(__FILE__) . 'dizi.js';
  $plugin_array['oyuncu_button'] = plugin_dir_url(__FILE__) . 'oyuncu.js';

  return $plugin_array;
}

function yirmiiki_add_tinymce_button($buttons) {
  $buttons[] = "dizi_button";
  $buttons[] = "oyuncu_button";

  return $buttons;
}

function yirmiiki_shortcode_json_key($base_str_raw) {
  $base_str = preg_replace("/\s+/u", " ", trim($base_str_raw));
  $replace_from = array(' ', "’", "'", "’", '&#8216;', '&#8217;', '&amp;', '&#038;', '&');
  $replace_to   = array('_', '_', '_', '_',       '_',       '_', 'and',   'and',    'and');

  $lower_str = strtolower($base_str);
  $search_key = str_replace($replace_from, $replace_to, $lower_str);

  return $search_key;
}

/*
function jsonWithoutUnicodeSequences($rawdata) {
  return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($rawdata));
}
*/

?>
