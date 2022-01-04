<br class="clear" />

<form id="create" enctype="multipart/form-data" method="post">
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

			<tr id="general-settings">
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
</form>
<?php do_action( 'woo_ts_export_settings_bottom' ); ?>
