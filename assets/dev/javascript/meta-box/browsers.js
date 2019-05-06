wps_js.browsers_meta_box = {

    placeholder: function () {
        return wps_js.circle_placeholder();
    },

    view: function (args = []) {
        return '<canvas id="wp-statistics-browsers-meta-box-chart" height="220"></canvas>';
    },

    meta_box_init: function (args = []) {

        // Prepare Data
        let data = [{
            label: wps_js._('browsers'),
            data: args['browsers_value'],
            backgroundColor: args['browsers_color'],
        }];

        // Label Callback
        let label_callback = function (tooltipItem, data) {
            let dataset = data.datasets[tooltipItem.datasetIndex];
            let total = dataset.data.reduce(function (previousValue, currentValue, currentIndex, array) {
                return previousValue + currentValue;
            });
            let currentValue = dataset.data[tooltipItem.index];
            let percentage = Math.floor(((currentValue / total) * 100) + 0.5);
            return percentage + "% - " + data.labels[tooltipItem.index];
        };

        // Show Chart
        wps_js.bar_chart("wp-statistics-browsers-meta-box-chart", args['browsers_name'], data, label_callback);
    }

};