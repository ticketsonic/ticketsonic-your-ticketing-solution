<div id="content">
	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php woo_ts_admin_active_tab( 'overview' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'woo_ts', 'tab' => 'overview' ), 'admin.php' ); ?>"><?php _e( 'Overview', 'woo_ts' ); ?></a>
		<a data-tab-id="export" class="nav-tab<?php woo_ts_admin_active_tab( 'import' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'woo_ts', 'tab' => 'import' ), 'admin.php' ); ?>"><?php _e( 'Import', 'woo_ts' ); ?></a>
		<a data-tab-id="settings" class="nav-tab<?php woo_ts_admin_active_tab( 'settings' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'woo_ts', 'tab' => 'settings' ), 'admin.php' ); ?>"><?php _e( 'Settings', 'woo_ts' ); ?></a>
	</h2>
	<?php woo_ts_tab_template( $tab ); ?>
</div>