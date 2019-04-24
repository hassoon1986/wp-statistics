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
				return WP_STATISTICS_VERSION;
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
		$path = 'assets/' . $ext . '/';

		// Prepare Full Url
		$url = WP_STATISTICS_URL . $path;

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

		$screen_id = Helper::get_screen_id();

		// Load Css Admin Area
		wp_enqueue_style( self::$prefix, self::url( 'admin.css' ), array(), self::version() );

		// Load Rtl Version Css
		if ( is_rtl() ) {
			wp_enqueue_style( self::$prefix . '-rtl', self::url( 'rtl.css' ), array(), self::version() );
		}

		// Load Pagination and Print Css only in Plugins Pages
		if ( Admin_Menus::in_plugin_page() ) {
			wp_enqueue_style( self::$prefix . '-print', self::url( 'print.css' ), array(), self::version() );
			wp_enqueue_style( self::$prefix . '-pagination', self::url( 'pagination.css' ), array(), self::version() );
		}


	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $pagenow;

		$screen_id = Helper::get_screen_id();

		// Load Chart Js Library
		$_is_load_chart_js = self::is_load_chart_js();
		if ( $_is_load_chart_js['status'] ) {
			wp_enqueue_script( self::$prefix . '-chart.js', self::url( 'chart.bundle.js' ), false, '2.8.0', $_is_load_chart_js['footer'] );
		}

		// Load Admin Js
		wp_enqueue_script( self::$prefix, self::url( 'admin.js' ), array( 'jquery' ), self::version() );


		// Load Tiny MCE for Widget Page
		if ( in_array( $screen_id, array( 'widgets' ) ) ) {
			wp_enqueue_script( self::$prefix . '-button-widget', self::url( 'tinymce.js' ), array( 'jquery' ), self::version() );
		}

		// Load Admin Dashboard Script
		if ( in_array( $screen_id, array( 'dashboard' ) ) and ! Option::get( 'disable_dashboard' ) ) {
			wp_enqueue_script( self::$prefix . '-dashboard', self::url( 'dashboard.js' ), array( 'jquery' ), self::version() );
		}

		// Load Editors Script
		if ( in_array( $screen_id, array( 'post', 'page' ) ) ) {
			wp_enqueue_script( self::$prefix . '-editor', self::url( 'editor.js' ), array( 'jquery' ), self::version() );
		}


		//Load Chart Js
		$load_in_footer = false;
		$load_chart     = false;

		//Load in Setting Page
		$pages_required_chart = array(
			'wps_overview_page',
			'wps_browsers_page',
			'wps_hits_page',
			'wps_pages_page',
			'wps_categories_page',
			'wps_tags_page',
			'wps_authors_page',
			'wps_searches_page',
		);
		if ( isset( $_GET['page'] ) and array_search( $_GET['page'], $pages_required_chart ) !== false ) {
			$load_chart = true;
		}

		//Load in Post Page
		if ( $pagenow == "post.php" and Option::get( 'hit_post_metabox' ) ) {
			$load_chart = true;
		}

	}

	/**
	 * Check Load Chart Js Library
	 */
	public static function is_load_chart_js() {
		$status = array( 'status' => false, 'footer' => false );


		return $status;
	}


}