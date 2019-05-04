/** Set AjaxQ Option */
wps_js.ajax_queue = {
    key: 'wp-statistics'
};

/**
 * Base AjaxQ function For All request
 *
 * @param url
 * @param params
 * @param callback
 * @param type
 */
wps_js.ajaxQ = function (url, params, callback, type = 'GET') {

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
            wps_js[callback](data);
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
};