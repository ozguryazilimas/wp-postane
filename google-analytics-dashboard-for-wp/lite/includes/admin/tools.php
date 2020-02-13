<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function exactmetrics_tools_url_builder() {
	ob_start();?>
	<div class="exactmetrics-upsell-under-box">
		<h2><?php esc_html_e( "Want even more fine tuned control over your website analytics?", 'google-analytics-dashboard-for-wp' ); ?></h2>
		<p class="exactmetrics-upsell-lite-text"><?php esc_html_e( "By upgrading to ExactMetrics Pro, you can unlock the ExactMetrics URL builder that helps you better track your advertising and email marketing campaigns.", 'google-analytics-dashboard-for-wp' ); ?></p>
		<p><a href="<?php echo exactmetrics_get_upgrade_link(); ?>" class="button button-primary"><?php esc_html_e( "Click here to Upgrade", 'google-analytics-dashboard-for-wp' ); ?></a></p>
	</div>
	<?php
	echo ob_get_clean();
}
add_action( 'exactmetrics_tools_url_builder_tab', 'exactmetrics_tools_url_builder' );