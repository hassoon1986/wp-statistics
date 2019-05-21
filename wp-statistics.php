<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * Description: Complete WordPress Analytics and Statistics for your site!
 * Version: 12.6.4
 * Author: VeronaLabs
 * Author URI: http://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /languages/
 */

# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

# Load Plugin Defines
require_once 'includes/defines.php';

# Load Plugin
if ( ! class_exists( 'WP_Statistics' ) ) {
	require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics.php';
}

# Returns the main instance of WP-Statistics.
function WP_Statistics() {
	return WP_Statistics::instance();
}

# Global for backwards compatibility.
$GLOBALS['WP_Statistics'] = WP_Statistics();

add_action('init', function(){
//	$days = 20;
//
//	// Prepare Date time
//	for ( $i = $days; $i >= 0; $i -- ) {
//		$date[] = \WP_STATISTICS\TimeZone::getCurrentDate( 'M j', '-' . $i );
//	}
//
//	print_r($date);
//	exit;

});