<?php

require("mpdf_generator.php");
require("cryptography.php");
require( dirname( __FILE__ ) . '/../vendor/autoload.php');

function request_create_new_event($url, $email, $key, $event_title, $event_description, $event_datetime, $event_location, $tickets_data) {
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key,
    );

    foreach ($tickets_data as $k => $value) {
        $tickets_data[$k]["price"] = intval($value["price"]) * 100;
    }

    $body = array(
        "body" => array(
            "title" => $event_title,
            "description" => $event_description,
            "datetime" => $event_datetime,
            "location" => $event_location,
            "tickets" => $tickets_data,
            "request_hash" => bin2hex(openssl_random_pseudo_bytes(16))
        ),
        "headers" => $headers
    );

    $response = post_request_to_remote($url, $headers, $body);

    if ($response["status"] == "error") {
        woo_ts_admin_notice("Error sending new event request: " . $response["message"] , "error");
        return;
    }

    return $response;
}

function request_create_new_ticket($url, $email, $key, $ticket_eventid, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock) {
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key,
        "x-api-eventid" => $ticket_eventid
    );

    $ticket_price = intval($ticket_price) * 100;
    $body = array(
        "body" => array(
            "title" => $ticket_title,
            "description" => $ticket_description,
            "price" => $ticket_price,
            "currency" => $ticket_currency,
            "stock" => $ticket_stock
        ),
        "headers" => $headers
    );
    $response = post_request_to_remote($url, $headers, $body);

    if ($response["status"] == "error") {
        woo_ts_admin_notice('Error sending new ticket request: ' . $response["message"] , 'error');
        return;
    }

    return $response;
}

function sync_tickets_with_remote($url, $email, $key, $event_id) {
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key,
        "x-api-eventid" => $event_id
    );
    
    $response = get_request_from_remote($url, $headers, null);

    if ($response["status"] == "error") {
        woo_ts_admin_notice("Error syncing tickets: " . $response["message"] , "error");
        return;
    }

    $imported_count = 0;
    foreach ($response['tickets'] as $key => $ticket) {
        $woo_product_id = wc_get_product_id_by_sku($ticket['sku']);

        $ticket_obj = new WC_Product_Simple();

        // Ticket does not exist so we skip
        if ($woo_product_id != 0) {
            $ticket_obj = new WC_Product_Simple($woo_product_id);
        }

        $ticket_obj->set_sku($ticket['sku']);
        $ticket_obj->set_name($ticket['ticket_title_en'] . ' ' . $ticket['ticket_description_en']);
        $ticket_obj->set_status('publish');
        $ticket_obj->set_catalog_visibility('visible');
        $ticket_obj->set_description($ticket['ticket_description_en']);
        
        $price = (int)$ticket['price'] / 100;
        $ticket_obj->set_price($price);
        $ticket_obj->set_regular_price($price);
        $ticket_obj->set_manage_stock(true);
        $ticket_obj->set_stock_quantity($ticket['stock']);
        $ticket_obj->set_stock_status('instock');
        $ticket_obj->set_sold_individually(false);
        $ticket_obj->set_downloadable(true);
        $ticket_obj->set_virtual(true);

        $ticketshit_term = get_term_by('slug', 'ticketshit', 'product_cat');
        if ($ticketshit_term) {
            $ticket_obj->set_category_ids(array($ticketshit_term->term_id));
        }

        $woo_ticket_id = $ticket_obj->save();

        $imported_count++;
    }

    $result = array("status" => "success", "message" => "Number of imported tickets: " . $imported_count, "user_public_key" => $response['user_public_key']);
    return $result;
}

function get_events_data_from_remote($url, $email, $key) {
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key,
    );

    $response = get_request_from_remote($url, $headers, null);
    return $response;
}

function get_event_ticket_data_from_remote($url, $email, $key, $event_id) {
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key,
        "x-api-eventid" => $event_id
    );

    $response = get_request_from_remote($url, $headers, null);
    return $response;
}

function request_order_tickets_in_remote($order_id, $url, $email, $key) {
    $order = wc_get_order($order_id);

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
    write_log('request_barcodes_from_ts for order ' . $order_id . ' is fired');
    write_log('sending req to TS');
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key
    );
    $body = prepare_order_tickets_request_body($order_id, $email, $key, $data);
    $response = post_request_to_remote($url, $headers, $body);

    write_log('result from the request to TS for ' . $order_id . ' is received');

    $order = wc_get_order($order_id);
    if ($response['status'] != 'success') {
        write_log('Error fetching result for order ' . $order_id . ': '. $response['message']);
        $order->update_status('failed', 'Error fetching result for order ' . $order_id . ': '. $response['message']);
        return;
    }
    write_log('$json_response is: ' . print_r($response, 1));

    $ticket_file_paths = generate_ticket_files($response, $order_id);
    write_log('File tickets generation for order ' . $order_id . ' is completed');
    
    $order->add_meta_data('ticket_file_paths', $ticket_file_paths);
    $order->save();
    write_log('Order meta for ticket files for order ' . $order_id . ' is saved');

    return $order;
}

