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
$now = new DateTime();
$now = $now->format('Y-m-d H:i:s');
$debug = in_array('-d', $argv);
$dry_run = in_array('-n', $argv);
$force = in_array('-f', $argv);


if (!$force) {
  $date = get_option("oyuncu_listesi_son_tarih");

  if (!$date) {
    if (!$dry_run) {
      add_option("oyuncu_listesi_son_tarih", $now,'','no');
    }

    $date = $now;
  }
}

$posts_table = $wpdb->prefix . "posts";
// $sql = "SELECT post_content FROM $posts_table WHERE post_modified_gmt >= '$date'";
$sql = "SELECT post_content FROM $posts_table WHERE post_content LIKE '%[oyuncu]%'";

if (!$force) {
  $sql .= " AND post_modified_gmt >= '$date'";
}

$post_list = $wpdb->get_results($sql);
$oyuncu_listesi = array();

if ($debug) {
  echo "\n DB fetch finished, parsing content\n\n";
}

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

$file_uri_imdb = plugin_dir_path(__FILE__) . '/imdb_name_basics_small.json';
$imdb_liste = array();

if (file_exists($file_uri_imdb)) {
  $fp = fopen($file_uri_imdb, 'r') or die('could not open file for reading: ' . $file_uri_imdb);
  $imdb_liste = json_decode(fread($fp, filesize($file_uri_imdb)), true);
  fclose($fp);
}


$oyuncu_listesi_unique = array_unique($oyuncu_listesi);

if ($debug) {
  echo "Found " . count($oyuncu_listesi_unique) . " unique oyuncu record\n\n";
}

$original_has_url = count($json_liste);
$has_changes = false;

foreach ($oyuncu_listesi_unique as $oyuncu) {
  $oyuncu_index = yirmiiki_shortcode_json_key($oyuncu);

  if (!isset($json_liste[$oyuncu_index])) {
    if ($debug) {
      echo $oyuncu . "\n";
    }

    if (isset($imdb_liste[$oyuncu])) {
      $has_changes = true;
      $found_nmlink = $imdb_liste[$oyuncu];
      $json_liste[$oyuncu_index] = array('link' => "https://www.imdb.com/name/" . $found_nmlink, 'name' => $oyuncu);
    }
  }
}

if ($dry_run) {
  echo "\n running in dry run mode, no update is done to json\n";
} else if ($has_changes) {
  $fp = fopen($file_uri, 'w') or die('could not open file for writing: ' . $file_uri);
  fwrite($fp, json_encode($json_liste));
  fclose($fp);
}

if (!$force || !$dry_run) {
  update_option("oyuncu_listesi_son_tarih", $now);
}

if ($debug) {
  $total = count($oyuncu_listesi_unique);
  $has_url = count($json_liste);
  $remaining = $total - $has_url;

  echo "\n Total: " . $total . " Old Has URL: " . $original_has_url . " New Has URL: " . $has_url . " Remaining: " . $remaining . "\n\n";
}


?>
