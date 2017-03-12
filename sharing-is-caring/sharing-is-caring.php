<?php
/*
Plugin Name: Sharing is Caring Plugin
Plugin URI: http://michaelbea.com/sharing-is-caring/
Description: Displays the social widgets from Facebook, Twitter, Google+ and Pinterest with your posts.  Most options for the buttons are customizable in the admin panel.  Also adds some meta tags for opengraph and schema.org.
Version: 1.4.3
Author: Michael Beacom
Author URI: http://michaelbea.com
License: GPL2

Copyright 2012 Michael Beacom  (email : michael.beacom@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Deactives the plugin if wordpress version is less than 3.3
function sic_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.3", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'sic_wordpress_version' );

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'sic_add_defaults');
register_uninstall_hook(__FILE__, 'sic_delete_plugin_options');
add_action('admin_init', 'sic_init' );
add_action('admin_menu', 'sic_add_options_page');
add_filter( 'plugin_action_links', 'sic_plugin_action_links', 10, 2 );

// Delete options table entries ONLY when plugin deactivated AND deleted
function sic_delete_plugin_options() {
	delete_option('sic_options');
}

function sic_detect_custom_post_types() {
	$args=array(
		'public'   => true,
		'_builtin' => false
	); 
	$post_types=get_post_types($args); 
	$options_arr=array();
	foreach ($post_types  as &$post_type ) {
		$options_arr["custom_type_$post_type"] = "0";
	}
	return $options_arr;
}


// Define default option settings
function sic_add_defaults() {

	
	$tmp = get_option('sic_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('sic_options'); //(don't think this is needed but leave for now)
		$arr = array(	"fb_position" => "1", // { 1, 0-4 } dropdown
					"fb_app_id" => "", // { '', facebook app id } text (#)
					"fb_width" => "", // { '', #pixels } text (#)
					"fb_layout" => "button_count", // { 'standard', 'button_count', 'box_count' } dropdown
					"fb_show_faces" => "0", // { 0, 0-1 } checkbox
					"fb_action" => "like", // { 'like', 'recommend' } dropdown
					"fb_font" => "arial", // { 'arial', 'lucida grande', 'segoe ui', 'tahoma', 'trebuchet ms', 'verdana' } dropdown
					"fb_colorscheme" => "light", // { 'light', 'dark' } dropdown
					"twitter_position" => "2", // { 2, 0-4 } dropdown
					"twitter_size" => "medium", // { 'medium', 'large' } dropdown
					"twitter_count" => "horizontal", // { 'none', 'horizontal', 'vertical' } dropdown
					"g_plus_position" => "3", // { 3, 0-4 } dropdown
					"g_plus_size" => "medium", // { 'standard', 'small', 'medium', 'tall' } dropdown
					"g_plus_annotation" => "bubble", // { 'none', 'bubble', 'inline' } dropdown
					"pinterest_position" => "4", // { 4, 0-4 } dropdown
					"pinterest_layout" => "horizontal", // { 'none', 'vertical', 'horizontal' } dropdown
					"posts" => "1", // { 1, 0-1 } checkbox
					"pages" => "1", // { 1, 0-1 } checkbox
					"homepage" => "0", // { 0, 0-1 } checkbox
					"categories" => "0", // { 0, 0-1 } checkbox
					"tags" => "0", // { 0, 0-1 } checkbox
					"taxonomies" => "0", // { 0, 0-1 } checkbox
					"dates" => "0", // { 0, 0-1 } checkbox
					"authors" => "0", // { 0, 0-1 } checkbox
					"searches" => "0", // { 0, 0-1 } checkbox
					"attachments" => "0", // { 0, 0-1 } checkbox
					"above_post" => "0", // { 0, 0-1 } checkbox
					"below_post" => "1", // { 1, 0-1 } checkbox
					"css_all" => "", // { '', any css code } text
					"css_each" => "vertical-align:middle; float:left;", // { '', any css code } text
					"content_filter_priority" => "10", // {10, #} text (#)
					"default_image" => "", // { '', url } text (url)
					"default_apple_image" => "", // { '', url } text (url)
					"html5" => "0", // { 0, 0-1 } checkbox
					"box_title" => "", // { '', any text } text
					"box_title_css" => "" // { '', any css code } text 
		);
		$arr=array_merge($arr,sic_detect_custom_post_types());
		update_option('sic_options', $arr);
	}
}

// Init plugin options to white list our options
function sic_init(){
	register_setting( 'sic_plugin_options', 'sic_options', 'sic_validate_options' );
}

// Add admin menu page
function sic_add_options_page() {
	add_options_page('Sharing is Caring Options Page', 'Sharing is Caring', 'manage_options', __FILE__, 'sic_render_form');
}

// Render the admin menu options form
function sic_render_form() {
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Sharing Is Caring</h2>
		<p><em>It's always more fun<br />To share with everyone</em></p>
		<form method="post" action="options.php">
			<?php settings_fields('sic_plugin_options'); ?>
			<?php $options = get_option('sic_options'); ?>
			<table class="form-table">
				<tr><th scope="rowgroup" rowspan="5">Position Options</th>
				
					<td>
						<label for="sic_options[fb_position]">Facebook: </label>
					</td><td>	
						<select name="sic_options[fb_position]">
							<option value="0" <?php selected('0', $options['fb_position']); ?>>Don't display</option>
							<option value="1" <?php selected('1', $options['fb_position']); ?>>Position 1</option>
							<option value="2" <?php selected('2', $options['fb_position']); ?>>Position 2</option>
							<option value="3" <?php selected('3', $options['fb_position']); ?>>Position 3</option>
							<option value="4" <?php selected('4', $options['fb_position']); ?>>Position 4</option>
						</select>
					</td>
				</tr><tr>
					<td>
						<label for="sic_options[twitter_position]">Twitter: </label>
					</td><td>	
						<select name="sic_options[twitter_position]">
							<option value="0" <?php selected('0', $options['twitter_position']); ?>>Don't display</option>
							<option value="1" <?php selected('1', $options['twitter_position']); ?>>Position 1</option>
							<option value="2" <?php selected('2', $options['twitter_position']); ?>>Position 2</option>
							<option value="3" <?php selected('3', $options['twitter_position']); ?>>Position 3</option>
							<option value="4" <?php selected('4', $options['twitter_position']); ?>>Position 4</option>
						</select>
					</td>
				</tr><tr>
					<td>
						<label for="sic_options[g_plus_position]">Google+: </label>
					</td><td>
						<select name="sic_options[g_plus_position]">
							<option value="0" <?php selected('0', $options['g_plus_position']); ?>>Don't display</option>
							<option value="1" <?php selected('1', $options['g_plus_position']); ?>>Position 1</option>
							<option value="2" <?php selected('2', $options['g_plus_position']); ?>>Position 2</option>
							<option value="3" <?php selected('3', $options['g_plus_position']); ?>>Position 3</option>
							<option value="4" <?php selected('4', $options['g_plus_position']); ?>>Position 4</option>
						</select>
					</td>
				</tr><tr>
					<td>
						<label for="sic_options[pinterest_position]">Pinterest: </label>
					</td><td>	
						<select name="sic_options[pinterest_position]">
							<option value="0" <?php selected('0', $options['pinterest_position']); ?>>Don't display</option>
							<option value="1" <?php selected('1', $options['pinterest_position']); ?>>Position 1</option>
							<option value="2" <?php selected('2', $options['pinterest_position']); ?>>Position 2</option>
							<option value="3" <?php selected('3', $options['pinterest_position']); ?>>Position 3</option>
							<option value="4" <?php selected('4', $options['pinterest_position']); ?>>Position 4</option>
						</select>
					</td>
				</tr><tr>
					<td colspan="2">
	
						<em>(Make sure you specify different positions for each.)</em>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">Display Options</th>
					<td>
						<label><input name="sic_options[posts]" type="checkbox" value="1" <?php if (isset($options['posts'])) { checked('1', $options['posts']); } ?> /> Display on posts</label><br />
						<label><input name="sic_options[pages]" type="checkbox" value="1" <?php if (isset($options['pages'])) { checked('1', $options['pages']); } ?> /> Display on pages</label><br />
						<label><input name="sic_options[homepage]" type="checkbox" value="1" <?php if (isset($options['homepage'])) { checked('1', $options['homepage']); } ?> /> Display on homepage</label><br />
						<label><input name="sic_options[categories]" type="checkbox" value="1" <?php if (isset($options['categories'])) { checked('1', $options['categories']); } ?> /> Display on categories</label><br />
						<label><input name="sic_options[tags]" type="checkbox" value="1" <?php if (isset($options['tags'])) { checked('1', $options['tags']); } ?> /> Display on tags</label><br />
						<label><input name="sic_options[taxonomies]" type="checkbox" value="1" <?php if (isset($options['taxonomies'])) { checked('1', $options['taxonomies']); } ?> /> Display on taxonomies</label><br />
						<label><input name="sic_options[dates]" type="checkbox" value="1" <?php if (isset($options['dates'])) { checked('1', $options['dates']); } ?> /> Display on dates</label><br />
						<label><input name="sic_options[authors]" type="checkbox" value="1" <?php if (isset($options['authors'])) { checked('1', $options['authors']); } ?> /> Display on authors</label><br />
						<label><input name="sic_options[searches]" type="checkbox" value="1" <?php if (isset($options['searches'])) { checked('1', $options['searches']); } ?> /> Display on searches</label><br />
						<label><input name="sic_options[attachments]" type="checkbox" value="1" <?php if (isset($options['attachments'])) { checked('1', $options['attachments']); } ?> /> Display on attachments</label><br />
						<?php
						foreach (array_keys(sic_detect_custom_post_types()) as $type) {
							?>
							<label><input name="sic_options[<?php echo $type; ?>]" type="checkbox" value="1" <?php if (isset($options[$type])) { checked('1', $options[$type]); } ?> /> Display on <?php echo str_replace("custom_type_", "", $type); ?> index pages</label><br />
							<?php
						}						
						?>
						<em>Note: do not display on pages that show excerpts instead of full posts.  This varies depending on your theme.</em>
					</td><td>
						<label><input name="sic_options[above_post]" type="checkbox" value="1" <?php if (isset($options['above_post'])) { checked('1', $options['above_post']); } ?> /> Display above post</label><br />
						<label><input name="sic_options[below_post]" type="checkbox" value="1" <?php if (isset($options['below_post'])) { checked('1', $options['below_post']); } ?> /> Display below post</label><br />
					</td>
				</tr>
				<tr style="border-top:#dddddd 1px solid;border-bottom:#dddddd 1px solid;">
					<th scope="row">Extended Options</th>
					<td colspan="2">
						<label><input type="text" size="55" name="sic_options[box_title]" value="<?php echo $options['box_title']; ?>" /> Optional text for box title.</label><br />
						<label><input type="text" size="55" name="sic_options[box_title_css]" value="<?php echo $options['box_title_css']; ?>" /> Optional css for box title.</label><br />
						<br />						
						<label><input type="text" size="55" name="sic_options[css_all]" value="<?php echo $options['css_all']; ?>" /> Css styles to apply to the group.</label><br />
						<label><input type="text" size="55" name="sic_options[css_each]" value="<?php echo $options['css_each']; ?>" /> Css styles to apply to each item in the group.</label><br />
						<br />
						<label><input type="text" size="55" name="sic_options[default_image]" value="<?php echo $options['default_image']; ?>" /> URL to your favicon.  This is also the default image to share if an image can't be found.</label><br />
						<label><input type="text" size="55" name="sic_options[default_apple_image]" value="<?php echo $options['default_apple_image']; ?>" /> URL to your favicon for Apple iDevices.</label><br />
						<br />
						<label><input type="text" size="5" name="sic_options[content_filter_priority]" value="<?php echo $options['content_filter_priority']; ?>" /> Content filter priority.</label><br />
						<br />
						<label><input name="sic_options[html5]" type="checkbox" value="1" <?php if (isset($options['html5'])) { checked('1', $options['html5']); } ?> /> Use html5.</label><br />
					</td>
				</tr>				
				<tr>
					<th scope="rowgroup" rowspan="17">Overextended Options</th>
				</tr><tr>	
					<th scope="colgroup" colspan="2"><h4>Facebook</h4></th>
				</tr><tr>
					<td>
						<input type="text" size="15" name="sic_options[fb_app_id]" value="<?php echo $options['fb_app_id']; ?>" />
					</td><td>	
						<label for="sic_options[fb_app_id]">Facebook App ID</label> <a href="https://developers.facebook.com/apps/?action=create"><em>(get one here)</em></a>
					</td>
				</tr><tr>
					<td>
						<input type="text" size="15" name="sic_options[fb_width]" value="<?php echo $options['fb_width']; ?>" />
					</td><td>	
						<label for="sic_options[fb_width]">Width in pixels</label> <em>(standard layout: min=225, default=450; button_count min:90, default:90; box_count min:55 default:55)</em>
					</td>
				</tr><tr>
					<td>
						<input name="sic_options[fb_show_faces]" type="checkbox" value="1" <?php if (isset($options['fb_show_faces'])) { checked('1', $options['fb_show_faces']); } ?> /><label for="sic_options[fb_show_faces]">Show faces</label>
					</td><td>
						<em>(only for standard layout)</em>
					</td>
				</tr><tr>
					<td>
						<label for="sic_options[fb_layout]">Layout: </label>
					</td><td>
						<select name="sic_options[fb_layout]">
							<option value="standard" <?php selected('standard', $options['fb_layout']); ?>>Standard</option>
							<option value="button_count" <?php selected('button_count', $options['fb_layout']); ?>>Button w/ count</option>
							<option value="box_count" <?php selected('box_count', $options['fb_layout']); ?>>Box Count</option>
						</select>
					</td>
				</tr><tr>
					<td>
						<label for="sic_options[fb_action]">Action: </label>
					</td><td>
						<select name="sic_options[fb_action]">
							<option value="like" <?php selected('like', $options['fb_action']); ?>>Like</option>
							<option value="recommend" <?php selected('recommend', $options['fb_action']); ?>>Recommend</option>
						</select>						
					</td>
				</tr><tr>
					<td>	
						<label for="sic_options[fb_font]">Font: </label>
					</td><td>
						<select name="sic_options[fb_font]">
							<option value="arial" <?php selected('arial', $options['fb_font']); ?>>arial</option>
							<option value="lucida grande" <?php selected('lucida grande', $options['fb_font']); ?>>lucida grande</option>
							<option value="segoe ui" <?php selected('segoe ui', $options['fb_font']); ?>>segoe ui</option>
							<option value="tahoma" <?php selected('tahoma', $options['fb_font']); ?>>tahoma</option>
							<option value="trebuchet ms" <?php selected('trebuchet ms', $options['fb_font']); ?>>trebuchet ms</option>
							<option value="verdana" <?php selected('verdana', $options['fb_font']); ?>>verdana</option>
						</select>								
					</td>
				</tr><tr>
					<td>	
						<label for="sic_options[fb_colorscheme]">Colorscheme: </label>
					</td><td>
						<select name="sic_options[fb_colorscheme]">
							<option value="light" <?php selected('light', $options['fb_colorscheme']); ?>>light</option>
							<option value="dark" <?php selected('dark', $options['fb_colorscheme']); ?>>dark</option>
						</select>
					</td>
				</tr><tr>
					<th scope="colgroup" colspan="2"><h4>Twitter</h4></th>
				</tr><tr>
					<td>	
						<label for="sic_options[twitter_size]">Size: </label>
					</td><td>
						<select name="sic_options[twitter_size]">
							<option value="medium" <?php selected('medium', $options['twitter_size']); ?>>medium</option>
							<option value="large" <?php selected('large', $options['twitter_size']); ?>>large</option>
						</select>	
					</td>
				</tr><tr>
					<td>													
						<label for="sic_options[twitter_count]">Count: </label>
					</td><td>
						<select name="sic_options[twitter_count]">
							<option value="none" <?php selected('none', $options['twitter_count']); ?>>none</option>
							<option value="horizontal" <?php selected('horizontal', $options['twitter_count']); ?>>horizontal</option>
							<option value="vertical" <?php selected('vertical', $options['twitter_count']); ?>>vertical</option>
						</select>
					</td>
				</tr><tr>							
					<th scope="colgroup" colspan="2"><h4>Google+</h4></th>
				</tr><tr>
					<td>	
						<label for="sic_options[g_plus_size]">Size: </label>
					</td><td>	
						<select name="sic_options[g_plus_size]">
							<option value="standard" <?php selected('standard', $options['g_plus_size']); ?>>standard</option>
							<option value="small" <?php selected('small', $options['g_plus_size']); ?>>small</option>
							<option value="medium" <?php selected('medium', $options['g_plus_size']); ?>>medium</option>
							<option value="tall" <?php selected('tall', $options['g_plus_size']); ?>>tall</option>
						</select>	
					</td>
				</tr><tr>
					<td>													
						<label for="sic_options[g_plus_annotation]">Count: </label>
					</td><td>
						<select name="sic_options[g_plus_annotation]">
							<option value="none" <?php selected('none', $options['g_plus_annotation']); ?>>none</option>
							<option value="bubble" <?php selected('bubble', $options['g_plus_annotation']); ?>>bubble</option>
							<option value="inline" <?php selected('inline', $options['g_plus_annotation']); ?>>inline</option>
						</select>						
					</td>
				</tr><tr>
					<th scope="colgroup" colspan="2"><h4>Pinterest</h4></th>
				</tr><tr>
					<td>	
						<label for="sic_options[pinterest_layout]">Count: </label>
					</td><td>
						<select name="sic_options[pinterest_layout]">
							<option value="none" <?php selected('none', $options['pinterest_layout']); ?>>none</option>
							<option value="vertical" <?php selected('vertical', $options['pinterest_layout']); ?>>vertical</option>
							<option value="horizontal" <?php selected('horizontal', $options['pinterest_layout']); ?>>horizontal</option>
						</select>	
						
					</td>
				</tr>				

			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>

	</div>
	
<?php 

}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function sic_validate_options( $input ) {
	// Sanitize textbox input (strip html tags, and escape characters)
	//fb_id (#), fb_app_id (#), fb_width (#), css_all (css), css_each (css), content_filter_priority (#), default_image (url), default_apple_image (url)
	$input['fb_app_id'] =  wp_filter_nohtml_kses($input['fb_app_id']); 
	$input['fb_width'] =  wp_filter_nohtml_kses($input['fb_width']); 
	$input['css_all'] =  wp_filter_nohtml_kses($input['css_all']); 
	$input['css_each'] =  wp_filter_nohtml_kses($input['css_each']); 
	$input['content_filter_priority'] =  wp_filter_nohtml_kses($input['content_filter_priority']); 
	$input['default_image'] =  wp_filter_nohtml_kses($input['default_image']); 
	$input['default_apple_image'] =  wp_filter_nohtml_kses($input['default_apple_image']);
	$input['box_title'] =  wp_filter_nohtml_kses($input['box_title']);
	$input['box_title_css'] =  wp_filter_nohtml_kses($input['box_title_css']);
	
	return $input;
}

// Display a Settings link on the main Plugins page
function sic_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$sic_links = '<a href="'.get_admin_url().'options-general.php?page=sharing-is-caring/sharing-is-caring.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $sic_links );
	}

	return $links;
}

/*opengraph and schema stuff*/
//makes the title, url, site name, description, type opengraph meta tags and favicon if default image is set
function sic_opengraph_tags() {
	$options = get_option('sic_options');
	if(is_single() || is_page()){ // Post
		if (have_posts()) : while (have_posts()) : the_post(); 
			echo "\n\t<meta property='og:title' content='",get_the_title($post->post_title),"' />",
				"\n\t<meta property='og:url' content='",get_permalink(),"' />",
				"\n\t<meta property='og:site_name' content='",get_option('blogname'),"' />",
				"\n\t<meta property='og:description' content='",sic_excerpt_max_charlength(300),"' />",
				"\n\t<meta property='og:type' content='article' />",
				"\n\t<meta itemprop='name' content='",get_the_title($post->post_title),"' />",
				"\n\t<meta itemprop='description' content='",sic_excerpt_max_charlength(300),"' />";
			$images_array = sic_get_images();
			foreach ($images_array as $image) {
				if ($image != '') {
					echo "\n\t<meta property='og:image' content='$image' />";
				}
			}
			echo "\n\t<meta itemprop='image' content='",$images_array[0],"' />";
		endwhile; endif; 
	}
	elseif(is_home() || is_front_page()) {
		echo "\n\t<meta property='og:title' content='",get_option('blogname'),"' />",
			"\n\t<meta property='og:url' content='",get_option('siteurl'),"' />",
			"\n\t<meta property='og:site_name' content='",get_option('blogname'),"' />",
			"\n\t<meta property='og:description' content='",get_option('blogdescription'),"' />",
			"\n\t<meta property='og:type' content='blog' />",
			"\n\t<meta itemprop='name' content='",get_option('siteurl'),"' />",
			"\n\t<meta itemprop='description' content='",get_option('blogdescription'),"' />";
			
	}

	else{
		echo "\n\t<meta property='og:title' content='",get_option('blogname'),"' />",
			"\n\t<meta property='og:url' content='",get_option('siteurl'),"' />",
			"\n\t<meta property='og:site_name' content='",get_option('blogname'),"' />",
			"\n\t<meta property='og:description' content='",get_option('blogdescription'),"' />",
			"\n\t<meta property='og:type' content='article' />",
			"\n\t<meta itemprop='name' content='",get_option('siteurl'),"' />",
			"\n\t<meta itemprop='description' content='",get_option('blogdescription'),"' />";
			
	}
	
	if ($options['default_image'] != '' )
		echo "\n\t<link rel='shortcut icon' href='",$options['default_image'],"' />";
	if ($options["default_apple_image"] != '' )
		echo "\n\t<link rel='apple-touch-icon' href='",$options['default_apple_image'],"' />";
}

