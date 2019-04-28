<?php

namespace WP_STATISTICS;

use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIP {
	/**
	 * List Geo ip Library
	 *
	 * @var array
	 */
	public static $library = array(
		'country' => array(
			'cdn'    => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz',
			'github' => 'https://raw.githubusercontent.com/wp-statistics/GeoLite2-Country/master/GeoLite2-Country.mmdb.gz',
			'file'   => 'GeoLite2-Country',
			'opt'    => 'geoip'
		),
		'city'    => array(
			'cdn'    => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz',
			'github' => 'https://raw.githubusercontent.com/wp-statistics/GeoLite2-City/master/GeoLite2-City.mmdb.gz',
			'file'   => 'GeoLite2-City',
			'opt'    => 'geoip_city'
		)
	);

	/**
	 * Geo IP file Extension
	 *
	 * @var string
	 */
	public static $file_extension = 'mmdb';

	/**
	 * Default Private Country
	 *
	 * @var String
	 */
	public static $private_country = '000';

	/**
	 * Get Geo IP Path
	 *
	 * @param $pack
	 * @return mixed
	 */
	public static function get_geo_ip_path( $pack ) {
		return path_join( Helper::get_uploads_dir( WP_STATISTICS_UPLOADS_DIR ), self::$library[ strtolower( $pack ) ]['file'] . '.' . self::$file_extension );
	}

	/**
	 * Check Is Active Geo-ip
	 *
	 * @param bool $which
	 * @return boolean
	 */
	public static function active( $which = false ) {

		//Default Geo-Ip Option name
		$opt = ( $which == "city" ? 'geoip_city' : 'geoip' );
		//TODO Check Exist DATABASE FILE

		// Return
		return Option::get( $opt );
	}

	/**
	 * geo ip Loader
	 *
	 * @param $pack
	 * @return bool|\GeoIp2\Database\Reader
	 */
	public static function Loader( $pack ) {

		// Check file Exist
		$file = self::get_geo_ip_path( $pack );
		if ( file_exists( $file ) ) {
			try {

				//Load GeoIP Reader
				$reader = new \GeoIp2\Database\Reader( $file );
			} catch ( InvalidDatabaseException $e ) {
				return false;
			}
		} else {
			return false;
		}

		return $reader;
	}

	/**
	 * Get Default Country Code
	 *
	 * @return String
	 */
	public static function getDefaultCountryCode() {

		$opt = Option::get( 'private_country_code' );
		if ( isset( $opt ) and ! empty( $opt ) ) {
			return trim( $opt );
		}

		return self::$private_country;
	}

	/**
	 * Get Country Detail By User IP
	 *
	 * @param bool $ip
	 * @param string $return
	 * @return String|null
	 * @see https://github.com/maxmind/GeoIP2-php
	 * @throws \Exception
	 */
	public static function getCountry( $ip = false, $return = 'isoCode' ) {

		// Default Country Name
		$default_country = self::getDefaultCountryCode();

		// Get User IP
		$ip = ( $ip === false ? IP::getIP() : $ip );

		// Check Unknown IP
		if ( $default_country != self::$private_country ) {
			if ( IP::CheckIPRange( IP::$private_SubNets ) ) {
				return $default_country;
			}
		}

		// Load GEO-IP
		$reader = self::Loader( 'country' );

		//Get Country name
		if ( $reader != false ) {

			try {
				//Search in Geo-IP
				$record = $reader->country( $ip );

				//Get Country
				if ( $return == "all" ) {
					$location = $record->country;
				} else {
					$location = $record->country->{$return};
				}
			} catch ( AddressNotFoundException $e ) {
				//Don't Stuff
			} catch ( InvalidDatabaseException $e ) {
				//Don't Stuff
			}
		}

		# Check Has Location
		if ( isset( $location ) and ! empty( $location ) ) {
			return $location;
		}

		return $default_country;
	}

	/**
	 * This function downloads the GeoIP database from MaxMind.
	 *
	 * @param $pack
	 * @param string $type
	 *
	 * @return string
	 */
	public static function download( $pack, $type = "enable" ) {

		// Create Empty Return Function
		$result["status"] = false;

		// Sanitize Pack name
		$pack = strtolower( $pack );

		// If GeoIP is disabled, bail out.
		if ( $type == "update" and Option::get( GeoIP::$library[ $pack ]['opt'] ) == '' ) {
			return '';
		}

		// Load Require Function
		if ( ! function_exists( 'download_url' ) ) {
			include( ABSPATH . 'wp-admin/includes/file.php' );
		}
		if ( ! function_exists( 'wp_generate_password' ) ) {
			include( ABSPATH . 'wp-includes/pluggable.php' );
		}

		// Get the upload directory from WordPress.
		$upload_dir = wp_upload_dir();

		// We need the gzopen() function
		if ( false === function_exists( 'gzopen' ) ) {
			if ( $type == "enable" ) {
				Option::update( GeoIP::$library[ $pack ]['opt'], '' );
			}

			return array_merge( $result, array( "notice" => __( 'Error the gzopen() function do not exist!', 'wp-statistics' ) ) );
		}

		// This is the location of the file to download.
		$download_url = GeoIP::$library[ $pack ]['cdn'];
		$response     = wp_remote_get( $download_url );

		// Change download url if the maxmind.com doesn't response.
		if ( wp_remote_retrieve_response_code( $response ) != '200' ) {
			$download_url = GeoIP::$library[ $pack ]['github'];
		}

		// Create a variable with the name of the database file to download.
		$DBFile = self::get_geo_ip_path( $pack );

		// Check to see if the subdirectory we're going to upload to exists, if not create it.
		if ( ! file_exists( $upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR ) ) {
			if ( ! @mkdir( $upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR, 0755 ) ) {
				if ( $type == "enable" ) {
					Option::update( GeoIP::$library[ $pack ]['opt'], '' );
				}

				return array_merge( $result, array( "notice" => sprintf( __( 'Error creating GeoIP database directory, make sure your web server has permissions to create directories in: %s', 'wp-statistics' ), $upload_dir['basedir'] ) ) );
			}
		}

		if ( ! is_writable( $upload_dir['basedir'] . '/' . WP_STATISTICS_UPLOADS_DIR ) ) {
			if ( $type == "enable" ) {
				Option::update( GeoIP::$library[ $pack ]['opt'], '' );
			}

			return array_merge( $result, array( "notice" => sprintf( __( 'Error setting permissions of the GeoIP database directory, make sure your web server has permissions to write to directories in : %s', 'wp-statistics' ), $upload_dir['basedir'] ) ) );
		}

		// Download the file from MaxMind, this places it in a temporary location.
		$TempFile = download_url( $download_url );

		// If we failed, through a message, otherwise proceed.
		if ( is_wp_error( $TempFile ) ) {
			if ( $type == "enable" ) {
				Option::update( GeoIP::$library[ $pack ]['opt'], '' );
			}

			return array_merge( $result, array( "notice" => sprintf( __( 'Error downloading GeoIP database from: %s - %s', 'wp-statistics' ), $download_url, $TempFile->get_error_message() ) ) );
		} else {
			// Open the downloaded file to unzip it.
			$ZipHandle = gzopen( $TempFile, 'rb' );

			// Create th new file to unzip to.
			$DBfh = fopen( $DBFile, 'wb' );

			// If we failed to open the downloaded file, through an error and remove the temporary file.  Otherwise do the actual unzip.
			if ( ! $ZipHandle ) {
				if ( $type == "enable" ) {
					Option::update( GeoIP::$library[ $pack ]['opt'], '' );
				}

				unlink( $TempFile );
				return array_merge( $result, array( "notice" => sprintf( __( 'Error could not open downloaded GeoIP database for reading: %s', 'wp-statistics' ), $TempFile ) ) );
			} else {
				// If we failed to open the new file, throw and error and remove the temporary file.  Otherwise actually do the unzip.
				if ( ! $DBfh ) {
					if ( $type == "enable" ) {
						Option::update( GeoIP::$library[ $pack ]['opt'], '' );
					}

					unlink( $TempFile );
					return array_merge( $result, array( "notice" => sprintf( __( 'Error could not open destination GeoIP database for writing %s', 'wp-statistics' ), $DBFile ) ) );
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
					$result["notice"] = __( 'GeoIP Database updated successfully!', 'wp-statistics' );

					// Update the options to reflect the new download.
					if ( $type == "update" ) {
						Option::update( 'last_geoip_dl', time() );
						Option::update( 'update_geoip', false );
					}

					// Populate any missing GeoIP information if the user has selected the option.
					if ( $pack == "country" ) {
						if ( Option::get( 'geoip' ) && GeoIP::IsSupport() && Option::get( 'auto_pop' ) ) {
							self::Update_GeoIP_Visitor();
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

			wp_mail( Option::get( 'email_list' ), __( 'GeoIP update on', 'wp-statistics' ) . ' ' . $blogname, $result['notice'], $headers );
		}

		// All of the messages displayed above are stored in a string, now it's time to actually output the messages.
		return $result;
	}

	/**
	 * Update All GEO-IP Visitors
	 *
	 * @return array
	 */
	public static function Update_GeoIP_Visitor() {
		global $wpdb;

		// Find all rows in the table that currently don't have GeoIP info or have an unknown ('000') location.
		$result = $wpdb->get_results( "SELECT id,ip FROM `" . DB::table( 'visitor' ) . "` WHERE location = '' or location = '" . GeoIP::$private_country . "' or location IS NULL" );

		// Try create a new reader instance.
		$reader = false;
		if ( Option::get( 'geoip' ) ) {
			$reader = GeoIP::Loader( 'country' );
		}

		if ( $reader === false ) {
			return array( 'status' => false, 'data' => __( 'Unable to load the GeoIP database, make sure you have downloaded it in the settings page.', 'wp-statistics' ) );
		}

		$count = 0;

		// Loop through all the missing rows and update them if we find a location for them.
		foreach ( $result as $item ) {
			$count ++;

			// If the IP address is only a hash, don't bother updating the record.
			if ( IP::IsHashIP( $item->ip ) === false and $reader != false ) {
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
					DB::table( 'visitor' ),
					array( 'location' => $location ),
					array( 'id' => $item->id )
				);
			}
		}

		return array( 'status' => true, 'data' => sprintf( __( 'Updated %s GeoIP records in the visitors database.', 'wp-statistics' ), $count ) );
	}

	/**
	 * if PHP modules we need for GeoIP exists.
	 *
	 * @return bool
	 */
	public static function IsSupport() {
		$enabled = true;

		// PHP cURL extension installed
		if ( ! function_exists( 'curl_init' ) ) {
			$enabled = false;
		}

		// PHP NOT running in safe mode
		if ( ini_get( 'safe_mode' ) ) {
			// Double check php version, 5.4 and above don't support safe mode but the ini value may still be set after an upgrade.
			if ( ! version_compare( phpversion(), '5.4', '<' ) ) {
				$enabled = false;
			}
		}

		return $enabled;
	}

}