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
wps_js.get_meta_box_method= function (meta_box) {
    return this.sanitize_meta_box_name(meta_box) + '_meta_box';
};

/**
 * Default PlaceHolder if Custom MetaBox have not this Method
 */
wps_js.placeholder= function () {
};

/**
 * Show No Data Error if Meta Box is Empty
 */
wps_js.no_data= function () {

};