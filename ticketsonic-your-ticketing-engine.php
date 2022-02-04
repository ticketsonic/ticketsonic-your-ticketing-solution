<?php

/**
 * Plugin Name: TicketSonic - your Ticketing Engine
 * Plugin URI: https://github.com/ticketsonic/woocommerce-ticketsonic
 * Description: TicketSonic is the ticketing engine effortlessly enabling every web and mobile to become an independent ticket seller
 * Version: 1.0
 * Author: TicketSonic
 * Author URI: https://github.com/ticketsonic/
 * License: GPL2

 * Text Domain: woo-ts
 * Domain Path: /languages/

 * WC requires at least: 2.3
 * WC tested up to: 6.1.1
 *
 * @package woocommerce-ticketsonic
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'TS_YTE_FILE', __FILE__ );
define( 'TS_YTE_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'TS_YTE_RELPATH', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'TS_YTE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TS_YTE_PREFIX', 'ts_yte' );
define( 'TS_YTE_PLUGINPATH', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) );

$uploads_dir = wp_get_upload_dir();

define( 'TS_YTE_UPLOADPATH', $uploads_dir['basedir'] . '/' . TS_YTE_DIRNAME );
define( 'TS_YTE_UPLOADURLPATH', $uploads_dir['baseurl'] . '/' . TS_YTE_DIRNAME );

require_once TS_YTE_PATH . 'includes/functions.php';

if ( is_admin() ) {
	function ts_yte_register_importer() {
		register_importer( 'ts_yte', __( 'Tickets', 'woo-ts' ), __( '<strong>TicketSonic Integrator</strong> - Integrate you WooCommerce store with TicketSonic.', 'woo-ts' ), 'ts_yte_html_page' );
	}
	add_action( 'admin_init', 'ts_yte_register_importer' );

	function ts_yte_admin_init() {
		// Check the User has the manage_woocommerce_products capability.
		if ( current_user_can( 'manage_woocommerce' ) === false )
			return;

		ts_yte_import_init();
		ts_yte_structure_init();
	}
	add_action( 'admin_init', 'ts_yte_admin_init' );

	function ts_yte_html_page() {
		// Check the User has the manage_woocommerce capability.
		if ( current_user_can( 'manage_woocommerce' ) === false )
			return;

		$title = __( 'TicketSonic', 'ts_yte' );

		ts_yte_template_header( $title );
		ts_yte_manage_form();
		ts_yte_template_footer();
	}

	function ts_yte_manage_form() {
		$tab = false;
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		} else if ( ts_yte_get_option( 'skip_overview', false ) ) {
			// If Skip Overview is set then jump to Export screen.
			$tab = 'import';
		}
		$url = add_query_arg( 'page', 'ts_yte' );

		include_once TS_YTE_PATH . 'templates/admin/tabs.php';
	}
	/* End of: WordPress Administration */
}

// Plugin Settings Page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ts_yte_settings_page');
function ts_yte_settings_page($links) {
	$url = get_admin_url() . "admin.php?page=ts_yte";
	$settings_link = '<a href="' . $url . '">Settings</a>';
	$links[] = $settings_link;
	return $links;
}
