<?php

namespace WP_STATISTICS;

class plugins_page {
	/**
	 * This function displays the HTML for the page.
	 */
	public static function view() {

		// Activate or deactivate the selected plugin
		if ( isset( $_GET['action'] ) ) {

			if ( $_GET['action'] == 'activate' ) {
				$result = activate_plugin( $_GET['plugin'] . '/' . $_GET['plugin'] . '.php' );
				if ( is_wp_error( $result ) ) {
					Helper::wp_admin_notice( $result->get_error_message(), "error" );
				} else {
					Helper::wp_admin_notice( __( 'Add-On activated.', 'wp-statistics' ), "success" );
				}

			}

			if ( $_GET['action'] == 'deactivate' ) {
				$result = deactivate_plugins( $_GET['plugin'] . '/' . $_GET['plugin'] . '.php' );
				if ( is_wp_error( $result ) ) {
					Helper::wp_admin_notice( $result->get_error_message(), "error" );
				} else {
					Helper::wp_admin_notice( __( 'Add-On deactivated.', 'wp-statistics' ), "success" );
				}
			}
		}

		$response      = wp_remote_get( Welcome::$addone );
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

		include WP_STATISTICS_DIR . 'includes/admin/templates/plugins.php';
	}

}