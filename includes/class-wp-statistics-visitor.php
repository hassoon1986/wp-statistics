<?php

namespace WP_STATISTICS;

class Visitor {
	/**
	 * For each visit to account for several hits.
	 *
	 * @var int
	 */
	public static $coefficient = 1;

	/**
	 * Get Coefficient
	 */
	public static function getCoefficient() {
		$coefficient = Option::get( 'coefficient', self::$coefficient );
		return is_numeric( $coefficient ) and $coefficient > 0 ? $coefficient : self::$coefficient;
	}

	/**
	 * Check Active Record Visitors
	 *
	 * @return mixed
	 */
	public static function active() {
		return ( has_filter( 'wp_statistics_active_visitors' ) ) ? apply_filters( 'wp_statistics_active_visitors', true ) : Option::get( 'visitors' );
	}

	/**
	 * Save new Visitor To DB
	 *
	 * @param array $visitor
	 * @return INT
	 */
	public static function save_visitor( $visitor = array() ) {
		global $wpdb;

		# Action Before Save Visitor To DB
		do_action( 'wp_statistics_before_save_visitor', $visitor );

		# Add Filter Insert ignore
		add_filter( 'query', 'wp_statistics_ignore_insert', 10 );

		# Save to WordPress Database
		$wpdb->insert( DB::table( 'visitor' ), $visitor, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ) );

		# Get Visitor ID
		$visitor_id = $wpdb->insert_id;

		# Remove ignore filter
		remove_filter( 'query', 'wp_statistics_ignore_insert', 10 );

		# Do Action After Save New Visitor
		do_action( 'wp_statistics_after_save_visitor', $visitor_id, $visitor );

		return $visitor_id;
	}

	/**
	 * Check This ip has recorded in Custom Day
	 *
	 * @param $ip
	 * @param $date
	 * @return bool
	 */
	public static function exist_ip_in_day( $ip, $date = false ) {
		global $wpdb;
		$visitor = $wpdb->get_row( "SELECT * FROM `" . DB::table( 'visitor' ) . "` WHERE `last_counter` = '" . ( $date === false ? TimeZone::getCurrentDate( 'Y-m-d' ) : $date ) . "' AND `ip` = '{$ip}'" );
		return ( ! $visitor ? false : $visitor );
	}

	/**
	 * Record Uniq Visitor Detail in DB
	 *
	 * @param array $arg
	 * @return bool|INT
	 */
	public static function record( $arg = array() ) {
		global $wpdb;

		// Define the array of defaults
		$defaults = array(
			'location'         => GeoIP::getDefaultCountryCode(),
			'exclusion_match'  => false,
			'exclusion_reason' => '',
		);
		$args     = wp_parse_args( $arg, $defaults );

		// Check User Exclusion
		if ( $args['exclusion_match'] === false || $args['exclusion_reason'] == 'Honeypot' ) {

			// Get User IP
			$user_ip = ( IP::getHashIP() != false ? IP::getHashIP() : IP::StoreIP() );

			// Get User Agent
			$user_agent = UserAgent::getUserAgent();

			//Check Exist This User in Current Day
			$same_visitor = self::exist_ip_in_day( $user_ip );

			// If we have a new Visitor in Day
			if ( ! $same_visitor ) {

				// Prepare Visitor information
				$visitor = array(
					'last_counter' => TimeZone::getCurrentDate( 'Y-m-d' ),
					'referred'     => Referred::get(),
					'agent'        => $user_agent['browser'],
					'platform'     => $user_agent['platform'],
					'version'      => $user_agent['version'],
					'ip'           => $user_ip,
					'location'     => $args['location'],
					'UAString'     => ( Option::get( 'store_ua' ) == true ? UserAgent::getHttpUserAgent() : '' ),
					'hits'         => 1,
					'honeypot'     => ( $args['exclusion_reason'] == 'Honeypot' ? 1 : 0 ),
				);
				$visitor = apply_filters( 'wp_statistics_visitor_information', $visitor );

				//Save Visitor TO DB
				$visitor_id = self::save_visitor( $visitor );

			} else {

				//Get Current Visitor ID
				$visitor_id = $same_visitor->ID;

				// Update Same Visitor Hits
				if ( $args['exclusion_reason'] != 'Honeypot' and $args['exclusion_reason'] != 'Robot threshold' ) {

					// Action Before Visitor Update
					do_action( 'wp_statistics_before_update_visitor_hits', $visitor_id, $same_visitor );

					// Update Visitor Count in DB
					$sql = $wpdb->prepare( 'UPDATE `' . DB::table( 'visitor' ) . '` SET `hits` = `hits` + %d WHERE `ID` = %d', 1, $visitor_id );
					$wpdb->query( $sql );
				}
			}
		}

		return ( isset( $visitor_id ) ? $visitor_id : false );
	}

	/**
	 * Save visitor relationShip
	 *
	 * @param $page_id
	 * @param $visitor_id
	 * @return int
	 */
	public static function save_visitors_relationships( $page_id, $visitor_id ) {
		global $wpdb;

		// Action Before Save Visitor Relation Ship
		do_action( 'wp_statistics_before_save_visitor_relationship', $page_id, $visitor_id );

		// Save To DB
		$wpdb->insert(
			DB::table( 'visitor_relationships' ),
			array(
				'visitor_id' => $visitor_id,
				'page_id'    => $page_id,
				'date'       => current_time( 'mysql' )
			),
			array( '%d', '%d', '%s' )
		);

		return $wpdb->insert_id;
	}


}