<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_STATISTICS\SearchEngine;
use WP_STATISTICS\TimeZone;

class summary {

	public static function get( $args = array() ) {

		return $args;
	}


	/**
	 * Get Summary Hits in WP-Statistics
	 *
	 * @param array $componenet
	 * @return array
	 */
	public static function data( $componenet = array() ) {
		$data = array();

		// User Online
		if ( Option::get( 'useronline' ) ) {
			$data['user_online'] = array(
				'value' => wp_statistics_useronline(),
				'link'  => Menus::admin_url( 'online' )
			);
		}

		// Get Visitors
		if ( Option::get( 'visitors' ) ) {
			$data['visitors'] = array();

			// Today
			$data['visitors']['today'] = array(
				'link'  => Menus::admin_url( 'visitors', array( 'hitdays' => 1 ) ),
				'value' => number_format_i18n( wp_statistics_visitor( 'today', null, true ) )
			);

			// Yesterday
			$data['visitors']['yesterday'] = array(
				'link'  => Menus::admin_url( 'visitors', array( 'hitdays' => 1 ) ),
				'value' => number_format_i18n( wp_statistics_visitor( 'yesterday', null, true ) )
			);

			// Week
			$data['visitors']['week'] = array(
				'link'  => Menus::admin_url( 'visitors', array( 'hitdays' => 7 ) ),
				'value' => number_format_i18n( wp_statistics_visitor( 'week', null, true ) )
			);

			// Month
			$data['visitors']['month'] = array(
				'link'  => Menus::admin_url( 'visitors', array( 'hitdays' => 30 ) ),
				'value' => number_format_i18n( wp_statistics_visitor( 'month', null, true ) )
			);

			// Year
			$data['visitors']['year'] = array(
				'link'  => Menus::admin_url( 'visitors', array( 'hitdays' => 365 ) ),
				'value' => number_format_i18n( wp_statistics_visitor( 'year', null, true ) )
			);

			// Total
			$data['visitors']['total'] = array(
				'link'  => Menus::admin_url( 'visitors', array( 'hitdays' => 365 ) ),
				'value' => number_format_i18n( wp_statistics_visitor( 'total', null, true ) )
			);

		}

		// Get Visits
		if ( Option::get( 'visits' ) ) {
			$data['visits'] = array();

			// Today
			$data['visits']['today'] = array(
				'link'  => Menus::admin_url( 'hits', array( 'hitdays' => 1 ) ),
				'value' => number_format_i18n( wp_statistics_visit( 'today' ) )
			);

			// Yesterday
			$data['visits']['yesterday'] = array(
				'link'  => Menus::admin_url( 'hits', array( 'hitdays' => 1 ) ),
				'value' => number_format_i18n( wp_statistics_visit( 'yesterday' ) )
			);

			// Week
			$data['visits']['week'] = array(
				'link'  => Menus::admin_url( 'hits', array( 'hitdays' => 7 ) ),
				'value' => number_format_i18n( wp_statistics_visit( 'week' ) )
			);

			// Month
			$data['visits']['month'] = array(
				'link'  => Menus::admin_url( 'hits', array( 'hitdays' => 30 ) ),
				'value' => number_format_i18n( wp_statistics_visit( 'month' ) )
			);

			// Year
			$data['visits']['year'] = array(
				'link'  => Menus::admin_url( 'hits', array( 'hitdays' => 365 ) ),
				'value' => number_format_i18n( wp_statistics_visit( 'year' ) )
			);

			// Total
			$data['visits']['total'] = array(
				'link'  => Menus::admin_url( 'hits', array( 'hitdays' => 365 ) ),
				'value' => number_format_i18n( wp_statistics_visit( 'total' ) )
			);
		}

		// Get Search Engine Detail
		if ( in_array( 'search-engine', $componenet ) ) {
			$data['search-engine'] = array();
			$total_today           = 0;
			$total_yesterday       = 0;
			foreach ( SearchEngine::getList() as $key => $value ) {

				// Get Statistics
				$today     = wp_statistics_searchengine( $value['tag'], 'today' );
				$yesterday = wp_statistics_searchengine( $value['tag'], 'yesterday' );

				// Push to List
				$data['search-engine'][ $key ] = array(
					'name'      => __( $value['name'], 'wp-statistics' ),
					'logo'      => $value['logo_url'],
					'today'     => number_format_i18n( $today ),
					'yesterday' => number_format_i18n( $yesterday )
				);

				// Sum Search engine
				$total_today     += $today;
				$total_yesterday += $yesterday;
			}
			$data['search-engine-total'] = array(
				'today'     => number_format_i18n( $total_today ),
				'yesterday' => number_format_i18n( $total_yesterday ),
				'total'     => number_format_i18n( wp_statistics_searchengine( 'all' ) ),
			);
		}

		// Get Current Date and Time
		if ( in_array( 'timezone', $componenet ) ) {
			$data['timezone'] = array(
				'option-link' => admin_url( 'options-general.php' ),
				'date'        => TimeZone::getCurrentDate_i18n( get_option( 'date_format' ) ),
				'time'        => TimeZone::getCurrentDate_i18n( get_option( 'time_format' ) )
			);
		}

		// Get Hits chartJs (10 Day Ago)
		if ( in_array( 'hit-chart', $componenet ) ) {
			$days     = ( isset( $componenet['days'] ) ? $componenet['days'] : 20 );
			$visitors = $date = $visits = array();

			// Prepare Date time
			for ( $i = $days; $i >= 0; $i -- ) {
				$date[] = TimeZone::getCurrentDate( 'M j', '-' . $i );
			}

			// Push Basic Chart Data
			$data['hits-chart'] = array(
				'days'  => $days,
				'title' => sprintf( __( 'Hits in the last %s days', 'wp-statistics' ), $days ),
				'date'  => $date
			);

			// Get Visits Chart
			if ( Option::get( 'visits' ) ) {
				for ( $i = $days; $i >= 0; $i -- ) {
					$visits[] = (int) wp_statistics_visit( '-' . $i, true );
				}
				$data['hits-chart']['visits'] = $visits;
			}

			// Get Visitors Chart
			if ( Option::get( 'visitors' ) ) {
				for ( $i = $days; $i >= 0; $i -- ) {
					$visitors[] = (int) wp_statistics_visitor( '-' . $i, true );
				}
				$data['hits-chart']['visitors'] = $visitors;
			}
		}

		return $data;
	}

}