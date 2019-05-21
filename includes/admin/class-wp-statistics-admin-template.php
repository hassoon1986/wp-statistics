<?php

namespace WP_STATISTICS;

class Admin_Template {
	/**
	 * Default Pagination GET name
	 *
	 * @var string
	 */
	public static $paginate_link_name = 'pagination-page';

	/**
	 * Default Item Per Page in Pagination
	 *
	 * @var int
	 */
	public static $item_per_page = 10;

	/**
	 * Jquery UI Datepicker Format in PHP
	 *
	 * @var string
	 */
	public static $datepicker_format = 'Y-m-d';

	/**
	 * Get Template File
	 *
	 * @param $template
	 * @param array $args
	 */
	public static function get_template( $template, $args = array() ) {

		// Push Args
		if ( is_array( $args ) && isset( $args ) ) :
			extract( $args );
		endif;

		// Check Load single file or array list
		if ( is_string( $template ) ) {
			$template = explode( " ", $template );
		}

		// Load File
		foreach ( $template as $file ) {

			$template_file = WP_STATISTICS_DIR . "includes/admin/templates/" . $file . ".php";
			if ( ! file_exists( $template_file ) ) {
				Helper::doing_it_wrong( __FUNCTION__, __( 'Template not found.', 'wp-statistics' ), '12.6.5' );
				return;
			}

			// include File
			include $template_file;
		}
	}

	/**
	 * Show Page title
	 * //TODO Remove at last
	 *
	 * @param string $title
	 */
	public static function show_page_title( $title = '' ) {

		//Check if $title not Set
		if ( empty( $title ) and function_exists( 'get_admin_page_title' ) ) {
			$title = get_admin_page_title();
		}

		//show Page title
		echo '<img src="' . WP_STATISTICS_URL . '/assets/images/title-logo.png" class="wps_page_title" alt="wp-statistics plugin logo"><h2 class="wps_title">' . $title . '</h2>';

		//do_action after wp_statistics
		do_action( 'wp_statistics_after_title' );
	}

	/**
	 * Get Current Paged
	 *
	 * @return float|int
	 */
	public static function getCurrentPaged() {
		return isset( $_GET[ Admin_Template::$paginate_link_name ] ) ? abs( (int) $_GET[ Admin_Template::$paginate_link_name ] ) : 1;
	}

	/**
	 * Get Current Offset
	 *
	 * @param bool $page
	 * @param $item_per_page
	 * @return float|int
	 */
	public static function getCurrentOffset( $page = false, $item_per_page = false ) {
		$page          = ( $page === false ? self::getCurrentPaged() : $page );
		$item_per_page = ( $item_per_page === false ? Admin_Template::$item_per_page : $item_per_page );
		return ( $page * $item_per_page ) - $item_per_page;
	}

	/**
	 * Pagination Link
	 *
	 * @param array $args
	 * @area admin
	 * @return string
	 */
	public static function paginate_links( $args = array() ) {

		//Prepare Arg
		$defaults        = array(
			'item_per_page' => self::$item_per_page,
			'container'     => 'pagination-wrap',
			'query_var'     => self::$paginate_link_name,
			'total'         => 0,
			'current'       => 0,
			'show_now_page' => true,
			'echo'          => false
		);
		$args            = wp_parse_args( $args, $defaults );
		$total_page      = ceil( $args['total'] / $args['item_per_page'] );
		$args['current'] = ( $args['current'] < 1 ? self::getCurrentPaged() : 1 );
		$export          = '';

		//Show Pagination Ui
		if ( $total_page > 1 ) {
			$export .= '<div class="' . $args['container'] . '">';
			$export .= paginate_links( array(
				'base'      => add_query_arg( $args['query_var'], '%#%' ),
				'format'    => '',
				'type'      => 'list',
				'mid_size'  => 3,
				'prev_text' => __( '&laquo;' ),
				'next_text' => __( '&raquo;' ),
				'total'     => $total_page,
				'current'   => $args['current']
			) );

			if ( $args['show_now_page'] ) {
				$export .= '<p class="wps-page-number">' . sprintf( __( 'Page %1$s of %2$s', 'wp-statistics' ), $args['current'], $total_page ) . '</p>';
			}
			$export .= '</div>';

			// Export Data
			if ( $args['echo'] ) {
				echo $export;
			} else {
				return $export;
			}
		}
	}

	/**
	 * Show WordPress DashIcons
	 *
	 * @param $dashicons
	 * @return string
	 */
	public static function icons( $dashicons ) {
		return '<span class="dashicons ' . $dashicons . '"></span>';
	}

	/**
	 * insert JQuery Code
	 *
	 * @param $code
	 * @return string
	 */
	public static function JQuery( $code ) {
		return '
		 	<script type="text/javascript">
			    jQuery(document).ready(function ($) {
			        ' . $code . '
			    });
			</script>
		';
	}

