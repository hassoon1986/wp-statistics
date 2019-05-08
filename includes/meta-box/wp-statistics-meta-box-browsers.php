<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;

class browsers {

	public static function get( $args = array() ) {
		global $wpdb;

		$Browsers      = wp_statistics_ua_list();
		$total         = $count = $top_ten = 0;
		$BrowserVisits = $top_ten_browser_color = $top_ten_browser_value = $top_ten_browser_name = array();

		// Get List Of Browsers
		foreach ( $Browsers as $Browser ) {

			//Get List Of count Visitor By Agent
			$BrowserVisits[ $Browser ] = wp_statistics_useragent( $Browser );

			//Sum This agent
			$total += $BrowserVisits[ $Browser ];
		}

		//Add Unknown Agent to total
		$total += $other_agent_count = $wpdb->get_var( 'SELECT COUNT(*) FROM `' . DB::table( 'visitor' ) . '` WHERE `agent` NOT IN (\'' . implode( "','", $Browsers ) . '\')' );

		//Sort Browser List By Visitor ASC
		arsort( $BrowserVisits );

		foreach ( $BrowserVisits as $key => $value ) {
			$top_ten += $value;
			$count ++;
			if ( $count > 9 ) {
				break;
			}

			//Get Browser name
			$browser_name = \WP_STATISTICS\UserAgent::BrowserList( strtolower( $key ) );

			$top_ten_browser_name[]  = $browser_name;
			$top_ten_browser_value[] = (int) $value;
			$top_ten_browser_color[] = Helper::GenerateRgbaColor( $count, '0.4', false );
		}

		if ( $top_ten_browser_name and $top_ten_browser_value and $other_agent_count > 0 ) {
			$top_ten_browser_name[]  = __( 'Other', 'wp-statistics' );
			$top_ten_browser_value[] = (int) ( $total - $top_ten );
			$top_ten_browser_color[] = Helper::GenerateRgbaColor( 10, '0.4', false );
		}

		// Prepare Response
		$response = array(
			'browsers_name'  => $top_ten_browser_name,
			'browsers_value' => $top_ten_browser_value,
			'browsers_color' => $top_ten_browser_color
		);

		// Check For No Data Meta Box
		if ( count( array_filter( $top_ten_browser_value ) ) < 1 ) {
			$response['no_data'] = 1;
		}

		// Response
		return $response;
	}

}