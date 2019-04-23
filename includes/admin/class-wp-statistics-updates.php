<?php

namespace WP_STATISTICS;

class Updates {
	/**
	 * This function downloads the GeoIP database from MaxMind.
	 *
	 * @param $pack
	 * @param string $type
	 *
	 * @return string
	 */
	static function download_geoip( $pack, $type = "enable" ) {

		//Create Empty Return Function
		$result["status"] = false;

		// We need the download_url() function, it should exists on virtually all installs of PHP, but if it doesn't for some reason, bail out.
		if ( ! function_exists( 'download_url' ) ) {
			include( ABSPATH . 'wp-admin/includes/file.php' );
		}

		// We need the wp_generate_password() function.
		if ( ! function_exists( 'wp_generate_password' ) ) {
			include( ABSPATH . 'wp-includes/pluggable.php' );
		}

		// We need the gzopen() function, it should exists on virtually all installs of PHP, but if it doesn't for some reason, bail out.
		// Also stop trying to update the database as it just won't work :)
		if ( false === function_exists( 'gzopen' ) ) {
			if ( $type == "enable" ) {
				Option::update( GeoIP::$library[ $pack ]['opt'], '' );
			}

			$result["notice"] = __( 'Error the gzopen() function do not exist!', 'wp-statistics' );
			Admin_Pages::set_admin_notice( $result["notice"], $type = 'error' );

			return $result;
		}

		// If GeoIP is disabled, bail out.
		if ( $type == "update" and Option::get( GeoIP::$library[ $pack ]['opt'] ) == '' ) {
			return '';
		}

		// This is the location of the file to download.
		$download_url = GeoIP::$library[ $pack ]['cdn'];
		$response     = wp_remote_get( $download_url );

		// Change download url if the maxmind.com doesn't response.
		if ( wp_remote_retrieve_response_code( $response ) != '200' ) {
			$download_url = GeoIP::$library[ $pack ]['github'];
		}

		// Get the upload directory from WordPress.
		$upload_dir = wp_upload_dir();

		// Create a variable with the name of the database file to download.
		$DBFile = $upload_dir['basedir'] . '/wp-statistics/' . GeoIP::$library[ $pack ]['file'] . '.mmdb';

		// Check to see if the subdirectory we're going to upload to exists, if not create it.
		if ( ! file_exists( $upload_dir['basedir'] . '/wp-statistics' ) ) {
			if ( ! @mkdir( $upload_dir['basedir'] . '/wp-statistics', 0755 ) ) {
				if ( $type == "enable" ) {
					Option::update( GeoIP::$library[ $pack ]['opt'], '' );
				}

				$result["notice"] = sprintf( __( 'Error creating GeoIP database directory, make sure your web server has permissions to create directories in: %s', 'wp-statistics' ), $upload_dir['basedir'] );
				Admin_Pages::set_admin_notice( $result["notice"], $type = 'error' );

				return $result;
			}
		}

		if ( ! is_writable( $upload_dir['basedir'] . '/wp-statistics' ) ) {
			if ( $type == "enable" ) {
				Option::update( GeoIP::$library[ $pack ]['opt'], '' );
			}

			$result["notice"] = sprintf( __( 'Error setting permissions of the GeoIP database directory, make sure your web server has permissions to write to directories in : %s', 'wp-statistics' ),
				$upload_dir['basedir']
			);
			Admin_Pages::set_admin_notice( $result["notice"], $type = 'error' );

			return $result;
		}

		// Download the file from MaxMind, this places it in a temporary location.
		$TempFile = download_url( $download_url );

		// If we failed, through a message, otherwise proceed.
		if ( is_wp_error( $TempFile ) ) {
			if ( $type == "enable" ) {
				Option::update( GeoIP::$library[ $pack ]['opt'], '' );
			}

			$result["notice"] = sprintf( __( 'Error downloading GeoIP database from: %s - %s', 'wp-statistics' ), $download_url, $TempFile->get_error_message() );
			Admin_Pages::set_admin_notice( $result["notice"], $type = 'error' );
		} else {
			// Open the downloaded file to unzip it.
			$ZipHandle = gzopen( $TempFile, 'rb' );

			// Create th new file to unzip to.
			$DBfh = fopen( $DBFile, 'wb' );

			// If we failed to open the downloaded file, through an error and remove the temporary file.  Otherwise do the actual unzip.
			if ( ! $ZipHandle ) {
				if ( $type == "enable" ) {
					WP_STATISTICS\Option::update( GeoIP::$library[ $pack ]['opt'], '' );
				}

				$result["notice"] = sprintf( __( 'Error could not open downloaded GeoIP database for reading: %s', 'wp-statistics' ), $TempFile );
				Admin_Pages::set_admin_notice( $result["notice"], $type = 'error' );

				unlink( $TempFile );
			} else {
				// If we failed to open the new file, throw and error and remove the temporary file.  Otherwise actually do the unzip.
				if ( ! $DBfh ) {
					if ( $type == "enable" ) {
						Option::update( GeoIP::$library[ $pack ]['opt'], '' );
					}

					$result["notice"] = sprintf( __( 'Error could not open destination GeoIP database for writing %s', 'wp-statistics' ), $DBFile );
					Admin_Pages::set_admin_notice( $result["notice"], $type = 'error' );

					unlink( $TempFile );
				} else {
					while ( ( $data = gzread( $ZipHandle, 4096 ) ) != false ) {
						fwrite( $DBfh, $data );
					}

					// Close the files.
					gzclose( $ZipHandle );
					fclose( $DBfh );

					// Delete the temporary file.
					unlink( $TempFile );

					// Display the success message.
					$result["status"] = true;
					$result["notice"] = "<div class='updated settings-error'><p><strong>" . __( 'GeoIP Database updated successfully!', 'wp-statistics' ) . "</strong></p></div>";

					// Update the options to reflect the new download.
					if ( $type == "update" ) {
						Option::update( 'last_geoip_dl', time() );
						Option::update( 'update_geoip', false );
					}

					// Populate any missing GeoIP information if the user has selected the option.
					if ( $pack == "country" ) {
						if ( Option::get( 'geoip' ) && wp_statistics_geoip_supported() && Option::get( 'auto_pop' ) ) {
							Updates::populate_geoip_info();
						}
					}
				}
			}
		}

		if ( Option::get( 'geoip_report' ) == true ) {
			$blogname  = get_bloginfo( 'name' );
			$blogemail = get_bloginfo( 'admin_email' );

			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";

			if ( Option::get( 'email_list' ) == '' ) {
				Option::update( 'email_list', $blogemail );
			}

			wp_mail( Option::get( 'email_list' ), __( 'GeoIP update on', 'wp-statistics' ) . ' ' . $blogname , $result['notice'], $headers );
		}

		// All of the messages displayed above are stored in a string, now it's time to actually output the messages.
		return $result;
	}

	/**
	 * Downloads the referrer spam database from https://github.com/matomo-org/referrer-spam-blacklist.
	 * @return string
	 */
	static function download_referrerspam() {

		// If referrer spam is disabled, bail out.
		if ( WP_STATISTICS\Option::get( 'referrerspam' ) == false ) {
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

		if ( $referrerspamlist != '' || WP_STATISTICS\Option::get( 'referrerspamlist' ) != '' ) {
			WP_STATISTICS\Option::update( 'referrerspamlist', $referrerspamlist );
		}

		return true;
	}

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
				} catch ( Exception $e ) {
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
