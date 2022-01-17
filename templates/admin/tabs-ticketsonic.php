<?php

require_once WOO_TS_PATH . '/includes/ticketsonic.php';

$url = woo_ts_get_option( 'event_info_endpoint', 'https://www.ticketsonic.com:9507/v1/event/list' );
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

$url = woo_ts_get_option( 'ticket_info_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/list' );
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
					<tr>
						<td><span id="new-event-button" class="button button-primary">Request new event</span></td>
					</tr>
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
					<tr>
						<td><span id="new-ticket-button" class="button button-primary">Request new ticket</span></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<input type="hidden" name="action" value="ticket-change" />
	</form>
</div>

<div class="popups">
	<div class="popups-overlay"></div>
	<div id="new-event-popup" class="popup">
		<button type="button" class="close-form">
			<span class="screen-reader-text">Close</span>
			<span class="tb-close-icon"></span>
		</button>
		<div class="form-title-bar">
			<span class="popup-title">Request new event</span>
		</div>
		<div class="popup-form" id="submit-new-event-request">
			<form id="submit-new-event-request-form" enctype="multipart/form-data" method="post">
				<table class="form-table table-event">
					<tbody>

						<?php do_action( 'woo_ts_export_settings_before' ); ?>

						<?php do_action( 'woo_ts_export_settings_general' ); ?>

						<tr id="new-event-ticket-settings">
							<td colspan="2">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Event</h3>
							</td>
						</tr>

						<tr>
							<th>
								<label for="event_title"><?php _e('Title', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="event_title" name="event_title" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="event_description"><?php _e('Description', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="event_description" name="event_description" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="event_location"><?php _e('Location', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="event_location" name="event_location" value="" class="text" />
							</td>
						</tr>
					</tbody>
				</table>
				<table class="form-table table-ticket">
					<tbody>
						<tr id="new-event-ticket-settings">
							<td colspan="2">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Ticket #1</h3>
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_title0"><?php _e('Title', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_title0" name="ticket[0][primary_text_pl]" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_description0"><?php _e('Description', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_description0" name="ticket[0][secondary_text_pl]" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_price0"><?php _e('Price', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_price0" name="ticket[0][price]" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_currency0"><?php _e('Currency', 'woo_ts' ); ?></label>
							</th>
							<td>
								<select name="ticket[0][currency]" id="ticket_currency0">
									<option value="BGN">BGN</option>
									<option value="EUR">EUR</option>
									<option value="USD">USD</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_stock0"><?php _e('Stock', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_stock0" name="ticket[0][stock]" value="" class="text" />
							</td>
						</tr>
					</tbody>
				</table>
				<div id="new-ticket-anchor"></div>
				<table class="form-table submit-button">
					<tbody>
						<tr id="new-event-ticket-settings">
							<td colspan="2">
								<p class="submit">
									<input type="button" id="new-event-ticket-button" class="button button-primary" value="Add new ticket">
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="form-table table-badge">
					<tbody>
						<tr>
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Badge settings', 'woo_ts' ); ?></h3>
								<p class="description"><?php _e( 'Set badge background and text location for autoprinting badges.', 'woo_ts' ); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_background"><?php _e('Badge Background', 'woo_ts' ); ?></label>
							</th>
							<td>
								<img style="width: 150px;" src="<?php print ( esc_attr( WOO_TS_UPLOADURLPATH ) ); ?>/badge_background.jpg"/>
								<br>
								<input type="file" name="badge_file" id="badge_file">
								<p class="description"><?php _e( 'Only jpeg files are accepted', 'woo_ts' ); ?>.</p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_text_horizontal_location"><?php _e('Badge text horizontal location', 'woo_ts' ); ?></label>
							</th>
							<td>
								<select name="badge_text_horizontal_location" id="badge_text_horizontal_location">
									<option value="left">Left</option>
									<option value="center" selected>Center</option>
									<option value="right">Right</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_text_vertical_location"><?php _e('Badge text vertical location', 'woo_ts' ); ?></label>
							</th>
							<td>
								<select name="badge_text_vertical_location" id="badge_text_vertical_location">
									<option value="top">Top</option>
									<option value="center" selected>Center</option>
									<option value="bottom">Bottom</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_fontsize"><?php _e( 'Primary text font size', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_primary_text_fontsize" name="badge_primary_text_fontsize" value="100" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_fontsize"><?php _e('Secondary text font size', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_secondary_text_fontsize" name="badge_secondary_text_fontsize" value="80" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_color"><?php _e('Primary text color', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_primary_text_color" name="badge_primary_text_color" value="#000000" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_color"><?php _e('Secondary text color', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_secondary_text_color" name="badge_secondary_text_color" value="#000000" class="text" />
							</td>
						</tr>
					</tbody>
				</table>
				<table class="form-table submit-button">
					<tbody>
						<tr id="new-event-ticket-settings">
							<td colspan="2">
								<p class="submit">
								<input type="submit" name="submit" id="submit-new-event-request-button" class="button button-primary" value="<?php _e( 'Request new event', 'woo_ts' ); ?>" />
								<span id="cancel-new-event-request-button" class="button button-primary">Cancel</span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="action" value="create-event" />
			</form>
		</div>
	</div>




	<div id="new-ticket-popup" class="popup">
		<div class="form-title-bar">
			<span class="popup-title">Request new ticket</span>
			<button type="button" class="close-form">
				<span class="screen-reader-text">Close</span>
				<span class="tb-close-icon"></span>
			</button>
		</div>
		<div class="popup-form" id="submit-new-ticket-request">
			<form id="submit-new-ticket-request-form" enctype="multipart/form-data" method="post">
				<table class="form-table">
					<tbody>

						<?php do_action( 'woo_ts_export_settings_before' ); ?>

						<tr id="general-settings">
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Create new ticket', 'woo_ts' ); ?></h3>
								<p class="description"><?php _e( 'Create a new a new ticket for a specified event. The request will be sent for processing. You will receive an email when the processing is ready.', 'woo_ts' ); ?></p>
							</td>
						</tr>

						<?php do_action( 'woo_ts_export_settings_general' ); ?>

						<tr id="ticket-settings">
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Create tickets for the event', 'woo_ts' ); ?></h3>
								<p class="description"><?php _e( 'Press the Add new ticket button to assign additional tickets.', 'woo_ts' ); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_eventid"><?php _e('Ticket event id', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_title" name="ticket_eventid" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="primary_text_pl"><?php _e('Ticket title', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticketprimary_text_pl_title" name="primary_text_pl" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="secondary_text_pl"><?php _e('Ticket description', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="secondary_text_pl" name="secondary_text_pl" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_price"><?php _e('Ticket price', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_price" name="ticket_price" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_currency"><?php _e('Ticket currency', 'woo_ts' ); ?></label>
							</th>
							<td>
								<select name="ticket_currency" id="ticket_currency">
									<option value="BGN">BGN</option>
									<option value="EUR">EUR</option>
									<option value="USD">USD</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_stock"><?php _e('Ticket stock', 'woo_ts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_stock" name="ticket_stock" value="" class="text" />
							</td>
						</tr>
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Request new ticket', 'woo_ts' ); ?>" />
				</p>
				<input type="hidden" name="action" value="create-ticket" />
				<span id="cancel-new-ticket-request-button" class="button button-primary">Cancel</span>
			</form>
		</div>
	</div>
</div>