	/**
	 * Convert PHP date Format to Moment js
	 *
	 * @param $phpFormat
	 * @return string
	 * @see https://stackoverflow.com/questions/30186611/php-dateformat-to-moment-js-format
	 */
	public static function convert_php_to_moment_js( $phpFormat ) {
		$replacements = array(
			'A' => 'A',
			'a' => 'a',
			'B' => '',
			'c' => 'YYYY-MM-DD[T]HH:mm:ssZ',
			'D' => 'ddd',
			'd' => 'DD',
			'e' => 'zz',
			'F' => 'MMMM',
			'G' => 'H',
			'g' => 'h',
			'H' => 'HH',
			'h' => 'hh',
			'I' => '',
			'i' => 'mm',
			'j' => 'D',
			'L' => '',
			'l' => 'dddd',
			'M' => 'MMM',
			'm' => 'MM',
			'N' => 'E',
			'n' => 'M',
			'O' => 'ZZ',
			'o' => 'YYYY',
			'P' => 'Z',
			'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ',
			'S' => 'o',
			's' => 'ss',
			'T' => 'z',
			't' => '',
			'U' => 'X',
			'u' => 'SSSSSS',
			'v' => 'SSS',
			'W' => 'W',
			'w' => 'e',
			'Y' => 'YYYY',
			'y' => 'YY',
			'Z' => '',
			'z' => 'DDD'
		);
		// Converts escaped characters.
		foreach ( $replacements as $from => $to ) {
			$replacements[ '\\' . $from ] = '[' . $from . ']';
		}
		return strtr( $phpFormat, $replacements );
	}

	/**
	 * Convert php date format to Jquery Ui
	 *
	 * @param $php_format
	 * @return string
	 */
	public static function convert_php_to_jquery_datepicker( $php_format ) {
		$SYMBOLS_MATCHING = array(
			// Day
			'd' => 'dd',
			'D' => 'D',
			'j' => 'd',
			'l' => 'DD',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			// Week
			'W' => '',
			// Month
			'F' => 'MM',
			'm' => 'mm',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'yy',
			'y' => 'y',
			// Time
			'a' => '',
			'A' => '',
			'B' => '',
			'g' => '',
			'G' => '',
			'h' => '',
			'H' => '',
			'i' => '',
			's' => '',
			'u' => ''
		);
		$jqueryui_format  = "";
		$escaping         = false;
		for ( $i = 0; $i < strlen( $php_format ); $i ++ ) {
			$char = $php_format[ $i ];
			if ( $char === '\\' ) {
				$i ++;
				if ( $escaping ) {
					$jqueryui_format .= $php_format[ $i ];
				} else {
					$jqueryui_format .= '\'' . $php_format[ $i ];
				}
				$escaping = true;
			} else {
				if ( $escaping ) {
					$jqueryui_format .= "'";
					$escaping        = false;
				}
				if ( isset( $SYMBOLS_MATCHING[ $char ] ) ) {
					$jqueryui_format .= $SYMBOLS_MATCHING[ $char ];
				} else {
					$jqueryui_format .= $char;
				}
			}
		}
		return $jqueryui_format;
	}

