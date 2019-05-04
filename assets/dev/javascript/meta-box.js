/**
 * Sanitize MetaBox name
 *
 * @param meta_box
 * @returns {*|void|string|never}
 */
wps_js.sanitize_meta_box_name = function (meta_box) {
    return (meta_box.replace("-", "_"));
};

/**
 * Get Meta Box Method name
 */
wps_js.get_meta_box_method = function (meta_box) {
    return this.sanitize_meta_box_name(meta_box) + '_meta_box';
};

/**
 * Get Meta Box Tags ID
 */
wps_js.getMetaBoxKey = function (key) {
    return 'wp-statistics-' + key + '-widget';
};

/**
 * Default PlaceHolder if Custom MetaBox have not this Method
 */
wps_js.placeholder = function () {
    return `
    <div class="wps-ph-item">
    <div class="wps-ph-col-12">
        <div class="wps-ph-picture"></div>
        <div class="wps-ph-row">
            <div class="wps-ph-col-6 big"></div>
            <div class="wps-ph-col-4 empty big"></div>
            <div class="wps-ph-col-2 big"></div>
            <div class="wps-ph-col-4"></div>
            <div class="wps-ph-col-8 empty"></div>
            <div class="wps-ph-col-6"></div>
            <div class="wps-ph-col-6 empty"></div>
            <div class="wps-ph-col-12"></div>
        </div>
    </div>
</div>
`;
};

/**
 * Show No Data Error if Meta Box is Empty
 */
wps_js.no_meta_box_data = function () {

};

/**
 * Show Error Connection if Meta Box is Empty
 */
wps_js.error_meta_box_data = function (text) {
    return 'error dad';
};

/**
 * Get MetaBox information by key
 */
wps_js.get_meta_box_info = function (key) {
    if (key in wps_js.i18n.meta_boxes) {
        return wps_js.i18n.meta_boxes[key];
    }
    return [];
};

/**
 * Get MetaBox inner text selector
 */
wps_js.meta_box_inner = function (key) {
    return "#" + wps_js.getMetaBoxKey(key) + " div.inside";
};

/**
 * Run Meta Box
 *
 * @param key
 * @param params
 */
wps_js.run_meta_box = function (key, params = false) {

    // Check Exist Meta Box div
    if (wps_js.exist_tag("#" + wps_js.getMetaBoxKey(key)) && jQuery("#" + wps_js.getMetaBoxKey(key)).is(":visible")) {

        // Meta Box Main
        let main = jQuery(wps_js.meta_box_inner(key));

        // Get Meta Box Method
        let method = wps_js.get_meta_box_method(key);

        // Check PlaceHolder Method
        if ("placeholder" in wps_js[method]) {
            main.html(wps_js[method]["placeholder"]());
        } else {
            main.html(wps_js.placeholder());
        }

        // Get Meta Box Data
        let arg = {'name': key};
        if (params !== false) {
            arg = jQuery.extend({}, params, arg);
        }
        wps_js.ajaxQ('metabox', arg, method, 'error_meta_box_data');
    }
};

/**
 * Load all Meta Boxes
 */
wps_js.run_meta_boxes = function (list = false) {
    if (list === false) {
        list = Object.keys(wps_js.i18n.meta_boxes);
    }
    list.forEach(function (value) {
        wps_js.run_meta_box(value);
    });
};