//returns an array of attachments from the post
function sic_get_images() {
	// Including global WP Enviroment.
	global $post, $posts;
	global $current_blog;
	$options = get_option('sic_options');
	
	$the_images = array();
	
	//Getting images attached to the post.
	$args = array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'numberposts'    => -1,
		'order'          => 'ASC',
		'post_status'    => null,
		'post_parent'    => $post->ID
	);
	
	$attachments = get_posts($args);
	
	// Check for attachments.
	if ($attachments) {
		// Cycling through attachments.
		for($i = 0, $size = sizeof($attachments); $i < $size; ++$i){
			// Retrieving image url.
			$the_images[$i] = wp_get_attachment_url($attachments[$i]->ID);
			//add hostname if url is relative (starts with /)
			if (substr($the_images[$i], 0, 1) == '/') {
				$the_images[$i]	= get_option('siteurl') . $the_images[$i]; //'http://' . $current_blog->domain
			}			
		}
	} else {
		// there are no attachment for the current post.  Return default image.
		if ($options["default_image"] != '') {
			$the_images[0] = $options['default_image']; //favicon
		}
	}
	return $the_images;	
}

/* Extracts the content, removes tags, replaces single and double quotes, cuts it, removes the caption shortcode */
function sic_excerpt_max_charlength($charlength) {
	$content = get_the_content(); //get the content
	$content = strip_tags($content); // strip all html tags
	$quotes = array('/"/',"/'/"); 
	$replacements = array('&quot;','&#39;');
	$content = preg_replace($quotes,$replacements,$content);
	$regex = "#([[]caption)(.*)([[]/caption[]])#e"; // the regex to remove the caption shortcude tag
	$content = preg_replace($regex,'',$content); // remove the caption shortcude tag
	$content = preg_replace( '/\r\n/', ' ', trim($content) ); // remove all new lines
	
	$excerpt = $content;
	$charlength++;
	if(strlen($excerpt)>$charlength) {
		$subex = substr($excerpt,0,$charlength-5);
		$exwords = explode(" ",$subex);
		$excut = -(strlen($exwords[count($exwords)-1]));
		if($excut<0) {
			return substr($subex,0,$excut).'...';
		} else {
			return $subex.'...';
		}
	} else {
		return $excerpt;
	}
}
/*end opengraph and schema stuff*/

