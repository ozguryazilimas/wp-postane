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

// initiate a fake curl session to mimmick browser
function shorttag_generator_get_contents_for_browser($url) {
  $header = array(
    // 'Content-Type: application/json',
    'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
    'Cache-Control: max-age=0',
    'Connection: keep-alive',
    'Keep-Alive: 300',
    'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
    'Accept-Language: en-us,en;q=0.5',
    'Pragma: ' // keep this blank
  );

  $options = array(
    CURLOPT_URL => $url,
    CURLOPT_HTTPHEADER => $header,
    CURLOPT_RETURNTRANSFER => true,
    // CURLOPT_FOLLOWLOCATION => true,
    // CURLOPT_USERAGENT => "Mozilla",
    CURLOPT_USERAGENT => 'spider',
    CURLOPT_AUTOREFERER => true,
    // CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
    // CURLOPT_TIMEOUT => 120, // timeout on response
    CURLOPT_TIMEOUT => 20,
    // CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_REFERER => '',
    CURLOPT_ENCODING => 'gzip,deflate'
  );

  $ch = curl_init();
  curl_setopt_array( $ch, $options );

  $result = curl_exec($ch);
  $response_header = curl_getinfo($ch);
  curl_close($ch);

  return $result;
}


global $wpdb;
$now = new DateTime();
$now = $now->format('Y-m-d H:i:s');
$debug = in_array('-d', $argv);
$dry_run = in_array('-n', $argv);


/*
$date = get_option("oyuncu_listesi_son_tarih");

if (!$date) {
  add_option("oyuncu_listesi_son_tarih", $now,'','no');
  $date = $now;
}
 */

$posts_table = $wpdb->prefix . "posts";
// $sql = "SELECT post_content FROM $posts_table WHERE post_modified_gmt >= '$date'";
$sql = "SELECT post_content FROM $posts_table WHERE post_content LIKE '%[oyuncu]%'";
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

$oyuncu_listesi_unique = array_unique($oyuncu_listesi);

if ($debug) {
  echo "Found " . count($oyuncu_listesi_unique) . " unique oyuncu record\n\n";
}

$original_has_url = count($json_liste);

foreach ($oyuncu_listesi_unique as $oyuncu) {
  $oyuncu_index = yirmiiki_shortcode_json_key($oyuncu);
  $oyuncu_imdb_str = strtolower($oyuncu);

  if (!isset($json_liste[$oyuncu_index])) {
    if ($debug) {
      echo $oyuncu . "\n";
    }

    if ($dry_run) {
      // echo "\n running in dry run mode, no call is done to imdb\n";
    } else {
      $query = str_replace(' ', '+', $oyuncu_imdb_str);
      $imdb_response = json_decode(shorttag_generator_get_contents_for_browser("http://www.imdb.com/xml/find?json=1&nr=1&nm=on&q=$query"));

      if (isset($imdb_response->name_popular) && strcasecmp($imdb_response->name_popular[0]->name,$oyuncu)==0 ) {
        $json_liste[$oyuncu_index] = array('link' => "http://www.imdb.com/name/".$imdb_response->name_popular[0]->id, 'name' => $imdb_response->name_popular[0]->name);
      } elseif (isset($imdb_response->name_exact) && strcasecmp($imdb_response->name_exact[0]->name,$oyuncu)==0) {
        $json_liste[$oyuncu_index] = array('link' => "http://www.imdb.com/name/".$imdb_response->name_exact[0]->id, 'name' => $imdb_response->name_exact[0]->name);
      }
    }
  }
}

if ($dry_run) {
  echo "\n running in dry run mode, no update is done to json\n";
} else {
  $fp = fopen($file_uri, 'w') or die('could not open file for writing: ' . $file_uri);
  fwrite($fp, json_encode($json_liste));
  fclose($fp);
}

// update_option("oyuncu_listesi_son_tarih", $now);

if ($debug) {
  $total = count($oyuncu_listesi_unique);
  $has_url = count($json_liste);
  $remaining = $total - $has_url;

  echo "\n Total: " . $total . " Old Has URL: " . $original_has_url . " New Has URL: " . $has_url . " Remaining: " . $remaining . "\n\n";
}


?>
