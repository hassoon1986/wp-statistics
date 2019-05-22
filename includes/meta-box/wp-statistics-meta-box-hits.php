<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Option;
use WP_STATISTICS\TimeZone;

class hits {

	public static function get( $args = array() ) {

		// Check Number Days Or Between
		if ( isset( $args['from'] ) and isset( $args['to'] ) ) {
			$params = array( 'from' => $args['from'], 'to' => $args['to'] );
		} else {
			$days   = ( isset( $args['days'] ) ? $args['days'] : 20 );
			$params = array( 'ago' => $days );
		}

		// Prepare Response
		$response = self::HitsChart( $params );

		// Check For No Data Meta Box
		if ( ( isset( $response['visits'] ) and isset( $response['visitors'] ) and count( array_filter( $response['visits'] ) ) < 1 and count( array_filter( $response['visitors'] ) ) < 1 ) || ( isset( $response['visits'] ) and ! isset( $response['visitors'] ) and count( array_filter( $response['visits'] ) ) < 1 ) || ( ! isset( $response['visits'] ) and isset( $response['visitors'] ) and count( array_filter( $response['visitors'] ) ) < 1 ) ) {
			$response['no_data'] = 1;
		}

		// Response
		return $response;
	}

	/**
	 * Get Last Hits Chart
	 *
	 * @param array $args
	 * @return array
	 * @throws \Exception
	 */
	public static function HitsChart( $args = array() ) {

		// Set Default Params
		$defaults = array(
			'ago'  => 0,
			'from' => '',
			'to'   => ''
		);
		$args     = wp_parse_args( $args, $defaults );

		// Prepare Default
		$visitors     = $date = $visits = array();
		$total_visits = $total_visitors = 0;

		// Get time ago Days Or Between Two Days
		if ( $args['ago'] > 0 ) {
			$days_list = TimeZone::getListDays( array( 'from' => TimeZone::getTimeAgo( $args['ago'] ) ) );
		} else {
			$days_list = TimeZone::getListDays( array( 'from' => $args['from'], 'to' => $args['to'] ) );
		}

		// Get List Of Days
		$days_time_list = array_keys( $days_list );
		foreach ( $days_list as $k => $v ) {
			$date[] = $v['format'];
		}

		// Prepare title Hit Chart
		if ( $args['ago'] > 0 ) {
			$count_day = $args['ago'];
		} else {
			$count_day = TimeZone::getNumberDayBetween( $args['from'], $args['to'] );
		}

		// Set Title
		if ( end( $days_time_list ) == TimeZone::getCurrentDate( "Y-m-d" ) ) {
			$title = sprintf( __( 'Hits in the last %s days', 'wp-statistics' ), $count_day );
		} else {
			$title = sprintf( __( 'Hits from %s to %s', 'wp-statistics' ), $args['from'], $args['to'] );
		}

		// Push Basic Chart Data
		$data = array(
			'days'  => $count_day,
			'title' => $title,
			'date'  => $date
		);

		// Get Visits Chart
		if ( Option::get( 'visits' ) ) {
			foreach ( $days_time_list as $d ) {
				$total_visits += $visits[] = (int) wp_statistics_visit( $d, true );
			}
			$data['visits'] = $visits;
		}

		// Get Visitors Chart
		if ( Option::get( 'visitors' ) ) {
			foreach ( $days_time_list as $d ) {
				$total_visitors += $visitors[] = (int) wp_statistics_visitor( $d, true );
			}
			$data['visitors'] = $visitors;
		}

		// Set Total
		$data['total'] = array(
			'visits'   => number_format_i18n( $total_visits ),
			'visitors' => number_format_i18n( $total_visitors )
		);

		return $data;
	}

}