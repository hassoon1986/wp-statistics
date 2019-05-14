<?php

namespace WP_STATISTICS;

class Option {
	/**
	 * Get WP-Statistics Basic Option name
	 *
	 * @var string
	 */
	public static $opt_name = 'wp_statistics';

	/**
	 * WP-Statistics Option name Prefix
	 *
	 * @var string
	 */
	public static $opt_prefix = 'wps_';

	/**
	 * Get Complete Option name with WP-Statistics Prefix
	 *
	 * @param $name
	 * @return mixed
	 */
	public static function get_option_name( $name ) {
		return self::$opt_prefix . $name;
	}

	/**
	 * WP-Statistics Default Option
	 *
	 * @param null $option_name
	 * @return array
	 */
	public static function defaultOption( $option_name = null ) {

		$options                          = array();
		$options['robotlist']             = Helper::get_robots_list();
		$options['anonymize_ips']         = false;
		$options['geoip']                 = false;
		$options['useronline']            = true;
		$options['visits']                = true;
		$options['visitors']              = true;
		$options['pages']                 = true;
		$options['check_online']          = UserOnline::$reset_user_time;
		$options['menu_bar']              = false;
		$options['coefficient']           = Visitor::$coefficient;
		$options['stats_report']          = false;
		$options['time_report']           = 'daily';
		$options['send_report']           = 'mail';
		$options['content_report']        = '';
		$options['update_geoip']          = true;
		$options['store_ua']              = false;
		$options['exclude_administrator'] = true;
		$options['disable_se_clearch']    = true;
		$options['disable_se_qwant']      = true;
		$options['disable_se_baidu']      = true;
		$options['disable_se_ask']        = true;
		$options['map_type']              = 'jqvmap';
		$options['force_robot_update']    = true;
		$options['ip_method']             = 'REMOTE_ADDR';

		if ( $option_name and isset( $options[ $option_name ] ) ) {
			return $options[ $option_name ];
		}

		return $options;
	}

	/**
	 * Get WP-Statistics All Options
	 *
	 * @return mixed
	 */
	public static function getOptions() {
		$get_opt = get_option( self::$opt_name );
		if ( ! isset( $get_opt ) || ! is_array( $get_opt ) ) {
			return array();
		}

		return $get_opt;
	}

	/**
	 * Saves the current options array to the database.
	 *
	 * @param $options
	 */
	public static function save_options( $options ) {
		update_option( self::$opt_name, $options );
	}

	/**
	 * Get the only Option that we want
	 *
	 * @param $option_name
	 * @param null $default
	 * @return string
	 */
	public static function get( $option_name, $default = null ) {

		// Get all Options
		$options = self::getOptions();

		// if the option isn't set yet, return the $default if it exists, otherwise FALSE.
		if ( ! array_key_exists( $option_name, $options ) ) {
			if ( isset( $default ) ) {
				return $default;
			} else {
				return false;
			}
		}

		/**
		 * Filters a For Return WP-Statistics Option
		 *
		 * @param string $option Option name.
		 * @param string $value Option Value.
		 * @example add_filter('wp_statistics_option_coefficient', function(){ return 5; });
		 */
		return apply_filters( "wp_statistics_option_{$option_name}", $options[ $option_name ] );
	}

	/**
	 * Update Wp-Statistics Option
	 *
	 * @param $option
	 * @param $value
	 */
	public static function update( $option, $value ) {

		// Get All Option
		$options = self::getOptions();

		// Store the value in the array.
		$options[ $option ] = $value;

		// Write the array to the database.
		update_option( self::$opt_name, $options );
	}

	/**
	 * Get WP-Statistics User Meta
	 *
	 * @param      $option
	 * @param null $default
	 * @return bool|null
	 */
	public static function getUserOption( $option, $default = null ) {

		// If the user id has not been set return FALSE.
		if ( User::get_user_id() == 0 ) {
			return false;
		}

		// Check User Exist
		$user_options = get_user_meta( User::get_user_id(), self::$opt_name, true );
		$user_options = ( is_array( $user_options ) ? $user_options : array() );

		// if the option isn't set yet, return the $default if it exists, otherwise FALSE.
		if ( isset( $user_options ) and ! array_key_exists( $option, $user_options ) ) {
			if ( isset( $default ) ) {
				return $default;
			} else {
				return false;
			}
		}

		// Return the option.
		return ( isset( $user_options[ $option ] ) ? $user_options[ $option ] : false );
	}

	/**
	 * Mimics WordPress's update_user_meta() function
	 * But uses the array instead of individual options.
	 *
	 * @param $option
	 * @param $value
	 *
	 * @return bool
	 */
	public static function update_user_option( $option, $value ) {
		// If the user id has not been set return FALSE.
		if ( User::get_user_id() == 0 ) {
			return false;
		}

		// Get All User Options
		$user_options = get_user_meta( User::get_user_id(), self::$opt_name, true );

		// Store the value in the array.
		$user_options[ $option ] = $value;

		// Write the array to the database.
		update_user_meta( User::get_user_id(), self::$opt_name, $user_options );
	}

	/**
	 * Check WP-statistics Option Require
	 *
	 * @param array $item
	 * @param string $condition_key
	 * @return array|bool
	 */
	public static function check_option_require( $item = array(), $condition_key = 'require' ) {

		// Default is True
		$condition = true;

		// Check Require Params
		if ( array_key_exists( 'require', $item ) ) {
			foreach ( $item[ $condition_key ] as $if => $value ) {

				// Check Type of Condition
				if ( ( $value === true and ! Option::get( $if ) ) || ( $value === false and Option::get( $if ) ) ) {
					$condition = false;
					break;
				}
			}
		}

		return $condition;
	}

	/**
	 * Get Email For Send notification
	 *
	 * @return string
	 */
	public static function getEmailNotification() {
		if ( Option::get( 'email_list' ) == '' ) {
			Option::update( 'email_list', get_bloginfo( 'admin_email' ) );
		}

		return Option::get( 'email_list' );
	}

}