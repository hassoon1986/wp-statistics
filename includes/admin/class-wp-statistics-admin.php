<?php

namespace WP_STATISTICS;

class Admin {
	/**
	 * WP-Statistics WordPress Admin
	 */
	public function __construct() {


		// If we've been flagged to remove all of the data, then do so now.
		if ( get_option( 'wp_statistics_removal' ) == 'true' ) {
			new Uninstall;
		}


	}

}