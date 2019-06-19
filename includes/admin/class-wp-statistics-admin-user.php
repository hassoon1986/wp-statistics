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
					return '<a href="' . Menus::admin_url( 'visitors', array( 'user_id' => $user_id ) ) . '" class="wps-text-muted" target="_blank"><span class="dashicons dashicons-chart-area"></span></a>';
				} else {
					return Admin_Template::UnknownColumn();
				}
			default:
		}
		return $val;
	}

}

new Admin_User;