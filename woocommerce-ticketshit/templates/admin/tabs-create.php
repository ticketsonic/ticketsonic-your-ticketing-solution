<br class="clear" />

<form enctype="multipart/form-data" method="post">
	<table class="form-table">
		<tbody>

			<?php do_action( 'woo_ts_export_settings_before' ); ?>

			<tr id="general-settings">
				<td colspan="2" style="padding:0;">
					<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Create new event', 'woo_ts' ); ?></h3>
					<p class="description"><?php _e( 'Create a new event and assing tickets. The request will be sent for processing. You will receive an email when the processing is ready.', 'woo_ts' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'woo_ts_export_settings_general' ); ?>

			<tr>
				<th>
					<label for="event_title"><?php _e('Event title', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_title" name="event_title" value="Event title" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="event_description"><?php _e('Event description', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_description" name="event_description" value="Event description" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="event_datetime"><?php _e('Event start date time', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_datetime" name="event_datetime" value="1598987456" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="event_location"><?php _e('Event location', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_location" name="event_location" value="Event location" class="text" />
				</td>
			</tr>

			<tr id="general-settings">
				<td colspan="2" style="padding:0;">
					<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Create tickets for the event', 'woo_ts' ); ?></h3>
					<p class="description"><?php _e( 'Press the Add new ticket button to assign additional tickets.', 'woo_ts' ); ?></p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_title1"><?php _e('Ticket title', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_title1" name="ticket[0][title]" value="Ticket title" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_description1"><?php _e('Ticket description', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_description1" name="ticket[0][description]" value="Ticket description" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_price1"><?php _e('Ticket price', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_price1" name="ticket[0][price]" value="90.00" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_currency1"><?php _e('Ticket currency', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_currency1" name="ticket[0][currency]" value="BGN" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_stock1"><?php _e('Ticket stock', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_stock1" name="ticket[0][stock]" value="900" class="text" />
				</td>
			</tr>
		</tbody>
	</table>
	<!-- .form-table -->
	
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Request event', 'woo_ts' ); ?>" />
	</p>
	<input type="hidden" name="action" value="create-event" />
</form>
<?php do_action( 'woo_ts_export_settings_bottom' ); ?>