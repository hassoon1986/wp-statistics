<?php

namespace WP_STATISTICS;

class Admin_Pages {

	/**
	 * Load Overview Page
	 */
	public static function overview() {

		// Right side "wide" widgets
		if ( Option::get( 'visits' ) ) {
			add_meta_box(
				'wps_hits_postbox',
				__( 'Hit Statistics', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'hits' )
			);
		}

		if ( Option::get( 'visitors' ) ) {
			add_meta_box(
				'wps_top_visitors_postbox',
				__( 'Top Visitors', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'top.visitors' )
			);
			add_meta_box(
				'wps_search_postbox',
				__( 'Search Engine Referrals', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'search' )
			);
			add_meta_box(
				'wps_words_postbox',
				__( 'Latest Search Words', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'words' )
			);
			add_meta_box(
				'wps_recent_postbox',
				__( 'Recent Visitors', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'recent' )
			);

			if ( Option::get( 'geoip' ) ) {
				add_meta_box(
					'wps_map_postbox',
					__( 'Today\'s Visitors Map', 'wp-statistics' ),
					'wp_statistics_generate_overview_postbox_contents',
					Menus::get_action_menu_slug( 'overview' ),
					'normal',
					null,
					array( 'widget' => 'map' )
				);
			}
		}

		if ( Option::get( 'pages' ) ) {
			add_meta_box(
				'wps_pages_postbox',
				__( 'Top 10 Pages', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'normal',
				null,
				array( 'widget' => 'pages' )
			);
		}

		// Left side "thin" widgets.
		if ( Option::get( 'visitors' ) ) {
			add_meta_box(
				'wps_summary_postbox',
				__( 'Summary', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'side',
				null,
				array( 'widget' => 'summary' )
			);
			add_meta_box(
				'wps_browsers_postbox',
				__( 'Browsers', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'side',
				null,
				array( 'widget' => 'browsers' )
			);
			add_meta_box(
				'wps_referring_postbox',
				__( 'Top Referring Sites', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				Menus::get_action_menu_slug( 'overview' ),
				'side',
				null,
				array( 'widget' => 'referring' )
			);

			if ( Option::get( 'geoip' ) ) {
				add_meta_box(
					'wps_countries_postbox',
					__( 'Top 10 Countries', 'wp-statistics' ),
					'wp_statistics_generate_overview_postbox_contents',
					Menus::get_action_menu_slug( 'overview' ),
					'side',
					null,
					array( 'widget' => 'countries' )
				);
			}
		}

		//Left Show User online table
		if ( Option::get( 'useronline' ) ) {
			add_meta_box( 'wps_users_online_postbox', __( 'Online Users', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', Menus::get_action_menu_slug( 'overview' ), 'side', null, array( 'widget' => 'users_online' ) );
		}
	}

	/**
	 * @param string $log_type Log Type
	 */
	public static function log( $log_type = "" ) {
		global $wpdb, $plugin_page;

		switch ( $plugin_page ) {
			case Menus::get_page_slug( 'browser' ):
				$log_type = 'all-browsers';

				break;
			case Menus::get_page_slug( 'countries' ):
				$log_type = 'top-countries';

				break;
			case Menus::get_page_slug( 'exclusions' ):
				$log_type = 'exclusions';

				break;
			case Menus::get_page_slug( 'hits' ):
				$log_type = 'hit-statistics';

				break;
			case Menus::get_page_slug( 'online' ):
				$log_type = 'online';

				break;
			case Menus::get_page_slug( 'pages' ):
				$log_type = 'top-pages';

				break;
			case Menus::get_page_slug( 'categories' ):
				$log_type = 'categories';

				break;
			case Menus::get_page_slug( 'tags' ):
				$log_type = 'tags';

				break;
			case Menus::get_page_slug( 'authors' ):
				$log_type = 'authors';

				break;
			case Menus::get_page_slug( 'referrers' ):
				$log_type = 'top-referring-site';

				break;
			case Menus::get_page_slug( 'searches' ):
				$log_type = 'search-statistics';

				break;
			case Menus::get_page_slug( 'words' ):
				$log_type = 'last-all-search';

				break;
			case Menus::get_page_slug( 'top-visitors' ):
				$log_type = 'top-visitors';

				break;
			case Menus::get_page_slug( 'visitors' ):
				$log_type = 'last-all-visitor';

				break;
			default:
				$log_type = "";
		}

		// We allow for a get style variable to be passed to define which function to use.
		if ( $log_type == "" && array_key_exists( 'type', $_GET ) ) {
			$log_type = $_GET['type'];
		}

		// Verify the user has the rights to see the statistics.
		if ( ! User::AccessUser( 'read' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// We want to make sure the tables actually exist before we blindly start access them.
		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visitor' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visit' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_exclusions' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_historical' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_pages' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_useronline' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_search'" );

		if ( $result != 7 ) {

			$get_bloginfo_url = Menus::admin_url( 'optimization', array( 'tab' => 'database' ) );
			$missing_tables   = array();

			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visitor'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_visitor';
			}
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visit'" );
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_visit';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_exclusions'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_exclusions';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_historical'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_historical';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_useronline'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_useronline';
			}
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_pages'" );
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_pages';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_search'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_search';
			}

			wp_die(
				'<div class="error"><p>' . sprintf(
					__(
						'The following plugin table(s) do not exist in the database, please re-run the %s install routine %s:',
						'wp-statistics'
					),
					'<a href="' . $get_bloginfo_url . '">',
					'</a>'
				) . implode( ', ', $missing_tables ) . '</p></div>'
			);
		}

		// The different pages have different files to load.
		switch ( $log_type ) {
			case 'all-browsers':
			case 'top-countries':
			case 'hit-statistics':
			case 'search-statistics':
			case 'exclusions':
			case 'online':
			case 'top-visitors':
			case 'categories':
			case 'tags':
			case 'authors':
				include WP_STATISTICS_DIR . 'includes/log/' . $log_type . '.php';
				break;
			case 'last-all-search':
				include WP_STATISTICS_DIR . 'includes/log/last-search.php';

				break;
			case 'last-all-visitor':
				include WP_STATISTICS_DIR . 'includes/log/last-visitor.php';

				break;
			case 'top-referring-site':
				include WP_STATISTICS_DIR . 'includes/log/top-referring.php';

				break;
			case 'top-pages':
				// If we've been given a page id or uri to get statistics for, load the page stats, otherwise load the page stats overview page.
				if ( array_key_exists( 'page-id', $_GET ) || array_key_exists( 'page-uri', $_GET ) || array_key_exists( 'prepage', $_GET ) ) {
					include WP_STATISTICS_DIR . 'includes/log/page-statistics.php';
				} else {
					include WP_STATISTICS_DIR . 'includes/log/top-pages.php';
				}

				break;
			default:
				if ( get_current_screen()->parent_base == Menus::get_page_slug( 'overview' ) ) {
					include WP_STATISTICS_DIR . 'includes/log/log.php';
				}

				break;
		}
	}

}