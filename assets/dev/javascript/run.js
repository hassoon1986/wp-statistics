// Load Jquery Date Picker in WP-Statistics Admin
wps_js.date_picker();

// Run Meta Box [Overview Or Dashboard]
if (wps_js.global.page.file === "index.php" || wps_js.global.page.file === "toplevel_page_wps_overview_page") {
    wps_js.run_meta_boxes();
}
