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

		//Change Plugin Action link in Plugin.php admin
		add_filter( 'plugin_action_links_' . plugin_basename( WP_STATISTICS_MAIN_FILE ), array( $this, 'settings_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'add_meta_links' ), 10, 2 );

		//Add Column in Post Type Wp_List Table
		add_action( 'load-edit.php', array( $this, 'load_edit_init' ) );
		if ( Option::get( 'pages' ) && ! Option::get( 'disable_column' ) ) {
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_init' ) );
		}
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
	 * Add a settings link to the plugin list.
	 *
	 * @param string $links Links
	 * @param string $file Not Used!
	 *
	 * @return string Links
	 */
	public function settings_links( $links, $file ) {

		if ( User::Access( 'manage' ) ) {
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

		if ( User::Access( 'read' ) && Option::get( 'pages' ) && ! Option::get( 'disable_column' ) ) {
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