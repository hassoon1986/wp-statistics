<?php

namespace WP_STATISTICS;

class hits_page {

	public function __construct() {

		// Disable Screen Option
		if ( Menus::in_page( 'hits' ) ) {
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
		$args['title'] = __( 'Hit Statistics', 'wp-statistics' );


		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'pages/hits', 'layout/postbox.toggle', 'layout/footer' ), $args );
	}

}

new hits_page;