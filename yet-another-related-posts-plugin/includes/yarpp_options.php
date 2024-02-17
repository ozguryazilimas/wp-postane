<?php
/**
 * @global $wpdb WPDB
 * @global $wp_version string
 * @global $yarpp YARPP
 */
global $wpdb, $wp_version, $yarpp;

/* Check to see that templates are in the right place */
if ( ! $yarpp->diagnostic_custom_templates() ) {

	$template_option = yarpp_get_option( 'template' );
	if ( $template_option !== false && $template_option !== 'thumbnails' ) {
		yarpp_set_option( 'template', false );
	}

	$template_option = yarpp_get_option( 'rss_template' );
	if ( $template_option !== false && $template_option !== 'thumbnails' ) {
		yarpp_set_option( 'rss_template', false );
	}
}

/**
 * @since 3.3  Move version checking here, in PHP.
 */
if ( current_user_can( 'update_plugins' ) ) {
	$yarpp_version_info = $yarpp->version_info();

	/*
	 * These strings are not localizable, as long as the plugin data on wordpress.org cannot be.
	 */
	$slug        = 'yet-another-related-posts-plugin';
	$plugin_name = 'Yet Another Related Posts Plugin';
	$file        = basename( YARPP_DIR ) . '/yarpp.php';
	if ( $yarpp_version_info && isset( $yarpp_version_info['result'] ) && $yarpp_version_info['result'] === 'new' ) {

		/* Make sure the update system is aware of this version. */
		$current = get_site_transient( 'update_plugins' );
		if ( ! isset( $current->response[ $file ] ) ) {
			delete_site_transient( 'update_plugins' );
			wp_update_plugins();
		}

		echo '<div class="updated"><p>';
		$details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=600&height=800' );
		printf(
			__(
				'There is a new version of %1$s available.' .
				'<a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a>' .
				'or <a href="%5$s">update automatically</a>.',
				'yet-another-related-posts-plugin'
			),
			$plugin_name,
			esc_url( $details_url ),
			esc_attr( $plugin_name ),
			$yarpp_version_info['current']['version'],
			wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file, 'upgrade-plugin_' . $file )
		);
		echo '</p></div>';

	} elseif ( isset( $yarpp_version_info['result'] ) && $yarpp_version_info['result'] === 'newbeta' ) {

		echo '<div class="updated"><p>';
		printf(
			__(
				'There is a new beta (%s) of Yet Another Related Posts Plugin. ' .
				'You can <a href="%s">download it here</a> at your own risk.',
				'yet-another-related-posts-plugin'
			),
			$yarpp_version_info['beta']['version'],
			$yarpp_version_info['beta']['url']
		);
		echo '</p></div>';

	}
}

/* MyISAM Check */
require 'yarpp_myisam_notice.php';

