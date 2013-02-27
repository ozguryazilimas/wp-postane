<?php 

/* Create the cache directory for the TimThumb images */
$target = WP_CONTENT_DIR . '/cache';
 if( !is_dir( $target) ) {
	wp_mkdir_p( $target );
}

if ( get_option( 'portfolio_slideshow_options' ) === false ) { //If the 1.4 options don't exist yet, run the older upgrade script first.

	$version = get_option( 'PORTFOLIO_SLIDESHOW_PRO_VERSION' );
		
	if ( $version ) { // if we've got a previous version of Portfolio Slideshow Pro to upgrade

		$ps_version = get_option( 'PORTFOLIO_SLIDESHOW_PRO_VERSION' );

		if ( $ps_version < "1.3.0" ) {  //settings upgrade for 1.3.0

			/* Map previous option for external URLs to the new "image click" option if necessary and then delete the old option */
			$ps_descriptionisURL = get_option( 'portfolio_slideshow_descriptionisURL' );
			if ( $ps_descriptionisURL ) {
				add_option( "portfolio_slideshow_image_click", 'openurl' );
			}

			delete_option( 'portfolio_slideshow_descriptionisURL' );
			
			/* If timeout was previously set, make sure the new autoplay option is set up to true. */
			$ps_timeout = get_option( 'portfolio_slideshow_timeout' );
			if ( $ps_timeout != 0 ) {
				add_option( "portfolio_slideshow_autoplay", 'true' );
			}		
			
		} // end 

		if ( $ps_version < "1.3.5" ) { // next we're going to do the options settings upgrade for 1.3.5
			
			//First we're going to make sure all of our checkbox options are mapped properly to the new boolean values:
			
			$showtitle = get_option( "portfolio_slideshow_show_titles" );
			if ( $showtitle != "true" ) {
				update_option( "portfolio_slideshow_show_titles", "false" );
			}
			
			
			$showcaps = get_option( "portfolio_slideshow_show_captions" );
			if ( $showcaps != "true" ) {
				update_option( "portfolio_slideshow_show_captions", "false" );
			}
			
			$showdesc = get_option( "portfolio_slideshow_show_descriptions" );
			if ( $showdesc != "true" ) {
				update_option( "portfolio_slideshow_show_descriptions", "false" );
			}
			
			$showloader = get_option( "portfolio_slideshow_showloader" );
			if ( $showloader != "true" ) {
				update_option( "portfolio_slideshow_showloader", "false" );
			}


			$togglethumbs = get_option( "portfolio_slideshow_thumbnail_toggle" );
			if ( $togglethumbs != "true" ) {
				update_option( "portfolio_slideshow_thumbnail_toggle", "false" );
			}
			
			$autoplay = get_option( "portfolio_slideshow_autoplay" );
			if ( $autoplay != "true" ) {
				update_option( "portfolio_slideshow_autoplay", "false" );
			}
			
			$centered = get_option( "portfolio_slideshow_display_centered" );
			if ( $centered != "true" ) {
				update_option( "portfolio_slideshow_display_centered", "false" );
			}
			
			$carousel = get_option( "portfolio_slideshow_carousel" );
			if ( $carousel != "true" ) {
				update_option( "portfolio_slideshow_carousel", "false" );
			}
			
			$random = get_option( "portfolio_slideshow_random" );
			if ( $random != "true" ) {
				update_option( "portfolio_slideshow_random", "false" );
			}
			
			$showhash = get_option( "portfolio_slideshow_showhash" );
			if ( $showhash != "true" ) {
				update_option( "portfolio_slideshow_showhash", "false" );
			}

			$fancybox = get_option( "portfolio_slideshow_fancybox" );
			if ( $fancybox != "true" ) {
				update_option( "portfolio_slideshow_fancybox", "false" );
			}

			$exclude_featured = get_option( "portfolio_slideshow_exclude_featured" );
			if ( $exclude_featured != "true" ) {
				update_option( "portfolio_slideshow_exclude_featured", "false" );
			}
			
			$jquery = get_option( "portfolio_slideshow_jquery_version" );
			if ( $jquery == "1.4.4" ) {
				update_option( "portfolio_slideshow_jquery_version", "1.7.1" );
			}

			$nowrap = get_option( "portfolio_slideshow_nowrap" );
			if ( $nowrap != "true" ) {
				update_option( "portfolio_slideshow_nowrap", "false" );
			}

			//Next we're going to grab all the options and merge them into a single array

			$ps_options = array(
				'version'	=>	PORTFOLIO_SLIDESHOW_PRO_VERSION,
				'size'	=> get_option( "portfolio_slideshow_size" ),
				'customwidth'	=> get_option( "portfolio_slideshow_custom_width" ),
				'customheight'	=> get_option( "portfolio_slideshow_custom_height" ),
				'trans'	=> get_option( "portfolio_slideshow_transition" ),
				'speed'	=> get_option( "portfolio_slideshow_transition_speed" ), 
				'showtitles'	=>	get_option( "portfolio_slideshow_show_titles" ), 
				'showcaps'	=>	get_option( "portfolio_slideshow_show_captions" ), 
				'centered'	=>	get_option( "portfolio_slideshow_display_centered" ), 
				'showdesc'	=>	get_option( "portfolio_slideshow_show_descriptions" ), 
				'pagerpos'	=>	get_option( "portfolio_slideshow_pager_position" ),
				'random'	=>	get_option( "portfolio_slideshow_random" ),
				'pagerstyle'	=>	get_option( "portfolio_slideshow_pager_style"),
				'thumbsize'	=>	get_option( "portfolio_slideshow_thumb_size" ),
				'carousel'	=>	get_option( "portfolio_slideshow_carousel" ),
				'carouselsize'	=>	get_option( "portfolio_slideshow_carousel_size" ),
				'carousel_thumbsize'	=> '75',
				'carousel_thumbnailmargin'	=> '8',
				'thumbnailmargin'	=>	get_option( "portfolio_slideshow_thumbnail_margin" ),
				'togglethumbs'	=>	get_option( "portfolio_slideshow_thumbnail_toggle" ),
				'navpos'	=>	get_option( "portfolio_slideshow_nav_position" ), 
				'fancygrid'	=>	'false',
				'navstyle'	=>	get_option( "portfolio_slideshow_nav_style" ), 
				'showplay'	=>	"true", 
				'showinfo'	=>	"true", 
				'touchswipe'	=>	"true", 
				'keyboardnav'	=>	"true", 
				'infotxt'	=>	'of',
				'fancygrid'	=>	'false', 
				'allowfluid'	=>	'false', 
				'debug'	=>	'false', 
				'nowrap'	=>	get_option( "portfolio_slideshow_nowrap" ),
				'showhash'	=>	get_option( "portfolio_slideshow_showhash" ), 
				'click'	=>	get_option( "portfolio_slideshow_image_click" ), 
				'click_target'	=>	get_option( "portfolio_slideshow_click_target" ), 
				'timeout'	=>	get_option( "portfolio_slideshow_timeout" ),
				'autoplay'	=>	get_option( "portfolio_slideshow_autoplay" ), 
				'exclude_featured'	=>	get_option( "portfolio_slideshow_exclude_featured" ), 
				'showloader'	=>	get_option( "portfolio_slideshow_showloader" ), 
				'jquery'	=>	get_option( "portfolio_slideshow_jquery_version" ),
				'fancybox'	=>	get_option( "portfolio_slideshow_fancybox" ),
				'load_scripts'	=>	"true", 
				'license'	=>	get_option( "portfolio_slideshow_license" )
			);	

			update_option( 'portfolio_slideshow_options', $ps_options );

		}
	
	} else { //New options don't exist and we're not upgrading from a previous version
		
		$ps_options = array(
				'version'	=>	PORTFOLIO_SLIDESHOW_PRO_VERSION,
				'size'	=> 'medium',
				'customwidth'	=> '500',
				'customheight'	=> '500',
				'trans'	=> 'fade',
				'speed'	=> '400', 
				'showtitles'	=>	"false", 
				'showcaps'	=>	"false", 
				'showdesc'	=>	"false", 
				'centered'	=>	"false", 
				'pagerpos'	=>	'bottom',
				'pagerstyle'	=>	'thumbs',
				'thumbsize'	=>	'75',
				'carouselsize'	=>	'6',
				'carousel_thumbsize'	=> '75',
				'carousel_thumbnailmargin'	=> '8',
				'thumbnailmargin'	=>	'5',
				'togglethumbs'	=>	"false",
				'proportionalthumbs'	=>	"false",
				'thumbborder'	=>	'FFFFFF',
				'navpos'	=>	'top', 
				'fancygrid'	=>	'false', 
				'random'	=>	"false", 
				'navstyle'	=>	'graphical',
				'showplay'	=>	"true",
				'showinfo'	=> 	"true",
				'touchswipe'	=>	"true",
				'keyboardnav'	=>	"true", 
				'infotxt'	=>	'of',
				'allowfluid'	=>	'false',
				'nowrap'	=>	"false",
				'showhash'	=>	"false", 
				'click'	=>	'advance', 
				'click_target'	=>	'_blank', 
				'timeout'	=>	'4000',
				'autoplay'	=>	'false', 
				'exclude_featured'	=>	"false", 
				'showloader'	=>	"false",
				'jquery'	=>	'1.7.1',
				'fancybox'	=>	"true",
				'cycle'	=>	"true",
				'load_scripts'	=>	"true", 
				'license'	=>	'',
				'debug'	=>	'false'
			);	
	
		update_option( 'portfolio_slideshow_options', $ps_options );
	} 
} else { // If we've already 1.4 or above, run the standard upgrade script
	
	$ps_options = get_option( 'portfolio_slideshow_options' );
	
	if ( $ps_options['version'] < '1.4.1' ) { //added a new option in 1.4.1
		$ps_options['allowfluid'] = "false"; 
	}
	
	if ( $ps_options['version'] < '1.4.6' ) { //added a new option in 1.4.6
		$ps_options['touchswipe'] = "true"; 
		$ps_options['keyboardnav'] = "true"; 
	}
	
	if ( $ps_options['version'] < '1.4.8' ) { //added a new option in 1.4.8
		$ps_options['cycle'] = "true";
	}
	
	if ( $ps_options['version'] < '1.5.0' ) { //added new options in 1.5.0
		$ps_options['fancygrid'] = "false";
		$ps_options['carousel_thumbsize'] = '75';
		$ps_options['carousel_thumbnailmargin'] = '8';
		if ( $ps_options['carousel'] == "true" ) 
			{ $ps_options['pagerstyle'] == "carousel"; }
		if ( $ps_options['jquery'] == "1.6.1" )
			{ $ps_options['jquery'] == "1.7.1"; }
	} 

	$ps_options['version'] = PORTFOLIO_SLIDESHOW_PRO_VERSION;
	update_option( 'portfolio_slideshow_options', $ps_options );		
}	

?>