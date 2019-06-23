<?php

namespace WP_STATISTICS;

class Admin_Post {
	/**
	 * Hits Chart Post/page Meta Box
	 *
	 * @var string
	 */
	public static $hits_chart_post_meta_box = 'post';

	/**
	 * Admin_Post constructor.
	 */
	public function __construct() {

		// Add Hits Column in All Admin Post-Type Wp_List_Table
		if ( User::Access( 'read' ) and Option::get( 'pages' ) and ! Option::get( 'disable_column' ) ) {
			foreach ( Helper::get_list_post_type() as $type ) {
				add_action( 'manage_' . $type . '_posts_columns', array( $this, 'add_hit_column' ), 10, 2 );
				add_action( 'manage_' . $type . '_posts_custom_column', array( $this, 'render_hit_column' ), 10, 2 );
				add_filter( 'manage_edit-' . $type . '_sortable_columns', array( $this, 'modify_sortable_columns' ) );
			}
			add_filter( 'posts_clauses', array( $this, 'modify_order_by_hits' ), 10, 2 );
		}

		// Add WordPress Post/Page Hit Chart Meta Box in edit Page
		if ( User::Access( 'read' ) and ! Option::get( 'disable_editor' ) ) {
			add_action( 'add_meta_boxes', array( $this, 'define_post_meta_box' ) );
		}

		// Add Post Hit Number in Publish Meta Box in WordPress Edit a post/page
		if ( Option::get( 'pages' ) and Option::get( 'hit_post_metabox' ) ) {
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_hit_misc' ) );
		}

	}

	/**
	 * Add a custom column to post/pages for hit statistics.
	 *
	 * @param array $columns Columns
	 * @return array Columns
	 */
	public function add_hit_column( $columns ) {
		$columns['wp-statistics-post-hits'] = __( 'Hits', 'wp-statistics' );
		return $columns;
	}

	/**
	 * Render the custom column on the post/pages lists.
	 *
	 * @param string $column_name Column Name
	 * @param string $post_id Post ID
	 */
	public function render_hit_column( $column_name, $post_id ) {
		if ( $column_name == 'wp-statistics-post-hits' ) {
			echo "<a href='" . Menus::admin_url( 'pages', array( 'page-id' => $post_id ) ) . "'>" . wp_statistics_pages( 'total', "", $post_id ) . "</a>";
		}
	}

	/**
	 * Added Sortable Params
	 *
	 * @param $columns
	 * @return mixed
	 */
	public function modify_sortable_columns( $columns ) {
		$columns['wp-statistics-post-hits'] = 'hits';
		return $columns;
	}

	/**
	 * Sort Post By Hits
	 *
	 * @param $clauses
	 * @param $query
	 */
	public function modify_order_by_hits( $clauses, $query ) {
		global $wpdb;

		// Check in Admin
		if ( ! is_admin() ) {
			return;
		}

		// Get global Variable
		$order   = $query->query_vars['order'];
		$orderby = $query->query_vars['orderby'];

		// If order-by.
		if ( 'hits' === $orderby ) {

			// Select Field
			$clauses['fields'] .= ", (select SUM(" . DB::table( "pages" ) . ".count) from " . DB::table( "pages" ) . " where (" . DB::table( "pages" ) . ".type = 'page' OR " . DB::table( "pages" ) . ".type = 'post' OR " . DB::table( "pages" ) . ".type = 'product') AND {$wpdb->posts}.ID = " . DB::table( "pages" ) . ".id) as post_hist_sortable ";

			// And order by it.
			$clauses['orderby'] = " post_hist_sortable $order";
		}

		return $clauses;
	}

	/**
	 * Add Post Hit Number in Publish Meta Box in WordPress Edit a post/page
	 */
	public function post_hit_misc() {
		global $post;
		if ( $post->post_status == 'publish' ) {
			echo "<div class='misc-pub-section'>" . __( 'WP Statistics - Hits', 'wp-statistics' ) . ": <b><a href='" . Menus::admin_url( 'pages', array( 'page-id' => $post->ID ) ) . "'>" . wp_statistics_pages( 'total', "", $post->ID ) . "</a></b></div>";
		}
	}

	/**
	 * Define Hit Chart Meta Box
	 */
	public function define_post_meta_box() {

		// Get MetaBox information
		$metaBox = Meta_Box::getList( self::$hits_chart_post_meta_box );

		// Add MEtaBox To all Post Type
		foreach ( Helper::get_list_post_type() as $screen ) {
			add_meta_box( Meta_Box::getMetaBoxKey( self::$hits_chart_post_meta_box ), $metaBox['name'], Meta_Box::LoadMetaBox( self::$hits_chart_post_meta_box ), $screen, 'normal', 'high', array( '__block_editor_compatible_meta_box' => true, '__back_compat_meta_box' => false ) );
		}
	}

}

new Admin_Post;