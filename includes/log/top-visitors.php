<script type="text/javascript">
    jQuery(document).ready(function () {
        postboxes.add_postbox_toggles(pagenow);
    });
</script>
<?php

use WP_STATISTICS\Admin_Helper;
use WP_STATISTICS\Admin_Templates;

$ISOCountryCode = \WP_STATISTICS\Helper::get_country_codes();
include( WP_STATISTICS_DIR . 'includes/log/widgets/top.visitors.php' );
?>
<div class="wrap wps-wrap">
	<?php Admin_Templates::show_page_title( __( 'Top 100 Visitors Today', 'wp-statistics' ) ); ?>
	<?php

	$current    = 0;
	$statsdate  = \WP_STATISTICS\TimeZone::getCurrentDate( get_option( "date_format" ), '-' . $current );
	$rang_start = \WP_STATISTICS\TimeZone::getCurrentDate( "Y-m-d" );
	if ( isset( $_GET['statsdate'] ) and strtotime( $_GET['statsdate'] ) != false ) {
		$statsdate  = date( get_option( "date_format" ), strtotime( $_GET['statsdate'] ) );
		$rang_start = date( "Y-m-d", strtotime( $_GET['statsdate'] ) );
	}

	echo '<br><form method="get">' . "\r\n";
	echo ' ' . __( 'Date', 'wp-statistics' ) . ': ';

	echo '<input type="hidden" name="page" value="' . \WP_STATISTICS\Admin_Menus::get_page_slug( 'top-visitors' ) . '">' . "\r\n";
	echo '<input type="text" size="18" name="statsdate" wps-date-picker="stats" value="' . htmlentities( $statsdate, ENT_QUOTES ) . '" autocomplete="off" placeholder="' . __( Admin_Templates::convert_php_to_jquery_datepicker( get_option( "date_format" ) ), 'wp-statistics' ) . '"> <input type="submit" value="' . __( 'Go', 'wp-statistics' ) . '" class="button-primary">' . "\r\n";
	echo '<input type="hidden" name="statsdate" id="date-stats" value="' . $rang_start . '">';
	echo '</form>' . "\r\n";
	?>
    <div class="postbox-container" id="last-log" style="width: 100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="postbox">
					<?php $paneltitle = __( 'Top Visitors', 'wp-statistics' ); ?>
                    <button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__( 'Toggle panel: %s', 'wp-statistics' ),
								$paneltitle
							); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

                    <div class="inside">

						<?php wp_statistics_generate_top_visitors_postbox_content(
							$ISOCountryCode,
							$statsdate,
							100,
							false
						); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>