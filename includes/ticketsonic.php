<?php

require 'cryptography.php';
require dirname( __FILE__ ) . '/../vendor/autoload.php';

function ts_yts_request_create_new_event(
	$url,
	$email,
	$key,
	$event_title,
	$event_secondary_text_pl,
	$event_datetime,
	$event_location,
	$tickets_data,
	$uploaded_badge_file_path,
	$badge_size,
	$badge_primary_test_text,
	$badge_primary_text_horizontal_location,
	$badge_primary_text_horizontal_offset,
	$badge_primary_text_vertical_location,
	$badge_primary_text_vertical_offset,
	$badge_primary_text_fontsize,
	$badge_primary_text_color,
	$badge_primary_text_break_distance,
	$badge_secondary_test_text,
	$badge_secondary_text_horizontal_location,
	$badge_secondary_text_horizontal_offset,
	$badge_secondary_text_vertical_location,
	$badge_secondary_text_vertical_offset,
	$badge_secondary_text_fontsize,
	$badge_secondary_text_color,
	$badge_secondary_text_break_distance
) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key'    => $key,
	);

	$body = array(
		'primary_text_pl'   => $event_title,
		'secondary_text_pl' => $event_secondary_text_pl,
		'start_datetime'    => strtotime( $event_datetime ),
		'location'          => $event_location,
		'tickets'           => $tickets_data,
		'request_hash'      => bin2hex( openssl_random_pseudo_bytes( 16 ) ),
		'badge'             => array(),
	);

	if ( isset( $uploaded_badge_file_path ) ) {
		$body['badge']['badge_background'] = base64_encode( file_get_contents( $uploaded_badge_file_path ) );
	}

	if ( isset( $badge_size ) ) {
		$body['badge']['badge_size'] = $badge_size;
	}

	if ( isset( $badge_primary_test_text ) ) {
		$body['badge']['badge_primary_test_text'] = $badge_primary_test_text;
	}

	if ( isset( $badge_primary_text_horizontal_location ) ) {
		$body['badge']['badge_primary_text_horizontal_location'] = $badge_primary_text_horizontal_location;
	}

	if ( isset( $badge_primary_text_horizontal_offset ) ) {
		$body['badge']['badge_primary_text_horizontal_offset'] = $badge_primary_text_horizontal_offset;
	}

	if ( isset( $badge_primary_text_vertical_location ) ) {
		$body['badge']['badge_primary_text_vertical_location'] = $badge_primary_text_vertical_location;
	}

	if ( isset( $badge_primary_text_vertical_offset ) ) {
		$body['badge']['badge_primary_text_vertical_offset'] = $badge_primary_text_vertical_offset;
	}

	if ( isset( $badge_primary_text_fontsize ) ) {
		$body['badge']['badge_primary_text_fontsize'] = $badge_primary_text_fontsize;
	}

	if ( isset( $badge_primary_text_color ) ) {
		$body['badge']['badge_primary_text_color'] = $badge_primary_text_color;
	}

	if ( isset( $badge_primary_text_break_distance ) ) {
		$body['badge']['badge_primary_text_break_distance'] = $badge_primary_text_break_distance;
	}

	if ( isset( $badge_secondary_test_text ) ) {
		$body['badge']['badge_secondary_test_text'] = $badge_secondary_test_text;
	}

	if ( isset( $badge_secondary_text_horizontal_location ) ) {
		$body['badge']['badge_secondary_text_horizontal_location'] = $badge_secondary_text_horizontal_location;
	}

	if ( isset( $badge_secondary_text_horizontal_offset ) ) {
		$body['badge']['badge_secondary_text_horizontal_offset'] = $badge_secondary_text_horizontal_offset;
	}

	if ( isset( $badge_secondary_text_vertical_location ) ) {
		$body['badge']['badge_secondary_text_vertical_location'] = $badge_secondary_text_vertical_location;
	}

	if ( isset( $badge_secondary_text_vertical_offset ) ) {
		$body['badge']['badge_secondary_text_vertical_offset'] = $badge_secondary_text_vertical_offset;
	}

	if ( isset( $badge_secondary_text_fontsize ) ) {
		$body['badge']['badge_secondary_text_fontsize'] = $badge_secondary_text_fontsize;
	}

	if ( isset( $badge_secondary_text_color ) ) {
		$body['badge']['badge_secondary_text_color'] = $badge_secondary_text_color;
	}

	if ( isset( $badge_secondary_text_break_distance ) ) {
		$body['badge']['badge_secondary_text_break_distance'] = $badge_secondary_text_break_distance;
	}

	$response = ts_yts_post_request_to_remote( $url, $headers, $body );

	if ( 'success' === $response['status'] && file_exists( $uploaded_badge_file_path ) ) {
		$pi	= pathinfo( $uploaded_badge_file_path );
		$badge_file_path = $pi['dirname'] . DIRECTORY_SEPARATOR . $response['event_id'] . '-badge-background' . '.' . $pi['extension'];
		rename( $uploaded_badge_file_path, $badge_file_path );
	}

	return $response;
}

