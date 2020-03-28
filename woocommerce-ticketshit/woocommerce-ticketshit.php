<?php
/*
Plugin Name: WooCommerce - Ticket Importer
Plugin URI: http://www.visser.com.au/woocommerce/plugins/product-importer/
Description: Import new Products into your WooCommerce store from simple formatted files (e.g. CSV, TXT, etc.).
Version: 1.3.1
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
License: GPL2

Text Domain: woocommerce-product-importer
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

// Turn this on to enable additional debugging options within the importer
//f( !defined( 'woo_ts_DEBUG' ) )
//	define( 'woo_ts_DEBUG', false );

include_once( WOO_TS_PATH . 'includes/functions.php' );
include_once( WOO_TS_PATH . 'includes/phpqrcode/qrlib.php' );

if( is_admin() ) {
	function woo_ts_register_importer() {
		register_importer( 'woo_ts', __( 'Tickets', 'woocommerce-ticketshit' ), __( '<strong>Tickets Importer</strong> - Import Tickets into WooCommerce from Ticket\'s HIT.', 'woo_ts' ), 'woo_ts_html_page' );
	}
	add_action( 'admin_init', 'woo_ts_register_importer' );

	// Initial scripts and import process
	function woo_ts_admin_init() {
		// Check the User has the manage_woocommerce_products capability
		if( current_user_can( 'manage_woocommerce' ) == false )
			return;

		// Check if Product Importer should run
		/*$product_importer = false;
		if( isset( $_GET['import'] ) || isset( $_GET['page'] ) ) {
			if( isset( $_GET['import'] ) ) {
				if( $_GET['import'] == WOO_TS_PREFIX )
					$product_importer = true;
			}
			if( isset( $_GET['page'] ) ) {
				if( $_GET['page'] == WOO_TS_PREFIX )
					$product_importer = true;
			}
		}
		if( $product_importer !== true )
			return;

		@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );*/
		woo_ts_import_init();

	}
	add_action( 'admin_init', 'woo_ts_admin_init' );

	// HTML templates and form processor for Product Importer screen
	function woo_ts_html_page() {

		global $import;

		// Check the User has the manage_woocommerce capability
		if( current_user_can( 'manage_woocommerce' ) == false )
			return;

		$action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );
		$title = __( 'Product Importer', 'woo_ts' );
		/*if( in_array( $action, array( 'upload', 'save' ) ) && !$import->cancel_import ) {
			if( $file = woo_ts_get_option( 'csv' ) )
				$title .= ': <em>' . basename( $file ) . '</em>';
		}

		$troubleshooting_url = 'http://www.visser.com.au/woocommerce/documentation/plugins/product-importer-deluxe/usage/';

		$woo_pd_url = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/';
		$woo_pd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Product Importer Deluxe', 'woo_ts' ) . '</a>', $woo_pd_url );*/

		woo_ts_template_header( $title );
		switch( $action ) {

			case 'save':
				// Display the opening Import tab if the import fails
				if( $import->cancel_import == false ) {
					include_once( WOO_TS_PATH . 'templates/admin/import_save.php' );
				} else {
					woo_ts_manage_form();
					return;
				}
				break;

			default:
				woo_ts_manage_form();
				break;

		}
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

?>