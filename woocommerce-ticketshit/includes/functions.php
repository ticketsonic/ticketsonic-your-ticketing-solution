<?php

require('admin_helper.php');
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
				woo_ts_update_option( 'ticket_html_template', ( isset( $_POST['ticket_html_template'] ) ? wp_kses($_POST['ticket_html_template'], allowed_html()) : '' ) );

				$message = __( 'Settings saved.', 'woo_ts' );
				woo_ts_admin_notice( $message );
				break;

			case 'import_new_tickets':
				$response = ts_post_get_my_passes();
				
				$ticket = array();
				$importedCount = 0;
				$ignoredCount = 0;
				$json_response = json_decode($response['body']);
				foreach ($json_response->tickets as $key => $ticket) {
					try {
						$objTicket = new WC_Product_Simple($ticket->sku);
						$objTicket->set_name($ticket->ticket_title_en);
						$objTicket->set_status("publish");
						$objTicket->set_catalog_visibility('visible');
						$objTicket->set_description($ticket->ticket_description_en);

						$ticketshit_term = get_term_by("slug", "ticketshit", "product_cat");
						if ($ticketshit_term) {
							$objTicket->set_category_ids(array($ticketshit_term->term_id));
						}

						$objTicket->set_sku($ticket->sku);
						$price = (int)$ticket->price / 100;
						$objTicket->set_price($price);
						$objTicket->set_regular_price($price);
						$objTicket->set_manage_stock(true);
						$objTicket->set_stock_quantity($ticket->stock);
						$objTicket->set_stock_status('instock');
						$objTicket->set_sold_individually(false);
						$objTicket->set_downloadable(true);
						$objTicket->set_virtual(true);
						
						$woo_ticket_id = $objTicket->save();
						$importedCount++;
					} catch (WC_Data_Exception $ex) {
						$ignoredCount++;
					}
				}

				woo_ts_update_option('api_public_key', "-----BEGIN PUBLIC KEY-----\n" . $json_response->api_public_key . "\n-----END PUBLIC KEY-----");

				woo_ts_admin_notice("Total imported: " . $importedCount, 'notice');
				woo_ts_admin_notice("Already imported: " . $ignoredCount, 'notice');
				//woo_ts_admin_notice("Public key: " . woo_ts_get_option('api_public_key', ''), 'notice');
				break;

			case 'update_existing_tickets':
			    $response = ts_post_get_my_passes();

				$ticket = array();
				$importedCount = 0;
				$ignoredCount = 0;
				
				foreach (json_decode($response['body'])->tickets as $key => $ticket) {
					try {
						$woo_product_id = wc_get_product_id_by_sku($ticket->sku);

						// Ticket does not exist so we skip
						if ($woo_product_id == 0) {
							$ignoredCount++;
							continue;
						}

						$objTicket = new WC_Product_Simple($woo_product_id);

						$objTicket->set_name($ticket->ticket_title_en);
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

				woo_ts_admin_notice("Total imported: " . $importedCount, 'notice');
				woo_ts_admin_notice("Already imported: " . $ignoredCount, 'notice');

				break;

			case 'sync_with_ts':
			    $response = ts_post_get_my_passes();

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

				woo_ts_admin_notice("Total imported: " . $importedCount, 'notice');
				woo_ts_admin_notice("Already imported: " . $ignoredCount, 'notice');
				woo_ts_update_option('api_public_key', "-----BEGIN PUBLIC KEY-----\n" . $json_response->api_public_key . "\n-----END PUBLIC KEY-----");

				break;
		}
	}

	add_action( 'plugins_loaded', 'woo_ts_init_memory' );
}

