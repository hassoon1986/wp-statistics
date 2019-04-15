<?php

namespace WP_STATISTICS;

use WP_STATISTICS;

class Helper {
	/**
	 * WP-Statistics WordPress Log
	 *
	 * @param $function
	 * @param $message
	 * @param $version
	 */
	public static function doing_it_wrong( $function, $message, $version ) {
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();
		if ( is_ajax() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
	}

	/**
	 * Returns an array of site id's
	 *
	 * @return array
	 */
	public static function get_wp_sites_list() {
		$site_list = array();
		$sites     = get_sites();
		foreach ( $sites as $site ) {
			$site_list[] = $site->blog_id;
		}
		return $site_list;
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public static function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'wp-cli':
				return defined( 'WP_CLI' ) && WP_CLI;
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! self::is_rest_request();
		}
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * @return bool
	 */
	public static function is_rest_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		return ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );
	}

	/**
	 * Show Admin Wordpress Ui Notice
	 *
	 * @param $text
	 * @param string $model
	 * @param bool $close_button
	 * @param bool $echo
	 * @param string $style_extra
	 * @return string
	 */
	public static function wp_admin_notice( $text, $model = "info", $close_button = true, $echo = true, $style_extra = 'padding:12px;' ) {
		$text = '
        <div class="notice notice-' . $model . '' . ( $close_button === true ? " is-dismissible" : "" ) . '">
           <div style="' . $style_extra . '">' . $text . '</div>
        </div>
        ';
		if ( $echo ) {
			echo $text;
		} else {
			return $text;
		}
	}

	/**
	 * Check User is Used Cache Plugin
	 *
	 * @return array
	 */
	public static function is_active_cache_plugin() {
		$use = array( 'status' => false, 'plugin' => '' );

		/* Wordpress core */
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return array( 'status' => true, 'plugin' => 'core' );
		}

		/* WP Rocket */
		if ( function_exists( 'get_rocket_cdn_url' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Rocket' );
		}

		/* WP Super Cache */
		if ( function_exists( 'wpsc_init' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Super Cache' );
		}

		/* Comet Cache */
		if ( function_exists( '___wp_php_rv_initialize' ) ) {
			return array( 'status' => true, 'plugin' => 'Comet Cache' );
		}

		/* WP Fastest Cache */
		if ( class_exists( 'WpFastestCache' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Fastest Cache' );
		}

		/* Cache Enabler */
		if ( defined( 'CE_MIN_WP' ) ) {
			return array( 'status' => true, 'plugin' => 'Cache Enabler' );
		}

		/* W3 Total Cache */
		if ( defined( 'W3TC' ) ) {
			return array( 'status' => true, 'plugin' => 'W3 Total Cache' );
		}

		return $use;
	}

	/**
	 * Get WordPress Uploads DIR
	 *
	 * @param string $path
	 * @return mixed
	 * @default For WP-Statistics Plugin is 'wp-statistics' dir
	 */
	public static function get_uploads_dir( $path = '' ) {
		$upload_dir = wp_upload_dir();
		return path_join( $upload_dir['basedir'], $path );
	}

	/**
	 * Get country codes
	 *
	 * @return array|bool|string
	 */
	public static function get_country_codes() {
		global $WP_Statistics;

		# Load From global
		if ( isset( $WP_Statistics->country_codes ) ) {
			return $WP_Statistics->country_codes;
		}

		# Load From file
		require_once WP_STATISTICS_DIR . "includes/defines/country-codes.php";
		if ( isset( $ISOCountryCode ) ) {
			return $ISOCountryCode;
		}

		return array();
	}

	/**
	 * Get Robots List
	 *
	 * @param string $type
	 * @return array|bool|string
	 */
	public static function get_robots_list( $type = 'list' ) {
		global $WP_Statistics;

		# Set Default
		$list = array();

		# Load From global
		if ( isset( $WP_Statistics->robots_list ) ) {
			$list = $WP_Statistics->robots_list;
		}

		# Load From file
		require_once WP_STATISTICS_DIR . "includes/defines/robots-list.php";
		if ( isset( $wps_robots_list_array ) ) {
			$list = $wps_robots_list_array;
		}

		return ( $type == "array" ? $list : implode( "\n", $list ) );
	}

	/**
	 * Get Number Days From install this plugin
	 * this method used for `ALL` Option in Time Range Pages
	 */
	public static function get_number_days_install_plugin() {
		global $wpdb;

		//Create Empty default Option
		$first_day = '';

		//First Check Visitor Table , if not exist Web check Pages Table
		$list_tbl = array(
			'visitor' => array( 'order_by' => 'ID', 'column' => 'last_counter' ),
			'pages'   => array( 'order_by' => 'page_id', 'column' => 'date' ),
		);
		foreach ( $list_tbl as $tbl => $val ) {
			$first_day = $wpdb->get_var( "SELECT `" . $val['column'] . "` FROM `" . WP_STATISTICS\DB::table( $tbl ) . "` ORDER BY `" . $val['order_by'] . "` ASC LIMIT 1" );
			if ( ! empty( $first_day ) ) {
				break;
			}
		}

		//Calculate hit day if range is exist
		if ( empty( $first_day ) ) {
			$result = array(
				'days' => 1,
				'date' => current_time( 'timestamp' )
			);
		} else {
			$earlier = new \DateTime( $first_day );
			$later   = new \DateTime( WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' ) );
			$result  = array(
				'days'      => $later->diff( $earlier )->format( "%a" ),
				'timestamp' => strtotime( $first_day ),
				'first_day' => $first_day,
			);
		}

		return $result;
	}

	/**
	 * Check User Is Using Gutenberg Editor
	 */
	public static function is_gutenberg() {
		$current_screen = get_current_screen();
		if ( ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) || ( function_exists( 'is_gutenberg_page' ) ) && is_gutenberg_page() ) {
			return true;
		}
		return false;
	}

	/**
	 * Get List WordPress Post Type
	 *
	 * @return array
	 */
	public static function get_list_post_type() {
		$post_types     = array( 'post', 'page' );
		$get_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
		foreach ( $get_post_types as $name ) {
			$post_types[] = $name;
		}

		return $post_types;
	}

	/**
	 * Check Url Scheme
	 *
	 * @param $url
	 * @param array $accept
	 * @return bool
	 */
	public static function check_url_scheme( $url, $accept = array( 'http', 'https' ) ) {
		$scheme = @parse_url( $url, PHP_URL_SCHEME );
		return in_array( $scheme, $accept );
	}

	/**
	 * Get Page Type For Push in WP-Statistics DB
	 */
	public static function get_page_type() {

		//Set Default Option
		$result = array( "type" => "unknown", "id" => 0 );

		//Check Query object
		$id = get_queried_object_id();
		if ( is_numeric( $id ) and $id > 0 ) {
			$result['id'] = $id;
		}

		//WooCommerce Product
		if ( class_exists( 'WooCommerce' ) ) {
			if ( is_product() ) {
				return wp_parse_args( array( "type" => "product" ), $result );
			}
		}

		//Home Page or Front Page
		if ( is_front_page() || is_home() ) {
			return wp_parse_args( array( "type" => "home" ), $result );
		}

		//attachment View
		if ( is_attachment() ) {
			$result['type'] = "attachment";
		}

		//is Archive Page
		if ( is_archive() ) {
			$result['type'] = "archive";
		}

		//Single Post Fro All Post Type
		if ( is_singular() ) {
			$result['type'] = "post";
		}

		//Single Page
		if ( is_page() ) {
			$result['type'] = "page";
		}

		//Category Page
		if ( is_category() ) {
			$result['type'] = "category";
		}

		//Tag Page
		if ( is_tag() ) {
			$result['type'] = "post_tag";
		}

		//is Custom Term From Taxonomy
		if ( is_tax() ) {
			$result['type'] = "tax";
		}

		//is Author Page
		if ( is_author() ) {
			$result['type'] = "author";
		}

		//is search page
		$search_query = filter_var( get_search_query( false ), FILTER_SANITIZE_STRING );
		if ( trim( $search_query ) != "" ) {
			return array( "type" => "search", "id" => 0, "search_query" => $search_query );
		}

		//is 404 Page
		if ( is_404() ) {
			$result['type'] = "404";
		}

		return apply_filters( 'wp_statistics_current_page', $result );
	}

	/**
	 * Check Track All Page WP-Statistics
	 *
	 * @return bool
	 */
	public static function is_track_all_page() {
		return $GLOBALS['WP_Statistics']->option->get( 'track_all_pages' ) || is_single() || is_page() || is_front_page();
	}

	/**
	 * Get WordPress Version
	 *
	 * @return mixed|string
	 */
	public static function get_wordpress_version() {
		return get_bloginfo( 'version' );
	}

	/**
	 * Convert Json To Array
	 *
	 * @param $json
	 * @return bool|mixed
	 */
	public static function json_to_array( $json ) {

		// Sanitize Slash Data
		$data = wp_unslash( $json );

		// Check Validate Json Data
		if ( ! empty( $data ) && is_string( $data ) && is_array( json_decode( $data, true ) ) && json_last_error() == 0 ) {
			return json_decode( $data, true );
		}

		return false;
	}

	/**
	 * Standard Json Encode
	 *
	 * @param $array
	 * @return false|string
	 */
	public static function standard_json_encode( $array ) {

		//Fixed entity decode Html
		foreach ( (array) $array as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}
			$array[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		return json_encode( $array, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Show Site Icon by Url
	 *
	 * @param $url
	 * @param int $size
	 * @param string $style
	 * @return bool|string
	 */
	public static function show_site_icon( $url, $size = 16, $style = '' ) {
		$url = preg_replace( '/^https?:\/\//', '', $url );
		if ( $url != "" ) {
			$imgurl = "https://www.google.com/s2/favicons?domain=" . $url;
			return '<img src="' . $imgurl . '" width="' . $size . '" height="' . $size . '" style="' . ( $style == "" ? 'vertical-align: -3px;' : '' ) . '" />';
		}

		return false;
	}

	/**
	 * Get Domain name from url
	 * e.g : https://wp-statistics.com/add-ons/ -> wp-statistics.com
	 *
	 * @param $url
	 * @return mixed
	 */
	public static function get_domain_name( $url ) {
		//Remove protocol
		$url = preg_replace( "(^https?://)", "", trim( $url ) );
		//remove w(3)
		$url = preg_replace( '#^(http(s)?://)?w{3}\.#', '$1', $url );
		//remove all Query
		$url = explode( "/", $url );

		return $url[0];
	}

	/**
	 * Get Page Url
	 *
	 * @return bool|mixed|string
	 */
	public static function get_page_uri() {

		// Get the site's path from the URL.
		$site_uri     = parse_url( site_url(), PHP_URL_PATH );
		$site_uri_len = strlen( $site_uri );

		// Get the site's path from the URL.
		$home_uri     = parse_url( home_url(), PHP_URL_PATH );
		$home_uri_len = strlen( $home_uri );

		// Get the current page URI.
		$page_uri = $_SERVER["REQUEST_URI"];

		/*
		 * We need to check which URI is longer in case one contains the other.
		 * For example home_uri might be "/site/wp" and site_uri might be "/site".
		 * In that case we want to check to see if the page_uri starts with "/site/wp" before
		 * we check for "/site", but in the reverse case, we need to swap the order of the check.
		 */
		if ( $site_uri_len > $home_uri_len ) {
			if ( substr( $page_uri, 0, $site_uri_len ) == $site_uri ) {
				$page_uri = substr( $page_uri, $site_uri_len );
			}

			if ( substr( $page_uri, 0, $home_uri_len ) == $home_uri ) {
				$page_uri = substr( $page_uri, $home_uri_len );
			}
		} else {
			if ( substr( $page_uri, 0, $home_uri_len ) == $home_uri ) {
				$page_uri = substr( $page_uri, $home_uri_len );
			}

			if ( substr( $page_uri, 0, $site_uri_len ) == $site_uri ) {
				$page_uri = substr( $page_uri, $site_uri_len );
			}
		}

		//Sanitize Xss injection
		$page_uri = filter_var( $page_uri, FILTER_SANITIZE_STRING );

		// If we're at the root (aka the URI is blank), let's make sure to indicate it.
		if ( $page_uri == '' ) {
			$page_uri = '/';
		}

		return apply_filters( 'wp_statistics_page_uri', $page_uri );
	}


}