<?php

namespace WP_STATISTICS;

class pages_page {

	public function __construct() {

		if ( Menus::in_page( 'pages' ) ) {

			// Disable Screen Option
			add_filter( 'screen_options_show_screen', '__return_false' );
		}
	}

	/**
	 * Display Html Page
	 *
	 * @throws \Exception
	 */
	public static function view() {

		// Page title
		$args['title'] = __( 'Top Pages', 'wp-statistics' );

		// Get Current Page Url
		$args['pageName']   = Menus::get_page_slug( 'pages' );
		$args['pagination'] = Admin_Template::getCurrentPaged();

		// Total Number
		$args['total'] = Pages::TotalCount();

		// Create WordPress Pagination
		$args['pagination'] = '';
		if ( $args['total'] > 0 ) {
			$args['pagination'] = Admin_Template::paginate_links( array(
				'total' => $args['total'],
				'echo'  => false
			) );
		}

		// Show Template Page
		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'pages/pages', 'layout/postbox.toggle', 'layout/footer' ), $args );
	}

}

new pages_page;