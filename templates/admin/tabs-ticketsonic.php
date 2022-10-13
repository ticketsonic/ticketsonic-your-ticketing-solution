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

	<form method="POST" id="events-list">
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
						<?php $badge_data = $event['badge']; ?>
						<tr id="events-row-<?php print ( esc_html( $key ) ); ?>">
							<td class="event-id"><?php print ( esc_html( $event['event_id'] ) ); ?></td>
							<td class="title"><?php print ( esc_html( $event['primary_text_pl'] ) ); ?></td>
							<td class="htext-loc"><?php print ( esc_html( $badge_data['primary_text_horizontal_location'] ) ); ?></td>
							<td class="vtext-loc"><?php print ( esc_html( $badge_data['primary_text_vertical_location'] ) ); ?></td>
							<!-- <td class=""><?php print ( esc_html( $badge_data['primary_text_horizontal_offset'] ) ); ?></td>
							<td class=""><?php print ( esc_html( $badge_data['primary_text_vertical_offset'] ) ); ?></td> -->
							<td class="htext-fontsize"><?php print ( esc_html( $badge_data['badge_primary_text_fontsize'] ) ); ?></td>
							<td class="vtext-fontsize"><?php print ( esc_html( $badge_data['badge_secondary_text_fontsize'] ) ); ?></td>
							<td class="htext-color"><?php print ( esc_html( $badge_data['badge_primary_text_color'] ) ); ?></td>
							<td class="vtext-color"><?php print ( esc_html( $badge_data['badge_secondary_text_color'] ) ); ?></td>
							<td class="edit-event-row"><a>Edit</a></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				<tr>
					<td><span id="new-event-button" class="button button-primary">Request new event</span></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="action" value="event-change" />
	</form>

	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;List of tickets</h3>

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
				<tr>
					<td><span id="new-ticket-button" class="button button-primary">Request new ticket</span></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
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
				<table class="form-table table-event table-first">
					<tbody>

						<?php do_action( 'ts_yts_export_settings_before' ); ?>

						<?php do_action( 'ts_yts_export_settings_general' ); ?>

						<tr id="new-event-ticket-settings1">
							<td colspan="2">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Event</h3>
							</td>
						</tr>

						<tr>
							<th>
								<label for="event_title"><?php _e('Title', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<input type="text" size="50" id="event_title" name="event_title" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="event_description"><?php _e('Description', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="event_description" name="event_description" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="event_location"><?php _e('Location', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="event_location" name="event_location" value="" class="text" />
							</td>
						</tr>
						<tr><td>* - required fields</td></tr>
					</tbody>
				</table>
				<table class="form-table table-ticket">
					<tbody>
						<tr id="new-event-ticket-settings2">
							<td colspan="2">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Ticket #1</h3>
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_title0"><?php _e('Title', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_title0" name="ticket[0][primary_text_pl]" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_description0"><?php _e('Description', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_description0" name="ticket[0][secondary_text_pl]" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_price0"><?php _e('Price', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_price0" name="ticket[0][price]" value="" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="ticket_currency0"><?php _e('Currency', 'ts_yts' ); ?></label>
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
								<label for="ticket_stock0"><?php _e('Stock', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<input type="text" size="50" id="ticket_stock0" name="ticket[0][stock]" value="" class="text" />
							</td>
						</tr>
						<tr><td>* - required fields</td></tr>
					</tbody>
				</table>
				<div id="new-ticket-anchor"></div>
				<table class="form-table submit-button">
					<tbody>
						<tr id="new-event-ticket-settings3">
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
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Badge settings', 'ts_yts' ); ?></h3>
								<p class="description"><?php _e( 'Set badge background and text location for autoprinting badges.', 'ts_yts' ); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_size"><?php _e('Badge size', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_size" id="badge_size">
									<option value="A4">A4 - 210 x 297 mm</option>
									<option value="A5">A5 - 148 x 210 mm</option>
									<option value="A6" selected>A6 - 105 x 148 mm</option>
									<option value="A7">A7 - 74 x 105 mm</option>
									<option value="A8">A8 - 52 x 74 mm</option>
									<option value="A8">A9 - 37 x 52 mm</option>
									<option value="A8">A10 - 26 x 37 mm</option>
								</select>
								<p class="description"><?php _e( 'The physical size of the printed badge', 'ts_yts' ); ?>.</p>
							</td>
						</tr>

						<?php // if ( file_exists( TS_YTS_UPLOADPATH . '/badge_background.jpg' ) ):?>
						<!-- <tr>
							<th>
								<label for="badge_background"><?php // _e('Default Badge Background', 'ts_yts' ); ?></label>
							</th>
							<td>
								<br>
								<img src="<?php // print TS_YTS_UPLOADURLPATH . '/badge_background.jpg'; ?>">
							</td>
						</tr> -->
						<?php // endif; ?>

						<tr>
							<th>
								<label for="badge_background"><?php _e('Badge Background', 'ts_yts' ); ?> *</label>
							</th>
							<td>
								<br>
								<input type="file" name="badge_file" id="badge_file">
								<p class="description"><?php _e( 'Only jpeg files are accepted.', 'ts_yts' ); ?>.</p>
								<p>A4 - 2480x3508, A5 - 1748x2480,<br>A6 - 1240x1748, A7 - 874x1240,<br>A8 - 614x874, A9 - 437x614,<br>A10 - 307x437</p>
							</td>
						</tr>

						<tr>
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Primary text settings', 'ts_yts' ); ?></h3>
								<p class="description"><?php _e( 'Set primary text location for autoprinting badges.', 'ts_yts' ); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_horizontal_location"><?php _e('Horizontal location', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_primary_text_horizontal_location" id="badge_primary_text_horizontal_location">
									<option value="left">Left</option>
									<option value="center" selected>Center</option>
									<option value="right">Right</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_horizontal_offset"><?php _e('Horizontal offset', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_primary_text_horizontal_offset" id="badge_primary_text_horizontal_offset">
									<option value="-50">-50%</option>
									<option value="-10">-10%</option>
									<option value="-9">-9%</option>
									<option value="-8">-8%</option>
									<option value="-7">-7%</option>
									<option value="-6">-6%</option>
									<option value="-5">-5%</option>
									<option value="-4">-4%</option>
									<option value="-3">-3%</option>
									<option value="-2">-2%</option>
									<option value="-1">-1%</option>
									<option value="0" selected>0%</option>
									<option value="1">1%</option>
									<option value="2">2%</option>
									<option value="3">3%</option>
									<option value="4">4%</option>
									<option value="5">5%</option>
									<option value="6">6%</option>
									<option value="7">7%</option>
									<option value="8">8%</option>
									<option value="9">9%</option>
									<option value="10">10%</option>
									<option value="50">50%</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_vertical_location"><?php _e('Vertical location', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_primary_text_vertical_location" id="badge_primary_text_vertical_location">
									<option value="top">Top</option>
									<option value="center" selected>Center</option>
									<option value="bottom">Bottom</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_vertical_offset"><?php _e('Vertical offset', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_primary_text_vertical_offset" id="badge_primary_text_vertical_offset">
									<option value="-10">-10%</option>
									<option value="-9">-9%</option>
									<option value="-8">-8%</option>
									<option value="-7">-7%</option>
									<option value="-6">-6%</option>
									<option value="-5">-5%</option>
									<option value="-4">-4%</option>
									<option value="-3">-3%</option>
									<option value="-2">-2%</option>
									<option value="-1">-1%</option>
									<option value="0" selected>0%</option>
									<option value="1">1%</option>
									<option value="2">2%</option>
									<option value="3">3%</option>
									<option value="4">4%</option>
									<option value="5">5%</option>
									<option value="6">6%</option>
									<option value="7">7%</option>
									<option value="8">8%</option>
									<option value="9">9%</option>
									<option value="10">10%</option>
									<option value="50">50%</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_fontsize"><?php _e( 'Font size', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_primary_text_fontsize" name="badge_primary_text_fontsize" value="70" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_color"><?php _e('Text color', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_primary_text_color" name="badge_primary_text_color" value="#000000" class="text" />
							</td>
						</tr>

						<tr>
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Secondary text settings', 'ts_yts' ); ?></h3>
								<p class="description"><?php _e( 'Set secondary text location for autoprinting badges.', 'ts_yts' ); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_horizontal_location"><?php _e('Horizontal location', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_secondary_text_horizontal_location" id="badge_secondary_text_horizontal_location">
									<option value="left">Left</option>
									<option value="center" selected>Center</option>
									<option value="right">Right</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_horizontal_offset"><?php _e('Horizontal offset', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_secondary_text_horizontal_offset" id="badge_secondary_text_horizontal_offset">
									<option value="-50">-50%</option>
									<option value="-10">-10%</option>
									<option value="-9">-9%</option>
									<option value="-8">-8%</option>
									<option value="-7">-7%</option>
									<option value="-6">-6%</option>
									<option value="-5">-5%</option>
									<option value="-4">-4%</option>
									<option value="-3">-3%</option>
									<option value="-2">-2%</option>
									<option value="-1">-1%</option>
									<option value="0" selected>0%</option>
									<option value="1">1%</option>
									<option value="2">2%</option>
									<option value="3">3%</option>
									<option value="4">4%</option>
									<option value="5">5%</option>
									<option value="6">6%</option>
									<option value="7">7%</option>
									<option value="8">8%</option>
									<option value="9">9%</option>
									<option value="10">10%</option>
									<option value="50">50%</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_vertical_location"><?php _e('Vertical location', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_secondary_text_vertical_location" id="badge_secondary_text_vertical_location">
									<option value="top">Top</option>
									<option value="center" selected>Center</option>
									<option value="bottom">Bottom</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_vertical_offset"><?php _e('Vertical offset', 'ts_yts' ); ?></label>
							</th>
							<td>
								<select name="badge_secondary_text_vertical_offset" id="badge_secondary_text_vertical_offset">
									<option value="-10">-10%</option>
									<option value="-9">-9%</option>
									<option value="-8">-8%</option>
									<option value="-7">-7%</option>
									<option value="-6">-6%</option>
									<option value="-5">-5%</option>
									<option value="-4">-4%</option>
									<option value="-3">-3%</option>
									<option value="-2">-2%</option>
									<option value="-1">-1%</option>
									<option value="0">0%</option>
									<option value="1">1%</option>
									<option value="2">2%</option>
									<option value="3">3%</option>
									<option value="4">4%</option>
									<option value="5" selected>5%</option>
									<option value="6">6%</option>
									<option value="7">7%</option>
									<option value="8">8%</option>
									<option value="9">9%</option>
									<option value="10">10%</option>
									<option value="50">50%</option>
								</select>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_fontsize"><?php _e('Font size', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_secondary_text_fontsize" name="badge_secondary_text_fontsize" value="50" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_color"><?php _e('Text color', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_secondary_text_color" name="badge_secondary_text_color" value="#000000" class="text" />
							</td>
						</tr>

						<tr>
							<td colspan="2" style="padding:0;">
								<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Badge Preview', 'ts_yts' ); ?></h3>
								<p class="description"><?php _e( 'Generate a preview before sending your new event for processing.', 'ts_yts' ); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_test_text"><?php _e('Primary Test Text', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_primary_test_text" name="badge_primary_test_text" value="FirstName LastName" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_primary_text_break_distance"><?php _e('Break Primary Text Distance', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" id="badge_primary_text_break_distance" name="badge_primary_text_break_distance" value="0" class="text" />
								<p class="description"><?php _e( 'Positive value will divide every word on a new line with the set vertical distance between them. Set 0 for single lined output.', 'ts_yts' ); ?>.</p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_test_text"><?php _e('Secondary Test Text', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" size="50" id="badge_secondary_test_text" name="badge_secondary_test_text" value="My Awesome Company" class="text" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_secondary_text_break_distance"><?php _e('Break Secondary Text Distance', 'ts_yts' ); ?></label>
							</th>
							<td>
								<input type="text" id="badge_secondary_text_break_distance" name="badge_secondary_text_break_distance" value="0" class="text" />
								<p class="description"><?php _e( 'Positive value will divide every word on a new line with the set vertical distance between them. Set 0 for single lined output.', 'ts_yts' ); ?>.</p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_background"><?php _e('Badge Background', 'ts_yts' ); ?></label>
							</th>
							<td>
								<br>
								<input type="button" name="generate_preview" value="Preview Badge" id="generate_preview"/>
								<p class="description"><?php _e( 'Generate badge preview', 'ts_yts' ); ?>.</p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="badge_background"><?php _e('Badge Preview', 'ts_yts' ); ?></label>
							</th>
							<td>
								<canvas id="badge_preview"></canvas>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="form-table submit-button">
					<tbody>
						<tr id="new-event-ticket-settings4">
							<td colspan="2">
								<p class="submit">
								<input type="submit" name="submit" id="submit-new-event-request-button" class="button button-primary" value="<?php _e( 'Request new event', 'ts_yts' ); ?>" />
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
											<option value="<?php print ( esc_html( $event['event_id'] ) ); ?>"><?php print ( esc_html( $event['title'] ) ); ?></option>
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