function woo_ts_error_log( $message = '' ) {
	if( $message == '' )
		return;

	if( class_exists( 'WC_Logger' ) ) {
		$logger = new WC_Logger();
		$logger->add( WOO_TS_PREFIX, $message );
		return true;
	} else {
		// Fallback where the WooCommerce logging engine is unavailable
		error_log( sprintf( '[product-importer] %s', $message ) );
	}
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

function ts_post_get_my_passes() {
	//$auth = base64_encode("guest:Gue\$t1");
	$mode = woo_ts_get_option( 'mode', 0 );
	$url = 'https://www.demo.ticketshit.net/v1/get_my_passes';
	$response = wp_remote_post($url, array(
		//'headers'     => array(
		//	"Authorization" => "Basic $auth",
		//),
		'method' => 'POST',
		'body' => array( 'email' => woo_ts_get_option('promoter_email', ''),)
		)
	);

	return $response;
}

function allowed_html() {
	return array (
		'html' => array (
			'href' => array(),
			'class' => array(),
		),
		'body' => array (
			'href' => array(),
			'class' => array(),
		),
		'style' => array (
			'href' => array(),
			'class' => array(),
		),
		'table' => array (
			'href' => array(),
			'class' => array(),
		),
		'tbody' => array (
			'href' => array(),
			'class' => array(),
		),
		'th' => array (
			'href' => array(),
			'class' => array(),
		),
		'tr' => array (
			'href' => array(),
			'class' => array(),
		),
		'div' => array (
			'href' => array(),
			'class' => array(),
		),
		'p' => array (
			'href' => array(),
			'class' => array(),
		),
		'h1' => array (
			'href' => array(),
			'class' => array(),
		),
	);
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

	wp_mkdir_p(WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/');
}
add_action( 'init', 'woo_ts_init' );

// Plugin language support
function woo_ts_i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-product-importer' );
	load_plugin_textdomain( 'woocommerce-product-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'init', 'woo_ts_i18n' );

add_action('woocommerce_payment_complete', 'mysite_woocommerce_payment_complete');
function mysite_woocommerce_payment_complete($order_id) {
	error_log("callback fired");
}

add_action('woocommerce_order_status_completed', 'request_barcodes_from_ts', 10, 1);
function request_barcodes_from_ts($order_id) {
	$data = array(
		'mode' => woo_ts_get_option('mode', ''),
		'promoter_email' => woo_ts_get_option('promoter_email', ''),
		'promoter_api_key' => woo_ts_get_option('api_key', ''),
		'order_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
		'customer_email' => woo_ts_get_option('promoter_email', ''),
		'tickets' => array()
	);

	$order = wc_get_order( $order_id );

	$items = $order->get_items();
	foreach($items as $item) {
		$ticket = new WC_Product_Simple($item['product_id']);
		$data['tickets'][] = array('sku' => $ticket->get_sku(), 'stock' => $item['quantity']);
	}

	$auth = base64_encode("guest:Gue\$t1");
	$mode = woo_ts_get_option('mode', '');
	$url = 'https://www.demo.ticketshit.net/v1/create.json';
	
	$result = wp_remote_post($url, array(
		'headers'     => array(
			'Content-Type' => 'application/json; charset=utf-8',
		//	"Authorization" => "Basic $auth",
		),
		'body'        => json_encode($data),
		'method'      => 'POST',
		'data_format' => 'body',
		'timeout' => 45,
	));

	try {
		if (is_wp_error($result)) {
			// TODO: This does not print on the page
			woo_ts_admin_notice_print("Error occured while fetching tickets from TS ", 'error' );
			//die(var_export($result));
			return;
		}
		$json_response = json_decode($result['body']);
		if ($json_response->status == 'error') {
			// TODO: This does not print on the page
			woo_ts_admin_notice_print("Error occured: " . $result['body']['error'], 'error' );
			return;
		}
		//die(var_export($result['body']));
		//$html_tickets_file_paths = generate_tickets_from_html_template($json_response);
		$tickets_file_paths = generate_pdf_ticket_files($json_response);
		//die(var_export($pdf_tickets_info_array));
		$order_info_array = generate_order_info_array($json_response);
		$order->add_meta_data("html_tickets", $order_info_array);
		$order->add_meta_data("pdf_tickets", $order_info_array);

		$order->save();

		send_tickets_by_mail($order->get_billing_email(), $order_id, $tickets_file_paths);
	} catch (Exception $ex) {
		die(var_export($ex));
	}
}

function send_tickets_by_mail($target_user_mail, $order_id, $tickets_absolute_path) {
	if (!empty($tickets_absolute_path)) {
		$headers = array();
		$mail_sent = wp_mail($target_user_mail, 'Your Grand Conderence tickets for order ' . $order_id . ' are ready!', 'Your Grand Conderence tickets are ready!', $headers, $tickets_absolute_path);
	}
}

/*function generate_tickets_from_html_template($json_response) {
	$public_key = openssl_pkey_get_public(woo_ts_get_option('api_public_key', ''));
	if (!$public_key) {
		// TODO: This does not print on the page
		woo_ts_admin_notice_print("Public key corrupted");
		return;
	}

	//die(var_export(woo_ts_get_option('api_public_key', '')));
	//die(var_export($public_key));
	$ticket_file_paths = array();
	
	foreach ($json_response->tickets as $ticket) {
		$sensitive_decoded = base64_decode($ticket->sensitive);
		$is_decrypted = openssl_public_decrypt($sensitive_decoded, $sensitive_decrypted, $public_key);
		
		$decrypted_ticket = parse_raw_recrypted_ticket($sensitive_decrypted);
		
		$html_ticket_template = woo_ts_get_option( 'ticket_html_template', '' );
		$html_ticket_template = str_replace('{{ barcode }}', qr_binary_to_html_table(base64_encode($sensitive_decoded)), $html_ticket_template);
		
		$html_ticket_template = str_replace('{{ price }}', $decrypted_ticket['price'], $html_ticket_template);
		$html_ticket_template = str_replace('{{ sensitive_decoded }}', $sensitive_decoded, $html_ticket_template);
		$html_ticket_template = str_replace('{{ sensitive_decoded_base64_encode }}', base64_encode($sensitive_decoded) , $html_ticket_template);
		$html_ticket_template = str_replace('{{ title }}', $ticket->title_en, $html_ticket_template);
		$html_ticket_template = str_replace('{{ description }}', $ticket->description_en, $html_ticket_template);

		//$pdf_ticket = generate_pdf_from_html_template($html_ticket_template, $ticket->line_item_id);
		$ticket_file_paths[] = save_ticket_in_fs($html_ticket_template, $json_response->order, $ticket->line_item_id);
	}
	
	return $ticket_file_paths;
}*/

function generate_pdf_ticket_files($json_response) {
	$public_key = openssl_pkey_get_public(woo_ts_get_option('api_public_key', ''));
	if (!$public_key) {
		// TODO: This does not print on the page
		woo_ts_admin_notice_print("Public key corrupted");
		return;
	}

	wp_mkdir_p(WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $json_response->order . '/');

	$ticket_file_paths = array();
	//$line_items_array = array();
	//$line_items_array[$json_response->order] = array();
	
	$order_ticket = new PDF();
	foreach ($json_response->tickets as $ticket) {
		write_log('start generation of pdf at: ' . date("Y-m-d H:i:s"));
		$sensitive_decoded = base64_decode($ticket->sensitive);
		$is_decrypted = openssl_public_decrypt($sensitive_decoded, $sensitive_decrypted, $public_key);
		$decrypted_ticket = parse_raw_recrypted_ticket($sensitive_decrypted);

		// Create separate pdf tickets
		$pdf_ticket = new PDF();
		$pdf_ticket->AddPage();
		$pdf_ticket->set_text($ticket->title_en, $ticket->description_en, $decrypted_ticket['price']);
		$pdf_ticket->set_qr(qr_binary_to_binary(base64_encode($sensitive_decoded)));
		$pdf_ticket->set_background();
		$pdf_ticket->Output('F', WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $json_response->order . '/' . $ticket->line_item_id . '.pdf');
		$ticket_file_paths[] = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $json_response->order . '/' . $ticket->line_item_id . '.pdf';

		// Add page to the order pdf
		$order_ticket->AddPage();
		$order_ticket->set_text($ticket->title_en, $ticket->description_en, $decrypted_ticket['price']);
		$order_ticket->set_qr(qr_binary_to_binary(base64_encode($sensitive_decoded)));
		$order_ticket->set_background();

		//$line_items_array[$json_response->order][] = $ticket->line_item_id;
		//$ticket_file_paths[] = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $ticket->line_item_id . '.pdf';

		write_log('end of generation of pdf at: ' . date("Y-m-d H:i:s"));
	}
	$order_ticket->Output('F', WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $json_response->order . '/' . $json_response->order . '.pdf');
	$ticket_file_paths[] = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $json_response->order . '/' . $json_response->order . '.pdf';
	
	return $ticket_file_paths;
}

function generate_order_info_array($json_response) {
	$public_key = openssl_pkey_get_public(woo_ts_get_option('api_public_key', ''));
	if (!$public_key) {
		// TODO: This does not print on the page
		woo_ts_admin_notice_print("Public key corrupted");
		return;
	}

	$line_items_array = array();
	$line_items_array[$json_response->order] = array();
	
	foreach ($json_response->tickets as $ticket) {
		$sensitive_decoded = base64_decode($ticket->sensitive);
		$is_decrypted = openssl_public_decrypt($sensitive_decoded, $sensitive_decrypted, $public_key);
		$decrypted_ticket = parse_raw_recrypted_ticket($sensitive_decrypted);
		$line_items_array[$json_response->order][] = $ticket->line_item_id;
	}
	
	return $line_items_array;
}

function parse_raw_recrypted_ticket($raw_decrypted_ticket) {
	$result = array();
	$checksum = 0;
	$checksumpos = 0;
	$i = 0;
	//die(var_export($raw_decrypted_ticket));
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

/*function save_ticket_in_fs($html_ticket_template, $order_id, $line_item_id) {
	global $wp_filesystem;
	if (empty($wp_filesystem)) {
		require_once (ABSPATH . '/wp-admin/includes/file.php');
		WP_Filesystem();
	}

	$context = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/' . $order_id . '/';
	wp_mkdir_p($context);
	$target_file = $context . $line_item_id . ".html";

	if (!$wp_filesystem->put_contents($target_file, $html_ticket_template, FS_CHMOD_FILE))
		return new WP_Error('writing_error', 'Error when writing file');
	
	return $target_file;
}*/

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

/*** WooCommerce Order Listing Page Override ***/
/*add_filter( 'manage_edit-shop_order_columns', 'add_tickets_link' );
function add_tickets_link( $columns ) {
	$new_columns = ( is_array( $columns ) ) ? $columns : array();
  	unset( $new_columns[ 'order_actions' ] );
	
  	//edit this for your column(s)
  	//all of your columns will be added before the actions column
  	$new_columns['get_tickets_column'] = 'Tickets';
	
  	//stop editing
  	$new_columns[ 'order_actions' ] = $columns[ 'order_actions' ];
  	return $new_columns;
}*/

add_action('manage_shop_order_posts_custom_column', 'add_tickets_link_value', 2);
function add_tickets_link_value($column) {
	global $the_order;

	if ($column == 'get_tickets_column') {
		echo (print_r($the_order->get_meta("tickets"), 1));
	}
}

/*** WooCommerce Order Details Page Override ***/

add_action( 'woocommerce_admin_order_data_after_order_details', 'edit_order_meta_general' );
function edit_order_meta_general($order) {
	print "<br class='clear' />";
	print "<h4>PDF Tickets</h4>";
	$pdf_ticket_files = $order->get_meta("html_tickets");
	if (!empty($pdf_ticket_files)) {
		$order_id = array_keys($pdf_ticket_files)[0];
		foreach($pdf_ticket_files[$order_id] as $line_item) {
			print('<div><a href="' . content_url() . '/plugins/woocommerce-ticketshit/tickets/' . $order_id . '/' . $line_item . '.pdf">Tickets</a></div>');
		}
		print "<br class='clear' />";
		print('<div><a href="' . content_url() . '/plugins/woocommerce-ticketshit/tickets/' . $order_id . '/' . $order_id . '.pdf">All Tickets</a></div>');
	} else {
		print('<div>No PDF tickets found for this order</div>');
	}
}

?>