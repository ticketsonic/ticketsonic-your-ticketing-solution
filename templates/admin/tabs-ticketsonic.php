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
                <th>Horizontal text location</th>
                <th>Vertical text location</th>
                <th>Horizontal text font size</th>
                <th>Vertical text font size</th>
                <th>Horizontal text font color</th>
                <th>Vertical text font color</th>
            </tr>
        </thead>    
        <tbody>
            <?php foreach ($raw_events["events"] as $event): ?>
                <?php $badge_data = json_decode($event["badge_data"]); ?>
                <tr>
                    <td class="sku column-name"><?php print $event["event_id"]; ?></td>
                    <td class="column-name"><?php print $event["title"]; ?></td>
                    <td class="column-name"><?php print $badge_data->badge_text_horizontal_location; ?></td>
                    <td class="column-name"><?php print $badge_data->badge_text_vertical_location; ?></td>
                    <td class="column-name"><?php print $badge_data->badge_primary_text_fontsize; ?></td>
                    <td class="column-name"><?php print $badge_data->badge_secondary_text_fontsize; ?></td>
                    <td class="column-name"><?php print $badge_data->badge_primary_text_color; ?></td>
                    <td class="column-name"><?php print $badge_data->badge_secondary_text_color; ?></td>
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
