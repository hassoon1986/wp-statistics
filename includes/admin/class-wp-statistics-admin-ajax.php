<?php

namespace WP_STATISTICS;

class Ajax {
	/**
	 * WP-Statistics Ajax
	 */
	function __construct() {

		/**
		 * List Of Setup Ajax request in Wordpress
		 */
		$list = array(
			'close_notice',
			'delete_agents',
			'delete_platforms',
			'delete_ip',
			'empty_table',
			'purge_data',
			'purge_visitor_hits'
		);
		foreach ( $list as $method ) {
			add_action( 'wp_ajax_wp_statistics_' . $method, array( $this, $method . '_action_callback' ) );
		}
	}

	/**
	 * Setup an AJAX action to close the notice on the overview page.
	 */
	public function close_notice_action_callback() {

		if ( Helper::is_request( 'ajax' ) and User::Access( 'manage' ) and isset( $_REQUEST['notice'] ) ) {
			switch ( $_REQUEST['notice'] ) {
				case 'donate':
					Option::update( 'disable_donation_nag', true );
					break;

				case 'suggestion':
					Option::update( 'disable_suggestion_nag', true );
					break;
			}

			Option::update( 'admin_notices', false );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to delete an agent in the optimization page.
	 */
	public function delete_agents_action_callback() {
		global $wpdb;

		if ( Helper::is_request( 'ajax' ) and User::Access( 'manage' ) ) {
			$agent = $_POST['agent-name'];

			if ( $agent ) {

				$result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM " . DB::table( 'visitor' ) . " WHERE `agent` = %s", $agent )
				);

				if ( $result ) {
					echo sprintf(
						__( '%s agent data deleted successfully.', 'wp-statistics' ),
						'<code>' . $agent . '</code>'
					);
				} else {
					_e( 'No agent data found to remove!', 'wp-statistics' );
				}

			} else {
				_e( 'Please select the desired items.', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to delete a platform in the optimization page.
	 */
	public function delete_platforms_action_callback() {
		global $wpdb;

		if ( Helper::is_request( 'ajax' ) and User::Access( 'manage' ) ) {
			$platform = $_POST['platform-name'];

			if ( $platform ) {

				$result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM " . DB::table( 'visitor' ) . " WHERE `platform` = %s", $platform )
				);

				if ( $result ) {
					echo sprintf(
						__( '%s platform data deleted successfully.', 'wp-statistics' ),
						'<code>' . htmlentities( $platform, ENT_QUOTES ) . '</code>'
					);
				} else {
					_e( 'No platform data found to remove!', 'wp-statistics' );
				}
			} else {
				_e( 'Please select the desired items.', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to delete a ip in the optimization page.
	 */
	public function delete_ip_action_callback() {
		global $wpdb;

		if ( Helper::is_request( 'ajax' ) and User::Access( 'manage' ) ) {
			$ip_address = sanitize_text_field( $_POST['ip-address'] );

			if ( $ip_address ) {

				$result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM {$wpdb->prefix}statistics_visitor WHERE `ip` = %s", $ip_address )
				);

				if ( $result ) {
					echo sprintf(
						__( '%s IP data deleted successfully.', 'wp-statistics' ),
						'<code>' . htmlentities( $ip_address, ENT_QUOTES ) . '</code>'
					);
				} else {
					_e( 'No IP address data found to remove!', 'wp-statistics' );
				}
			} else {
				_e( 'Please select the desired items.', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to empty a table in the optimization page.
	 */
	public function empty_table_action_callback() {

		// Check Ajax Request
		if ( ! Helper::is_request( 'ajax' ) ) {
			exit;
		}

		//Check isset Table-post
		if ( ! isset( $_POST['table-name'] ) ) {
			_e( 'Please select the desired items.', 'wp-statistics' );
			exit;
		}

		//Check Valid Table name
		$table_name    = sanitize_text_field( $_POST['table-name'] );
		$list_db_table = DB::table( 'all', 'historical' );
		if ( ! array_key_exists( $table_name, $list_db_table ) ) {
			_e( 'Access denied!', 'wp-statistics' );
			exit;
		}

		if ( User::Access( 'manage' ) ) {

			if ( $table_name == "all" ) {
				$x_tbl = 1;
				foreach ( $list_db_table as $tbl_key => $tbl_name ) {
					echo ( $x_tbl > 1 ? '<br>' : '' ) . DB::EmptyTable( $tbl_name );
					$x_tbl ++;
				}
			} else {
				echo DB::EmptyTable( DB::table( $table_name ) );
			}

		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to purge old data in the optimization page.
	 */
	public function purge_data_action_callback() {

		if ( Helper::is_request( 'ajax' ) and User::Access( 'manage' ) ) {
			$purge_days = 0;

			if ( array_key_exists( 'purge-days', $_POST ) ) {
				// Get the number of days to purge data before.
				$purge_days = intval( $_POST['purge-days'] );
			}

			echo Purge::purge_data( $purge_days );
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to purge visitors with more than a defined number of hits.
	 */
	public function purge_visitor_hits_action_callback() {

		if ( Helper::is_request( 'ajax' ) and User::Access( 'manage' ) ) {
			$purge_hits = 10;

			if ( array_key_exists( 'purge-hits', $_POST ) ) {
				// Get the number of days to purge data before.
				$purge_hits = intval( $_POST['purge-hits'] );
			}

			if ( $purge_hits < 10 ) {
				_e( 'Number of hits must be greater than or equal to 10!', 'wp-statistics' );
			} else {
				echo Purge::purge_visitor_hits( $purge_hits );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

}

new Ajax;