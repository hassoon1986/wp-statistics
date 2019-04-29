<?php

namespace WP_STATISTICS;

class RestApi {
	/**
	 * WP-Statistics Rest API namespace
	 *
	 * @var string
	 */
	public static $namespace = 'wp-statistics/v2';

	/**
	 * Get WP-Statistics Options
	 *
	 * @var array
	 */
	public $option;

	/**
	 * Use WordPress DB Class
	 *
	 * @var \wpdb
	 */
	protected $db;

	/**
	 * RestApi constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->option = Option::getOptions();
		$this->db     = $wpdb;
	}

	/**
	 * Handle Response
	 *
	 * @param $message
	 * @param int $status
	 * @return \WP_REST_Response
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
	 */
	public static function response( $message, $status = 200 ) {
		if ( $status == 200 ) {
			$output = array(
				'data' => $message
			);
		} else {
			$output = array(
				'error' => array(
					'status'  => $status,
					'message' => $message,
				)
			);
		}
		return new \WP_REST_Response( $output, $status );
	}

	/**
	 * Check User Access To WP-Statistics Rest API
	 */
	public function permissions_access_user() {
		return current_user_can( wp_statistics_validate_capability( Option::get( 'read_capability', 'manage_option' ) ) );
	}
}
