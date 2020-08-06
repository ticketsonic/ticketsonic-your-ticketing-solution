<?php

require('helper.php');
require('eventhome.php');
require('cryptography.php');

if (is_admin()) {
	include_once( WOO_TS_PATH . 'includes/admin.php' );

	function woo_ts_import_init() {
		global $wpdb;
		$wpdb->hide_errors();
		@ob_start();

		$action = woo_ts_get_action();
		switch( $action ) {
			case 'save-settings':
				woo_ts_update_option( 'api_key', ( isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '' ) );
				woo_ts_update_option( 'api_userid', ( isset( $_POST['api_userid'] ) ? sanitize_text_field( $_POST['api_userid'] ) : '' ) );
				woo_ts_update_option( 'email_subject', ( isset( $_POST['email_subject'] ) ? sanitize_text_field( $_POST['email_subject'] ) : '' ) );
				woo_ts_update_option( 'email_body', ( isset( $_POST['email_body'] ) ? wp_kses($_POST['email_body'], allowed_html()) : '' ) );
				woo_ts_update_option( 'ticket_info_endpoint', ( isset( $_POST['ticket_info_endpoint'] ) ? sanitize_text_field( $_POST['ticket_info_endpoint'] ) : '' ) );
				woo_ts_update_option( 'external_order_endpoint', ( isset( $_POST['external_order_endpoint'] ) ? sanitize_text_field( $_POST['external_order_endpoint'] ) : '' ) );

				upload_custom_ticket_background();

				$message = __( 'Settings saved.', 'woo_ts' );
				woo_ts_admin_notice( $message );
				break;

			case 'sync_with_ts':
				$url = woo_ts_get_option('ticket_info_endpoint', '');
				$email = woo_ts_get_option('api_userid', '');
				$key = woo_ts_get_option('api_key', '');
				$helper = new Helper();
				$result = $helper->sync_tickets_with_remote($url, $email, $key);

				woo_ts_admin_notice('Synced tickets: ' . $result['imported_count'], 'notice');
				woo_ts_admin_notice('Public key: ' . $result['user_public_key'], 'notice');
				woo_ts_update_option('user_public_key', '-----BEGIN PUBLIC KEY-----\n' . $result['user_public_key'] . '\n-----END PUBLIC KEY-----');

				break;
		}
	}

	// Add plugin ticket term
	function woo_ts_structure_init() {
		wp_insert_term('Ticket\'s HIT Tickets','product_cat',
			array(
			'description'=> 'Ticket’s HIT Tickets imported tickets.',
			'slug' => 'ticketshit'
			)
		);

		// TODO: Add catch handler
		wp_mkdir_p(WOO_TS_TICKETSDIR);
		wp_mkdir_p(WOO_TS_UPLOADPATH);
	}
}

add_action('woocommerce_payment_complete', 'mysite_woocommerce_payment_complete');
function mysite_woocommerce_payment_complete($order_id) {
	// write_log('mysite_woocommerce_payment_complete for order ' . $order_id . ' is fired');
}

add_action('woocommerce_order_status_processing', 'order_tickets_in_remote', 10, 1);
function order_tickets_in_remote($order_id) {
	$endpoint_url = woo_ts_get_option('external_order_endpoint', '');
	$api_userid = woo_ts_get_option('api_userid', '');
	$promoter_api_key = woo_ts_get_option('api_key', '');
	$helper = new Helper();
	$helper->order_tickets_in_remote($order_id, $endpoint_url, $api_userid, $promoter_api_key);
}

add_action('woocommerce_order_status_completed', 'send_tickets_to_customer_after_order_completed', 10, 1);
function send_tickets_to_customer_after_order_completed($order_id) {
	$endpoint_url = woo_ts_get_option('external_order_endpoint', '');
	$api_userid = woo_ts_get_option('api_userid', '');
	$promoter_api_key = woo_ts_get_option('api_key', '');
	$helper = new Helper();
	$helper->send_tickets_to_customer_after_order_completed($order_id, $endpoint_url, $api_userid, $promoter_api_key);
}

add_action( 'woocommerce_admin_order_data_after_order_details', 'display_ticket_links_in_order_details' );
function display_ticket_links_in_order_details($order) {
	$helper = new Helper();
	$helper->display_ticket_links_in_order_details($order);
}

add_action( 'admin_notices', 'ticketsdir_writable_error_message' );
function ticketsdir_writable_error_message() {
    if (!is_writable(WOO_TS_TICKETSDIR)) {
		print '<div class="error notice">';
		print    '<p>Ensure ' . WOO_TS_TICKETSDIR . ' is writable</p>';
		print '</div>';
	} else {
		print '<div class="notice notice-success">';
		print    '<p>' . WOO_TS_TICKETSDIR . ' is writable</p>';
		print '</div>';
	}
}

add_action( 'admin_notices', 'uploadpath_writable_error_message' );
function uploadpath_writable_error_message() {
    if (!is_writable(WOO_TS_UPLOADPATH)) {
		print '<div class="error notice">';
		print    '<p>Ensure ' . WOO_TS_UPLOADPATH . ' is writable</p>';
		print '</div>';
	} else {
		print '<div class="notice notice-success">';
		print    '<p>' . WOO_TS_UPLOADPATH . ' is writable</p>';
		print '</div>';
	}
}


function woo_ts_get_action( $prefer_get = false ) {
	if ( isset( $_GET['action'] ) && $prefer_get )
		return sanitize_text_field( $_GET['action'] );

	if ( isset( $_POST['action'] ) )
		return sanitize_text_field( $_POST['action'] );

	if ( isset( $_GET['action'] ) )
		return sanitize_text_field( $_GET['action'] );

	return false;
}

function woo_ts_get_option( $option = null, $default = false, $allow_empty = false ) {
	$output = '';
	if( isset( $option ) ) {
		$separator = '_';
		$output = get_option( WOO_TS_PREFIX . $separator . $option, $default );
		if( $allow_empty == false && $output != 0 && ( $output == false || $output == '' ) )
			$output = $default;
	}
	return $output;
}

function woo_ts_update_option( $option = null, $value = null ) {
	$output = false;
	if( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output = update_option( WOO_TS_PREFIX . $separator . $option, $value );
	}
	return $output;
}

function wpse_141088_upload_dir( $dir ) {
    return array(
        'path'   => WOO_TS_UPLOADPATH,
        'url'    => WOO_TS_UPLOADPATH,
        'subdir' => '/' . WOO_TS_DIRNAME,
    ) + $dir;
}

function upload_custom_ticket_background() {
    add_filter( 'upload_dir', 'wpse_141088_upload_dir' );
    include_once(ABSPATH . 'wp-admin/includes/file.php' );
    include_once(ABSPATH . 'wp-admin/includes/media.php');
    
    $allowed_file_types = array('jpg' =>'image/jpeg','jpeg' =>'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
    $upload_overrides = array( 'test_form' => false, 'unique_filename_callback' => 'filename_renamer', 'mimes' => $allowed_file_types);
    $uploadedfile = $_FILES['fileToUpload'];

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    if (!$movefile || isset($movefile['error']) ) {
        woo_ts_admin_notice($movefile['error'], 'error');
    }

    remove_filter( 'upload_dir', 'wpse_141088_upload_dir' );
}

function filename_renamer($dir, $name, $ext){
    return 'pdf_background' . $ext;
}

function allowed_html() {
	return array (
		'html' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'body' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'style' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'table' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'tbody' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'th' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'tr' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'td' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'div' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'p' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'h1' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
	);
}

?>