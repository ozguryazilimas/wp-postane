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

$res_array = array();

/*
$posts_table = $wpdb->prefix . "posts";
$sql = "SELECT post_content FROM $posts_table where post_type='page' and post_title='Dizi Listesi'";
$post_content = $wpdb->get_row($sql)->post_content;

$dom = new DOMDocument();
$dom->loadHTML(mb_convert_encoding($post_content, 'HTML-ENTITIES', 'UTF-8'));

$list = $dom->getElementsByTagName("ul");

$ul_list = array();

foreach ($list as $elem) {
  if ($elem->hasAttribute("class") && $elem->getAttribute("class")=="dizi_listesi") {
    $ul_list[] = $elem;
  }
}
//print_r($ul_list);

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
      $search_key = yirmiiki_shortcode_json_key($a_elem->textContent);
      $res_array[$search_key] = array('link' => $link,'name' => $a_elem->textContent);
    }

    $child = $child->nextSibling;
  }
}
 */

$peyton_list_db = $wpdb->prefix . 'peyton_list';
$sql_str = "SELECT * FROM $peyton_list_db ORDER BY title";
$data = $wpdb->get_results($sql_str);

foreach($data as $k) {
  if ($k->link != NULL && $k->link != '') {
    $search_key = yirmiiki_shortcode_json_key($k->title);

    $res_array[$search_key] = array(
      'link' => $k->link,
      'name' => $k->title
    );
  }
}


$file_uri = plugin_dir_path(__FILE__) . '/dizi_listesi.json';
$fp = fopen($file_uri, 'w') or die('could not open file: ' . $file_uri);
fwrite($fp, json_encode($res_array));
fclose($fp);

?>
