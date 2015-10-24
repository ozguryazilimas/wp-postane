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

$posts_table = $wpdb->prefix . "posts";
$replace_from = array("’", ' ', '&', '#038;');
$replace_to = array("'", '_', 'and', '');

$sql = "SELECT post_content FROM $posts_table where post_type='page' and post_title='Dizi Listesi'";

$post_content = $wpdb->get_row($sql)->post_content;

$dom = new DOMDocument();
$dom->loadHTML($post_content);

$list = $dom->getElementsByTagName("ul");

$ul_list = array();

foreach ($list as $elem) {
  if ($elem->hasAttribute("class") && $elem->getAttribute("class")=="dizi_listesi") {
    $ul_list[] = $elem;
  }
}
//print_r($ul_list);

$res_array = array();

foreach($ul_list as $list) {
  $child = $list->firstChild;

  while ($child) {
    $a_elem = $child->firstChild;
    $link = NULL;

    foreach($a_elem->attributes as $attr) {
      if($attr->nodeName == "href") {
        $link = $attr->nodeValue;
      }
    }

    if ($link != NULL) {
      $search_key_raw = strtolower($a_elem->textContent);
      $search_key = str_replace($replace_from, $replace_to, $search_key_raw);
      $res_array[$search_key] = array('link' => $link,'name' => $a_elem->textContent);
    }

    $child = $child->nextSibling;
  }
}

$file_uri = plugin_dir_path(__FILE__) . '/dizi_listesi.json';
$fp = fopen($file_uri, 'w') or die('could not open file: ' . $file_uri);
fwrite($fp, json_encode($res_array));
fclose($fp);

?>
