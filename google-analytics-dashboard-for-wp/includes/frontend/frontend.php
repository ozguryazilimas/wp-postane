<?php
/**
 * Frontend events tracking.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Get frontend tracking options.
 *
 * This function is used to return an array of parameters
 * for the frontend_output() function to output. These are
 * generally dimensions and turned on GA features.
 *
 * @since 7.0.0
 * @access public
 *
 * @return array Array of the options to use.
 */
function exactmetrics_tracking_script( ) {
    require_once plugin_dir_path( EXACTMETRICS_PLUGIN_FILE ) . 'includes/frontend/class-tracking-abstract.php';

    $mode = is_preview() ? 'preview' : 'analytics';

    do_action( 'exactmetrics_tracking_before_' . $mode );
    do_action( 'exactmetrics_tracking_before', $mode );
    if ( $mode === 'preview' ) {
        require_once plugin_dir_path( EXACTMETRICS_PLUGIN_FILE ) . 'includes/frontend/tracking/class-tracking-preview.php';
        $tracking = new ExactMetrics_Tracking_Preview();
        echo $tracking->frontend_output();
    } else {
         require_once plugin_dir_path( EXACTMETRICS_PLUGIN_FILE ) . 'includes/frontend/tracking/class-tracking-analytics.php';
         $tracking = new ExactMetrics_Tracking_Analytics();
         echo $tracking->frontend_output();
    }

    do_action( 'exactmetrics_tracking_after_' . $mode );
    do_action( 'exactmetrics_tracking_after', $mode );
}
add_action( 'wp_head', 'exactmetrics_tracking_script', 6 );
//add_action( 'login_head', 'exactmetrics_tracking_script', 6 );

/**
 * Get frontend tracking options.
 *
 * This function is used to return an array of parameters
 * for the frontend_output() function to output. These are
 * generally dimensions and turned on GA features.
 *
 * @since 6.0.0
 * @access public
 *
 * @return array Array of the options to use.
 */
function exactmetrics_events_tracking( ) {
    $track_user    = exactmetrics_track_user();

    if ( $track_user ) {
        require_once plugin_dir_path( EXACTMETRICS_PLUGIN_FILE ) . 'includes/frontend/events/class-analytics-events.php';
        new ExactMetrics_Analytics_Events();
    } else {
        // User is in the disabled group or events mode is off
    }
}
add_action( 'template_redirect', 'exactmetrics_events_tracking', 9 );

/**
 * Add the UTM source parameters in the RSS feeds to track traffic.
 *
 * @since 6.0.0
 * @access public
 *
 * @param string $guid The link for the RSS feed.
 *
 * @return string The new link for the RSS feed.
 */
function exactmetrics_rss_link_tagger( $guid ) {
    global $post;

    if ( exactmetrics_get_option( 'tag_links_in_rss', false ) ){
        if ( is_feed() ) {
            if ( exactmetrics_get_option( 'allow_anchor', false ) ) {
                $delimiter = '#';
            } else {
                $delimiter = '?';
                if ( strpos( $guid, $delimiter ) > 0 ) {
                    $delimiter = '&amp;';
                }
            }
            return $guid . $delimiter . 'utm_source=rss&amp;utm_medium=rss&amp;utm_campaign=' . urlencode( $post->post_name );
        }
    }
    return $guid;
}
add_filter( 'the_permalink_rss', 'exactmetrics_rss_link_tagger', 99 );


/**
 * Checks used for loading the frontend scripts/admin bar button.
 */
function exactmetrics_prevent_loading_frontend_reports() {
	return ! current_user_can( 'exactmetrics_view_dashboard' ) || exactmetrics_get_option( 'hide_admin_bar_reports' ) || function_exists( 'exactmetrics_is_reports_page' ) && exactmetrics_is_reports_page() || function_exists( 'exactmetrics_is_settings_page' ) && exactmetrics_is_settings_page();
}

