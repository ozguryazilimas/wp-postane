<?php
/**
 * ################################################################################
 * IMSANITY ADMIN/SETTINGS UI
 * ################################################################################
 */

// register the plugin settings menu
add_action( 'admin_menu', 'imsanity_create_menu' );
add_action( 'network_admin_menu', 'imsanity_register_network' );
add_filter( 'plugin_action_links_imsanity/imsanity.php', 'imsanity_settings_link' );
add_action( 'admin_enqueue_scripts', 'imsanity_queue_script' );
add_action( 'admin_init', 'imsanity_register_settings' );

// activation hooks
// TODO: custom table is not removed because de-activating one site shouldn't affect the entire server
register_activation_hook('imsanity/imsanity.php', 'imsanity_maybe_created_custom_table' );
// add_action('plugins_loaded', 'imsanity_maybe_created_custom_table');
// register_deactivation_hook('imsanity/imsanity.php', ...);
// register_uninstall_hook('imsanity/imsanity.php', 'imsanity_maybe_remove_custom_table');

// settings cache
$_imsanity_multisite_settings = null;

/**
 * Create the settings menu item in the WordPress admin navigation and
 * link it to the plugin settings page
 */
function imsanity_create_menu()
{
	// create new menu for site configuration
	add_options_page( esc_html__( 'Imsanity Plugin Settings', 'imsanity' ), 'Imsanity', 'administrator', __FILE__, 'imsanity_settings_page' );
}

/**
 * Register the network settings page
 */
