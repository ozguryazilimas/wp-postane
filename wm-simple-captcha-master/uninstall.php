<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

$option_name = 'wmsimplecaptcha';

// For Single site
if ( !is_multisite() ) 
{
	delete_option($option_name);
	delete_option($option_name.'_per_page_default');
	delete_option($option_name.'_activated_plugin_error');
} 
// For Multisite
else 
{
    // For regular options.
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();
    foreach ( $blog_ids as $blog_id ) 
    {
        switch_to_blog($blog_id);
		delete_option($option_name);
		delete_option($option_name.'_per_page_default');
		delete_option($option_name.'_activated_plugin_error');	
    }
    switch_to_blog( $original_blog_id );

    // For site options.
    delete_option($option_name);
	delete_option($option_name.'_per_page_default');
	delete_option($option_name.'_activated_plugin_error');
}