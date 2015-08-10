<?php
/*
Plugin Name: Detailed-Search v1
Plugin URI: http://ozguryazilim.com.tr
Description: Detailed Search plugin For 22dakika.org project
Version: 1.1
Author: Kivilcim Eray
Author URI: http://github.com/kivicko
License: GPL
*/


/*********************************************************/
/* SEARCH FOR posts or comments                          */
/* WRITTEN BY THE author                                 */
/* BETWEEN date_begin AND date_end                       */
/* INCLUDING words_included NON-ORDERED                  */
/*           words_ordered ORDERED                       */
/*           AT LEAST ONE OF words_at_least_one          */
/* NOT INCLUDING words_excluded                          */
/* (IN CASE OF posts) HAVING AT LEAST ONE OF tags        */
/* (IN CASE OF posts) HAVING ALL OF tags_all             */
/* (IN CASE OF posts) HAVING MINIMUM min_like LIKES      */
/* ORDERED BY order                                      */
/*********************************************************/

/**
* Class for generating queries.
*
* It is used for generating query,holding variables for prepare function, 
* holding message to be written in the screen and evaluating query with given variables.
* 
* @author baskin
*/
class OY_Query
{
  private $query;
  private $variables;
  private $message;
  private $wpdb_p;

  /**
   * Constructer
   *
   * Initializes objects.Saves a reference to $wpdb.
   *
   * @author baskin
   *
   * @param string $q Initial query string
   * @param string $m Initial message string
   * @param array $v Initial variables
  */
  function __construct($q="",$m="",$v=array()) {
    global $wpdb;
    $this->wpdb_p     = &$wpdb;
    $this->query      = $q;
    $this->message    = $m;
    $this->variables  = $v;
  }

  /**
   * Extends the query from the end.
   *

   * Given added query,message and variables for prepare,function extends the query.
   *
   * @author baskin
   *
   * @access public
   *
   * @param string $added Query to be added.
   * @param string $mess Message to be added.
   * @param array $vars Array of variables for prepare to be added.
   * @param string $vars Variable for prepare to be added.
   * @param int $vars Variable for prepare, to be added.
  */
  public function extend_query($added, $mess, $vars=array()) {
    $this->query   .= " ".$added;
    $this->message .= $mess;

    if (is_array($vars)) {
      $this->variables = array_merge($this->variables, $vars);
    } else {
      array_push($this->variables, $vars);    
    }
  }

  /**
   * Evaluates query and returns the results.
   *
   * Query is first prepared and then executed.
   * An array containing results and message is returned from the function.
   *
   * @author baskin
   *
   * @access public
   *
   * @return array Contains results and message.
  */
  public function evaluate_query() {
    $ret=$this->wpdb_p->get_results($this->wpdb_p->prepare($this->query,$this->variables));
    $this->message .= " ".count($ret)." adet sonuç bulunmuştur.";
    return array("results" => $ret, "message" => $this->message);
  }

  /**
   * Getter for query.
   *
   * @author baskin
   *
   * @access public
   *
   * @return string Query string.
  */
  public function get_query() {
    return $this->query;
  }
  
  /**
   * Getter for variables.
   *
   * @author baskin
   *
   * @access public
   *
   * @return array Array of variables to be used in prepare.
  */
  public function get_variables() {
    return $this->variables;
  }
  
  /**
   * Getter for message.
   *
   * @author baskin
   *
   * @access public
   *
   * @return string The message associated with the query.
  */
  public function get_message() {
    return $this->message;  
  }
}




