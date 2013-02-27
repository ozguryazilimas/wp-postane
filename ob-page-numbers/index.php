<?php
/*
Plugin Name: OB Page Numbers
Plugin URI: http://code.olib.co.uk
Description: A simple paging navigation plugin for users and search engines. Instead of next and previous page it shows numbers and arrows. Settings available..
Version: 1.1.1
Author: Olly Benson, Jens T&ouml;rnell
Author URI: http://code.olib.co.uk
*/
	
if (!class_exists('ob_settings_v1_4')) include ("ob_settings_v1_4.class.php");
include ("ob_settings.vars.php");

$obPageNumbers = new ob_settings_v1_4();
$obPageNumbers-> init('ob_page_numbers_values');



class ob_page_numbers {
 
function stylesheet($head_stylesheet = NULL) {
	if(is_archive() || is_search() || is_home() ||is_page()) : 
		$theme = get_option("ob_page_numbers_theme",array('text_string' => "default"));
		printf('<link rel="stylesheet" href="%s" type="text/css" media="screen" />',
				plugins_url( sprintf('css/%s.css',$theme['text_string']), __FILE__ ));
		endif;
    }


function action() {
	global $wp_query,$paged;
	$getOptions = array ('limitPages' => 10,
		'isPageOfPage' => true,
		'pageOfPageText' => "Page %u of %u",
		'isNextPrevButton' => true,
		'prevPage' => "&lt;",
		'nextPage' => "&gt;",
		'isFirstLastNumbers' => true,
		'isFirstLastGap' => true,
		'firstGap' => "...",
		'lastGap' => "...");

	foreach ($getOptions AS $key=>$value) :
		$temp = get_option("ob_page_numbers_".$key);
		$$key = (!empty($temp)) ? $temp['text_string'] : $value;
		endforeach;
	$total_pages = $wp_query->max_num_pages; // total pages in category
	if ($total_pages==1) { return null;}  // ends function if there is only one page.

	$current_page = (!empty($paged)) ? $paged : 1; // current page

	$min_page = $current_page - floor(intval($limitPages)/2); // works out the lowest page number to be displayed
	$limitPages = (intval($limitPages)-1);
	if ($min_page<1) $min_page=1;
	$max_page = $min_page + $limitPages; // words out the highest page number to be displayed
	if ($max_page>$total_pages) $max_page=$total_pages;
	if ($max_page==$total_pages && $max_page>$limitPages) $min_page= ($max_page-$limitPages); // changes min_page if max is last page

	$pagingString = "<div id='wp_page_numbers'><ul>"; // builds output

	// displays "Page x of y"
	if($isPageOfPage)
		$pagingString.= sprintf("<li class='page_info'>".$pageOfPageText."</li>",floor ($current_page),floor($total_pages));

	// displays link to previous page
	if($isNextPrevButton && $current_page!=1) 
		$pagingString.=sprintf("<li><a href='%s'>%s</a></li>",get_pagenum_link($current_page-1),$prevPage);

	// displays page 1 link and ellipses when min page is more than 1
	if ($min_page>1) {
		if ($isFirstLastNumbers) $pagingString.= sprintf("<li class='first_last_page'><a href='%s'>%u</a>",get_pagenum_link(1),1);
		if ($isFirstLastGap) $pagingString.= sprintf("<li class='space'>%s</li>",$firstGap);
		}

	// displays lowest to highest page
	for($i=$min_page; $i<=$max_page; $i++) 
		$pagingString.= ($current_page == $i) ? 
			sprintf("<li class='active_page'><a>%u</a></li>\n",$i) :	
			sprintf("<li %s><a href='%s'>%u</a></li>\n",($current_page == $i) ? "class='active_page'" : null,get_pagenum_link($i),$i);	
		
	// displays total page link and ellipses when max page is lower than total page
	if ($max_page<$total_pages) {
		if ($isFirstLastGap) $pagingString.= sprintf("<li class='space'>%s</li>",$lastGap);
		if ($isFirstLastNumbers) $pagingString.= sprintf("<li class='first_last_page'><a href='%s'>%u</a>",get_pagenum_link($total_pages),$total_pages);
		}
	
	// displays link to next page
	if($isNextPrevButton && $current_page!=$total_pages)
		$pagingString.=sprintf("<li><a href='%s'>%s</a></li>",get_pagenum_link($current_page+1),$nextPage);
 
  	$pagingString.= "</ul>\n<div style='float: none; clear: both;'></div>\n</div>\n";
	
	printf($pagingString);
}

// end class
}

add_action('wp_head', array('ob_page_numbers','stylesheet'));

function ob_page_numbers () {
	$a = "ob_page_numbers";
	$b = new $a;
	$b->action();
	}

?>