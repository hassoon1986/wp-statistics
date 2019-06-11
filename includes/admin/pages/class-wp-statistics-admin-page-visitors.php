<?php

namespace WP_STATISTICS;

class visitors_page {

	public function __construct() {

		if ( Menus::in_page( 'visitors' ) ) {

			// Disable Screen Option
			add_filter( 'screen_options_show_screen', '__return_false' );

			// Set Default All Option for DatePicker
			add_filter( 'wp_statistics_days_ago_request', array( '\WP_STATISTICS\Helper', 'set_all_option_datepicker' ) );

			// Is Validate Date Request
			$DateRequest = Admin_Template::isValidDateRequest();
			if ( ! $DateRequest['status'] ) {
				wp_die( $DateRequest['message'] );
			}
		}
	}

	/**
	 * Display Html Page
	 *
	 * @throws \Exception
	 */
	public static function view() {

		// Page title
		$args['title'] = ( count( $_GET ) > 1 ? __( 'Visitors', 'wp-statistics' ) : __( 'Recent Visitors', 'wp-statistics' ) );

		// Get Current Page Url
		$args['pageName'] = Menus::get_page_slug( 'visitors' );
		$args['paged']    = Admin_Template::getCurrentPaged();

		// Get Date-Range
		$args['DateRang'] = Admin_Template::DateRange();
		$date_link        = array( 'from' => $args['DateRang']['from'], 'to' => $args['DateRang']['to'] );

		// Create Default SQL Params
		$sql[] = array( 'key' => 'last_counter', 'compare' => 'BETWEEN', 'from' => $args['DateRang']['from'], 'to' => $args['DateRang']['to'] );

		// Create Sub List
		$args['sub']['all'] = array( 'title' => __( 'All', 'wp-statistics' ), 'count' => Visitor::Count( $sql ), 'active' => ( isset( $_GET['platform'] ) || isset( $_GET['agent'] ) || isset( $_GET['referred'] ) || isset( $_GET['ip'] ) || isset( $_GET['location'] ) ? false : true ), 'link' => Menus::admin_url( 'visitors' ) );

		/**
		 * IP Filter
		 */
		if ( isset( $_GET['ip'] ) ) {

			// Add Params To SQL
			$sql[] = array( 'key' => 'ip', 'compare' => 'LIKE', 'value' => trim( $_GET['ip'] ) );

			// Set New Sub List
			$args['sub'][ $_GET['ip'] ] = array( 'title' => $_GET['ip'], 'count' => Visitor::Count( $sql ), 'active' => ( ( isset( $_GET['ip'] ) and $_GET['ip'] == $_GET['ip'] ) ? true : false ), 'link' => add_query_arg( array_merge( $date_link, array( 'ip' => $_GET['ip'] ) ), Menus::admin_url( 'visitors' ) ) );

			/**
			 * Location Filter
			 */
		} elseif ( isset( $_GET['location'] ) ) {

			// Add Params To SQL
			$sql[] = array( 'key' => 'location', 'compare' => 'LIKE', 'value' => trim( $_GET['location'] ) );

			// Set New Sub List
			$args['sub'][ $_GET['location'] ] = array( 'title' => Country::getName( $_GET['location'] ), 'count' => Visitor::Count( $sql ), 'active' => ( ( isset( $_GET['location'] ) and $_GET['location'] == $_GET['location'] ) ? true : false ), 'link' => add_query_arg( array_merge( $date_link, array( 'location' => $_GET['location'] ) ), Menus::admin_url( 'visitors' ) ) );

			/**
			 * Platform Filter
			 */
		} elseif ( isset( $_GET['platform'] ) ) {

			// Add Params To SQL
			$sql[] = array( 'key' => 'platform', 'compare' => 'LIKE', 'value' => trim( Helper::getUrlDecode( $_GET['platform'] ) ) );

			// Set New Sub List
			$args['sub'][ $_GET['platform'] ] = array( 'title' => Helper::getUrlDecode( $_GET['platform'] ), 'count' => Visitor::Count( $sql ), 'active' => ( ( isset( $_GET['platform'] ) and $_GET['platform'] == $_GET['platform'] ) ? true : false ), 'link' => add_query_arg( array_merge( $date_link, array( 'platform' => $_GET['platform'] ) ), Menus::admin_url( 'visitors' ) ) );

			/**
			 * Agent Filter (Default)
			 */
		} else {

			// Add to SQL
			if ( isset( $_GET['agent'] ) ) {
				$sql[] = array( 'key' => 'agent', 'compare' => 'LIKE', 'value' => trim( $_GET['agent'] ) );
			}

			// Get List Of Browser
			$browsers = UserAgent::BrowserList();
			foreach ( $browsers as $key => $se ) {
				$args['sub'][ $key ] = array( 'title' => $se, 'count' => Visitor::Count( array_merge( $sql, array( 'key' => 'agent', 'compare' => 'LIKE', 'value' => $key ) ) ), 'active' => ( ( isset( $_GET['agent'] ) and $_GET['agent'] == $key ) ? true : false ), 'link' => add_query_arg( array_merge( $date_link, array( 'agent' => $key ) ), Menus::admin_url( 'visitors' ) ) );
			}

		}

		// Get Current View
		$CurrentView = array_filter( $args['sub'], function ( $val, $key ) {
			return $val['active'] === true;
		}, ARRAY_FILTER_USE_BOTH );

		//Get Total List
		$args['total'] = $CurrentView[ key( $CurrentView ) ]['count'];
		$args['list']  = array();
		if ( $args['total'] > 0 ) {
			$args['list'] = Visitor::get( array(
				'sql'      => "SELECT * FROM `" . DB::table( 'visitor' ) . "` " . Helper::getConditionSQL( $sql ) . " ORDER BY ID DESC",
				'per_page' => Admin_Template::$item_per_page,
				'paged'    => $args['paged'],
			) );
		}

		// Create WordPress Pagination
		$args['pagination'] = '';
		if ( $args['total'] > 0 ) {
			$args['pagination'] = Admin_Template::paginate_links( array(
				'total' => $args['total'],
				'echo'  => false
			) );
		}

		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'layout/date.range', 'pages/visitors', 'layout/footer' ), $args );
	}

}

new visitors_page;