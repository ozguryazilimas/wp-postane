<?php

// create the shortcode
add_shortcode( 'portfolio_slideshow', 'portfolio_slideshow_pro_shortcode' );

// define the shortcode function
function portfolio_slideshow_pro_shortcode( $atts ) {
	
	STATIC $i=0;

	global $ps_options;

	extract( shortcode_atts( array(
		'size' => $ps_options['size'],
		'nowrap' => $ps_options['nowrap'],
		'speed' => $ps_options['speed'],
		'delay' => '0',
		'trans' => $ps_options['trans'],
		'centered' => $ps_options['centered'],
		'height'	=> '',
		'width'	=> '',
		'timeout' => $ps_options['timeout'],
		'exclude_featured'	=> $ps_options['exclude_featured'],
		'autoplay' => $ps_options['autoplay'],
		'duration'	=>	'',
		'audio'	=>	'',
		'showinfo' => $ps_options['showinfo'],
		'showplay' => $ps_options['showplay'],
		'pagerpos' => $ps_options['pagerpos'],
		'pagerstyle' => $ps_options['pagerstyle'],
		'togglethumbs' => $ps_options['togglethumbs'],
		'thumbnailsize' => $ps_options['thumbsize'],
		'thumbnailmargin' => $ps_options['thumbnailmargin'],
		'proportionalthumbs' => $ps_options['proportionalthumbs'],
		'navpos' => $ps_options['navpos'],
		'fancygrid' => $ps_options['fancygrid'],
		'random' => $ps_options['random'],
		'carousel' => '', //kept for compatibility with previous versions of the plugin
		'carouselsize' => $ps_options['carouselsize'],
		'navstyle' => $ps_options['navstyle'],
		'showcaps' => $ps_options['showcaps'],
		'showtitles' => $ps_options['showtitles'],
		'showdesc' => $ps_options['showdesc'],
		'click' =>	$ps_options['click'],
		'target' => $ps_option['click_target'],
		'fluid'	=>	$ps_options['allowfluid'],
		'slideheight' => '',
		'pagerwidth' => '',
		'class' =>	'',
		'id' => '',
		'exclude' => '',
		'include' => ''
	), $atts ) );

	/* Has a custom post id been declared or should we use current page ID? */
	
	if ( ! $id ) { $id = get_the_ID(); }

	/* If the exclude_featured attribute is set, get the featured thumb ID and add it to the $exclude string */
	
	/* Future note: should we make exclude an array so it's easier to work with? */ 
	
	if ( $exclude_featured == "true" ) {
		$featured_id = get_post_thumbnail_id( $id );
		if ( $exclude ) { // if $exclude is already set, concatenate it
			$exclude = $exclude . "," . $featured_id;	
		} else { // $exclude is simply equal to $featured_id 
			$exclude = $featured_id;
		}
	} 
	
	/* Now we exclude any images that have been marked as "exclude" in the Gallery tab */
	
	$attachments = get_children( array('post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'meta_key' => '_ps_exclude_checkbox', 'meta_value' => 'exclude') );
		
	if ( $attachments ) { // if we have any attachments, let's build this list
		$k = 1;
		if ( !$exclude ) { // if exclude is already empty, create a blank value
		 	$exclude = '';
		} else { // otherwise we're going to append a comma to get the number list started
			$exclude .= ',';
		}
		foreach ($attachments as $attachment ) {
			$exclude .= $attachment->ID;
			//add a comma if it's not the last item in the list
			if ( $k != count( $attachments ) ) { $exclude .= ','; } 
			$k++;
		}
	}
	
	/* Now we can run the query */
		
	if ( $include ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$attachments = get_posts( array(
		'order'          => 'ASC',
		'orderby' 		 => 'menu_order ID',
		'post_type'      => 'attachment',
		'post_parent'    => $id,
		'post_mime_type' => 'image',
		'post_status'    => null,
		'numberposts'    => -1,
		'include'		 => $include) );
	} elseif ( $exclude ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_posts( array(
		'order'          => 'ASC',
		'orderby' 		 => 'menu_order ID',
		'post_type'      => 'attachment',
		'post_parent'    => $id,
		'post_mime_type' => 'image',
		'post_status'    => null,
		'numberposts'    => -1,
		'exclude'		 => $exclude) );
	} else {
		$attachments = get_posts( array(
		'order'          => 'ASC',
		'orderby' 		 => 'menu_order ID',
		'post_type'      => 'attachment',
		'post_parent'    => $id,
		'post_mime_type' => 'image',
		'post_status'    => null,
		'numberposts'    => -1 ) );
	}
	
	global $ps_count;
	$ps_count = count( $attachments );
	
	/*
	 * Overrides
	 */
	
	if ( $audio ) { $psaudio = "true"; } else { $psaudio = "false"; } 
		
	/* If carousel is true, change the pager option (legacy support) */
	if ( $carousel == "true" ) $pagerstyle = "carousel";
	
	/* If a click target is set, map that to the proper options */
	if ( $target == "current" || $target == "" ) { 
		$target = "_self"; 
	} elseif ( $target == "parent" ) {
		$target = "_parent";
	} else { 
		$target = "_blank"; 
	}
	
	/* If fancygrid is active, force the correct options */
	if ( $fancygrid == "true" ) {
		$pagerpos = "top";
		$pagerstyle = "thumbs";		
		$trans = "fade";
		$speed = 100;	
		$togglethumbs = "true";
	}
	
	/* Override the per-slide timeout if a full-slideshow duration is set */
	if ( $duration ) { $timeout = ( $duration * 1000 ) / $ps_count; }

	/* If we don't have enough images to show the carousel, use the thumbnail display instead */
	if ( $pagerstyle == "carousel" && $carouselsize >= $ps_count ) $pagerstyle = "thumbs";
			
	/*
	 * Navigation
	 */
	 
	if ( ! is_feed() && $ps_count > 1 ) { //no need for navigation if there's only one slide
		switch ( $navstyle ) { 
					
			default :
					$ps_nav .= '<div id="slideshow-nav'.$i.'" class="slideshow-nav arrows hoson"><a href="javascript:void(0);" class="slideshow-prev">Previous</a><a class="slideshow-next" href="javascript:void(0);">Next</a></div>';
			break;
		}
	} 

	/*
	 * Pagers
	 */
	
	if ( ! is_feed() &&  $ps_count > 1 ) {
					
		switch ( $pagerstyle ) {
			case "numbers": 
				$ps_pager = '<div id="pager' . $i . '" class="pager clearfix"><div class="numbers"></div><!--.numbers--></div><!--#pager-->';	
				break;
			
			case "bullets":
				$ps_pager = '<div id="pager' . $i . '" class="pager clearfix"><div class="bullets">'; 
					for ($k = 1; $k <= $ps_count; $k++) {
						$ps_pager .= '<a href="javascript: void(0);" class="bullet"></a>';
					}
				$ps_pager .= '	
				</div><!--.bullets--></div><!--#pager-->';	
				break;
				
				case "carousel":
			
					$carouselwidth = ( $ps_options['carousel_thumbsize'] + $ps_options['carousel_thumbnailmargin'] ) * $carouselsize; /* Add the margin to the original thumbnail size and multiply it by the number of images in the row to find out how long the row width should be */
	
					$carouselwidth = $carouselwidth - $ps_options['carousel_thumbnailmargin']; /* Subtract the width of one margin so everything fits */
					
					$ps_pager = '<div class="pscarousel';

					$ps_pager .= '" style="width: '  . $carouselwidth . 'px;';			
						  
					$ps_pager .='">';

					/* This is the hidden nav for the carousel */
					$pstabs = ceil( $ps_count/$carouselsize );
					$ps_pager .= '<ul id="carouselnav' . $i . '" class="navi">';
					for ($t = 1; $t <= $pstabs; $t++) {
						$ps_pager .= '<li><a href="javascript: void(0);">'.$t.'</a></li>';
					}
					$ps_pager .= '</ul>';	
				
					$ps_pager .= '<a class="prev browse left hidden">left</a><div id= "scrollable' . $i . '" class="scrollable"'; 
					$ps_pager .= ' style="width: '  . $carouselwidth . 'px;">';
									   
					$ps_pager .= '<div id="pager' . $i . '" class="pager items clearfix">';
													
					if ( $attachments ) {
						$j = 1;
						$ps_pager .='<div>';
						foreach ( $attachments as $attachment ) {
					
							/* We use WPThumb to generate a custom thumb here */
							
							$thumbsrc = wp_get_attachment_image_src( $attachment->ID, 'medium' );
							
							$thumbsrc = wpthumb( $thumbsrc[0], 'width=' . $ps_options[carousel_thumbsize] . '&height=' . $ps_options[carousel_thumbsize] . '&crop=true' );
								
							$ps_pager .= '<img height="' . $ps_options[carousel_thumbsize] . '" width="' . $ps_options[carousel_thumbsize] . '" src="' . $thumbsrc . '" alt="' . $attachment->post_title . ' thumbnail"" />';
																					
							if ( $j % $carouselsize == 0 && $j != $ps_count ) { 
								$ps_pager .='</div>
								<div>';
							}

							$j++;
						}
					}
					
					$ps_pager .= '</div></div></div><a class="next browse right">right</a></div><!--.pscarousel-->';
					break;				
			
			case "thumbs":
				
				$ps_pager = '<div class="psthumbs';
					if ( $togglethumbs == "true" ) {
						$ps_pager .= ' toggle-thumbs';
					}				
				$ps_pager .='">';
												   
				$ps_pager .= '<div id="pager' . $i . '" style="';
				
				if ( $pagerwidth ) {
					$ps_pager .= 'width:' . $pagerwidth . 'px;';
				}				
				
				if ( $centered == "true" ) {
					$ps_pager .= 'margin-left:' . $thumbnailmargin / 2 . 'px;';
				} 
				
				$ps_pager .= '" class="pager items clearfix">';
				
				if ( $proportionalthumbs == "true" ) {
					$crop = 0;	
				} else {
					$crop = 1;
				}
																							
				if ( $attachments ) {
					
					foreach ( $attachments as $attachment ) {
						
						
						/* We use WPThumb to generate a custom thumb here */
						
						$thumbsrc = wp_get_attachment_image_src( $attachment->ID, 'medium' );
						
						$thumbsrc = wpthumb( $thumbsrc[0], 'width=' . $thumbnailsize . '&height=' . $thumbnailsize . '&crop=' . $crop );
											
						$ps_pager .= '<div><span style="height:' . $thumbnailsize . 'px; width:' . $thumbnailsize . 'px;"><span></span><img style="margin-right:' . $thumbnailmargin . 'px; margin-bottom:' . $thumbnailmargin . 'px;" src="' . $thumbsrc . '" alt="' . $attachment->post_title . ' thumbnail" /></span></div>';					
					
					} /* End foreach */
			  } /* End if ( $attachments ) */
				
				$ps_pager .= '</div><!--.pager--></div><!--.psthumbs-->';
				break;	
		
			default :
				$ps_pager .= '<ul id="pager'.$i.'" class="pager"></ul>';
				break;
		}	
		
	}	
		
	if ( ! is_feed() ) { 
		
		$slideshow = 
		'<script type="text/javascript">/* <![CDATA[ */ psTimeout['.$i.']='.$timeout.';psAudio['.$i.']='.$psaudio.';psAutoplay['.$i.']='.$autoplay.';psDelay['.$i.']='.$delay.';psTrans['.$i.']=\''.$trans.'\';psNoWrap['.$i.']='.$nowrap.';psCarouselSize['.$i.']='.$carouselsize.';psSpeed['.$i.']='.$speed.';psRandom['.$i.']='.$random.';psPagerStyle['.$i.']=\''.$pagerstyle.'\';/* ]]> */</script>'; 
	
		//wrap the whole thing in a div for styling		
		$slideshow .= '<div id="slideshow-wrapper'.$i.'" class="slideshow-wrapper clearfix';
		
		if ( $centered == "true" ) { 
			$slideshow .= " centered"; 
		}


		if ( $fancygrid == "true" ) { 
			$slideshow .= " fancygrid"; 
		}

		if ( $fluid == "true" ) { 
			$slideshow .= " fluid"; 
		}

		if ( $trans == 'fade') {
			$slideshow .= ' fade';
		}		

		if ( $ps_options['showloader'] == "true" ) { 
			$slideshow .= " showloader"; 
		}
		
		if ( $class ) { 
			$slideshow .= " $class"; 
		}
		
		$slideshow .='"><a id="psprev'.$i.'" href="javascript: void(0);"></a><a id="psnext'.$i.'" href="javascript: void(0);"></a>
		';	
		
		if ( $navpos == "top" ) { 
			$slideshow .= $ps_nav;
		}
	
		if ( $pagerpos == "top" ) { 
			$slideshow .= $ps_pager;
		}
	
		$slideshow .= '<div id="portfolio-slideshow'.$i.'" class="portfolio-slideshow"';
		
		/* An inline style if they need to set a height for the main slideshow container */	
		
		if ( $slideheight ) {
			$slideshow .= ' style="min-height:' . $slideheight . 'px !important;"';
		}
		
		$slideshow .='>
		';

} //end ! is_feed()
	
	$slideID = 0;

	if ( $attachments ) { //if attachments are found, run the slideshow
	
		//begin the slideshow loop
		foreach ( $attachments as $attachment ) {
			
			$alttext = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

			if ( ! $alttext ) {
				$alttext = $attachment->post_title;
			}
				
			$slideshow .= '<div class="';
			if ( $slideID != "0" ) { $slideshow .= "not-first "; }
			$slideshow .= 'slideshow-next slideshow-content'; 
			$slideshow .= '">
			';
			
			
			switch ( $click ) {
			
				case "lightbox" :	
					$imgsrc = wp_get_attachment_image_src( $attachment->ID, 'large' );
					$imagelink = $imgsrc[0] . '" class="fancybox" rel="group-'.$id;

					if ( $showcaps == "true" ) { 
						$caption = $attachment->post_excerpt;
						$imagelink = $imagelink . '" title="' . $caption . '"'; 
					}	

					break;

				case "openurl" :
					$imagelink = get_post_meta( $attachment->ID, '_ps_image_link', true );
					
					if (preg_match('%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $imagelink, $match)) {
					    $video_class = "video-youtube";
					}

					if (preg_match('%(?:vimeo\.com/(\d+))%i', $imagelink, $match)) {
					    $video_class = "video-vimeo";
					}
										
					if ( $imagelink ) { $imagelink = $imagelink . '" target="' . $target; } else {
						$imagelink = 'javascript: void(0);" class="slideshow-next';
					}
					
					if ( $video_class ) { $imagelink = $imagelink . '" class="'. $video_class; }
					$video_class = '';
					break;
				
				case "none":
					$imagelink = NULL;
					break;			
					
				default :
					$imagelink = 'javascript: void(0);" class="slideshow-next';
					break;	
			}		
						
			if ( $imagelink ) {		
				if ( $nowrap == "true" && $ps_count - 1 != $slideID || $nowrap != "true" || $nowrap == "true" && $click == "lightbox" ) { 
					$slideshow .= '<a href="'.$imagelink.'">';
				}
			}
			
/*
 * This is the part of the loop that actually returns the images
 */
 			if ( is_feed() ) { /* No slideshow if we're in the feed */
 			 	$feedimg = wp_get_attachment_image_src( $attachment->ID, 'large' );		
 				$slideshow .= '<img style="margin-bottom:15px" src="' . $feedimg[0] . '"/><br />';
  				
 			} else { /* Slideshow output */
 			
	 			$ps_placeholder = PORTFOLIO_SLIDESHOW_PRO_URL . '/img/tiny.png';
	 								 
				if ( $width || $height ) { 
				
				/* Determine if we've got an explicit height or width in the shortcode */
								
					if ( ! $width ) { $width = 0; };
					if ( ! $height ) { $height = 0; };
										
					$imgsource = wp_get_attachment_image_src( $attachment->ID, 'full' );
			
					$imgsource= wpthumb( $imgsource[0], 'width=' . $width . '&height=' . $height );
					
				} elseif ( $size == "custom" ) { 
	
				/* If we're using a defined custom size */
					
					$imgsource = wp_get_attachment_image_src( $attachment->ID, 'full' );
				
					$imgsource= wpthumb( $imgsource[0], 'width=' . $ps_options[customwidth] . '&height=' . $ps_options[customheight] );
						
				} else { /* Otherwise it's just one of the WP defaults */
					
					$imgsource = wp_get_attachment_image_src( $attachment->ID, $size );
					
					$imgsource = $imgsource[0];
				
				}		
						
				$slideshow .= '<img class="psp-active" data-img="' . $imgsource . '"'; 
				
				if ( $slideID < 1 ) { 
					$slideshow .= ' src="' . $imgsource . '"';
				} else {
					$slideshow .= ' src="' . $ps_placeholder . '"';
				}
				//include the real src attribute for the first slide only
				
				/* WPThumb reference here - if we need heights and widths use the native functions */
				
				$slideshow .= ' alt="' . $alttext . '" /><noscript><img src="' . $imgsource . '" alt="' . $alttext . '" /></noscript>';
									 						
 			} /* End is_feed determination for slideshow */

/*
 * That's it for the images
 */			
			if ( $imagelink ) {		
				if ( $nowrap == "true" && $ps_count - 1 != $slideID || $nowrap != "true" || $nowrap == "true" && $click == "lightbox"  ) { 
					$slideshow .= "</a>";
				}		
			}
			
			if ( $showtitles == "true" || $showcaps == "true" || $showdesc == "true" ) {
				$slideshow .= '<div class="slideshow-meta">';
			}

			//if titles option is selected
			if ( $showtitles == "true" ) {
				$title = $attachment->post_title;
				if ( $title ) { 
					$slideshow .= '<p class="slideshow-title">'.$title.'</p>'; 
				} 
			}
			
			//if captions option is selected
			if ( $showcaps == "true" ) {			
				$caption = $attachment->post_excerpt;
				if ( $caption ) { 
					$slideshow .= '<p class="slideshow-caption">'.$caption.'</p>'; 
				}
			}
			
			//if descriptions option is selected
			if ( $showdesc == "true" ) {			
				$description = $attachment->post_content;
				if ( $description ) { 
					$slideshow .= '<p class="slideshow-description">'. wpautop( $description ) .'</p>'; 
				}
			}
			if ( $showtitles == "true" || $showcaps == "true" || $showdesc == "true" ) {
				$slideshow .= '</div>';
			}

			$slideshow .= '</div>
			';
			
			$slideID++;
					
		}  // end slideshow loop
	} // end if ( $attachments)

	if ( ! is_feed() ) {
		
		$slideshow .= "</div><!--#portfolio-slideshow-->";
		
		if ( $navpos == "bottom" ) { 
			$slideshow .= $ps_nav;
		}
		
		if ( $pagerpos == "bottom" ) { 
			$slideshow .= $ps_pager;
		}
		
		$slideshow .='</div><!--#slideshow-wrapper-->';

	} /* End ! is_feed() */
	
	$i++;

	if ( $audio ) {
		$slideshow .= '<div id="haiku-text-player'.$i.'" class="haiku-text-player"></div><div style="display:none">';
		$slideshow .= do_shortcode("[haiku url=$audio graphical=false noplayerdiv=true]") . "</div>";		
	}

	return $slideshow;	//that's the slideshow
	
	
} //ends the portfolio_shortcode function ?>