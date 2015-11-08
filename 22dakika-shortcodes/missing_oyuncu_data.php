<?php
if (php_sapi_name() != "cli") {
  die();
}

define('WP_USE_THEMES', false);
$wp_blog_path = array(
  '../../../../../wp-blog-header.php',
  '../../../../wp-blog-header.php',
  '../../../wp-blog-header.php',
  '../../wp-blog-header.php',
  '/srv/www/vhosts/22dk/wp-blog-header.php',
  '/var/www/22dakika.org/wp-blog-header.php',
  '/var/www/test.22dakika.org/wp-blog-header.php'
);

foreach($wp_blog_path as $incpath) {
  if (file_exists($incpath)) {
    require($incpath);
    # echo "including " . $incpath . "\n";
    break;
  }
}


global $wpdb;
$now_raw = new DateTime("now", new DateTimeZone('Europe/Istanbul'));
$now = $now_raw->format('Y-m-d H:i:s');
$debug = in_array('-d', $argv);
$dry_run = in_array('-n', $argv);
$force = in_array('-f', $argv);
$option_name = 'oyuncu_listesi_eksik_son_tarih';

if (!$force) {
  $date = get_option($option_name);

  if (!$date) {
    if (!$dry_run) {
      add_option($option_name, $now,'','no');
    }

    $date = $now;
  }
}

$posts_table = $wpdb->prefix . "posts";
$sql = "SELECT post_content FROM $posts_table WHERE post_content LIKE '%[oyuncu]%'";

if (!$force) {
  $sql .= " AND post_modified_gmt >= '$date'";
}

$post_list = $wpdb->get_results($sql);
$oyuncu_listesi = array();

foreach ($post_list as $post) {
  $content = $post->post_content;
  $start_pos = strpos($content, "[oyuncu]");
  $end_pos = strpos($content, "[/oyuncu]");

  while ($start_pos !== false && $end_pos !== false && $end_pos > $start_pos) {
    $oyuncu_name = substr($content,$start_pos + 8, $end_pos-$start_pos - 8);
    $oyuncu_listesi[] = $oyuncu_name;

    $start_pos = strpos($content, "[oyuncu]",$end_pos+1);
    $end_pos = strpos($content, "[/oyuncu]",$end_pos+1);
  }
}

$file_uri = plugin_dir_path(__FILE__) . '/oyuncu_listesi.json';
$json_liste = array();

if (file_exists($file_uri)) {
  $fp = fopen($file_uri, 'r') or die('could not open file for reading: ' . $file_uri);
  $json_liste = json_decode(fread($fp, filesize($file_uri)), true);
  fclose($fp);
}

$oyuncu_listesi_unique = array_unique($oyuncu_listesi);

if ($debug) {
  echo "Found " . count($oyuncu_listesi_unique) . " unique oyuncu record\n\n";
}

$missing_oyuncu = array();
foreach ($oyuncu_listesi_unique as $oyuncu) {
  $oyuncu_index = yirmiiki_shortcode_json_key($oyuncu);

  if (!isset($json_liste[$oyuncu_index])) {
    if ($debug) {
      echo $oyuncu . "\n";
    }

    array_push($missing_oyuncu, $oyuncu);
  }
}


if (count($missing_oyuncu) > 0) {
  $headers = 'From: 22dakika.org <noreply@22dakika.org>' . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=UTF-8\r\n";

  $to = 'editor@22dakika.org';
  $subject = '22dakika.org Eksik Oyuncu Listesi Raporu ';
  $content = '<br/> Merhaba,<br/><br/>' .
             'Eksik oyuncu kaydı: ' . count($missing_oyuncu) . '<br/><br/>' .
             'Denetleme zamanı:   ' . $now . '<br/><br/><br/>';


  foreach ($missing_oyuncu as $k) {
    $content .= ' ' . $k . '<br/><br/>';
  }

  if ($debug) {
    echo "\n * Eposta içeriği *" . "\n\n" . $content . "\n\n";
  }

  if (!$dry_run) {
    wp_mail($to, $subject, $content, $headers);
  }
}


if (!$force || !$dry_run) {
  update_option($option_name, $now);
}

if ($debug) {
  echo "\nTotal: " . count($oyuncu_listesi_unique) . " Missing: " . count($missing_oyuncu) . "\n\n";
}


?>
