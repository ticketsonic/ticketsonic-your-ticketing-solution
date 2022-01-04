<?php
/*
Plugin Name: WooCommerce - TicketSonic
Plugin URI: https://github.com/ticketsonic/woocommerce-ticketsonic
Description: Sync Tickets into your WooCommerce store from the TicketSonic system
Version: 0.1
Author: Martin Vassilev
Author URI: https://github.com/ticketsonic/
License: GPL2

Text Domain: woo-ts
Domain Path: /languages/

WC requires at least: 2.3
WC tested up to: 3.6
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'WOO_TS_FILE', __FILE__ );
define( 'WOO_TS_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'WOO_TS_RELPATH', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'WOO_TS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_TS_PREFIX', 'woo_ts' );
define( 'WOO_TS_PLUGINPATH', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) );

$uploads_dir = wp_get_upload_dir();

define( 'WOO_TS_UPLOADPATH', $uploads_dir['basedir'] . '/' . WOO_TS_DIRNAME );
define( 'WOO_TS_UPLOADURLPATH', $uploads_dir['baseurl'] . '/' . WOO_TS_DIRNAME );

include_once( WOO_TS_PATH . 'includes/functions.php' );

if( is_admin() ) {
	function woo_ts_register_importer() {
		register_importer( 'woo_ts', __( 'Tickets', 'woocommerce-ticketsonic' ), __( '<strong>Tickets Importer</strong> - Import Tickets into WooCommerce from TicketSonic.', 'woo_ts' ), 'woo_ts_html_page' );
	}
	add_action( 'admin_init', 'woo_ts_register_importer' );

	// Initial scripts and import process
	function woo_ts_admin_init() {
		// Check the User has the manage_woocommerce_products capability
		if( current_user_can( 'manage_woocommerce' ) == false )
			return;

		woo_ts_import_init();
		woo_ts_structure_init();

	}
	add_action( 'admin_init', 'woo_ts_admin_init' );

	function woo_ts_html_page() {
		// Check the User has the manage_woocommerce capability
		if( current_user_can( 'manage_woocommerce' ) == false )
			return;

		$title = __( 'TicketSonic', 'woo_ts' );

		woo_ts_template_header( $title );
		woo_ts_manage_form();
		woo_ts_template_footer();
	}

	// HTML template for Import screen
	function woo_ts_manage_form() {
		$tab = false;
		if( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		} else if( woo_ts_get_option( 'skip_overview', false ) ) {
			// If Skip Overview is set then jump to Export screen
			$tab = 'import';
		}
		$url = add_query_arg( 'page', 'woo_ts' );

		include_once( WOO_TS_PATH . 'templates/admin/tabs.php' );
	}
	/* End of: WordPress Administration */
}
