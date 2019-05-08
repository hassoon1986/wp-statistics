<?php

namespace WP_STATISTICS;

class Country {
	/**
	 * Default Unknown flag
	 *
	 * @var string
	 */
	public static $unknown_location = '000';

	/**
	 * Get country codes
	 *
	 * @return array|bool|string
	 */
	public static function getList() {
		global $WP_Statistics;

		# Load From global
		if ( isset( $WP_Statistics->country_codes ) ) {
			return $WP_Statistics->country_codes;
		}

		# Load From file
		include WP_STATISTICS_DIR . "includes/defines/country-codes.php";
		if ( isset( $ISOCountryCode ) ) {
			return $ISOCountryCode;
		}

		return array();
	}

	/**
	 * Get Country flag
	 *
	 * @param $location
	 * @return string
	 */
	public static function flag( $location ) {
		$list_country = self::getList();
		if ( ! array_key_exists( $location, $list_country ) ) {
			$location = self::$unknown_location;
		}
		return WP_STATISTICS_URL . 'assets/images/flags/' . $location . '.png';
	}

	/**
	 * Get Country name by Code
	 *
	 * @param $code
	 * @return mixed
	 */
	public static function getName( $code ) {
		$list_country = self::getList();
		if ( array_key_exists( $code, $list_country ) ) {
			return $list_country[ $code ];
		}

		return $list_country[ self::$unknown_location ];
	}

	/**
	 * get Top Country List
	 *
	 * @param int $number
	 * @return array
	 */
	public static function getTop( $number = 10 ) {
		global $wpdb;

		// Load List Country Code
		$ISOCountryCode = Country::getList();

		// Get List From DB
		$list   = array();
		$result = $wpdb->get_results( "SELECT `location`, COUNT(`location`) AS `count` FROM `" . DB::table( 'visitor' ) . "` GROUP BY `location` ORDER BY `count` DESC LIMIT " . $number );
		foreach ( $result as $item ) {
			$item->location = strtoupper( $item->location );
			$list[]         = array(
				'name'   => $ISOCountryCode[ $item->location ],
				'flag'   => self::flag( $item->location ),
				'link'   => Menus::admin_url( 'countries', array( 'country' => $item->location ) ),
				'number' => number_format_i18n( $item->count )
			);
		}

		return $list;
	}


}