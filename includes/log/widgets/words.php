<?php

//TODO Remove At last

use WP_STATISTICS\Admin_Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Referred;

function wp_statistics_generate_words_postbox_content( $ISOCountryCode, $count = 10 ) {
	global $wpdb;

	$search_query = wp_statistics_searchword_query( 'all' );
	$result       = $wpdb->get_results( "SELECT * FROM `" . \WP_STATISTICS\DB::table( 'search' ) . "` INNER JOIN `" . \WP_STATISTICS\DB::table( 'visitor' ) . "` on `" . \WP_STATISTICS\DB::table( 'search' ) . "`.`visitor` = " . \WP_STATISTICS\DB::table( 'visitor' ) . ".`ID` WHERE {$search_query} ORDER BY `" . \WP_STATISTICS\DB::table( 'search' ) . "`.`ID` DESC  LIMIT 0, {$count}" );

	if ( sizeof( $result ) > 0 ) {
		echo "<div class=\"wp-statistics-responsive-table\">";
		echo "<table width=\"100%\" class=\"widefat table-stats wps-report-table\">
		  <tr>";
		echo "<td>" . __( 'Word', 'wp-statistics' ) . "</td>";
		echo "<td>" . __( 'Browser', 'wp-statistics' ) . "</td>";
		if ( WP_STATISTICS\Option::get( 'geoip' ) ) {
			echo "<td>" . __( 'Country', 'wp-statistics' ) . "</td>";
		}
		if ( WP_STATISTICS\Option::get( 'geoip_city' ) ) {
			echo "<td>" . __( 'City', 'wp-statistics' ) . "</td>";
		}
		echo "<td>" . __( 'Date', 'wp-statistics' ) . "</td>";
		echo "<td>" . __( 'IP', 'wp-statistics' ) . "</td>";
		echo "<td>" . __( 'Referrer', 'wp-statistics' ) . "</td>";
		echo "</tr>";

		// Load city name
		$geoip_reader = false;
		if ( WP_STATISTICS\Option::get( 'geoip_city' ) ) {
			$geoip_reader = \WP_STATISTICS\GeoIP::Loader( 'city' );
		}

		foreach ( $result as $items ) {

			if ( ! WP_STATISTICS\SearchEngine::getByQueryString( $items->referred ) ) {
				continue;
			}

			$words = WP_STATISTICS\SearchEngine::getByQueryString( $items->referred );


			echo "<tr>";
			echo "<td style=\"text-align: left\"><span title='{$words}' class='wps-cursor-default wps-text-wrap'>" . $words . "</span></td>";

			echo "<td style=\"text-align: left\">";
			if ( array_search( strtolower( $items->agent ), \WP_STATISTICS\UserAgent::BrowserList( 'key' ) ) !== false ) {
				$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $items->agent . ".png' class='log-tools' title='{$items->agent}'/>";
			} else {
				$agent = \WP_STATISTICS\Admin_Templates::icons( 'dashicons-editor-help', 'unknown' );
			}
			echo "<a href='" . Menus::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'agent' => $items->agent ) ) . "'>{$agent}</a>";
			echo "</td>";


			$city = '';
			if ( WP_STATISTICS\Option::get( 'geoip_city' ) ) {
				if ( $geoip_reader != false ) {
					try {
						$reader = $geoip_reader->city( $items->ip );
						$city   = $reader->city->name;
					} catch ( Exception $e ) {
						$city = __( 'Unknown', 'wp-statistics' );
					}

					if ( ! $city ) {
						$city = __( 'Unknown', 'wp-statistics' );
					}
				}
			}

			if ( WP_STATISTICS\Option::get( 'geoip' ) ) {
				echo "<td style=\"text-align: left\">";
				echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
				echo "</td>";
			}

			if ( WP_STATISTICS\Option::get( 'geoip_city' ) ) {
				echo "<td style=\"text-align: left\">";
				echo $city;
				echo "</td>";
			}

			echo "<td style=\"text-align: left\">";
			echo date_i18n( get_option( 'date_format' ), strtotime( $items->last_counter ) );
			echo "</td>";

			echo "<td style=\"text-align: left\">";
			if ( \WP_STATISTICS\IP::IsHashIP( $items->ip ) ) {
				$ip_string = \WP_STATISTICS\IP::$hash_ip_prefix;
			} else {
				$ip_string = "<a href='" . Menus::admin_url( 'visitors', array( 'type' => 'last-all-visitor', 'ip' => $items->ip ) ) . "'>{$items->ip}</a>";
			}
			echo $ip_string;
			echo "</td>";
			echo "<td style=\"text-align: left\">" . Referred::get_referrer_link( $items->referred ) . "</td>";;
			echo "</tr>";
		}

		echo "</table>";
		echo "</div>";
	}
}

