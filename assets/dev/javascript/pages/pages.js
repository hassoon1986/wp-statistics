if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "pages") {

    // Check has Custom Page
    if (wps_js.isset(wps_js.global, 'request_params', 'ID') && wps_js.isset(wps_js.global, 'request_params', 'type')) {

        // Create Params
        let params;

        // Check Days ago or Between
        if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
            params = {'from': wps_js.global.request_params.from, 'to': wps_js.global.request_params.to};
        } else {
            params = {'ago': 30};
        }

        // Add Page ID and type
        params = Object.assign(params, {
            'ID': wps_js.global.request_params.ID,
            'type': wps_js.global.request_params.type
        });

        // Run MetaBox
        wps_js.run_meta_box('pages-chart', params, false);

    } else {

        // Check Pagination
        let params;
        if (wps_js.isset(wps_js.global, 'request_params', 'pagination-page')) {
            params = {'paged': wps_js.global.request_params['pagination-page']};
        }

        // Run Pages list MetaBox
        wps_js.run_meta_box('pages', params, false);

        // Run Top Pages chart Meta Box
        wps_js.run_meta_box('top-pages-chart', {});
    }
}