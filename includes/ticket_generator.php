<?php

require 'html_generator.php';
require 'mpdf_generator.php';

function generate_file_tickets($ts_response, $order_id) {
	$is_dir_created = wp_mkdir_p(WOO_TS_UPLOADPATH . '/' . $order_id . '/');
	if (!$is_dir_created) {
		return array('status' => 'failure', 'message' => 'Unable to create a folder for storing the ticket files');
	}

	$ticket_files_paths = array();
	$ticket_url_paths = array();

	foreach ($ts_response as $index => $ticket) {
		$decoded_barcode = decode_barcode($ticket['encrypted_data']);
		if ($decoded_barcode['status'] == 'failure')
			return $decoded_barcode;

		$woo_product_id = wc_get_product_id_by_sku($ticket['sku']);
		$woo_product = new WC_Product_Simple($woo_product_id);

		$generated_file = generate_file_ticket($woo_product->get_name(), $woo_product->get_description(), $decoded_barcode['payload']['formatted_price'], $decoded_barcode['payload']['sensitive_decoded'], $order_id, $index);
		if ($generated_file['status'] == 'failure')
			return $generated_file;

		$ticket_files_paths[] = $generated_file['ticket_file_abs_path'];
		$ticket_url_paths[] = $generated_file['ticket_file_url_path'];
	}

	return array('status' => 'success', 'payload' => array('ticket_file_abs_path' => $ticket_files_paths, 'ticket_file_url_path' => $ticket_url_paths));
}

function generate_file_ticket($name, $description, $price, $sensitive_decoded, $order_id, $index) {
	$file_generator = new HTML_Generator();

	$ticket_file_abs_path = WOO_TS_UPLOADPATH . '/' . $order_id . '/' . $index . '.' . $file_generator->extension; // file extension will be appended by the generator
	$ticket_file_url_path = WOO_TS_UPLOADURLPATH . '/' . $order_id . '/' . $index . '.' . $file_generator->extension;; // file extension will be appended by the generator

	$result = $file_generator->generate_file($name, $description, $price, $sensitive_decoded, $ticket_file_abs_path);
	if ($result['status'] == 'failure')
		return $result;

	$ticket_file_paths = array('ticket_file_url_path' => $ticket_file_url_path, 'ticket_file_abs_path' => $ticket_file_abs_path);
	return $ticket_file_paths;
}

function send_file_tickets_by_mail($target_user_mail, $ticket_file_abs_paths) {
	if (!empty($ticket_file_abs_paths)) {
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$mail_sent = wp_mail($target_user_mail, woo_ts_get_option('email_subject', ''), woo_ts_get_option('email_body', ''), $headers, $ticket_file_abs_paths);

		return $mail_sent;
	}

	return false;
}

?>