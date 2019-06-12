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
			'purge_visitor_hits',
			'visitors_page_filters'
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

	/**
	 * Show Page Visitors Filter
	 */
	public function visitors_page_filters_action_callback() {

		if ( Helper::is_request( 'ajax' ) and isset( $_REQUEST['page'] ) ) {

			// Run only Visitors Page
			if ( $_REQUEST['page'] != "visitors" ) {
				exit;
			}

			// Check Refer Ajax
			check_ajax_referer( 'wp_rest', 'wps_nonce' );

			// Create Output object
			$filter = array( 'from' => '', 'to' => '', 'ip' => '' );

			// Check Data filter
			$_exist_date_filter = false;
			foreach ( array( 'from', 'to' ) as $item ) {
				if ( isset( $_REQUEST[ $item ] ) and ! empty( $_REQUEST[ $item ] ) ) {
					$filter[ $item ]    = $_REQUEST[ $item ];
					$_exist_date_filter = true;
				}
			}

			// Browsers
			$filter['browsers'] = array();
			$browsers           = UserAgent::BrowserList();
			foreach ( $browsers as $key => $se ) {
				$filter['browsers'][ $key ] = array( 'title' => $se, 'active' => ( ( isset( $_REQUEST['agent'] ) and $_REQUEST['agent'] == $key ) ? true : false ) );
			}

			// Location
			$filter['location'] = array();
			$country_list       = Country::getList();
			foreach ( $country_list as $key => $name ) {
				$filter['location'][ $key ] = array( 'title' => $name, 'active' => ( ( isset( $_REQUEST['location'] ) and $_REQUEST['location'] == $key ) ? true : false ) );
			}

			// Push First "000" Unknown to End of List
			$first_key = key( $filter['location'] );
			$first_val = $filter['location'][ $first_key ];
			unset( $filter['location'][ $first_key ] );
			$filter['location'][ $first_key ] = $first_val;

			// Platforms
			$filter['platform'] = array();
			$platforms_list     = RestAPI::request( array( 'route' => 'metabox', 'params' => array_merge( array( 'name' => 'platforms', 'number' => 15, 'order' => 'DESC' ), ( $_exist_date_filter ? array( 'from' => $filter['from'], 'to' => $filter['to'] ) : array() ) ) ) );
			for ( $x = 0; $x < count( $platforms_list['platform_name'] ); $x ++ ) {
				$filter['platform'][ $platforms_list['platform_name'][ $x ] ] = array( 'title' => $platforms_list['platform_name'][ $x ], 'active' => ( ( isset( $_REQUEST['platform'] ) and $_REQUEST['platform'] == $platforms_list['platform_name'][ $x ] ) ? true : false ) );
			}

			// Referrer
			$filter['referrer'] = array();
			$referrer_list      = Referred::getList( ( $_exist_date_filter === true ? array( 'from' => $filter['from'], 'to' => $filter['to'], 'min' => 50, 'limit' => 300 ) : array( 'min' => 50, 'limit' => 300 ) ) );
			foreach ( $referrer_list as $site ) {
				$filter['referrer'][ $site->domain ] = array( 'title' => $site->domain, 'active' => ( ( isset( $_REQUEST['referrer'] ) and $_REQUEST['referrer'] == $site->domain ) ? true : false ) );
			}

			// IP
			$filter['ip'] = ( isset( $_REQUEST['ip'] ) ? trim( $_REQUEST['ip'] ) : '' );

			// Send Json
			wp_send_json( $filter );
		}
		exit;
	}

}

new Ajax;