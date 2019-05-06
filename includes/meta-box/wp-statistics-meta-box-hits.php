<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Option;
use WP_STATISTICS\TimeZone;

class hits {

	public static function get( $args = array() ) {

		// Check Number Days
		$days = ( isset( $args['days'] ) ? $args['days'] : 20 );

		// Prepare Response
		$response = self::LastHitsChart( $days );

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
	 * @param int $days
	 * @return array
	 */
	public static function LastHitsChart( $days = 20 ) {

		// Prepare Default
		$visitors = $date = $visits = array();

		// Prepare Date time
		for ( $i = $days; $i >= 0; $i -- ) {
			$date[] = TimeZone::getCurrentDate( 'M j', '-' . $i );
		}

		// Push Basic Chart Data
		$data = array(
			'days'  => $days,
			'title' => sprintf( __( 'Hits in the last %s days', 'wp-statistics' ), $days ),
			'date'  => $date
		);

		// Get Visits Chart
		if ( Option::get( 'visits' ) ) {
			for ( $i = $days; $i >= 0; $i -- ) {
				$visits[] = (int) wp_statistics_visit( '-' . $i, true );
			}
			$data['visits'] = $visits;
		}

		// Get Visitors Chart
		if ( Option::get( 'visitors' ) ) {
			for ( $i = $days; $i >= 0; $i -- ) {
				$visitors[] = (int) wp_statistics_visitor( '-' . $i, true );
			}
			$data['visitors'] = $visitors;
		}

		return $data;
	}

}