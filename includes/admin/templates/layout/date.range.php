<ul class="subsubsub wp-statistics-sub-fullwidth">
	<?php
	foreach ( $DateRang['list'] as $number_days => $value ) {
		?>
        <li class="all">
            <a <?php if ( $value['active'] === true ) { ?> class="current" <?php } ?> href="<?php echo $value['link']; ?>"><?php echo $value['title']; ?></a>
        </li> |
	<?php } ?>

    <!-- Show JQuery DatePicker -->
	<?php _e( 'Time Frame', 'wp-statistics' ); ?>:
    <form action="<?php echo admin_url('admin.php'); ?>" method="get" class="wps-inline" id="jquery-datepicker">

        <!-- Set Page name To Form -->
        <input name="page" type="hidden" value="<?php echo $pageName; ?>">

        <!-- set Page Pagination To Form -->
		<?php if ( $pagination > 1 ) { ?>
            <input name="<?php echo \WP_STATISTICS\Admin_Template::$paginate_link_name; ?>" type="hidden" value="<?php echo $pagination; ?>">
		<?php } ?>

        <!-- Set Jquery DatePicker -->
        <input type="text" size="18" name="date-from" data-wps-date-picker="from" value="<?php echo $DateRang['from']; ?>" placeholder="YYYY-MM-DD" autocomplete="off">
		<?php _e( 'to', 'wp-statistics' ); ?>
        <input type="text" size="18" name="date-to" data-wps-date-picker="to" value="<?php echo $DateRang['to']; ?>" placeholder="YYYY-MM-DD" autocomplete="off">
        <input type="submit" value="<?php _e( 'Go', 'wp-statistics' ); ?>" class="button-primary">
        <input type="hidden" name="<?php echo \WP_STATISTICS\Admin_Template::$request_from_date; ?>" id="date-from" value="<?php echo $DateRang['from']; ?>">
        <input type="hidden" name="<?php echo \WP_STATISTICS\Admin_Template::$request_to_date; ?>" id="date-to" value="<?php echo $DateRang['to']; ?>">
    </form>
    <script>
        jQuery('#jquery-datepicker').submit(function () {
            jQuery("input[data-wps-date-picker]").prop('disabled', true);
        });
    </script>
</ul>
