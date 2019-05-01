<?php

namespace WP_STATISTICS;

class Admin_Dashboard {
	/**
	 * User Meta Set Dashboard Option name
	 *
	 * @var string
	 */
	public static $dashboard_set = 'dashboard_set';

	/**
	 * Admin_Dashboard constructor.
	 */
	public function __construct() {

		//Register Dashboard Widget
		add_action( 'wp_dashboard_setup', array( $this, 'load_dashboard_widget' ) );

		//Add Inline Script in Admin Footer
		add_action( 'admin_footer', array( $this, 'inline_javascript' ) );
	}

	/**
	 * This function Register Wp-statistics Dashboard to wordpress Admin
	 */
	public function register_dashboard_widget() {

		foreach ( Meta_Box::_list() as $widget_key => $dashboard ) {
			if ( Option::check_option_require( $dashboard ) === true and isset( $dashboard['show_on_dashboard'] ) and $dashboard['show_on_dashboard'] === true ) {
				wp_add_dashboard_widget( Meta_Box::getMetaBoxKey( $widget_key ), $dashboard['name'], array( $this, 'generate_postbox_contents' ), $control_callback = null, array( 'widget' => $widget_key ) );
			}
		}
	}

	/**
	 * Load Dashboard Widget
	 * This Function add_action to `wp_dashboard_setup`
	 */
	public function load_dashboard_widget() {

		// If the user does not have at least read access to the status plugin, just return without adding the widgets.
		if ( ! current_user_can( wp_statistics_validate_capability( Option::get( 'read_capability', 'manage_option' ) ) ) ) {
			return;
		}

		//Check Hidden User Dashboard Option
		$user_dashboard = Option::getUserOption( self::$dashboard_set );
		if ( $user_dashboard === false || $user_dashboard != WP_STATISTICS_VERSION ) {
			self::set_user_hidden_dashboard_option();
		}

		// If the admin has disabled the widgets, don't display them.
		if ( ! Option::get( 'disable_dashboard' ) ) {
			$this->register_dashboard_widget();
		}

	}

	/**
	 * Set Default Hidden Dashboard User Option
	 */
	public static function set_user_hidden_dashboard_option() {

		//Get List Of Wp-statistics Dashboard Widget
		$dashboard_list = Meta_Box::_list();
		$hidden_opt     = 'metaboxhidden_dashboard';

		//Create Empty Option and save in User meta
		Option::update_user_option( self::$dashboard_set, WP_STATISTICS_VERSION );

		//Get Dashboard Option User Meta
		$hidden_widgets = get_user_meta( User::get_user_id(), $hidden_opt, true );
		if ( ! is_array( $hidden_widgets ) ) {
			$hidden_widgets = array();
		}

		//Set Default Hidden Dashboard in Admin Wordpress
		foreach ( $dashboard_list as $widget => $dashboard ) {
			if ( isset( $dashboard['hidden'] ) and $dashboard['hidden'] === true ) {
				$hidden_widgets[] = Meta_Box::getMetaBoxKey( $widget );
			}
		}

		update_user_meta( User::get_user_id(), $hidden_opt, $hidden_widgets );
	}

	/**
	 * Add inline Script
	 * For Add button Refresh/Direct Button Link in Top of Meta Box
	 */
	static function inline_javascript() {

		//if not Dashboard Page
		$screen = get_current_screen();
		if ( 'dashboard' != $screen->id ) {
			return;
		}

		//Prepare List Of Dashboard
		$page_urls  = array();
		$dashboards = Meta_Box::_list();
		foreach ( $dashboards as $widget_key => $dashboard ) {
			if ( array_key_exists( 'page_url', $dashboard ) ) {
				$page_urls[ 'wp-statistics-' . $widget_key . '-widget_more_button' ] = Admin_Menus::admin_url( $dashboard['page_url'] );
			}
		}

		//Add Extra Pages For Overview Page
		foreach ( array( 'exclusions' => 'exclusions', 'users_online' => 'online' ) as $p_key => $p_link ) {
			$page_urls[ 'wp-statistics-' . $p_key . '-widget_more_button' ] = Admin_Menus::admin_url( $p_link );
		}

		?>
        <script type="text/javascript">
            var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>;
            var wp_statistics_loading_image = '<?php echo Admin_Templates::loading_meta_box(); ?>';

            function wp_statistics_wait_for_postboxes() {

                if (!jQuery('#show-settings-link').is(':visible')) {
                    setTimeout(wp_statistics_wait_for_postboxes, 500);
                }

                jQuery('.wps-refresh').unbind('click').on('click', wp_statistics_refresh_widget);
                jQuery('.wps-more').unbind('click').on('click', wp_statistics_goto_more);

                jQuery('.hide-postbox-tog').on('click', wp_statistics_refresh_on_toggle_widget);
            }

            jQuery(document).ready(function () {

                // Add the "more" and "refresh" buttons.
                jQuery('.postbox').each(function () {
                    var temp = jQuery(this);
                    var temp_id = temp.attr('id');

                    if (temp_id.substr(0, 14) != 'wp-statistics-') {
                        return;
                    }

                    var temp_html = temp.html();
                    if (temp_id == '<?php echo Meta_Box::getMetaBoxKey( 'summary' ); ?>') {
                        new_text = '<?php echo Admin_Templates::meta_box_button( 'refresh' );?>';
                        new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');
                        temp_html = temp_html.replace('</button>', new_text);
                    } else {
                        new_text = '<?php echo Admin_Templates::meta_box_button();?>';
                        new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');
                        new_text = new_text.replace('{{moreid}}', temp_id + '_more_button');
                        temp_html = temp_html.replace('</button>', new_text);
                    }
                    temp.html(temp_html);
                });

                // We have use a timeout here because we don't now what order this code will run in comparison to the postbox code.
                // Any timeout value should work as the timeout won't run until the rest of the javascript as run through once.
                setTimeout(wp_statistics_wait_for_postboxes, 100);
            });
        </script>
		<?php
	}

	/**
	 * Generate widget Post Box
	 *
	 * @param $post
	 * @param $args
	 */
	public function generate_postbox_contents( $post, $args ) {
		$widget       = $args['args']['widget'];
		$container_id = 'wp-statistics-' . str_replace( '.', '-', $widget ) . '-div';

		echo '<div id="' . $container_id . '">' . Admin_Templates::loading_meta_box() . '</div>';
		wp_statistics_generate_widget_load_javascript( $widget, $container_id );
	}

}
