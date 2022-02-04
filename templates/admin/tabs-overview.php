<div class="overview-left">
	<h3><div class="dashicons dashicons-migrate"></div>&nbsp;<a href="<?php print ( esc_attr( add_query_arg( 'tab', 'sync' ) ) ); ?>"><?php _e( 'Sync', 'ts_yte' ); ?></a></h3>
	<p><?php _e( 'Sync TicketSonic data with the WooCommerce store.', 'ts_yte' ); ?></p>

	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<a href="<?php print ( esc_attr( add_query_arg( 'tab', 'settings' ) ) ); ?>"><?php _e( 'Settings', 'ts_yte' ); ?></a></h3>
	<p><?php _e( 'Manage credentials data to gain access to TicketSonic.', 'ts_yte' ); ?></p>

	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<a href="<?php print ( esc_attr( add_query_arg( 'tab', 'ticketsonic' ) ) ); ?>"><?php _e( 'TicketSonic', 'ts_yte' ); ?></a></h3>
	<p><?php _e( 'Preview live data in TicketSonic.', 'ts_yte' ); ?></p>
</div>
<!-- .overview-left -->
<div class="ts-welcome-panel overview-right">
	<h3>
		<!-- <span><a href="#"><attr title="<?php _e( 'Dismiss this message', 'ts_yte' ); ?>"><?php _e( 'Dismiss', 'ts_yte' ); ?></attr></a></span> -->
		<?php _e( 'Status', 'ts_yte' ); ?>
	</h3>
	<?php 
	if ( ! is_writable( TS_YTE_UPLOADPATH ) ) {
		print '<span class="dashicons dashicons-no"></span><span> Ensure the plugin upload folder ' . esc_html( TS_YTE_UPLOADPATH ) . ' is writable</span>';
	} else {
		print '<span class="dashicons dashicons-yes"></span><span> Plugin upload folder is writable</span>';
	}
	?>
</div>
<!-- .overview-right -->
