<?php
/**
 * Ask for some love.
 *
 * @package    ExactMetrics
 * @author     ExactMetrics
 * @since      7.0.7
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, ExactMetrics LLC
 */
class ExactMetrics_Review {
	/**
	 * Primary class constructor.
	 *
	 * @since 7.0.7
	 */
	public function __construct() {
		// Admin notice requesting review.
		add_action( 'admin_notices', array( $this, 'review_request' ) );
		add_action( 'wp_ajax_exactmetrics_review_dismiss', array( $this, 'review_dismiss' ) );
	}
	/**
	 * Add admin notices as needed for reviews.
	 *
	 * @since 7.0.7
	 */
	public function review_request() {
		// Only consider showing the review request to admin users.
		if ( ! is_super_admin() ) {
			return;
		}

		// If the user has opted out of product annoucement notifications, don't
		// display the review request.
		if ( exactmetrics_get_option( 'hide_am_notices', false ) || exactmetrics_get_option( 'network_hide_am_notices', false ) ) {
			return;
		}
		// Verify that we can do a check for reviews.
		$review = get_option( 'exactmetrics_review' );
		$time   = time();
		$load   = false;

		if ( ! $review ) {
			$review = array(
				'time'      => $time,
				'dismissed' => false,
			);
			update_option( 'exactmetrics_review', $review );
		} else {
			// Check if it has been dismissed or not.
			if ( ( isset( $review['dismissed'] ) && ! $review['dismissed'] ) && ( isset( $review['time'] ) && ( ( $review['time'] + DAY_IN_SECONDS ) <= $time ) ) ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}

		$this->review();
	}

	/**
	 * Maybe show review request.
	 *
	 * @since 7.0.7
	 */
	public function review() {
		// Fetch when plugin was initially installed.
		$activated = get_option( 'exactmetrics_over_time', array() );
		$ua_code   = exactmetrics_get_ua();

		if ( ! empty( $activated['connected_date'] ) ) {
			// Only continue if plugin has been tracking for at least 14 days.
			$days = 14;
			if ( exactmetrics_get_option( 'gadwp_migrated', 0 ) > 0 ) {
				$days = 21;
			}
			if ( ( $activated['connected_date'] + ( DAY_IN_SECONDS * $days ) ) > time() ) {
				return;
			}
		} else {
			if ( empty( $activated ) ) {
				$data = array(
					'installed_version' => EXACTMETRICS_VERSION,
					'installed_date'    => time(),
					'installed_pro'     => exactmetrics_is_pro_version(),
				);
			} else {
				$data = $activated;
			}
			// If already has a UA code mark as connected now.
			if ( ! empty( $ua_code ) ) {
				$data['connected_date'] = time();
			}

			update_option( 'exactmetrics_over_time', $data );
			return;
		}

		// Only proceed with displaying if the user is tracking.
		if ( empty( $ua_code ) ) {
			return;
		}

		$feedback_url = add_query_arg( array(
			'wpf192157_24' => untrailingslashit( home_url() ),
			'wpf192157_26' => exactmetrics_get_license_key(),
			'wpf192157_27' => exactmetrics_is_pro_version() ? 'pro' : 'lite',
			'wpf192157_28' => EXACTMETRICS_VERSION,
		), 'https://www.exactmetrics.com/plugin-feedback/' );
		$feedback_url = exactmetrics_get_url( 'review-notice', 'feedback', $feedback_url );
		// We have a candidate! Output a review message.
		?>
		<div class="notice notice-info is-dismissible exactmetrics-review-notice">
			<div class="exactmetrics-review-step exactmetrics-review-step-1">
				<p><?php esc_html_e( 'Are you enjoying ExactMetrics?', 'google-analytics-dashboard-for-wp' ); ?></p>
				<p>
					<a href="#" class="exactmetrics-review-switch-step" data-step="3"><?php esc_html_e( 'Yes', 'google-analytics-dashboard-for-wp' ); ?></a><br />
					<a href="#" class="exactmetrics-review-switch-step" data-step="2"><?php esc_html_e( 'Not Really', 'google-analytics-dashboard-for-wp' ); ?></a>
				</p>
			</div>
			<div class="exactmetrics-review-step exactmetrics-review-step-2" style="display: none">
				<p><?php esc_html_e( 'We\'re sorry to hear you aren\'t enjoying ExactMetrics. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'google-analytics-dashboard-for-wp' ); ?></p>
				<p>
					<a href="<?php echo esc_url( $feedback_url ); ?>" class="exactmetrics-dismiss-review-notice exactmetrics-review-out"><?php esc_html_e( 'Give Feedback', 'google-analytics-dashboard-for-wp' ); ?></a><br>
					<a href="#" class="exactmetrics-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'No thanks', 'google-analytics-dashboard-for-wp' ); ?></a>
				</p>
			</div>
			<div class="exactmetrics-review-step exactmetrics-review-step-3" style="display: none">
				<p><?php esc_html_e( 'That’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'google-analytics-dashboard-for-wp' ); ?></p>
				<p><strong><?php echo wp_kses( __( '~ Syed Balkhi<br>Co-Founder of ExactMetrics', 'google-analytics-dashboard-for-wp' ), array( 'br' => array() ) ); ?></strong></p>
				<p>
					<a href="https://wordpress.org/support/plugin/google-analytics-dashboard-for-wp/reviews/?filter=5#new-post" class="exactmetrics-dismiss-review-notice exactmetrics-review-out" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ok, you deserve it', 'google-analytics-dashboard-for-wp' ); ?></a><br>
					<a href="#" class="exactmetrics-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Nope, maybe later', 'google-analytics-dashboard-for-wp' ); ?></a><br>
					<a href="#" class="exactmetrics-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'I already did', 'google-analytics-dashboard-for-wp' ); ?></a>
				</p>
			</div>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function ( $ ) {
				$( document ).on( 'click', '.exactmetrics-dismiss-review-notice, .exactmetrics-review-notice button', function ( event ) {
					if ( ! $( this ).hasClass( 'exactmetrics-review-out' ) ) {
						event.preventDefault();
					}
					$.post( ajaxurl, {
						action: 'exactmetrics_review_dismiss'
					} );
					$( '.exactmetrics-review-notice' ).remove();
				} );

				$( document ).on( 'click', '.exactmetrics-review-switch-step', function ( e ) {
					e.preventDefault();
					var target = $( this ).attr( 'data-step' );
					if ( target ) {
						var notice = $( this ).closest( '.exactmetrics-review-notice' );
						var review_step = notice.find( '.exactmetrics-review-step-' + target );
						if ( review_step.length > 0 ) {
							notice.find( '.exactmetrics-review-step:visible').fadeOut( function (  ) {
								review_step.fadeIn();
							});
						}
					}
				})
			} );
		</script>
		<?php
	}
	/**
	 * Dismiss the review admin notice
	 *
	 * @since 7.0.7
	 */
	public function review_dismiss() {
		$review              = get_option( 'exactmetrics_review', array() );
		$review['time']      = time();
		$review['dismissed'] = true;
		update_option( 'exactmetrics_review', $review );

		if ( is_super_admin() && is_multisite() ) {
			$site_list = get_sites();
			foreach ( (array) $site_list as $site ) {
				switch_to_blog( $site->blog_id );

				update_option( 'exactmetrics_review', $review );

				restore_current_blog();
			}
		}

		die;
	}
}

new ExactMetrics_Review();
