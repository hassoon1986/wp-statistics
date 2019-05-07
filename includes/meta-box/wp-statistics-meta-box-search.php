<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Option;
use WP_STATISTICS\SearchEngine;
use WP_STATISTICS\TimeZone;

class search {

	public static function get( $args = array() ) {

		$days           = ( isset( $args['number'] ) ? $args['number'] : 10 );
		$total_stats    = Option::get( 'chart_totals' );
		$date           = $stats = $total_daily = $search_engine_list = array();
		$search_engines = SearchEngine::getList();

		for ( $i = $days; $i >= 0; $i -- ) {
			$date[] = TimeZone::getCurrentDate( 'M j', '-' . $i );
		}

		foreach ( $search_engines as $se ) {

			// Get Search engine information
			$search_engine_list[] = $se;

			// Get Number of Search
			for ( $i = $days; $i >= 0; $i -- ) {
				if ( ! array_key_exists( $i, $total_daily ) ) {
					$total_daily[ $i ] = 0;
				}

				$stat                   = wp_statistics_searchengine( $se['tag'], '-' . $i );
				$stats[ $se['name'] ][] = $stat;
				$total_daily[ $i ]      += $stat;
			}
		}

		// Prepare Response
		$response = array(
			'title'         => sprintf( __( 'Search engine referrals in the last %s days', 'wp-statistics' ), $days ),
			'date'          => $date,
			'stat'          => $stats,
			'search-engine' => $search_engine_list,
			'total'         => array(
				'active' => ( $total_stats == 1 ? 1 : 0 ),
				'color'  => '180, 180, 180',
				'stat'   => array_values( $total_daily )
			)
		);

		// Check For No Data Meta Box
		if ( count( array_filter( $total_daily ) ) < 1 ) {
			$response['no_data'] = 1;
		}

		// Response
		return $response;
	}

}