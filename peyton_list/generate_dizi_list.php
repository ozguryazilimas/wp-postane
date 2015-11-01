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

foreach ($wp_blog_path as $incpath) {
  if (file_exists($incpath)) {
    require($incpath);
    # echo "including " . $incpath . "\n";
    break;
  }
}

global $wpdb;

$posts_table = $wpdb->prefix . "posts";
$sql = "SELECT post_content FROM $posts_table where post_type='page' and post_title='Dizi Listesi'";
$post_content = $wpdb->get_row($sql)->post_content;

$dom = new DOMDocument();
$dom->loadHTML(mb_convert_encoding($post_content, 'HTML-ENTITIES', 'UTF-8'));

$list = $dom->getElementsByTagName("ul");

$ul_list = array();

foreach ($list as $elem) {
  if ($elem->hasAttribute("class") && $elem->getAttribute("class") == "dizi_listesi") {
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
    $style = '';

    foreach($a_elem->attributes as $attr) {
      if ($attr->nodeName == "href") {
        $link = $attr->nodeValue;
      }

      if ($attr->nodeName == "style") {
        $style = $attr->nodeValue;
      }
    }

    // echo "\n" . $a_elem->textContent . " => " . $link . "\n";
    if ($link != NULL) {
      $search_key = yirmiiki_shortcode_json_key($a_elem->textContent);
      $res_array[$search_key] = array(
        'link' => $link,
        'name' => $a_elem->textContent,
        'style' => $style
      );
    }

    $child = $child->nextSibling;
  }
}

$insert_data = array();
foreach($res_array as $search_key => $parsed_data) {
  $style = $parsed_data['style'];
  $data = array(
    'title' => $parsed_data['name'],
    'category' => 1,
    'status' => 3,
    'link' => $parsed_data['link']
  );

  if ($style == 'color: #006803') {
    $data['status'] = 1; // tanitimi var
    array_push($insert_data, $data);
  } else if ($style == 'color: #ff7900') {
    $data['status'] = 2; // mini tanitim
    array_push($insert_data, $data);
  } else if ($style == 'color: #ff0000') {
    $data['status'] = 3; // tanitim yok
    array_push($insert_data, $data);
  } else if ($style == 'color: #3366ff') {
    $data['category'] = 5; // anime
    array_push($insert_data, $data);
  } else if ($style == 'color: #000000') {
    $data['category'] = 6; // anime
    array_push($insert_data, $data);
  } else {
    echo "Unkown data TITLE:" . $parsed_data['name'] . " STYLE:" . $parsed_data['style'] . " LINK:" . $parsed_data['link'] . "\n";
  }
}

foreach($insert_data as $data) {
  peyton_list_insert_entry($data, true);
}


?>
