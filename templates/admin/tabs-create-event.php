<br class="clear" />

<form id="create" enctype="multipart/form-data" method="post">
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
					<input type="text" size="50" id="event_title" name="event_title" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="event_description"><?php _e('Event description', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_description" name="event_description" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="event_location"><?php _e('Event location', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_location" name="event_location" value="" class="text" />
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
					<label for="ticket_title0"><?php _e('Ticket title1', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_title0" name="ticket[0][primary_text_pl]" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_description0"><?php _e('Ticket description1', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_description0" name="ticket[0][secondary_text_pl]" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_price0"><?php _e('Ticket price1', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_price0" name="ticket[0][price]" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_currency0"><?php _e('Ticket currency1', 'woo_ts' ); ?></label>
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
					<label for="ticket_stock0"><?php _e('Ticket stock1', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_stock0" name="ticket[0][stock]" value="" class="text" />
				</td>
			</tr>
			<tr>
				<td>
					<p class="submit">
						<input type="button" id="new-ticket-button" class="button button-primary" value="Add new ticket">
					</p>
				</td>
			</tr>

			<tr id="badge-settings">
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
					<img style="width: 150px;" src="<?php print WOO_TS_UPLOADURLPATH; ?>/badge_background.jpg"/>
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
					<label for="badge_primary_text_fontsize"><?php _e('Primary text font size', 'woo_ts' ); ?></label>
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

	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Request new event', 'woo_ts' ); ?>" />
	</p>
	<input type="hidden" name="action" value="create-event" />
</form>
<?php do_action( 'woo_ts_export_settings_bottom' ); ?>
