if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "overview") {

    // Show ADS
    if (wps_js.isset(wps_js.global, 'overview', 'ads')) {
        let PostBox = `
            <div id="wps_overview_ads_postbox" class="postbox">
            <div class="inside">
                <div class="close-overview-ads">
                <span class="dashicons dashicons-dismiss"></span>
                </div>
                    <a href="${wps_js.global.overview.ads['link']}" title="${wps_js.global.overview.ads['title']}" ${(wps_js.global.overview.ads['_target'] == "yes" ? ' target="_blank"' : '')}>
                    <img src="${wps_js.global.overview.ads['image']}" alt="${wps_js.global.overview.ads['title']}">
                    </a>
                </div>
            </div>`;
        jQuery(PostBox).insertAfter("#wps-postbox-container-2 #normal-sortables div.postbox:first");

        // Add Click Close Event
        jQuery(document).on('click', '.close-overview-ads', function () {
            jQuery("#wps_overview_ads_postbox").fadeOut("normal");
            jQuery.ajax({
                url: wps_js.global.ajax_url,
                type: 'get',
                data: {
                    'action': 'wp_statistics_close_overview_ads',
                    'ads_id': '' + wps_js.global.overview.ads["ID"] + '',
                    'wps_nonce': '' + wps_js.global.rest_api_nonce + ''
                },
                datatype: 'json'
            });
        });

        // Add Click Close Donate Notice
        jQuery('#wps-donate-notice').on('click', '.notice-dismiss', function () {
            jQuery.ajax({
                url: wps_js.global.ajax_url,
                type: 'get',
                data: {
                    'action': 'wp_statistics_close_notice',
                    'notice': 'donate',
                    'wps_nonce': '' + wps_js.global.rest_api_nonce + ''
                },
                datatype: 'json',
            });
        });
    }

}