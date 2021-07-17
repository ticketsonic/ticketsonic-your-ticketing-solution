<div id="poststuff">
	<div id="remote-sync">
		<form id="sync_with_ts" enctype="multipart/form-data" method="post">
			<?php do_action('woo_ts_before_upload' ); ?>

				<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Sync tickets with TicketSonic', 'woo_ts' ); ?></h3>
				<p class="description"><?php _e( 'Sync your tickets as WooCommerce products with TicketSonic.', 'woo_ts' ); ?></p>
				<div class="inside">
					<p class="submit">
						<input type="submit" value="<?php _e('Sync with TS', 'woo_ts' ); ?>" class="button-primary" />
					</p>
				</div>

			<?php do_action('woo_ts_after_upload'); ?>
			<?php do_action('woo_ts_before_options'); ?>

			<input type="hidden" name="action" value="sync_with_ts" />
			<?php wp_nonce_field( 'update-options' ); ?>
		</form>
	</div>
</div>