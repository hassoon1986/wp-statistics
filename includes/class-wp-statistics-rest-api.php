<?php

namespace WP_STATISTICS;

class RestApi {
	/**
	 * WP-Statistics Rest API namespace
	 *
	 * @var string
	 */
	public static $namespace = 'wpstatistics/v2';

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
	 *
	 * @return \WP_REST_Response
	 */
	public static function response( $message, $status = 200 ) {
		if ( $status == 200 ) {
			$output = array(
				'data' => $message,
				'error'   => array(),
			);
		} else {
			$output = array(
				'error' => array(
					'code'    => $status,
					'data' => $message,
				),
			);
		}
		return new \WP_REST_Response( $output, $status );
	}

}
