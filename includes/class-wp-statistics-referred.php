<?php

namespace WP_STATISTICS;

class Referred {
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
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}statistics_visitor` WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND (`referred` LIKE  %s OR `referred` LIKE %s OR `referred` LIKE %s OR `referred` LIKE %s) " . $time_sql . " ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC", 'https://www.' . $wpdb->esc_like( $search_url ) . '%', 'https://' . $wpdb->esc_like( $search_url ) . '%', 'http://www.' . $wpdb->esc_like( $search_url ) . '%', 'http://' . $wpdb->esc_like( $search_url ) . '%' );

		//Get Count
		return $wpdb->get_var( $sql );
	}

	/**
	 * Downloads the referrer spam database from https://github.com/matomo-org/referrer-spam-blacklist.
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


}