/* This is not a yarpp plugin update, it is an yarpp option update */
if ( isset( $_POST['update_yarpp'] ) && check_admin_referer( 'update_yarpp', 'update_yarpp-nonce' ) ) {
	$new_options = array();
	foreach ( $yarpp->default_options as $option => $default ) {
		if ( is_bool( $default ) ) {
			$new_options[ $option ] = isset( $_POST[ $option ] );
		}
		if ( ( is_string( $default ) || is_int( $default ) ) &&
			isset( $_POST[ $option ] ) && is_string( $_POST[ $option ] ) ) {
			// Sanitize input
			$new_options[ $option ] = stripslashes( wp_kses_post ($_POST[ $option ]) );
		}
	}

	if ( isset( $_POST['weight'] ) ) {
		$new_options['weight']      = array();
		$new_options['require_tax'] = array();
		// if we're going to use titles or bodies, make sure those indexes exist.
		if ( isset( $_POST['weight']['title'] ) && $_POST['weight']['title'] !== 'no' ) {
			$yarpp->enable_fulltext_titles();
		}
		if ( isset( $_POST['weight']['body'] ) && $_POST['weight']['body'] !== 'no' ) {
			$yarpp->enable_fulltext_contents();
		}
		foreach ( (array) $_POST['weight'] as $key => $value ) {
			if ( $value == 'consider' ) {
				$new_options['weight'][ $key ] = 1;
			}
			if ( $value == 'consider_extra' ) {
				$new_options['weight'][ $key ] = YARPP_EXTRA_WEIGHT;
			}
		}
		foreach ( (array) $_POST['weight']['tax'] as $tax => $value ) {
			if ( $value == 'consider' ) {
				$new_options['weight']['tax'][ $tax ] = 1;
			}
			if ( $value == 'consider_extra' ) {
				$new_options['weight']['tax'][ $tax ] = YARPP_EXTRA_WEIGHT;
			}
			if ( $value == 'require_one' ) {
				$new_options['weight']['tax'][ $tax ] = 1;
				$new_options['require_tax'][ $tax ]   = 1;
			}
			if ( $value == 'require_more' ) {
				$new_options['weight']['tax'][ $tax ] = 1;
				$new_options['require_tax'][ $tax ]   = 2;
			}
		}
	}

	if ( isset( $_POST['auto_display_post_types'] ) ) {
		$new_options['auto_display_post_types'] = array_keys( $_POST['auto_display_post_types'] );
	} else {
		$new_options['auto_display_post_types'] = array();
	}

	// The new value for "recent only" will be used directly in MySQL query, so make sure its sanitized.
	if ( isset( $_POST['recent_only'] ) ) {
		if ( in_array(
			$_POST['recent_units'],
			array_keys( $yarpp->recent_units() )
		) ) {
			$unit = $_POST['recent_units'];
		} else {
			$unit = 'day';
		}
		$recent = ( (int) $_POST['recent_number'] ) . ' ' . $unit;
	} else {
		$recent = false;
	}
	$new_options['recent'] = $recent;

	if ( isset( $_POST['exclude'] ) ) {
		$new_options['exclude'] = implode( ',', array_keys( $_POST['exclude'] ) );
	} else {
		$new_options['exclude'] = '';
	}

	if ( isset( $_POST['same_post_type'] ) ) {
		$new_options['cross_relate'] = false;
	} else {
		$new_options['cross_relate'] = true;
	}

	if ( isset( $_POST['include_post_type'] ) ) {
		$new_options['include_post_type'] = implode( ',', array_keys( $_POST['include_post_type'] ) );
	} else {
		$new_options['include_post_type'] = '';
	}
	$new_options['include_sticky_posts'] = isset( $_POST['include_sticky_posts'] ) ? 1 : 0;
		$new_options['template']         = $_POST['use_template'] == 'custom' ? $_POST['template_file'] :
		( $_POST['use_template'] == 'thumbnails' ? 'thumbnails' : false );
	$new_options['rss_template']         = $_POST['rss_use_template'] == 'custom' ? $_POST['rss_template_file'] :
		( $_POST['rss_use_template'] == 'thumbnails' ? 'thumbnails' : false );

	$new_options = apply_filters( 'yarpp_settings_save', $new_options );

	yarpp_set_option( $new_options );

	echo '<div class="updated fade"><p>' . __( 'Options saved!', 'yet-another-related-posts-plugin' ) . '</p></div>';
}

wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'yarpp_display_exclude_terms', 'yarpp_display_exclude_terms-nonce', false );
wp_nonce_field( 'yarpp_optin_data', 'yarpp_optin_data-nonce', false );
wp_nonce_field( 'yarpp_display_preview', 'yarpp_display_preview-nonce', false );

if ( ! count( $yarpp->admin->get_templates() ) && $yarpp->admin->can_copy_templates() ) {
	wp_nonce_field( 'yarpp_copy_templates', 'yarpp_copy_templates-nonce', false );
}

require YARPP_DIR . '/includes/phtmls/yarpp_options.phtml';
