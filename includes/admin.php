<?php
// Display admin notice on screen load
function woo_ts_admin_notice( $message = '', $priority = 'updated', $screen = '' ) {
	if ( false === $priority || '' === $priority )
		$priority = 'updated';

	if ( '' !== $message ) {
		ob_start();
		woo_ts_admin_notice_html( $message, $priority, $screen );
		$output = ob_get_contents();
		ob_end_clean();

		// Check if an existing notice is already in queue
		$existing_notice = get_transient( WOO_TS_PREFIX . '_notice' );

		if ( false !== $existing_notice ) {
			$existing_notice = base64_decode( $existing_notice );
			$output = $existing_notice . $output;
		}

		$response = set_transient( WOO_TS_PREFIX . '_notice', base64_encode( $output ), MINUTE_IN_SECONDS );

		// Check if the Transient was saved
		if ( false !== $response )
			add_action( 'admin_notices', 'woo_ts_admin_notice_print' );
	}

}

// HTML template for admin notice
function woo_ts_admin_notice_html( $message = '', $priority = 'updated', $screen = '' ) {
	// Display admin notice on specific screen
	if ( ! empty( $screen ) ) {
		global $pagenow;

		if ( is_array( $screen ) ) {
			if ( false === in_array( $pagenow, $screen ) )
				return;
		} else {
			if ( $pagenow !== $screen )
				return;
		}

	} ?>
<div id="message" class="<?php echo $priority; ?>">
	<p><?php echo $message; ?></p>
</div>
<?php

}

// Grabs the WordPress transient that holds the admin notice and prints it
function woo_ts_admin_notice_print() {
	$output = get_transient( WOO_TS_PREFIX . '_notice' );
	if ( false !== $output ) {
		delete_transient( WOO_TS_PREFIX . '_notice' );
		$output = base64_decode( $output );
		echo $output;
	}
}

function woo_ts_template_header( $title = '', $icon = 'woocommerce' ) { ?>
<div id="woo-pi" class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32 icon32-woocommerce-importer"><br /></div>
	<h2><?php echo $title; ?></h2>
<?php

}

function woo_ts_template_footer() { ?>
</div>
<!-- .wrap -->
<?php

}

// Add Product Import to WordPress Administration menu
function woo_ts_admin_menu() {
	$page = add_submenu_page( 'woocommerce', __( 'TicketSonic', 'woo_ts' ), __( 'TicketSonic', 'woo_ts' ), 'manage_woocommerce', 'woo_ts', 'woo_ts_html_page' );
	add_action( 'admin_print_styles-' . $page, 'woo_ts_enqueue_scripts' );
}
add_action( 'admin_menu', 'woo_ts_admin_menu', 11 );

function woo_ts_enqueue_scripts( $hook ) {
	// Simple check that WooCommerce is activated
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
	}

	wp_enqueue_style( 'woo_ts_styles', plugins_url( '/templates/admin/import.css', WOO_TS_RELPATH ) );
	wp_enqueue_script( 'woo_ts_scripts', plugins_url( '/templates/admin/import.js', WOO_TS_RELPATH ), array( 'jquery' ) );
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'woo_vm_styles', plugins_url( '/templates/admin/woocommerce-admin_dashboard_vm-plugins.css', WOO_TS_RELPATH ) );
}

function woo_ts_admin_active_tab( $tab_name = null, $tab = null ) {
	if ( isset( $_GET['tab'] ) && ! $tab )
		$tab = $_GET['tab'];
	else if ( ! isset( $_GET['tab'] ) && woo_ts_get_option( 'skip_overview', false ) )
		$tab = 'import';
	else
		$tab = 'overview';

	$output = '';
	if ( isset( $tab_name ) && $tab_name ) {
		if ( $tab_name === $tab )
			$output = ' nav-tab-active';
	}
	echo $output;
}

function woo_ts_tab_template( $tab = '' ) {
	global $import;

	if ( ! $tab)
		$tab = 'overview';

	switch ( $tab ) {
		case 'overview':
			$skip_overview = woo_ts_get_option( 'skip_overview', false );
			break;

		case 'import':
			if ( isset( $_GET['import'] ) && WOO_TS_PREFIX === $_GET['import'] )
				$url = 'import';
			if ( isset( $_GET['page'] ) && WOO_TS_PREFIX === $_GET['page'] )
				$url = 'page';
			break;

		case 'settings':
			$api_key                 = woo_ts_get_option( 'api_key', '' );
			$api_userid              = woo_ts_get_option( 'api_userid', '' );
			$ticket_info_endpoint    = woo_ts_get_option( 'ticket_info_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/list' );
			$event_info_endpoint     = woo_ts_get_option( 'event_info_endpoint', 'https://www.ticketsonic.com:9507/v1/event/list' );
			$new_event_endpoint      = woo_ts_get_option( 'new_event_endpoint', 'https://www.ticketsonic.com:9507/v1/event/new' );
			$new_ticket_endpoint     = woo_ts_get_option( 'new_ticket_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/new' );
			$change_ticket_endpoint  = woo_ts_get_option( 'change_ticket_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/edit' );
			$change_event_endpoint   = woo_ts_get_option( 'change_event_endpoint', 'https://www.ticketsonic.com:9507/v1/event/edit' );
			$external_order_endpoint = woo_ts_get_option( 'external_order_endpoint', 'https://www.ticketsonic.com:9507/v1/order/new' );
			$event_id                = woo_ts_get_option( 'event_id', '' );
			$email_subject           = woo_ts_get_option( 'email_subject', 'Ticket #[ticket_number] - [ticket_title] for the Your Event is ready' );
			$email_body              = woo_ts_get_option(
				'email_body',
				'
				<html lang="en">
					<head>
						<style type="text/css">
						table {
								border-spacing: 0;
						}
						td.black-square {
							background-color: black;
							width: 2px;
							height: 4px;
						}

						td.white-square {
							background-color: white;
							width: 2px;
							height: 4px;
						}
						</style>
					</head>
					<body style="width: 100%;margin: 50px;padding: 0px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #dbe5ea;">
						<div style="width: 500px; margin: auto;">
							<div id="header">
								<div style="background-color: 537895; border-top-left-radius: 5px;border-top-right-radius: 5px; height: 50px; text-align: center;">
								</div>
							</div>
							<div id="body" style="background: white; padding: 10px;">
								<div style="float: left; height: 140px; margin-right: 20px;;">
									[ticket_qr]
								</div>
								<div style="height: 140px">
									<div style="margin: 10px">
										[ticket_title]
									</div>
									<div style="margin: 10px">
										[ticket_description]
									</div>
									<div style="margin: 10px">
										[ticket_price]
									</div>
								</div>
							</div>
							<div id="footer" style="border-top: 1px solid lightgray;">
								<div style="background-color: white; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px; color: gray; font-family: Helvetica, Arial, sans-serif; font-size: 12px; height: 50px; line-height: 50px; text-align: center;">©2022 Demo Conference</div>
							</div>
						</div>
					</body>
				</html>'
			);

			break;
	}

	if ( $tab ) {
		if ( file_exists( WOO_TS_PATH . 'templates/admin/tabs-' . $tab . '.php' ) )
			include_once WOO_TS_PATH . 'templates/admin/tabs-' . $tab . '.php';
	}
}
