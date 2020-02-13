<?php
/**
 * Overview Report
 *
 * Ensures all of the reports have a uniform class with helper functions.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Reports
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Report_Overview extends ExactMetrics_Report {

	public $title;
	public $class = 'ExactMetrics_Report_Overview';
	public $name = 'overview';
	public $version = '1.0.0';
	public $level = 'lite';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Overview', 'google-analytics-dashboard-for-wp' );
		parent::__construct();
	}

	/**
	 * Prepare report-specific data for output.
	 *
	 * @param array $data The data from the report before it gets sent to the frontend.
	 *
	 * @return mixed
	 */
	public function prepare_report_data( $data ) {
		// Add flags to the countries report.
		if ( ! empty( $data['data']['countries'] ) ) {
			$country_names = exactmetrics_get_country_list( true );
			foreach ( $data['data']['countries'] as $key => $country ) {
				$data['data']['countries'][ $key ]['name'] = isset( $country_names[ $country['iso'] ] ) ? $country_names[ $country['iso'] ] : $country['iso'];
			}
		}

		// Escape urls for the top pages report.
		if ( ! empty( $data['data']['toppages'] ) ) {
			foreach ( $data['data']['toppages'] as $key => $page ) {
				$title = $data['data']['toppages'][ $key ]['title'];
				$url   = '(not set)' === $title ? '' : esc_url( $data['data']['toppages'][ $key ]['hostname'] );

				$data['data']['toppages'][ $key ]['hostname'] = $url;
			}
		}

		// Bounce rate add symbol.
		if ( ! empty( $data['data']['infobox']['bounce']['value'] ) ) {
			$data['data']['infobox']['bounce']['value'] .= '%';
		}

		// Add GA links.
		if ( ! empty( $data['data'] ) ) {
			$data['data']['galinks'] = array(
				'countries' => 'https://analytics.google.com/analytics/web/#report/visitors-geo/' . ExactMetrics()->auth->get_referral_url() . $this->get_ga_report_range( $data['data'] ),
				'referrals' => 'https://analytics.google.com/analytics/web/#report/trafficsources-referrals/' . ExactMetrics()->auth->get_referral_url() . $this->get_ga_report_range( $data['data'] ),
				'topposts'  => 'https://analytics.google.com/analytics/web/#/report/content-pages/' . ExactMetrics()->auth->get_referral_url() . $this->get_ga_report_range( $data['data'] ),
			);
		}

		return $data;
	}
}
