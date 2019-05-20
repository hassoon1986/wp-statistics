<div class="metabox-holder" id="overview-widgets">
    <div class="postbox-container" id="wps-postbox-container-1">
		<?php do_meta_boxes( $overview_page_slug, 'side', '' ); ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
		<?php do_meta_boxes( $overview_page_slug, 'normal', '' ); ?>
    </div>
</div>

<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

<!-- OverView Page PostBox -->
<script type="text/javascript">
    jQuery(document).ready(function () {

        // close postboxes that should be closed
        jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

        // postboxes setup
        postboxes.add_postbox_toggles('<?php echo $overview_page_slug; ?>');

        // Donate Notice
        jQuery('#wps-donate-notice').on('click', '.notice-dismiss', function () {
            jQuery.ajax({
                url: ajaxurl,
                type: 'get',
                data: {
                    'action': 'wp_statistics_close_notice',
                    'notice': 'donate',
                },
                datatype: 'json',
            });
        });
    });
</script>

