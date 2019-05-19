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

		# Add Filter Insert ignore
		add_filter( 'query', array( '\WP_STATISTICS\DB', 'insert_ignore' ), 10 );

		# Save to WordPress Database
		$wpdb->insert( DB::table( 'visitor' ), $visitor, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ) );

		# Get Visitor ID
		$visitor_id = $wpdb->insert_id;

		# Remove ignore filter
		remove_filter( 'query', array( '\WP_STATISTICS\DB', 'insert_ignore' ), 10 );

		# Do Action After Save New Visitor
		do_action( 'wp_statistics_save_visitor', $visitor_id, $visitor, User::get_user_id() );

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
	 * @throws \Exception
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
					'location'     => GeoIP::getCountry( IP::getIP() ),
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
					do_action( 'wp_statistics_update_visitor_hits', $visitor_id, $same_visitor );

					// Update Visitor Count in DB
					$wpdb->query( $wpdb->prepare( 'UPDATE `' . DB::table( 'visitor' ) . '` SET `hits` = `hits` + %d WHERE `ID` = %d', 1, $visitor_id ) );
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
		$insert_id = $wpdb->insert_id;

		// Save visitor Relationship Action
		do_action( 'wp_statistics_save_visitor_relationship', $page_id, $visitor_id, $insert_id );

		return $insert_id;
	}

	/**
	 * Get Top Visitors
	 *
	 * @param array $arg
	 * @return array
	 * @throws \Exception
	 */
	public static function getTop( $arg = array() ) {

		// Define the array of defaults
		$defaults = array(
			'day'      => 'today',
			'per_page' => 10,
			'paged'    => 1,
		);
		$args     = wp_parse_args( $arg, $defaults );

		// Prepare time
		if ( $args['day'] == 'today' ) {
			$sql_time = TimeZone::getCurrentDate( 'Y-m-d' );
		} else {
			$sql_time = date( 'Y-m-d', strtotime( $args['day'] ) );
		}

		// Prepare Query
		$args['sql'] = "SELECT * FROM `" . DB::table( 'visitor' ) . "` WHERE last_counter = '{$sql_time}' ORDER BY hits DESC";

		// Get Visitors Data
		return self::get( $args );
	}

	/**
	 * Get Visitors List By Custom Query
	 *
	 * @param array $arg
	 * @return array
	 * @throws \Exception
	 */
	public static function get( $arg = array() ) {
		global $wpdb;

		// Define the array of defaults
		$defaults = array(
			'sql'      => '',
			'per_page' => 10,
			'paged'    => 1,
			'fields'   => 'all',
			'order'    => 'DESC',
			'orderby'  => 'ID'
		);
		$args     = wp_parse_args( $arg, $defaults );

		// Prepare Query
		if ( empty( $args['sql'] ) ) {
			$args['sql'] = "SELECT * FROM `" . DB::table( 'visitor' ) . "` ORDER BY ID DESC";
		}

		// Set Pagination
		$args['sql'] = $args['sql'] . " LIMIT 0, {$args['per_page']}";

		// Send Request
		$result = $wpdb->get_results( $args['sql'] );

		// Get List
		$list = array();
		foreach ( $result as $items ) {

			$item = array(
				'hits'     => (int) $items->hits,
				'referred' => Referred::get_referrer_link( $items->referred ),
				'date'     => date_i18n( get_option( 'date_format' ), strtotime( $items->last_counter ) ),
				'agent'    => $items->agent,
				'platform' => $items->platform,
				'version'  => $items->version
			);

			// Push Browser
			$item['browser'] = array(
				'name' => $items->agent,
				'logo' => UserAgent::getBrowserLogo( $items->agent ),
				'link' => Menus::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'agent' => $items->agent ) )
			);

			// Push IP
			if ( IP::IsHashIP( $items->ip ) ) {
				$item['hash_ip'] = IP::$hash_ip_prefix;
			} else {
				$item['ip'] = array( 'value' => $items->ip, 'link' => Menus::admin_url( 'visitors', array( 'type' => 'last-all-visitor', 'ip' => $items->ip ) ) );
			}

			// Push Country
			if ( GeoIP::active() ) {
				$item['country'] = array( 'location' => $items->location, 'flag' => Country::flag( $items->location ), 'name' => Country::getName( $items->location ) );
			}

			// Push City
			if ( GeoIP::active( 'city' ) ) {
				$item['city'] = GeoIP::getCity( $items->ip );
			}

			$list[] = $item;
		}

		return $list;
	}

}