<?php

namespace WP_STATISTICS;

class visitors_page {

	public function __construct() {

		if ( Menus::in_page( 'visitors' ) ) {

			// Disable Screen Option
			add_filter( 'screen_options_show_screen', '__return_false' );
		}
	}

	/**
	 * Display Html Page
	 *
	 * @throws \Exception
	 */
	public static function view() {
		global $wpdb;

		// Page title
		$args['title'] = __( 'Recent Visitors', 'wp-statistics' );

		// Get Current Page Url
		$args['pageName'] = Menus::get_page_slug( 'visitors' );
		$args['paged']    = Admin_Template::getCurrentPaged();

		//Get Sub List
		$args['sub']['all'] = array( 'title' => __( 'All', 'wp-statistics' ), 'count' => $wpdb->get_var( "SELECT COUNT(*) FROM `" . DB::table( 'visitor' ) . "`" ), 'active' => ( isset( $_GET['referred'] ) || isset( $_GET['ip'] ) || isset( $_GET['location'] ) ? false : true ), 'link' => Menus::admin_url( 'visitors' ) );
		if ( isset( $_GET['ip'] ) ) {
			$args['sub'][ $_GET['ip'] ] = array( 'title' => $_GET['ip'], 'count' => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . DB::table( 'visitor' ) . "` WHERE `ip` LIKE %s", $_GET['ip'] ) ), 'active' => ( ( isset( $_GET['ip'] ) and $_GET['ip'] == $_GET['ip'] ) ? true : false ), 'link' => add_query_arg( 'ip', $_GET['ip'], Menus::admin_url( 'visitors' ) ) );
		} elseif ( isset( $_GET['location'] ) ) {
			$args['sub'][ $_GET['location'] ] = array( 'title' => Country::getName( $_GET['location'] ), 'count' => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . DB::table( 'visitor' ) . "` WHERE `location` LIKE %s", $_GET['location'] ) ), 'active' => ( ( isset( $_GET['location'] ) and $_GET['location'] == $_GET['location'] ) ? true : false ), 'link' => add_query_arg( 'location', $_GET['location'], Menus::admin_url( 'visitors' ) ) );
		} else {
			$browsers = UserAgent::BrowserList();
			foreach ( $browsers as $key => $se ) {
				$args['sub'][ $key ] = array( 'title' => $se, 'count' => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . DB::table( 'visitor' ) . "` WHERE `agent` LIKE %s", $key ) ), 'active' => ( ( isset( $_GET['agent'] ) and $_GET['agent'] == $key ) ? true : false ), 'link' => add_query_arg( 'agent', $key, Menus::admin_url( 'visitors' ) ) );
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
			$where        = ( isset( $_GET['ip'] ) ? "WHERE `ip` LIKE '{$_GET['ip']}'" : ( isset( $_GET['agent'] ) ? "WHERE `agent` LIKE '{$_GET['agent']}'" : ( isset( $_GET['location'] ) ? "WHERE `location` LIKE '{$_GET['location']}'" : '' ) ) );
			$args['list'] = Visitor::get( array(
				'sql'      => "SELECT * FROM `" . DB::table( 'visitor' ) . "` " . $where . " ORDER BY ID DESC",
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

		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'pages/visitors', 'layout/postbox.toggle', 'layout/footer' ), $args );
	}

}

new visitors_page;