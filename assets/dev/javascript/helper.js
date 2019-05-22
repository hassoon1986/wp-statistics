/**
 * Check Exist Dom
 */
wps_js.exist_tag = function (tag) {
    return (jQuery(tag).length);
};

/**
 * Jquery UI Picker
 */
wps_js.date_picker = function (input, mask) {
    if (jQuery.fn.datepicker && typeof wps_i18n_jquery_datepicker !== 'undefined') {
        jQuery("input[data-wps-date-picker]").datepicker({
            monthNames: wps_i18n_jquery_datepicker.monthNames,
            monthNamesShort: wps_i18n_jquery_datepicker.monthNamesShort,
            dayNames: wps_i18n_jquery_datepicker.dayNames,
            dayNamesShort: wps_i18n_jquery_datepicker.dayNamesShort,
            dayNamesMin: wps_i18n_jquery_datepicker.dayNamesMin,
            dateFormat: wps_i18n_jquery_datepicker.dateFormat,
            firstDay: wps_i18n_jquery_datepicker.firstDay,
            isRTL: wps_i18n_jquery_datepicker.isRTL,
            onSelect: function (selectedDate) {
                let ID = $(this).attr("data-wps-date-picker");
                if (selectedDate.length > 0) {
                    $("input[id=date-" + ID + "]").val(selectedDate);
                }
            }
        });
    }
};

/**
 * Redirect To Custom Url
 *
 * @param url
 */
wps_js.redirect = function (url) {
    window.location.replace(url);
};

/**
 * Create Line Chart JS
 */
wps_js.line_chart = function (tag_id, title, label, data) {

    // Get Element By ID
    let ctx = document.getElementById(tag_id).getContext('2d');

    // Check is RTL Mode
    if (wps_js.is_active('rtl')) {
        Chart.defaults.global.defaultFontFamily = "tahoma";
    }

    // Create Chart
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: label,
            datasets: data
        },
        options: {
            responsive: true,
            legend: {
                position: 'bottom',
            },
            animation: {
                duration: 0,
            },
            title: {
                display: true,
                text: title
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
};

/**
 * Create Bar Chart JS
 */
wps_js.bar_chart = function (tag_id, label, data, label_callback) {

    // Get Element By ID
    let ctx = document.getElementById(tag_id).getContext('2d');

    // Check is RTL Mode
    if (wps_js.is_active('rtl')) {
        Chart.defaults.global.defaultFontFamily = "tahoma";
    }

    // Create Chart
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: label,
            datasets: data
        },
        options: {
            responsive: true,
            legend: {
                position: 'bottom',
            },
            animation: {
                duration: 0,
            },
            tooltips: {
                callbacks: {
                    label: label_callback
                }
            }
        }
    });
};

/**
 * Create Chart ID by Meta Box name
 *
 * @param meta_box
 */
wps_js.chart_id = function (meta_box) {
    return 'wp-statistics-' + meta_box + '-meta-box-chart';
};

/**
 * Show Domain Icon
 */
wps_js.site_icon = function (domain) {
    return `<img src="https://www.google.com/s2/favicons?domain=${domain}" width="16" height="16" alt="${domain}" style="vertical-align: -3px;" />`;
};

/**
 * Enable/Disable WordPress Admin PostBox Ajax Request
 *
 * @param type
 */
wps_js.wordpress_postbox_ajax = function (type = 'enable') {
    let wordpress_postbox = jQuery('.postbox .hndle, .postbox .handlediv');
    if(type ==='enable') {
        wordpress_postbox.on('click', window.postboxes.handle_click);
    } else {
        wordpress_postbox.off('click', window.postboxes.handle_click);
    }
};