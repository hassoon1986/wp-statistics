wps_js.browsers_meta_box = {

    placeholder: function () {
        return wps_js.circle_placeholder();
    },

    view: function (args = []) {
        return '<canvas id="' + wps_js.chart_id('browsers') + '" height="220"></canvas>';
    },

    meta_box_init: function (args = []) {

        // Get Background Color
        let backgroundColor = [];
        let color;
        for (let i = 0; i <= 10; i++) {
            color = wps_js.random_color(i);
            backgroundColor.push('rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + '0.4)');
        }

        // Prepare Data
        let data = [{
            label: wps_js._('browsers'),
            data: args['browsers_value'],
            backgroundColor: backgroundColor
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
        wps_js.bar_chart(wps_js.chart_id('browsers'), args['browsers_name'], data, label_callback);
    }

};