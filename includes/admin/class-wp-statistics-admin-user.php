<?php

namespace WP_STATISTICS;

class Admin_User {
	/**
	 * constructor.
	 */
	public function __construct() {

		// Add Visits Column in All Admin User Wp_List_Table
		if ( User::Access( 'read' ) and Option::get( 'enable_user_column' ) ) {
			add_filter( 'manage_users_columns', array( $this, 'add_column_user_table' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'modify_user_table_row' ), 10, 3 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'sort_by_custom_field' ) );
			add_action( 'pre_user_query', array( $this, 'modify_pre_user_query' ) );
		}
	}

	/**
	 * Add Visits Link
	 *
	 * @param $column
	 * @return mixed
	 */
	public function add_column_user_table( $column ) {
		$column['visits'] = __( "Visits", "wp-statistics" );
		return $column;
	}

	/**
	 * Modify Users Row
	 *
	 * @param $val
	 * @param $column_name
	 * @param $user_id
	 * @return mixed
	 */
	public function modify_user_table_row( $val, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'visits' :
				$count = Visitor::Count( array( 'key' => 'user_id', 'compare' => '=', 'value' => $user_id ) );
				if ( $count > 0 ) {
					return '<a href="' . Menus::admin_url( 'visitors', array( 'user_id' => $user_id ) ) . '" class="wps-text-muted" target="_blank">' . number_format_i18n( $count ) . '</a>';
				} else {
					return Admin_Template::UnknownColumn();
				}
			default:
		}
		return $val;
	}

	/**
	 * Sort By Users Visit
	 *
	 * @param $columns
	 * @return mixed
	 */
	function sort_by_custom_field( $columns ) {
		$columns['visits'] = 'visit';
		return $columns;
	}

	/**
	 * Pre User Query Join by visitors
	 *
	 * @param $user_query
	 */
	public function modify_pre_user_query( $user_query ) {
		global $wpdb;

		// Check in Admin
		if ( ! is_admin() ) {
			return;
		}

		// Get global Variable
		$order   = $user_query->query_vars['order'];
		$orderby = $user_query->query_vars['orderby'];

		// If order-by.
		if ( 'visit' === $orderby ) {

			// Select Field
			$user_query->query_fields .= ", (select Count(*) from " . DB::table( "visitor" ) . " where {$wpdb->users}.ID = " . DB::table( "visitor" ) . ".user_id) as user_visit ";

			// And order by it.
			$user_query->query_orderby = " ORDER BY user_visit $order";
		}
	}

}

new Admin_User;