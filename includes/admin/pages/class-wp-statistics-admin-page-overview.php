<?php

namespace WP_STATISTICS;

class log_page {

	public function __construct() {
		add_action( 'load-' . Menus::get_action_menu_slug( 'overview' ), array( $this, 'meta_box_init' ) );
	}

	/**
	 * Define Meta Box
	 */
	public function meta_box_init() {

		foreach ( Meta_Box::getList() as $meta_key => $meta_box ) {
			if ( Option::check_option_require( $meta_box ) === true and ( ( isset( $meta_box['disable_overview'] ) and $meta_box['disable_overview'] === false ) || ! isset( $meta_box['disable_overview'] ) ) ) {
				add_meta_box( Meta_Box::getMetaBoxKey( $meta_key ), $meta_box['name'], Meta_Box::LoadMetaBox( $meta_key ), Menus::get_action_menu_slug( 'overview' ), $meta_box['place'], $control_callback = null, array( 'widget' => $meta_key ) );
			}
		}

	}

	/**
	 * Display Html Page
	 */
	public static function view() {
		$args['overview_page_slug'] = Menus::get_action_menu_slug( 'overview' );
		Admin_Template::get_template( array( 'layout/header', 'layout/title', 'pages/overview', 'layout/footer' ), $args );
	}

}

new log_page();