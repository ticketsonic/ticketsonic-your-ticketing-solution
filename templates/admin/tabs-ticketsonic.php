<?php

require_once TS_YTS_PATH . '/includes/ticketsonic.php';

$url = ts_yts_get_option( 'event_info_endpoint', 'https://www.ticketsonic.com:9507/v1/event/list' );
if ( empty( $url ) ) {
	ts_yts_admin_notice_html( 'Event Info Endpoint have to set in Settings', 'error' );

	return;
}

$email = ts_yts_get_option( 'api_userid', '' );
if ( empty( $email ) ) {
	ts_yts_admin_notice_html( 'API E-mail have to set in Settings', 'error' );

	return;
}

$key = ts_yts_get_option( 'api_key', '' );
if ( empty( $key ) ) {
	ts_yts_admin_notice_html( 'API Key have to set in Settings', 'error' );

	return;
}

$raw_events = ts_yts_get_events_data_from_remote( $url, $email, $key );

$url = ts_yts_get_option( 'ticket_info_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/list' );
if ( empty( $url ) ) {
	ts_yts_admin_notice_html( 'Event Info Endpoint have to set in Settings', 'error' );

	return;
}

$raw_tickets = ts_yts_get_event_ticket_data_from_remote( $url, $email, $key, null );
?>
<div class="remote-data">
	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;List of events</h3>

	<span id="new-event-button" class="button button-primary">Request new event</span>
	<form method="POST" id="events-list">
		<table id="events" class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th class="manage-column column-xs heading-event-id" rowspan="3">Event ID</th>
					<th class="manage-column heading-title" rowspan="3">Title</th>
					<th class="manage-column heading-description" rowspan="3">Description</th>
					<th class="manage-column column-xs heading-date" rowspan="3">Date</th>
					<th class="manage-column heading-location" rowspan="3">Location</th>
					<th class="manage-column heading-badge" colspan="19">Badge <a class="toggle-badge-details" id="toggle-badge-details" href="#">Show details</a></th>
					<th class="manage-column column-xs heading-edit" rowspan="3">Edit</th>
				</tr>
				<tr>
					<th class="manage-column badge-foldable" rowspan="2">Preview</th>
					<th class="manage-column badge-foldable" rowspan="2">Size</th>
					<th class="manage-column badge-foldable" rowspan="2">Background</th>
					<th class="manage-column badge-foldable" colspan="8">Primary text</th>
					<th class="manage-column badge-foldable" colspan="8">Secondary text</th>
				</tr>
				<tr>
					<th class="manage-column badge-foldable">Hor. loc.</th>
					<th class="manage-column badge-foldable">Hor. offset</th>
					<th class="manage-column badge-foldable">Vert. loc.</th>
					<th class="manage-column badge-foldable">Vert. offset</th>
					<th class="manage-column badge-foldable">Font size</th>
					<th class="manage-column badge-foldable">Font color</th>
					<th class="manage-column badge-foldable">Test text</th>
					<th class="manage-column badge-foldable">Break dist.</th>
					<th class="manage-column badge-foldable">Hor. loc.</th>
					<th class="manage-column badge-foldable">Hor. offset</th>
					<th class="manage-column badge-foldable">Vert. loc.</th>
					<th class="manage-column badge-foldable">Vert. offset</th>
					<th class="manage-column badge-foldable">Font size</th>
					<th class="manage-column badge-foldable">Font color</th>
					<th class="manage-column badge-foldable">Test text</th>
					<th class="manage-column badge-foldable">Break dist.</th>
				</tr>
			</thead>    
			<tbody>
				<?php if ( count( $raw_events['events'] ) > 0 ) : ?>
					<?php foreach ( $raw_events['events'] as $key => $event ) : ?>
						<?php $badge_data = $event['badge']; ?>
						<tr class="event-row" id="event-row-<?php print ( esc_html( $key ) ); ?>">
							<td class="event-id"><?php print ( esc_html( $event['event_id'] ) ); ?></td>
							<td class="event-primary"><?php print ( esc_html( $event['primary_text_pl'] ) ); ?></td>
							<td class="event-secondary"><?php print ( esc_html( $event['secondary_text_pl'] ) ); ?></td>
							<td class="event-start-time"><?php print ( esc_html( date( 'd M Y', $event['start_time'] ) ) ); ?></td>
							<td class="event-location"><?php print ( esc_html( $event['location'] ) ); ?></td>

							<td class="badge-preview">
								<a class="badge-show-preview" id="badge-show-preview-<?php print ( esc_html( $key ) ); ?>">Show preview</a>
								<canvas class="badge-canvas" id="badge-preview-<?php print ( esc_html( $key ) ); ?>"></canvas>
							</td>
							<td class="badge-size badge-foldable"><?php print ( esc_html( $badge_data['badge_size'] ) ); ?></td>
							<?php $badge_background_file = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . TS_YTS_DIRNAME . DIRECTORY_SEPARATOR . $event['event_id'] . '-badge-background.jpg'; ?>
							<?php $badge_background_url = wp_upload_dir()['baseurl'] . DIRECTORY_SEPARATOR . TS_YTS_DIRNAME . DIRECTORY_SEPARATOR . $event['event_id'] . '-badge-background.jpg'; ?>
							
							<?php if ( file_exists( $badge_background_file ) ) : ?>
								<td class="badge-background badge-foldable">
									<img class="badge-background" src="<?php print ( esc_html( $badge_background_url ) ); ?>"/>
								</td>
							<?php else : ?>
								<td class="badge-background badge-foldable"></td>
							<?php endif; ?>
							

							<td class="badge-pr-htext-loc badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_text_horizontal_location'] ) ); ?></td>
							<td class="badge-pr-htext-offset badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_text_horizontal_offset'] ) ); ?></td>
							<td class="badge-pr-vtext-loc badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_text_vertical_location'] ) ); ?></td>
							<td class="badge-pr-vtext-offset badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_text_vertical_offset'] ) ); ?></td>
							<td class="badge-pr-fontsize badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_text_fontsize'] ) ); ?></td>
							<td class="badge-pr-color badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_text_color'] ) ); ?></td>
							<td class="badge-pr-test-text badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_test_text'] ) ); ?></td>
							<td class="badge-pr-br-distance badge-foldable"><?php print ( esc_html( $badge_data['badge_primary_text_break_distance'] ) ); ?></td>

							<td class="badge-sc-htext-loc badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_text_horizontal_location'] ) ); ?></td>
							<td class="badge-sc-htext-offset badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_text_horizontal_offset'] ) ); ?></td>
							<td class="badge-sc-vtext-loc badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_text_vertical_location'] ) ); ?></td>
							<td class="badge-sc-vtext-offset badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_text_vertical_offset'] ) ); ?></td>
							<td class="badge-sc-fontsize badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_text_fontsize'] ) ); ?></td>
							<td class="badge-sc-color badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_text_color'] ) ); ?></td>
							<td class="badge-sc-test-text badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_test_text'] ) ); ?></td>
							<td class="badge-sc-br-distance badge-foldable"><?php print ( esc_html( $badge_data['badge_secondary_text_break_distance'] ) ); ?></td>
							<td class="edit-event-row"><a>Edit</a></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<input type="hidden" name="action" value="event-change" />
	</form>

	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;List of tickets</h3>

	<span id="new-ticket-button" class="button button-primary">Request new ticket</span>

	<form method="POST" id="tickets-list">
		<table id="tickets" class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<th class="manage-column column-xs">SKU</th>
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
						<td class="price"><?php printf( '%2.2f', esc_html( $ticket['price'] ) ); ?></td>
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

