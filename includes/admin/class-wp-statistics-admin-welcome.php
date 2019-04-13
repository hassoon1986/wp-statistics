<?php

namespace WP_STATISTICS;

class Welcome {
	/**
	 * List Of WP-Statistics AddOne API
	 *
	 * @var string
	 */
	public static $addone = 'https://wp-statistics.com/wp-json/plugin/addons';

	/**
	 * Get Change Log of Last Version Wp-Statistics
	 *
	 * @var string
	 */
	public static $change_log = 'https://api.github.com/repos/wp-statistics/wp-statistics/releases/latest';

	/**
	 * Welcome constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'upgrader_process_complete', array( $this, 'do_welcome' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Initial
	 */
	public function init() {
		global $WP_Statistics;

		if ( $WP_Statistics->option->get( 'show_welcome_page', false ) and ( strpos( $_SERVER['REQUEST_URI'], '/wp-admin/index.php' ) !== false or ( isset( $_GET['page'] ) and $_GET['page'] == 'wps_overview_page' ) ) ) {
			// Disable show welcome page
			$WP_Statistics->option->update( 'first_show_welcome_page', true );
			$WP_Statistics->option->update( 'show_welcome_page', false );

			// Redirect to welcome page
			wp_redirect( \WP_Statistics_Admin_Pages::admin_url( 'wps_welcome' ) );
		}

		if ( ! $WP_Statistics->option->get( 'first_show_welcome_page', false ) ) {
			$WP_Statistics->option->update( 'show_welcome_page', true );
		}
	}

	/**
	 * Register menu
	 */
	public function menu() {
		add_submenu_page( __( 'WP-Statistics Welcome', 'wp-statistics' ), __( 'WP-Statistics Welcome', 'wp-statistics' ), __( 'WP-Statistics Welcome', 'wp-statistics' ), 'administrator', 'wps_welcome', array( $this, 'page_callback' ) );
	}

	/**
	 * Welcome page
	 */
	public function page_callback() {
		$response      = wp_remote_get( self::$addone );
		$response_code = wp_remote_retrieve_response_code( $response );
		$error         = null;
		$plugins       = array();

		// Check response
		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} else {
			if ( $response_code == '200' ) {
				$plugins = json_decode( $response['body'] );
			} else {
				$error = $response['body'];
			}
		}

		include( WP_STATISTICS_DIR . "includes/admin/templates/welcome.php" );
	}

	/**
	 * @param $upgrader_object
	 * @param $options
	 */
	public function do_welcome( $upgrader_object, $options ) {
		$current_plugin_path_name = 'wp-statistics/wp-statistics.php';

		if ( isset( $options['action'] ) and $options['action'] == 'update' and isset( $options['type'] ) and $options['type'] == 'plugin' and isset( $options['plugins'] ) ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == $current_plugin_path_name ) {
					global $WP_Statistics;

					// Enable welcome page in database
					$WP_Statistics->option->update( 'show_welcome_page', true );

					// Run the upgrade
					\WP_Statistics_Updates::do_upgrade();
				}
			}
		}
	}

	/**
	 * Show change log
	 */
	public static function show_change_log() {

		// Get Change Log From Github Api
		$response = wp_remote_get( self::$change_log );
		if ( is_wp_error( $response ) ) {
			return;
		}
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code == '200' ) {

			// Json Data To Array
			$data = json_decode( $response['body'] );

			// Load ParseDown
			include( WP_STATISTICS_DIR . "includes/lib/Parsedown.php" );
			$parse = new \Parsedown();

			// convert MarkDown To Html
			echo $parse->text( nl2br( $data->body ) );
		}
	}
}