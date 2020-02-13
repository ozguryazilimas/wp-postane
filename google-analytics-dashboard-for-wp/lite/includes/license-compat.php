<?php
/**
 * This class is used to prevent fatal errors in legacy code
 * that others have written based on testing we've done.
 *
 * @package ExactMetrics
 */

/**
 * Class ExactMetrics_License_Compat
 */
class ExactMetrics_License_Compat {

	/**
	 * ExactMetrics_License_Shim constructor.
	 */
	public function __construct() {}

	/**
	 * @return string
	 */
	public function get_site_license_type() {
		return '';
	}

	/**
	 * @return string
	 */
	public function get_site_license_key() {
		return '';
	}

	/**
	 * @return string
	 */
	public function get_network_license_type() {
		return '';
	}

	/**
	 * @return string
	 */
	public function get_network_license_key() {
		return '';
	}

	/**
	 * @return string
	 */
	public function get_license_key() {
		return '';
	}

}
