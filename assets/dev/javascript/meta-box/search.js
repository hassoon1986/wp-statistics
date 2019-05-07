wps_js.search_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {
        return `<canvas id="wp-statistics-search-meta-box-chart" height="210"></canvas>`;
    },

    meta_box_init: function (args = []) {

        // Prepare Chart Data
        let datasets = [];
        Object.keys(args['search-engine']).forEach(function (key) {
            let search_engine_name = args['search-engine'][key]['name'];
            datasets.push({
                label: search_engine_name,
                data: args['stat'][search_engine_name],
                backgroundColor: 'rgba(' + args['search-engine'][key]['color'] + ', 0.2)',
                borderColor: 'rgba(' + args['search-engine'][key]['color'] + ', 1)',
                borderWidth: 1,
                fill: true
            });
        });

        // Set Total
        if (args['total']['active'] === 1) {
            datasets.push({
                label: wps_js._('total'),
                data: args['total']['stat'],
                backgroundColor: 'rgba(' + args['total']['color'] + ', 0.2)',
                borderColor: 'rgba(' + args['total']['color'] + ', 1)',
                borderWidth: 1,
                fill: true
            });
        }
        wps_js.line_chart("wp-statistics-search-meta-box-chart", args['title'], args['date'], datasets);
    },

};