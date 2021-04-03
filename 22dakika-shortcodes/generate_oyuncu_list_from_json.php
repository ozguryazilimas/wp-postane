<?php
if (php_sapi_name() != "cli") {
  die();
}

ini_set('memory_limit', '5120M');

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
$published_only = in_array('-p', $argv);

function print_debug($str) {
  global $debug;

  if ($debug) {
    echo $str . "\n";
  }
}

if (!$force) {
  $date = get_option("oyuncu_listesi_son_tarih");

  if (!$date) {
    if (!$dry_run) {
      add_option("oyuncu_listesi_son_tarih", $now,'','no');
    }

    $date = $now;
  }
}

$working_on_str = $force ? 'never forever' : $date;
print_debug("Working on DB data after " . $working_on_str);

$posts_table = $wpdb->prefix . "posts";
// $sql = "SELECT post_content FROM $posts_table WHERE post_modified_gmt >= '$date'";
$sql = "SELECT post_content FROM $posts_table WHERE post_content LIKE '%[oyuncu]%'";

if (!$force) {
  $sql .= " AND post_modified_gmt >= '$date'";
}

if ($published_only) {
  $sql .= " AND post_status = 'publish'";
}

$post_list = $wpdb->get_results($sql);
$oyuncu_listesi = array();

print_debug("DB fetch finished, parsing content");

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

print_debug("reading file " . $file_uri);
$fp = fopen($file_uri, 'r') or die('could not open file for reading: ' . $file_uri);
$json_liste = json_decode(fread($fp, filesize($file_uri)), true);
$json_liste_old_count = count($json_liste);
fclose($fp);

$file_uri_imdb = plugin_dir_path(__FILE__) . '/imdb_name_basics_small.json';
$imdb_liste = array();

print_debug("reading file " . $file_uri_imdb);
$fp = fopen($file_uri_imdb, 'r') or die('could not open file for reading: ' . $file_uri_imdb);
$imdb_liste = json_decode(fread($fp, filesize($file_uri_imdb)), true);
fclose($fp);


$oyuncu_listesi_unique = array_unique($oyuncu_listesi);

print_debug("Found " . count($oyuncu_listesi_unique) . " oyuncu record we need to work on in DB");
print_debug(var_export($oyuncu_listesi_unique, true));

$original_has_url = count($json_liste);
$has_changes = false;
$oyuncu_added = array();
$oyuncu_could_not_add = array();

foreach ($oyuncu_listesi_unique as $oyuncu) {
  $oyuncu_index = yirmiiki_shortcode_json_key($oyuncu);

  if (!isset($json_liste[$oyuncu_index])) {
    print_debug("Missing in our data " . $oyuncu);
    $oyuncu_downcase = strtolower($oyuncu);

    if (isset($imdb_liste[$oyuncu])) {
      $has_changes = true;
      $found_nmlink = $imdb_liste[$oyuncu_downcase];
      $json_liste[$oyuncu_index] = array('link' => "https://www.imdb.com/name/" . $found_nmlink, 'name' => $oyuncu);
      array_push($oyuncu_added, $oyuncu);
    } else {
      array_push($oyuncu_could_not_add, $oyuncu);
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

if (!$force && !$dry_run) {
  update_option("oyuncu_listesi_son_tarih", $now);
}

if ($debug) {
  $oyuncu_added_count = count($oyuncu_added);
  $oyuncu_could_not_add_count = count($oyuncu_could_not_add);
  $json_liste_new_count = count($json_liste);

  print_debug("Added");
  print_debug(var_export($oyuncu_added, true));

  print_debug("could not add");
  print_debug(var_export($oyuncu_could_not_add, true));

  print_debug("Stored old count " . $json_liste_old_count . " new count " . $json_liste_new_count . " added " . $oyuncu_added_count . " not added " . $oyuncu_could_not_add_count);
  echo "\n";
}


?>
