<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Option;
use WP_STATISTICS\RestAPI;
use WP_STATISTICS\TimeZone;

class post {

	public static function get( $args = array() ) {

		// Set Not Publish Content
		$not_publish = array( 'content' => __( 'This post is not yet published.', 'wp-statistics' ) );

		// Check Isset POST ID
		if ( ! isset( $args['ID'] ) || $args['ID'] < 1 ) {
			return $not_publish;
		}

		// Get Post Information
		$post = get_post( $args['ID'] );

		// Check Number Days
		$days = ( isset( $args['days'] ) ? $args['days'] : 20 );

		// Check Not Publish Post
		if ( $post->post_status != 'publish' && $post->post_status != 'private' ) {
			return $not_publish;
		}

		// Prepare Object
		$stats = $date = array();

		// Prepare Date time
		for ( $i = $days; $i >= 0; $i -- ) {
			$date[] = Timezone::getCurrentDate( 'M j', '-' . $i );
		}

		// Prepare State TODO [ Fix at Last ]
		list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $days, '', '' );
		$daysInThePast = round( ( time() - $rangeend_utime ) / 86400, 0 );
		$post_type     = get_post_type( $post->ID );
		if ( $post_type != "page" or $post_type != "product" ) {
			$post_type = 'post';
		}
		for ( $i = $daysToDisplay; $i >= 0; $i -- ) {
			$stats[] = wp_statistics_pages( '-' . ( $i + $daysInThePast ), '', $post->ID, null, null, $post_type );
		}

		// Push Basic Chart Data
		$response = array(
			'days'       => $days,
			'title'      => __( 'Number of Hits', 'wp-statistics' ),
			'post_title' => get_the_title( $post->ID ),
			'date'       => $date,
			'state'      => $stats
		);

		// Check For No Data Meta Box
		if ( count( array_filter( $response['state'] ) ) < 1 ) {
			$response['no_data'] = 1;
		}

		// Response
		return $response;
	}


}