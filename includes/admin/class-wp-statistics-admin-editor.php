<?php

namespace WP_STATISTICS;

class Editor {

	/**
	 * Adds a box to the main column on the Post and Page edit screens.
	 */
	public static function add_meta_box() {

		// We need to fudge the display settings for first time users so not all of the widgets are displayed, we only want to do this on
		// the first time they visit the dashboard though so check to see if we've been here before.
		if ( ! Option::getUserOption( 'editor_set' ) ) {
			Option::update_user_option( 'editor_set', WP_STATISTICS_VERSION );

			$hidden_widgets = get_user_meta( User::get_user_id(), 'metaboxhidden_post', true );
			if ( ! is_array( $hidden_widgets ) ) {
				$hidden_widgets = array();
			}

			if ( ! in_array( 'wp_statistics_editor_meta_box', $hidden_widgets ) ) {
				$hidden_widgets[] = 'wp_statistics_editor_meta_box';
			}

			update_user_meta( User::get_user_id(), 'metaboxhidden_post', $hidden_widgets );

			$hidden_widgets = get_user_meta( User::get_user_id(), 'metaboxhidden_page', true );
			if ( ! is_array( $hidden_widgets ) ) {
				$hidden_widgets = array();
			}

			if ( ! in_array( 'wp_statistics_editor_meta_box', $hidden_widgets ) ) {
				$hidden_widgets[] = 'wp_statistics_editor_meta_box';
			}

			update_user_meta( User::get_user_id(), 'metaboxhidden_page', $hidden_widgets );
		}

		// If the user does not have at least read access to the status plugin, just return without adding the widgets.
		if ( User::AccessUser( 'read' ) === false ) {
			return;
		}

		// If the admin has disabled the widgets don't display them.
		if ( Option::get( 'disable_editor' ) ) {
			return;
		}

		// If the admin has disabled the Hit Post MetaBox.
		if ( ! Option::get( 'hit_post_metabox' ) ) {
			return;
		}

		//Show Hit Column in All Post Type in Wordpress
		$screens = Helper::get_list_post_type();
		foreach ( $screens as $screen ) {
			add_meta_box( 'wp_statistics_editor_meta_box', __( 'Hit Statistics', 'wp-statistics' ), 'WP_Statistics_Editor::meta_box', $screen, 'normal', 'high',
				array(
					'__block_editor_compatible_meta_box' => true,
					'__back_compat_meta_box'             => false,
				)
			);
		}
	}


	static function meta_box( $post ) {
		// If the post isn't published yet, don't output the stats as they take too much memory and CPU to compute for no reason.
		if ( $post->post_status != 'publish' && $post->post_status != 'private' ) {
			_e( 'This post is not yet published.', 'wp-statistics' );
			return;
		}

		add_action( 'admin_footer', 'WP_Statistics_Editor::inline_javascript' );
		Editor::generate_postbox_contents( $post->ID, array( 'args' => array( 'widget' => 'page' ) ) );
	}

	static function generate_postbox_contents( $post, $args ) {
		if ( Helper::is_gutenberg() ) {
			//If Gutenberg Editor
			if ( isset( $_GET['post'] ) and ! empty( $_GET['post'] ) ) {
				echo '<div class="wps-gutenberg-chart-js">';
				require( WP_STATISTICS_DIR . 'includes/log/widgets/page.php' );
				wp_statistics_generate_page_postbox_content( null, $_GET['post'] );
				echo '</div>';
				echo '<style>button#wp_statistics_editor_meta_box_more_button { z-index: 9999;position: absolute;top: 1px;right: 3%;}</style>';
			}
		} else {
			$widget       = $args['args']['widget'];
			$container_id = 'wp-statistics-' . str_replace( '.', '-', $widget ) . '-div';
			echo '<div id="' . $container_id . '">' . Admin_Templates::loading_meta_box() . '</div>';
			echo '<script type="text/javascript">var wp_statistics_current_id = \'' . $post . '\';</script>';
			wp_statistics_generate_widget_load_javascript( $widget, $container_id );
		}
	}

	static function inline_javascript() {
		$screen = get_current_screen();

		$screens = Helper::get_list_post_type();
		if ( ! in_array( $screen->id, $screens ) ) {
			return;
		}

		$loading_img = Admin_Templates::loading_meta_box();
		$new_buttons = '</button>';

		//If Classic Editor
		if ( Helper::is_gutenberg() === false ) {
			$new_buttons .= '<button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . Admin_Templates::icons( 'dashicons-update' ) . '<span class="screen-reader-text">' . __( 'Reload', 'wp-statistics' ) . '</span></button>';
		}
		$new_buttons .= '<button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' . Admin_Templates::icons( 'dashicons-external' ) . '<span class="screen-reader-text">' . __( 'More Details', 'wp-statistics' ) . '</span></button>';


		$admin_url                                              = get_admin_url() . "/admin.php?page=";
		$page_urls                                              = array();
		$page_urls['wp_statistics_editor_meta_box_more_button'] = $admin_url . Menus::get_page_slug( 'pages' ) . '&page-id=';

		//Button for Gutenberg
		$btn_more_action = 'wp_statistics_goto_more';
		if ( Helper::is_gutenberg() ) {
			$btn_more_action = "function () { window.location.href = '" . wp_normalize_path( $page_urls['wp_statistics_editor_meta_box_more_button'] . ( isset( $_GET['post'] ) === true ? $_GET['post'] : '' ) ) . "';}";
		}

		?>
        <script type="text/javascript">
            var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>;
            var wp_statistics_loading_image = '<?php echo $loading_img; ?>';

            function wp_statistics_wait_for_postboxes() {

                if (!jQuery('#show-settings-link').is(':visible')) {
                    setTimeout(wp_statistics_wait_for_postboxes, 500);
                }

                jQuery('.wps-refresh').unbind('click').on('click', wp_statistics_refresh_widget);
                jQuery('.wps-more').unbind('click').on('click', <?php echo $btn_more_action; ?>);
                jQuery('.hide-postbox-tog').on('click', wp_statistics_refresh_on_toggle_widget);
            }

            jQuery(document).ready(function () {

                // Add the "more" and "refresh" buttons.
                jQuery('.postbox').each(function () {
                    var temp = jQuery(this);
                    var temp_id = temp.attr('id');

                    if (temp_id == 'wp_statistics_editor_meta_box') {

                        var temp_html = temp.html();

                        new_text = '<?php echo $new_buttons;?>';
                        new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');
                        new_text = new_text.replace('{{moreid}}', temp_id + '_more_button');

                        temp_html = temp_html.replace('</button>', new_text);

                        temp.html(temp_html);
                    }
                });

                // We have use a timeout here because we don't now what order this code will run in comparison to the postbox code.
                // Any timeout value should work as the timeout won't run until the rest of the javascript as run through once.
                setTimeout(wp_statistics_wait_for_postboxes, 100);
            });
        </script>
		<?php
	}

}