function ts_yts_request_create_new_ticket( $url, $email, $key, $ticket_eventid, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock ) {
	$headers = array(
		'x-api-userid'  => $email,
		'x-api-key'     => $key,
		'x-api-eventid' => $ticket_eventid,
	);

	$body = array(
		'request_hash'      => bin2hex( openssl_random_pseudo_bytes( 16 ) ),
		'primary_text_pl'   => $ticket_title,
		'secondary_text_pl' => $ticket_description,
		'price'             => $ticket_price,
		'currency'          => $ticket_currency,
		'stock'             => $ticket_stock,
	);
	$response = ts_yts_post_request_to_remote( $url, $headers, $body );

	return $response;
}

function ts_yts_request_change_ticket( $url, $email, $key, $ticket_sku, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock ) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key'    => $key,
		'x-api-sku'    => $ticket_sku,
	);

	$body = array(
		'request_hash'      => bin2hex( openssl_random_pseudo_bytes( 16 ) ),
		'primary_text_pl'   => $ticket_title,
		'secondary_text_pl' => $ticket_description,
		'price'             => $ticket_price,
		'currency'          => $ticket_currency,
		'stock'             => $ticket_stock,
	);
	$response = ts_yts_post_request_to_remote( $url, $headers, $body );

	return $response;
}

