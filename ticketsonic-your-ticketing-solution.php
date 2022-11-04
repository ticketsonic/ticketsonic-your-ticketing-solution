<?php

/**
 * Plugin Name: TicketSonic - your Ticketing Solution
 * Plugin URI: https://github.com/ticketsonic/ticketsonic-your-ticketing-solution
 * Description: TicketSonic is the ticketing solution effortlessly enabling every web and mobile to become an independent ticket seller
 * Version: 1.3.3
 * Author: TicketSonic
 * Author URI: https://www.ticketsonic.com
 * License: GPL2

 * Text Domain: woo-ts
 * Domain Path: /languages/

 * WC requires at least: 2.3
 * WC tested up to: 6.1
 *
 * @package ticketsonic-your-ticketing-solution
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'TS_YTS_FILE', __FILE__ );
define( 'TS_YTS_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'TS_YTS_RELPATH', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'TS_YTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'TS_YTS_PREFIX', 'ts_yts' );
define( 'TS_YTS_PLUGINPATH', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) );

$uploads_dir = wp_get_upload_dir();

define( 'TS_YTS_UPLOADPATH', $uploads_dir['basedir'] . '/' . TS_YTS_DIRNAME );
define( 'TS_YTS_UPLOADURLPATH', $uploads_dir['baseurl'] . '/' . TS_YTS_DIRNAME );

require_once TS_YTS_PATH . 'includes/functions.php';

if ( is_admin() ) {
	function ts_yts_register_importer() {
		register_importer( 'ts_yts', __( 'Tickets', 'woo-ts' ), __( '<strong>TicketSonic Integrator</strong> - Integrate you WooCommerce store with TicketSonic.', 'woo-ts' ), 'ts_yts_html_page' );
	}
	add_action( 'admin_init', 'ts_yts_register_importer' );

	function ts_yts_admin_init() {
		// Check the User has the manage_woocommerce_products capability.
		if ( current_user_can( 'manage_woocommerce' ) === false )
			return;

		ts_yts_import_init();
		ts_yts_structure_init();
	}
	add_action( 'admin_init', 'ts_yts_admin_init' );

	function ts_yts_html_page() {
		// Check the User has the manage_woocommerce capability.
		if ( current_user_can( 'manage_woocommerce' ) === false )
			return;

		$title = __( 'TicketSonic', 'ts_yts' );

		ts_yts_template_header( $title );
		ts_yts_manage_form();
		ts_yts_template_footer();
	}

	function ts_yts_manage_form() {
		$tab = false;
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		} else if ( ts_yts_get_option( 'skip_overview', false ) ) {
			// If Skip Overview is set then jump to Export screen.
			$tab = 'import';
		}
		$url = add_query_arg( 'page', 'ts_yts' );

		include_once TS_YTS_PATH . 'templates/admin/tabs.php';
	}
	/* End of: WordPress Administration */
}

/**
 * Add plugin settings link in the plugins list.
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ts_yts_settings_page');
function ts_yts_settings_page($links) {
	$url = get_admin_url() . "admin.php?page=ts_yts";
	$settings_link = '<a href="' . $url . '">Settings</a>';
	$links[] = $settings_link;
	return $links;
}

/**
 * Check for WooCommerce availability.
 */
add_action( 'admin_init', 'ts_yts_plugin_has_parents' );
function ts_yts_plugin_has_parents() {
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

		add_action( 'admin_notices', 'ts_yts_plugin_notice' );

		deactivate_plugins( plugin_basename( __FILE__ ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

/**
 * Display notice regarding the WooCommerce requirement.
 */
function ts_yts_plugin_notice() {
	print( '<div class="error"><p>Sorry, TicketSonic - your Ticketing Solution requires WooCommerce to be installed and activated. You can download WooCommerce <a href="https://woocommerce.com/">here</a>.</p></div>' );
}
