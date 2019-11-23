<?php

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

	// Increase memory for AJAX importer process and Product Importer screens
	function woo_ts_init_memory() {

		$page = $_SERVER['SCRIPT_NAME'];
		if( isset( $_POST['action'] ) )
			$action = $_POST['action'];
		elseif( isset( $_GET['action'] ) )
			$action = $_GET['action'];
		else
			$action = '';

		$allowed_actions = array( 'product_importer', 'finish_import', 'upload_image' );

		if( $page == '/wp-admin/admin-ajax.php' && in_array( $action, $allowed_actions ) )
			@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

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
function woo_ts_add_ticket_term() {
	wp_insert_term(
		'Ticket\'s HIT Tickets', // the term 
		'product_cat', // the taxonomy
		array(
		  'description'=> 'Ticket’s HIT Tickets imported tickets.',
		  'slug' => 'ticketshit'
		)
	  );
}
add_action( 'init', 'woo_ts_add_ticket_term' );

// Plugin language support
function woo_ts_i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-product-importer' );
	load_plugin_textdomain( 'woocommerce-product-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'init', 'woo_ts_i18n' );

add_action('woocommerce_order_status_completed', 'mysite_completed', 10, 1);
function mysite_completed($order_id) {
	$data = array(
		'mode' => woo_ts_get_option('mode', ''),
		'promoter_email' => woo_ts_get_option('promoter_email', ''),
		'promoter_api_key' => woo_ts_get_option('api_key', ''),
		'order_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
		'customer_email' => woo_ts_get_option('promoter_email', ''),
		'tickets' => array()
	);

	$order = wc_get_order( $order_id );
	$user = $order->get_user();

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
		die(var_export($user));
		$result = generate_tickets_from_template($json_response);

		if ($result == 1) {
			$mail_sent = wp_mail('mrtn.vassilev@gmail.com', 'Your tickets', 'Your tickets are ready to be checkedin!', $headers, $ticket_file_paths);
			
			if ($mail_sent) {
				woo_ts_admin_notice("Tickets sent");
			}
		}
	} catch (Exception $ex) {
		die(var_export($ex));
	}
}

function generate_tickets_from_template($json_response) {
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
		$html_ticket_template = str_replace('{{ barcode }}', base64_encode($sensitive_decoded) 
															. " | " 
															. qr_binary_to_html_table(base64_encode($sensitive_decoded)), $html_ticket_template);
		
		$html_ticket_template = str_replace('{{ price }}', $decrypted_ticket['price'], $html_ticket_template);
		$html_ticket_template = str_replace('{{ sensitive_decoded }}', $sensitive_decoded, $html_ticket_template);
		$html_ticket_template = str_replace('{{ sensitive_decoded_base64_encode }}', base64_encode($sensitive_decoded) , $html_ticket_template);
		$html_ticket_template = str_replace('{{ title }}', $ticket->title_en, $html_ticket_template);
		$html_ticket_template = str_replace('{{ description }}', $ticket->description_en, $html_ticket_template);
		$ticket_file_paths[] = save_ticket_in_fs($html_ticket_template, $ticket->line_item_id);
	}

	return 1;
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

function save_ticket_in_fs($html_ticket_template, $line_item_id) {
	global $wp_filesystem;
	if (empty($wp_filesystem)) {
		require_once (ABSPATH . '/wp-admin/includes/file.php');
		WP_Filesystem();
	}

	$context = WP_PLUGIN_DIR . '/woocommerce-ticketshit/tickets/';
	$target_file = $context . $line_item_id . ".html";

	if (!$wp_filesystem->put_contents($target_file, $html_ticket_template, FS_CHMOD_FILE))
		return new WP_Error('writing_error', 'Error when writing file');
	
	return $target_file;
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

?>