wps_js.quickstats_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<table width="100%" class="widefat table-stats wps-summary-stats"><tbody>`;

        // Show Visitor Online
        if (args['user_online']) {
            t += `<tr>
                    <th>${wps_js._('online_users')}:</th>
                    <th colspan="2" id="th-colspan"><span><a href="${args['user_online']['link']}">${args['user_online']['value']}</a></span></th>
                </tr>`;
        }

        // Show Visitors and Visits
        if (wps_js.is_active('visitors') || wps_js.is_active('visits')) {
            t += `<tr><th width="60%"></th>`;
            ["visitors", "visits"].forEach(function (key) {
                t += `<th class="th-center">` + (wps_js.is_active(key) ? wps_js._(key) : ``) + `</th>`;
            });
            t += `</tr>`;

            // Show Statistics in Days
            let summary_item = ["today", "yesterday", "week", "month", "year", "total"];
            for (let i = 0; i < summary_item.length; i++) {
                t += `<tr><th>${wps_js._(summary_item[i])}: </th>`;
                ["visitors", "visits"].forEach(function (key) {
                    t += `<th class="th-center">` + (wps_js.is_active(key) ? `<a href="${args[key][summary_item[i]]['link']}"><span>${args[key][summary_item[i]]['value']}</span></a>` : ``) + `</th>`;
                });
                t += `</tr>`;
            }

        }

        t += `</tbody></table>`;
        t += `<br><hr width="80%"/><br>`;

        // Show Chart JS
        t += `<canvas id="wp-statistics-quickstats-meta-box-chart" height="200"></canvas>`;
        return t;
    },

    meta_box_init: function (args = []) {

        let ctx = document.getElementById("wp-statistics-quickstats-meta-box-chart").getContext('2d');
        if (wps_js.is_active('rtl')) {
            Chart.defaults.global.defaultFontFamily = "tahoma";
        }
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
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: args['hits-chart']['date'],
                datasets: datasets
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
                    text: args['hits-chart']['title']
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
    }

};