/**
 * Function to generate query for posts.
 *
 * Firstly,it extents the query to select ID from wp_posts that have status 'publish' and type 'post'.
 * Then,for each parameter function extends the query appropriately:
 * $tags -> It checks whether the ID of the post is in the list of posts that have at least one of the tags.
 * $tags_all -> It checks whether the ID of the post is in the list of posts that have all of the tags.
 * $author_id -> It checks whether the author_id of the post is $author_id.
 * $date_begin, $date_end -> It checks whether the post_date is between [$date_begin,$date_end]
 * $words_included -> For each of the words in $words_included it checks whether the post_title is like word or
 *                    post_content is like word and post_content contains it not inside tag(before closing).
 * $words_at_least_one -> Does the same as $words_included but it selects if at least one of words is found.
 * $words_ordered -> Does the same as $words_included but it selects if the all words are ordered.
 * $words_excluded -> Selects posts if any of the words in this array is not included in the post.
 * $min_like -> Selects the posts that have 'tutma' at least this value.
 * $order -> Determines the order of results (ascending or descending)
 *
 * @param int $author_id ID of the author of the posts.
 * @param string $date_begin Minimum date post was published.
 * @param string $date_end Maximum date post was published.
 * @param string $words_included List of words that post contais,seperated by space.
 * @param string $words_ordered List of words that post contains ordered,seperated by space.
 * @param string $words_at_least_one List of words that post contains at least once,seperated by space.
 * @param string $words_excluded List of excluded words,seperated by space.
 * @param string $order "asc" or "desc" denoting order of results.
 * @param int $min_like Minimum 'tutma' that posts have.
 * @param string $author_slug Slug of the author,printing purposes only
 * @param string $tags List of tags that post have at least one of,seperated by comma.
 * @param string $tags_all List of tags that post have all,seperated by comma.
 *
 * @author Kivilcim Eray
 * @author baskin
 *
 * @return OY_Query OY_Query object that includes query,variables and message
*/
function oy_generate_post_query($author_id, $date_begin, $date_end, $words_included, $words_ordered,  $words_at_least_one, $words_excluded, $order, $min_like, $author_slug,$tags,$tags_all){

  if($words_included != NULL){
		$words_included = explode(" ",$words_included);
  }

  if($words_at_least_one != NULL){
		$words_at_least_one = explode(" ", $words_at_least_one);
  }

  if($words_excluded != NULL){
		$words_excluded = explode(" ", $words_excluded);
  }

  if($tags!=NULL) {
		$tags=explode(",", $tags);
  }

  if($tags_all!=NULL)	{
		$tags_all=explode(",", $tags_all);
  }

  $query = new OY_Query();

  $query->extend_query("SELECT wp_posts.ID as ID FROM wp_posts WHERE post_status = '%s' AND post_type = '%s'","Arama sonucu ", array('publish','post'));

  if ($tags!=NULL) {
    $query->extend_query("AND ID in (select distinct(wp_term_relationships.object_id) as ID from (select term_taxonomy_id from (select * from wp_terms where", "(");
    $first=true;
    foreach($tags as $tag){
      if($first){
        $first=false;
        $query->extend_query("name = '%s'", $tag, $tag);
      } else {
        $query->extend_query("or name = '%s'", ",".$tag, $tag);
      }
    }
    $query->extend_query(") as A inner join wp_term_taxonomy on wp_term_taxonomy.term_id=A.term_id where wp_term_taxonomy.taxonomy='post_tag') as B inner join wp_term_relationships on B.term_taxonomy_id=wp_term_relationships.term_taxonomy_id)",") etiketlerinin en az birine sahip ");
	}
 
  if($tags_all!=NULL){
    $added_sql="";
    $i=0;
    $added_message .= '(';
    $variables=array();
    foreach($tags_all as $tag){
      if($i == 0){
        $added_sql = "(select wp_term_relationships.object_id as ID from (select term_taxonomy_id from (select * from wp_terms where name = '%s') as A inner join wp_term_taxonomy on wp_term_taxonomy.term_id=A.term_id where wp_term_taxonomy.taxonomy='post_tag') as B inner join wp_term_relationships on B.term_taxonomy_id=wp_term_relationships.term_taxonomy_id)";
        $added_message .= $tag;
        $i++;
      } else {
        $added_sql .= " as A";
        $added_sql = "(SELECT A.ID as ID from ".$added_sql." inner join (select distinct(wp_term_relationships.object_id) as ID from (select term_taxonomy_id from (select * from wp_terms where name = '%s') as A inner join wp_term_taxonomy on wp_term_taxonomy.term_id=A.term_id where wp_term_taxonomy.taxonomy='post_tag') as B inner join wp_term_relationships on B.term_taxonomy_id=wp_term_relationships.term_taxonomy_id) as B on B.ID=A.ID)";
        $added_message .= ','.$tag;
      }
      array_push($variables,$tag);
    }
    $added_message.=') etiketlerinin hepsine sahip ';
    $query->extend_query("AND ID in".$added_sql." ", $added_message, $variables);
  }

  if ($author_id != NULL) {
    $query->extend_query("AND post_author = %d", $author_slug . " üyesine ait olan ", array($author_id));
  }

  if ($date_begin != NULL && $date_end != NULL) {
    $query->extend_query("AND post_date >= '%s' AND post_date <= '%s'",$date_begin." ile ".$date_end." tarihleri arasında yazılmış ", array($date_begin,$date_end));
  }
  
  if ($words_included != NULL) {
    foreach ($words_included as $key ) {
      $query->extend_query("AND (post_title LIKE '%s' OR (post_content LIKE '%s' AND (post_content REGEXP CONCAT(CONCAT(CONCAT('^[^<>]*','%s'),'|^.*<[^<>]*>[^<]*'),'%s'))))", $key." ", array('%'.$key.'%', '%'.$key.'%', $key, $key));
    }
		  $query->extend_query("","ifadeleri geçen ");
  }

  if ($words_at_least_one != NULL) {
    $query->extend_query("AND ( 1=0");
    foreach ($words_at_least_one as $key ) {
      $query->extend_query("OR post_title LIKE '%s' OR (post_content LIKE '%s' AND (post_content REGEXP CONCAT(CONCAT(CONCAT('^[^<>]*','%s'),'|^.*<[^<>]*>[^<]*'),'%s')))", $key . " ", array('%'.$key.'%', '%'.$key.'%', $key, $key));
    }
    $query->extend_query(")", "kelimelerinden en az birine sahip olan");
  }

  if ($words_ordered != NULL) {
    $query->extend_query("AND (post_title LIKE '%s' OR (post_content LIKE '%s' AND (post_content REGEXP CONCAT(CONCAT(CONCAT('^[^<>]*','%s'),'|^.*<[^<>]*>[^<]*'),'%s'))))", $words_ordered . " kelimeleri sıralı olan ", array('%'.$words_ordered.'%', '%'.$words_ordered.'%', $words_ordered, $words_ordered));
  }

  if($words_excluded != NULL) {
    foreach ($words_excluded as $key ){
      $query->extend_query("AND post_title NOT LIKE '%s' AND (post_content NOT LIKE '%s' OR NOT (post_content REGEXP CONCAT(CONCAT(CONCAT('^[^<>]*','%s'),'|^.*<[^<>]*>[^<]*'),'%s')))", $key." ", array('%'.$key.'%', '%'.$key.'%', $key, $key));
    }
			$query->extend_query("","kelimelerini bulundurmayan ");
  }

  if ((int)$min_like > 0) {
    $query->extend_query("AND ID in (select post_id from wp_custom_likes GROUP BY post_id HAVING SUM(value) >= %d)", " en az ".$min_like." tutulmaya sahip ",  array($min_like));
  }

  if($order == "asc") {
    $query->extend_query("order by post_date asc");
  } else {
		$query->extend_query("order by post_date desc");
  }

  return $query;
}