function ts_yts_request_change_event(
	$url,
	$email,
	$key,
	$event_id,
	$event_title,
	$event_description,
	$event_location,
	$event_date,
	$uploaded_badge_file_path,
	$badge_size,
	$badge_primary_test_text,
	$badge_primary_text_horizontal_location,
	$badge_primary_text_horizontal_offset,
	$badge_primary_text_vertical_location,
	$badge_primary_text_vertical_offset,
	$badge_primary_text_fontsize,
	$badge_primary_text_color,
	$badge_primary_text_break_distance,
	$badge_secondary_test_text,
	$badge_secondary_text_horizontal_location,
	$badge_secondary_text_horizontal_offset,
	$badge_secondary_text_vertical_location,
	$badge_secondary_text_vertical_offset,
	$badge_secondary_text_fontsize,
	$badge_secondary_text_color,
	$badge_secondary_text_break_distance
	) {
	$headers = array(
		'x-api-userid'  => $email,
		'x-api-key'     => $key,
		'x-api-eventid' => $event_id,
	);

	$body = array(
		'request_hash'      => bin2hex( openssl_random_pseudo_bytes( 16 ) ),
		'primary_text_pl'   => $event_title,
		'secondary_text_pl' => $event_description,
		'location'          => $event_location,
		'start_datetime'    => strtotime( $event_date ),
		'badge'             => array(),
	);

	if ( isset( $badge_size ) ) {
		$body['badge']['badge_size'] = $badge_size;
	}

	if ( isset( $uploaded_badge_file_path ) ) {
		$body['badge']['badge_background'] = base64_encode( file_get_contents( $uploaded_badge_file_path ) );
	}

	if ( isset( $badge_primary_text_horizontal_location ) ) {
		$body['badge']['badge_primary_text_horizontal_location'] = $badge_primary_text_horizontal_location;
	}

	if ( isset( $badge_primary_text_horizontal_offset ) ) {
		$body['badge']['badge_primary_text_horizontal_offset'] = $badge_primary_text_horizontal_offset;
	}

	if ( isset( $badge_primary_text_vertical_location ) ) {
		$body['badge']['badge_primary_text_vertical_location'] = $badge_primary_text_vertical_location;
	}

	if ( isset( $badge_primary_text_vertical_offset ) ) {
		$body['badge']['badge_primary_text_vertical_offset'] = $badge_primary_text_vertical_offset;
	}

	if ( isset( $badge_primary_text_fontsize ) ) {
		$body['badge']['badge_primary_text_fontsize'] = $badge_primary_text_fontsize;
	}

	if ( isset( $badge_primary_text_color ) ) {
		$body['badge']['badge_primary_text_color'] = $badge_primary_text_color;
	}

	if ( isset( $badge_primary_test_text ) ) {
		$body['badge']['badge_primary_test_text'] = $badge_primary_test_text;
	}

	if ( isset( $badge_primary_text_break_distance ) ) {
		$body['badge']['badge_primary_text_break_distance'] = $badge_primary_text_break_distance;
	}

	if ( isset( $badge_secondary_text_horizontal_location ) ) {
		$body['badge']['badge_secondary_text_horizontal_location'] = $badge_secondary_text_horizontal_location;
	}

	if ( isset( $badge_secondary_text_horizontal_offset ) ) {
		$body['badge']['badge_secondary_text_horizontal_offset'] = $badge_secondary_text_horizontal_offset;
	}

	if ( isset( $badge_secondary_text_vertical_location ) ) {
		$body['badge']['badge_secondary_text_vertical_location'] = $badge_secondary_text_vertical_location;
	}

	if ( isset( $badge_secondary_text_vertical_offset ) ) {
		$body['badge']['badge_secondary_text_vertical_offset'] = $badge_secondary_text_vertical_offset;
	}

	if ( isset( $badge_secondary_text_fontsize ) ) {
		$body['badge']['badge_secondary_text_fontsize'] = $badge_secondary_text_fontsize;
	}

	if ( isset( $badge_secondary_text_color ) ) {
		$body['badge']['badge_secondary_text_color'] = $badge_secondary_text_color;
	}

	if ( isset( $badge_secondary_test_text ) ) {
		$body['badge']['badge_secondary_test_text'] = $badge_secondary_test_text;
	}

	if ( isset( $badge_secondary_text_break_distance ) ) {
		$body['badge']['badge_secondary_text_break_distance'] = $badge_secondary_text_break_distance;
	}

	$response = ts_yts_post_request_to_remote( $url, $headers, $body );

	if ( 'success' === $response['status'] && file_exists( $uploaded_badge_file_path ) ) {
		$pi	= pathinfo( $uploaded_badge_file_path );
		$badge_file_path = $pi['dirname'] . DIRECTORY_SEPARATOR . $event_id . '-badge-background' . '.' . $pi['extension'];
		rename( $uploaded_badge_file_path, $badge_file_path );
	}

	return $response;
}

function ts_yts_get_tickets_with_remote( $url, $email, $key, $event_id ) {
	$headers = array(
		'x-api-userid'  => $email,
		'x-api-key'     => $key,
		'x-api-eventid' => $event_id,
	);

	$response = ts_yts_get_request_from_remote( $url, $headers, null );

	return $response;
}

function ts_yts_get_events_data_from_remote( $url, $email, $key ) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key'    => $key,
	);

	$response = ts_yts_get_request_from_remote( $url, $headers, null );
	return $response;
}

function ts_yts_get_event_ticket_data_from_remote( $url, $email, $key, $event_id ) {
	$headers = array(
		'x-api-userid'  => $email,
		'x-api-key'     => $key,
		'x-api-eventid' => $event_id,
	);

	$response = ts_yts_get_request_from_remote( $url, $headers, null );
	return $response;
}

function ts_yts_request_create_tickets_order_in_remote( $order_id, $url, $email, $key ) {
	$headers = array(
		'x-api-userid' => $email,
		'x-api-key'    => $key,
	);
	$body = ts_yts_prepare_order_tickets_request_body( $order_id, $email, $key );

	$response = ts_yts_post_request_to_remote( $url, $headers, $body );

	return $response;
}

