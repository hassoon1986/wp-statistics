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
 * Default Circle PlaceHolder
 */
wps_js.circle_placeholder = function () {
    return `
<div class="wps-ph-item">
    <div class="wps-ph-col-2"></div>
    <div class="wps-ph-col-8">
        <div class="wps-ph-avatar"></div>
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
    if (key in wps_js.global.meta_boxes) {
        return wps_js.global.meta_boxes[key];
    }
    return [];
};

/**
 * Get MetaBox Lang
 */
wps_js.meta_box_lang = function (meta_box, lang) {
    if (lang in wps_js.global.meta_boxes[meta_box]['lang']) {
        return wps_js.global.meta_boxes[meta_box]['lang'][lang];
    }
    return '';
};

/**
 * Get MetaBox inner text selector
 */
wps_js.meta_box_inner = function (key) {
    return "#" + wps_js.getMetaBoxKey(key) + " div.inside";
};

/**
 * Get MetaBox name by tag ID
 * ex: wp-statistics-summary-widget -> summary
 */
wps_js.meta_box_name_by_id = function (ID) {
    return ID.split('statistics-').pop().split('-widget')[0];
};

/**
 * Create Custom Button for Meta Box
 */
wps_js.meta_box_button = function (key) {
    let selector = "#" + wps_js.getMetaBoxKey(key) + " button[class=handlediv]";
    let meta_box_info = wps_js.get_meta_box_info(key);

    // Clean Button
    jQuery("#" + wps_js.getMetaBoxKey(key) + " button[class*=wps-refresh], #" + wps_js.getMetaBoxKey(key) + " button[class*=wps-more]").remove();

    // Add Refresh Button
    jQuery(`<button class="handlediv button-link wps-refresh" type="button"><span class="dashicons dashicons-update"></span> <span class="screen-reader-text">` + wps_js._('reload') + `</span></button>`).insertAfter(selector);

    // Check Page Url Button
    if ("page_url" in meta_box_info) {
        jQuery(`<button class="handlediv button-link wps-more" type="button" onclick="location.href = '` + meta_box_info.page_url + `';"><span class="dashicons dashicons-external"></span> <span class="screen-reader-text">` + wps_js._('more_detail') + `</span></button>`).insertAfter("#" + wps_js.getMetaBoxKey(key) + " button[class*=wps-refresh]");
    }
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

        // Add Custom Button
        wps_js.meta_box_button(key);

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
        list = Object.keys(wps_js.global.meta_boxes);
    }
    list.forEach(function (value) {
        wps_js.run_meta_box(value);
    });
};

/**
 * Disable Close WordPress Post ox for Meta Box Button
 */
jQuery(document).on('mouseenter mouseleave', '.wps-refresh, .wps-more', function (ev) {
    let wordpress_postbox = jQuery('.postbox h3, .postbox .handlediv');
    if (ev.type === 'mouseenter') {
        wordpress_postbox.unbind('click.postboxes');
    } else {
        wordpress_postbox.bind('click.postboxes');
    }
});

/**
 * Meta Box Refresh Click Handler
 */
jQuery(document).on("click", '.wps-refresh', function (e) {
    e.preventDefault();

    // Get Meta Box name By Parent ID
    let parentID = jQuery(this).parent(".postbox").attr("id");
    let meta_box_name = wps_js.meta_box_name_by_id(parentID);

    // Run Meta Box
    wps_js.run_meta_box(meta_box_name);
});

/**
 * Watch Show/Hide Meta Box in WordPress Dashboard
 * We dont Use PreventDefault Because WordPress Core use Checked checkbox.
 */
jQuery(document).on("click", 'input[type=checkbox][id^="wp-statistics-"][id$="-widget-hide"]', function () {

    // Check is Checked For Show Post Box
    if (jQuery(this).is(':checked')) {

        // Get Meta Box name By ID
        let ID = jQuery(this).attr("id");
        let meta_box_name = wps_js.meta_box_name_by_id(ID);

        // Run Meta Box
        wps_js.run_meta_box(meta_box_name);
    }
});