function imsanity_register_network() {
	if ( ! function_exists( 'is_plugin_active_for_network' ) && is_multisite() ) {
		// need to include the plugin library for the is_plugin_active function
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	if ( is_multisite() && is_plugin_active_for_network( 'imsanity/imsanity.php' ) ) {
		add_submenu_page( 'settings.php', esc_html__( 'Imsanity Network Settings', 'imsanity' ), 'Imsanity', 'manage_options', 'imsanity_network', 'imsanity_network_settings' );
	}
}

/**
 * Settings link that appears on the plugins overview page
 * @param array $links
 * @return array
 */
function imsanity_settings_link( $links ) {
	$links[] = '<a href="'. get_admin_url( null, 'options-general.php?page=' . __FILE__ ) . '">' . esc_html__( 'Settings', 'imsanity' ) . '</a>';
	return $links;
}

function imsanity_queue_script( $hook ) {
	// make sure we are being called from the settings page
	if ( strpos( $hook, 'settings_page_imsanity' ) !== 0 ) {
		return;
	}
	// register the scripts that are used by the bulk resizer
	wp_enqueue_script( 'imsanity_script', plugins_url( '/imsanity/scripts/imsanity.js' ), array( 'jquery' ), IMSANITY_VERSION );
	wp_localize_script( 'imsanity_script', 'imsanity_vars', array(
			'_wpnonce' => wp_create_nonce( 'imsanity-bulk' ),
			'resizing_complete' => esc_html__( 'Resizing Complete', 'imsanity' ),
			'resize_selected' => esc_html__( 'Resize Selected Images', 'imsanity' ),
			'image' => esc_html__( 'Image', 'imsanity' ),
			'invalid_response' => esc_html__( 'Received an invalid response, please check for errors in the Developer Tools console of your browser.', 'imsanity' ),
			'none_found' => esc_html__( 'There are no images that need to be resized.', 'imsanity' ),
		)
	);
}

// TODO: legacy code to support previous MU version... ???
// if ( dm_site_admin() && version_compare( $wp_version, '3.0.9', '<=' ) ) {
// 	if ( version_compare( $wp_version, '3.0.1', '<=' ) ) {
// 		add_submenu_page('wpmu-admin.php', __( 'Domain Mapping', 'wordpress-mu-domain-mapping' ), __( 'Domain Mapping', 'wordpress-mu-domain-mapping'), 'manage_options', 'dm_admin_page', 'dm_admin_page');
// 		add_submenu_page('wpmu-admin.php', __( 'Domains', 'wordpress-mu-domain-mapping' ), __( 'Domains', 'wordpress-mu-domain-mapping'), 'manage_options', 'dm_domains_admin', 'dm_domains_admin');
// 	} else {
// 		add_submenu_page('ms-admin.php', __( 'Domain Mapping', 'wordpress-mu-domain-mapping' ), 'Domain Mapping', 'manage_options', 'dm_admin_page', 'dm_admin_page');
// 		add_submenu_page('ms-admin.php', __( 'Domains', 'wordpress-mu-domain-mapping' ), 'Domains', 'manage_options', 'dm_domains_admin', 'dm_domains_admin');
// 	}
// }
// add_action( 'admin_menu', 'dm_add_pages' );

// TODO: put network options where they belong, not in a custom table
/**
 * Returns the name of the custom multi-site settings table.
 * this will be the same table regardless of the blog
 */
function imsanity_get_custom_table_name()
{
	global $wpdb;

	// passing in zero seems to return $wpdb->base_prefix, which is not public
	return $wpdb->get_blog_prefix(0) . "imsanity";
}

/**
 * Return true if the multi-site settings table exists
 * @return bool
 */
function imsanity_multisite_table_exists()
{
	global $wpdb;
	$table_name = imsanity_get_custom_table_name();
	return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
}

/**
* Return true if the multi-site settings table exists
* @return bool
*/
function imsanity_multisite_table_schema_version()
{
	// if the table doesn't exist then there is no schema to report
	if (!imsanity_multisite_table_exists()) return '0';

	global $wpdb;
	$version = $wpdb->get_var('SELECT data FROM ' . imsanity_get_custom_table_name() . " WHERE setting = 'schema'");

	if (!$version) $version = '1.0'; // this is a legacy version 1.0 installation

	return $version;

}

/**
 * Returns the default network settings in the case where they are not
 * defined in the database, or multi-site is not enabled
 * @return stdClass
 */
function imsanity_get_default_multisite_settings()
{
	$data = new stdClass();
	$data->imsanity_override_site = false;
	$data->imsanity_max_height = IMSANITY_DEFAULT_MAX_HEIGHT;
	$data->imsanity_max_width = IMSANITY_DEFAULT_MAX_WIDTH;
	$data->imsanity_max_height_library = IMSANITY_DEFAULT_MAX_HEIGHT;
	$data->imsanity_max_width_library = IMSANITY_DEFAULT_MAX_WIDTH;
	$data->imsanity_max_height_other = IMSANITY_DEFAULT_MAX_HEIGHT;
	$data->imsanity_max_width_other = IMSANITY_DEFAULT_MAX_WIDTH;
	$data->imsanity_bmp_to_jpg = IMSANITY_DEFAULT_BMP_TO_JPG;
	$data->imsanity_png_to_jpg = IMSANITY_DEFAULT_PNG_TO_JPG;
	$data->imsanity_quality = IMSANITY_DEFAULT_QUALITY;
	return $data;
}


/**
 * On activation create the multisite database table if necessary.  this is
 * called when the plugin is activated as well as when it is automatically
 * updated.
 *
 * @param bool set to true to force the query to run in the case of an upgrade
 */
function imsanity_maybe_created_custom_table()
{
	// if not a multi-site no need to do any custom table lookups
	if ( (!function_exists("is_multisite")) || (!is_multisite()) ) return;

	global $wpdb;

	$schema = imsanity_multisite_table_schema_version();
	$table_name = imsanity_get_custom_table_name();

	if ($schema == '0')
	{
		// this is an initial database setup
		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
					  setting varchar(55),
					  data text NOT NULL,
					  PRIMARY KEY (setting)
					);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		$data = imsanity_get_default_multisite_settings();

		// add the rows to the database
		$data = imsanity_get_default_multisite_settings();
		$wpdb->insert( $table_name, array( 'setting' => 'multisite', 'data' => maybe_serialize($data) ) );
		$wpdb->insert( $table_name, array( 'setting' => 'schema', 'data' => IMSANITY_SCHEMA_VERSION ) );
	}

	if ($schema != IMSANITY_SCHEMA_VERSION)
	{
		// this is a schema update.  for the moment there is only one schema update available, from 1.0 to 1.1
		if ($schema == '1.0')
		{
			// update from version 1.0 to 1.1
			$wpdb->insert( $table_name, array( 'setting' => 'schema', 'data' => IMSANITY_SCHEMA_VERSION ) );
			$update1 = "ALTER TABLE " . $table_name . " CHANGE COLUMN data data TEXT NOT NULL;";
			$wpdb->query($update1);
		}
		else
		{
			// @todo we don't have this yet
			$wpdb->update(
				$table_name,
				array('data' =>  IMSANITY_SCHEMA_VERSION),
				array('setting' => 'schema')
			);
		}

	}


}

