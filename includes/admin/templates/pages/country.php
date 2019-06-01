<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php printf( __( 'Toggle panel: %s', 'wp-statistics' ), $title ); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
                <h2 class="hndle"><?php echo $title; ?></h2>
                <div class="inside">
					<?php if ( ! is_array( $list ) ) { ?>
                        <div class='wps-center'><?php _e( "No information is available.", "wp-statistics" ); ?></div>
					<?php } else { ?>
                        <table class="widefat table-stats wps-report-table" style="width: 100%;">
                            <tr>
                                <td width="10%"><?php _e( 'Rank', 'wp-statistics' ); ?></td>
                                <td width="20%" style="text-align: left;"><?php _e( 'Flag', 'wp-statistics' ); ?></td>
                                <td width="30%" style="text-align: left;"><?php _e( 'Country', 'wp-statistics' ); ?></td>
                                <td width="30%" style="text-align: left;"><?php _e( 'Visitor Count', 'wp-statistics' ); ?></td>
                            </tr>
							<?php
							$i = 1;
							foreach ( $list as $item ) { ?>
                                <tr>
                                    <td><?php echo number_format_i18n( $i ); ?></td>
                                    <td style="text-align: left;">
                                        <img src='<?php echo \WP_STATISTICS\Country::flag( $item->location ); ?>' alt='<?php echo \WP_STATISTICS\Country::getName( $item->location ); ?>' title='<?php echo \WP_STATISTICS\Country::getName( $item->location ); ?>'/>
                                    </td>
                                    <td style="text-align: left;"><?php echo \WP_STATISTICS\Country::getName( $item->location ); ?></td>
                                    <td style="text-align: left;">
                                        <a target="_blank" href='<?php echo \WP_STATISTICS\Menus::admin_url( 'visitors', array( 'location' => $item->location ) ); ?>'><?php echo number_format_i18n( $item->count ); ?></a>
                                    </td>
                                </tr>
                                <?php $i++; ?>
							<?php } ?>
                        </table>
					<?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>