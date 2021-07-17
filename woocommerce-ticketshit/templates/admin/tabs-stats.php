<?php

require_once WOO_TS_PATH . '/includes/helper.php';
$helper = new Helper();
$raw_events = $helper->get_events_data_from_remote();
$raw_tickets = $helper->get_event_ticket_data_from_remote(null);

print "<h3><div class=\"dashicons dashicons-admin-settings\"></div>&nbsp;List of events</h3>";

print "<table><thead><tr><td>Title</td><td>Event ID</td></tr></thead><tr>";

foreach ($raw_events["events"] as $event) {
    print "<tr>";
    print "<td>" . $event["title"] . "</td>";
    print "<td>" . $event["event_id"] . "</td>";
    print "</tr>";
}

print "</table>";

print "<h3><div class=\"dashicons dashicons-admin-settings\"></div>&nbsp;List of tickets</h3>";

print "<table><thead><tr><td>Event ID</td><td>Title</td><td>Price</td><td>Currency</td><td>Stock</td></tr></thead>";
    foreach ($raw_tickets["tickets"] as $ticket) {
        print "<tr>";
        print "<td>" . $ticket["event_id"] . "</td>";
        print "<td>" . $ticket["ticket_title_en"] . "</td>";
        print "<td>" . $ticket["price"] . "</td>";
        print "<td>" . $ticket["currency"] . "</td>";
        print "<td>" . $ticket["stock"] . "</td>";
        print "</tr>";
    }
    print "</tr></table>";
?>