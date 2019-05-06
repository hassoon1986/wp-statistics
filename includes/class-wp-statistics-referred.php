<?php

namespace WP_STATISTICS;

class Referred {
	/**
	 * Top Referring Transient name
	 *
	 * @var string
	 */
	public static $top_referring_transient = 'wps_top_referring';

	/**
	 * Referral Url Details Option name
	 *
	 * @var string
	 */
	public static $referral_detail_opt = 'wp_statistics_referrals_detail';

	/**
	 * Get referer URL
	 *
	 * @return string
	 */
	public static function getRefererURL() {
		return ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
	}

	/**
	 * Return the referrer link for the current user.
	 *
	 * @return array|bool|string
	 */
	public static function get() {

		// Get Default
		$referred = self::getRefererURL();

		// Sanitize Referer Url
		$referred = esc_sql( strip_tags( $referred ) );

		// If Referer is Empty then use same WebSite Url
		if ( empty( $referred ) ) {
			$referred = get_bloginfo( 'url' );
		}

		// Check Search Engine
		if ( Option::get( 'addsearchwords', false ) ) {

			// Check to see if this is a search engine referrer
			$SEInfo = SearchEngine::getByUrl( $referred );
			if ( is_array( $SEInfo ) ) {

				// If we're a known SE, check the query string
				if ( $SEInfo['tag'] != '' ) {
					$result = SearchEngine::getByQueryString( $referred );

					// If there were no search words, let's add the page title
					if ( $result == '' || $result == SearchEngine::$error_found ) {
						$result = wp_title( '', false );
						if ( $result != '' ) {
							$referred = esc_url( add_query_arg( $SEInfo['querykey'], urlencode( '~"' . $result . '"' ), $referred ) );
						}
					}
				}
			}
		}

		return apply_filters( 'wp_statistics_user_referer', $referred );
	}

	/**
	 * Get referrer link
	 *
	 * @param  string $referrer
	 * @param string $title
	 * @param bool $is_blank
	 * @return string
	 */
	public static function get_referrer_link( $referrer, $title = '', $is_blank = false ) {

		// Sanitize Link
		$html_referrer = self::html_sanitize_referrer( $referrer );

		// Check Url Protocol
		if ( ! Helper::check_url_scheme( $html_referrer ) ) {
			$html_referrer = '//' . $html_referrer;
		}

		// Parse Url
		$base_url = @parse_url( $html_referrer );

		// Get Page title
		$title = ( trim( $title ) == "" ? $html_referrer : $title );

		// Get Html Link
		return "<a href='{$html_referrer}' title='{$title}'" . ( $is_blank === true ? ' target="_blank"' : '' ) . ">{$base_url['host']}</a>";
	}

	/**
	 * Sanitizes the referrer
	 *
	 * @param     $referrer
	 * @param int $length
	 * @return string
	 */
	public static function html_sanitize_referrer( $referrer, $length = - 1 ) {
		$referrer = trim( $referrer );

		if ( 'data:' == strtolower( substr( $referrer, 0, 5 ) ) ) {
			$referrer = 'http://127.0.0.1';
		}

		if ( 'javascript:' == strtolower( substr( $referrer, 0, 11 ) ) ) {
			$referrer = 'http://127.0.0.1';
		}

		if ( $length > 0 ) {
			$referrer = substr( $referrer, 0, $length );
		}

		return htmlentities( $referrer, ENT_QUOTES );
	}

	/**
	 * Get Number Referer Domain
	 *
	 * @param $url
	 * @param array $time_rang
	 * @return integer
	 */
	public static function get_number_referer_from_domain( $url, $time_rang = array() ) {
		global $wpdb;

		//Get Domain Name
		$search_url = Helper::get_domain_name( esc_url_raw( $url ) );

		//Prepare SQL
		$time_sql = '';
		if ( count( $time_rang ) > 0 and ! empty( $time_rang ) ) {
			$time_sql = sprintf( "AND `last_counter` BETWEEN '%s' AND '%s'", $time_rang[0], $time_rang[1] );
		}
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM `" . DB::table( 'visitor' ) . "` WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND (`referred` LIKE  %s OR `referred` LIKE %s OR `referred` LIKE %s OR `referred` LIKE %s) " . $time_sql . " ORDER BY `" . DB::table( 'visitor' ) . "`.`ID` DESC", 'https://www.' . $wpdb->esc_like( $search_url ) . '%', 'https://' . $wpdb->esc_like( $search_url ) . '%', 'http://www.' . $wpdb->esc_like( $search_url ) . '%', 'http://' . $wpdb->esc_like( $search_url ) . '%' );

		//Get Count
		return $wpdb->get_var( $sql );
	}

