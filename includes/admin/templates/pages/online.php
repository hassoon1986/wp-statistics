<div class="wrap wps-wrap">
	<?php
	WP_STATISTICS\Admin_Templates::show_page_title( $page_title );

	if ( ! is_array( $user_online_list ) ) {
		$content = "<div class='wps-center'>" . $user_online_list . "</div>";
	} else {
		$content = '<table width="100%" class="widefat table-stats"><tr>';
		$content .= '<td>' . __( 'Browser', 'wp-statistics' ) . '</td>';
		if ( WP_STATISTICS\GeoIP::active() ) {
			$content .= "<td>" . __( 'Country', 'wp-statistics' ) . "</td>";
		}
		if ( WP_STATISTICS\GeoIP::active( 'city' ) ) {
			$content .= "<td>" . __( 'City', 'wp-statistics' ) . "</td>";
		}
		$content .= "<td>" . __( 'IP', 'wp-statistics' ) . "</td>";
		$content .= "<td>" . __( 'Online For', 'wp-statistics' ) . "</td>";
		$content .= "<td>" . __( 'Page', 'wp-statistics' ) . "</td>";
		$content .= "<td>" . __( 'Referrer', 'wp-statistics' ) . "</td>";
		$content .= "<td></td>";
		$content .= "</tr>";

		foreach ( $user_online_list as $item ) {

			$content .= "<tr>";
			$content .= '<td style="text-align: left"><a href="' . $item['browser']['link'] . '" title="' . $item['browser']['name'] . '"><img src="' . $item['browser']['logo'] . '" alt="' . $item['browser']['name'] . '" class="log-tools" title="' . $item['browser']['name'] . '"/></a></td>';
			if ( WP_STATISTICS\GeoIP::active() ) {
				$content .= '<td style="text-align: left"><img src="' . $item['country']['flag'] . '" alt="' . $item['country']['name'] . '" title="' . $item['country']['name'] . '" class="log-tools"/></td>';
			}
			if ( WP_STATISTICS\GeoIP::active( 'city' ) ) {
				$content .= '<td>' . $item['city'] . '</td>';
			}
			$content .= "<td style='text-align: left'>" . ( isset( $item['hash_ip'] ) ? $item['hash_ip'] : "<a href='" . $item['ip']['link'] . "'>" . $item['ip']['value'] . "</a>" ) . "</td>";
			$content .= "<td style='text-align: left'><span>" . $item['online_for'] . "</span></td>";
			$content .= "<td style='text-align: left'>" . ( $item['page']['link'] != '' ? '<a href="' . $item['page']['link'] . '" target="_blank" class="wps-text-danger">' : '' ) . $item['page']['title'] . ( $item['page']['link'] != '' ? '</a>' : '' ) . "</td>";
			$content .= "<td style='text-align: left'>" . $item['referred'] . "</td>";
			$content .= "<td style='text-align: center'>" . ( isset( $item['map'] ) ? "<a class='wps-text-muted' href='" .  $item['ip']['link'] . "'>" . WP_STATISTICS\Admin_Templates::icons( 'dashicons-visibility', 'visibility' ) . "</a><a class='show-map wps-text-muted' href='".$item['map']."' target='_blank' title='" . __( 'Map', 'wp-statistics' ) . "'>" . WP_STATISTICS\Admin_Templates::icons( 'dashicons-location-alt', 'map' ) . "</a>" : "" ) . "</td>";
			$content .= '</tr>';
		}

		$content .= "</table>";
	}

	WP_STATISTICS\Admin_Templates::PostBox( $page_title, $content, $pagination );
	?>
</div>