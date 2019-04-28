<?php

namespace WP_STATISTICS;

class optimization_page {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'save' ) );
	}

	/**
	 * This function displays the HTML for the settings page.
	 */
	public static function view() {
		global $wpdb;

		// Check the current user has the rights to be here.
		if ( ! current_user_can( wp_statistics_validate_capability( Option::get( 'manage_capability', 'manage_options' ) ) ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Get the row count for each of the tables, we'll use this later on in the wps_optimization.php file.
		$list_table = DB::table( 'all' );
		$result     = array();
		foreach ( $list_table as $tbl_key => $tbl_name ) {
			$result[ $tbl_name ] = $wpdb->get_var( "SELECT COUNT(*) FROM `$tbl_name`" );
		}

		include WP_STATISTICS_DIR . "includes/admin/templates/optimization.php";
	}

	/**
	 * Save Setting
	 */
	public function save() {
		global $wpdb;

		// Check Hash IP Update
		if ( isset( $_GET['hash-ips'] ) and intval( $_GET['hash-ips'] ) == 1 ) {
			IP::Update_HashIP_Visitor();
			Helper::wp_admin_notice( __( 'IP Addresses replaced with hash values.', "wp-statistics" ), "success" );
		}

		// Update All GEO IP Country
		if ( isset( $_GET['populate'] ) and intval( $_GET['populate'] ) == 1 ) {
			$result = GeoIP::Update_GeoIP_Visitor();
			Helper::wp_admin_notice( $result['data'], ( $result['status'] === false ? "error" : "success" ) );
		}

		// Re-install All DB Table
		if ( isset( $_GET['install'] ) and intval( $_GET['install'] ) == 1 ) {
			//TODO Create new method for re-install DB
			Helper::wp_admin_notice( __( 'Install routine complete.', "wp-statistics" ), "success" );
		}

		// Update Historical Value
		if ( isset( $_POST['historical-submit'] ) ) {

			if ( isset( $_POST['wps_historical_visitors'] ) ) {
				$wpdb->update( DB::table( 'historical' ), array( 'value' => $_POST['wps_historical_visitors'] ), array( 'category' => 'visitors' ) );
			}

			if ( isset( $_POST['wps_historical_visits'] ) ) {
				$wpdb->update( DB::table( 'historical' ), array( 'value' => $_POST['wps_historical_visits'] ), array( 'category' => 'visits' ) );
			}

			Helper::wp_admin_notice( __( 'Updated Historical Values.', "wp-statistics" ), "success" );
		}

		if ( isset( $_GET['index'] ) and intval( $_GET['index'] ) == 1 ) {

			// Check the number of index's on the visitors table, if it's only 5 we need to check for duplicate entries and remove them
			$result = $wpdb->query( "SHOW INDEX FROM " . DB::table( 'visitor' ) . " WHERE Key_name = 'date_ip'" );

			if ( $result != 5 ) {
				$result = $wpdb->get_results( "SELECT ID, last_counter, ip FROM " . DB::table( 'visitor' ) . " ORDER BY last_counter, ip" );

				// Setup the inital values.
				$lastrow    = array( 'last_counter' => '', 'ip' => '' );
				$deleterows = array();

				// Ok, now iterate over the results.
				foreach ( $result as $row ) {
					// if the last_counter (the date) and IP is the same as the last row, add the row to be deleted.
					if ( $row->last_counter == $lastrow['last_counter'] && $row->ip == $lastrow['ip'] ) {
						$deleterows[] .= $row->ID;
					}

					// Update the last row data.
					$lastrow['last_counter'] = $row->last_counter;
					$lastrow['ip']           = $row->ip;
				}

				// Now do the actual deletions.
				foreach ( $deleterows as $row ) {
					$wpdb->delete( DB::table( 'visitor' ), array( 'ID' => $row ) );
				}

				// The table should be ready to be updated now with the new index, so let's do it.
				$wpdb->query( "ALTER TABLE " . DB::table( 'visitor' ) . " ADD UNIQUE `date_ip_agent` ( `last_counter`, `ip`, `agent` (75), `platform` (75), `version` (75) )" );

				// We might have an old index left over from 7.1-7.3 so lets make sure to delete it.
				$wpdb->query( "DROP INDEX `date_ip` ON " . DB::table( 'visitor' ) );

				// Record in the options that we've done this update.
				$dbupdates                  = Option::get( 'pending_db_updates' );
				$dbupdates['date_ip_agent'] = false;
				Option::update( 'pending_db_updates', $dbupdates );
			}
		}

		if ( isset( $_GET['visits'] ) and intval( $_GET['visits'] ) == 1 ) {

			// Check the number of index's on the visits table, if it's only 5 we need to check for duplicate entries and remove them
			$result = $wpdb->query( "SHOW INDEX FROM " . DB::table( 'visit' ) . " WHERE Key_name = 'unique_date'" );

			// Note, the result will be the number of fields contained in the index, so in our case 1.
			if ( $result != 1 ) {

				$result = $wpdb->get_results( "SELECT ID, last_counter, visit FROM " . DB::table( 'visit' ) . " ORDER BY last_counter, visit DESC" );

				// Setup the initial values.
				$lastrow    = array( 'last_counter' => '', 'visit' => 0, 'id' => 0 );
				$deleterows = array();

				// Ok, now iterate over the results.
				foreach ( $result as $row ) {
					// if the last_counter (the date) and IP is the same as the last row, add the row to be deleted.
					if ( $row->last_counter == $lastrow['last_counter'] ) {
						$deleterows[] .= $row->ID;
					}

					// Update the lastrow data.
					$lastrow['last_counter'] = $row->last_counter;
					$lastrow['id']           = $row->ID;
					$lastrow['visit']        = $row->visit;
				}

				// Now do the acutal deletions.
				foreach ( $deleterows as $row ) {
					$wpdb->delete( DB::table( 'visit' ), array( 'ID' => $row ) );
				}

				// The table should be ready to be updated now with the new index, so let's do it.
				$wpdb->query( "ALTER TABLE " . DB::table( 'visit' ) . " ADD UNIQUE `unique_date` ( `last_counter` )" );

				// Record in the options that we've done this update.
				$dbupdates                = Option::get( 'pending_db_updates' );
				$dbupdates['unique_date'] = false;
				Option::update( 'pending_db_updates', $dbupdates );
			}
		}
	}

}