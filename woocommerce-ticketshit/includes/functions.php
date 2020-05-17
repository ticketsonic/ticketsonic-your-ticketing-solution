<?php

require('helper.php');
require('pdf.php');

if (is_admin()) {
	include_once( WOO_TS_PATH . 'includes/admin.php' );
	function woo_ts_import_init() {
		global $wpdb;
		$wpdb->hide_errors();
		@ob_start();

		$mode = "demo";
		if( isset( $_POST['mode'] ) )
			$mode = sanitize_text_field( $_POST['mode'] );

		$action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );
		switch( $action ) {
			case 'save-settings':
				woo_ts_update_option( 'delete_file', ( isset( $_POST['delete_file'] ) ? absint( $_POST['delete_file'] ) : 0 ) );
				woo_ts_update_option( 'mode', ( isset( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : '' ) );
				woo_ts_update_option( 'api_key', ( isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '' ) );
				woo_ts_update_option( 'promoter_email', ( isset( $_POST['promoter_email'] ) ? sanitize_text_field( $_POST['promoter_email'] ) : '' ) );
				woo_ts_update_option( 'email_subject', ( isset( $_POST['email_subject'] ) ? sanitize_text_field( $_POST['email_subject'] ) : '' ) );
				woo_ts_update_option( 'email_body', ( isset( $_POST['email_body'] ) ? wp_kses($_POST['email_body'], allowed_html()) : '' ) );
				woo_ts_update_option( 'ticket_info_endpoint', ( isset( $_POST['ticket_info_endpoint'] ) ? sanitize_text_field( $_POST['ticket_info_endpoint'] ) : '' ) );
				woo_ts_update_option( 'external_order_endpoint', ( isset( $_POST['external_order_endpoint'] ) ? sanitize_text_field( $_POST['external_order_endpoint'] ) : '' ) );

				upload_custom_ticket_background();

				$message = __( 'Settings saved.', 'woo_ts' );
				woo_ts_admin_notice( $message );
				break;

			case 'sync_with_ts':
				$response = ts_post_get_my_passes();
				
				if (is_wp_error($response)) {
					woo_ts_admin_notice($response->get_error_message(), 'error');
					write_log('Error syncing with TS: '. $response->get_error_message());
					return;
				}

				$ticket = array();
				$importedCount = 0;
				$ignoredCount = 0;
				
				$json_response = json_decode($response['body']);
				foreach ($json_response->tickets as $key => $ticket) {
					try {
						$woo_product_id = wc_get_product_id_by_sku($ticket->sku);

						$objTicket = new WC_Product_Simple();

						// Ticket does not exist so we skip
						if ($woo_product_id != 0) {
							$objTicket = new WC_Product_Simple($woo_product_id);
						}

						$objTicket->set_sku($ticket->sku);
						$objTicket->set_name($ticket->ticket_title_en . ' ' . $ticket->ticket_description_en);
						$objTicket->set_status("publish");
						$objTicket->set_catalog_visibility('visible');
						$objTicket->set_description($ticket->ticket_description_en);
						
						$price = (int)$ticket->price / 100;
						$objTicket->set_price($price);
						$objTicket->set_regular_price($price);
						$objTicket->set_manage_stock(true);
						$objTicket->set_stock_quantity($ticket->stock);
						$objTicket->set_stock_status('instock');
						$objTicket->set_sold_individually(false);
						$objTicket->set_downloadable(true);
						$objTicket->set_virtual(true);

						$ticketshit_term = get_term_by("slug", "ticketshit", "product_cat");
						if ($ticketshit_term) {
							$objTicket->set_category_ids(array($ticketshit_term->term_id));
						}

						$woo_ticket_id = $objTicket->save();

						$importedCount++;
					} catch (WC_Data_Exception $ex) {
						$ignoredCount++;
					}
				}

				woo_ts_admin_notice("Synced tickets: " . $importedCount, 'notice');
				// woo_ts_admin_notice("Already imported: " . $ignoredCount, 'notice');
				woo_ts_update_option('api_public_key', "-----BEGIN PUBLIC KEY-----\n" . $json_response->api_public_key . "\n-----END PUBLIC KEY-----");

				break;
		}
	}
}