/**
 * display the form for the multi-site settings page
 */
function imsanity_network_settings()
{
	imsanity_settings_css();

	echo '
		<div class="wrap">
		<h1>' . esc_html__( 'Imsanity Network Settings' , 'imsanity' ) . '</h1>
		';

	// we only want to update if the form has been submitted
//	if (isset($_POST['update_settings']))
//	{
//		imsanity_network_settings_update();
//		echo "<div id='imsanity-network-settings-saved' class='updated fade'><p><strong>". esc_html__( "Imsanity network settings saved.", 'imsanity' ) . "</strong></p></div>";
//	}

//	imsanity_settings_banner();

	$settings = imsanity_get_multisite_settings();
// TODO: insert labels for all settings
	?>

	<form method="post" action="settings.php?page=imsanity_network">
	<input type="hidden" name="update_imsanity_settings" value="1" />
	<?php wp_nonce_field( "imsanity_network_options" ); ?>
	<table class="form-table">
	<tr valign="top">
	<th scope="row"><?php esc_html_e( 'Global Settings Override', 'imsanity' ); ?></th>
	<td>
		<select name="imsanity_override_site">
			<option value="0" <?php if ($settings->imsanity_override_site == '0') echo "selected='selected'" ?> ><?php esc_html_e("Allow each site to configure Imsanity settings",'imsanity'); ?></option>
			<option value="1" <?php if ($settings->imsanity_override_site == '1') echo "selected='selected'" ?> ><?php esc_html_e("Use global Imsanity settings (below) for all sites",'imsanity'); ?></option>
		</select>
	</td>
	</tr>

	<tr valign="top">
	<th><?php esc_html_e("Images uploaded within a Page/Post",'imsanity');?></th>
	<td>
		<?php esc_html_e( 'Max Width', 'imsanity' ); ?> <input type="number" step="1" min="0" class='small-text' name="imsanity_max_width" value="<?php echo $settings->imsanity_max_width ?>" />
		<?php esc_html_e( 'Max Height', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_height" value="<?php echo $settings->imsanity_max_height ?>" /> <?php esc_html_e( 'in pixels, enter 0 to disable','imsanity'); ?>
	</td>
	</tr>

	<tr valign="top">
	<th><?php esc_html_e("Images uploaded directly to the Media Library",'imsanity'); ?></th>
	<td>
		<?php esc_html_e( 'Max Width', 'imsanity' ); ?> <input type="number" step="1" min="0" class='small-text' name="imsanity_max_width_library" value="<?php echo $settings->imsanity_max_width_library ?>" />
		<?php esc_html_e( 'Max Height', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_height_library" value="<?php echo $settings->imsanity_max_height_library ?>" /> <?php esc_html_e( 'in pixels, enter 0 to disable','imsanity'); ?>
	</td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php esc_html_e("Images uploaded elsewhere (Theme headers, backgrounds, logos, etc)",'imsanity'); ?></th>
	<td>
		<?php esc_html_e( 'Max Width', 'imsanity' ); ?> <input type="number" step="1" min="0" class='small-text' name="imsanity_max_width_other" value="<?php echo $settings->imsanity_max_width_other ?>" />
		<?php esc_html_e( 'Max Height', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_height_other" value="<?php echo $settings->imsanity_max_height_other ?>" /> <?php esc_html_e( 'in pixels, enter 0 to disable','imsanity'); ?>
	</td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php esc_html_e("Convert BMP to JPG",'imsanity'); ?></th>
	<td><select name="imsanity_bmp_to_jpg">
		<option value="1" <?php if ($settings->imsanity_bmp_to_jpg == '1') echo "selected='selected'" ?> ><?php esc_html_e("Yes",'imsanity'); ?></option>
		<option value="0" <?php if ($settings->imsanity_bmp_to_jpg == '0') echo "selected='selected'" ?> ><?php esc_html_e("No",'imsanity'); ?></option>
	</select></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php esc_html_e("Convert PNG to JPG",'imsanity'); ?></th>
	<td><select name="imsanity_png_to_jpg">
		<option value="1" <?php if ($settings->imsanity_png_to_jpg == '1') echo "selected='selected'" ?> ><?php esc_html_e("Yes",'imsanity'); ?></option>
		<option value="0" <?php if ($settings->imsanity_png_to_jpg == '0') echo "selected='selected'" ?> ><?php esc_html_e("No",'imsanity'); ?></option>
	</select></td>
	</tr>

	<tr>
	<th><label for='imsanity_quality' ><?php esc_html_e( "JPG image quality", 'imsanity' ); ?></th>
	<td><input type='text' id='imsanity_quality' name='imsanity_quality' class='small-text' value='<?php echo (int) $settings->imsanity_quality; ?>' /> <?php esc_html_e( 'Valid values are 1-100.', 'imsanity' ); ?>
	<p class='description'><?php esc_html_e( 'WordPress default is 82', 'imsanity' ); ?></p></td>
	</tr>

	</table>

	<p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e( 'Update Settings', 'imsanity' ); ?>" /></p>

	</form>
	<?php

	echo '</div>';
}

/**
 * Process the form, update the network settings
 * and clear the cached settings
 */
function imsanity_network_settings_update() {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'imsanity_network_options' ) ) {
		return;
	}
	global $wpdb;
	global $_imsanity_multisite_settings;

	// ensure that the custom table is created when the user updates network settings
	// this is not ideal but it's better than checking for this table existance
	// on every page load
	imsanity_maybe_created_custom_table();

	$table_name = imsanity_get_custom_table_name();

	$data = new stdClass();
	$data->imsanity_override_site = $_POST['imsanity_override_site'] == 1;
	$data->imsanity_max_height = sanitize_text_field($_POST['imsanity_max_height']);
	$data->imsanity_max_width = sanitize_text_field($_POST['imsanity_max_width']);
	$data->imsanity_max_height_library = sanitize_text_field($_POST['imsanity_max_height_library']);
	$data->imsanity_max_width_library = sanitize_text_field($_POST['imsanity_max_width_library']);
	$data->imsanity_max_height_other = sanitize_text_field($_POST['imsanity_max_height_other']);
	$data->imsanity_max_width_other = sanitize_text_field($_POST['imsanity_max_width_other']);
	$data->imsanity_bmp_to_jpg = $_POST['imsanity_bmp_to_jpg'] == 1;
	$data->imsanity_png_to_jpg = $_POST['imsanity_png_to_jpg'] == 1;
	$data->imsanity_quality = imsanity_jpg_quality($_POST['imsanity_quality']);
	$wpdb->update(
		$table_name,
		array('data' =>  maybe_serialize($data)),
		array('setting' => 'multisite')
	);

	// clear the cache
	$_imsanity_multisite_settings = null;
	add_action( 'network_admin_notices', 'imsanity_network_settings_saved' );
}

function imsanity_network_settings_saved() {
	// TODO: figure out why this won't fade
	echo "<div id='imsanity-network-settings-saved' class='updated fade'><p><strong>" . esc_html__( "Imsanity network settings saved.", 'imsanity' ) . "</strong></p></div>";
}

/**
 * Return the multi-site settings as a standard class.  If the settings are not
 * defined in the database or multi-site is not enabled then the default settings
 * are returned.  This is cached so it only loads once per page load, unless
 * imsanity_network_settings_update is called.
 * @return stdClass
 */
function imsanity_get_multisite_settings()
{
	global $_imsanity_multisite_settings;
	$result = null;

	if (!$_imsanity_multisite_settings)
	{
		if (function_exists("is_multisite") && is_multisite())
		{
			global $wpdb;

			$result = $wpdb->get_var('select data from ' . imsanity_get_custom_table_name() . " where setting = 'multisite'");
		}

		// if there's no results, return the defaults instead
		$_imsanity_multisite_settings = $result
			? unserialize($result)
			: imsanity_get_default_multisite_settings();

		// this is for backwards compatibility
		if ($_imsanity_multisite_settings->imsanity_max_height_library == '')
		{
			$_imsanity_multisite_settings->imsanity_max_height_library = $_imsanity_multisite_settings->imsanity_max_height;
			$_imsanity_multisite_settings->imsanity_max_width_library = $_imsanity_multisite_settings->imsanity_max_width;
			$_imsanity_multisite_settings->imsanity_max_height_other = $_imsanity_multisite_settings->imsanity_max_height;
			$_imsanity_multisite_settings->imsanity_max_width_other = $_imsanity_multisite_settings->imsanity_max_width;
		}

	}

	return $_imsanity_multisite_settings;
}

/**
 * Gets the option setting for the given key, first checking to see if it has been
 * set globally for multi-site.  Otherwise checking the site options.
 * @param string $key
 * @param string $ifnull value to use if the requested option returns null
 */
function imsanity_get_option($key,$ifnull)
{
	$result = null;

	$settings = imsanity_get_multisite_settings();

	if ($settings->imsanity_override_site)
	{
		$result = $settings->$key;
		if ($result == null) $result = $ifnull;
	}
	else
	{
		$result = get_option($key,$ifnull);
	}

	return $result;

}

/**
 * Register the configuration settings that the plugin will use
 */
function imsanity_register_settings() {
	if ( ! function_exists( 'is_plugin_active_for_network' ) && is_multisite() ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	// we only want to update if the form has been submitted
	if ( isset( $_POST['update_imsanity_settings'] ) && is_multisite() && is_plugin_active_for_network( 'imsanity/imsanity.php' ) ) {
		imsanity_network_settings_update();
	}
	//register our settings
	register_setting( 'imsanity-settings-group', 'imsanity_max_height' );
	register_setting( 'imsanity-settings-group', 'imsanity_max_width' );
	register_setting( 'imsanity-settings-group', 'imsanity_max_height_library' );
	register_setting( 'imsanity-settings-group', 'imsanity_max_width_library' );
	register_setting( 'imsanity-settings-group', 'imsanity_max_height_other' );
	register_setting( 'imsanity-settings-group', 'imsanity_max_width_other' );
	register_setting( 'imsanity-settings-group', 'imsanity_bmp_to_jpg' );
	register_setting( 'imsanity-settings-group', 'imsanity_png_to_jpg' );
	register_setting( 'imsanity-settings-group', 'imsanity_quality', 'imsanity_jpg_quality' );
}

/**
 * Validate and return the JPG quality setting
 */
function imsanity_jpg_quality( $quality = null ) {
	if ( $quality === null ) {
		$quality = get_option( 'imsanity_quality' );
	}
	if ( preg_match( '/^(100|[1-9][0-9]?)$/', $quality ) ) {
		return (int) $quality;
	} else {
		return IMSANITY_DEFAULT_QUALITY;
	}
}

/**
 * Helper function to render css styles for the settings forms
 * for both site and network settings page
 */
function imsanity_settings_css()
{
	echo "
	<style>
	#imsanity_header {
		border: solid 1px #c6c6c6;
		margin: 10px 0px;
		padding: 0px 10px;
		background-color: #e1e1e1;
	}
	#imsanity_header p {
		margin: .5em 0;
	}
	</style>";
}

