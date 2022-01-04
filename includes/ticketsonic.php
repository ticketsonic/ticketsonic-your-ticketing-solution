<?php

require('cryptography.php');
require('ticketsonic.inc');

require( dirname( __FILE__ ) . '/../vendor/autoload.php');

function request_create_new_event( $url, $email,  $key, $event_title, $event_description, $event_datetime, $event_location, $tickets_data, $badge_text_horizontal_location, $badge_text_vartical_location, $badge_primary_text_fontsize, $badge_secondary_text_fontsize, $badge_primary_text_color, $badge_secondary_text_color ) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key,
	);

	foreach ($tickets_data as $k => $value) {
		$tickets_data[$k]['price'] = intval($value['price']) * 100;
	}

	$badge_background = WOO_TS_UPLOADURLPATH . '/badge_background.jpg';

	$body = array(
		'primary_text_pl' => $event_title,
		'secondary_text_pl' => $event_description,
		'datetime' => $event_datetime,
		'location' => $event_location,
		'tickets' => $tickets_data,
		'request_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
		'badge_background' => base64_encode(file_get_contents($badge_background)),
		'badge_text_horizontal_location' => $badge_text_horizontal_location,
		'badge_text_vertical_location' => $badge_text_vartical_location,
		'badge_primary_text_fontsize' => $badge_primary_text_fontsize,
		'badge_secondary_text_fontsize' => $badge_secondary_text_fontsize,
		'badge_primary_text_color' => $badge_primary_text_color,
		'badge_secondary_text_color' => $badge_secondary_text_color
	);

	$response = post_request_to_remote($url, $headers, $body);

	if ($response['status'] == 'error') {
		woo_ts_admin_notice('Error sending new event request: ' . $response['message'] , 'error');
		return;
	}

	return $response;
}

function request_create_new_ticket($url, $email, $key, $ticket_eventid, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key,
		'x-api-eventid' => $ticket_eventid
	);

	$ticket_price = intval($ticket_price) * 100;
	$body = array(
		'primary_text_pl' => $ticket_title,
		'secondary_text_pl' => $ticket_description,
		'price' => $ticket_price,
		'currency' => $ticket_currency,
		'stock' => $ticket_stock
	);
	$response = post_request_to_remote($url, $headers, $body);

	if ($response['status'] == 'error') {
		woo_ts_admin_notice('Error sending new ticket request: ' . $response['message'] , 'error');
		return;
	}

	return $response;
}

function request_change_ticket($url, $email, $key, $ticket_sku, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key,
		'x-api-sku' => $ticket_sku
	);

	$ticket_price = intval($ticket_price * 100);
	$body = array(
		'primary_text_pl' => $ticket_title,
		'secondary_text_pl' => $ticket_description,
		'price' => $ticket_price,
		'currency' => $ticket_currency,
		'stock' => $ticket_stock
	);
	$response = post_request_to_remote($url, $headers, $body);

	if ($response['status'] == 'error') {
		woo_ts_admin_notice('Error sending new ticket request: ' . $response['message'] , 'error');
		return;
	}

	return $response;
}

function request_change_event($url, $email, $key, $event_id, $event_title, $event_description, $event_location, $event_starttime, $event_badge_data) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key,
		'x-api-eventid' => $event_id
	);

	$body = array(
		'primary_text_pl' => $event_title,
		'secondary_text_pl' => $event_description,
		'location' => $event_location,
		'start_datetime' => $event_starttime,
		'badge_data' => json_encode($event_badge_data)
	);
	$response = post_request_to_remote($url, $headers, $body);

	if ($response['status'] == 'error') {
		woo_ts_admin_notice('Error sending new ticket request: ' . $response['message'] , 'error');
		return;
	}

	return $response;
}

function get_tickets_with_remote($url, $email, $key, $event_id) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key,
		'x-api-eventid' => $event_id
	);

	$response = get_request_from_remote($url, $headers, null);

	return $response;
}

function get_events_data_from_remote($url, $email, $key) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key,
	);

	$response = get_request_from_remote($url, $headers, null);
	return $response;
}

function get_event_ticket_data_from_remote($url, $email, $key, $event_id) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key,
		'x-api-eventid' => $event_id
	);

	$response = get_request_from_remote($url, $headers, null);
	return $response;
}

function request_create_tickets_order_in_remote($order_id, $url, $email, $key) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key' => $key
	);
	$body = prepare_order_tickets_request_body($order_id, $email, $key);

	$response = post_request_to_remote($url, $headers, $body);

	return $response;
}

function prepare_order_tickets_request_body($order_id, $email, $key) {
	$order = wc_get_order($order_id);

	$data = array(
		'start_time' => null,
		'end_time' => null,
		'group' => null
	);

	if (class_exists('Booked_WC_Appointment')) {
		$appointment_id = $order->get_meta('_booked_wc_order_appointments');
		$appointment = Booked_WC_Appointment::get($appointment_id[0]);
		$from_to_arr = explode('-', $appointment->timeslot);
		$from_date = date_create_from_format('Hi', $from_to_arr[0]);
		$to_date = date_create_from_format('Hi', $from_to_arr[1]);
		$interval = date_diff($to_date, $from_date);
		$minutes_diff = $interval->d * 24 * 60;
		$minutes_diff += $interval->h * 60;
		$minutes_diff += $interval->i;

		$data['start_time'] = strval(intval($appointment->timestamp));
		$data['end_time'] = strval(intval($appointment->timestamp) + $minutes_diff * 60);
	}

	$body = array(
		'order_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
		'order_details' => array(
			'customer_billing_name' => get_customer_name($order),
			'customer_billing_company' => get_customer_company($order)
		),
		'tickets' => array()
	);

	$items = $order->get_items();
	foreach($items as $item) {
		$ticket = new WC_Product_Simple($item['product_id']);
		$body['tickets'][] = array(
			'sku' => $ticket->get_sku(),
			'stock' => $item['quantity'],
			'start_time' => $data['start_time'],
			'end_time' => $data['end_time']
		);
	}

	return $body;
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

?>
