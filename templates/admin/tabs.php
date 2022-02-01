<div id="content">
	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php ts_yte_admin_active_tab( 'overview' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yte', 'tab' => 'overview' ), 'admin.php' ); ?>"><?php _e( 'Overview', 'ts_yte' ); ?></a>
		<a data-tab-id="sync" class="nav-tab<?php ts_yte_admin_active_tab( 'sync' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yte', 'tab' => 'sync' ), 'admin.php' ); ?>"><?php _e( 'Sync', 'ts_yte' ); ?></a>
		<a data-tab-id="settings" class="nav-tab<?php ts_yte_admin_active_tab( 'settings' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yte', 'tab' => 'settings' ), 'admin.php' ); ?>"><?php _e( 'Settings', 'ts_yte' ); ?></a>
		<a data-tab-id="ticketsonic" class="nav-tab<?php ts_yte_admin_active_tab( 'ticketsonic' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'ts_yte', 'tab' => 'ticketsonic' ), 'admin.php' ); ?>"><?php _e( 'TicketSonic', 'ts_yte' ); ?></a>
	</h2>
	<?php ts_yte_tab_template( $tab ); ?>
</div>