/**
 * Function to generate query for comments.
 *
 * Firstly,it extents the query to select anything from wp_comments that are approved.
 * Then,for each parameter function extends the query appropriately:
 * $author_slug -> It checks whether the author of the comment is $author_slug.
 * $date_begin, $date_end -> It checks whether the comment_date is between [$date_begin,$date_end]
 * $words_included -> For each of the words in $words_included it checks whether the comment_content is like word
 * $words_at_least_one -> Does the same as $words_included but it selects if at least one of words is found.
 * $words_ordered -> Does the same as $words_included but it selects if the all words are ordered.
 * $words_excluded -> Selects comments if any of the words in this array is not included in the comment_content.
 * $order -> Determines the order of results (ascending or descending)
 *
 * @param string $author_slug Slug of the author
 * @param string $date_begin Minimum date comment was published.
 * @param string $date_end Maximum date comment was published.
 * @param string $words_included List of words that comment contais,seperated by space.
 * @param string $words_ordered List of words that comment contains ordered,seperated by space.
 * @param string $words_at_least_one List of words that comment contains at least once,seperated by space.
 * @param string $words_excluded List of excluded words,seperated by space.
 * @param string $order "asc" or "desc" denoting order of results.
 *
 * @author Kivilcim Eray
 * @author baskin
 *
 * @return OY_Query OY_Query object that includes query,variables and message
*/
function oy_generate_comment_query($author_slug, $date_begin, $date_end, $words_included, $words_ordered, $words_at_least_one, $words_excluded, $order){
  
  $query = new OY_Query();
  	
  if($words_included != NULL){
		$words_included = explode(" ",$words_included);
  }

  if($words_at_least_one != NULL){
		$words_at_least_one = explode(" ",$words_at_least_one);
  }

  if($words_excluded != NULL){
		$words_excluded = explode(" ",$words_excluded);
  }
  
  $query->extend_query("SELECT * FROM wp_comments WHERE comment_approved= %d", "Arama sonucu ", 1);
  
  if($author_slug != NULL){
    $query->extend_query("AND comment_author = %s", $author_slug." üyesine ait olan ", $author_slug);
  }

  if($date_begin != NULL && $date_end != NULL){
    $query->extend_query("AND comment_date >= '%s' AND comment_date <= '%s'", $date_begin . " ile " . $date_end . " tarihleri arasında yazılmış ", array($date_begin,$date_end));
  }

  if($words_included != NULL){
    foreach ($words_included as $key ){
      $query->extend_query("AND comment_content LIKE '%s'", $key." ",'%'.$key.'%');
    }
			$query->extend_query("","ifadeleri geçen ");
  }

  if($words_at_least_one != NULL){
      $query->extend_query("AND (1=0");
    foreach ($words_at_least_one as $key ){
      $query->extend_query("OR comment_content LIKE '%s'", $key . " ", '%'.$key.'%');
    }
    $query->extend_query(")","kelime grubuna sahip olan");
  }

  if($words_ordered != NULL){
    $query->extend_query("AND comment_content LIKE '%s'",$words_ordered." kelimeleri sıralı olan ",'%'.$words_ordered.'%');
  }

  if($words_excluded != NULL){
    foreach ($words_excluded as $key ){
      $query->extend_query("AND comment_content NOT LIKE '%s'",$key." ",'%'.$key.'%');
    }
    $query->extend_query("","kelimelerini bulundurmayan");
  }

  if($order == "asc"){
    $query->extend_query("order by comment_date asc");
  }else{
    $query->extend_query("order by comment_date desc");
  }

	return $query;
}

