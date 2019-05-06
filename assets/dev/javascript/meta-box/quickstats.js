wps_js.quickstats_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<table width="100%" class="widefat table-stats wps-summary-stats"><tbody>`;

        //Summary Statistics
        t += wps_js.summary_meta_box.summary_statistics(args);

        t += `</tbody></table>`;
        t += `<br><hr width="80%"/><br>`;

        // Show Chart JS
        t += `<canvas id="wp-statistics-quickstats-meta-box-chart" height="200"></canvas>`;
        return t;
    },

    meta_box_init: function (args = []) {

        let datasets = [];
        if (wps_js.is_active('visitors')) {
            datasets.push({
                label: wps_js._('visitors'),
                data: args['hits-chart']['visitors'],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                fill: true
            });
        }
        if (wps_js.is_active('visits')) {
            datasets.push({
                label: wps_js._('visits'),
                data: args['hits-chart']['visits'],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                fill: true
            });
        }
        wps_js.line_chart("wp-statistics-quickstats-meta-box-chart", args['hits-chart']['title'], args['hits-chart']['date'], datasets);
    }

};