	/**
	 * Create Jquery UI Date Picker
	 *
	 * @param $page
	 * @param $current
	 * @param array $range
	 * @param array $desc
	 * @param string $extrafields
	 * @param string $pre_extra
	 * @param string $post_extra
	 * @throws \Exception
	 */
	public static function date_range_selector( $page, $current, $range = array(), $desc = array(), $extrafields = '', $pre_extra = '', $post_extra = '' ) {

		//Create Object List Of Default Hit Day to Display
		if ( $range == null or count( $range ) == 0 ) {

			//Get Number Of Time Range
			$range = array( 10, 20, 30, 60, 90, 180, 270, 365 );

			//Added All time From installed plugin to now
			$installed_date = Helper::get_number_days_install_plugin();
			array_push( $range, $installed_date['days'] );

			//Get List Of Text Lang time Range
			$desc = array(
				__( '10 Days', 'wp-statistics' ),
				__( '20 Days', 'wp-statistics' ),
				__( '30 Days', 'wp-statistics' ),
				__( '2 Months', 'wp-statistics' ),
				__( '3 Months', 'wp-statistics' ),
				__( '6 Months', 'wp-statistics' ),
				__( '9 Months', 'wp-statistics' ),
				__( '1 Year', 'wp-statistics' ),
				__( 'All', 'wp-statistics' ),
			);
		}
		if ( count( $desc ) == 0 ) {
			$desc = $range;
		}
		$rcount = count( $range );
		$bold   = true;

		// Check to see if there's a range in the URL, if so set it, otherwise use the default.
		if ( isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false ) {
			$rangestart = $_GET['rangestart'];
		} else {
			$rangestart = TimeZone::getCurrentDate( 'm/d/Y', '-' . $current );
		}
		if ( isset( $_GET['rangeend'] ) and strtotime( $_GET['rangeend'] ) != false ) {
			$rangeend = $_GET['rangeend'];
		} else {
			$rangeend = TimeZone::getCurrentDate( 'm/d/Y' );
		}

		// Convert the text dates to unix timestamps and do some basic sanity checking.
		$rangestart_utime = TimeZone::strtotimetz( $rangestart );
		if ( false === $rangestart_utime ) {
			$rangestart_utime = time();
		}
		$rangeend_utime = TimeZone::strtotimetz( $rangeend );
		if ( false === $rangeend_utime || $rangeend_utime < $rangestart_utime ) {
			$rangeend_utime = time();
		}

		// Now get the number of days in the range.
		$daysToDisplay = (int) ( ( $rangeend_utime - $rangestart_utime ) / 24 / 60 / 60 );
		$today         = TimeZone::getCurrentDate( 'm/d/Y' );

		// Re-create the range start/end strings from our utime's to make sure we get ride of any cruft and have them in the format we want.
		$rangestart = TimeZone::getLocalDate( self::$datepicker_format, $rangestart_utime );
		$rangeend   = TimeZone::getLocalDate( self::$datepicker_format, $rangeend_utime );

		//Calculate hit day if range is exist
		if ( isset( $_GET['rangeend'] ) and isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false and strtotime( $_GET['rangeend'] ) != false ) {
			$earlier = new \DateTime( $_GET['rangestart'] );
			$later   = new \DateTime( $_GET['rangeend'] );
			$current = $daysToDisplay = $later->diff( $earlier )->format( "%a" );
		}

		echo '<form method="get"><ul class="subsubsub wp-statistics-sub-fullwidth">' . "\r\n";
		// Output any extra HTML we've been passed after the form element but before the date selector.
		echo $pre_extra;

		for ( $i = 0; $i < $rcount; $i ++ ) {
			echo '<li class="all"><a ';
			if ( $current == $range[ $i ] ) {
				echo 'class="current" ';
				$bold = false;
			}

			// Don't bother adding he date range to the standard links as they're not needed any may confuse the custom range selector.
			echo 'href="?page=' . $page . '&hitdays=' . $range[ $i ] . esc_html( $extrafields ) . '">' . $desc[ $i ] . '</a></li>';
			if ( $i < $rcount - 1 ) {
				echo ' | ';
			}
			echo "\r\n";
		}
		echo ' | ';
		echo '<input type="hidden" name="page" value="' . $page . '">';

		parse_str( $extrafields, $parse );
		foreach ( $parse as $key => $value ) {
			echo '<input type="hidden" name="' . $key . '" value="' . esc_sql( $value ) . '">';
		}

		if ( $bold ) {
			echo ' <b>' . __( 'Time Frame', 'wp-statistics' ) . ':</b> ';
		} else {
			echo ' ' . __( 'Time Frame', 'wp-statistics' ) . ': ';
		}

		//Print Time Range Select Ui
		echo '<input type="text" size="18" name="rangestart" wps-date-picker="from" value="' . $rangestart . '" placeholder="' . self::$datepicker_format . '" autocomplete="off"> ' . __( 'to', 'wp-statistics' ) . ' <input type="text" size="18" name="rangeend" wps-date-picker="to" value="' . $rangeend . '" placeholder="' . self::$datepicker_format . '" autocomplete="off"> <input type="submit" value="' . __( 'Go', 'wp-statistics' ) . '" class="button-primary">' . "\r\n";

		//Sanitize Time Request
		echo '<input type="hidden" name="rangestart" id="date-from" value="' . TimeZone::getLocalDate( self::$datepicker_format, $rangestart_utime ) . '">';
		echo '<input type="hidden" name="rangeend" id="date-to" value="' . TimeZone::getLocalDate( self::$datepicker_format, $rangeend_utime ) . '">';

		// Output any extra HTML we've been passed after the date selector but before the submit button.
		echo $post_extra;
		echo '</form>' . "\r\n";
	}

	/**
	 * Prepare Range Time For Time picker
	 */
	public static function prepare_range_time_picker() {

		//Get Default Number To display in All
		$installed_date = Helper::get_number_days_install_plugin();
		$daysToDisplay  = $installed_date['days'];

		//List Of Pages For show 20 Days as First Parameter
		$list_of_pages = array( 'hits', 'searches', 'pages', 'countries', 'categories', 'tags', 'authors', 'browser', 'exclusions' );
		foreach ( $list_of_pages as $page ) {
			if ( Menus::in_page( $page ) ) {
				$daysToDisplay = 30;
			}
		}

		//Set Default Object Time Range
		$rangestart = '';
		$rangeend   = '';

		//Check Hit Day
		if ( isset( $_GET['hitdays'] ) and $_GET['hitdays'] > 0 ) {
			$daysToDisplay = intval( $_GET['hitdays'] );
		}
		if ( isset( $_GET['rangeend'] ) and isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false and strtotime( $_GET['rangeend'] ) != false ) {
			$rangestart = $_GET['rangestart'];
			$rangeend   = $_GET['rangeend'];

			//Calculate hit day if range is exist
			$earlier       = new \DateTime( $_GET['rangestart'] );
			$later         = new \DateTime( $_GET['rangeend'] );
			$daysToDisplay = $later->diff( $earlier )->format( "%a" );
		}

		return array( $daysToDisplay, $rangestart, $rangeend );
	}
}