/**
 *
 * Used for sorting array by its 'start' element.
 *
 * @author baskin
 *
 * @return int Indicating the order of elements.Positive means greater, negative means less than, zero means equal to.
 *
*/
function oy_sort_by_start($x, $y) {
  return $x['start']-$y['start'];
}

/**
 *
 * Generates content to be printed.
 *
 * It does that by searching each word of word_list in content_string and taking the sentence of that word.
 *
 * @author baskin
 *
 * @param string $content_string Content string of either post or comment.
 * @param array $word_list Array of words that are searched in $content_string.
 *
 * @return string The string to be printed.
 *
*/
function oy_generate_print_content($content_string, $word_list) {
  
  $replaced_content = preg_replace('/<[^>]+>/','',$content_string);
  $replaced_content = preg_replace('/\[[^\[]+\]/','',$replaced_content);
  $replaced_content = preg_replace('/[=]+/','',$replaced_content);

  $str_len          = strlen($replaced_content);
  $result_array     = array();
  foreach ($word_list as $word) {
    $position = stripos ($replaced_content, $word);
    if ($position != false) {
      $endpos = $position;
      $startpos = $position;
      for (; $endpos < $str_len; $endpos++) {
        if ($replaced_content[$endpos] == '.') {
          break;
        }
      }
      for(; $startpos>0; $startpos--) {
        if ($replaced_content[$startpos] == '.') {
          $startpos++;
          break;
        }
      }
      array_push($result_array,array("content" => substr($replaced_content,$startpos,$endpos-$startpos+1),
                                   "start" => $startpos,
                                   "end" => $endpos));
    }
  }		
  $check_array = array();
  foreach ($result_array as $idx => $res) {
    if (array_search($res["start"].",".$res["end"],$check_array) == false) {
      array_push($check_array, $res["start"].",".$res["end"]);
    } else {
      unset($result_array[$idx]);
    }
  }
  usort($result_array, 'oy_sort_by_start');
    
  $print_content = "...";
  if (count($result_array) == 0) {
    $print_content = "";
  } else if ($result_array[0]["start"]==0) {
    $print_content = "";
  }
 foreach ($result_array as $res) {
    $print_content .= $res["content"];
    $print_content .= "..";
  }
  return $print_content;
}

