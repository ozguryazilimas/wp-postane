<?php
/*
Plugin Name: Simple Author Highlighter
Plugin URI: http://www.dakulov.eu#page5
Description: Easy highlight authors comments
Version: 0.6.5
Author: Akulov Dmitriy
Author URI: http://www.dakulov.eu
*/
?>
<?php
// create custom plugin settings menu
add_action('admin_menu', 'sah_create_menu');

function sah_create_menu() {

	//create new top-level menu
	add_options_page('Simple Author Highlighter', 'Simple Author Highlighter', 'administrator', 'sah', 'sah_settings_page');

	//call register settings function
	add_action( 'admin_init', 'sah_register_mysettings' );
}

add_action ('wp_head', 'sah_addHeaderCode') ;

function sah_addHeaderCode() {
		$sah_users = get_option('user');
		$sah_userArray = explode(",", $sah_users);
		echo "\n".'<!-- Start Simple Author Highlighter -->'."\n";
		echo '<style type="text/css">' . "\n";
		echo '.bypostauthor {background-color: ' . get_option('color_code') . ' !important; color: ' . get_option('color_code2') . ' ;}' . "\n";
		for($i = 0; $i < count($sah_userArray); $i++){
	echo '.comment-author-' . $sah_userArray[$i] . ' {background-color: ' . get_option('user_color_code') . ' !important; color: ' . get_option('user_color_code2') . ' ;}' . "\n";
}
		echo '</style>'."\n";
		echo '<!-- Stop Simple Author Highlighter -->'."\n";
}

if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'sah_deinstall');
 
 /**
 * Delete options in database
 */
function sah_deinstall() {
 
	delete_option('color_code');
	delete_option('color_code2');
	delete_option('user_color_code');
	delete_option('user_color_code2');
	delete_option('user');
}

function sah_register_mysettings() {
	//register  settings
	register_setting( 'sah-settings-group', 'color_code' );
	register_setting( 'sah-settings-group', 'color_code2' );
	register_setting( 'sah-settings-group', 'user_color_code' );
	register_setting( 'sah-settings-group', 'user_color_code2' );
	register_setting( 'sah-settings-group', 'user' );
}

function sah_settings_page() {
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"></div>
<h2>Simple Author Highlighter - Settings</h2>
<br/>
<form method="post" action="options.php">
    <?php settings_fields( 'sah-settings-group' ); ?>
	
    <table class="form-table">
        <tr valign="top">
		<h2>Main - Author Highlighter</h2>
		<h4>Select the colors to highlight the comments of the post authors</h4>
		        <th scope="row">Enter the highlight color</th>
        <td><input type="text" name="color_code" value="<?php echo get_option('color_code'); ?>" /> <i>For example: #b6bdf6 Leave blank for theme's default</i><br/></td>
        </tr>
		<tr valign="top">
		<th scope="row">Enter the text color</th>
		<td><input type="text" name="color_code2" value="<?php echo get_option('color_code2'); ?>" /> <i>For example: #ffffff Leave blank for theme's default</i></td>
		</tr>
	</table>
	<h2>(Optional) - Global User Highlighter </h2>
	 <table class="form-table">
    <tr valign="top">
	<h4>Select the colors to highlight the comments of the selected users</h4>
        <th scope="row">Enter the users login names</th>
        <td><input type="text" name="user" value="<?php echo get_option('user'); ?>" /> <i>For example: admin For multiple users use comma as a delimiter</i><br/></td>
        </tr>
		<tr valign="top">
        <th scope="row">Enter the highlight color</th>
        <td><input type="text" name="user_color_code" value="<?php echo get_option('user_color_code'); ?>" /> <i>For example: #b6bdf6 Leave blank for theme's default</i><br/></td>
        </tr>
		<tr valign="top">
		<th scope="row">Enter the text color</th>
		<td><input type="text" name="user_color_code2" value="<?php echo get_option('user_color_code2'); ?>" /> <i>For example: #ffffff Leave blank for theme's default</i></td>
		</tr>
		</table>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
<br/><center>
If you have any questions or problems leave a comment <a target="_blank" class="button-secondary" href="http://www.dakulov.eu#page5">website</a> | If you liked the plugin please <a target="_blank" class="button-secondary" href="http://wordpress.org/extend/plugins/simple-author-highlighter/">vote</a> for us </center>
</div>
<?php } ?>