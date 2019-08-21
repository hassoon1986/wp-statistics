<table class="form-table">
    <tbody>

    <tr valign="top">
        <th scope="row" colspan="2"><h3><?php _e( 'WP-CLI', 'wp-statistics' ); ?></h3></th>
    </tr>

    <tr valign="top">
        <th scope="row"><label for="wps-wp_cli"><?php _e( 'Enable WP-CLI:', 'wp-statistics' ); ?></label>
        </th>
        <td>
            <input id="wps-wp_cli" type="checkbox" value="1" name="wps_wp_cli" <?php echo WP_STATISTICS\Option::get( 'wp_cli' ) == true ? "checked='checked'" : ''; ?>>
            <label for="wps-wp_cli"><?php _e( 'Enable', 'wp-statistics' ); ?></label>
            <p class="description"><?php echo __( 'This feature enables you to get WP-Statistics reporting in the WP-CLI.', 'wp-statistics' ); ?></p>
        </td>
    </tr>




    </tbody>
</table>

<?php submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' );