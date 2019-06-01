<ul class="subsubsub wp-statistics-sub-fullwidth">
	<?php
	foreach ( $sub as $key => $item ) {
		?>
        <li class="all">
            <a <?php if ( $item['active'] === true ) { ?> class="current" <?php } ?> href="<?php echo $item['link']; ?>">
				<?php echo $item['title']; ?>
                <span class='count'>(<?php echo number_format_i18n( $item['count'] ); ?>)</span>
            </a>
        </li>
		<?php $sub_keys = array_keys( $sub );
		if ( end( $sub_keys ) != $key ) { ?> | <?php } ?><?php } ?>
</ul>

<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php echo sprintf( __( 'Toggle panel: %s', 'wp-statistics' ), $title ); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
                <h2 class="hndle"><span><?php echo $title; ?></span></h2>
                <div class="inside">
	                <?php if ( ! is_array( $list ) ) { ?>
                        <div class='wps-center'><?php echo $list; ?></div>
	                <?php } else { ?>
                        <table width="100%" class="widefat table-stats">
                            <tr>
                                <td><?php _e( 'Browser', 'wp-statistics' ); ?></td>
				                <?php if ( WP_STATISTICS\GeoIP::active() ) { ?>
                                    <td><?php _e( 'Country', 'wp-statistics' ); ?></td>
				                <?php } ?>
				                <?php if ( WP_STATISTICS\GeoIP::active( 'city' ) ) { ?>
                                    <td><?php _e( 'City', 'wp-statistics' ); ?></td>
				                <?php } ?>
                                <td><?php _e( 'Date', 'wp-statistics' ); ?></td>
                                <td><?php _e( 'IP', 'wp-statistics' ); ?></td>
                                <td><?php _e( 'Referrer', 'wp-statistics' ); ?></td>
                            </tr>

			                <?php foreach ( $list as $item ) { ?>
                                <tr>
                                    <td style="text-align: left">
                                        <a href="<?php echo $item['browser']['link']; ?>" title="<?php echo $item['browser']['name']; ?>"><img src="<?php echo $item['browser']['logo']; ?>" alt="<?php echo $item['browser']['name']; ?>" class="log-tools" title="<?php echo $item['browser']['name']; ?>"/></a>
                                    </td>
					                <?php if ( WP_STATISTICS\GeoIP::active() ) { ?>
                                        <td style="text-align: left">
                                            <img src="<?php echo $item['country']['flag']; ?>" alt="<?php echo $item['country']['name']; ?>" title="<?php echo $item['country']['name']; ?>" class="log-tools"/>
                                        </td>
					                <?php } ?>
					                <?php if ( WP_STATISTICS\GeoIP::active( 'city' ) ) { ?>
                                        <td><?php echo $item['city']; ?></td>
					                <?php } ?>
                                    <td style='text-align: left'><span><?php echo $item['date']; ?></span></td>
                                    <td style='text-align: left'><?php echo( isset( $item['hash_ip'] ) ? $item['hash_ip'] : "<a href='" . $item['ip']['link'] . "' class='wps-text-danger'>" . $item['ip']['value'] . "</a>" ); ?></td>
                                    <td style='text-align: left'><?php echo $item['referred']; ?></td>
                                </tr>
			                <?php } ?>
                        </table>
	                <?php } ?>
                </div>
            </div>
			<?php echo isset( $pagination ) ? $pagination : ''; ?>
        </div>
    </div>
</div>