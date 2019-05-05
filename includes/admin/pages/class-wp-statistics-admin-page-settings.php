<?php

namespace WP_STATISTICS;

class settings_page {

	public function __construct() {

		add_action( 'admin_init', array( $this, 'save' ) );
		add_action( 'admin_notices', array( $this, 'notice' ) );
	}

	/**
	 * This function displays the HTML for the settings page.
	 */
	public static function view() {

		// Check the current user has the rights to be here.
		if ( ! current_user_can( wp_statistics_validate_capability( Option::get( 'read_capability', 'manage_options' ) ) ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Check admin notices.
		if ( Option::get( 'admin_notices' ) == true ) {
			Option::update( 'disable_donation_nag', false );
			Option::update( 'disable_suggestion_nag', false );
		}

		// Check User Access To Save Setting
		$wps_admin = false;
		if ( current_user_can( wp_statistics_validate_capability( Option::get( 'manage_capability', 'manage_options' ) ) ) ) {
			$wps_admin = true;
		}
		if ( $wps_admin === false ) {
			$wps_admin = 0;
		}
		$selist                       = SearchEngine::getList( true );
		$permalink                    = get_option( 'permalink_structure' );
		$disable_strip_uri_parameters = false;
		if ( $permalink == '' || strpos( $permalink, '?' ) !== false ) {
			$disable_strip_uri_parameters = true;
		}
		$wp_statistics_options = Option::getOptions();

		include WP_STATISTICS_DIR . "includes/admin/templates/settings.php";
	}

	/**
	 * Save Setting
	 */
	public function save() {
		global $wpdb;

		// Save Settings
		if ( array_key_exists( 'wp-statistics-nonce', $_POST ) ) {
			if ( wp_verify_nonce( $_POST['wp-statistics-nonce'], 'update-options' ) ) {

				$wp_statistics_options = Option::getOptions();

				// General Option
				$selist                       = SearchEngine::getList( true );
				$permalink                    = get_option( 'permalink_structure' );
				$disable_strip_uri_parameters = false;

				if ( $permalink == '' || strpos( $permalink, '?' ) !== false ) {
					$disable_strip_uri_parameters = true;
				}
				foreach ( $selist as $se ) {
					$se_post = 'wps_disable_se_' . $se['tag'];

					if ( array_key_exists( $se_post, $_POST ) ) {
						$value = $_POST[ $se_post ];
					} else {
						$value = '';
					}
					$new_option                           = str_replace( "wps_", "", $se_post );
					$wp_statistics_options[ $new_option ] = $value;
				}

				$wps_option_list = array(
					'wps_useronline',
					'wps_visits',
					'wps_visitors',
					'wps_visitors_log',
					'wps_pages',
					'wps_track_all_pages',
					'wps_use_cache_plugin',
					'wps_disable_column',
					'wps_hit_post_metabox',
					'wps_show_hits',
					'wps_display_hits_position',
					'wps_check_online',
					'wps_menu_bar',
					'wps_coefficient',
					'wps_chart_totals',
					'wps_hide_notices',
					'wps_all_online',
					'wps_strip_uri_parameters',
					'wps_addsearchwords',
				);

				// We need to check the permalink format for the strip_uri_parameters option, if the permalink is the default or contains uri parameters, we can't strip them.
				if ( $disable_strip_uri_parameters ) {
					$_POST['wps_strip_uri_parameters'] = '';
				}

				foreach ( $wps_option_list as $option ) {
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$new_option                           = str_replace( "wps_", "", $option );
					$wp_statistics_options[ $new_option ] = $value;
				}

				//Add Visitor RelationShip Table
				if ( isset( $_POST['wps_visitors_log'] ) and $_POST['wps_visitors_log'] == 1 ) {
					Install::setup_visitor_relationship_table();
				}

				//Flush Rewrite Use Cache Plugin
				if ( isset( $_POST['wps_use_cache_plugin'] ) ) {
					flush_rewrite_rules();
				}

				// Prepare Visitor IP
				$value = 'REMOTE_ADDR';
				if ( isset( $_POST['ip_method'] ) and ! empty( $_POST['ip_method'] ) ) {

					// Check Custom Header
					if ( $_POST['ip_method'] == "CUSTOM_HEADER" ) {
						if ( trim( $_POST['user_custom_header_ip_method'] ) != "" ) {
							$value = $_POST['user_custom_header_ip_method'];
						}
					} else {
						$value = $_POST['ip_method'];
					}
				}

				$wp_statistics_options['ip_method'] = $value;


				// Prepare Access Level
				$wps_option_list = array_merge( $wps_option_list, array( 'wps_read_capability', 'wps_manage_capability' ) );
				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );

					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Exclusion List
				foreach ( User::get_role_list() as $role ) {
					$role_post = 'wps_exclude_' . str_replace( " ", "_", strtolower( $role ) );

					if ( array_key_exists( $role_post, $_POST ) ) {
						$value = $_POST[ $role_post ];
					} else {
						$value = '';
					}

					$new_option                           = str_replace( "wps_", "", $role_post );
					$wp_statistics_options[ $new_option ] = $value;
				}

				if ( array_key_exists( 'wps_create_honeypot', $_POST ) ) {
					$my_post                      = array(
						'post_type'    => 'page',
						'post_title'   => __( 'WP Statistics Honey Pot Page', 'wp-statistics' ) . ' [' . TimeZone::getCurrentDate() . ']',
						'post_content' => __( 'This is the Honey Pot for WP Statistics to use, do not delete.', 'wp-statistics' ),
						'post_status'  => 'publish',
						'post_author'  => 1,
					);
					$_POST['wps_honeypot_postid'] = wp_insert_post( $my_post );
				}

				$wps_option_list = array_merge(
					$wps_option_list,
					array(
						'wps_record_exclusions',
						'wps_robotlist',
						'wps_exclude_ip',
						'wps_exclude_loginpage',
						'wps_exclude_adminpage',
						'wps_force_robot_update',
						'wps_excluded_countries',
						'wps_included_countries',
						'wps_excluded_hosts',
						'wps_robot_threshold',
						'wps_use_honeypot',
						'wps_honeypot_postid',
						'wps_exclude_feeds',
						'wps_excluded_urls',
						'wps_exclude_404s',
						'wps_corrupt_browser_info',
						'wps_exclude_ajax',
					)
				);

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );

					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare External
				$wps_option_list = array(
					'wps_geoip',
					'wps_update_geoip',
					'wps_schedule_geoip',
					'wps_geoip_city',
					'wps_auto_pop',
					'wps_private_country_code',
					'wps_referrerspam',
					'wps_schedule_referrerspam'
				);

				// For country codes we always use upper case, otherwise default to 000 which is 'unknown'.
				if ( array_key_exists( 'wps_private_country_code', $_POST ) ) {
					$_POST['wps_private_country_code'] = trim( strtoupper( $_POST['wps_private_country_code'] ) );
				} else {
					$_POST['wps_private_country_code'] = GeoIP::$private_country;
				}

				if ( $_POST['wps_private_country_code'] == '' ) {
					$_POST['wps_private_country_code'] = GeoIP::$private_country;
				}

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}

				// Check Is Checked GEO-IP and Download
				foreach ( array( "geoip" => "country", "geoip_city" => "city" ) as $geo_opt => $geo_name ) {
					if ( ! isset( $_POST['update_geoip'] ) and isset( $_POST[ 'wps_' . $geo_opt ] ) ) {

						//Check File Not Exist
						$file = GeoIP::get_geo_ip_path( $geo_name );
						if ( ! file_exists( $file ) ) {
							$result = GeoIP::download( $geo_name );
							if ( isset( $result['status'] ) and $result['status'] === false ) {
								$wp_statistics_options[ $geo_opt ] = '';
							}
						}
					}
				}

				// Check Update Referrer Spam List
				if ( isset( $_POST['wps_referrerspam'] ) ) {
					$status = Referred::download_referrer_spam();
					if ( is_bool( $status ) and $status === false ) {
						$wp_statistics_options['referrerspam'] = '';
					}
				}

				// Prepare Maintenance
				$wps_option_list = array(
					'wps_schedule_dbmaint',
					'wps_schedule_dbmaint_days',
					'wps_schedule_dbmaint_visitor',
					'wps_schedule_dbmaint_visitor_hits',
				);

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( "wps_", "", $option );
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Notification

				if ( array_key_exists( 'wps_time_report', $_POST ) ) {
					// If the report has been changed, we need to update the schedule.
					if ( Option::get( 'time_report' ) != $_POST['wps_time_report'] ) {
						// Remove the old schedule if it exists.
						if ( wp_next_scheduled( 'report_hook' ) ) {
							wp_unschedule_event( wp_next_scheduled( 'report_hook' ), 'report_hook' );
						}

						// Setup the new schedule, we could just let this fall through and let the code in schedule.php deal with it
						// but that would require an extra page load to start the schedule so do it here instead.
						wp_schedule_event( time(), $_POST['wps_time_report'], 'report_hook' );
					}
				}

				$wps_option_list = array(
					"wps_stats_report",
					"wps_time_report",
					"wps_send_report",
					"wps_content_report",
					"wps_email_list",
					"wps_geoip_report",
					"wps_prune_report",
					"wps_upgrade_report",
					"wps_admin_notices",
				);

				foreach ( $wps_option_list as $option ) {
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}

					// WordPress escapes form data no matter what the setting of magic quotes is in PHP (http://www.theblog.ca/wordpress-addslashes-magic-quotes).
					$value = stripslashes( $value );

					$new_option                           = str_replace( "wps_", "", $option );
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare OverView Display
				$wps_option_list = array( 'wps_disable_map', 'wps_disable_dashboard', 'wps_disable_editor' );

				foreach ( $wps_option_list as $option ) {
					$new_option = str_replace( 'wps_', '', $option );

					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$wp_statistics_options[ $new_option ] = $value;
				}

				// Prepare Privacy
				$wps_option_list = array(
					'wps_anonymize_ips',
					'wps_hash_ips',
					'wps_store_ua',
					'wps_all_online',
				);

				// If the IP hash's are enabled, disable storing the complete user agent.
				if ( array_key_exists( 'wps_hash_ips', $_POST ) ) {
					$_POST['wps_store_ua'] = '';
				}

				foreach ( $wps_option_list as $option ) {
					if ( array_key_exists( $option, $_POST ) ) {
						$value = $_POST[ $option ];
					} else {
						$value = '';
					}
					$new_option                           = str_replace( "wps_", "", $option );
					$wp_statistics_options[ $new_option ] = $value;
				}


				// Prepare Removal
				if ( array_key_exists( 'wps_remove_plugin', $_POST ) ) {
					if ( is_super_admin() ) {
						update_option( 'wp_statistics_removal', 'true' );
						wp_redirect( admin_url( 'plugins.php' ) );
						exit;
					}
				}

				if ( array_key_exists( 'wps_reset_plugin', $_POST ) ) {

					$default_options   = Option::defaultOption();
					$excluded_defaults = array( 'force_robot_update', 'robot_list' );
					$again_options     = array();

					// Handle multi site implementations
					if ( is_multisite() ) {

						// Loop through each of the sites.
						$sites = Helper::get_wp_sites_list();
						foreach ( $sites as $blog_id ) {

							switch_to_blog( $blog_id );

							// Delete the wp_statistics option.
							update_option( Option::$opt_name, array() );

							// Delete the user options.
							$wpdb->query( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'" );

							// Set some intelligent defaults.
							foreach ( $default_options as $key => $value ) {
								if ( ! in_array( $key, $excluded_defaults ) ) {
									$again_options[ $key ] = $value;
								}
							}

							// Disable Show Welcome Page Again
							$again_options['first_show_welcome_page'] = true;
							$again_options['show_welcome_page']       = false;

							update_option( Option::$opt_name, $again_options );
						}

						restore_current_blog();
					} else {

						// Delete the wp_statistics option.
						update_option( Option::$opt_name, array() );

						// Delete the user options.
						$wpdb->query( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'" );

						// Set some intelligent defaults.
						foreach ( $default_options as $key => $value ) {
							if ( ! in_array( $key, $excluded_defaults ) ) {
								$again_options[ $key ] = $value;
							}
						}

						// Disable Show Welcome Page Again
						$again_options['first_show_welcome_page'] = true;
						$again_options['show_welcome_page']       = false;

						update_option( Option::$opt_name, $again_options );
					}

					wp_redirect( add_query_arg( array( 'reset_settings' => 'yes' ), Menus::admin_url( 'settings' ) ) );
					exit;
				}

				Option::save_options( $wp_statistics_options );
				wp_redirect( add_query_arg( array( 'save_setting' => 'yes' ), Menus::admin_url( 'settings' ) ) );
				exit;

			}
		}

	}

	/**
	 * Admin Notice
	 */
	public function notice() {

		// Update Referrer Spam
		if ( isset( $_GET['update-referrer-spam'] ) ) {
			$status = Referred::download_referrer_spam();
			if ( is_bool( $status ) ) {
				if ( $status === false ) {
					Helper::wp_admin_notice( __( "Error Updating Referrer Spam Blacklist.", "wp-statistics" ), "error" );
				} else {
					Helper::wp_admin_notice( __( "Updated Referrer Spam Blacklist.", "wp-statistics" ), "success" );
				}
				return;
			}
		}

		// Update GEO IP
		if ( Option::get( 'geoip' ) and isset( $_POST['update_geoip'] ) and isset( $_POST['geoip_name'] ) ) {

			//Check Geo ip Exist in Database
			if ( isset( GeoIP::$library[ $_POST['geoip_name'] ] ) ) {
				$result = GeoIP::download( $_POST['geoip_name'], "update" );
				if ( is_array( $result ) and isset( $result['status'] ) ) {
					Helper::wp_admin_notice( $result['notice'], ( $result['status'] === false ? "error" : "success" ) );
					return;
				}
			}
		}

		// Save Setting
		if ( isset( $_GET['save_setting'] ) ) {
			Helper::wp_admin_notice( __( "Saved Settings.", "wp-statistics" ), "success" );
		}

		// Reset Setting
		if ( isset( $_GET['reset_settings'] ) ) {
			Helper::wp_admin_notice( __( "All settings reset.", "wp-statistics" ), "success" );
		}

	}
}