function sic_snippets() {
	$options = get_option('sic_options');

	if ($options['fb_position'] != 0) {
		echo '
		<!-- xfbml code -->
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=',$options['fb_app_id'],'";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, \'script\', \'facebook-jssdk\'));
		</script>
		';	
	}
	if ($options['twitter_position'] != 0) {
		echo '
		<!-- twitter code -->
		<script>
		!function(d,s,id){
			var js,fjs=d.getElementsByTagName(s)[0];
			if(!d.getElementById(id)){
				js=d.createElement(s);
				js.id=id;js.src="//platform.twitter.com/widgets.js";
				fjs.parentNode.insertBefore(js,fjs);
			}
		}(document,"script","twitter-wjs");
		</script>
		';	    
	}
	if ($options['g_plus_position'] != 0) {
		echo '
		<!-- google +1 code -->
		<script type="text/javascript">
		(function() {
			var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
			po.src = \'https://apis.google.com/js/plusone.js\';
			var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
		})();
		</script>
	    ';
	}
	if ($options['pinterest_position'] != 0 ) {
		echo '
		<!-- pinterest code -->
		<script type="text/javascript">
		(function() {
			window.PinIt = window.PinIt || { loaded:false };
			if (window.PinIt.loaded) return;
			window.PinIt.loaded = true;
			function async_load(){
				var s = document.createElement("script");
				s.type = "text/javascript";
				s.async = true;
				s.src = "https://assets.pinterest.com/js/pinit.js";
				var x = document.getElementsByTagName("script")[0];
				x.parentNode.insertBefore(s, x);
			}
			if (window.attachEvent)
				window.attachEvent("onload", async_load);
			else
				window.addEventListener("load", async_load, false);
		})();
		</script>
		';	
	}
}

