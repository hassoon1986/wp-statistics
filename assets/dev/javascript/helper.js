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
    if (jQuery.fn.datepicker) {
        jQuery("input[wps-date-picker]").datepicker({
            dateFormat: this.global.date_format.jquery_ui,
            onSelect: function (selectedDate) {
                let ID = $(this).attr("wps-date-picker");
                if (selectedDate.length > 0) {
                    $("input[id=date-" + ID + "]").val(moment(selectedDate, wps_js.global.date_format.moment_js).format('YYYY-MM-DD'));
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
 * Show Domain Icon
 */
wps_js.site_icon = function (domain) {
    return `<img src="https://www.google.com/s2/favicons?domain=${domain}" width="16" height="16" alt="${domain}" style="vertical-align: -3px;" />`;
};