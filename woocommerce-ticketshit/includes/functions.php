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
				woo_ts_update_option( 'event_info_endpoint', ( isset( $_POST['event_info_endpoint'] ) ? sanitize_text_field( $_POST['event_info_endpoint'] ) : '' ) );
				woo_ts_update_option( 'new_event_endpoint', ( isset( $_POST['new_event_endpoint'] ) ? sanitize_text_field( $_POST['new_event_endpoint'] ) : '' ) );
				woo_ts_update_option( 'external_order_endpoint', ( isset( $_POST['external_order_endpoint'] ) ? sanitize_text_field( $_POST['external_order_endpoint'] ) : '' ) );
				woo_ts_update_option( 'event_id', ( isset( $_POST['event_id'] ) ? sanitize_text_field( $_POST['event_id'] ) : '' ) );

				upload_custom_ticket_background();

				$message = __( 'Settings saved.', 'woo_ts' );
				woo_ts_admin_notice( $message );
				break;

			case 'sync_with_ts':
				$url = woo_ts_get_option('ticket_info_endpoint', '');
				$email = woo_ts_get_option('api_userid', '');
				$key = woo_ts_get_option('api_key', '');
				$event_id = woo_ts_get_option('event_id', '');
				$helper = new Helper();
				$result = $helper->sync_tickets_with_remote($url, $email, $key, $event_id);

				if ($result != null) {
					woo_ts_admin_notice('Synced tickets: ' . $result['imported_count'], 'notice');
					woo_ts_admin_notice('Public key: ' . $result['user_public_key'], 'notice');
					woo_ts_update_option('user_public_key', "-----BEGIN PUBLIC KEY-----\n" . $result['user_public_key'] . "\n-----END PUBLIC KEY-----");
				}

				break;

			case 'create-event':
				$url = woo_ts_get_option('new_event_endpoint', '');
				if (empty($url)) {
					woo_ts_admin_notice("New Event Endpoint have to set in Settings", "error");
					return;
				}
				
				$email = woo_ts_get_option('api_userid', '');
				if (empty($email)) {
					woo_ts_admin_notice("Partner E-mail have to set in Settings", "error");
					return;
				}

				$key = woo_ts_get_option('api_key', '');
				if (empty($key)) {
					woo_ts_admin_notice("Partner API Key have to set in Settings", "error");
					return;
				}

				$event_title = $_POST['event_title'];
				if (empty($event_title)) {
					woo_ts_admin_notice("Event title field have to set", "error");
					return;
				}

				$event_description = $_POST['event_description'];
				$event_datetime = $_POST['event_datetime'];
				$event_location = $_POST['event_location'];
				
				$tickets_data = $_POST['ticket'];
				foreach ($tickets_data as $value) {
					if (empty($value["title"])) {
						woo_ts_admin_notice("Ticket title must be set", "error");

						return;
					}

					if (empty($value["price"])) {
						woo_ts_admin_notice("Ticket price must be set", "error");

						return;
					}

					if (!is_int(intval($value["price"]))) {
						woo_ts_admin_notice("Ticket price must be an integer number", "error");

						return;
					}

					if (empty($value["stock"])) {
						woo_ts_admin_notice("Ticket stock must be set", "error");

						return;
					}

					if (empty($value["currency"])) {
						woo_ts_admin_notice("Ticket currency must be set", "error");

						return;
					}
				}

				$helper = new Helper();
				$result = $helper->create_new_event($url, $email, $key, $event_title, $event_description, $event_datetime, $event_location, $tickets_data);

				if ($result["status"] == "success") {
					woo_ts_admin_notice("Status: success<br>Event ID: " . $result["event_id"] . " successfully sent for processing. You will receive an email when it is processed.", "notice");
				} else {
					woo_ts_admin_notice("Failed to request new event: " . $result["message"], "error");
				}

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
	$order = wc_get_order($order_id);
	$endpoint_url = woo_ts_get_option('external_order_endpoint', '');
	$api_userid = woo_ts_get_option('api_userid', '');
	$promoter_api_key = woo_ts_get_option('api_key', '');

	$data = array(
		"start_time" => null,
		"end_time" => null,
		"group" => null
	);

	if (class_exists("Booked_WC_Appointment")) {
		$appointment_id = $order->get_meta('_booked_wc_order_appointments');
		$appointment = Booked_WC_Appointment::get($appointment_id[0]);
		$from_to_arr = explode("-", $appointment->timeslot);
		$from_date = date_create_from_format('Hi', $from_to_arr[0]);
		$to_date = date_create_from_format('Hi', $from_to_arr[1]);
		$interval = date_diff($to_date, $from_date);
		$minutes_diff = $interval->d * 24 * 60;
		$minutes_diff += $interval->h * 60;
		$minutes_diff += $interval->i;

		$data["start_time"] = strval(intval($appointment->timestamp));
		$data["end_time"] = strval(intval($appointment->timestamp) + $minutes_diff * 60);
	}

	$helper = new Helper();
	$helper->order_tickets_in_remote($order_id, $endpoint_url, $api_userid, $promoter_api_key, $data);
}

/**
 * Add a custom action to order actions select box on edit order page
 * Ability to manually generate ticket files
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
add_action( 'woocommerce_order_actions', 'manual_ticket_generation_order_action' );
function manual_ticket_generation_order_action($actions) {
	global $theorder;

    $order_id = $theorder->id;
	$endpoint_url = woo_ts_get_option('external_order_endpoint', '');
	$api_userid = woo_ts_get_option('api_userid', '');
	$promoter_api_key = woo_ts_get_option('api_key', '');
	$helper = new Helper();

	$data = array(
		"start_time" => null,
		"end_time" => null,
		"group" => null
	);

	if (class_exists("Booked_WC_Appointment")) {
		$order = wc_get_order($order_id);
		$appointment_id = $order->get_meta('_booked_wc_order_appointments');
		$appointment = Booked_WC_Appointment::get($appointment_id[0]);
		$from_to_arr = explode("-", $appointment->timeslot);
		$from_date = date_create_from_format('Hi', $from_to_arr[0]);
		$to_date = date_create_from_format('Hi', $from_to_arr[1]);
		$interval = date_diff($to_date, $from_date);
		$minutes_diff = $interval->d * 24 * 60;
		$minutes_diff += $interval->h * 60;
		$minutes_diff += $interval->i;

		$data["start_time"] = intval($appointment->timestamp);
		$data["end_time"] = intval($appointment->timestamp) + $minutes_diff * 60;
	}

	//FIXME: check if arguments are not. Causes failure in WP if $endpoint_url, $api_userid, $promoter_api_key are null
	// $helper->order_tickets_in_remote($order_id, $endpoint_url, $api_userid, $promoter_api_key, $data);

    $actions['wc_manual_ticket_generation_order_action'] = __( 'Generate tickets', 'generate-tickets' );
    return $actions;
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