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
	 * Get Screen ID
	 *
	 * @return string
	 */
	public static function get_screen_id() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		return $screen_id;
	}

	/**
	 * Get File Path Of Plugins File
	 *
	 * @param $path
	 * @return string
	 */
	public static function get_file_path( $path ) {
		return wp_normalize_path( path_join( WP_STATISTICS_DIR, $path ) );
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
		include WP_STATISTICS_DIR . "includes/defines/country-codes.php";
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
		include WP_STATISTICS_DIR . "includes/defines/robots-list.php";
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
	 * Get Site title By Url
	 *
	 * @param $url string e.g : wp-statistics.com
	 * @return bool|string
	 */
	public static function get_site_title_by_url( $url ) {

		//Get Body Page
		$html = Helper::get_html_page( $url );
		if ( $html === false ) {
			return false;
		}

		//Get Page Title
		if ( class_exists( 'DOMDocument' ) ) {
			$dom = new \DOMDocument;
			@$dom->loadHTML( $html );
			$title = '';
			if ( isset( $dom ) and $dom->getElementsByTagName( 'title' )->length > 0 ) {
				$title = $dom->getElementsByTagName( 'title' )->item( '0' )->nodeValue;
			}
			return ( wp_strip_all_tags( $title ) == "" ? false : wp_strip_all_tags( $title ) );
		}

		return false;
	}

	/**
	 * Get Html Body Page By Url
	 *
	 * @param $url string e.g : wp-statistics.com
	 * @return bool
	 */
	public static function get_html_page( $url ) {

		//sanitize Url
		$parse_url = wp_parse_url( $url );
		$urls[]    = esc_url_raw( $url );

		//Check Protocol Url
		if ( ! array_key_exists( 'scheme', $parse_url ) ) {
			$urls      = array();
			$url_parse = wp_parse_url( $url );
			foreach ( array( 'http://', 'https://' ) as $scheme ) {
				$urls[] = preg_replace( '/([^:])(\/{2,})/', '$1/', $scheme . path_join( ( isset( $url_parse['host'] ) ? $url_parse['host'] : '' ), ( isset( $url_parse['path'] ) ? $url_parse['path'] : '' ) ) );
			}
		}

		//Send Request for Get Page Html
		foreach ( $urls as $page ) {
			$response = wp_remote_get( $page, array(
				'timeout'    => 30,
				'user-agent' => "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36"
			) );
			if ( is_wp_error( $response ) ) {
				continue;
			}
			$data = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $data ) ) {
				continue;
			}
			return ( wp_strip_all_tags( $data ) == "" ? false : $data );
		}

		return false;
	}

	/**
	 * Generate Random String
	 *
	 * @param $num
	 * @return string
	 */
	public static function random_string( $num = 50 ) {
		$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ( $i = 0; $i < $num; $i ++ ) {
			$randomString .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $randomString;
	}

	/**
	 * Get Post List From custom Post Type
	 *
	 * @param array $args
	 * @area utility
	 * @return mixed
	 */
	public static function get_post_list( $args = array() ) {

		//Prepare Arg
		$defaults = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => '-1',
			'order'          => 'ASC',
			'fields'         => 'ids'
		);
		$args     = wp_parse_args( $args, $defaults );

		//Get Post List
		$query = new \WP_Query( $args );
		$list  = array();
		foreach ( $query->posts as $ID ) {
			$list[ $ID ] = get_the_title( $ID );
		}

		return $list;
	}

	/**
	 * Get Page information
	 *
	 * @param $page_id
	 * @param string $type
	 * @return array
	 */
	public static function get_page_info( $page_id, $type = 'post' ) {

		//Create Empty Object
		$arg      = array();
		$defaults = array(
			'link'      => '',
			'edit_link' => '',
			'object_id' => $page_id,
			'title'     => '-',
			'meta'      => array()
		);

		if ( ! empty( $type ) ) {
			switch ( $type ) {
				case "product":
				case "attachment":
				case "post":
				case "page":
					$arg = array(
						'title'     => get_the_title( $page_id ),
						'link'      => get_the_permalink( $page_id ),
						'edit_link' => get_edit_post_link( $page_id ),
						'meta'      => array(
							'post_type' => get_post_type( $page_id )
						)
					);
					break;
				case "category":
				case "post_tag":
				case "tax":
					$term = get_term( $page_id );
					$arg  = array(
						'title'     => $term->name,
						'link'      => ( is_wp_error( get_term_link( $page_id ) ) === true ? '' : get_term_link( $page_id ) ),
						'edit_link' => get_edit_term_link( $page_id ),
						'meta'      => array(
							'taxonomy'         => $term->taxonomy,
							'term_taxonomy_id' => $term->term_taxonomy_id,
							'count'            => $term->count,
						)
					);
					break;
				case "home":
					$arg = array(
						'title' => __( 'Home Page', 'wp-statistics' ),
						'link'  => get_site_url()
					);
					break;
				case "author":
					$user_info = get_userdata( $page_id );
					$arg       = array(
						'title'     => ( $user_info->display_name != "" ? $user_info->display_name : $user_info->first_name . ' ' . $user_info->last_name ),
						'link'      => get_author_posts_url( $page_id ),
						'edit_link' => get_edit_user_link( $page_id ),
					);
					break;
				case "search":
					$result['title'] = __( 'Search Page', 'wp-statistics' );
					break;
				case "404":
					$result['title'] = __( '404 not found', 'wp-statistics' );
					break;
				case "archive":
					$result['title'] = __( 'Post Archive', 'wp-statistics' );
					break;
			}
		}

		return wp_parse_args( $arg, $defaults );
	}

	/**
	 * Generate RGBA colors
	 *
	 * @param        $num
	 * @param string $opacity
	 * @param bool $quote
	 * @return string
	 */
	public static function GenerateRgbaColor( $num, $opacity = '1', $quote = true ) {
		$hash   = md5( 'color' . $num );
		$format = ( $quote === true ? "'rgba(%s, %s, %s, %s)'" : "rgba(%s, %s, %s, %s)" );

		return sprintf( $format,
			hexdec( substr( $hash, 0, 2 ) ),
			hexdec( substr( $hash, 2, 2 ) ),
			hexdec( substr( $hash, 4, 2 ) ),
			$opacity
		);
	}

}