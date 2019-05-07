wps_js.hits_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {
        return '<canvas id="' + wps_js.chart_id('hits') + '" height="210"></canvas>';
    },

    meta_box_init: function (args = []) {
        this.hits_chart(wps_js.chart_id('hits'), args);
    },

    hits_chart: function (tag_id, args = []) {

        // Check Hit-chart for Quick State
        let params = args;
        if ('hits-chart' in args) {
            params = args['hits-chart'];
        }

        // Prepare Chart Data
        let datasets = [];
        if (wps_js.is_active('visitors')) {
            datasets.push({
                label: wps_js._('visitors'),
                data: params['visitors'],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                fill: true
            });
        }
        if (wps_js.is_active('visits')) {
            datasets.push({
                label: wps_js._('visits'),
                data: params['visits'],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                fill: true
            });
        }
        wps_js.line_chart(tag_id, params['title'], params['date'], datasets);
    }
};