function prepare_order_tickets_request_body($order_id, $email, $key, $data) {
    $order = wc_get_order($order_id);
    $body = array(
        'headers' => array(
            'api_userid' => $email,
            'api_key' => $key,
        ),
        'payload' => array(
            'order_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
            'order_details' => array(
                'customer_billing_name' => get_customer_name($order),
                'customer_billing_company' => get_customer_company($order)
            ),
            'tickets' => array()
        )
    );

    $items = $order->get_items();
    foreach($items as $item) {
        $ticket = new WC_Product_Simple($item['product_id']);
        $body['payload']['tickets'][] = array(
            'sku' => $ticket->get_sku(),
            'stock' => $item['quantity'],
            'start_time' => $data["start_time"],
            'end_time' => $data["end_time"]
        );
    }

    return $body;
}

function generate_ticket_files($response, $order_id) {
    // TODO: Add a check if is writable
    wp_mkdir_p(WOO_TS_UPLOADPATH . '/' . $order_id . '/');

    $ticket_file_paths = array();
    
    $starttime = microtime(true);
    foreach ($response['tickets'] as $key => $ticket) {
        write_log('start generation of ticket file');
        $decoded = decode_barcode($ticket['encrypted_data']);
        if ($decoded == null)
            return null;

        $woo_product_id = wc_get_product_id_by_sku($ticket['sku']);
        $woo_product = new WC_Product_Simple($woo_product_id);

        // Create separate ticket files
        $temp = generate_file($woo_product->get_name(), $woo_product->get_description(), $decoded['formatted_price'], $decoded['sensitive_decoded'], $order_id, $key);
        $ticket_file_paths['ticket_file_abs_path'][] = $temp['ticket_file_abs_path'];
        $ticket_file_paths['ticket_file_url_path'][] = $temp['ticket_file_url_path'];
        write_log('end of generation of ticket file at: ' . date('Y-m-d H:i:s'));
    }

    $endtime = microtime(true);
    $temp = $endtime - $starttime;
    write_log('start: ' . $starttime);
    write_log('end: ' . $endtime);
    write_log('time diff: ' . $temp);
    
    return $ticket_file_paths;
}

function generate_file($name, $description, $price, $sensitive_decoded, $order_id, $key) {
    $file_generator = new MPDF_Generator();

    $ticket_file_abs_path = WOO_TS_UPLOADPATH . '/' . $order_id . '/' . $key . '.' . $file_generator->extension; // file extension will be appended by the generator
    $ticket_file_url_path = WOO_TS_UPLOADURLPATH . '/' . $order_id . '/' . $key . '.' . $file_generator->extension;; // file extension will be appended by the generator
    $file_generator->generate_ticket($name, $description, $price, $sensitive_decoded, $ticket_file_abs_path);
    
    $ticket_file_paths = array('ticket_file_url_path' => $ticket_file_url_path, 'ticket_file_abs_path' => $ticket_file_abs_path);
    return $ticket_file_paths;
}

function decode_barcode($encrypted_data) {
    $public_key = openssl_pkey_get_public(woo_ts_get_option('user_public_key', ''));
    if (!$public_key) {
        write_log('Public key corrupted');
        return null;
    }

    $result = array();
    $result['sensitive_decoded'] = base64_decode($encrypted_data);
    $result['is_decrypted'] = openssl_public_decrypt($result['sensitive_decoded'], $sensitive_decrypted, $public_key);
    $result['decrypted_ticket'] = parse_raw_recrypted_ticket($sensitive_decrypted);
    $result['formatted_price'] = floatval($result['decrypted_ticket']['price']) / 100 . ' ' . currency_to_ascii(get_woocommerce_currency());

    return $result;
}

function send_tickets_by_mail($target_user_mail, $order_id, $ticket_file_abs_paths) {
    write_log('send_tickets_to_email_after_payment_confirmed fired');
    if (!empty($ticket_file_abs_paths)) {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $mail_sent = wp_mail($target_user_mail, woo_ts_get_option('email_subject', ''), woo_ts_get_option('email_body', ''), $headers, $ticket_file_abs_paths);

        return $mail_sent;
    }

    return false;
}

function get_customer_name($order) {
    return $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
}
    
function get_customer_company($order) {
    return $order->get_billing_company();;
}

function get_request_from_remote($url, $headers, $body) {
    $http = new GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);
    $response = array();
    try {
        $response = $http->request('GET', $url, [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);
    
        $response = json_decode($response->getBody(), true);
    } catch (Exception $ex) {
        $response['status'] = 'error';
        $response['message'] = $ex->getMessage();
    }

    return $response;
}

function post_request_to_remote($url, $headers, $body) {
    $http = new GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);
    $response = array();
    try {
        $response = $http->request('POST', $url, [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);
    
        $response = json_decode($response->getBody(), true);
    } catch (Exception $ex) {
        $response['status'] = 'error';
        $response['message'] = $ex->getMessage();
    }

    return $response;
}

if (!function_exists('write_log')) {
    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(date('Y-m-d H:i:s') . ': ' . print_r($log, true));
            } else {
                error_log(date('Y-m-d H:i:s') . ': ' . $log);
            }
        }
    }
}

function currency_to_ascii($currency_code) {
    $currencies = array(
        "BGN" => "BGN",
        "USD" => "USD",
        "EUR" => "EUR",
        "GBP" => "GBP"
    );

    return $currencies[$currency_code];
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