//stuff in footer
function sic_footer() {
	echo '<!--Sharing is Caring!-->';
	
}

function sic_filter_html_tag() { //Add fb namespace (if needed) and schema.org itemscope
	//fb namespace
	$options = get_option('sic_options');
	if ($options['html5'] == "0") {
		echo ' xmlns:fb="http://ogp.me/ns/fb#" ';
	}
	//schema.org itemscope
	if (is_single() || is_page()) {
		echo ' itemscope itemtype="https://schema.org/Article" ';
	} else {
		echo ' itemscope itemtype="https://schema.org/Blog" ';
	}
}

function sic_content( $atts ){
	extract( shortcode_atts( array(
		'f' => 1,
		't' => 1,
		'g' => 1,
		'p' => 1
	), $atts ) );
	
	$widgets= array();
	if(in_the_loop()) {
		$the_link = strip_tags(get_permalink());
		$the_title = strip_tags(get_the_title());
	} else {
		$the_link = get_option('siteurl');
		$the_title = get_option('blogname');
	}
	$options = get_option('sic_options');
	
	if (($options["fb_position"] != 0) && ($f != 0)) {

		if ($options['html5'] == "0") {
			$widgets[$options["fb_position"]] = 
			sprintf(
				'<div class="sic-button sic-facebook" style="display:inline;%s"><fb:like href="%s" send="false" layout="%s" width="%s" show_faces="%s" action="%s" colorscheme="%s" font="%s"></fb:like></div>',
				$options["css_each"],
				$the_link,
				$options["fb_layout"],
				$options["fb_width"],
				$options["fb_show_faces"],
				$options["fb_action"],
				$options["fb_colorscheme"],
				$options["fb_font"]
			);		
		} else {
			$widgets[$options["fb_position"]] = 
			sprintf(
				'<div class="sic-button sic-facebook" style="display:inline;%s"><div class="fb-like" data-href="%s" data-send="false" data-layout="%s" data-width="%s" data-show-faces="%s" data-action="%s" data-colorscheme="%s" data-font="%s"></div></div>',
				$options["css_each"],
				$the_link,
				$options["fb_layout"],
				$options["fb_width"],
				$options["fb_show_faces"],
				$options["fb_action"],
				$options["fb_colorscheme"],
				$options["fb_font"]
			);	
		}  
	}

	if (($options["twitter_position"] != 0) && ($t != 0)) {
		$widgets[$options["twitter_position"]] = 
		sprintf(
			'<div class="sic-button sic-twitter" style="display:inline;%s"><div class="twitter-button"><a href="https://twitter.com/share" class="twitter-share-button" data-url="%s" data-text="%s" data-count="%s" data-size="%s">Tweet</a></div></div>',
			$options["css_each"],
			$the_link,
			$the_title,
			$options["twitter_count"],
			$options["twitter_size"]
		);
	}
	
	if (($options["g_plus_position"] != 0) && ($g != 0)) {
		if ($options['html5'] == "0") {
			$widgets[$options["g_plus_position"]] = 
			sprintf(
				'<div class="sic-button sic-gplus" style="display:inline;%s"><g:plusone size="%s" annotation= "%s" href="%s"></g:plusone></div>',
				$options["css_each"],
				$options["g_plus_size"],
				$options["g_plus_annotation"],
				$the_link
			);
		} else {
			$widgets[$options["g_plus_position"]] = 
			sprintf(
				'<div class="sic-button sic-gplus" style="display:inline;%s"><div class="g-plusone" data-size="%s" data-annotation= "%s" data-href="%s"></div></div>',
				$options["css_each"],
				$options["g_plus_size"],
				$options["g_plus_annotation"],
				$the_link
			);			
		}
	}
	
	if (($options["pinterest_position"] != 0) && ($p != 0)) {  
		$image_array = sic_get_images();
		$widgets[$options["pinterest_position"]] = 
		sprintf(
			'<div class="sic-button sic-pinterest" style="display:inline;%s"><div class="pinterest-button"><a href="http://pinterest.com/pin/create/button/?url=%s&amp;media=%s&amp;description=%s" class="pin-it-button" count-layout="%s">Pin It</a></div></div>',
			$options["css_each"],
			$the_link,
			$image_array[0],
			$the_title,
			$options["pinterest_layout"]
		);
	}	
	
	ksort($widgets);
	
	$sic_content = '<div class="sic-box" style="' . $options["css_all"] . '">';
	$sic_content .= '<div class="sic-title" style="' . $options["box_title_css"]. '">' . $options["box_title"] . '</div>';
	foreach ($widgets as $widget) {
		$sic_content .= $widget;
	}
	$sic_content .= '</div>';
	return $sic_content;
}