/**
 * Helper function to render the settings banner
 * for both site and network settings page
 */
function imsanity_settings_banner()
{
	
//	echo '
//	<div id="imsanity_header" style="float: left;">';
	
//	if (!defined('IMSANITY_HIDE_LOGO')) 
//		echo '<a href="http://verysimple.com/products/imsanity/"><img alt="Imsanity" src="' . plugins_url() . '/imsanity/images/imsanity.png" style="float: right; margin-left: 15px;"/></a>';
	
/*	echo '
		<h4>'.__("Imsanity automatically resizes insanely huge image uploads",'imsanity').'</h4>'.

		__("<p>Imsanity automaticaly reduces the size of images that are larger than the specified maximum and replaces the original
		with one of a more \"sane\" size.  Site contributors don\'t need to concern themselves with manually scaling images
		and can upload them directly from their camera or phone.</p>

		<p>The resolution of modern cameras is larger than necessary for typical web display.
		The average computer screen is not big enough to display a 3 megapixel camera-phone image at full resolution.
		WordPress does a good job of creating scaled-down copies which can be used, however the original images
		are permanently stored, taking up disk quota and, if used on a page, create a poor viewer experience.</p>

		<p>This plugin is designed for sites where high-resolution images are not necessary and/or site contributors
		do not want (or understand how) to deal with scaling images.  This plugin should not be used on
		sites for which original, high-resolution images must be stored.</p>

		<p>Be sure to save back-ups of your full-sized images if you wish to keep them.</p>",'imsanity') .

		sprintf( __("<p>Imsanity Version %s by %s </p>",'imsanity'),IMSANITY_VERSION ,'<a href="https://ewww.io/">Shane Bishop</a>') .
	'</div>
	<br style="clear:both" />';*/
}

