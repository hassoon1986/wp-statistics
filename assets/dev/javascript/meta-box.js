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
 * Default Circle PlaceHolder
 */
wps_js.rectangle_placeholder = function (cls = '') {
    return `
<div class="wps-ph-item` + (cls.length > 0 ? ' ' + cls : '') + `">
    <div class="wps-ph-col-12">
        <div class="wps-ph-picture"></div>
    </div>
</div>
`;
};

/**
 * Show No Data Error if Meta Box is Empty
 */
wps_js.no_meta_box_data = function () {
    return 'NO Data';
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

    // Gutenberg Button Style
    let gutenberg_style = 'z-index: 9999;position: absolute;top: 1px;';

    // Clean Button
    jQuery("#" + wps_js.getMetaBoxKey(key) + " button[class*=wps-refresh], #" + wps_js.getMetaBoxKey(key) + " button[class*=wps-more]").remove();

    // Add Refresh Button
    jQuery(`<button class="handlediv button-link wps-refresh"` + (wps_js.is_active('gutenberg') ? ` style="${gutenberg_style}right: 3%;" ` : '') + ` type="button"><span class="dashicons dashicons-update"></span> <span class="screen-reader-text">` + wps_js._('reload') + `</span></button>`).insertAfter(selector);

    // Check Page Url Button
    if ("page_url" in meta_box_info) {
        jQuery(`<button class="handlediv button-link wps-more"` + (wps_js.is_active('gutenberg') ? ` style="${gutenberg_style}right: 7%;" ` : '') + ` type="button" onclick="location.href = '` + meta_box_info.page_url + `';"><span class="dashicons dashicons-external"></span> <span class="screen-reader-text">` + wps_js._('more_detail') + `</span></button>`).insertAfter("#" + wps_js.getMetaBoxKey(key) + " button[class*=wps-refresh]");
    }
};

/**
 * Run Meta Box
 *
 * @param key
 * @param params
 * @param button
 */
wps_js.run_meta_box = function (key, params = false, button = true) {

    // Check Exist Meta Box div
    if (wps_js.exist_tag("#" + wps_js.getMetaBoxKey(key)) && (wps_js.is_active('gutenberg') || (!wps_js.is_active('gutenberg') && jQuery("#" + wps_js.getMetaBoxKey(key)).is(":visible")))) {

        // Meta Box Main
        let main = jQuery(wps_js.meta_box_inner(key));

        // Get Meta Box Method
        let method = wps_js.get_meta_box_method(key);

        // Check Exist Method name
        if (method in wps_js) {

            // Check PlaceHolder Method
            if ("placeholder" in wps_js[method]) {
                main.html(wps_js[method]["placeholder"]());
            } else {
                main.html(wps_js.placeholder());
            }

            // Add Custom Button
            if (button === true) {
                wps_js.meta_box_button(key);
            }

            // Get Meta Box Data
            let arg = {'name': key};
            if (params !== false) {
                arg = Object.assign(params, arg);
            }

            // Check Request Params in Meta box
            if ("params" in wps_js[method]) {
                arg = Object.assign(arg, wps_js[method]['params']());
            }

            // Run
            wps_js.ajaxQ('metabox', arg, method, 'error_meta_box_data');
        }
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
 *
 * @see wp-admin/js/postbox.js:107
 */
jQuery(document).on('mouseenter mouseleave', '.wps-refresh, .wps-more', function (ev) {
    if (ev.type === 'mouseenter') {
        wps_js.wordpress_postbox_ajax('disable');
    } else {
        wps_js.wordpress_postbox_ajax('enable');
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

/**
 * Show Select Date Time For Chart MetaBox
 */
wps_js.btn_group_chart = function (chart, args = false) {

    // Datetime Select List
    let select_list = {
        7: wps_js._('str_week'),
        30: wps_js._('str_month'),
        365: wps_js._('str_year')
    };

    // Check Active time
    var active;
    if (args.type == "ago") {
        active = parseInt(args.days);
    }

    // Create Html Data
    let html = `<div class="wps-btn-group"><div class="btn-group" role="group">`;

    // Show Data
    Object.keys(select_list).forEach(function (key) {
        html += `<button type="button" class="btn ` + (key == active ? 'btn-primary' : 'btn-default') + `" data-chart-time="${chart}" data-time="${key}">${select_list[key]}</button>`;
    });

    // Add Custom
    html += `<button type="button" class="btn ` + (args.type == "between" ? 'btn-primary' : 'btn-default') + `" data-custom-date-picker="${chart}">${wps_js._('custom')}</button>`;
    html += `</div></div>`;

    // Show Jquery Date Picker
    html += `
    <div data-chart-date-picker="${chart}"` + (args.type == "ago" ? ' style="display:none;"' : '') + `>
        <input type="text" size="18" name="date-from" data-wps-date-picker="from" value="${args['from']}" placeholder="YYYY-MM-DD" autocomplete="off">
        ` + wps_js._('to') + `
        <input type="text" size="18" name="date-to" data-wps-date-picker="to" value="${args['to']}" placeholder="YYYY-MM-DD" autocomplete="off">
        <input type="submit" value="` + wps_js._('go') + `" data-between-chart-show="${chart}" class="button-primary">
        <input type="hidden" name="" id="date-from" value="${args['from']}">
        <input type="hidden" name="" id="date-to" value="${args['to']}">
    </div>
    `;

    // Show HTMl
    return html;
};

/**
 * Seat Active Class after Click Btn Group
 */
jQuery(document).on("click", '.wps-btn-group button', function () {
    jQuery('.wps-btn-group button').attr('class', 'btn btn-default');
    jQuery(this).attr('class', 'btn btn-primary');
});

/**
 * SlideToggle Click on Custom Date Range
 */
jQuery(document).on("click", 'button[data-custom-date-picker]', function () {
    jQuery('div[data-chart-date-picker= ' + jQuery(this).attr('data-custom-date-picker') + ']').slideDown();
});

/**
 * Button Group Handle Chart time Show
 */
jQuery(document).on("click", 'button[data-chart-time]', function () {
    wps_js.run_meta_box(jQuery(this).attr('data-chart-time'), {'ago': jQuery(this).attr('data-time')});
});

/**
 * Send From/To Chart
 */
jQuery(document).on("click", 'input[data-between-chart-show]', function () {
    let chart = jQuery(this).attr('data-between-chart-show');
    wps_js.run_meta_box(chart, {
        'from': jQuery("div[data-chart-date-picker=" + chart + "] input[id=date-from]").val(),
        'to': jQuery("div[data-chart-date-picker=" + chart + "] input[id=date-to]").val()
    });
});