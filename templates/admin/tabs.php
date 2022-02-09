<div id="content">
	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab <?php ts_yts_admin_active_tab( 'overview' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yts', 'tab' => 'overview' ), 'admin.php' ); ?>"><?php _e( 'Overview', 'ts_yts' ); ?></a>
		<a data-tab-id="sync" class="nav-tab <?php ts_yts_admin_active_tab( 'sync' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yts', 'tab' => 'sync' ), 'admin.php' ); ?>"><?php _e( 'Sync', 'ts_yts' ); ?></a>
		<a data-tab-id="settings" class="nav-tab <?php ts_yts_admin_active_tab( 'settings' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yts', 'tab' => 'settings' ), 'admin.php' ); ?>"><?php _e( 'Settings', 'ts_yts' ); ?></a>
		<a data-tab-id="ticketsonic" class="nav-tab <?php ts_yts_admin_active_tab( 'ticketsonic' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yts', 'tab' => 'ticketsonic' ), 'admin.php' ); ?>"><?php _e( 'TicketSonic', 'ts_yts' ); ?></a>
	</h2>
	<?php ts_yts_tab_template( $tab ); ?>
</div>
