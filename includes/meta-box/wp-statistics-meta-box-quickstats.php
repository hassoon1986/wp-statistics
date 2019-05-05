<?php

namespace WP_STATISTICS\MetaBox;

class quickstats {
	/**
	 * Get Quick States Meta Box Data
	 *
	 * @param array $args
	 * @return array
	 */
	public static function get( $args = array() ) {
		return summary::data( array( 'hit-chart' ) );
	}

	/**
	 * Quick States Meta Box Lang
	 *
	 * @return array
	 */
	public static function lang() {
		return array(
			'search_engine'     => __( 'Search Engine Referrals', 'wp-statistics' ),
			'current_time_date' => __( 'Current Time and Date', 'wp-statistics' ),
			'adjustment'        => __( '(Adjustment)', 'wp-statistics' ),
			'date'              => __( 'Date:', 'wp-statistics' ), //space after
			'time'              => __( 'Time:', 'wp-statistics' ), //space after
		);
	}

}