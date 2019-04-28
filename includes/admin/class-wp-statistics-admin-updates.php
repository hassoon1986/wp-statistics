<?php

namespace WP_STATISTICS;

class Updates {
	/**
	 * Populate GeoIP information in to the database.
	 * It is used in two different parts of the plugin;
	 * When a user manual requests the update to happen and after a new GeoIP database has been download
	 * (if the option is selected).
	 *
	 * @return string
	 */
	static function populate_geoip_info() {
		global $wpdb;

		// Find all rows in the table that currently don't have GeoIP info or have an unknown ('000') location.
		$result = $wpdb->get_results( "SELECT id,ip FROM `{$wpdb->prefix}statistics_visitor` WHERE location = '' or location = '".GeoIP::$private_country."' or location IS NULL" );

		// Try create a new reader instance.
		$reader = false;
		if ( Option::get( 'geoip' ) ) {
			$reader = GeoIP::Loader( 'country' );
		}

		if ( $reader === false ) {
			$text_error = __( 'Unable to load the GeoIP database, make sure you have downloaded it in the settings page.', 'wp-statistics' );
			Admin_Pages::set_admin_notice( $text_error, $type = 'error' );
		}

		$count = 0;

		// Loop through all the missing rows and update them if we find a location for them.
		foreach ( $result as $item ) {
			$count ++;

			// If the IP address is only a hash, don't bother updating the record.
			if ( substr( $item->ip, 0, 6 ) != '#hash#' and $reader != false ) {
				try {
					$record   = $reader->country( $item->ip );
					$location = $record->country->isoCode;
					if ( $location == "" ) {
						$location = GeoIP::$private_country;
					}
				} catch ( \Exception $e ) {
					$location = GeoIP::$private_country;
				}

				// Update the row in the database.
				$wpdb->update(
					$wpdb->prefix . "statistics_visitor",
					array( 'location' => $location ),
					array( 'id' => $item->id )
				);
			}
		}

		return "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Updated %s GeoIP records in the visitors database.', 'wp-statistics' ), $count ) . "</strong></p></div>";
	}
}
