<?php

use WP_STATISTICS\Admin_Helper;
use WP_STATISTICS\Referred;

function wp_statistics_generate_words_postbox_content( $ISOCountryCode, $count = 10 ) {
	global $wpdb;

	// Retrieve MySQL data for the search words.
	$search_query = wp_statistics_searchword_query( 'all' );

	// Determine if we're using the old or new method of storing search engine info and build the appropriate table name.
	$tablename = $wpdb->prefix . 'statistics_';

	if ( WP_STATISTICS\Option::get( 'search_converted' ) ) {
		$tabletwo  = $tablename . 'visitor';
		$tablename .= 'search';
		$result    = $wpdb->get_results(
			"SELECT * FROM `{$tablename}` INNER JOIN `{$tabletwo}` on {$tablename}.`visitor` = {$tabletwo}.`ID` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT 0, {$count}"
		);
	} else {
		$tablename .= 'visitor';
		$result    = $wpdb->get_results(
			"SELECT * FROM `{$tablename}` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT 0, {$count}"
		);
	}

	if ( sizeof( $result ) > 0 ) {
		echo "<div class=\"wp-statistics-table\">";
		echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"last-referrer\">
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

			if ( WP_STATISTICS\Option::get( 'search_converted' ) ) {
				$this_search_engine = WP_STATISTICS\SearchEngine::get( $items->engine );
				$words              = $items->words;
			} else {
				$this_search_engine = WP_STATISTICS\SearchEngine::getByUrl( $items->referred );
				$words              = WP_STATISTICS\SearchEngine::getByQueryString( $items->referred );
			}

			echo "<tr>";
			echo "<td style=\"text-align: left\"><span title='{$words}' class='wps-cursor-default wps-text-wrap'>".$words."</span></td>";
			echo "<td style=\"text-align: left\">";
			if ( array_search( strtolower( $items->agent ), wp_statistics_get_browser_list( 'key' ) ) !== false ) {
				$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $items->agent . ".png' class='log-tools' title='{$items->agent}'/>";
			} else {
				$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
			}
			echo "<a href='" . Admin_Helper::admin_url( 'overview', array( 'type' => 'last-all-visitor', 'agent' => $items->agent ) ) . "'>{$agent}</a>";
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
			if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
				$ip_string = __( '#hash#', 'wp-statistics' );
			} else {
				$ip_string = "<a href='" . Admin_Helper::admin_url( 'visitors', array( 'type' => 'last-all-visitor', 'ip' => $items->ip ) ) . "'>{$items->ip}</a>";
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

