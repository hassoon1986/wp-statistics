<?php

namespace WP_STATISTICS;

class Admin_Dashboard {
	/**
	 * User Meta Set Dashboard Option name
	 *
	 * @var string
	 */
	public static $dashboard_set = 'dashboard_set';

	/**
	 * Admin_Dashboard constructor.
	 */
	public function __construct() {

		//Register Dashboard Widget
		add_action( 'wp_dashboard_setup', array( $this, 'load_dashboard_widget' ) );
	}

	/**
	 * This function Register Wp-statistics Dashboard to wordpress Admin
	 */
	public function register_dashboard_widget() {

		foreach ( Meta_Box::_list() as $widget_key => $dashboard ) {
			if ( Option::check_option_require( $dashboard ) === true and isset( $dashboard['show_on_dashboard'] ) and $dashboard['show_on_dashboard'] === true ) {
				wp_add_dashboard_widget( Meta_Box::getMetaBoxKey( $widget_key ), $dashboard['name'], function(){ return null; }, $control_callback = null, array( 'widget' => $widget_key ) );
			}
		}
	}

	/**
	 * Load Dashboard Widget
	 * This Function add_action to `wp_dashboard_setup`
	 */
	public function load_dashboard_widget() {

		// If the user does not have at least read access to the status plugin, just return without adding the widgets.
		if ( ! current_user_can( wp_statistics_validate_capability( Option::get( 'read_capability', 'manage_option' ) ) ) ) {
			return;
		}

		// Check Hidden User Dashboard Option
		$user_dashboard = Option::getUserOption( self::$dashboard_set );
		if ( $user_dashboard === false || $user_dashboard != WP_STATISTICS_VERSION ) {
			self::set_user_hidden_dashboard_option();
		}

		// If the admin has disabled the widgets, don't display them.
		if ( ! Option::get( 'disable_dashboard' ) ) {
			$this->register_dashboard_widget();
		}
	}

	/**
	 * Set Default Hidden Dashboard User Option
	 */
	public static function set_user_hidden_dashboard_option() {

		//Get List Of Wp-statistics Dashboard Widget
		$dashboard_list = Meta_Box::_list();
		$hidden_opt     = 'metaboxhidden_dashboard';

		//Create Empty Option and save in User meta
		Option::update_user_option( self::$dashboard_set, WP_STATISTICS_VERSION );

		//Get Dashboard Option User Meta
		$hidden_widgets = get_user_meta( User::get_user_id(), $hidden_opt, true );
		if ( ! is_array( $hidden_widgets ) ) {
			$hidden_widgets = array();
		}

		//Set Default Hidden Dashboard in Admin Wordpress
		foreach ( $dashboard_list as $widget => $dashboard ) {
			if ( isset( $dashboard['hidden'] ) and $dashboard['hidden'] === true ) {
				$hidden_widgets[] = Meta_Box::getMetaBoxKey( $widget );
			}
		}

		update_user_meta( User::get_user_id(), $hidden_opt, $hidden_widgets );
	}
}