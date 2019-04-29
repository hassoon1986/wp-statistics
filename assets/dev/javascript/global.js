/* Start Wp-statistics Admin Js */
var wps_js = {

    /**
     * Load WordPress Language
     */
    i18n: (typeof wps_i18n != 'undefined') ? wps_i18n : [],

    /**
     * WordPress Localize
     *
     * @param key
     * @returns {*}
     * @private
     */
    _: function (key) {
        return (key in this.i18n ? this.i18n[key] : '');
    },

    /**
     * Set AjaxQ Option
     */
    ajax_queue: {
        key: 'wp-statistics',
        time: 500 //MilliSecond
    },

    /**
     * Check Exist Dom
     */
    exist_tag: function (tag) {
        return ($(tag).length);
    },

    /**
     * Jquery UI Picker
     */
    date_picker: function (input, mask) {
        if ($.fn.datepicker) {
            $("input[wps-date-picker]").datepicker({
                dateFormat: this.i18n.date_format.jquery_ui,
                onSelect: function (selectedDate) {
                    let ID = $(this).attr("wps-date-picker");
                    if (selectedDate.length > 0) {
                        $("input[id=date-" + ID + "]").val(moment(selectedDate, wps_js.i18n.date_format.moment_js).format('YYYY-MM-DD'));
                    }
                }
            });
        }
    },


    /**
     * Run Application
     */
    run: function () {


        // Load Jquery Date Picker in WP-Statistics Admin
        this.date_picker();
    }

};

/* Run */
wps_js.run();