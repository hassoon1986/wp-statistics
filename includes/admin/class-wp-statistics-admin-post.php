<?php

namespace WP_STATISTICS;

class Admin_Post {

	public function __construct() {

		// Add Hits Column in All Admin Post-Type Wp_List_Table
		if ( User::Access( 'read' ) && Option::get( 'pages' ) && ! Option::get( 'disable_column' ) ) {
			foreach ( Helper::get_list_post_type() as $type ) {
				add_action( 'manage_' . $type . '_posts_columns', array( $this, 'add_hit_column' ), 10, 2 );
				add_action( 'manage_' . $type . '_posts_custom_column', array( $this, 'render_hit_column' ), 10, 2 );
			}
		}

		// Add WordPress Post/Page Hit Chart Meta Box in edit Page
		if ( User::Access( 'read' ) and ! Option::get( 'disable_editor' ) and ! Option::get( 'hit_post_metabox' ) ) {
			add_action( 'add_meta_boxes', array( $this, 'define_post_meta_box' ) );
		}

		// Add Post Hit Number in Publish Meta Box in WordPress Edit a post/page
		if ( Option::get( 'pages' ) && ! Option::get( 'disable_column' ) ) {
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
		$columns['wp-statistics'] = __( 'Hits', 'wp-statistics' );
		return $columns;
	}

	/**
	 * Render the custom column on the post/pages lists.
	 *
	 * @param string $column_name Column Name
	 * @param string $post_id Post ID
	 */
	public function render_hit_column( $column_name, $post_id ) {
		if ( $column_name == 'wp-statistics' ) {
			echo "<a href='" . Menus::admin_url( 'pages', array( 'page-id' => $post_id ) ) . "'>" . wp_statistics_pages( 'total', "", $post_id ) . "</a>";
		}
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
		foreach ( Helper::get_list_post_type() as $screen ) {
			add_meta_box( 'wp_statistics_editor_meta_box', __( 'Hit Statistics', 'wp-statistics' ), array( $this, 'hit_chart_meta_box' ), $screen, 'normal', 'high', array( '__block_editor_compatible_meta_box' => true, '__back_compat_meta_box' => false ) );
		}
	}

	/**
	 * Hit Chart Meta Box
	 *
	 * @param $post
	 */
	public function hit_chart_meta_box( $post ) {
		if ( $post->post_status != 'publish' && $post->post_status != 'private' ) {
			_e( 'This post is not yet published.', 'wp-statistics' );
		} else {
			add_action( 'admin_footer', array( $this, 'inline_javascript' ) );
			self::generate_postbox_contents( $post->ID, array( 'args' => array( 'widget' => 'page' ) ) );
		}
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

	public function inline_javascript() {
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

new Admin_Post;