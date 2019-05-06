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

}