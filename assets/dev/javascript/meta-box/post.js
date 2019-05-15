wps_js.post_meta_box = {

    view: function (args = []) {
        return (args.hasOwnProperty('content') ? '<div class="wps-center" style="padding: 15px;"> ' + args['content'] + '</div>' : '<canvas id="' + wps_js.chart_id('post') + '" height="' + (wps_js.is_active('overview_page') ? 110 : 210) + '"></canvas>');
    },

    meta_box_init: function (args = []) {
        if (!args.hasOwnProperty('content')) {
            this.post_hits_chart(wps_js.chart_id('post'), args);
        }
    },

    post_hits_chart: function (tag_id, args = []) {
        wps_js.line_chart(tag_id, args['title'], args['date'], [{
            label: args['post_title'],
            data: args['state'],
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            fill: true
        }]);
    }
};