/**
 * Add an admin bar menu item on the frontend.
 *
 * @since 7.5.0
 *
 * @return void
 */
function exactmetrics_add_admin_bar_menu() {
	if ( exactmetrics_prevent_loading_frontend_reports() ) {
		return;
	}

	global $wp_admin_bar;

	$args = array(
		'id'    => 'exactmetrics_frontend_button',
		'title' => '<span class="ab-icon dashicons-before dashicons-chart-bar"></span> ExactMetrics', // Maybe allow translation?
		'href'  => '#',
	);

	if ( method_exists( $wp_admin_bar, 'add_menu' ) ) {
		$wp_admin_bar->add_menu( $args );
	}
}

add_action( 'admin_bar_menu', 'exactmetrics_add_admin_bar_menu', 999 );

/**
 * Load the scripts needed for the admin bar.
 *
 * @since 7.5.0
 *
 * @return void
 */
function exactmetrics_frontend_admin_bar_scripts() {
	if ( exactmetrics_prevent_loading_frontend_reports() ) {
		return;
	}

	$version_path    = exactmetrics_is_pro_version() ? 'pro' : 'lite';
	$rtl             = is_rtl() ? '.rtl' : '';
	$frontend_js_url = defined( 'EXACTMETRICS_LOCAL_FRONTEND_JS_URL' ) && EXACTMETRICS_LOCAL_FRONTEND_JS_URL ? EXACTMETRICS_LOCAL_FRONTEND_JS_URL : plugins_url( $version_path . '/assets/vue/js/frontend.js', EXACTMETRICS_PLUGIN_FILE );

	if ( ! defined( 'EXACTMETRICS_LOCAL_FRONTEND_JS_URL' ) ) {
		wp_enqueue_style( 'exactmetrics-vue-frontend-style', plugins_url( $version_path . '/assets/vue/css/frontend' . $rtl . '.css', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version() );
		wp_enqueue_script( 'exactmetrics-vue-vendors', plugins_url( $version_path . '/assets/vue/js/chunk-frontend-vendors.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
		wp_enqueue_script( 'exactmetrics-vue-common', plugins_url( $version_path . '/assets/vue/js/chunk-common.js', EXACTMETRICS_PLUGIN_FILE ), array(), exactmetrics_get_asset_version(), true );
	} else {
		wp_enqueue_script( 'exactmetrics-vue-vendors', EXACTMETRICS_LOCAL_VENDORS_JS_URL, array(), exactmetrics_get_asset_version(), true );
		wp_enqueue_script( 'exactmetrics-vue-common', EXACTMETRICS_LOCAL_COMMON_JS_URL, array(), exactmetrics_get_asset_version(), true );
	}

	wp_register_script( 'exactmetrics-vue-frontend', $frontend_js_url, array(), exactmetrics_get_asset_version(), true );
	wp_enqueue_script( 'exactmetrics-vue-frontend' );

	$page_title = is_singular() ? get_the_title() : exactmetrics_get_page_title();
	// We do not have a current auth.
	$site_auth = ExactMetrics()->auth->get_viewname();
	$ms_auth   = is_multisite() && ExactMetrics()->auth->get_network_viewname();

	// Check if any of the other admin scripts are enqueued, if so, use their object.
	if ( ! wp_script_is( 'exactmetrics-vue-script' ) && ! wp_script_is( 'exactmetrics-vue-reports' ) && ! wp_script_is( 'exactmetrics-vue-widget' ) ) {
		$reports_url = is_network_admin() ? add_query_arg( 'page', 'exactmetrics_reports', network_admin_url( 'admin.php' ) ) : add_query_arg( 'page', 'exactmetrics_reports', admin_url( 'admin.php' ) );
		wp_localize_script(
			'exactmetrics-vue-frontend',
			'exactmetrics',
			array(
				'ajax'                => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'mi-admin-nonce' ),
				'network'             => is_network_admin(),
				'translations'        => wp_get_jed_locale_data( exactmetrics_is_pro_version() ? 'exactmetrics-premium' : 'google-analytics-dashboard-for-wp' ),
				'assets'              => plugins_url( $version_path . '/assets/vue', EXACTMETRICS_PLUGIN_FILE ),
				'addons_url'          => is_multisite() ? network_admin_url( 'admin.php?page=exactmetrics_network#/addons' ) : admin_url( 'admin.php?page=exactmetrics_settings#/addons' ),
				'page_id'             => is_singular() ? get_the_ID() : false,
				'page_title'          => $page_title,
				'plugin_version'      => EXACTMETRICS_VERSION,
				'shareasale_id'       => exactmetrics_get_shareasale_id(),
				'shareasale_url'      => exactmetrics_get_shareasale_url( exactmetrics_get_shareasale_id(), '' ),
				'is_admin'            => is_admin(),
				'reports_url'         => $reports_url,
				'authed'              => $site_auth || $ms_auth,
				'getting_started_url' => is_multisite() ? network_admin_url( 'admin.php?page=exactmetrics_network#/about/getting-started' ) : admin_url( 'admin.php?page=exactmetrics_settings#/about/getting-started' ),
				'wizard_url'          => is_network_admin() ? network_admin_url( 'index.php?page=exactmetrics-onboarding' ) : admin_url( 'index.php?page=exactmetrics-onboarding' ),
			)
		);
	}
}

add_action( 'wp_enqueue_scripts', 'exactmetrics_frontend_admin_bar_scripts' );
add_action( 'admin_enqueue_scripts', 'exactmetrics_frontend_admin_bar_scripts', 1005 );


/**
 * Load the tracking notice for logged in users.
 */
function exactmetrics_administrator_tracking_notice() {
	// Don't do anything for guests.
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Only show this to users who are not tracked.
	if ( exactmetrics_track_user() ) {
		return;
	}

	// Only show when tracking.
	$ua = exactmetrics_get_ua();
	if ( empty( $ua ) ) {
		return;
	}

	// Don't show if already dismissed.
	if ( get_option( 'exactmetrics_frontend_tracking_notice_viewed', false ) ) {
		return;
	}

	// Automatically dismiss when loaded.
	update_option( 'exactmetrics_frontend_tracking_notice_viewed', 1 );

	?>
	<div class="exactmetrics-tracking-notice exactmetrics-tracking-notice-hide">
		<div class="exactmetrics-tracking-notice-icon">
			<img src="<?php echo esc_url( plugins_url( 'assets/images/em-mascot.png', EXACTMETRICS_PLUGIN_FILE ) ); ?>" width="40" alt="ExactMetrics Mascot" />
		</div>
		<div class="exactmetrics-tracking-notice-text">
			<h3><?php esc_html_e( 'Tracking is Disabled for Administrators', 'google-analytics-dashboard-for-wp' ); ?></h3>
			<p>
				<?php
				$doc_url = 'https://exactmetrics.com/docs/tracking-disabled-administrators-editors';
				$doc_url = add_query_arg( array(
					'utm_source'   => exactmetrics_is_pro_version() ? 'proplugin' : 'liteplugin',
					'utm_medium'   => 'frontend-notice',
					'utm_campaign' => 'admin-tracking-doc',
				), $doc_url );
				// Translators: %s is the link to the article where more details about tracking are listed.
				printf( esc_html__( 'To keep stats accurate, we do not load Google Analytics scripts for admin users. %1$sLearn More &raquo;%2$s', 'google-analytics-dashboard-for-wp' ), '<a href="' . esc_url( $doc_url ) . '" target="_blank">', '</a>' );
				?>
			</p>
		</div>
		<div class="exactmetrics-tracking-notice-close">&times;</div>
	</div>
	<style type="text/css">
		.exactmetrics-tracking-notice {
			position: fixed;
			bottom: 20px;
			right: 15px;
			font-family: Arial, Helvetica, "Trebuchet MS", sans-serif;
			background: #fff;
			box-shadow: 0 0 10px 0 #dedede;
			padding: 6px 5px;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 380px;
			max-width: calc( 100% - 30px );
			border-radius: 6px;
			transition: bottom 700ms ease;
			z-index: 10000;
		}

		.exactmetrics-tracking-notice h3 {
			font-size: 13px;
			color: #222;
			font-weight: 700;
			margin: 0 0 8px;
			padding: 0;
			line-height: 1;
			border: none;
		}

		.exactmetrics-tracking-notice p {
			font-size: 13px;
			color: #7f7f7f;
			font-weight: 400;
			margin: 0;
			padding: 0;
			line-height: 1.2;
			border: none;
		}

		.exactmetrics-tracking-notice p a {
			color: #7f7f7f;
			font-size: 13px;
			line-height: 1.2;
			margin: 0;
			padding: 0;
			text-decoration: underline;
			font-weight: 400;
		}

		.exactmetrics-tracking-notice p a:hover {
			color: #7f7f7f;
			text-decoration: none;
		}

		.exactmetrics-tracking-notice-icon img {
			height: auto;
			display: block;
			margin: 0;
		}

		.exactmetrics-tracking-notice-icon {
			padding: 14px;
			background-color: #f4f3f7;
			border-radius: 6px;
			flex-grow: 0;
			flex-shrink: 0;
			margin-right: 12px;
		}

		.exactmetrics-tracking-notice-close {
			padding: 0;
			margin: 0 3px 0 0;
			border: none;
			box-shadow: none;
			border-radius: 0;
			color: #7f7f7f;
			background: transparent;
			line-height: 1;
			align-self: flex-start;
			cursor: pointer;
			font-weight: 400;
		}

		.exactmetrics-tracking-notice.exactmetrics-tracking-notice-hide {
			bottom: -200px;
		}
	</style>
	<?php

	if ( ! wp_script_is( 'jquery', 'queue' ) ) {
		wp_enqueue_script( 'jquery' );
	}
	?>
	<script>
		if ( 'undefined' !== typeof jQuery ) {
			jQuery( document ).ready( function ( $ ) {
				/* Don't show the notice if we don't have a way to hide it (no js, no jQuery). */
				$( document.querySelector( '.exactmetrics-tracking-notice' ) ).removeClass( 'exactmetrics-tracking-notice-hide' );
				$( document.querySelector( '.exactmetrics-tracking-notice-close' ) ).on( 'click', function ( e ) {
					e.preventDefault();
					$( this ).closest( '.exactmetrics-tracking-notice' ).addClass( 'exactmetrics-tracking-notice-hide' );
					$.ajax( {
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						method: 'POST',
						data: {
							action: 'exactmetrics_dismiss_tracking_notice',
							nonce: '<?php echo esc_js( wp_create_nonce( 'exactmetrics-tracking-notice' ) ); ?>',
						}
					} );
				} );
			} );
		}
	</script>
	<?php
}

add_action( 'wp_footer', 'exactmetrics_administrator_tracking_notice', 300 );

/**
 * Ajax handler to hide the tracking notice.
 */
function exactmetrics_dismiss_tracking_notice() {

	check_ajax_referer( 'exactmetrics-tracking-notice', 'nonce' );

	update_option( 'exactmetrics_frontend_tracking_notice_viewed', 1 );

	wp_die();

}

add_action( 'wp_ajax_exactmetrics_dismiss_tracking_notice', 'exactmetrics_dismiss_tracking_notice' );

/**
 * If the legacy shortcodes are not registered, make sure they don't output.
 */
function exactmetrics_maybe_handle_legacy_shortcodes() {

	if ( ! shortcode_exists( 'gadwp_useroptout' ) ) {
		add_shortcode( 'gadwp_useroptout', '__return_empty_string' );
	}

}

add_action( 'init', 'exactmetrics_maybe_handle_legacy_shortcodes', 1000 );
