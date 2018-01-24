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

$oyuncu = $argv[1];
$oyuncu_url = $argv[2];
$file_uri = plugin_dir_path(__FILE__) . '/oyuncu_listesi.json';

$fp = fopen($file_uri, 'r') or die('could not open file for reading: ' . $file_uri);
$json_liste = json_decode(fread($fp, filesize($file_uri)), true);
fclose($fp);

$oyuncu_index = yirmiiki_shortcode_json_key($oyuncu);

$json_liste[$oyuncu_index] = array(
  'link' => $oyuncu_url,
  'name' => $oyuncu
);

$fp = fopen($file_uri, 'w') or die('could not open file for writing: ' . $file_uri);
fwrite($fp, json_encode($json_liste));
fclose($fp);

?>