/**
 * Prints the posts given as array.
 *
 * @author Kivilcim Eray
 * @author baskin
 *
 * @param array $post_array The array containing the post ids.
 * @param array $word_list The array containing words that have been searched.
*/
function oy_print_posts($post_array, $word_list){
	global $wpdb;
	foreach ($post_array as $key ){
		$page             = $key->ID;
		$page_data        = get_post($page);

		$print_title      = $page_data->post_title;
		$print_time       = $page_data->post_date;
		$print_link       = get_post_permalink( $page );
		$print_user       = get_userdata($page_data->post_author)->user_nicename;
    $thumbnail        = get_the_post_thumbnail($key->ID, array(100,100));	

    $print_content    = oy_generate_print_content($page_data->post_content,$word_list);    

    echo '
			<div class="oy-arama-sonuc">
        <div class="oy-thumb">
          <a href="'.$print_link.'">
          '.$thumbnail.'
          </a>
        </div>
        <div class="oy-content">
				  <div class="oy-arama-sonuc-baslik">
					  <p><a href="'.$print_link.'">'.$print_title.'</a></p>
				  </div>
				  <div class="oy-arama-sonuc-meta">
					  <p>Yazar:<a href="'.site_url().'?author='.$page_data->post_author.'">'.$print_user.'</a>,Yayınlanma tarihi: '.$page_data->post_date.'</p>
				  </div>
				  <div class="oy-arama-sonuc-icerik">
					  <p>'.$print_content.'</p>
				  </div>
        </div>
			</div>
		';
	}
}


/**
 * Prints the comments given as array.
 *
 * @author Kivilcim Eray
 * @author baskin
 *
 * @param array $comment_array The array containing the comments.
*/
function oy_print_comments($comment_array,$word_list){
	global $wpdb;
	foreach ($comment_array as $key ) {
    $page             = $key->comment_post_ID;
    $comment_date     = $key->comment_date;
    $author           = $key->comment_author;
    $page_data        = get_post($page);
    $print_title      = $page_data->post_title;
    $print_link       = get_post_permalink( $page );
    $comment_id       = $key->comment_ID;
    $user_id          = get_user_by('slug',$author)->ID;
    $thumbnail        = get_the_post_thumbnail($key->comment_post_ID, array(100,100));	

    $print_content    = oy_generate_print_content($key->comment_content,$word_list);

  	echo '
				<div class="oy-arama-sonuc">
          <div class="oy-thumb">
            <a href="'.$print_link.'">
            '.$thumbnail.'
            </a>
          </div>
          <div class="oy-content">
					  <div class="oy-arama-sonuc-baslik">
						  <p><a href="'.$print_link.'#comment-'.$comment_id.'">'.$print_title.'</a></p>
					  </div>
					  <div class="oy-arama-sonuc-meta">
						  <p>Yorumlayan:<a href="'.site_url().'?author='.$user_id.'">'.$author.'</a>,Yayınlanma tarihi: '.$comment_date.'</p>
					  </div>
					  <div class="oy-arama-sonuc-icerik">
						  <p>'.$print_content.'</p>
					  </div>
          </div>
				</div>
			';
		
	}
}


add_action('template_redirect', 'oy_custom_page_template_redirect');

/**
 * Function to load search page.
 *
 * Page is loaded if get array contains 'name' index as 'ayrintili-ara'
 *
 * @author Kivilcim Eray 
 *
*/
function oy_custom_page_template_redirect() {
    global $wp_query;
    if ($wp_query->query_vars['name'] == 'ayrintili-ara') {
        $wp_query->is_404 = false;
        status_header(200);
        include(ABSPATH . 'wp-content/plugins/oy-detailed-search/oy-template.php');
        exit;
    }
}

    
add_action('init','oy_load');
    
/**
 * Function to load css and js files that are used by the plugin.
 *
 * @author Kivilcim Eray
 * @author baskin
*/
function oy_load() {
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
  wp_register_style('oy_template_css', WP_PLUGIN_URL.'/oy-detailed-search/css/oy-css.css');
  wp_enqueue_style('oy_template_css');
  wp_enqueue_script('oy-js', WP_PLUGIN_URL.'/oy-detailed-search/js/oy-js.js');
}
?>