	/**
	 * Downloads the referrer spam database from https://github.com/matomo-org/referrer-spam-blacklist.
	 *
	 * @return string
	 */
	public static function download_referrer_spam() {

		// If referrer spam is disabled, bail out.
		if ( Option::get( 'referrerspam' ) == false ) {
			return '';
		}

		// This is the location of the file to download.
		$download_url = 'https://raw.githubusercontent.com/matomo-org/referrer-spam-blacklist/master/spammers.txt';

		// Download the file from MaxMind, this places it in a temporary location.
		$response = wp_remote_get( $download_url, array( 'timeout' => 30 ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$referrerspamlist = wp_remote_retrieve_body( $response );
		if ( is_wp_error( $referrerspamlist ) ) {
			return false;
		}

		if ( $referrerspamlist != '' || Option::get( 'referrerspamlist' ) != '' ) {
			Option::update( 'referrerspamlist', $referrerspamlist );
		}

		return true;
	}

	/**
	 * Get WebSite IP Server And Country Name
	 *
	 * @param $url string domain name e.g : wp-statistics.com
	 * @return array
	 * @throws \Exception
	 */
	public static function get_domain_server( $url ) {

		//Create Empty Object
		$result = array( 'ip' => '', 'country' => '' );

		//Get Ip by Domain
		if ( function_exists( 'gethostbyname' ) ) {

			// Get Host Domain
			$ip = gethostbyname( $url );

			// Check Validate IP
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				$result['ip']      = $ip;
				$result['country'] = GeoIP::getCountry( $ip );
			}
		}

		return $result;
	}

	/**
	 * Get Top Referring Site
	 *
	 * @param int $number
	 * @return array
	 * @throws \Exception
	 */
	public static function getTop( $number = 10 ) {
		global $wpdb;

		//Get Wordpress Domain
		$site_url = wp_parse_url( get_site_url() );
		$site_url = $site_url['scheme'] . "://" . $site_url['host'];

		//Get Top Referring
		if ( false === ( $get_urls = get_transient( self::$top_referring_transient ) ) ) {

			$result = $wpdb->get_results( "SELECT SUBSTRING_INDEX(REPLACE( REPLACE( referred, 'http://', '') , 'https://' , '') , '/', 1 ) as `domain`, count(referred) as `number` FROM " . DB::table( 'visitor' ) . " WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND `referred` NOT LIKE '{$site_url}%' GROUP BY domain ORDER BY `number` DESC LIMIT $number" );
			foreach ( $result as $items ) {
				$get_urls[ $items->domain ] = self::get_number_referer_from_domain( $items->domain );
			}

			// Put the results in a transient. Expire after 12 hours.
			set_transient( self::$top_referring_transient, $get_urls, 20 * HOUR_IN_SECONDS );
		}

		//Load country Code
		$ISOCountryCode = Country::getList();

		//Get Refer Site Detail
		$refer_opt     = get_option( self::$referral_detail_opt );
		$referrer_list = ( empty( $refer_opt ) ? array() : $refer_opt );

		if ( ! $get_urls ) {
			return array();
		}

		$list = array();
		foreach ( $get_urls as $domain => $number ) {

			//Get Site Link
			$referrer_html = Referred::html_sanitize_referrer( $domain );

			//Get Site information if Not Exist
			if ( ! array_key_exists( $domain, $referrer_list ) ) {
				$get_site_inf             = Referred::get_domain_server( $domain );
				$get_site_title           = Helper::get_site_title_by_url( $domain );
				$referrer_list[ $domain ] = array(
					'ip'      => $get_site_inf['ip'],
					'country' => $get_site_inf['country'],
					'title'   => ( $get_site_title === false ? '' : $get_site_title ),
				);
			}

			// Push to list
			$list[] = array(
				'domain'    => $domain,
				'title'     => $referrer_list[ $domain ]['title'],
				'ip'        => ( $referrer_list[ $domain ]['ip'] != "" ? $referrer_list[ $domain ]['ip'] : '-' ),
				'country'   => ( $referrer_list[ $domain ]['country'] != "" ? $ISOCountryCode[ $referrer_list[ $domain ]['country'] ] : '' ),
				'page_link' => Menus::admin_url( 'referrers', array( 'referr' => $referrer_html ) ),
				'number'    => number_format_i18n( $number )
			);
		}

		//Save Referrer List Update
		update_option( self::$referral_detail_opt, $referrer_list, 'no' );

		// Return Data
		return $list;
	}

}