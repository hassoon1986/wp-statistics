<?php

namespace WP_STATISTICS;

class category_page {

	public function __construct() {

		// Check if in category Page
		if ( Menus::in_page( 'category' ) ) {

			// Disable Screen Option
			add_filter( 'screen_options_show_screen', '__return_false' );

			// Is Validate Date Request
			$DateRequest = Admin_Template::isValidDateRequest();
			if ( ! $DateRequest['status'] ) {
				wp_die( $DateRequest['message'] );
			}
		}
	}

	/**
	 * Display Html Page
	 *
	 * @throws \Exception
	 */
	public static function view() {

		// Page title
		$args['title'] = __( 'Category Statistics', 'wp-statistics' );

		// Get Current Page Url
		$args['pageName']   = Menus::get_page_slug( 'categories' );
		$args['pagination'] = Admin_Template::getCurrentPaged();

		// Get Date-Range
		$args['DateRang'] = Admin_Template::DateRange();

		// Create Select Box
		$args['select_box'] = array(
			'name'  => 'cat',
			'title' => __( 'Select Category', 'wp-statistics' )
		);

		$terms = get_terms( 'category', array(
			'hide_empty' => true,
		) );
		foreach ( $terms as $category ) {
			$args['select_box']['list'][ $category->term_id ] = $category->name;
		}
		$args['select_box']['active'] = ( ( isset( $_GET['cat'] ) and term_exists( (int) trim( $_GET['cat'] ), 'category' ) !== null ) ? $_GET['cat'] : 0 );


		$args['category'] = ( isset( $_GET['cat_id'] ) and term_exists( $_GET['cat_id'], 'category' ) ? $_GET['cat_id'] : 0 );

		// Show Template Page
		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'layout/date.range', 'pages/category', 'layout/postbox.toggle', 'layout/footer' ), $args );
	}

}

new category_page;