//outputs the buttons on posts
function sic_content_filter($content, $force = false) {
	$options = get_option('sic_options');
	$custom_post_type_yepnope = false;
	foreach (array_keys(sic_detect_custom_post_types()) as $type) {
		if ($options[$type] && is_post_type_archive(str_replace("custom_type_", "", $type)) ) {
			$custom_post_type_yepnope=true;
		}
	}

	if (!($force ||
		($options["posts"] && is_single()) ||
		($options["pages"] && is_page()) ||
		($options["homepage"] && is_home()) ||
		($options["categories"] && is_category()) ||
		($options["tags"] && is_tag()) ||
		($options["taxonomies"] && is_tax()) ||
		($options["dates"] && is_date()) ||
		($options["authors"] && is_author()) ||
		($options["searches"] && is_search()) ||
		($options["attachments"] && is_attachment()) ||
		$custom_post_type_yepnope		
		)
		)
		return $content;
	
	$sic_content = sic_content( array('f' => 1,'t' => 1,'g' => 1,'p' => 1) );
	if ($options["above_post"] == 1)
		$content = $sic_content . $content;
	if ($options["below_post"] == 1)
		$content .= $sic_content;
	
	return $content;
} //end function sic_content_filter

function sic_wp_action(){	echo sic_content( array('f' => 1,'t' => 1,'g' => 1,'p' => 1) );} //echo the buttons

if (has_action('sic_sharing')) { //check for existence of hook sic_sharing
	add_action('sic_sharing', 'sic_wp_action'); //echo sic_content at sic_sharing hook
}

add_shortcode('sic', 'sic_content'); //add [sic] shortcode to manually output buttons

// add_filter('the_content', 'sic_content_filter', $options["contentfilterpriority"]);
add_filter('the_content', 'sic_content_filter', 20);

add_filter('language_attributes', 'sic_filter_html_tag');//Add fb namespace (if needed) and schema.org itemscope

add_action('wp_head', 'sic_opengraph_tags');//Add the opengraph meta tags to wp_head

add_action('wp_footer', 'sic_snippets');//Add various code snippets to footer

add_action('wp_footer', 'sic_footer');//Add sic footer
?>
