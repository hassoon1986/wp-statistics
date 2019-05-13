<?php

namespace WP_STATISTICS;

class Install {

	public function __construct() {

	    // Create or Remove WordPress DB Table in Multi Site
		add_action( 'wpmu_new_blog', array( $this, 'add_table_on_create_blog' ), 10, 1 );
		add_filter( 'wpmu_drop_tables', array( $this, 'remove_table_on_delete_blog' ) );

		// Page Type Updater @since 12.6
		Install::init_page_type_updater();
	}

	/**
	 * Install
	 *
	 * @param $network_wide
	 */
	public function install( $network_wide ) {

		// Check installed plugin version
		$installed_version = get_option( 'wp_statistics_plugin_version' );
		if ( $installed_version == WP_STATISTICS_VERSION ) {
			return;
		}

		// Create MySQL Table
		self::create_table( $network_wide );

		// Create Default Option in Database
		self::create_options();

		// Store the new version information.
		update_option( 'wp_statistics_plugin_version', WP_STATISTICS_VERSION );
		update_option( 'wp_statistics_db_version', WP_STATISTICS_VERSION );
	}

	/**
	 * Adding new MYSQL Table in Activation Plugin
	 */
	public static function create_table( $network_wide ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {

				switch_to_blog( $blog_id );
				self::table_sql();
				restore_current_blog();

			}
		} else {
			self::table_sql();
		}
	}

	/**
	 * Create Database Table
	 */
	public static function table_sql() {
		global $wpdb;

		// Load dbDelta WordPress
		self::load_dbDelta();

		// Users Online Table
		$create_user_online_table = ( "
					CREATE TABLE " . DB::table( 'useronline' ) . " (
						ID int(11) NOT NULL AUTO_INCREMENT,
	  					ip varchar(60) NOT NULL,
						created int(11),
						timestamp int(10) NOT NULL,
						date datetime NOT NULL,
						referred text CHARACTER SET utf8 NOT NULL,
						agent varchar(255) NOT NULL,
						platform varchar(255),
						version varchar(255),
						location varchar(10),
						PRIMARY KEY  (ID)
					) CHARSET=utf8" );
		dbDelta( $create_user_online_table );

		// Added User_id and Page_id in user online table
		$result = $wpdb->query( "SHOW COLUMNS FROM " . DB::table( 'useronline' ) . " LIKE 'user_id'" );
		if ( $result == 0 ) {
			$wpdb->query( "ALTER TABLE `" . DB::table( 'useronline' ) . "` ADD `user_id` BIGINT(48) NOT NULL AFTER `location`, ADD `page_id` BIGINT(48) NOT NULL AFTER `user_id`, ADD `type` VARCHAR(100) NOT NULL AFTER `page_id`;" );
		}

		// Visit Table
		$create_visit_table = ( "
					CREATE TABLE " . DB::table( 'visit' ) . " (
						ID int(11) NOT NULL AUTO_INCREMENT,
						last_visit datetime NOT NULL,
						last_counter date NOT NULL,
						visit int(10) NOT NULL,
						PRIMARY KEY  (ID),
						UNIQUE KEY unique_date (last_counter)
					) CHARSET=utf8" );
		dbDelta( $create_visit_table );

		// Visitor Table
		$create_visitor_table = ( "
					CREATE TABLE " . DB::table( 'visitor' ) . " (
						ID int(11) NOT NULL AUTO_INCREMENT,
						last_counter date NOT NULL,
						referred text NOT NULL,
						agent varchar(255) NOT NULL,
						platform varchar(255),
						version varchar(255),
						UAString varchar(255),
						ip varchar(60) NOT NULL,
						location varchar(10),
						hits int(11),
						honeypot int(11),
						PRIMARY KEY  (ID),
						UNIQUE KEY date_ip_agent (last_counter,ip,agent(75),platform(75),version(75)),
						KEY agent (agent),
						KEY platform (platform),
						KEY version (version),
						KEY location (location)
					) CHARSET=utf8" );
		dbDelta( $create_visitor_table );

		// Check if the date_ip index still exists, then removed.
		$result = $wpdb->query( "SHOW INDEX FROM " . DB::table( 'visitor' ) . " WHERE Key_name = 'date_ip'" );
		if ( $result > 1 ) {
			$wpdb->query( "DROP INDEX `date_ip` ON " . DB::table( 'table' ) );
		}

		// drop the 'AString' column from visitors if it exists.
		$result = $wpdb->query( "SHOW COLUMNS FROM " . DB::table( 'visitor' ) . " LIKE 'AString'" );
		if ( $result > 0 ) {
			$wpdb->query( "ALTER TABLE `" . DB::table( 'visitor' ) . "` DROP `AString`" );
		}

		// Exclusion Table
		$create_exclusion_table = ( "
					CREATE TABLE " . DB::table( 'exclusions' ) . " (
						ID int(11) NOT NULL AUTO_INCREMENT,
						date date NOT NULL,
						reason varchar(255) DEFAULT NULL,
						count bigint(20) NOT NULL,
						PRIMARY KEY  (ID),
						KEY date (date),
						KEY reason (reason)
					) CHARSET=utf8" );
		dbDelta( $create_exclusion_table );

		// Pages Table
		$create_pages_table = ( "
					CREATE TABLE " . DB::table( 'pages' ) . " (
						uri varchar(255) NOT NULL,
						type varchar(255) NOT NULL,
						date date NOT NULL,
						count int(11) NOT NULL,
						id int(11) NOT NULL,
						UNIQUE KEY date_2 (date,uri),
						KEY url (uri),
						KEY date (date),
						KEY id (id),
						KEY `uri` (`uri`,`count`,`id`)
					) CHARSET=utf8" );
		dbDelta( $create_pages_table );

		//Added page_id column in statistics_pages if not exist
		$result = $wpdb->query( "SHOW COLUMNS FROM " . DB::table( 'pages' ) . " LIKE 'page_id'" );
		if ( $result == 0 ) {
			$wpdb->query( "ALTER TABLE `" . DB::table( 'pages' ) . "` ADD `page_id` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`page_id`);" );
		}

		// Historical Table
		$create_historical_table = ( "
					CREATE TABLE " . DB::table( 'historical' ) . " (
						ID bigint(20) NOT NULL AUTO_INCREMENT,
						category varchar(25) NOT NULL,
						page_id bigint(20) NOT NULL,
						uri varchar(255) NOT NULL,
						value bigint(20) NOT NULL,
						PRIMARY KEY  (ID),
						KEY category (category),
						UNIQUE KEY page_id (page_id),
						UNIQUE KEY uri (uri)
					) CHARSET=utf8" );
		dbDelta( $create_historical_table );

		// Search Table
		$create_search_table = ( "
					CREATE TABLE " . DB::table( 'search' ) . " (
						ID bigint(20) NOT NULL AUTO_INCREMENT,
						last_counter date NOT NULL,
						engine varchar(64) NOT NULL,
						host varchar(255),
						words varchar(255),
						visitor bigint(20),
						PRIMARY KEY  (ID),
						KEY last_counter (last_counter),
						KEY engine (engine),
						KEY host (host)
					) CHARSET=utf8" );
		dbDelta( $create_search_table );
	}

	/**
	 * Setup Visitor RelationShip Table
	 */
	public static function create_visitor_relationship_table() {

		// Load WordPress DBDelta
		self::load_dbDelta();

		// Get Table name
		$table_name = DB::table( 'visitor_relationships' );

		// if not Found then Create Table
		if ( DB::ExistTable( $table_name ) === false ) {

			$create_visitor_relationships_table =
				"CREATE TABLE IF NOT EXISTS $table_name (
				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
				`visitor_id` bigint(20) NOT NULL,
				`page_id` bigint(20) NOT NULL,
				`date` datetime NOT NULL,
				PRIMARY KEY  (ID),
				KEY visitor_id (visitor_id),
				KEY page_id (page_id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";

			dbDelta( $create_visitor_relationships_table );
		}
	}

	/**
	 * Load WordPress dbDelta Function
	 */
	public static function load_dbDelta() {
		if ( ! function_exists( 'dbDelta' ) ) {
			require( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}
	}

	/**
	 * Create Default Option
	 */
	public static function create_options() {
		global $wpdb;

		// Get List Default
		$default_options = Option::defaultOption();
		$store_options   = Option::getOptions();

		// If this is an upgrade, we need to check to see if we need to convert anything from old to new formats.
		// Check to see if the "new" settings code is in place or not, if not, upgrade the old settings to the new system.
		if ( get_option( Option::$opt_name ) === false ) {

			$core_options   = array(
				'wps_disable_map',
				'wps_map_location',
				'wps_google_coordinates',
				'wps_schedule_dbmaint',
				'wps_schedule_dbmaint_days',
				'wps_geoip',
				'wps_update_geoip',
				'wps_schedule_geoip',
				'wps_last_geoip_dl',
				'wps_auto_pop',
				'wps_useronline',
				'wps_check_online',
				'wps_visits',
				'wps_visitors',
				'wps_visitors_log',
				'wps_store_ua',
				'wps_coefficient',
				'wps_pages',
				'wps_track_all_pages',
				'wps_use_cache_plugin',
				'wps_geoip_city',
				'wps_disable_column',
				'wps_hit_post_metabox',
				'wps_menu_bar',
				'wps_hide_notices',
				'wps_chart_totals',
				'wps_stats_report',
				'wps_time_report',
				'wps_send_report',
				'wps_content_report',
				'wps_read_capability',
				'wps_manage_capability',
				'wps_record_exclusions',
				'wps_robotlist',
				'wps_exclude_ip',
				'wps_exclude_loginpage',
				'wps_exclude_adminpage',
			);
			$var_options    = array( 'wps_disable_se_%', 'wps_exclude_%' );
			$widget_options = array(
				'name_widget',
				'useronline_widget',
				'tvisit_widget',
				'tvisitor_widget',
				'yvisit_widget',
				'yvisitor_widget',
				'wvisit_widget',
				'mvisit_widget',
				'ysvisit_widget',
				'ttvisit_widget',
				'ttvisitor_widget',
				'tpviews_widget',
				'ser_widget',
				'select_se',
				'tp_widget',
				'tpg_widget',
				'tc_widget',
				'ts_widget',
				'tu_widget',
				'ap_widget',
				'ac_widget',
				'au_widget',
				'lpd_widget',
				'select_lps',
			);

			// Handle the core options, we're going to strip off the 'wps_' header as we store them in the new settings array.
			foreach ( $core_options as $option ) {
				$new_name                   = substr( $option, 4 );
				$store_options[ $new_name ] = get_option( $option );
				delete_option( $option );
			}

			$widget = array();
			foreach ( $widget_options as $option ) {
				$widget[ $option ] = get_option( $option );
				delete_option( $option );
			}
			$store_options['widget'] = $widget;


			foreach ( $var_options as $option ) {

				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE '{$option}'" );
				foreach ( $result as $opt ) {
					$new_name                   = substr( $opt->option_name, 4 );
					$store_options[ $new_name ] = $opt->option_value;
					delete_option( $opt->option_name );
				}
			}

			Option::save_options( $store_options );
		}

		// Get Robot List if Empty
		$wps_temp_robots_list = Option::get( 'robotlist' );
		if ( trim( $wps_temp_robots_list ) == "" || Option::get( 'force_robot_update' ) == true ) {
			Option::update( 'robotlist', $default_options['robotlist'] );
		}

		// We've already handled some of the default or need to do more logic checks on them so create a list to exclude from the next loop.
		$excluded_defaults = array( 'force_robot_update', 'robot_list' );
		foreach ( $default_options as $key => $value ) {
			if ( ! in_array( $key, $excluded_defaults ) && false === Option::get( $key ) ) {
				$store_options[ $key ] = $value;
			}
		}
		Option::save_options( $store_options );

		// Update Send Upgrade
		if ( Option::get( 'upgrade_report' ) == true ) {
			Option::update( 'send_upgrade_email', true );
		}
	}

	/**
	 * Creating Table for New Blog in wordpress
	 *
	 * @param $blog_id
	 */
	public function add_table_on_create_blog( $blog_id ) {
		if ( is_plugin_active_for_network( 'wp-statistics/wp-statistics.php' ) ) {
			switch_to_blog( $blog_id );
			self::table_sql();
			restore_current_blog();
		}
	}

	/**
	 * Remove Table On Delete Blog Wordpress
	 *
	 * @param $tables
	 * @return array
	 */
	public function remove_table_on_delete_blog( $tables ) {
		$tables[] = array_merge( $tables, DB::table( 'all' ) );
		return $tables;
	}

	/**
	 * Update WordPress Page Type for older wp-statistics Version
	 *
	 * @since 12.6
	 *
	 * -- List Methods ---
	 * init_page_type_updater        -> define WordPress Hook
	 * get_require_number_update     -> Get number of rows that require update page type
	 * is_require_update_page        -> Check Wp-statistics require update page table
	 * get_page_type_by_obj          -> Get Page Type by information
	 */
	public static function init_page_type_updater() {

		# Check Require Admin Process
		if ( self::is_require_update_page() === true ) {

			# Add Admin Notice
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-info is-dismissible" id="wp-statistics-update-page-area" style="display: none;">';
				echo '<p style="margin-top: 17px; float:' . ( is_rtl() ? 'right' : 'left' ) . '">';
				echo __( 'WP-Statistics database requires upgrade.', 'wp-statistics' );
				echo '</p>';
				echo '<div style="float:' . ( is_rtl() ? 'left' : 'right' ) . '">';
				echo '<button type="button" id="wps-upgrade-db" class="button button-primary" style="padding: 20px;line-height: 0px;box-shadow: none !important;border: 0px !important;margin: 10px 0;"/>' . __( 'Upgrade Database', 'wp-statistics' ) . '</button>';
				echo '</div>';
				echo '<div style="clear:both;"></div>';
				echo '</div>';
			} );

			# Add Script
			add_action( 'admin_footer', function () {
				?>
                <script>
                    jQuery(document).ready(function () {

                        // Check Page is complete Loaded
                        jQuery(window).load(function () {
                            jQuery("#wp-statistics-update-page-area").fadeIn(2000);
                            jQuery("#wp-statistics-update-page-area button.notice-dismiss").hide();
                        });

                        // Update Page type function
                        function wp_statistics_update_page_type() {

                            //Complete Progress
                            let wps_end_progress = `<div id="wps_end_process" style="display:none;">`;
                            wps_end_progress += `<p>`;
                            wps_end_progress += `<?php _e( 'Database upgrade operation completed!', 'wp-statistics' ); ?>`;
                            wps_end_progress += `</p>`;
                            wps_end_progress += `</div>`;
                            wps_end_progress += `<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>`;

                            //new Ajax Request
                            jQuery.ajax({
                                url: ajaxurl,
                                type: 'get',
                                dataType: "json",
                                cache: false,
                                data: {
                                    'action': 'wp_statistics_update_post_type_db',
                                    'number_all': <?php echo self::get_require_number_update(); ?>
                                },
                                success: function (data) {
                                    if (data.process_status === "complete") {

                                        // Get Process Area
                                        let wps_notice_area = jQuery("#wp-statistics-update-page-area");
                                        //Add Html Content
                                        wps_notice_area.html(wps_end_progress);
                                        //Fade in content
                                        jQuery("#wps_end_process").fadeIn(2000);
                                        //enable demiss button
                                        wps_notice_area.removeClass('notice-info').addClass('notice-success');
                                    } else {

                                        //Get number Process
                                        jQuery("span#wps_num_page_process").html(data.number_process);
                                        //Get process Percentage
                                        jQuery("progress#wps_upgrade_html_progress").attr("value", data.percentage);
                                        jQuery("span#wps_num_percentage").html(data.percentage);
                                        //again request
                                        wp_statistics_update_page_type();
                                    }
                                },
                                error: function () {
                                    jQuery("#wp-statistics-update-page-area").html('<p><?php _e( 'Error occurred during operation. Please refresh the page.', 'wp-statistics' ); ?></p>');
                                }
                            });
                        }

                        //Click Start Progress
                        jQuery(document).on('click', 'button#wps-upgrade-db', function (e) {
                            e.preventDefault();

                            // Added Progress Html
                            let wps_progress = `<div id="wps_process_upgrade" style="display:none;"><p>`;
                            wps_progress += `<?php _e( 'Please don\'t close the browser window until the database operation was completed.', 'wp-statistic' ); ?>`;
                            wps_progress += `</p><p><b>`;
                            wps_progress += `<?php echo __( 'Item processed', 'wp-statistics' ); ?>`;
                            wps_progress += ` : <span id="wps_num_page_process">0</span> / <?php echo number_format( self::get_require_number_update() ); ?> &nbsp;<span class="wps-text-warning">(<span id="wps_num_percentage">0</span>%)</span></b></p>`;
                            wps_progress += '<p><progress id="wps_upgrade_html_progress" value="0" max="100" style="height: 20px;width: 100%;"></progress></p></div>';

                            // set new Content
                            jQuery("#wp-statistics-update-page-area").html(wps_progress);
                            jQuery("#wps_process_upgrade").fadeIn(2000);

                            // Run WordPress Ajax Updator
                            wp_statistics_update_page_type();
                        });

                        //Remove Notice event
                        jQuery(document).on('click', '#wp-statistics-update-page-area button.notice-dismiss', function (e) {
                            e.preventDefault();
                            jQuery("#wp-statistics-update-page-area").fadeOut('normal');
                        });
                    });
                </script>
				<?php
			} );

		}

		# Add Admin Ajax Process
		add_action( 'wp_ajax_wp_statistics_update_post_type_db', function () {
			global $wpdb;

			# Create Default Obj
			$return = array( 'process_status' => 'complete', 'number_process' => 0, 'percentage' => 0 );

			# Check is Ajax WordPress
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

				# Check Status Of Process
				if ( self::is_require_update_page() === true ) {

					# Number Process Per Query
					$number_per_query = 80;

					# Check Number Process
					$number_process = self::get_require_number_update();
					$i              = 0;
					if ( $number_process > 0 ) {

						# Start Query
						$query = $wpdb->get_results( "SELECT * FROM `" . DB::table( 'pages' ) . "` WHERE `type` = '' ORDER BY `page_id` DESC LIMIT 0,{$number_per_query}", ARRAY_A );
						foreach ( $query as $row ) {

							# Get Page Type
							$page_type = self::get_page_type_by_obj( $row['id'], $row['uri'] );

							# Update Table
							$wpdb->update(
								DB::table( 'pages' ),
								array(
									'type' => $page_type
								),
								array( 'page_id' => $row['page_id'] )
							);

							$i ++;
						}

						if ( $_GET['number_all'] > $number_per_query ) {
							# calculate number process
							$return['number_process'] = $_GET['number_all'] - ( $number_process - $i );

							# Calculate Per
							$return['percentage'] = round( ( $return['number_process'] / $_GET['number_all'] ) * 100 );

							# Set Process
							$return['process_status'] = 'incomplete';

						} else {

							$return['number_process'] = $_GET['number_all'];
							$return['percentage']     = 100;
							update_option( 'wp_statistics_update_page_type', 'yes' );
						}
					}
				} else {

					# Closed Process
					update_option( 'wp_statistics_update_page_type', 'yes' );
				}

				# Export Data
				wp_send_json( $return );
				exit;
			}
		} );


	}

	public static function get_require_number_update() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM `" . DB::table( 'pages' ) . "` WHERE `type` = ''" );
	}

	public static function is_require_update_page() {

		# require update option name
		$opt_name = 'wp_statistics_update_page_type';

		# Check exist option
		$get_opt = get_option( $opt_name );
		if ( ! empty( $get_opt ) ) {
			return false;
		}

		# Check number require row
		if ( self::get_require_number_update() > 0 ) {
			return true;
		}

		return false;
	}

	public static function get_page_type_by_obj( $obj_ID, $page_url ) {

		//Default page type
		$page_type = 'unknown';

		//check if Home Page
		if ( $page_url == "/" ) {
			return 'home';

		} else {

			// Page url
			$page_url = ltrim( $page_url, "/" );
			$page_url = trim( get_bloginfo( 'url' ), "/" ) . "/" . $page_url;

			// Check Page Path is exist
			$exist_page = url_to_postid( $page_url );

			//Check Post Exist
			if ( $exist_page > 0 ) {

				# Get Post Type
				$p_type = get_post_type( $exist_page );

				# Check Post Type
				if ( $p_type == "product" ) {
					$page_type = 'product';
				} elseif ( $p_type == "page" ) {
					$page_type = 'page';
				} elseif ( $p_type == "attachment" ) {
					$page_type = 'attachment';
				} else {
					$page_type = 'post';
				}

			} else {

				# Check is Term
				$term = get_term( $obj_ID );
				if ( is_wp_error( get_term_link( $term ) ) === true ) {
					//Don't Stuff
				} else {
					//Which Taxonomy
					$taxonomy = $term->taxonomy;

					//Check Url is contain
					$term_link = get_term_link( $term );
					$term_link = ltrim( str_ireplace( get_bloginfo( 'url' ), "", $term_link ), "/" );
					if ( stristr( $page_url, $term_link ) === false ) {
						//Return Unknown
					} else {
						//Check Type of taxonomy
						if ( $taxonomy == "category" ) {
							$page_type = 'category';
						} elseif ( $taxonomy == "post_tag" ) {
							$page_type = 'post_tag';
						} else {
							$page_type = 'tax';
						}
					}

				}
			}
		}

		return $page_type;
	}
}

new Install();