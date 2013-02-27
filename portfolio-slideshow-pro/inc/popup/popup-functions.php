<?php /*
 * Simple functions required for the modal popup version of the slideshow
 */
 
/* Create the shortcode */
add_shortcode( 'popup-slideshow', 'portfolio_slideshow_pro_popup_shortcode' );
 
/* Define the shortcode function */
function portfolio_slideshow_pro_popup_shortcode( $atts ) {

	global $ps_options;
 
 	extract( shortcode_atts( array(
 		'id' => '',
 		'text' => '',
 		'windowheight' => '625',
 		'windowwidth' => '625',
 		'height' => '',
 		'width' => '',
 		'slideheight' => '',
 		'centered' => '',
 		'carousel' => '',
 		'carouselsize' => '4',
 		'showcaps' => '',
 		'showtitles' => '',
 		'showdesc' => '',
 		'navstyle' => '',
 		'navpos' => '',
 		'autoplay' => ''
 		
 	), $atts ) );
 
 	if ( ! $id ) die();
 	
 	$url = PORTFOLIO_SLIDESHOW_PRO_URL . '/inc/popup/popup.php?id=' . $id; 
 	
 	$args = '';
 	
 	if ( $height ) $args .= '&amp;h=' . $height;
	if ( $width ) $args .= '&amp;w=' . $width;
	if ( $slideheight ) $args .= '&amp;sh=' . $slideheight; 
 	if ( $windowheight ) $args .= '&amp;wh=' . $windowheight;
 	if ( $windowwidth ) $args .= '&amp;ww=' . $windowwidth;
 	if ( $showcaps ) $args .= '&amp;caps=' . $showcaps;
	if ( $showtitles ) $args .= '&amp;titles=' . $showtitles;
	if ( $showdesc ) $args .= '&amp;desc=' . $showdesc;
	if ( $carousel ) $args .= '&amp;carousel=' .$carousel;
	if ( $carouselsize ) $args .= '&amp;carouselsize=' .$carouselsize;
	if ( $centered ) $args .= '&amp;centered=' . $centered;
	if ( $navstyle ) $args .= '&amp;nav=' . $navstyle;
	if ( $navpos ) $args .= '&amp;navpos=' . $navpos;
	if ( $autoplay ) $args .= '&amp;autoplay=' . $autoplay;
 
 	if ( ! $text ) {
 		$text = $url;
 	}
 	
 	$ps_popup_output = '<a class="slideshow-popup" href="' . $url . $args . '">' . $text . '</a>'; 
 
 	return $ps_popup_output;
}