<?php
global $wpdb;

// If the button was clicked
if ( ! empty( $_POST['_wpnonce'] ) ) {
	// Form nonce check
	check_admin_referer( 'generate-post-thumbnails' );

	$apt_ag = isset( $_POST['apt-auto-generation'] ) && ! empty( $_POST['apt-auto-generation'] ) ? true : false;
	update_option( 'wbcr_apt_auto_generation', $apt_ag );

	$apt_ds = isset( $_POST['apt-delete-settings'] ) && ! empty( $_POST['apt-delete-settings'] ) ? true : false;
	update_option( 'wbcr_apt_delete_settings', $apt_ds );
}

$apt_ag = get_option( 'wbcr_apt_auto_generation' );
$apt_ds = get_option( 'wbcr_apt_delete_settings' );
?>
<div class="wrap">
	<form method="post" action="">
		<?php wp_nonce_field( 'generate-post-thumbnails' ); ?>

		<p>
			<label>
				<input type="checkbox" name="apt-auto-generation" value="1"<?php checked( true, $apt_ag, true ); ?>>
				<?php esc_html_e( 'Enable automatic post thumbnail generation', 'apt' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input type="checkbox" name="apt-delete-settings" value="1"<?php checked( true, $apt_ds, true ); ?>>
				<?php esc_html_e( 'Delete settings when removing the plugin', 'apt' ); ?>
			</label>
		</p>

		<p><input type="submit" class="button hide-if-no-js" value="<?php esc_attr_e( 'Save settings', 'apt' ); ?>"/></p>

		<noscript>
			<p>
				<em><?php esc_html_e( 'You must enable Javascript in order to proceed!', 'apt' ); ?></em>
			</p>
		</noscript>
	</form>
</div>
