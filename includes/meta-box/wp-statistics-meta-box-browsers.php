<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\TimeZone;

class browsers {
	/**
	 * Get Browser ar Chart
	 *
	 * @param array $arg
	 * @return array
	 * @throws \Exception
	 */
	public static function get( $arg = array() ) {
		global $wpdb;

		// Set Default Params
		$defaults = array(
			'ago'  => 0,
			'from' => '',
			'to'   => ''
		);
		$args     = wp_parse_args( $arg, $defaults );

		// Check Default
		if ( empty( $args['from'] ) and empty( $args['to'] ) and $args['ago'] < 1 ) {
			$args['ago'] = 'all';
		}

		// Prepare Count Day
		if ( ! empty( $args['from'] ) and ! empty( $args['to'] ) ) {
			$count_day = TimeZone::getNumberDayBetween( $args['from'], $args['to'] );
		} else {
			if ( is_numeric( $args['ago'] ) and $args['ago'] > 0 ) {
				$count_day = $args['ago'];
			} else {
				$first_day = Helper::get_date_install_plugin();
				$count_day = (int) TimeZone::getNumberDayBetween( $first_day );
			}
		}

		// Get time ago Days Or Between Two Days
		if ( ! empty( $args['from'] ) and ! empty( $args['to'] ) ) {
			$days_list = TimeZone::getListDays( array( 'from' => $args['from'], 'to' => $args['to'] ) );
		} else {
			if ( is_numeric( $args['ago'] ) and $args['ago'] > 0 ) {
				$days_list = TimeZone::getListDays( array( 'from' => TimeZone::getTimeAgo( $args['ago'] ) ) );
			} else {
				$days_list = TimeZone::getListDays( array( 'from' => TimeZone::getTimeAgo( $count_day ) ) );
			}
		}

		// Get List Of Days
		$days_time_list = array_keys( $days_list );
		foreach ( $days_list as $k => $v ) {
			$date[]            = $v['format'];
			$total_daily[ $k ] = 0;
		}

		// Get List Browser
		$Browsers      = wp_statistics_ua_list();
		$total         = $count = $top_ten = 0;
		$BrowserVisits = $top_ten_browser_value = $top_ten_browser_name = array();

		// Get List Of Browsers
		foreach ( $Browsers as $Browser ) {

			//Get List Of count Visitor By Agent
			if ( empty( $args['from'] ) and empty( $args['to'] ) and $args['ago'] == "all" ) {

				// IF All Time
				$BrowserVisits[ $Browser ] = wp_statistics_useragent( $Browser );
			} else {

				// IF Custom Time
				$BrowserVisits[ $Browser ] = wp_statistics_useragent( $Browser, reset( $days_time_list ), end( $days_time_list ) );
			}

			// Set All
			$total += $BrowserVisits[ $Browser ];
		}

		//Add Unknown Agent to total
		if ( empty( $args['from'] ) and empty( $args['to'] ) and $args['ago'] == "all" ) {
			$total += $other_agent_count = $wpdb->get_var( 'SELECT COUNT(*) FROM `' . DB::table( 'visitor' ) . '` WHERE `agent` NOT IN (\'' . implode( "','", $Browsers ) . '\')' );
		} else {
			$total += $other_agent_count = $wpdb->get_var( 'SELECT COUNT(*) FROM `' . DB::table( 'visitor' ) . '` WHERE `last_counter` BETWEEN \'' . reset( $days_time_list ) . '\' AND \'' . end( $days_time_list ) . '\' AND `agent` NOT IN (\'' . implode( "','", $Browsers ) . '\')' );
		}

		//Sort Browser List By Visitor ASC
		arsort( $BrowserVisits );

		// Get List Of Browser
		foreach ( $BrowserVisits as $key => $value ) {
			$top_ten += $value;
			$count ++;
			if ( $count > 9 ) { // Max 10 Browser
				break;
			}

			//Get Browser name
			$browser_name            = \WP_STATISTICS\UserAgent::BrowserList( strtolower( $key ) );
			$top_ten_browser_name[]  = $browser_name;
			$top_ten_browser_value[] = (int) $value;
		}

		// Push Other Browser
		if ( $top_ten_browser_name and $top_ten_browser_value and $other_agent_count > 0 ) {
			$top_ten_browser_name[]  = __( 'Other', 'wp-statistics' );
			$top_ten_browser_value[] = (int) ( $total - $top_ten );
		}

		// Prepare Response
		$response = array(
			'days'           => $count_day,
			'from'           => reset( $days_time_list ),
			'to'             => end( $days_time_list ),
			'type'           => ( ( $args['from'] != "" and $args['to'] != "" ) ? 'between' : 'ago' ),
			'browsers_name'  => $top_ten_browser_name,
			'browsers_value' => $top_ten_browser_value,
			'total'          => $total
		);

		// Check For No Data Meta Box
		if ( count( array_filter( $top_ten_browser_value ) ) < 1 and ! isset( $args['no-data'] ) ) {
			$response['no_data'] = 1;
		}

		// Response
		return $response;
	}

}