function ts_yts_prepare_order_tickets_request_body( $order_id, $email, $key ) {
	$order = wc_get_order( $order_id );

	$data = array(
		'start_time' => null,
		'end_time'   => null,
		'group'      => null,
	);

	if ( class_exists( 'Booked_WC_Appointment' ) ) {
		$appointment_id = $order->get_meta( '_booked_wc_order_appointments' );
		$appointment    = Booked_WC_Appointment::get( $appointment_id[0] );
		$from_to_arr    = explode( '-', $appointment->timeslot );
		$from_date      = date_create_from_format( 'Hi', $from_to_arr[0] );
		$to_date        = date_create_from_format( 'Hi', $from_to_arr[1] );
		$interval       = date_diff( $to_date, $from_date );
		$minutes_diff   = $interval->d * 24 * 60;
		$minutes_diff  += $interval->h * 60;
		$minutes_diff  += $interval->i;

		$data['start_time'] = strval( intval( $appointment->timestamp ) );
		$data['end_time']   = strval( intval( $appointment->timestamp ) + $minutes_diff * 60 );
	}

	$body = array(
		'request_hash'  => bin2hex( openssl_random_pseudo_bytes( 16 ) ),
		'order_details' => array(
			'primary_text_pl'   => ts_yts_get_customer_name( $order ),
			'secondary_text_pl' => ts_yts_get_customer_company( $order ),
		),
		'tickets'       => array(),
	);

	$items = $order->get_items();
	foreach ( $items as $item ) {
		$ticket            = new WC_Product_Simple($item['product_id'] );
		$body['tickets'][] = array(
			'sku'        => $ticket->get_sku(),
			'stock'      => $item['quantity'],
			'start_time' => $data['start_time'],
			'end_time'   => $data['end_time'],
		);
	}

	return $body;
}

function ts_yts_get_remote_health( $url, $email, $key ) {
	$headers = array(
		'x-api-userid'  => $email,
		'x-api-key'     => $key,
	);

	$response = ts_yts_get_request_from_remote( $url, $headers, null );

	return $response;
}

function ts_yts_get_request_from_remote( $url, $headers, $body ) {
	$http     = new GuzzleHttp\Client( array( 'base_uri' => $url, 'verify' => false ) );
	$response = array();
	try {
		$response = $http->request( 'GET', $url, array('headers' => $headers, 'body' => json_encode( $body ) ) );
		$response = json_decode( $response->getBody(), true );
	} catch ( GuzzleHttp\Exception\ConnectException $ex ) {
		$response['status']  = 'error';
		$response['status_code']  = null;
		$response['reason_phrase']  = 'Error connecting to the TS server';
		$response['message'] = 'Error connecting to the TS server';
		$response['full_message'] = 'Error connecting to the TS server';
	} catch ( Exception $ex ) {
		$response['status']  = 'error';
		$response['status_code']  = $ex->getResponse()->getStatusCode();
		$response['reason_phrase']  = $ex->getResponse()->getReasonPhrase();
		$response['message'] = $ex->getResponse()->getBody()->getContents();
		$response['full_message'] = $ex->getMessage();
	}

	return $response;
}

function ts_yts_post_request_to_remote( $url, $headers, $body ) {
	$http     = new GuzzleHttp\Client( array( 'base_uri' => $url, 'verify' => false ) );
	$response = array();
	try {
		$response = $http->request( 'POST', $url, array( 'headers' => $headers, 'body' => json_encode( $body ) ) );
		$response = json_decode( $response->getBody(), true );
	} catch ( GuzzleHttp\Exception\ConnectException $ex ) {
		$response['status']  = 'error';
		$response['status_code']  = null;
		$response['reason_phrase']  = 'Error connecting to the TS server';
		$response['message'] = 'Error connecting to the TS server';
		$response['full_message'] = 'Error connecting to the TS server';
	} catch ( Exception $ex ) {
		$response['status']  = 'error';
		$response['status_code']  = $ex->getResponse()->getStatusCode();
		$response['reason_phrase']  = $ex->getResponse()->getReasonPhrase();
		$response['message'] = $ex->getResponse()->getBody()->getContents();
		$response['full_message'] = $ex->getMessage();
	}

	return $response;
}

