<?php
/**
 * Display banners on settings page
 * @package Captcha by BestWebSoft
 * @since 4.1.5
 */

/** 
 * Show ads for PRO
 * @param     string     $func        function to call
 * @return    void 
 */
if ( ! function_exists( 'cptch_pro_block' ) ) {
	function cptch_pro_block( $func ) { 
		global $cptch_plugin_info, $wp_version, $cptch_options;
		if ( ! bws_hide_premium_options_check( $cptch_options ) ) { ?>
			<div class="bws_pro_version_bloc cptch_pro_block <?php echo $func;?>" title="<?php _e( 'This options is available in Pro version of plugin', 'captcha' ); ?>">
				<div class="bws_pro_version_table_bloc">
					<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'captcha' ); ?>"></button>
					<div class="bws_table_bg"></div>
					<?php call_user_func( $func ); ?>
				</div>
				<div class="bws_pro_version_tooltip">
					<div class="bws_info"><?php _e( 'Unlock premium options by upgrading to Pro version', 'captcha' ); ?></div>
					<a class="bws_button" href="http://bestwebsoft.com/products/captcha/?k=9701bbd97e61e52baa79c58c3caacf6d&pn=75&v=<?php echo $cptch_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Captcha Pro by BestWebSoft Plugin"><?php _e( 'Learn More', 'captcha' ); ?></a>
				</div>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'cptch_basic_banner' ) ) {
	function cptch_basic_banner() { ?>
		<table class="form-table bws_pro_version">
			<tr valign="top">
				<th scope="row"><?php _e( 'Enable CAPTCHA for', 'captcha' ); ?>:</th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Enable CAPTCHA for', 'captcha' ); ?></span></legend>
						<label><input disabled='disabled' type="checkbox" /> Contact Form 7</label><br />
						<label><input disabled='disabled' type="checkbox" name="cptchpr_subscriber" value="1" /> Subscriber by BestWebSoft</label><br />
						<label><input disabled='disabled' type="checkbox" /> <?php _e( 'Buddypress Registration form', 'captcha' ); ?></label><br />
						<label><input disabled='disabled' type="checkbox" /> <?php _e( 'Buddypress Comments form', 'captcha' ); ?></label><br />
						<label><input disabled='disabled' type="checkbox" /> <?php _e( 'Buddypress "Create a Group" form', 'captcha' ); ?></label><br />
						<label><input disabled='disabled' type="checkbox" /> WooCommerce login form</label>	
					</fieldset>
				</td>
			</tr>	
			<tr valign="top">
				<th scope="row" colspan="2">
					* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'captcha' ); ?>
				</th>
			</tr>
		</table>
	<?php }
} 

if ( ! function_exists( 'cptch_advanced_banner' ) ) {
	function cptch_advanced_banner() { ?>
		<table class="form-table bws_pro_version">
			<tr valign="top">
				<th scope="row"><?php _e( 'Use several packages at the same time', 'captcha' ); ?></th>
				<td>
					<input disabled='disabled' type="checkbox" /><br/>
					<span class="bws_info"><?php _e( 'If this option is enabled, CAPTCHA will be use pictures from different packages at the same time', 'captcha' ); ?>.</span>
				</td>
			</tr>
		</table>
	<?php }
} 

if ( ! function_exists( 'cptch_whitelist_banner' ) ) {
	function cptch_whitelist_banner() { ?>
		<table class="form-table bws_pro_version">
			<tr>
				<td valign="top"><?php _e( 'Reason', 'captcha' ); ?>
					<input disabled type="text" style="margin: 10px 0;"/><br />
					<span class="bws_info" style="line-height: 2;"><?php _e( "Allowed formats", 'captcha' ); ?>:&nbsp;<code>192.168.0.1, 192.168.0., 192.168., 192., 192.168.0.1/8, 123.126.12.243-185.239.34.54</code></span><br />
					<span class="bws_info" style="line-height: 2;"><?php _e( "Allowed separators for IPs: a comma", 'captcha' ); ?> (<code>,</code>), <?php _e( 'semicolon', 'captcha' ); ?> (<code>;</code>), <?php _e( 'ordinary space, tab, new line or carriage return', 'captcha' ); ?></span><br />
					<span class="bws_info" style="line-height: 2;"><?php _e( "Allowed separators for reasons: a comma", 'captcha' ); ?> (<code>,</code>), <?php _e( 'semicolon', 'captcha' ); ?> (<code>;</code>), <?php _e( 'tab, new line or carriage return', 'captcha' ); ?></span>
				</td>
			</tr>
		</table>
	<?php }
}