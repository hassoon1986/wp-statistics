<?php

namespace WP_STATISTICS;

class online_page {
	/**
	 * Display Html Page
	 *
	 * @throws \Exception
	 */
	public static function view() {

		// Page title
		$page_title = __( 'Online Users', 'wp-statistics' );

		//Get Total User Online
		$total_user_online = UserOnline::get( array( 'fields' => 'count' ) );

		// Get List OF User Online
		if ( $total_user_online > 0 ) {
			$user_online_list = UserOnline::get( array( 'offset' => Admin_Templates::getCurrentOffset(), 'per_page' => Admin_Templates::$item_per_page ) );
		} else {
			$user_online_list = __( 'Currently there are no online users in the site.', 'wp-statistics' );
		}

		// Create WordPress Pagination
		$pagination = '';
		if ( $total_user_online > 0 ) {
			$pagination = Admin_Templates::paginate_links( array(
				'total' => $total_user_online,
				'echo'  => false
			) );
		}

		include WP_STATISTICS_DIR . "includes/admin/templates/pages/online.php";
	}

}