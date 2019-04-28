<?php
function wp_statistics_generate_map_postbox_content( $ISOCountryCode ) {

	global $wpdb;

	if ( WP_STATISTICS\Option::get( 'geoip' ) && ! WP_STATISTICS\Option::get( 'disable_map' ) ) { ?>
        <div id="map_canvas"></div>

		<?php
		$current_date = \WP_STATISTICS\TimeZone::getCurrentDate( 'Y-m-d' );
		$result       = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE last_counter = '{$current_date}'" );
		?>
        <script type="text/javascript">
            var country_pin = Array();
            var country_color = Array();

            jQuery(document).ready(function () {

				<?php
				$result = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE last_counter = '" . \WP_STATISTICS\Timezone::getCurrentDate( 'Y-m-d' ) . "'" );
				$final_result = array();
				$final_result[\WP_STATISTICS\GeoIP::$private_country] = array();

				//Load City Geoip
				$geoip_reader = false;
				if ( WP_STATISTICS\Option::get( 'geoip_city' ) ) {
					$geoip_reader = \WP_STATISTICS\GeoIP::Loader( 'city' );
				}

				if ( $result ) {
					foreach ( $result as $new_r ) {
						$new_r->location = strtolower( $new_r->location );

						$final_result[ $new_r->location ][] = array
						(
							'location' => $new_r->location,
							'agent'    => $new_r->agent,
							'ip'       => $new_r->ip,
						);
					}
				}

				$final_total = count( $result ) - count( $final_result[\WP_STATISTICS\GeoIP::$private_country] );

				unset( $final_result[\WP_STATISTICS\GeoIP::$private_country] );

				$startColor = array( 200, 238, 255 );
				$endColor = array( 0, 100, 145 );

				foreach($final_result as $items) {

				foreach ( $items as $markets ) {

					if ( $markets['location'] == \WP_STATISTICS\GeoIP::$private_country ) {
						continue;
					}

					$flag = "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . strtoupper( $markets['location'] ) . '.png' ) . "' title='{$ISOCountryCode[strtoupper($markets['location'])]}' class='log-tools'/> {$ISOCountryCode[strtoupper($markets['location'])]}";

					if ( array_search( strtolower( $markets['agent'] ), wp_statistics_get_browser_list( 'key' ) ) !== false ) {
						$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $markets['agent'] . ".png' class='log-tools' title='{$markets['agent']}'/>";
					} else {
						$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/unknown.png' ) . "' class='log-tools' title='{$markets['agent']}'/>";
					}

					if ( \WP_STATISTICS\IP::IsHashIP( $markets['ip'] ) ) {
						$markets['ip'] = \WP_STATISTICS\IP::$hash_ip_prefix;
					}

					$city = '';
					if ( $geoip_reader != false ) {
						try {
							$reader = $geoip_reader->city( $markets['ip'] );
							$city   = $reader->city->name;
						} catch ( Exception $e ) {
							$city = __( 'Unknown', 'wp-statistics' );
						}
					}
					if ( $city != "" ) {
						$city = ' - ' . $city;
					}

					$get_ipp[ $markets['location'] ][] = "<p>{$agent} {$markets['ip']} {$city}</p>";
				}

				$market_total = count( $get_ipp[ $markets['location'] ] );
				$last_five = "";

				// Only show the last five visitors, more just makes the map a mess.
				for ( $i = $market_total; $i > $market_total - 6; $i -- ) {
					if ( array_key_exists( $i, $get_ipp[ $markets['location'] ] ) ) {
						$last_five .= $get_ipp[ $markets['location'] ][ $i ];
					}
				}

				$summary = ' [' . $market_total . ']';

				$color = sprintf( "#%02X%02X%02X", round( $startColor[0] + ( $endColor[0] - $startColor[0] ) * $market_total / $final_total ), round( $startColor[1] + ( $endColor[1] - $startColor[1] ) * $market_total / $final_total ), round( $startColor[2] + ( $endColor[2] - $startColor[2] ) * $market_total / $final_total ) );
				?>
                country_pin['<?php echo $markets['location'];?>'] = "<div class='map-html-marker'><?php echo $flag . $summary . '<hr />' . $last_five; ?></div>";
                country_color['<?php echo $markets['location'];?>'] = "<?php echo $color;?>";
				<?php
				}
				?>
                var data_total = <?php echo $final_total;?>;

                jQuery('#map_canvas').vectorMap({
                    map: 'world_en',
                    colors: country_color,
                    onLabelShow: function (element, label, code) {
                        if (country_pin[code] !== undefined) {
                            label.html(country_pin[code]);
                        } else {
                            label.html(label.html() + ' [0]<hr />');
                        }
                    },
                });


            });
        </script>
		<?php
	}
}