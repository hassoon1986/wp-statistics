<?php

namespace WP_STATISTICS;

/**
 * WordPress Statistics
 *
 * ## EXAMPLES
 *
 *      # show summary of statistics
 *      $ wp statistics summary
 *
 *      # get list of users online in WordPress
 *      $ wp statistics online
 *
 *      # show list of last visitors
 *      $ wp statistics visitors
 *
 * @package wp-cli
 */
class WP_STATISTICS_CLI extends \WP_CLI_Command {

	/**
	 * Show Summary of statistics.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *      # show summary of statistics
	 *      $ wp statistics summary
	 *
	 * @alias overview
	 * @throws \Exception
	 */
	function summary( $args, $assoc_args ) {

		// Check Enable Command
		if ( Option::get( 'wp_cli_summary' ) == false ) {
			\WP_CLI::error( "The `summary` command is not active." );
		}

		// Prepare Item
		\WP_CLI::line( "Users Online: " . number_format( wp_statistics_useronline() ) );
		$items = array();
		foreach ( array( "Today", "Yesterday", "Week", "Month", "Year", "Total" ) as $time ) {
			$item = array(
				'Time' => $time
			);
			foreach ( array( "Visitors", "Visits" ) as $state ) {
				$item[ $state ] = number_format( ( strtolower( $state ) == "visitors" ? wp_statistics_visitor( strtolower( $time ), null, true ) : wp_statistics_visit( strtolower( $time ) ) ) );
			}
			$items[] = $item;
		}

		\WP_CLI\Utils\format_items( $assoc_args['format'], $items, array( 'Time', 'Visitors', 'Visits' ) );
	}

	public function online() {

	}

	public function visitors() {

	}

}

/**
 * Register Command
 */
\WP_CLI::add_command( 'statistics', '\\WP_STATISTICS\WP_STATISTICS_CLI' );