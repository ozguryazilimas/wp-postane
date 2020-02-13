<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Lite_Report_Forms extends ExactMetrics_Report {

	public $title;
	public $class   = 'ExactMetrics_Lite_Report_Forms';
	public $name    = 'forms';
	public $version = '1.0.0';
	public $level   = 'pro';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Forms', 'google-analytics-dashboard-for-wp' );
		parent::__construct();
	}

	protected function get_report_html( $data = array() ){
		return $this->get_upsell_notice();
	}
}