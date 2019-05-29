if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "pages") {

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