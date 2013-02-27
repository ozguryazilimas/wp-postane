<?php 
global $options;
$options = get_option( 'portfolio_slideshow_options' );

$cachedir = WP_CONTENT_DIR . '/cache';

$psp_errors = array();

if ( !is_dir( $cachedir ) ) { 
	$psp_errors[] = __( 'The plugin was not able to create the cache directory required for thumbnail and custom image size generation. Please see the <a href="http://madebyraygun.com/support/faq/why-arent-my-thumbnail-and-custom-sized-images-being-generated/">FAQ</a> for instructions on how to resolve this issue.', 'portfolio-slideshow-pro' ); 
}

if ( ! function_exists('gd_info')) {
	$psp_errors[] = __( 'Your server does not support the GD function. You may have trouble running some thumbnail related functions in this plugin.', 'portfolio-slideshow-pro' ); 
}

if ( PHP_VERSION > 5 ) { //don't bother showing this error message unless the server is running PHP5 or above
	if ( empty( $options['license'] ) ) {
		$psp_errors[] = __( 'The plugin will not be registered for auto-updates until you enter your email address in the "Diagnostic" section below.', 'portfolio-slideshow-pro' ); 
	}
}

if ( PHP_VERSION < 5 ) {
	$psp_errors[] = __( 'Your server is running an out-of-date version of PHP and the plugin cannot auto-update. It is strongly recommended that you update your web server to the current version of PHP.', 'portfolio-slideshow-pro' ); 
}

// Add a menu for our option page
add_action('admin_menu', 'portfolio_slideshow_pro_add_page');
function portfolio_slideshow_pro_add_page() {
	add_options_page( 'Portfolio Slideshow Pro', 'Portfolio Slideshow Pro', 'manage_options', 'portfolio_slideshow', 'portfolio_slideshow_pro_option_page' );
}


// Draw the option page
function portfolio_slideshow_pro_option_page() {
global $psp_errors;	
$options = get_option( 'portfolio_slideshow_settings' );

// set up some defaults if these fields are empty
?>
	<div class="wrap" style="max-width:880px;">
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1"><?php _e( 'Slideshow Settings', 'portfolio-slideshow-pro' );?></a></li>
			<li><a href="#tabs-2"><?php _e( 'Documentation', 'portfolio-slideshow-pro' );?></a></li>
		</ul>
		
		<div id="tabs-1">
		
		<?php screen_icon(); ?>
		<h2><?php _e( 'Portfolio Slideshow Pro', 'portfolio-slideshow-pro' );?></h2>

		<?php if ( $psp_errors ) {
			foreach ( $psp_errors as $error ) { 
				echo '<div id="message" class="updated fade">
					<p>' . $error . '</p>
					</div>';	
			}
		}?>

		<form action="options.php" method="post">
			<?php settings_fields('portfolio_slideshow_options'); ?>
			<?php do_settings_sections('portfolio_slideshow'); ?>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'portfolio-slideshow-pro') ?>" />
			</p>
		</form>
		
		</div><!--#tabs-1-->
				
		<div id="tabs-2" class="documentation">
		
		<?php require('documentation.php'); ?>
			
		</div><!--#tabs-2-->
		</div><!--#tabs-->

		<a href="http://madebyraygun.com"><img style="margin-top:30px;" src="<?php echo plugins_url( 'img/logo.png', __FILE__ );?>" width="225" height="70" alt="Made by Raygun" /></a>
		<p>You're using Portfolio Slideshow, made by <a href="http://madebyraygun.com">Raygun</a>. Check out our <a href="http://madebyraygun.com/lab/" target="_blank">other plugins</a>, and if you have any problems, stop by our <a href="http://madebyraygun.com/support/forum/" target="_blank">support forum</a>!</p>
		</div>
	<?php
}

