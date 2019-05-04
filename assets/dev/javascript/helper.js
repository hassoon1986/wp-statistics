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
            dateFormat: this.i18n.date_format.jquery_ui,
            onSelect: function (selectedDate) {
                let ID = $(this).attr("wps-date-picker");
                if (selectedDate.length > 0) {
                    $("input[id=date-" + ID + "]").val(moment(selectedDate, wps_js.i18n.date_format.moment_js).format('YYYY-MM-DD'));
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
wps_js.redirect = function(url) {
    window.location.replace(url);
};