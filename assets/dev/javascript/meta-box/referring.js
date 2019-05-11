wps_js.referring_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<table width="100%" class="widefat table-stats wps-report-table"><tbody>
        <tr>
            <td width="50%">${wps_js._('address')}</td>
            <td width="40%">${wps_js.meta_box_lang('referring', 'server_ip')}</td>
            <td width="10%">${wps_js.meta_box_lang('referring', 'references')}</td>
        </tr>`;

        args.forEach(function (value) {
            t += `<tr>
			<td>` + wps_js.site_icon(value['domain']) + ` <a href='//${value['domain']}' title='${value['title']}' target="_blank">${value['domain']}</a></td>
            <td><span class='wps-cursor-default'` + (value['country'].length > 2 ? ` title="${value['country']}"` : ``) + `>${value['ip']}</span></td>
			<td><a href="${value['page_link']}">${value['number']}</a></td>
			</tr>`;
        });

        t += `</tbody></table>`;
        return t;
    }

};