/**
 * Render the settings page by writing directly to stdout.  if multi-site is enabled
 * and imsanity_override_site is true, then display a notice message that settings
 * are not editable instead of the settings form
 */
function imsanity_settings_page()
{
	imsanity_settings_css();

	?>
	<div class="wrap">
	<h1><?php esc_html_e( 'Imsanity Settings', 'imsanity' ); ?></h1>
	<?php

//	imsanity_settings_banner();

	$settings = imsanity_get_multisite_settings();

	if ($settings->imsanity_override_site)
	{
		imsanity_settings_page_notice();
	}
	else
	{
		imsanity_settings_page_form();
	}

	?>

	<h2 style="margin-top: 0px;"><?php _e("Bulk Resize Images",'imsanity'); ?></h2>

	<div id="imsanity_header">
	<p><?php esc_html_e( 'If you have existing images that were uploaded prior to installing Imsanity, you may resize them all in bulk to recover disk space. To begin, click the "Search Images" button to search all existing attachments for images that are larger than the configured limit.', 'imsanity' ); ?></p>
	<p><?php printf( esc_html__( 'NOTE: To give you greater control over the resizing process, a maximum of %d images will be returned at one time. Bitmap images cannot be bulk resized and will not appear in the search results.', 'imsanity' ), IMSANITY_AJAX_MAX_RECORDS ); ?></p>
	</div>

	<div style="border: solid 1px #ff6666; background-color: #ffbbbb; padding: 0 10px;">
		<h4><?php esc_html_e( 'WARNING: Bulk Resize will alter your original images and cannot be undone!', 'imsanity' ); ?></h4>
		
		<p><?php esc_html_e( 'It is HIGHLY recommended that you backup your wp-content/uploads folder before proceeding. You will have a chance to preview and select the images to convert.', 'imsanity' ); ?><br>
		<?php esc_html_e( 'It is also recommended that you initially select only 1 or 2 images and verify that everything is working properly before processing your entire library.', 'imsanity' ); ?></p>
	</div>

	<p class="submit" id="imsanity-examine-button">
		<button class="button-primary" onclick="imsanity_load_images('imsanity_image_list');"><?php esc_html_e( 'Search Images...', 'imsanity' ); ?></button>
	</p>
	<div id='imsanity_image_list'>
		<div id="imsanity_target" style="display: none; border: solid 2px #666666; padding: 10px; height: 0px; overflow: auto;">
			<div id="imsanity_loading" style="display: none;"><img src="<?php echo plugins_url( 'images/ajax-loader.gif', __FILE__ ); ?>" style="margin-bottom: .25em; vertical-align:middle;" />
				<?php esc_html_e( 'Scanning existing images. This may take a moment.', 'imsanity' ); ?>
			</div>
		</div>
	</div>

	<?php

	echo '</div>';

}

