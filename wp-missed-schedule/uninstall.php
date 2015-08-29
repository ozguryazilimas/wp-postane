<?php 

	/**
	 * @package     WordPress Plugin
	 * @subpackage  WP Missed Schedule
	 * @description Uninstall Module
	 * @status      Stable Code in Becoming!
	 * @todolist    Extend Multisite Support - WordPress 4.4+ Compatibility
	 *
	 * @indentation //www.gnu.org/prep/standards/standards.html
	 * @license     //www.gnu.org/licenses/gpl-2.0.html
	 * @link        //wordpress.org/plugins/global-admin-bar-hide-or-remove/
	 *
	 * @branche 2014
	 * @since   2014.1231.2
	 * @version 2014.1231.2014
	 * @build   2015-08-25
	 * @author  sLa NGjI's @ slangji.wordpress.com
	 *
	 * @since  WordPress 2.7+
	 * @tested WordPress 4.4+
	 */

	defined ( 'ABSPATH' ) OR exit;

	defined ( 'WPINC' ) OR exit;

	defined ( 'WP_UNINSTALL_PLUGIN' ) OR exit;

	$option_names = array( 
			'byrev_fixshedule_next_verify',
			'missed_schedule',
			'scheduled_post_guardian_next_run',
			'simpul_missed_schedule',
			'wpt_scheduled_check',
			'wp_missed_schedule',
			'wp_missed_schedule_beta',
			'wp_missed_schedule_dev',
			'wp_missed_schedule_gold',
			'wp_missed_schedule_pro',
			'wp_scheduled_missed',
			'wp_scheduled_missed_beta',
			'wp_scheduled_missed_dev',
			'wp_scheduled_missed_gold',
			'wp_scheduled_missed_pro',
			'wp_scheduled_missed_options',
			'wp_scheduled_missed_options_beta',
			'wp_scheduled_missed_options_dev',
			'wp_scheduled_missed_options_gold',
			'wp_scheduled_missed_options_pro' 
	);

	global $wp_version;

	if ( $wp_version >= 3.0 )
		{
			if ( !is_multisite() )
				{
					foreach ( $option_names as $option_name )
						{
							delete_option( $option_name );
						}
				}

			if ( is_multisite() )
				{
					foreach ( $option_names as $option_name )
						{
							delete_option( $option_name );
						}

					global $wpdb;

					$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
					$original_blog_id = get_current_blog_id();

					foreach ( $blog_ids as $blog_id )
						{
							switch_to_blog( $blog_id );

							foreach ( $option_names as $option_name )
								{
									delete_site_option( $option_name );
								}
						}
					switch_to_blog( $original_blog_id );
				}
		}
?>