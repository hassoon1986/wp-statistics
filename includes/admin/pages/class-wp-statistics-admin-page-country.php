<?php

namespace WP_STATISTICS;

class country_page {

	public function __construct() {

		if ( Menus::in_page( 'countries' ) ) {

			// Disable Screen Option
			add_filter( 'screen_options_show_screen', '__return_false' );

			// Set Default All Option for DatePicker
			add_filter( 'wp_statistics_days_ago_request', array( $this, 'set_all_option_datepicker' ) );

			// Is Validate Date Request
			$DateRequest = Admin_Template::isValidDateRequest();
			if ( ! $DateRequest['status'] ) {
				wp_die( $DateRequest['message'] );
			}
		}
	}

	/**
	 * Set All Option For DatePicker
	 */
	public function set_all_option_datepicker() {
		$first_day = Helper::get_date_install_plugin();
		return ( $first_day === false ? 30 : (int) TimeZone::getNumberDayBetween( $first_day ) );
	}

	/**
	 * Display Html Page
	 *
	 * @throws \Exception
	 */
	public static function view() {
		global $wpdb;

		// Page title
		$args['title'] = __( 'Top Countries', 'wp-statistics' );

		// Get Current Page Url
		$args['pageName'] = Menus::get_page_slug( 'countries' );
		$args['paged']    = Admin_Template::getCurrentPaged();

		// Get Date-Range
		$args['DateRang'] = Admin_Template::DateRange();

		// Total List
		$args['list']  = $wpdb->get_results(
			sprintf( "SELECT `location`, COUNT(`location`) AS `count` FROM `" . DB::table( 'visitor' ) . "` WHERE `last_counter` BETWEEN '%s' AND '%s' GROUP BY `location` ORDER BY `count` DESC",
				$args['DateRang']['from'],
				$args['DateRang']['to']
			)
		);

		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'layout/date.range', 'pages/country', 'layout/postbox.toggle', 'layout/footer' ), $args );
	}

}

new country_page;