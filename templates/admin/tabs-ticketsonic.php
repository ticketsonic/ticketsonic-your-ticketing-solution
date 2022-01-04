<?php

require_once WOO_TS_PATH . '/includes/ticketsonic.php';

$url = woo_ts_get_option( 'event_info_endpoint', '' );
if ( empty( $url ) ) {
	woo_ts_admin_notice( 'Event Info Endpoint have to set in Settings', 'error' );

	return;
}

$email = woo_ts_get_option( 'api_userid', '' );
if ( empty( $email ) ) {
	woo_ts_admin_notice( 'Partner E-mail have to set in Settings', 'error' );

	return;
}

$key = woo_ts_get_option( 'api_key', '' );
if ( empty( $key ) ) {
	woo_ts_admin_notice( 'Partner API Key have to set in Settings', 'error' );

	return;
}

$raw_events = get_events_data_from_remote( $url, $email, $key );

$url = woo_ts_get_option( 'ticket_info_endpoint', '' );
if ( empty( $url ) ) {
	woo_ts_admin_notice( 'Event Info Endpoint have to set in Settings', 'error' );

	return;
}

$raw_tickets = get_event_ticket_data_from_remote( $url, $email, $key, null );
?>
<div class="remote-data">
	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;List of events</h3>
	<form method="POST">
		<table id="events" class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th class="manage-column column-xs">Event ID</th>
					<th class="manage-column column-xs">Title</th>
					<th class="manage-column column-xs">Horizontal text location</th>
					<th class="manage-column column-xs">Vertical text location</th>
					<th class="manage-column column-xs">Horizontal text font size</th>
					<th class="manage-column column-xs">Vertical text font size</th>
					<th class="manage-column column-xs">Horizontal text font color</th>
					<th class="manage-column column-xs">Vertical text font color</th>
					<th class="manage-column column-xs">Edit</th>
				</tr>
			</thead>    
			<tbody>
				<?php if ( count( $raw_events['events'] ) > 0 ) : ?>
					<?php foreach ( $raw_events['events'] as $key => $event ) : ?>
						<?php $badge_data = json_decode( $event['badge_data'] ); ?>
						<tr id="row-<?php print ( esc_html( $key ) ); ?>">
							<td class="event-id"><?php print ( esc_html( $event['event_id'] ) ); ?></td>
							<td class="title"><?php print ( esc_html( $event['title'] ) ); ?></td>
							<td class="htext-loc"><?php print ( esc_html( $badge_data->badge_text_horizontal_location ) ); ?></td>
							<td class="vtext-loc"><?php print ( esc_html( $badge_data->badge_text_vertical_location ) ); ?></td>
							<td class="htext-fontsize"><?php print ( esc_html( $badge_data->badge_primary_text_fontsize ) ); ?></td>
							<td class="vtext-fontsize"><?php print ( esc_html( $badge_data->badge_secondary_text_fontsize ) ); ?></td>
							<td class="htext-color"><?php print ( esc_html( $badge_data->badge_primary_text_color ) ); ?></td>
							<td class="vtext-color"><?php print ( esc_html( $badge_data->badge_secondary_text_color ) ); ?></td>
							<td class="edit-event-row"><a>Edit</a></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<input type="hidden" name="action" value="event-change" />
	</form>

	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;List of tickets</h3>

	<form method="POST">
		<table id="tickets" class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th class="manage-column column-xs">Sku</th>
					<th class="manage-column column-xm">Title</th>
					<th class="manage-column column-xs">Price</th>
					<th class="manage-column column-xs">Currency</th>
					<th class="manage-column column-xs">Stock</th>
					<th class="manage-column column-xs">Event ID</th>
					<th class="manage-column column-xm">Edit</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( count( $raw_tickets['tickets'] ) > 0 ) : ?>
					<?php foreach ( $raw_tickets['tickets'] as $key => $ticket ) : ?>
					<tr id="row-<?php print ( esc_html( $key ) ); ?>">
						<td class="sku"><?php print ( esc_html( $ticket['sku'] ) ); ?></td>
						<td class="ticket-title"><?php print ( esc_html( $ticket['primary_text_pl'] ) ); ?></td>
						<td class="price"><?php printf( '%2.2f', esc_html( $ticket['price'] / 100 ) ); ?></td>
						<td class="currency"><?php print ( esc_html( $ticket['currency'] ) ); ?></td>
						<td class="stock"><?php print ( esc_html( $ticket['stock'] ) ); ?></td>
						<td class="event_id"><?php print ( esc_html( $ticket['event_id'] ) ); ?></td>
						<td class="edit-ticket-row"><a>Edit</a></td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<input type="hidden" name="action" value="ticket-change" />
	</form>
</div>
