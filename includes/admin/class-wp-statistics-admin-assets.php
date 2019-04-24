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
	 * Check asset Suffix
	 */
	public static function suffix() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? array( 'suffix' => '', 'dir' => '' ) : array( 'suffix' => '.min', 'dir' => '/min/' );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {
		global $pagenow;

		$screen_id = Helper::get_screen_id();
		$suffix     = self::suffix();

		// Load Css Admin Area
		wp_enqueue_style( self::$prefix , WP_STATISTICS_URL . 'assets/css/admin.css', array(), WP_STATISTICS_VERSION );

		// Load rtl Version Css
		if ( is_rtl() ) {
			wp_enqueue_style( self::$prefix . '-rtl', WP_STATISTICS_URL . 'assets/css/rtl.css', array(), WP_STATISTICS_VERSION );
		}



		// Check in Dashboard
		if ( in_array( $screen_id, array( 'dashboard' ) ) ) {

		}


	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $pagenow;

		$screen_id = Helper::get_screen_id();
		$suffix     = self::suffix();

		//Load Admin Js
		wp_enqueue_script( 'wp-statistics-admin-js', WP_STATISTICS_URL . 'assets/js/admin.js', array( 'jquery' ), WP_STATISTICS_VERSION );


		if ( $pagenow == "widgets.php" ) {
			wp_enqueue_script( 'add_wp_statistic_button_for_widget_text', WP_STATISTICS_URL . 'assets/js/tinymce.js' );
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

		if ( $load_chart === true ) {
			wp_enqueue_script( 'wp-statistics-chart-js', WP_STATISTICS_URL . 'assets/js/Chart.bundle.min.js', false, '2.7.3', $load_in_footer );
		}

	}


}