function ts_yts_decode_tickets( $tickets_data ) {
	$tickets_meta = array();

	foreach ( $tickets_data as $index => $ticket ) {
		$decoded_ticket = ts_yts_decode_barcode( $ticket['encrypted_data'] );
		if ( $decoded_ticket['status'] === 'failure' )
			return $decoded_ticket;

		$woo_product_id = wc_get_product_id_by_sku( $ticket['sku'] );
		$woo_product    = new WC_Product_Simple( $woo_product_id );

		$ticket_meta = array(
			'title'             => $woo_product->get_name(),
			'description'       => $woo_product->get_description(),
			'formatted_price'   => $decoded_ticket['payload']['formatted_price'],
			'sensitive_decoded' => ts_yts_bin_to_int_array( $decoded_ticket['payload']['sensitive_decoded'] ),
		);

		$tickets_meta[] = $ticket_meta;
	}

	return array(
		'status'  => 'success',
		'payload' => array(
			'tickets_meta' => $tickets_meta,
		),
	);
}

function ts_yts_decode_barcode( $encrypted_data ) {
	$public_key = openssl_pkey_get_public( ts_yts_get_option( 'user_public_key', '' ) );
	if ( ! $public_key ) {
		return array(
			'status'  => 'failure',
			'message' => 'Public key corrupted',
		);
	}

	$result                      = array();
	$result['sensitive_decoded'] = base64_decode( $encrypted_data );
	$result['is_decrypted']      = openssl_public_decrypt( $result['sensitive_decoded'], $sensitive_decrypted, $public_key );
	$result['decrypted_ticket']  = ts_yts_parse_raw_recrypted_ticket( $sensitive_decrypted );
	$result['formatted_price']   = floatval( $result['decrypted_ticket']['price'] ) / 100 . ' ' . ts_yts_currency_to_ascii( get_woocommerce_currency() );

	return array(
		'status'  => 'success',
		'payload' => $result,
	);
}

function ts_yts_send_html_tickets_by_mail( $target_user_mail, $tickets_data ) {
	$tickets_meta = $tickets_data['tickets_meta'];
	if ( ! empty( $tickets_meta ) ) {
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		foreach ( $tickets_meta as $key => $ticket_meta ) {
			$ticket_title       = $ticket_meta['title'];
			$ticket_description = $ticket_meta['description'];
			$ticket_price       = $ticket_meta['formatted_price'];

			$email_body    = ts_yts_get_option( 'email_body', '' );
			$email_subject = ts_yts_get_option( 'email_subject', '' );
			$qr            = null;

			$qr = ts_yts_sensitive_to_html_qr( $ticket_meta['sensitive_decoded'] );

			$email_body = str_replace( '[ticket_number]', $key + 1, $email_body );
			$email_body = str_replace( '[ticket_qr]', $qr, $email_body );
			$email_body = str_replace( '[ticket_title]', $ticket_title, $email_body );
			$email_body = str_replace( '[ticket_description]', $ticket_description, $email_body );
			$email_body = str_replace( '[ticket_price]', $ticket_price, $email_body );

			$email_subject = str_replace( '[ticket_number]', $key + 1, $email_subject );
			$email_subject = str_replace( '[ticket_title]', $ticket_title, $email_subject );
			$email_subject = str_replace( '[ticket_description]', $ticket_description, $email_subject );
			$email_subject = str_replace( '[ticket_price]', $ticket_price, $email_subject );

			$mail_sent = wp_mail( $target_user_mail, $email_subject, $email_body, $headers, null );
		}

		return $mail_sent;
	}

	return false;
}

function ts_yts_sensitive_to_html_qr( $sensitive_decoded ) {
	$qr        = '';
	$int_array = ts_yts_int_array_to_bin( $sensitive_decoded );
	$matrix    = ts_yts_get_qr_matrix( base64_encode( $int_array ) );
	$qr       .= '<table><tbody>';

	foreach ( $matrix as $row ) {
		$qr .= '<tr>';

		for ( $i = 0; $i < $row->count(); $i++ )
			if ( $row[ $i ] === 1 )
				$qr .= '<td class="black-square"></td>';
			else
				$qr .= '<td class="white-square"></td>';

		$qr .= '</tr>';
	}

	$qr .= '</tbody></table>';
	return $qr;
}

function ts_yts_get_customer_name( $order ) {
	return $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
}

function ts_yts_get_customer_company( $order ) {
	return $order->get_billing_company();
}