<div class="popups">
	<div class="popups-overlay"></div>
	




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
				<table class="form-table table-ticket table-first">
					<tbody>

						<?php do_action( 'ts_yts_export_settings_before' ); ?>

						<tr id="general-settings">
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Create new ticket', 'ts_yts' ); ?></h3>
								<p class="description"><?php _e( 'Create a new a new ticket for a specified event. The request will be sent for processing. You will receive an email when the processing is ready.', 'ts_yts' ); ?></p>
							</td>
						</tr>

						<?php do_action( 'ts_yts_export_settings_general' ); ?>

						<tr id="ticket-settings">
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Create tickets for the event', 'ts_yts' ); ?></h3>
								<p class="description"><?php _e( 'Press the Add new ticket button to assign additional tickets.', 'ts_yts' ); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_eventid"><?php _e('Event Name', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="ticket_eventid" id="ticket_eventid">
									<?php if ( count( $raw_events['events'] ) > 0 ) : ?>
										<?php foreach ( $raw_events['events'] as $key => $event ) : ?>
											<option value="<?php print ( esc_html( $event['event_id'] ) ); ?>"><?php print ( esc_html( $event['primary_text_pl'] ) ); ?></option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="primary_text_pl"><?php _e('Title', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<input type="text" size="50" id="ticketprimary_text_pl_title" name="primary_text_pl" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="secondary_text_pl"><?php _e('Description', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="secondary_text_pl" name="secondary_text_pl" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_price"><?php _e('Price', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_price" name="ticket_price" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_currency"><?php _e('Currency', 'ts_yts' ); ?></label>
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
								<label for="ticket_stock"><?php _e('Ticket stock', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_stock" name="ticket_stock" value="" class="text" />
							</td>
						</tr>
						<tr><td>* - required fields</td></tr>
					</tbody>
				</table>

				<table class="form-table submit-button">
					<tbody>
						<tr id="new-event-ticket-settings5">
							<td colspan="2">
								<p class="submit">
									<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Request new ticket', 'ts_yts' ); ?>" />
									<span id="cancel-new-ticket-request-button" class="button button-primary">Cancel</span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="action" value="create-ticket" />
			</form>
		</div>
	</div>
</div>
