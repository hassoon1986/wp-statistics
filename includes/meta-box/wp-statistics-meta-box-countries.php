<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;

class countries {

	public static function get( $args = array() ) {
		global $wpdb;

		// Check Number of Country
		$number = ( isset( $args['number'] ) ? $args['number'] : 10 );

		// Get Country Code List
		$ISOCountryCode = Helper::get_country_codes();

		// Create Response Object
		$response = array();

		// Get List Top 10 Country
		$result = $wpdb->get_results( "SELECT `location`, COUNT(`location`) AS `count` FROM `" . DB::table( 'visitor' ) . "` GROUP BY `location` ORDER BY `count` DESC LIMIT " . $number );
		foreach ( $result as $item ) {
			$item->location = strtoupper( $item->location );
			$response[]     = array(
				'name'   => $ISOCountryCode[ $item->location ],
				'flag'   => Helper::get_country_flag( $item->location ),
				'link'   => Menus::admin_url( 'countries', array( 'country' => $item->location ) ),
				'number' => number_format_i18n( $item->count )
			);
		}

		// Check For No Data Meta Box
		if ( count( $result ) < 1 ) {
			$response['no_data'] = 1;
		}

		// Response
		return $response;
	}

}