
function wp_statistics_get_widget_contents(widget, container_id) {
    var data = {
        'action': 'wp_statistics_get_widget_contents',
        'widget': widget,
    };

    container = jQuery("#" + container_id);

    if (container.is(':visible')) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: data,
            datatype: 'json',
        })
            .always(function (result) {
                // Take the returned result and add it to the DOM.
                jQuery("#" + container_id).html("").html(result);
            })
            .fail(function (result) {
                // If we failed for some reason, like a timeout, try again.
                container.html(wp_statistics_loading_image);
                wp_statistics_get_widget_contents(widget, container_id);
            });

    }
}

function wp_statistics_refresh_widget() {
    var widget = this.id.replace('wps_', '');
    widget = widget.replace('_refresh_button', '');
    var container_id = widget.replace('.', '_') + '_postbox';

    var container = jQuery("#" + container_id);

    if (container.is(':visible')) {
        container.html(wp_statistics_loading_image);

        wp_statistics_get_widget_contents(widget, container_id);
    }

    return false;
}

function wp_statistics_refresh_on_toggle_widget() {
    if (this.value.substring(0, 4) != 'wps_') {
        return;
    }

    var container_id = this.value.replace('wps_', '');
    var widget = container_id.replace('_postbox', '');

    wp_statistics_get_widget_contents(widget, container_id);
}

function wp_statistics_goto_more() {
    var widget = this.id;

    if (wp_statistics_destinations[widget] !== undefined) {
        window.location.href = wp_statistics_destinations[widget];
    }

    return false;
}

