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
$date = get_option("oyuncu_listesi_son_tarih");

if (!$date) {
  add_option("oyuncu_listesi_son_tarih", $now,'','no');
  $date = $now;
}

$posts_table = $wpdb->prefix . "posts";
$sql = "SELECT post_content FROM $posts_table WHERE post_modified_gmt >= '$date'";
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

foreach ($oyuncu_listesi as $oyuncu) {
  $oyuncu_index = strtolower($oyuncu);

  if (!isset($json_liste[$oyuncu_index])) {
    $query = str_replace(' ','+',$oyuncu_index);
    $imdb_response = json_decode(file_get_contents("http://www.imdb.com/xml/find?json=1&nr=1&nm=on&q=$query"));

    if (isset($imdb_response->name_popular) && strcasecmp($imdb_response->name_popular[0]->name,$oyuncu)==0 ) {
      $json_liste[$oyuncu_index] = array('link' => "http://www.imdb.com/name/".$imdb_response->name_popular[0]->id, 'name' => $imdb_response->name_popular[0]->name);
    } elseif (isset($imdb_response->name_exact) && strcasecmp($imdb_response->name_exact[0]->name,$oyuncu)==0) {
      $json_liste[$oyuncu_index] = array('link' => "http://www.imdb.com/name/".$imdb_response->name_exact[0]->id, 'name' => $imdb_response->name_exact[0]->name);
    }
  }
}

$fp = fopen($file_uri, 'w') or die('could not open file for writing: ' . $file_uri);
fwrite($fp, json_encode($json_liste));
fclose($fp);

update_option("oyuncu_listesi_son_tarih", $now);

?>
