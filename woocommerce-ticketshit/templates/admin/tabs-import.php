<div id="poststuff">
	<div id="upload-csv" class="postbox">
		<form id="sync_with_ts" enctype="multipart/form-data" method="post">
			<?php do_action('woo_ts_before_upload' ); ?>

				<h3 class="hndle"><?php _e('Update Tickets', 'woo_ts'); ?></h3>
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