<?php

namespace WP_STATISTICS;

class pages_page {

	public function __construct() {
		global $wpdb;

		if ( Menus::in_page( 'pages' ) ) {

			// Disable Screen Option
			add_filter( 'screen_options_show_screen', '__return_false' );

			// Check Exist Statistics For Custom Page
			if ( self::is_custom_page() ) {
				$page_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . DB::table( 'pages' ) . " WHERE `id` = " . esc_sql( $_GET['ID'] ) . " AND `type` = '" . esc_sql( $_GET['type'] ) . "'" );
				if ( $page_count < 1 ) {
					wp_die( __( 'Your request is not valid.', 'wp-statistics' ) );
				}
			}
		}
	}

	public static function is_custom_page() {
		return ( isset( $_GET['ID'] ) and isset( $_GET['type'] ) );
	}

	/**
	 * Display Html Page
	 *
	 * @throws \Exception
	 */
	public static function view() {

		// Check Show Custom Page
		if ( self::is_custom_page() ) {
			self::custom_page_statistics();
			exit;
		}

		// Page title
		$args['title'] = __( 'Top Pages', 'wp-statistics' );

		// Get Current Page Url
		$args['pageName']   = Menus::get_page_slug( 'pages' );
		$args['pagination'] = Admin_Template::getCurrentPaged();

		// Total Number
		$args['total'] = Pages::TotalCount();

		// Create WordPress Pagination
		$args['pagination'] = '';
		if ( $args['total'] > 0 ) {
			$args['pagination'] = Admin_Template::paginate_links( array(
				'total' => $args['total'],
				'echo'  => false
			) );
		}

		// Show Template Page
		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'pages/pages', 'layout/postbox.toggle', 'layout/footer' ), $args );
	}

	/**
	 * @throws \Exception
	 */
	public static function custom_page_statistics() {

		// Page ID
		$ID   = esc_html( $_GET['ID'] );
		$Type = esc_html( $_GET['type'] );

		// Page title
		$args['title'] = __( 'Page Statistics', 'wp-statistics' );

		// Get Current Page Url
		$args['pageName']   = Menus::get_page_slug( 'pages' );
		$args['custom_get'] = array(
			'ID'   => $ID,
			'type' => $Type
		);

		// Get Date-Range
		$args['DateRang'] = Admin_Template::DateRange();

		// List Of Pages From custom Type
		$args['lists'] = array();

		// Show Template Page
		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'layout/date.range', 'pages/page-chart', 'layout/footer' ), $args );
	}

}

new pages_page;