function ts_post_get_my_passes() {
	$mode = woo_ts_get_option( 'mode', 0 );
	$url = woo_ts_get_option('ticket_info_endpoint', '');
	$response = wp_remote_post($url, array(
		'sslverify' => false,
		'method' => 'POST',
		'timeout' => 45,
		'body' => array(
			'email' => woo_ts_get_option('promoter_email', ''),
			'api_key' => woo_ts_get_option('api_key', ''),
		)
	));

	return $response;
}

if( !function_exists( 'woo_get_action' ) ) {
	function woo_get_action( $prefer_get = false ) {
		if ( isset( $_GET['action'] ) && $prefer_get )
			return sanitize_text_field( $_GET['action'] );

		if ( isset( $_POST['action'] ) )
			return sanitize_text_field( $_POST['action'] );

		if ( isset( $_GET['action'] ) )
			return sanitize_text_field( $_GET['action'] );

		return false;
	}
}

// Add plugin ticket term
function woo_ts_init() {
	wp_insert_term('Ticket\'s HIT Tickets','product_cat',
		array(
		  'description'=> 'Ticket’s HIT Tickets imported tickets.',
		  'slug' => 'ticketshit'
		)
	);

	// TODO: Add catch handler
	wp_mkdir_p(WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/');
	wp_mkdir_p(WOO_TS_UPLOADPATH);
}
add_action( 'init', 'woo_ts_init' );

// Plugin language support
function woo_ts_i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-product-importer' );
	load_plugin_textdomain( 'woocommerce-product-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'woo_ts_i18n' );

// TODO: Is this needed?
add_action('woocommerce_payment_complete', 'mysite_woocommerce_payment_complete');
function mysite_woocommerce_payment_complete($order_id) {
	write_log('mysite_woocommerce_payment_complete for order ' . $order_id . ' is fired');
}

add_action('woocommerce_order_status_processing', 'set_order_ts_meta_data', 10, 1);
function set_order_ts_meta_data($order_id) {
	write_log('request_barcodes_from_ts for order ' . $order_id . ' is fired');
	$order = wc_get_order($order_id);
	$data = array(
		'mode' => woo_ts_get_option('mode', ''),
		'promoter_email' => woo_ts_get_option('promoter_email', ''),
		'promoter_api_key' => woo_ts_get_option('api_key', ''),
		'order_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
		'order_details' => array(
			'customer_billing_name' => get_customer_name($order)
		),
		'tickets' => array()
	);

	$items = $order->get_items();
	foreach($items as $item) {
		$ticket = new WC_Product_Simple($item['product_id']);
		$data['tickets'][] = array('sku' => $ticket->get_sku(), 'stock' => $item['quantity']);
	}

	$mode = woo_ts_get_option('mode', '');
	$url = woo_ts_get_option('external_order_endpoint', '');
	
	write_log('sending req to TS');
	$result = wp_remote_post($url, array(
		'headers'     => array(
			'Content-Type' => 'application/json; charset=utf-8',
		),
		'sslverify' => false,
		'body'        => json_encode($data),
		'method'      => 'POST',
		'data_format' => 'body',
		'timeout' => 45,
	));

	write_log('result from the request to TS for ' . $order_id . ' is received');

	try {
		if (is_wp_error($result)) {
			write_log('Error fetching result for order ' . $order_id . ': '. $result->get_error_message());
			$order->update_status('failed', 'Error fetching result for order ' . $order_id . ': '. $result->get_error_message());
			return;
		}
		$json_response = json_decode($result['body']);
		write_log('$json_response is: ' . var_export($json_response));
		if (empty($json_response)) {
			write_log('Error: empty response from endpoint ' . woo_ts_get_option('external_order_endpoint', ''));
			$order->update_status('failed', 'Error: empty response from endpoint ' . woo_ts_get_option('external_order_endpoint', ''));
			return;
		}

		if ($json_response->status == 'error') {
			write_log('Error response from endpoint: ' . $result['body']);
			$order->update_status('failed', 'Error response from endpoint: ' . $result['body']);
			return;
		}
		generate_pdf_ticket_files($json_response, $order_id);
		write_log('PDF tickets generation for order ' . $order_id . ' is completed');
		
		$order_info_array = generate_order_info_array($json_response, $order_id);
		$order->add_meta_data("pdf_tickets", $order_info_array);
		$order->save();
		write_log('Order meta for PDF tickets for order ' . $order_id . ' is saved');

		return $order;
	} catch (Exception $ex) {
		$order->update_status('failed', 'Catch: ' . $ex);
	}
}

add_action('woocommerce_order_status_completed', 'send_tickets_to_email_after_order_completed', 10, 1);
function send_tickets_to_email_after_order_completed($order_id) {
	$order = wc_get_order($order_id);

	// paypal does not fire the processing hook
	if ($order->get_payment_method() == 'paypal')
	  $order = set_order_ts_meta_data($order_id);

    write_log('woocommerce_order_status_completed');
	write_log('send_tickets_to_email_after_order_completed for order ' . $order_id . ' is fired');

	$pdf_ticket_files_paths = array();
	$pdf_ticket_files = $order->get_meta("pdf_tickets");

	if (!empty($pdf_ticket_files)) {
		foreach($pdf_ticket_files[$order_id] as $value) {
			// TODO: Check if there are files generated
			$pdf_ticket_files_paths[] = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/' . $value . '.pdf';
			write_log('adding: ' . WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/' . $value . '.pdf');
		}
		
		// TODO: Check if there are files generated
		$pdf_ticket_files_paths[] = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/tickets.pdf';
		$mail_sent = send_tickets_by_mail($order->get_billing_email(), $order_id, $pdf_ticket_files_paths);
		write_log('mail status: ' . $mail_sent);
		write_log('mail attachments: ' . print_r($pdf_ticket_files_paths));
		if (!$mail_sent)
			write_log('Could not send mail with tickets');
	}

	write_log('PDF tickets for order ' . $order_id . ' are sent via mail to ' . $order->get_billing_email());
}

function send_tickets_by_mail($target_user_mail, $order_id, $tickets_absolute_path) {
	write_log('send_tickets_to_email_after_payment_confirmed fired');
	if (!empty($tickets_absolute_path)) {
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$mail_sent = wp_mail($target_user_mail, woo_ts_get_option('email_subject', ''), woo_ts_get_option('email_body', ''), $headers, $tickets_absolute_path);

		return $mail_sent;
	}

	return false;
}

function generate_pdf_ticket_files($json_response, $order_id) {
	$public_key = openssl_pkey_get_public(woo_ts_get_option('api_public_key', ''));
	if (!$public_key) {
		write_log("Public key corrupted");
		return;
	}

	// TODO: Add a check if is writable
	wp_mkdir_p(WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/');

	$ticket_file_paths = array();
	
	$starttime = microtime(true);
	$order_ticket = new PDF();
	foreach ($json_response->tickets as $key => $ticket) {
		write_log('start generation of pdf');
		$sensitive_decoded = base64_decode($ticket->encrypted_data);
		$is_decrypted = openssl_public_decrypt($sensitive_decoded, $sensitive_decrypted, $public_key);
		$decrypted_ticket = parse_raw_recrypted_ticket($sensitive_decrypted);
		$formatted_price = floatval($decrypted_ticket['price']) / 100;// . ' €';

		$woo_product_id = wc_get_product_id_by_sku($ticket->sku);
		$woo_product = new WC_Product_Simple($woo_product_id);

		// Create separate pdf tickets
		$pdf_ticket = new PDF();
		$pdf_ticket->AddPage();
		$pdf_ticket->set_background();
		
		$pdf_ticket->set_text($woo_product->get_name(), $woo_product->get_description(), $formatted_price);
		$pdf_ticket->set_qr(qr_binary_to_binary(base64_encode($sensitive_decoded)));
		// TODO: Check if it is writable
		$pdf_ticket->Output('F', WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/' . $key . '.pdf');
		$ticket_file_paths[] = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/' . $key . '.pdf';

		// Add page to the order pdf
		$order_ticket->AddPage();
		$order_ticket->set_background();
		$order_ticket->set_text($woo_product->get_name(), $woo_product->get_description(), $formatted_price);
		$order_ticket->set_qr(qr_binary_to_binary(base64_encode($sensitive_decoded)));

		write_log('end of generation of pdf at: ' . date("Y-m-d H:i:s"));
	}

	$endtime = microtime(true);
	$temp = $endtime - $starttime;
	write_log("start: " . $starttime);
	write_log("end: " . $endtime);
	write_log("time diff: " . $temp);

	// TODO: Check if it is writable
	$order_ticket->Output('F', WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/tickets.pdf');
	$ticket_file_paths[] = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/tickets.pdf';
	
	return $ticket_file_paths;
}

function generate_order_info_array($json_response, $order_id) {
	$line_items_array = array();
	$line_items_array[$order_id] = array();
	
	foreach ($json_response->tickets as $key => $ticket) {
		$line_items_array[$order_id][] = $key;
	}
	
	return $line_items_array;
}

function parse_raw_recrypted_ticket($raw_decrypted_ticket) {
	$result = array();
	$checksum = 0;
	$checksumpos = 0;
	$i = 0;
	try {
		while ($i < strlen($raw_decrypted_ticket)) {
			$label = $raw_decrypted_ticket[$i++];
			$len = ord($raw_decrypted_ticket[$i++]);
			switch ($label) {
				case 'V':
					$result['version'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'H':
					$result['barcode'] = substr($raw_decrypted_ticket, $i, $len);
					break;

				case '$':
					$result['price'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'E':
					$result['event_id'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'S':
					$result['segment1'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'B':
					$result['segment2'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'R':
					$result['segment3'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'P':
					$result['segment4'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				// TODO: we do not really have event like this to implement this feature
				case 'T':
					$result['expiry'] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;

				case 'X':
					$temp = substr($raw_decrypted_ticket, $i, $len);
					$delimiter_pos = strpos($temp, '=');

					$result['extension.' . substr($temp, 0, $delimiter_pos)] = substr($temp, $delimiter_pos + 1);
					break;

				case 'C':
					$checksumpos = $i - 2;
					$checksum = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
					break;
				
				default:
					$result[$label] = substr($raw_decrypted_ticket, $i, $len);
					$result['warning'] = "Unrecognized label";
					break;
			}
			$i += $len;
		}
	} catch (Exception $e) {
		$result["error"] = 1;
	}

	if ($checksumpos > 0) {
		$c = 0;
		for ($i = 0; $i < $checksumpos; $i++) {
			$c = ($c + ord($raw_decrypted_ticket[$i])) & 0xFFFF;
		}
		
		if ($c != $checksum) {
			$result["error"] = 1;
		}
	}

	return $result;
}

function bin_to_int_data($str) {
	$result = 0;
	for ($i = strlen($str) - 1; $i >= 0; $i--) {
		$result = ($result << 8) | ord($str[$i]);
	}

	return $result;
}

function qr_binary_to_html_table($raw_input) {
	$binary_input = QRcode::text($raw_input);
	$output = "<table><tbody>";
	foreach($binary_input as $line) {
		//die(gettype($line));
		$output .= "<tr>";
		$patterns = array();
		$patterns[0] = '/0/';
		$patterns[1] = '/1/';
		$replacements = array();
		$replacements[1] = '<td></td>';
		$replacements[0] = '<th></th>';
		
		$output .= preg_replace($patterns, $replacements, $line);
		$output .= "</tr>";
	}
	$output .= "</tbody></table>";
	return $output;
}

function qr_binary_to_binary($raw_input) {
	return QRcode::text($raw_input);
}

add_action('manage_shop_order_posts_custom_column', 'add_tickets_link_value', 2);
function add_tickets_link_value($column) {
	global $the_order;

	if ($column == 'get_tickets_column') {
		echo (print_r($the_order->get_meta("tickets"), 1));
	}
}

add_action( 'woocommerce_admin_order_data_after_order_details', 'edit_order_meta_general' );
function edit_order_meta_general($order) {
	print "<br class='clear' />";
	print "<h4>PDF Tickets</h4>";
	$pdf_ticket_files = $order->get_meta("pdf_tickets");
	if (!empty($pdf_ticket_files)) {
		$order_id = array_keys($pdf_ticket_files)[0];
		foreach($pdf_ticket_files[$order_id] as $key => $line_item) {
			print('<div><a href="' . content_url() . '/plugins/woocommerce-ticketshit/tickets/' . $order_id . '/' . $key . '.pdf">Tickets</a></div>');
		}
		print "<br class='clear' />";
		print('<div><a href="' . content_url() . '/plugins/woocommerce-ticketshit/tickets/' . $order_id . '/tickets.pdf">All Tickets</a></div>');
	} else {
		print('<div>No PDF tickets found for this order</div>');
	}
}

?>