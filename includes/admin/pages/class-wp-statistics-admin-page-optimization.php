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
		if ( ! User::Access( 'manage' ) ) {
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
			Install::create_table( false );
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
	}

}