/**
 * Multi-user config file exists so display a notice
 */
function imsanity_settings_page_notice()
{
	?>
	<div class="updated settings-error">
	<p><strong><?php esc_html_e("Imsanity settings have been configured by the server administrator. There are no site-specific settings available.",'imsanity'); ?></strong></p>
	</div>

	<?php
}

/**
* Render the site settings form.  This is processed by
* WordPress built-in options persistance mechanism
*/
function imsanity_settings_page_form()
{
	?>
	<form method="post" action="options.php">
	<?php settings_fields( 'imsanity-settings-group' ); ?>
		<table class="form-table">

		<tr valign="middle">
		<th scope="row"><?php _e("Images uploaded within a Page/Post",'imsanity'); ?></th>
		<td>
			<?php esc_html_e( 'Max Width', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_width" value="<?php echo get_option('imsanity_max_width',IMSANITY_DEFAULT_MAX_WIDTH); ?>" />
			<?php esc_html_e( 'Max Height', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_height" value="<?php echo get_option('imsanity_max_height',IMSANITY_DEFAULT_MAX_HEIGHT); ?>" /> <?php esc_html_e( 'in pixels, enter 0 to disable','imsanity'); ?>
		</td>
		</tr>

		<tr valign="middle">
		<th scope="row"><?php _e("Images uploaded directly to the Media Library",'imsanity'); ?></th>
		<td>
			<?php esc_html_e( 'Max Width', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_width_library" value="<?php echo get_option('imsanity_max_width_library',IMSANITY_DEFAULT_MAX_WIDTH); ?>" />
			<?php esc_html_e( 'Max Height', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_height_library" value="<?php echo get_option('imsanity_max_height_library',IMSANITY_DEFAULT_MAX_HEIGHT); ?>" /> <?php esc_html_e( 'in pixels, enter 0 to disable','imsanity'); ?>
		</td>
		</tr>

		<tr valign="middle">
		<th scope="row"><?php _e("Images uploaded elsewhere (Theme headers, backgrounds, logos, etc)",'imsanity'); ?></th>
		<td>
			<?php esc_html_e( 'Max Width', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_width_other" value="<?php echo get_option('imsanity_max_width_other',IMSANITY_DEFAULT_MAX_WIDTH); ?>" />
			<?php esc_html_e( 'Max Height', 'imsanity' ); ?> <input type="number" step="1" min="0" class="small-text" name="imsanity_max_height_other" value="<?php echo get_option('imsanity_max_height_other',IMSANITY_DEFAULT_MAX_HEIGHT); ?>" /> <?php esc_html_e( 'in pixels, enter 0 to disable','imsanity'); ?>
		</td>
		</tr>


		<tr>
		<th><label for='imsanity_quality' ><?php esc_html_e( "JPG image quality", 'imsanity' ); ?></th>
		<td><input type='text' id='imsanity_quality' name='imsanity_quality' class='small-text' value='<?php echo imsanity_jpg_quality(); ?>' /> <?php esc_html_e('Valid values are 1-100.', 'imsanity' ); ?>
		<p class='description'><?php esc_html_e( 'WordPress default is 82','imsanity' ); ?></p></td>
		</tr>

		<tr valign="middle">
		<th scope="row"><?php esc_html_e("Convert BMP To JPG",'imsanity'); ?></th>
		<td><select name="imsanity_bmp_to_jpg">
			<option <?php if ( get_option( 'imsanity_bmp_to_jpg', IMSANITY_DEFAULT_BMP_TO_JPG) == "1" ) { echo "selected='selected'"; } ?> value="1"><?php esc_html_e("Yes",'imsanity'); ?></option>
			<option <?php if ( get_option( 'imsanity_bmp_to_jpg', IMSANITY_DEFAULT_BMP_TO_JPG) == "0" ) { echo "selected='selected'"; } ?> value="0"><?php esc_html_e("No",'imsanity'); ?></option>
		</select></td>
		</tr>

		<tr valign="middle">
		<th scope="row"><?php esc_html_e("Convert PNG To JPG",'imsanity'); ?></th>
		<td><select name="imsanity_png_to_jpg">
			<option <?php if (get_option('imsanity_png_to_jpg',IMSANITY_DEFAULT_PNG_TO_JPG) == "1") {echo "selected='selected'";} ?> value="1"><?php esc_html_e("Yes",'imsanity'); ?></option>
			<option <?php if (get_option('imsanity_png_to_jpg',IMSANITY_DEFAULT_PNG_TO_JPG) == "0") {echo "selected='selected'";} ?> value="0"><?php esc_html_e("No",'imsanity'); ?></option>
		</select></td>
		</tr>

	</table>

	<p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" /></p>

	</form>
	<?php

}

?>
