<?php

namespace WP_STATISTICS;

class Admin_Assets {
	/**
	 * Prefix Of Load Css/Js in WordPress Admin
	 *
	 * @var string
	 */
	public static $prefix = 'wp-statistics-admin';

	/**
	 * Suffix Of Minify File in Assets
	 *
	 * @var string
	 */
	public static $suffix_min = '.min';

	/**
	 * Assets Folder name in Plugin
	 *
	 * @var string
	 */
	public static $asset_dir = 'assets';

	/**
	 * Basic Of Plugin Url in Wordpress
	 *
	 * @var string
	 * @example http://site.com/wp-content/plugins/my-plugin/
	 */
	public static $plugin_url = WP_STATISTICS_URL;

	/**
	 * Current Asset Version for this plugin
	 *
	 * @var string
	 */
	public static $asset_version = WP_STATISTICS_VERSION;

	/**
	 * Admin_Assets constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Get Version of File
	 *
	 * @param $ver
	 * @return bool
	 */
	public static function version( $ver = false ) {
		if ( $ver ) {
			return $ver;
		} else {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				return time();
			} else {
				return self::$asset_version;
			}
		}
	}

	/**
	 * Get Asset Url
	 *
	 * @param $file_name
	 * @return string
	 */
	public static function url( $file_name ) {

		// Get file Extension Type
		$ext = pathinfo( $file_name, PATHINFO_EXTENSION );
		if ( $ext != "js" and $ext != "css" ) {
			$ext = 'images';
		}

		// Prepare File Path
		$path = self::$asset_dir . '/' . $ext . '/';

		// Prepare Full Url
		$url = self::$plugin_url . $path;

		// Check Exist Min Version for Css / Js
		if ( defined( 'SCRIPT_DEBUG' ) and SCRIPT_DEBUG === false and ( $ext == "css" || $ext == "js" ) ) {
			$min_version = str_replace( array( ".css", ".js" ), array( self::$suffix_min . ".css", self::$suffix_min . ".js" ), $file_name );
			if ( file_exists( Helper::get_file_path( $path . $min_version ) ) ) {
				return $url . $min_version;
			}
		}

		return $url . $file_name;
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {

		// Get Current Screen ID
		$screen_id = Helper::get_screen_id();

		// Load Admin Css
		wp_enqueue_style( self::$prefix, self::url( 'admin.css' ), array(), self::version() );

		// Load Rtl Version Css
		if ( is_rtl() ) {
			wp_enqueue_style( self::$prefix . '-rtl', self::url( 'rtl.css' ), array(), self::version() );
		}

		//Load Jquery VMap Css
		if ( ! Option::get( 'disable_map' ) and ( Menus::in_page( 'overview' ) || ( in_array( $screen_id, array( 'dashboard' ) ) and ! Option::get( 'disable_dashboard' ) ) ) ) {
			wp_enqueue_style( self::$prefix . '-jqvmap', self::url( 'jqvmap/jqvmap.min.css' ), array(), '1.5.1' );
		}

		// Load Jquery-ui theme
		if ( Menus::in_plugin_page() and Menus::in_page( 'overview' ) === false and Menus::in_page( 'optimization' ) === false and Menus::in_page( 'settings' ) === false ) {
			wp_enqueue_style( self::$prefix . '-jquery-ui-smooth', self::url( 'jquery-ui/smoothness.min.css' ), array(), '1.11.4' );
		}

	}

	/**
	 * Enqueue scripts.
	 *
	 * @param $hook [ Page Now ]
	 */
	public function admin_scripts( $hook ) {

		// Get Current Screen ID
		$screen_id = Helper::get_screen_id();

		// Load Chart Js Library [ Load in <head> Tag ]
		if ( Menus::in_plugin_page() || ( in_array( $screen_id, array( 'dashboard' ) ) and ! Option::get( 'disable_dashboard' ) ) || ( in_array( $hook, array( 'post.php', 'edit.php', 'post-new.php' ) ) and ! Option::get( 'disable_editor' ) ) ) {
			wp_enqueue_script( self::$prefix . '-chart.js', self::url( 'chartjs/chart.bundle.min.js' ), false, '2.8.0', false );
		}

		// Load Jquery VMap Js Library
		if ( ! Option::get( 'disable_map' ) and ( Menus::in_page( 'overview' ) || ( in_array( $screen_id, array( 'dashboard' ) ) and ! Option::get( 'disable_dashboard' ) ) ) ) {
			wp_enqueue_script( self::$prefix . '-jqvmap', self::url( 'jqvmap/jquery.vmap.min.js' ), true, '1.5.1' );
			wp_enqueue_script( self::$prefix . '-jqvmap-world', self::url( 'jqvmap/jquery.vmap.world.min.js' ), true, '1.5.1' );
		}

		// Load Jquery UI and Moment Js
		if ( Menus::in_plugin_page() and Menus::in_page( 'overview' ) === false and Menus::in_page( 'optimization' ) === false and Menus::in_page( 'settings' ) === false ) {
			wp_enqueue_script( self::$prefix . '-momentjs', self::url( 'moment-js/moment.min.js' ), true, '2.24.0' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}

		// Load WordPress PostBox Script
		if ( Menus::in_plugin_page() and Menus::in_page( 'optimization' ) === false and Menus::in_page( 'settings' ) === false ) {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
		}

		// Load Admin Js
		if ( Menus::in_plugin_page() || ( in_array( $screen_id, array( 'dashboard' ) ) and ! Option::get( 'disable_dashboard' ) ) || ( in_array( $hook, array( 'post.php', 'edit.php', 'post-new.php' ) ) and ! Option::get( 'disable_editor' ) and ! Helper::is_gutenberg() ) ) {
			wp_enqueue_script( self::$prefix, self::url( 'admin.min.js' ), array( 'jquery' ), self::version() );
			wp_localize_script( self::$prefix, 'wps_global', self::wps_global( $hook ) );
		}

		// Load TinyMCE for Widget Page
		if ( in_array( $screen_id, array( 'widgets' ) ) ) {
			wp_enqueue_script( self::$prefix . '-button-widget', self::url( 'tinymce.min.js' ), array( 'jquery' ), self::version() );
		}

		// Load Gutenberg Script
		if ( in_array( $hook, array( 'post.php', 'edit.php', 'post-new.php' ) ) and ! Option::get( 'disable_editor' ) and Helper::is_gutenberg() ) {
			wp_enqueue_script( self::$prefix . '-gutenberg', self::url( 'gutenberg.min.js' ), self::version() );
		}

	}

	/**
	 * Prepare global WP-Statistics data for use Admin Js
	 *
	 * @param $hook
	 * @return mixed
	 */
	public static function wps_global( $hook ) {
		global $post;

		// Date Format
		$list['date_format'] = array(
			'jquery_ui' => Admin_Templates::convert_php_to_jquery_datepicker( get_option( "date_format" ) ),
			'moment_js' => Admin_Templates::convert_php_to_moment_js( get_option( "date_format" ) ),
		);

		//Global Option
		$list['options'] = array(
			'rtl'           => ( is_rtl() ? 1 : 0 ),
			'user_online'   => ( Option::get( 'useronline' ) ? 1 : 0 ),
			'visitors'      => ( Option::get( 'visitors' ) ? 1 : 0 ),
			'visits'        => ( Option::get( 'visits' ) ? 1 : 0 ),
			'geo_ip'        => ( GeoIP::active() ? 1 : 0 ),
			'geo_city'      => ( GeoIP::active( 'geoip_city' ) ? 1 : 0 ),
			'overview_page' => ( Menus::in_page( 'overview' ) ? 1 : 0 ),
			'gutenberg'     => ( Helper::is_gutenberg() ? 1 : 0 )
		);

		// WordPress Current Page
		$list['page'] = array(
			'file' => $hook,
			'ID'       => ( isset( $post ) ? $post->ID : 0 )
		);

		// Global Lang
		$list['i18n'] = array(
			'more_detail'   => __( 'More Details', 'wp-statistics' ),
			'reload'        => __( 'Reload', 'wp-statistics' ),
			'online_users'  => __( 'Online Users', 'wp-statistics' ),
			'visitors'      => __( 'Visitors', 'wp-statistics' ),
			'visits'        => __( 'Visits', 'wp-statistics' ),
			'today'         => __( 'Today', 'wp-statistics' ),
			'yesterday'     => __( 'Yesterday', 'wp-statistics' ),
			'week'          => __( 'Last 7 Days (Week)', 'wp-statistics' ),
			'month'         => __( 'Last 30 Days (Month)', 'wp-statistics' ),
			'year'          => __( 'Last 365 Days (Year)', 'wp-statistics' ),
			'total'         => __( 'Total', 'wp-statistics' ),
			'daily_total'   => __( 'Daily Total', 'wp-statistics' ),
			'date'          => __( 'Date', 'wp-statistics' ),
			'time'          => __( 'Time', 'wp-statistics' ),
			'browsers'      => __( 'Browsers', 'wp-statistics' ),
			'rank'          => __( 'Rank', 'wp-statistics' ),
			'flag'          => __( 'Flag', 'wp-statistics' ),
			'country'       => __( 'Country', 'wp-statistics' ),
			'visitor_count' => __( 'Visitor Count', 'wp-statistics' ),
			'id'            => __( 'ID', 'wp-statistics' ),
			'title'         => __( 'Title', 'wp-statistics' ),
			'link'          => __( 'Link', 'wp-statistics' ),
			'address'       => __( 'Address', 'wp-statistics' ),
			'word'          => __( 'Word', 'wp-statistics' ),
			'browser'       => __( 'Browser', 'wp-statistics' ),
			'city'          => __( 'City', 'wp-statistics' ),
			'ip'            => __( 'IP', 'wp-statistics' ),
			'referrer'      => __( 'Referrer', 'wp-statistics' ),
			'hits'          => __( 'Hits', 'wp-statistics' ),
			'agent'         => __( 'Agent', 'wp-statistics' ),
			'platform'      => __( 'Platform', 'wp-statistics' ),
			'version'       => __( 'Version', 'wp-statistics' ),
			'page'          => __( 'Page', 'wp-statistics' ),
		);

		// Rest-API Meta Box Url
		$list['meta_box_api']   = get_rest_url( null, RestApi::$namespace . '/metabox' );
		$list['rest_api_nonce'] = wp_create_nonce( 'wp_rest' );

		// Meta Box List
		$meta_boxes_list    = Meta_Box::getList();
		$list['meta_boxes'] = array();
		foreach ( $meta_boxes_list as $meta_box => $value ) {

			// Convert Page Url
			if ( isset( $value['page_url'] ) ) {
				$value['page_url'] = Menus::admin_url( $value['page_url'] );
			}

			// Add Post ID Params To Post Widget Link
			if ( $meta_box == "post" and isset( $post ) and in_array( $post->post_status, array( "publish", "private" ) ) ) {
				$value['page_url'] = add_query_arg( 'page-id', $post->ID, $value['page_url'] );
			}

			// Remove unnecessary params
			foreach ( array( 'show_on_dashboard', 'hidden', 'place', 'require', 'js', 'disable_overview' ) as $param ) {
				unset( $value[ $param ] );
			}

			// Add Meta Box Lang
			$class = Meta_Box::getMetaBoxClass( $meta_box );
			if ( method_exists( $class, 'lang' ) ) {
				$value['lang'] = $class::lang();
			}

			//Push to List
			$list['meta_boxes'][ $meta_box ] = $value;
		}

		return $list;
	}

}