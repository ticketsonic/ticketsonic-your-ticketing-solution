<?php

require_once WOO_TS_PATH . "/includes/ticketsonic.php";

$url = woo_ts_get_option("event_info_endpoint", "");
if (empty($url)) {
    woo_ts_admin_notice("Event Info Endpoint have to set in Settings", "error");
    return;
}

$email = woo_ts_get_option("api_userid", "");
if (empty($email)) {
    woo_ts_admin_notice("Partner E-mail have to set in Settings", "error");
    return;
}

$key = woo_ts_get_option("api_key", "");
if (empty($key)) {
    woo_ts_admin_notice("Partner API Key have to set in Settings", "error");
    return;
}

$raw_events = get_events_data_from_remote($url, $email, $key);

$url = woo_ts_get_option("ticket_info_endpoint", "");
if (empty($url)) {
    woo_ts_admin_notice("Event Info Endpoint have to set in Settings", "error");
    return;
}

$raw_tickets = get_event_ticket_data_from_remote($url, $email, $key, null);
?>
<div class="remote-data">
    <h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;List of events</h3>

    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
            <tr>
                <th class="manage-column column-xm">Event ID</th>
                <th>Title</th>
            </tr>
        </thead>    
        <tbody>
            <?php foreach ($raw_events["events"] as $event): ?>
                <tr>
                    <td class="sku column-name"><?php print $event["event_id"]; ?></td>
                    <td class="column-name"><?php print $event["title"]; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;List of tickets</h3>

    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
            <tr>
                <th class="manage-column column-xs">Sku</th>
                <th class="manage-column column-xm">Title</th>
                <th class="manage-column column-xs">Price</th>
                <th class="manage-column column-xs">Currency</th>
                <th class="manage-column column-xs">Stock</th>
                <th class="manage-column column-xm">Event ID</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($raw_tickets["tickets"] as $ticket): ?>
            <tr>
                <td><?php print $ticket["sku"]; ?></td>
                <td><?php print $ticket["primary_text_pl"]; ?></td>
                <td><?php print $ticket["price"]; ?></td>
                <td><?php print $ticket["currency"]; ?></td>
                <td><?php print $ticket["stock"]; ?></td>
                <td><?php print $ticket["event_id"]; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