function ts_yts_get_qr_matrix( $raw_input ) {
	$qr_code = BaconQrCode\Encoder\Encoder::encode( $raw_input, BaconQrCode\Common\ErrorCorrectionLevel::L(), BaconQrCode\Encoder\Encoder::DEFAULT_BYTE_MODE_ECODING );
	$matrix  = $qr_code->getMatrix();
	$rows    = $matrix->getArray()->toArray();
	return $rows;
}

function ts_yts_write_log( $log ) {
	if ( true === WP_DEBUG ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( date( 'Y-m-d H:i:s' ) . ': ' . print_r( $log, true ) );
		} else {
			error_log( date( 'Y-m-d H:i:s' ) . ': ' . $log );
		}
	}
}

function ts_yts_currency_to_ascii( $currency_code ) {
	$currencies = array(
		'BGN' => 'BGN',
		'USD' => 'USD',
		'EUR' => 'EUR',
		'GBP' => 'GBP',
	);

	return $currencies[ $currency_code ];
}

function ts_yts_upload_custom_badge_background() {
	add_filter( 'upload_dir', 'wpse_141088_upload_dir' );
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/media.php';

	$allowed_file_types = array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
	);

	$upload_overrides = array(
		'test_form'                => false,
		'unique_filename_callback' => 'badge_background_file_renamer',
		'mimes'                    => $allowed_file_types,
	);

	$badge_file = $_FILES['badge_file'];

	$movefile = wp_handle_upload( $badge_file, $upload_overrides );
	if ( ! $movefile || isset( $movefile['error'] ) ) {
		ts_yts_admin_notice_html( $movefile['error'], 'error' );
	}

	remove_filter( 'upload_dir', 'wpse_141088_upload_dir' );

	return $movefile;
}

function ts_yts_badge_background_file_renamer( $dir, $name, $ext ) {
	return 'badge_background' . $ext;
}

function ts_yts_sanitize_or_default( $data, $default = '' ) {
	if ( isset( $data ) ) {
		return sanitize_text_field( wp_unslash( $data ) );
	}

	return sanitize_text_field( wp_unslash( $default ) );
}

function ts_yts_allowed_html() {
	return array(
		'html' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'head' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'body' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'style' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'table' => array(
			'href'        => array(),
			'class'       => array(),
			'style'       => array(),
			'width'       => array(),
			'cellspacing' => array(),
			'cellpadding' => array(),
			'border'      => array(),
			'role'        => array(),
		),
		'tbody' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'th' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'tr' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'td' => array(
			'href'    => array(),
			'class'   => array(),
			'style'   => array(),
			'align'   => array(),
			'width'   => array(),
			'height'  => array(),
			'rowspan' => array(),
		),
		'div' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'p' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'a' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'h1' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'h2' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'h3' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'h4' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'svg' => array(
			'href'        => array(),
			'class'       => array(),
			'style'       => array(),
			'viewbox'     => true,
			'width'       => array(),
			'height'      => array(),
			'xmlns'       => array(),
			'xmlns:xlink' => array(),
			'xmlns:bx'    => array(),
		),
		'defs' => array(
			'href'  => array(),
			'class' => array(),
			'style' => array(),
		),
		'stop' => array(
			'offset' => array(),
			'class'  => array(),
			'style'  => array(),
			'id'     => array(),
		),
		'lineargradient' => array(
			'id'                => array(),
			'gradientunits'     => array(),
			'x1'                => array(),
			'x2'                => array(),
			'y1'                => array(),
			'y2'                => array(),
			'gradienttransform' => array(),
			'xlink:href'        => array(),
			'bx:pinned'         => array(),
		),
		'path' => array(
			'd'         => array(),
			'class'     => array(),
			'style'     => array(),
			'id'        => array(),
			'transform' => array(),
		),
		'rect' => array(
			'href'      => array(),
			'class'     => array(),
			'style'     => array(),
			'x'         => array(),
			'y'         => array(),
			'height'    => array(),
			'width'     => array(),
			'transform' => array(),
			'id'        => array(),
		),
		'g' => array(
			'href'      => array(),
			'class'     => array(),
			'style'     => array(),
			'transform' => array(),
			'id'        => array(),
		),
	);
}
