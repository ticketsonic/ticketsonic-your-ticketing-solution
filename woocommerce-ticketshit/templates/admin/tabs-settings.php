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
					<h3><div class="dashicons dashicons-media-spreadsheet"></div>&nbsp;<?php _e( 'CSV Settings', 'woo_ts' ); ?></h3>
				</td>
			</tr>

			<tr>
				<th>
					<label for="mode"><?php _e( 'Script mode', 'woo_pi' ); ?></label>
				</th>
				<td>
					<select id="mode" name="mode">
						<option value="https://dev.ticketshit.net"<?php selected( $mode, "https://dev.ticketshit.net" ); ?>>dev</option>
						<option value="https://www.demo.ticketshit.net"<?php selected( $mode, "https://www.demo.ticketshit.net" ); ?>>demo</option>
						<option value="https://www.ticketshit.net"<?php selected( $mode, "https://www.ticketshit.net" ); ?>>live</option>
					</select>
					<p class="description"><?php _e( 'Mode.', 'woo_pi' ); ?></p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="promoter_email"><?php _e('Ticket\'S HIT Promoter E-mail', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="30" id="promoter_email" name="promoter_email" value="<?php echo $promoter_email; ?>" class="text" />
					<p class="description"><?php _e( 'Ticket\'S HIT Promoter E-mail', 'woo_ts' ); ?>.</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="api_key"><?php _e('Ticket\'S HIT Promoter API Key', 'woo_ts' ); ?></label>
				</th>
				<td>
					<input type="text" size="30" id="api_key" name="api_key" value="<?php echo $api_key; ?>" class="text" />
					<p class="description"><?php _e( 'Ticket\'S HIT Promoter API Key', 'woo_ts' ); ?>.</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_html_template"><?php _e( 'Ticket HTML Template', 'woo_ts' ); ?></label>
				</th>
				<td>
					<textarea rows="10" cols="150" id="ticket_html_template" name="ticket_html_template" class="text" ><?php echo $ticket_html_template; ?></textarea>
					<p class="description"><?php _e( 'Ticket HTML Template', 'woo_ts' ); ?>.</p>
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