<br class="clear" />

<form enctype="multipart/form-data" method="post">
	<table class="form-table">
		<tbody>
			<?php do_action( 'woo_ts_export_settings_before' ); ?>

			<tr id="general-settings">
				<td colspan="2" style="padding:0;">
					<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'General Settings', 'woo_ts' ); ?></h3>
					<p class="description"><?php _e( 'Manage import options across Product Importer from this screen.', 'woo_ts' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'woo_ts_export_settings_general' ); ?>

			<tr id="csv-settings">
				<td colspan="2" style="padding:0;">
					<hr />
					<h3><div class="dashicons dashicons-media-spreadsheet"></div>&nbsp;<?php _e( 'Settings', 'woo_ts' ); ?></h3>
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_info_endpoint"><?php _e('Get Ticket Info Endpoint', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_info_endpoint" name="ticket_info_endpoint" value="<?php print ( esc_attr( $ticket_info_endpoint ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Ticket Endpoint', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="event_info_endpoint"><?php _e('Event Info Endpoint', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_info_endpoint" name="event_info_endpoint" value="<?php print ( esc_attr( $event_info_endpoint ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Event Info Endpoint', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="change_event_endpoint"><?php _e('Change Event Endpoint', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="change_event_endpoint" name="change_event_endpoint" value="<?php print ( esc_attr( $change_event_endpoint ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Endpoint for requesting changes in event', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="new_event_endpoint"><?php _e('New Event Endpoint', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="new_event_endpoint" name="new_event_endpoint" value="<?php print ( esc_attr( $new_event_endpoint ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Event Endpoint', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="new_ticket_endpoint"><?php _e('New Ticket Endpoint', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="new_ticket_endpoint" name="new_ticket_endpoint" value="<?php print ( esc_attr( $new_ticket_endpoint ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Ticket Endpoint', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="change_ticket_endpoint"><?php _e('Change Ticket Endpoint', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="change_ticket_endpoint" name="change_ticket_endpoint" value="<?php print ( esc_attr( $change_ticket_endpoint ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Ticket Endpoint', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="external_order_endpoint"><?php _e('Order Endpoint', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="external_order_endpoint" name="external_order_endpoint" value="<?php print ( esc_attr( $external_order_endpoint ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Order Endpoint', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="event_id"><?php _e('Event ID', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="50" id="event_id" name="event_id" value="<?php print ( esc_attr( $event_id ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Event ID', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="api_userid"><?php _e('Partner E-mail', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="30" id="api_userid" name="api_userid" value="<?php print ( esc_attr( $api_userid ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Partner E-mail', 'woo_ts' ); ?>.</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="api_key"><?php _e('Partner API Key', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="30" id="api_key" name="api_key" value="<?php print ( esc_attr( $api_key ) ); ?>" class="text" />
					<p class="description"><?php _e( 'Partner API Key', 'woo_ts' ); ?>.</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="email_subject"><?php _e('E-mail Subject', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="30" id="email_subject" name="email_subject" value="<?php print ( esc_attr( $email_subject ) ); ?>" class="text" />
					<p class="description"><?php _e( 'E-mail Subject', 'woo_ts' ); ?>.</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="email_body"><?php _e('E-mail Body', 'woo_ts' ); ?></label>
				</th>
				<td>
					<textarea id="email_body" name="email_body" rows="50" cols="150"><?php print ( esc_attr( $email_body ) ); ?></textarea>
					<p class="description"><?php _e( 'E-mail Body', 'woo_ts' ); ?>.</p>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- .form-table -->

	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'woo_ts' ); ?>" />
	</p>
	<input type="hidden" name="action" value="save-settings" />
</form>
<?php do_action( 'woo_ts_export_settings_bottom' ); ?>
