<div id="content">
	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php woo_ts_admin_active_tab( "overview" ); ?>" href="<?php echo add_query_arg( array( "page" => "woo_ts", "tab" => "overview" ), "admin.php" ); ?>"><?php _e( "Overview", "woo_ts" ); ?></a>
		<a data-tab-id="sync" class="nav-tab<?php woo_ts_admin_active_tab( "sync" ); ?>" href="<?php echo add_query_arg( array( "page" => "woo_ts", "tab" => "sync" ), "admin.php" ); ?>"><?php _e( "Sync", "woo_ts" ); ?></a>
		<a data-tab-id="settings" class="nav-tab<?php woo_ts_admin_active_tab( "settings" ); ?>" href="<?php echo add_query_arg( array( "page" => "woo_ts", "tab" => "settings" ), "admin.php" ); ?>"><?php _e( "Settings", "woo_ts" ); ?></a>
		<a data-tab-id="create-event" class="nav-tab<?php woo_ts_admin_active_tab( "create-event" ); ?>" href="<?php echo add_query_arg( array( "page" => "woo_ts", "tab" => "create-event" ), "admin.php" ); ?>"><?php _e( "Create event", "woo_ts" ); ?></a>
		<a data-tab-id="create-ticket" class="nav-tab<?php woo_ts_admin_active_tab( "create-ticket" ); ?>" href="<?php echo add_query_arg( array( "page" => "woo_ts", "tab" => "create-ticket" ), "admin.php" ); ?>"><?php _e( "Create ticket", "woo_ts" ); ?></a>
		<a data-tab-id="stats" class="nav-tab<?php woo_ts_admin_active_tab( "stats" ); ?>" href="<?php echo add_query_arg( array( "page" => "woo_ts", "tab" => "stats" ), "admin.php" ); ?>"><?php _e( "Statistics", "woo_ts" ); ?></a>
	</h2>
	<?php woo_ts_tab_template( $tab ); ?>
</div>