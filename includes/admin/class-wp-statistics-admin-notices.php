<?php

namespace WP_STATISTICS;

class Admin_Notices {
	/**
	 * List Of Admin Notice
	 *
	 * @var array
	 */
	private static $core_notices = array(
		'use_cache_plugin',
		'enable_rest_api'
	);

	/**
	 * Admin Notice constructor.
	 */
	public function __construct() {

		// Instantiate the Admin Notice
		add_action( 'admin_notices', array( $this, "setup" ), 20, 2 );
	}

	public function setup() {
		if ( is_admin() and ! Helper::is_request( 'ajax' ) ) {
			$list_notice = self::$core_notices;
			foreach ( $list_notice as $notice ) {
				self::{$notice}();
			}
		}
	}

	public function use_cache_plugin() {
		$plugin = Helper::is_active_cache_plugin();
		if ( ! Option::get( 'use_cache_plugin' ) and $plugin['status'] === true ) {
			$text = ( $plugin['plugin'] == "core" ? __( 'WP_CACHE is enable in your WordPress', 'wp-statistics' ) : sprintf( __( 'You are using %s plugin in WordPress', 'wp-statistics' ), $plugin['plugin'] ) );
			Helper::wp_admin_notice( $text . ", " . sprintf( __( 'Please enable %1$sCache Setting%2$s in WP Statistics.', 'wp-statistics' ), '<a href="' . Menus::admin_url( 'settings' ) . '">', '</a>' ), 'warning', true );
		}
	}

	public function enable_rest_api() {

		if ( Option::get( 'use_cache_plugin' ) and false === ( $check_rest_api = get_transient( 'check-wp-statistics-rest' ) ) ) {

			// Check Connect To WordPress Rest API
			$status  = true;
			$request = wp_remote_post( get_rest_url( null, RestApi::$namespace . '/enable' ), array( 'method' => 'POST', 'body' => array( 'connect' => 'wp-statistics' ), 'timeout' => 30 ) );
			if ( is_wp_error( $request ) ) {
				$status = false;
			}
			$body = wp_remote_retrieve_body( $request );
			$data = json_decode( $body, true );
			if ( isset( $data['error'] ) ) {
				$status = false;
			}

			if ( $status === true ) {
				set_transient( 'check-wp-statistics-rest', array( "status" => "enable" ), 3 * HOUR_IN_SECONDS );
			} else {
				Helper::wp_admin_notice( sprintf( __( 'Here is an error associated with Connecting WordPress Rest API, Please Flushing rewrite rules or activate wp rest api for performance WP-Statistics Plugin Cache / Go %1$sSettings->Permalinks%2$s', 'wp-statistics' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">', '</a>' ), 'warning', true );
			}
		}

	}
}