// Register and define the settings
add_action('admin_init', 'portfolio_slideshow_pro_admin_init');
function portfolio_slideshow_pro_admin_init(){
	register_setting(
		'portfolio_slideshow_options',
		'portfolio_slideshow_options',
		'portfolio_slideshow_pro_validate_options'
	);
	add_settings_section(
		'portfolio_slideshow_pro_display',
		'Slideshow Display',
		'portfolio_slideshow_pro_section_text',
		'portfolio_slideshow'
	);
	
	add_settings_section(
		'portfolio_slideshow_pro_behavior',
		'Slideshow Behavior',
		'portfolio_slideshow_pro_section_text',
		'portfolio_slideshow'
	);
	
	add_settings_section(
		'portfolio_slideshow_pro_navigation',
		'Slideshow Navigation',
		'portfolio_slideshow_pro_section_text',
		'portfolio_slideshow'
	);
	
	add_settings_section(
		'portfolio_slideshow_pro_diagnostic',
		'Slideshow Diagnostic',
		'portfolio_slideshow_pro_section_text',
		'portfolio_slideshow'
	);
	
	add_settings_field(
		'portfolio_slideshow_size',
		__( 'Slideshow size <span class="vtip" title="You can change the default image sizes in the Media Settings control panel, or add a new custom image size of your own. If you change the settings for an existing (WordPress built-in) image size, you will need to regenerate your thumbnails to see the changes reflected in existing images. Search the WordPress plugin repository for the Regenerate Thumbnails plugin for information on how to do this.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_size_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_display'
	);


	add_settings_field(
		'portfolio_slideshow_customsize',
		__( 'Custom size: <span class="vtip" title="The size in pixels of your new default image size. This can be overridden in the shortcode with the height and width attributes, e.g.: [portfolio_slideshow width=500].">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_customsize_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_display'
	);
	
	add_settings_field(
		'portfolio_slideshow_trans',
		__( 'Transition FX <span class="vtip" title="You can override these in the shortcode with any option that the jQuery Cycle plugin supports. Note: The scrollHorz transition does not work well with fluid-layout themes such as TwentyEleven.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_trans_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_display'
	);
		
	add_settings_field(
		'portfolio_slideshow_speed',
		__( 'Transition speed <span class="vtip" title="How long should the transition last when the slideshow is advanced? Enter in milliseconds, e.g. 400 = 0.4 seconds per transition."?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_speed_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_display'
	);	
	
	add_settings_field(
		'portfolio_slideshow_showtitles',
		__( 'Show titles, captions, & descriptions', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_showtitles_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_display'
	);	

	add_settings_field(
		'portfolio_slideshow_centered',
		__( 'Center slideshow <span class="vtip" title="Centers slideshow, nav, &amp; pager within the parent content area.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_centered_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_display'
	);	

	add_settings_field(
		'portfolio_slideshow_allowfluid',
		__( 'Support for fluid layouts <span class="vtip" title="If you have a fluid layout (like TwentyEleven) and want your slideshows to resize dynamically, leave this checked. If you have a fixed-width layout and notice the content area shifting before the slideshow loads, you can uncheck this to prevent that.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_allowfluid_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_display'
	);	


	add_settings_field(
		'portfolio_slideshow_timeout',
		__( 'Slideshow timing <span class="vtip" title="How long should each slide be displayed during an automatic slideshow? Enter in milliseconds, e.g. 3000 = 3 seconds per slide.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_timeout_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	add_settings_field(
		'portfolio_slideshow_autoplay',
		__( 'Enable autoplay <span class="vtip" title="Starts slideshows automatically when the page is loaded.">?</span>','portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_autoplay_input', 
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	add_settings_field(
		'portfolio_slideshow_random',
		__( 'Randomize slideshow <span class="vtip" title="Play the slideshow back in a random order.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_random_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	add_settings_field(
		'portfolio_slideshow_exclude_featured',
		__( 'Exclude featured images from slideshow <span class="vtip" title="If you use the featured image function to create gallery thumbnails but don\'t want those images to appear in the slideshow, use this option.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_exclude_featured_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	
	add_settings_field(
		'portfolio_slideshow_showloader',
		__( 'Show loading<br />animation <span class="vtip" title="If you\'ve got a slow connection or lots of images, sometimes the slideshow can take a little while to load. Selecting this option will include a loading gif to show that something is happening. You can customize the placement of the loading gif with CSS.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_showloader_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	
	add_settings_field(
		'portfolio_slideshow_nowrap',
		__( 'Disable slideshow wrapping <span class="vtip" title="Should the slideshow play through to the beginning after it gets to the end, or simply stop?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_nowrap_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	add_settings_field(
		'portfolio_slideshow_click',
		__( 'Clicking on a<br />slideshow image: <span class="vtip" title="URLs for the <em>Opens a custom URL</em> option are set in the image uploader. The lighbox option links to the full-size version of the image, so make sure your full-size images aren\'t too big.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_click_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	add_settings_field(
		'portfolio_slideshow_click_target',
		__( 'New URL opens in:', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_click_target_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	add_settings_field(
		'portfolio_slideshow_showhash',
		__( 'Update URL with slide IDs <span class="vtip" title="You can enable this feature to udpate the URL of the page with the slide ID number, e.g: http://example.com/slideshow/#3 will link directly to the third slide in the slideshow.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_showhash_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_behavior'
	);
	
	add_settings_field(
		'portfolio_slideshow_navstyle',
		__( 'Navigation style: <span class="vtip" title="What kind of navigation would you like to use?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_navstyle_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	

	add_settings_field(
		'portfolio_slideshow_navoptions',
		__( 'Navigation display options:', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_navoptions_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	add_settings_field(
		'portfolio_slideshow_enhanced_navigation',
		__( 'Enhanced navigation:', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_enhanced_navigation_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	add_settings_field(
		'portfolio_slideshow_navpos',
		__( 'Navigation position <span class="vtip" title="Where should the navigation controls appear?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_navpos_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	add_settings_field(
		'portfolio_slideshow_pagerstyle',
		__( 'Pager style <span class="vtip" title="Which type of slideshow pager would you like to use?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_pagerstyle_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	add_settings_field(
		'portfolio_slideshow_thumbsize',
		__( 'Thumbnail size <span class="vtip" title="You can specify the size of the thumbnails in the pager. (px)">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_thumbsize_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	add_settings_field(
		'portfolio_slideshow_thumbnailmargin',
		__( 'Thumbnail margin <span class="vtip" title="How much space between each thumbnail? (px)">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_thumbnailmargin_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	
	add_settings_field(
		'portfolio_slideshow_togglethumbs',
		__( 'Thumbnail toggle <span class="vtip" title="Hides thumbnails by default with an option to show them.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_togglethumbs_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	add_settings_field(
		'portfolio_slideshow_proportionalthumbs',
		__( 'Proportional thumbnails <span class="vtip" title="Preserve thumbnail aspect ratio.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_proportionalthumbs_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
		
	add_settings_field(
		'portfolio_slideshow_carouselsize',
		__( 'Number of thumbs per row <span class="vtip" title="Number of thumbnails to display per row.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_carouselsize_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
		
	add_settings_field(
		'portfolio_slideshow_carousel_thumbsize',
		__( 'Carousel thumbnail size <span class="vtip" title="What size should we display the image thumbnails in the carousel?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_carousel_thumbsize_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	add_settings_field(
		'portfolio_slideshow_carousel_thumbnailmargin',
		__( 'Carousel thumbnail margin <span class="vtip" title="How much space between each thumbnail? (px)">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_carousel_thumbnailmargin_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	
	
	
	add_settings_field(
		'portfolio_slideshow_pagerpos',
		__( 'Pager position <span class="vtip" title="Where should the slideshow pager appear?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_pagerpos_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_navigation'
	);	


add_settings_field(
	'portfolio_slideshow_fancygrid',
	__( 'Enable Fancy Grid <span class="vtip" title="The fancy grid toggles between a thumbnail view and a slideshow view.">?</span>', 'portfolio-slideshow-pro' ),
	'portfolio_slideshow_pro_fancygrid_input',
	'portfolio_slideshow',
	'portfolio_slideshow_pro_navigation'
);	
	
	add_settings_field(
		'portfolio_slideshow_license',
		__( 'Email address <span class="vtip" title="The email address you used to purchase the plugin.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_license_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_diagnostic'
	);
	
	add_settings_field(
		'portfolio_slideshow_jquery',
		__( 'jQuery version <span class="vtip" title="If you\'re having trouble with the Javascript effects, you can try an older version of jQuery, or disable it altogether. This sometimes helps if you have plugins or themes that rely on their own version of jQuery. For best results, leave this at the default setting.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_jquery_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_diagnostic'
	);
	
	add_settings_field(
		'portfolio_slideshow_fancybox',
		__( 'Load Fancybox <span class="vtip" title="Should we load the Fancybox library for image zoom, or do you have your own plugin for that?">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_fancybox_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_diagnostic'
	);
	
	add_settings_field(
		'portfolio_slideshow_cycle',
		__( 'Load Cycle <span class="vtip" title="If another plugin is loading a conflicting Cycle library, you can try disabling ours.">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_cycle_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_diagnostic'
	);

		add_settings_field(
		'portfolio_slideshow_debug',
		__( 'Enable debug mode <span class="vtip" title="Don\'t load minified scripts and possibly output debug info to screen to help troubleshoot issues with the plugin">?</span>', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_debug_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_diagnostic'
	);


	add_settings_field(
		'portfolio_slideshow_pro_version',
		__( 'Version', 'portfolio-slideshow-pro' ),
		'portfolio_slideshow_pro_version_input',
		'portfolio_slideshow',
		'portfolio_slideshow_pro_diagnostic'
	);
}

// Draw the section header
function portfolio_slideshow_pro_section_text() {
}

// Display and fill the form fields

function portfolio_slideshow_pro_size_input() {
	$options = get_option( 'portfolio_slideshow_options' );
		
	echo "<select name='portfolio_slideshow_options[size]' id='portfolio_slideshow_options_size'  value='" . $options['size'] . "' />";
	ps_get_image_sizes();
	echo "</select></li>";
}

function portfolio_slideshow_pro_customsize_input() {
	$options = get_option( 'portfolio_slideshow_options' );
	
	echo "<input name='portfolio_slideshow_options[customwidth]' type='text' size='5' value='$options[customwidth]' /> <span>" . __( 'width (px)', 'portfolio-slideshow-pro' ) . "</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name='portfolio_slideshow_options[customheight]' type='text' size='5' value='$options[customheight]' /> <span>" . __( 'height (px)', 'portfolio-slideshow-pro' ) . "</span>";
}

function portfolio_slideshow_pro_trans_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select name="portfolio_slideshow_options[trans]" value="<?php echo $options[trans]; ?>" />
					<option value="fade" <?php if ( $options['trans'] == 'fade' ) echo " selected='selected'";?>><?php _e( 'fade', 'portfolio-slideshow-pro' );?></option>
					<option value="scrollHorz" <?php if ( $options['trans'] == 'scrollHorz' ) echo " selected='selected'";?>><?php _e( 'horizontal scroll', 'portfolio-slideshow-pro' );?></option>
				</select>
<?php }

function portfolio_slideshow_pro_speed_input() {
	$options = get_option( 'portfolio_slideshow_options' );
	echo "<input id='speed' name='portfolio_slideshow_options[speed]' type='text' size='5' value='$options[speed]' />";
}

function portfolio_slideshow_pro_showtitles_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[showtitles]" value="true" <?php checked( "true", $options['showtitles'] ); ?> />
 	<span><?php _e( 'titles', 'portfolio-slideshow-pro' );?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="portfolio_slideshow_options[showcaps]" value="true" <?php checked( "true", $options['showcaps'] ); ?> />
 	<span><?php _e( 'captions', 'portfolio-slideshow-pro' );?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="portfolio_slideshow_options[showdesc]" value="true" <?php checked( "true", $options['showdesc'] ); ?> />
 	<span><?php _e( 'descriptions', 'portfolio-slideshow-pro' );?></span>
<?php }

function portfolio_slideshow_pro_centered_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[centered]" value="true" <?php checked( "true", $options['centered'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_allowfluid_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	<input type="checkbox" name="portfolio_slideshow_options[allowfluid]" value="true" <?php checked( "true", $options['allowfluid'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_timeout_input() {
	// get option 'text_string' value from the database
	$options = get_option( 'portfolio_slideshow_options' );
		
	// echo the field
	echo "<input id='version' name='portfolio_slideshow_options[timeout]' type='text' size='5' value='$options[timeout]' />";
}

function portfolio_slideshow_pro_autoplay_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[autoplay]" value="true" <?php checked( "true", $options['autoplay'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_random_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[random]" value="true" <?php checked( "true", $options['random'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_exclude_featured_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[exclude_featured]" value="true" <?php checked( "true", $options['exclude_featured'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_showloader_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[showloader]" value="true" <?php checked( "true", $options['showloader'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_nowrap_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[nowrap]" value="true" <?php checked( "true", $options['nowrap'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_click_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select id="portfolio_slideshow_options_click" name="portfolio_slideshow_options[click]" value="<?php echo $options[click]; ?>" />
		<option value="advance" <?php if ( $options['click'] == "advance" ) echo " selected='selected'";?>><?php _e( 'Advances the slideshow', 'portfolio-slideshow-pro' );?></option>
		<option value="openurl" <?php if ( $options['click'] == "openurl" ) echo " selected='selected'";?>><?php _e( 'Opens a custom URL', 'portfolio-slideshow-pro' );?></option>
		<option value="lightbox" <?php if ( $options['click'] == "lightbox" ) echo " selected='selected'";?>><?php _e( 'Opens the image in a lightbox', 'portfolio-slideshow-pro' );?></option>
		<option value="none" <?php if ( $options['click'] == "none" ) echo " selected='selected'";?>><?php _e( 'Does nothing', 'portfolio-slideshow-pro' );?></option>
	</select>
<?php }

function portfolio_slideshow_pro_click_target_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select name="portfolio_slideshow_options[click_target]" value="<?php echo $options[click]; ?>" />
		<option value="_self" <?php if ( $options['click_target'] == "_self" ) echo " selected='selected'";?>><?php _e( 'Same window', 'portfolio-slideshow-pro' );?></option>
		<option value="_blank" <?php if ( $options['click_target'] == "_blank" ) echo " selected='selected'";?>><?php _e( 'New window', 'portfolio-slideshow-pro' );?></option>
		<option value="_parent" <?php if ( $options['click_target'] == "_parent" ) echo " selected='selected'";?>><?php _e( 'Parent window', 'portfolio-slideshow-pro' );?></option>
	</select>
<?php }

function portfolio_slideshow_pro_showhash_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[showhash]" value="true" <?php checked( "true", $options['showhash'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_navstyle_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select name="portfolio_slideshow_options[navstyle]" value="<?php echo $options[navstyle]; ?>" />
		<option value="text" <?php if ( $options['navstyle'] == "text" ) echo " selected='selected'";?>><?php _e( 'text', 'portfolio-slideshow-pro' );?></option>
		<option value="graphical" <?php if ( $options['navstyle'] == "graphical" ) echo " selected='selected'";?>><?php _e( 'graphical', 'portfolio-slideshow-pro' );?></option>
	</select>
<?php }

function portfolio_slideshow_pro_navoptions_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[showplay]" value="true" <?php checked( "true", $options['showplay'] ); ?> />
 	<span><?php _e( 'show play button', 'portfolio-slideshow-pro' );?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="portfolio_slideshow_options[showinfo]" value="true" <?php checked( "true", $options['showinfo'] ); ?> />
 	<span><?php _e( 'show slide numbers', 'portfolio-slideshow-pro' );?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name='portfolio_slideshow_options[infotxt]' type='text' size='6' value=<?php echo $options['infotxt']?> /> <span>(1 <?php echo $options['infotxt'];?> 12)</span>
<?php }


function portfolio_slideshow_pro_enhanced_navigation_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[touchswipe]" value="true" <?php checked( "true", $options['touchswipe'] ); ?> />
 	<span><?php _e( 'Enable touch/swipe controls', 'portfolio-slideshow-pro' );?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="portfolio_slideshow_options[keyboardnav]" value="true" <?php checked( "true", $options['keyboardnav'] ); ?> />
 	<span><?php _e( 'Enable keyboard navigation', 'portfolio-slideshow-pro' );?></span>
<?php }


function portfolio_slideshow_pro_navpos_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select name="portfolio_slideshow_options[navpos]" value="<?php echo $options[navpos]; ?>" />
		<option value="top" <?php if ($options['navpos'] == 'top' ) echo " selected='selected'";?>><?php _e( 'top', 'portfolio-slideshow-pro' );?></option>
		<option value="bottom" <?php if ($options['navpos'] == 'bottom' ) echo " selected='selected'";?>><?php _e( 'bottom', 'portfolio-slideshow-pro' );?></option>
		<option value="disabled" <?php if ($options['navpos'] == 'disabled' ) echo " selected='selected'";?>><?php _e( 'disabled', 'portfolio-slideshow-pro' );?></option>
	</select>
<?php }

function portfolio_slideshow_pro_pagerstyle_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select id="portfolio_slideshow_options_pagerstyle" name="portfolio_slideshow_options[pagerstyle]" value="<?php echo $options[pagerstyle]; ?>" />
		<option value="thumbs" <?php if ( $options['pagerstyle'] == "thumbs" ) echo " selected='selected'";?>><?php _e( 'thumbs', 'portfolio-slideshow-pro' );?></option>
		<option value="carousel" <?php if ( $options['pagerstyle'] == "carousel" ) echo " selected='selected'";?>><?php _e( 'carousel', 'portfolio-slideshow-pro' );?></option>
		<option value="numbers" <?php if ( $options['pagerstyle'] == "numbers" ) echo " selected='selected'";?>><?php _e( 'numbers', 'portfolio-slideshow-pro' );?></option>
		<option value="bullets" <?php if ( $options['pagerstyle'] == "bullets" ) echo " selected='selected'";?>><?php _e( 'bullets', 'portfolio-slideshow-pro' );?></option>
	</select>
<?php }

function portfolio_slideshow_pro_thumbsize_input() {
	$options = get_option( 'portfolio_slideshow_options' );
	
	echo "<input id='thumbsize' name='portfolio_slideshow_options[thumbsize]' type='text' size='5' value='$options[thumbsize]' />";
}


function portfolio_slideshow_pro_thumbnailmargin_input() {
	$options = get_option( 'portfolio_slideshow_options' );
	
	echo "<input id='thumbnailmargin' name='portfolio_slideshow_options[thumbnailmargin]' type='text' size='5' value='$options[thumbnailmargin]' />";
}

function portfolio_slideshow_pro_togglethumbs_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[togglethumbs]" value="true" <?php checked( "true", $options['togglethumbs'] ); ?> />
 
<?php }


function portfolio_slideshow_pro_proportionalthumbs_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input id="proportionalthumbs" type="checkbox" name="portfolio_slideshow_options[proportionalthumbs]" value="true" <?php checked( "true", $options['proportionalthumbs'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_carousel_thumbsize_input() {
	$options = get_option( 'portfolio_slideshow_options' );
	
	echo "<input id='carousel-thumbsize' name='portfolio_slideshow_options[carousel_thumbsize]' type='text' size='5' value='$options[carousel_thumbsize]' />";
}


function portfolio_slideshow_pro_carousel_thumbnailmargin_input() {
	$options = get_option( 'portfolio_slideshow_options' );
	
	echo "<input id='carousel-thumbnailmargin' name='portfolio_slideshow_options[carousel_thumbnailmargin]' type='text' size='5' value='$options[carousel_thumbnailmargin]' />";
}


function portfolio_slideshow_pro_carouselsize_input() {
	$options = get_option( 'portfolio_slideshow_options' );

	echo "<input id='carouselsize' name='portfolio_slideshow_options[carouselsize]' type='text' size='2' value='$options[carouselsize]' />";
}


function portfolio_slideshow_pro_pagerpos_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select name="portfolio_slideshow_options[pagerpos]" value="<?php echo $options[pagerpos]; ?>" />
		<option value="top" <?php if ( $options['pagerpos'] == 'top' ) echo " selected='selected'";?>><?php _e( 'top', 'portfolio-slideshow-pro' );?></option>
		<option value="bottom" <?php if ( $options['pagerpos'] == 'bottom' ) echo " selected='selected'";?>><?php _e( 'bottom', 'portfolio-slideshow-pro' );?></option>
		<option value="disabled" <?php if ( $options['pagerpos'] == 'disabled' ) echo " selected='selected'";?>><?php _e( 'disabled', 'portfolio-slideshow-pro' );?></option>
	</select>
<?php }

function portfolio_slideshow_pro_fancygrid_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[fancygrid]" value="true" <?php checked( "true", $options['fancygrid'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_license_input() {
	$options = get_option( 'portfolio_slideshow_options' );
	
	if ( PHP_VERSION > 5 ) {
	
	echo "<input name='portfolio_slideshow_options[license]' type='text' size='50' value='$options[license]' /><span><br />" . __( 'The email address you used to purchase the plugin is your license key to enable automatic updates.', 'portfolio-slideshow-pro' ) . "</span>";
	
	} 
}
				
function portfolio_slideshow_pro_jquery_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<select name="portfolio_slideshow_options[jquery]" value="<?php echo $options[jquery]; ?>" />
		<option value="1.7.1" <?php if ( $options['jquery'] == "1.7.1" ) echo " selected='selected'";?>>1.7.1</option>
		<option value="1.4.4" <?php if ( $options['jquery'] == "1.4.4" ) echo " selected='selected'";?>>1.4.4</option>
		<option value="disabled" <?php if ( $options['jquery'] == "disabled" ) echo " selected='selected'";?>>disabled</option>
	</select>
<?php }


function portfolio_slideshow_pro_debug_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[debug]" value="true" <?php checked( "true", $options['debug'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_fancybox_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[fancybox]" value="true" <?php checked( "true", $options['fancybox'] ); ?> />
 
<?php }

function portfolio_slideshow_pro_cycle_input() {
	$options = get_option( 'portfolio_slideshow_options' );?>
	
	<input type="checkbox" name="portfolio_slideshow_options[cycle]" value="true" <?php checked( "true", $options['cycle'] ); ?> />
 
<?php }
				
function portfolio_slideshow_pro_version_input() {
	// get option 'text_string' value from the database
	$options = get_option( 'portfolio_slideshow_options' );
		
	// echo the field
	echo "<input type='text' readonly='readonly' id='version' name='portfolio_slideshow_options[version]' type='text' value='$options[version]' />";
}

// Validate user input
function portfolio_slideshow_pro_validate_options( $input ) {
	
	if ( ! $input['speed'] )
    $input['speed'] = '400';
    
  if ( ! $input['infotxt'] )
    $input['infotxt'] = 'of';
    
  if ( ! $input['timeout'] )
    $input['timeout'] = '3000';
    
  if ( ! $input['thumbsize'] )
  	$input['thumbsize'] = '75';
    
  if ( ! $input['thumbnailmargin'] )
    $input['thumbnailmargin'] = '8';
    
  if ( ! $input['carousel-thumbsize'] )
    $input['carousel-thumbsize'] = '75';
    
  if ( ! $input['carousel-thumbnailmargin'] )
    $input['carousel-thumbnailmargin'] = '8';
    
  if ( ! $input['carouselsize'] )
    $input['carouselsize'] = '7';  
	
	if ( ! $input['carousel_thumbsize'] )
  	$input['carousel_thumbsize'] = '75'; 

	if ( ! $input['carousel_thumbnailmargin'] )
		$input['carousel_thumbnailmargin'] = '8'; 
		
	if ( ! isset( $input['showtitles'] ) )
    $input['showtitles'] = null;
		$input['showtitles'] = ( $input['showtitles'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['showcaps'] ) )
    $input['showcaps'] = null;
		$input['showcaps'] = ( $input['showcaps'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['showdesc'] ) )
    $input['showdesc'] = null;
		$input['showdesc'] = ( $input['showdesc'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['centered'] ) )
    $input['centered'] = null;
		$input['centered'] = ( $input['centered'] == "true" ? "true" : "false" );

	if ( ! isset( $input['allowfluid'] ) )
    $input['allowfluid'] = null;
		$input['allowfluid'] = ( $input['allowfluid'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['autoplay'] ) )
    $input['autoplay'] = null;
		$input['autoplay'] = ( $input['autoplay'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['random'] ) )
    $input['random'] = null;
		$input['random'] = ( $input['random'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['exclude_featured'] ) )
    $input['exclude_featured'] = null;
		$input['exclude_featured'] = ( $input['exclude_featured'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['showloader'] ) )
    $input['showloader'] = null;
		$input['showloader'] = ( $input['showloader'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['showhash'] ) )
    $input['showhash'] = null;
		$input['showhash'] = ( $input['showhash'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['nowrap'] ) )
    $input['nowrap'] = null;
	$input['nowrap'] = ( $input['nowrap'] == "true" ? "true" : "0" );
	
	if ( ! isset( $input['togglethumbs'] ) )
    $input['togglethumbs'] = null;
	$input['togglethumbs'] = ( $input['togglethumbs'] == "true" ? "true" : "false" );

	if ( ! isset( $input['proportionalthumbs'] ) )
  	$input['proportionalthumbs'] = null;
		$input['proportionalthumbs'] = ( $input['proportionalthumbs'] == "true" ? "true" : "false" );

	if ( ! isset( $input['fancygrid'] ) )
  	$input['fancygrid'] = null;
		$input['fancygrid'] = ( $input['fancygrid'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['showplay'] ) )
    $input['showplay'] = null;
		$input['showplay'] = ( $input['showplay'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['showinfo'] ) )
    $input['showinfo'] = null;
		$input['showinfo'] = ( $input['showinfo'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['touchswipe'] ) )
    $input['touchswipe'] = null;
		$input['touchswipe'] = ( $input['touchswipe'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['keyboardnav'] ) )
    $input['keyboardnav'] = null;
		$input['keyboardnav'] = ( $input['keyboardnav'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['fancybox'] ) )
    $input['fancybox'] = null;
		$input['fancybox'] = ( $input['fancybox'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['cycle'] ) )
    $input['cycle'] = null;
		$input['cycle'] = ( $input['cycle'] == "true" ? "true" : "false" );
	
	if ( ! isset( $input['debug'] ) )
    $input['debug'] = null;
		$input['debug'] = ( $input['debug'] == "true" ? "true" : "false" );

	if ( ! isset( $input['disable_thumbs'] ) )
	  $input['disable_thumbs'] = null;
		$input['disable_thumbs'] = ( $input['disable_thumbs'] == "true" ? "true" : "false" );
	
	$input['timeout'] =  wp_filter_nohtml_kses($input['timeout']); // Sanitize textbox input 

	return $input;

}
