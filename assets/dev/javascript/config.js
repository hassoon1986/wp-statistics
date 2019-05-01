/* Start Wp-statistics Admin Js */
var wps_js = {};

/* Get Wordpress i18n */
wps_js.i18n = (typeof wps_i18n != 'undefined') ? wps_i18n : [];

/* WordPress Localize Method */
wps_js._ = function (key) {
    return (key in this.i18n ? this.i18n[key] : '');
};