<?php _e( '
<h3>Table of contents</h3>
<ul style="margin-bottom:50px">
<li><a href="#general">General plugin usage</a></li>
<li><a href="#shortcode">Shortcode attributes</a></li>
<li><a href="#popup">Popup shortcode</a></li>
<li><a href="#video">Working with video links</a></li>
</ul>

	<h3 id="general">General plugin usage</h3>', 'portfolio-slideshow-pro' );?>
		
		<iframe src="http://player.vimeo.com/video/20679115?byline=0&amp;color=ffffff" width="601" height="378" frameborder="0"></iframe><p><a href="http://vimeo.com/20679115">Portfolio Slideshow Pro demo</a> from <a href="http://vimeo.com/madebyraygun">Raygun</a> on <a href="http://vimeo.com">Vimeo</a>.</p>

		<?php _e( '<p>To use the plugin, upload your photos directly to a post or page using the WordPress media uploader, or use the standalone slideshow panel (the menu labeled "Slideshows" in the left-hand WordPress navigation menu) to add a slideshow anywhere. Use the [portfolio_slideshow] shortcode to display the slideshow in your page or post. You can customize the slideshow settings globally in the plugin settings panel or customize an individual slideshow by modifying the shortcode attributes.</p>
							
				<h3 id="shortcode">Shortcode Attributes</h3>
		
				<p>If you would like to customize your slideshows on a per-slideshow basis, you can add the following attributes to the shortcode, which will temporarily override the defaults.
		
				<p>To select a <strong>different page parent ID</strong> for the images:</p>
				<p><code>[portfolio_slideshow id=xxx]</code></p>
				
				<p>
				To <strong>add a custom class</strong> to the slideshow wrapper:
				</p>
				
				<p>
				<code>[portfolio_slideshow class=alignright]</code>
				</p>	
		
				<p>
				To change the <strong>image size</strong> you would use the size attribute in the shortcode like this:
				</p>
				
				<p>
				<code>[portfolio_slideshow size=thumbnail], [portfolio_slideshow size=medium], [portfolio_slideshow size=large], [portfolio_slideshow size=full], [portfolio_slideshow size=custom]</code>
				</p>
				<p>This setting can use any custom image size that you\'ve registered in WordPress.
				
				<p>You can specify a <strong>custom image size</strong> directly in the shortcode with the height and width attributes:</p>
		
				<code>[portfolio_slideshow width=600], [portfolio_slideshow height=450], [portfolio_slideshow width=600 height=450]</code> 
		
				<p>The height and width attributes override the size attribute and by default will not upscale or crop the images.
		
				<p>
				You can add a custom <strong>slide container height</strong>: 
				</p>
				
				<p>
				<code>[portfolio_slideshow slideheight=400]</code>
				</p>
				
				<p>
				Useful if you don\'t want the page height to adjust with the slideshow.
				</p>
				
				<p>
				<strong>Image transition FX</strong>: 
				</p>
				
				<p>
				<code>[portfolio_slideshow trans=scrollHorz]</code>
				</p>
				
				<p>
				You can use this shortcode attribute to supply any transition effect supported by jQuery Cycle, even if they\'re not in the plugin! List of supported transitions <a href="http://jquery.malsup.com/cycle/begin.html">here</a> Not all transitions will work with all themes, if in doubt, stick with fade or none.
				</p>
						
				<p>
				<strong>Transition speed</strong>:
				</p>
				
				<p>
				<code>[portfolio_slideshow speed=400]</code>
				</p>
			
				<p>
				Add a <strong>delay</strong> to the beginning of the slideshow:
				</p>
				
				<p>
				<code>[portfolio_slideshow delay=1000]</code>
				</p>
					
				<p>
				<strong>Show titles, captions, or descriptions</strong>:
				</p>
				
				<p>
				<code>[portfolio_slideshow showtitles=true], [portfolio_slideshow showcaps=true], [portfolio_slideshow showdesc=true]</code>
				(use false to disable)</p>
				
				<p>
				<strong>Center the slideshow</strong>:
				</p>
				
				<p>
				<code>[portfolio_slideshow centered=true], [portfolio_slideshow centered=false]</code></p>
				
				
				<p>
				<strong>Time per slide when slideshow is playing (timeout)</strong>:
				</p>
				
				<p>
				<code>[portfolio_slideshow timeout=4000]</code>
				</p>
		
				
				<p>
				<strong>Autoplay</strong>:
				</p>
				<p>
				<code>[portfolio_slideshow autoplay=true]</code>
				</p>
		
				<p>
				<strong>Duration</strong>:
				</p>
				<p>You can set a time for the entire slideshow, in seconds, which will automatically calculate the per-slide timeout for you. This pairs nicely with the audio player support.</p>
				<p>
				<code>[portfolio_slideshow duration=300]</code>
				</p>
		
		
				<p>
				<strong>Audio</strong>:
				</p>
				<p>You can add audio to the slideshow by specifying any MP3 URL. This feature pairs nicely with the "Duration" attribute, you can create a slideshow that lasts exactly as long as the audio file. Be careful with autoplay! If you have multiple posts on a page (the blog homepage, for example), you\'ll end up with overlapping audio. Autoplay is recommended on single pages only. You must have our free <a href="http://madebyraygun.com/lab/haiku/">Haiku</a> audio player plugin installed and activated for this feature to work.</p>
				<p>
				<code>[portfolio_slideshow audio=http://madebyraygun.com/uploads/audiofile.mp3]</code>
				</p>
		
				<p>
				<strong>Randomize slideshow</strong>:
				</p>
				<p>
				<code>[portfolio_slideshow random=true]</code>
				</p>
		
				<p>
				<strong>Exclude featured image</strong>:
				</p>
				<p>
				<code>[portfolio_slideshow exclude_featured=true]</code>
				</p>
		
				<p>
				<strong>Disable slideshow wrapping</strong>: 
				</p>
				
				<p>
				<code>[portfolio_slideshow nowrap=true]</code>
				</p>
				
				<p>
				or enable it like this:
				</p>
				
				<p>
				<code>[portfolio_slideshow nowrap=false]</code>
				</p>
				
		
				<p>
				<strong>Clicking on a slideshow image:</strong>:
				</p>
				<p>Clicking on a slideshow image can advance the slideshow, open a custom URL (set in the media uploader), or open the full-size version of the image in a lightbox:
				<p>
				<code>[portfolio_slideshow click=advance] or [portfolio_slideshow click=openurl] or [portfolio_slideshow click=lightbox]</code>
				</p>
				
				<p>
				<strong>Click target:</strong>:
				</p>
				<p>If you\'ve set the click behavior to open a URL, you can specify whether it should open in the current window or a new window:
				<p>
				<code>[portfolio_slideshow target=current] or [portfolio_slideshow target=new] or [portfolio_slideshow target=parent]</code> The parent option is for slideshows embedded in iframes and popups.)
				</p>
				
				<p>
				<strong>Navigation links</strong> can be placed at the top:
				</p>
				
				<p>
				<code>[portfolio_slideshow navpos=top]</code>
				</p>
				
				<p>
				or at the bottom:
				</p>
				
				<p>
				<code>[portfolio_slideshow navpos=bottom]</code></p>
				
				<p>Use <code>[portfolio_slideshow navpos=disabled]</code> to disable navigation altogether. Slideshow will still advance when clicking on slides, using the pager, or with autoplay.</p>
				
				<p><strong>Navigation link style</strong> can be selected:</p>
				
				<p>
				<code>[portfolio_slideshow navstyle=text]</code> or <code>[portfolio_slideshow navstyle=graphical]</code> </p>
		
				<p>The <strong>navigation info</strong> can be customized:</p>
				
				<p>Hide the play button: </p>
				<p><code>[portfolio_slideshow showplay=false]</code></p>
		
				<p>Hide the slidecounter: </p>
				<p><code>[portfolio_slideshow showinfo=false]</code></p>
				
				<p><strong>Pager style</strong> can be selected:</p>
				
				<p>
				<code>[portfolio_slideshow pagerstyle=thumbs]</code> or <code>[portfolio_slideshow pagerstyle=carousel]</code> or <code>[portfolio_slideshow pagerstyle=bullets]</code> or <code>[portfolio_slideshow pagerstyle=numbers]</code></p>
				
				<p>In the carousel, you can change the number of <strong>thumbnails per row</strong>:</p>
				
				<p>
				<code>[portfolio_slideshow carouselsize=8]</code>
				
				<p>When the thumbnail pager is selected, you can customize the <strong>thumbnail size</strong>, <strong>thumbnail margin</strong>, and whether to enable <strong>proportional (non-cropped) thumbnails</strong>:</p>
				
				<p><code>[portfolio_slideshow thumbsize=80]</code><br />
				<code>[portfolio_slideshow thumbnailmargin=10]</code><br />
				<code>[portfolio_slideshow proportionalthumbs=true]</code></p>
				
				<p>You can also control the <strong>width of the thumbnail pager</strong> if you want to limit the number of thumbnails per row:</p>
				
				<code>[portfolio_slideshow pagerwidth=500]</code></p>
				
				<p><strong>Pager position</strong> can be selected:</p>
		
			  <p><code>[portfolio_slideshow pagerpos=top]</code> 
				</p>
				
				<p>
				or at the bottom:
				</p>
				
				<p>
				<code>[portfolio_slideshow pagerpos=bottom]</code></p> 
		
				<p>or disabled :
				</p>
				
				<p>
				<code>[portfolio_slideshow pagerpos=disabled]</code></p>
				<p>
				
				<p><strong>Toggle thumbnails</strong> can be enabled or disabled:
		
			<code>[portfolio_slideshow togglethumbs=true]</code> or </code> [portfolio_slideshow togglethumbs=false]
				</p>
				
				<p>You can enable the <strong>fancygrid</strong>, which toggles between thumbnail view and slideshow view:
        <code>[portfolio_slideshow fancygrid=true]</code>
				
				<p>
				<strong>Include or exclude slides</strong>:
				</p>
				
				<p>
				<code>[portfolio_slideshow include="1,2,3,4"]</code>
				</p>
				
				<p>
				<code>[portfolio_slideshow exclude="1,2,3,4"]</code>
				</p>
				
		', 'portfolio-slideshow-pro' );?>
				
		<?php _e( '<p>You need to specify the attachment ID, which you can find in your ', 'portfolio-slideshow-pro' );?><a href="<?php bloginfo( 'wpurl' )?>/wp-admin/upload.php"><?php _e( 'Media Library</a> by hovering over the thumbnail. You can only include attachments which are attached to the current post. Do not use these attributes simultaneously, they are mutually exclusive.</p>
		
				<p>There is also an "Exclude from slideshow" checkbox in the image uploader. Instead of including or excluding slides from your slideshow, you may want to consider adding a ', 'portfolio-slideshow-pro' );?><a href="<?php bloginfo( 'wpurl' )?>/wp-admin/edit.php?post_type=portfolio_slideshow"><?php _e( 'standalone slideshow</a> with just the images you need and inserting that instead.</p>
				
				<p>
				<strong>Multiple slideshows per post/page</strong>:
				</p>
				
				<p>
				You can insert as many slideshows as you want in a single post or page by using the include/exclude attributes, or by creating multiple standalone slideshows.</p>
				</p>
				
				<h3 id="popup">Popup shortcode</h3>
				<p>It\'s possible to create a link to a popup window that includes a slideshow with the shortcode <code>[popup-slideshow id=xx text="Link text goes here"]</code>. This shortcode is generated when you upload images using the standalone slideshow builder in the WordPress dashboard sidebar. Slideshow ID and link text are the only required attributes, but the shorcode supports several configuration options.</p>
				
				<p><strong>Window height</strong>: customize the height of the popup window:</p>
				<p><code>[popup-slideshow windowheight=600]</code></p>
				
				<p><strong>Window width</strong>: customize the width of the popup window:</p>
				<p><code>[popup-slideshow windowwidth=800]</code></p>
				
				<p><strong>Height</strong>: customize the height of the slideshow inside the popup window:</p>
				<p><code>[popup-slideshow height=600]</code></p>
				
				<p><strong>Width</strong>: customize the width of the slideshow inside the popup window:</p>
				<p><code>[popup-slideshow width=800]</code></p>
				
				<p><strong>Slideheight</strong>: create a minimum fixed height for the slides inside the slideshow area (prevents items below the slideshow from adjusting to the height of the slideshow)</p>
				<p><code>[popup-slideshow slideheight=400]</code></p>
				
				<p><strong>Center the slideshow</strong></p>
				<p><code>[popup-slideshow centered=true]</code></p>
								
				<p><strong>Enable the carousel pager</strong></p>
				<p><code>[popup-slideshow carousel=true]</code> or <code>[popup-slideshow carousel=false]</code></p>
				
				<p>Note that the carousel pager is the only pager available in popup mode.</p>
				
				<p><strong>Carousel size</strong>: the number of thumbs to show in the carousel</p>
				<p><code>[popup-slideshow carouselsize=6]</code></p>
				
				<p><strong>Captions, descriptions, and titles</strong></p>
				<p><code>[popup-slideshow showcaps=true showdesc=true showtitles=true]</code></p>
				
				<p><strong>Autoplay</strong></p>
				<p><code>[popup-slideshow autoplay=true]</code></p>
				
				<p><strong>Navigation style</strong></p>
				<p><code>[popup-slideshow navstyle=graphical]</code> or <code>[popup-slideshow navstyle=text]</code></p>
				
				<p><strong>Navigation position</strong></p>
				<p><code>[popup-slideshow navpos=top]</code> or <code>[popup-slideshow navpos=bottom]</code> or <code>[popup-slideshow navpos=disabled]</code></p>
				
				<h3 id="video">Working with video links</h3> 
				
				<p>Portfolio Slideshow Pro allows you to link thumbnail images directly to YouTube and Vimeo links and have those videos open in a lightbox window. To use this feature, the slideshow must use the "Custom URL" feature. You can enable this feature globally in the slideshow settings panel by changing "Clicking on a slideshow image" to "Opens a custom URL", or you can enable it in the shortcode with the shortcode attribute <code>click=openurl</code>. Next, just add a link directly to your YouTube or Vimeo video in the "Portfolio Slideshow slide link URL" field when you upload your images. It probably makes sense to use a screenshot or thumbnail from your video as the image you\'re linking <em>from</em>.</p>
				<p>It is possible to customize the size of the lightbox window by appending width and height variables to the video URL. The variable format is <code>ww</code> for window width and <code>wh</code> for window height, like this:<br />
				http://vimeo.com/20679115<strong>?ww=800&wh=600</strong><br />
				http://www.youtube.com/watch?v=mddw-cn-mOU<strong>&ww=800&wh=600</strong></p>', 'portfolio-slideshow-pro' );?>
