if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "visitors") {

    // TickBox
    jQuery(document).on('click', "div#visitors-filter", function (e) {
        e.preventDefault();

        // Show
        tb_show('', '#TB_inline?&width=430&height=668&inlineId=visitors-filter-popup');

        // Add Content
        setTimeout(function () {

            var tickBox_DIV = "#wps-visitors-filter-form";
            if (!wps_js.exist_tag(tickBox_DIV + " input[type=submit]")) {

                // Set PlaceHolder
                jQuery(tickBox_DIV).html('<div style="height: 50px;"></div>' + wps_js.line_placeholder(5));

                // Create Params
                let params = {
                    'wps_nonce': wps_js.global.rest_api_nonce,
                    'action': 'wp_statistics_visitors_page_filters'
                };
                params = Object.assign(params, wps_js.global.request_params);

                // Create Ajax
                jQuery.ajax({
                    url: wps_js.global.ajax_url,
                    type: 'GET',
                    dataType: "json",
                    data: params,
                    timeout: 30000,
                    success: function (data) {

                        // Create Table
                        let html = '<table class="widefat">';

                        // Show List Select
                        let select = {
                            /**
                             * Key: global i18n
                             * [0]: select name
                             * [1]: data key from ajax
                             */
                            'browsers': ['agent', 'browsers'],
                            'country': ['location', 'location'],
                            'platform': ['platform', 'platform'],
                            'referrer': ['referrer', 'referrer'],
                            'user': ['user_id', 'users']
                        };

                        Object.keys(select).forEach((key) => {
                            html += `<tr><td>${wps_js._(key)}</td></tr>`;
                            html += `<tr><td><select name="${select[key][0]}" class="select2 wps-width-100" data-type-show="select2">`;
                            html += `<option value=''>${wps_js._('all')}</option>`;
                            Object.keys(data[select[key][1]]).forEach(function (item) {
                                html += `<option value='${item}' ${(data[select[key][1]][item]['active'] === true ? `selected` : ``)}>${data[select[key][1]][item]['title']}</option>`;
                            });
                            html += `</select></td></tr>`;
                        });

                        // Add IP
                        html += `<tr><td>${wps_js._('ip')}</td></tr>`;
                        html += `<tr><td><input name="ip" value="${data.ip}" class="wps-width-100" placeholder='xxx.xxx.xxx.xxx' autocomplete="off"></td></tr>`;

                        // Add Date
                        html += `<tr><td>${wps_js._('date')}</td></tr>`;
                        let input_date_style = 'width: calc(50% - 5px);display: inline-block;';
                        html += `<tr>
                            <td>
                                <div style="${input_date_style}">${wps_js._('from')}: <input name="date-from" data-wps-date-picker="from" value="${data.from}" style="width: calc(100% - 5px);" placeholder="YYYY-MM-DD" autocomplete="off"></div>
                                <div style="${input_date_style}">${wps_js._('to')}: <input name="date-to" data-wps-date-picker="to" value="${data.to}" style="width: 100%;" placeholder="YYYY-MM-DD" autocomplete="off"></div>
                                <input type="hidden" name="from" id="date-from" value="${data.from}">
                                <input type="hidden" name="to" id="date-to" value="${data.to}">
                            </td>
                            </tr>`;

                        // Submit Button
                        html += `<tr><td></td></tr>`;
                        html += `<tr><td><input type="submit" value="${wps_js._('filter')}" class="button-primary"> &nbsp; <span class="filter-loading"></span></td></tr>`;
                        html += `</table>`;
                        jQuery(tickBox_DIV).html(html);

                        // Set datePicker and Select 2
                        setTimeout(function () {
                            wps_js.date_picker();
                            wps_js.select2();
                        }, 200);
                    },
                    error: function (xhr, status, error) {
                        jQuery("span.tb-close-icon").click();
                    }
                });
            }
        }, 500);

    });

    // submit and disable empty value
    var FORM_ID = '#wp_statistics_visitors_filter_form';
    jQuery(document).on('submit', FORM_ID, function () {

        //Validate DatePicker
        var FROM_DATE = jQuery(FORM_ID + " input[name=date-from]");
        var TO_DATE = jQuery(FORM_ID + " input[name=date-to]");
        if ((FROM_DATE.val().length > 0 && TO_DATE.val().length < 1) || (FROM_DATE.val().length < 1 && TO_DATE.val().length > 1)) {
            alert(wps_js._('er_datepicker'));
            return false;
        }

        // Check IS IP
        var Input_IP = jQuery(FORM_ID + " input[name=ip]").val();
        if (Input_IP.length > 0 && wps_js.isIP(Input_IP) === false) {
            alert(wps_js._('er_valid_ip'));
            return false;
        }

        // Disable DatePicker
        jQuery("input[data-wps-date-picker]").prop('disabled', true);

        // Remove Empty Parameter
        let forms = {
            'input': ['date-from', 'date-to', 'ip'],
            'select': ['agent', 'platform', 'location', 'referrer']
        };
        Object.keys(forms).forEach(function (type) {
            forms[type].forEach((name) => {
                let input = jQuery(FORM_ID + " " + type + "[name=" + name + "]");
                if (input.val().length < 1) {
                    input.prop('disabled', true);
                    ['from', 'to'].forEach((key) => {
                        if (name == "date-" + key) {
                            jQuery(FORM_ID + " input[name=" + key + "]").prop('disabled', true);
                        }
                    });
                }
            });
        });

        // Show Loading
        jQuery("span.filter-loading").html(wps_js._('please_wait'));

        return true;
    });
}

// When close TickBox
//jQuery(window).bind('tb_unload', function () {});