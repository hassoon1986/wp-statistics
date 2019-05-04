/** Set AjaxQ Option */
wps_js.ajax_queue = {
    key: 'wp-statistics',
    time: 500 // millisecond
};

/**
 * Base AjaxQ function For All request
 *
 * @param url
 * @param params
 * @param callback
 * @param error_callback
 * @param type
 */
wps_js.ajaxQ = function (url, params, callback, error_callback, type = 'GET') {

    setTimeout(function () {

        // Check Url
        if (url === false || url === "metabox") {
            url = wps_js._('metabox_api');
        }

        // Query
        jQuery.ajaxq(wps_js.ajax_queue.key, {
            url: url,
            type: type,
            dataType: "json",
            cache: false,
            data: params,
            success: function (data) {

                // Check Meta Box URL
                if (url === wps_js._('metabox_api')) {

                    // Check is NO Data Meta Box
                    if (data['no_data']) {

                        jQuery(wps_js.meta_box_inner(params.name)).empty().html(wps_js.no_meta_box_data());
                    } else {

                        // Show Meta Box
                        jQuery(wps_js.meta_box_inner(params.name)).empty().html(wps_js[callback]['view'](data));
                    }
                } else {

                    // If Not Meta Box Ajax
                    wps_js[callback](data);
                }
            },
            error: function (xhr, status, error) {

                // Check Meta Box Error
                if (url === wps_js._('metabox_api')) {
                    jQuery(wps_js.meta_box_inner(params.name)).empty().html(wps_js[error_callback](xhr.responseText));
                } else {

                    // Global Call Back Error
                    wps_js[error_callback](xhr.responseText)
                }
            }
        });
    }, wps_js.ajax_queue.time);
};