<?php

namespace WP_STATISTICS;

class Admin {
	/**
	 * WP_Statistics_Admin constructor.
	 */
	public function __construct() {

		// If we've been flagged to remove all of the data, then do so now.
		if ( get_option( 'wp_statistics_removal' ) == 'true' ) {
			new Uninstall;
		}

		// If we've been removed, return without doing anything else.
		if ( get_option( 'wp_statistics_removal' ) == 'done' ) {
			add_action( 'admin_notices', array( $this, 'removal_admin_notice' ), 10, 2 );
			return;
		}

		//Add Custom MetaBox in Wp-statistics Admin Page
		add_action( 'add_meta_boxes', array( '\WP_STATISTICS\Editor', 'add_meta_box' ) );

		// Display the admin notices if we should.
		if ( isset( $pagenow ) && array_key_exists( 'page', $_GET ) ) {
			if ( $pagenow == "admin.php" && substr( $_GET['page'], 0, 14 ) == 'wp-statistics/' ) {
				add_action( 'admin_notices', array( $this, 'not_enable' ) );
			}
		}

		//Change Plugin Action link in Plugin.php admin
		add_filter( 'plugin_action_links_' . plugin_basename( WP_STATISTICS_MAIN_FILE ), array( $this, 'settings_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'add_meta_links' ), 10, 2 );

		//Add Column in Post Type Wp_List Table
		add_action( 'load-edit.php', array( $this, 'load_edit_init' ) );
		if ( Option::get( 'pages' ) && ! Option::get( 'disable_column' ) ) {
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_init' ) );
		}

		//Check Require update page type in database
		Install::_init_page_type_updater();
	}

	/**
	 * This adds a row after WP Statistics in the plugin page
	 * IF we've been removed via the settings page.
	 */
	public function removal_admin_notice() {
		$screen = get_current_screen();

		if ( 'plugins' !== $screen->id ) {
			return;
		}

		?>
        <div class="error">
            <p style="max-width:800px;"><?php
				echo '<p>' . __( 'WP Statistics has been removed, please disable and delete it.', 'wp-statistics' ) . '</p>';
				?></p>
        </div>
		<?php
	}

	/**
	 * This function outputs error messages in the admin interface
	 * if the primary components of WP Statistics are enabled.
	 */
	public function not_enable() {

		// If the user had told us to be quite, do so.
		if ( ! Option::get( 'hide_notices' ) ) {

			// Check to make sure the current user can manage WP Statistics,
			// if not there's no point displaying the warnings.
			$manage_cap = wp_statistics_validate_capability( Option::get( 'manage_capability', 'manage_options' ) );
			if ( ! current_user_can( $manage_cap ) ) {
				return;
			}


			$get_bloginfo_url = Menus::admin_url( 'settings' );

			$itemstoenable = array();
			if ( ! Option::get( 'useronline' ) ) {
				$itemstoenable[] = __( 'online user tracking', 'wp-statistics' );
			}
			if ( ! Option::get( 'visits' ) ) {
				$itemstoenable[] = __( 'hit tracking', 'wp-statistics' );
			}
			if ( ! Option::get( 'visitors' ) ) {
				$itemstoenable[] = __( 'visitor tracking', 'wp-statistics' );
			}
			if ( ! Option::get( 'geoip' ) && GeoIp::IsSupport() ) {
				$itemstoenable[] = __( 'geoip collection', 'wp-statistics' );
			}

			if ( count( $itemstoenable ) > 0 ) {
				echo '<div class="update-nag">' . sprintf( __( 'The following features are disabled, please go to %ssettings page%s and enable them: %s', 'wp-statistics' ), '<a href="' . $get_bloginfo_url . '">', '</a>', implode( __( ',', 'wp-statistics' ), $itemstoenable ) ) . '</div>';
			}


			$get_bloginfo_url = Menus::admin_url( 'optimization', array( 'tab' => 'database' ) );
			$dbupdatestodo    = array();

			if ( ! Option::get( 'search_converted' ) ) {
				$dbupdatestodo[] = __( 'search table', 'wp-statistics' );
			}

			// Check to see if there are any database changes the user hasn't done yet.
			$dbupdates = Option::get( 'pending_db_updates', false );

			// The database updates are stored in an array so loop thorugh it and output some notices.
			if ( is_array( $dbupdates ) ) {
				$dbstrings = array(
					'date_ip_agent' => __( 'countries database index', 'wp-statistics' ),
					'unique_date'   => __( 'visit database index', 'wp-statistics' ),
				);

				foreach ( $dbupdates as $key => $update ) {
					if ( $update == true ) {
						$dbupdatestodo[] = $dbstrings[ $key ];
					}
				}

				if ( count( $dbupdatestodo ) > 0 ) {
					echo '<div class="update-nag">' . sprintf( __( 'Database updates are required, please go to %soptimization page%s and update the following: %s', 'wp-statistics' ), '<a href="' . $get_bloginfo_url . '">', '</a>', implode( __( ',', 'wp-statistics' ), $dbupdatestodo ) ) . '</div>';
				}
			}
		}
	}

	/**
	 * Add a settings link to the plugin list.
	 *
	 * @param string $links Links
	 * @param string $file Not Used!
	 *
	 * @return string Links
	 */
	public function settings_links( $links, $file ) {

		$manage_cap = wp_statistics_validate_capability( Option::get( 'manage_capability', 'manage_options' ) );
		if ( current_user_can( $manage_cap ) ) {
			array_unshift( $links, '<a href="' . Menus::admin_url( 'settings' ) . '">' . __( 'Settings', 'wp-statistics' ) . '</a>' );
		}

		return $links;
	}

	/**
	 * Add a WordPress plugin page and rating links to the meta information to the plugin list.
	 *
	 * @param string $links Links
	 * @param string $file File
	 *
	 * @return array Links
	 */
	public function add_meta_links( $links, $file ) {
		if ( $file == plugin_basename( WP_STATISTICS_MAIN_FILE ) ) {
			$plugin_url = 'http://wordpress.org/plugins/wp-statistics/';

			$links[]  = '<a href="' . $plugin_url . '" target="_blank" title="' . __( 'Click here to visit the plugin on WordPress.org', 'wp-statistics' ) . '">' . __( 'Visit WordPress.org page', 'wp-statistics' ) . '</a>';
			$rate_url = 'https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post';
			$links[]  = '<a href="' . $rate_url . '" target="_blank" title="' . __( 'Click here to rate and review this plugin on WordPress.org', 'wp-statistics' ) . '">' . __( 'Rate this plugin', 'wp-statistics' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Call the add/render functions at the appropriate times.
	 */
	public function load_edit_init() {

		$read_cap = wp_statistics_validate_capability( Option::get( 'read_capability', 'manage_options' ) );

		if ( current_user_can( $read_cap ) && Option::get( 'pages' ) && ! Option::get( 'disable_column' ) ) {
			$post_types = Helper::get_list_post_type();
			foreach ( $post_types as $type ) {
				add_action( 'manage_' . $type . '_posts_columns', 'WP_Statistics_Admin::add_column', 10, 2 );
				add_action( 'manage_' . $type . '_posts_custom_column', 'WP_Statistics_Admin::render_column', 10, 2 );
			}
		}
	}

	/**
	 * Add a custom column to post/pages for hit statistics.
	 *
	 * @param array $columns Columns
	 *
	 * @return array Columns
	 */
	static function add_column( $columns ) {
		$columns['wp-statistics'] = __( 'Hits', 'wp-statistics' );

		return $columns;
	}

	/**
	 * Render the custom column on the post/pages lists.
	 *
	 * @param string $column_name Column Name
	 * @param string $post_id Post ID
	 */
	static function render_column( $column_name, $post_id ) {
		if ( $column_name == 'wp-statistics' ) {
			echo "<a href='" . Menus::admin_url( 'pages', array( 'page-id' => $post_id ) ) . "'>" . wp_statistics_pages( 'total', "", $post_id ) . "</a>";
		}
	}

	/**
	 * Add the hit count to the publish widget in the post/pages editor.
	 */
	public function post_init() {
		global $post;

		$id = $post->ID;
		echo "<div class='misc-pub-section'>" . __( 'WP Statistics - Hits', 'wp-statistics' ) . ": <b><a href='" . Menus::admin_url( 'pages', array( 'page-id' => $id ) ) . "'>" . wp_statistics_pages( 'total', "", $id ) . "</a></b></div>";
	}
}