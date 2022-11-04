<?php

require 'ticketsonic.php';
require 'ticket_generator.php';

if ( is_admin() ) {
	include_once TS_YTS_PATH . 'includes/admin.php';

	function ts_yts_import_init() {
		global $wpdb;
		$wpdb->hide_errors();
		@ob_start();

		$action = ts_yts_get_action();
		switch ( $action ) {
			case 'ticket-change':
				$url = ts_yts_get_option( 'change_ticket_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/edit' );
				if ( empty( $url ) ) {
					ts_yts_admin_notice_html( 'Change Ticket Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = ts_yts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					ts_yts_admin_notice_html( 'API E-mail have to set in Settings', 'error' );
					return;
				}

				$key = ts_yts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					ts_yts_admin_notice_html( 'API Key have to set in Settings', 'error' );
					return;
				}

				$ticket_sku = ts_yts_sanitize_or_default( $_POST['ticket_sku'] );
				if ( empty( $ticket_sku ) ) {
					ts_yts_admin_notice_html( 'Sku field have to be set', 'error' );
					return;
				}

				$ticket_title = ts_yts_sanitize_or_default( $_POST['ticket_primary_text_pl'] );
				if ( empty( $ticket_title ) ) {
					ts_yts_admin_notice_html( 'Ticket title have to set', 'error' );
					return;
				}

				$ticket_description = ts_yts_sanitize_or_default( $_POST['ticket_secondary_text_pl'] );
				$ticket_price = ts_yts_sanitize_or_default( $_POST['ticket_price'] );

				if ( ! is_numeric( $ticket_price ) ) {
					ts_yts_admin_notice_html( 'Ticket price must be a valid number', 'error' );
					return;
				}

				$ticket_currency = ts_yts_sanitize_or_default( $_POST['ticket_currency'] );

				$ticket_stock = ts_yts_sanitize_or_default( $_POST['ticket_stock'] );
				if ( ! is_numeric( $ticket_stock ) ) {
					ts_yts_admin_notice_html( 'Ticket stock must be a valid number', 'error' );
					return;
				}

				$result = ts_yts_request_change_ticket( $url, $email, $key, $ticket_sku, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock );

				if ( 'success' === $result['status'] ) {
					ts_yts_admin_notice_html( 'Status: success. Ticket with SKU: ' . $ticket_sku . ' successfully sent for processing. You will receive an email when it is processed.', 'updated' );
				} else {
					$json = json_decode($result['message']);
					ts_yts_admin_notice_html( 'Failed to request new event: ' . $json->message, 'error' );
				}

				break;

			case 'event-change':
				$url = ts_yts_get_option( 'change_event_endpoint', 'https://www.ticketsonic.com:9507/v1/event/edit' );
				if ( empty( $url ) ) {
					ts_yts_admin_notice_html( 'Change Event Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = ts_yts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					ts_yts_admin_notice_html( 'API E-mail have to set in Settings', 'error' );
					return;
				}

				$key = ts_yts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					ts_yts_admin_notice_html( 'API Key have to set in Settings', 'error' );
					return;
				}

				$event_id = ts_yts_sanitize_or_default( $_POST['event_id'] );
				if ( empty( $event_id ) ) {
					ts_yts_admin_notice_html( 'Event ID have to be set', 'error' );
					return;
				}

				$event_primary_text_pl = ts_yts_sanitize_or_default( $_POST['event_primary_text_pl'] );
				if ( empty( $event_primary_text_pl ) ) {
					ts_yts_admin_notice_html( 'Event title field have to set', 'error' );
					return;
				}

				$event_secondary_text_pl = ts_yts_sanitize_or_default( $_POST['event_secondary_text_pl'] );
				$event_location          = ts_yts_sanitize_or_default( $_POST['event_location'] );
				$event_date              = ts_yts_sanitize_or_default( $_POST['event_date'] );

				$badge_size                             = ts_yts_sanitize_or_default( $_POST['badge_size'] );
				$badge_primary_text_horizontal_location = ts_yts_sanitize_or_default( $_POST['badge_primary_text_horizontal_location'] );
				$badge_primary_text_horizontal_offset   = ts_yts_sanitize_or_default( $_POST['badge_primary_text_horizontal_offset'] );
				$badge_primary_text_vertical_location   = ts_yts_sanitize_or_default( $_POST['badge_primary_text_vertical_location'] );
				$badge_primary_text_vertical_offset     = ts_yts_sanitize_or_default( $_POST['badge_primary_text_vertical_offset'] );
				$badge_primary_text_fontsize            = ts_yts_sanitize_or_default( $_POST['badge_primary_text_fontsize'] );
				$badge_primary_text_color               = ts_yts_sanitize_or_default( $_POST['badge_primary_text_color'] );
				$badge_primary_test_text                = ts_yts_sanitize_or_default( $_POST['badge_primary_test_text'] );
				$badge_primary_text_break_distance      = ts_yts_sanitize_or_default( $_POST['badge_primary_text_break_distance'] );

				$badge_secondary_text_horizontal_location = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_horizontal_location'] );
				$badge_secondary_text_horizontal_offset   = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_horizontal_offset'] );
				$badge_secondary_text_vertical_location   = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_vertical_location'] );
				$badge_secondary_text_vertical_offset     = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_vertical_offset'] );
				$badge_secondary_text_fontsize            = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_fontsize'] );
				$badge_secondary_text_color               = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_color'] );
				$badge_secondary_test_text                = ts_yts_sanitize_or_default( $_POST['badge_secondary_test_text'] );
				$badge_secondary_text_break_distance      = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_break_distance'] );

				$uploaded_badge_file_path = null;
				if ( isset( $_FILES['badge_file'] ) ) {
					$result = ts_yts_upload_custom_badge_background();
					$uploaded_badge_file_path = $result['file'];
				}

				$result = ts_yts_request_change_event(
					$url,
					$email,
					$key,
					$event_id,
					$event_primary_text_pl,
					$event_secondary_text_pl,
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
				);

				if ( 'success' === $result['status'] ) {
					ts_yts_admin_notice_html( 'Status: success. Event with ID: ' . $event_id . ' successfully sent for processing. You will receive an email when it is processed.', 'updated' );
				} else {
					$json = json_decode( $result['message'] );
					ts_yts_admin_notice_html( 'Failed to request new event: ' . $json->message, 'error' );
				}

				break;

			case 'save-settings':
				ts_yts_update_option( 'api_key', ts_yts_sanitize_or_default( $_POST['api_key'] ) );
				ts_yts_update_option( 'api_userid', ts_yts_sanitize_or_default( $_POST['api_userid'] ) );
				ts_yts_update_option( 'email_subject', ts_yts_sanitize_or_default( $_POST['email_subject'] ) );
				ts_yts_update_option( 'email_body', ( isset( $_POST['email_body'] ) ? wp_kses( $_POST['email_body'], ts_yts_allowed_html() ) : '' ) );
				ts_yts_update_option( 'ticket_info_endpoint', ts_yts_sanitize_or_default( $_POST['ticket_info_endpoint'] ) );
				ts_yts_update_option( 'event_info_endpoint', ts_yts_sanitize_or_default( $_POST['event_info_endpoint'] ) );
				ts_yts_update_option( 'new_event_endpoint', ts_yts_sanitize_or_default( $_POST['new_event_endpoint'] ) );
				ts_yts_update_option( 'change_event_endpoint', ts_yts_sanitize_or_default( $_POST['change_event_endpoint'] ) );
				ts_yts_update_option( 'new_ticket_endpoint', ts_yts_sanitize_or_default( $_POST['new_ticket_endpoint'] ) );
				ts_yts_update_option( 'change_ticket_endpoint', ts_yts_sanitize_or_default( $_POST['change_ticket_endpoint'] ) );
				ts_yts_update_option( 'external_order_endpoint', ts_yts_sanitize_or_default( $_POST['external_order_endpoint'] ) );
				ts_yts_update_option( 'health_check_endpoint', ts_yts_sanitize_or_default( $_POST['health_check_endpoint'] ) );
				ts_yts_update_option( 'event_id', ts_yts_sanitize_or_default( $_POST['event_id'] ) );

				$message = __( 'Settings saved.', 'woo-ts' );
				ts_yts_admin_notice_html( $message );
				break;

			case 'sync_with_ts':
				$url = ts_yts_get_option( 'ticket_info_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/list' );
				if ( empty( $url ) ) {
					ts_yts_admin_notice_html( 'Ticket Info Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = ts_yts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					ts_yts_admin_notice_html( 'API E-mail have to set in Settings', 'error' );
					return;
				}

				$key = ts_yts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					ts_yts_admin_notice_html( 'API Key have to set in Settings', 'error' );
					return;
				}

				$event_id = ts_yts_get_option( 'event_id', '' );

				$response = ts_yts_get_tickets_with_remote( $url, $email, $key, $event_id );
				if ( 'error' === $response['status'] ) {
					ts_yts_admin_notice_html( 'Error syncing tickets: ' . $response['message'], 'error' );
					return;
				}

				$imported_count = 0;
				foreach ( $response['tickets'] as $key => $ticket ) {
					$woo_product_id = wc_get_product_id_by_sku( $ticket['sku'] );

					$ticket_obj = new WC_Product_Simple();

					// Ticket does not exist so we skip.
					if ( 0 !== $woo_product_id ) {
						$ticket_obj = new WC_Product_Simple( $woo_product_id );
					}

					$ticket_obj->set_sku( $ticket['sku'] );
					$ticket_obj->set_name( $ticket['primary_text_pl'] );
					$ticket_obj->set_description( $ticket['secondary_text_pl'] );
					$ticket_obj->set_status( 'publish' );
					$ticket_obj->set_catalog_visibility( 'visible' );

					// $price = (int) $ticket['price'] / 100;
					$price = $ticket['price'];
					$ticket_obj->set_price( $price );
					$ticket_obj->set_regular_price( $price );
					$ticket_obj->set_manage_stock( true );
					$ticket_obj->set_stock_quantity( $ticket['stock'] );
					$ticket_obj->set_stock_status( 'instock' );
					$ticket_obj->set_sold_individually( false );
					$ticket_obj->set_downloadable( true );
					$ticket_obj->set_virtual( true );

					$ticketsonic_term = get_term_by( 'slug', 'ticketsonic', 'product_cat' );
					if ( $ticketsonic_term ) {
						$ticket_obj->set_category_ids( array( $ticketsonic_term->term_id ) );
					}

					$woo_ticket_id = $ticket_obj->save();

					$imported_count++;
				}

				$result = array(
					'status'          => 'success',
					'message'         => 'Number of imported tickets: ' . $imported_count,
					'user_public_key' => $response['user_public_key'],
				);

				if ( 'success' === $result['status'] ) {
					ts_yts_admin_notice_html( $result['message'], 'updated' );
					ts_yts_update_option( 'user_public_key', '-----BEGIN PUBLIC KEY-----' . PHP_EOL . $result['user_public_key'] . PHP_EOL . '-----END PUBLIC KEY-----' );
				}

				break;

			case 'create-event':
				$url = ts_yts_get_option( 'new_event_endpoint', 'https://www.ticketsonic.com:9507/v1/event/new' );
				if ( empty( $url ) ) {
					ts_yts_admin_notice_html( 'New Event Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = ts_yts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					ts_yts_admin_notice_html( 'API E-mail have to set in Settings', 'error' );
					return;
				}

				$key = ts_yts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					ts_yts_admin_notice_html( 'API Key have to set in Settings', 'error' );
					return;
				}

				$event_title = ts_yts_sanitize_or_default( $_POST['event_primary_text_pl'] );
				if ( empty( $event_title ) ) {
					ts_yts_admin_notice_html( 'Event title field have to set', 'error' );
					return;
				}

				$event_secondary_text_pl = ts_yts_sanitize_or_default( $_POST['event_secondary_text_pl'] );
				$event_date        = ts_yts_sanitize_or_default( $_POST['event_date'] );
				$event_location    = ts_yts_sanitize_or_default( $_POST['event_location'] );

				$ticket_data  = ts_yts_sanitize_or_default( json_encode( $_POST['ticket'] ) );
				$tickets_data = json_decode( $ticket_data, true );
				foreach ( $tickets_data as $value ) {
					if ( empty( $value['primary_text_pl'] ) ) {
						$value['primary_text_pl'] = ts_yts_sanitize_or_default( $value['primary_text_pl'] );
						ts_yts_admin_notice_html( 'Ticket title must be set', 'error' );

						return;
					}

					if ( ! is_numeric( $value['price'] ) ) {
						$value['price'] = ts_yts_sanitize_or_default( $value['price'] );
						ts_yts_admin_notice_html( 'Ticket price must be a valid number', 'error' );

						return;
					}

					if ( ! is_numeric( $value['stock'] ) ) {
						$value['stock'] = ts_yts_sanitize_or_default( $value['stock'] );
						ts_yts_admin_notice_html( 'Ticket stock must be an integer number', 'error' );

						return;
					}

					if ( empty( $value['currency'] ) ) {
						$value['currency'] = ts_yts_sanitize_or_default( $value['currency'] );
						ts_yts_admin_notice_html( 'Ticket currency must be set', 'error' );

						return;
					}
				}

				$valid_badge_size = array( 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10' );
				$badge_size = ts_yts_sanitize_or_default( $_POST['badge_size'] );
				if ( isset( $badge_size ) ) {
					if ( ! in_array( $badge_size, $valid_badge_size ) ) {
						ts_yts_admin_notice_html( 'Invalid badge size', 'error' );
						return;
					}
				}

				$badge_primary_test_text = ts_yts_sanitize_or_default( $_POST['badge_primary_test_text'] );

				$horizontal_locations = array( 'left', 'center', 'right' );
				$badge_primary_text_horizontal_location = ts_yts_sanitize_or_default( $_POST['badge_primary_text_horizontal_location'] );
				if ( isset( $badge_primary_text_horizontal_location ) ) {
					if ( ! in_array( $badge_primary_text_horizontal_location, $horizontal_locations ) ) {
						ts_yts_admin_notice_html( 'Primary text horizontal location value must be either left, center or right', 'error' );
						return;
					}
				}

				$badge_primary_text_horizontal_offset = ts_yts_sanitize_or_default( $_POST['badge_primary_text_horizontal_offset'] );
				if ( isset( $badge_primary_text_horizontal_offset ) ) {
					if ( ! is_numeric( $badge_primary_text_horizontal_offset ) ) {
						ts_yts_admin_notice_html( 'Primary text horizontal offset must be a number', 'error' );
						return;
					}
				}

				$vertical_locations = array( 'top', 'center', 'bottom' );
				$badge_primary_text_vertical_location = ts_yts_sanitize_or_default( $_POST['badge_primary_text_vertical_location'] );
				if ( isset( $badge_primary_text_vertical_location ) ) {
					if ( ! in_array( $badge_primary_text_vertical_location, $vertical_locations ) ) {
						ts_yts_admin_notice_html( 'Primary text vertical location value must be either top, center or bottom', 'error' );
						return;
					}
				}

				$badge_primary_text_vertical_offset = ts_yts_sanitize_or_default( $_POST['badge_primary_text_vertical_offset'] );
				if ( isset( $badge_primary_text_vertical_offset ) ) {
					if ( ! is_numeric( $badge_primary_text_vertical_offset ) ) {
						ts_yts_admin_notice_html( 'Primary text vertical offset must be a number', 'error' );
						return;
					}
				}

				$badge_primary_text_fontsize = ts_yts_sanitize_or_default( $_POST['badge_primary_text_fontsize'] );
				if ( isset( $badge_primary_text_fontsize ) ) {
					if ( ! is_numeric( $badge_primary_text_fontsize ) ) {
						ts_yts_admin_notice_html( 'Primary text font size must be a number', 'error' );
						return;
					}
				}

				$badge_primary_text_color = ts_yts_sanitize_or_default( $_POST['badge_primary_text_color'] );
				if ( isset( $badge_primary_text_color ) ) {
					$output = null;
					preg_match_all( "/#[0-9a-f]{6}/i", $badge_primary_text_color, $output );
					preg_match_all( "/#[0-9a-f]{3}/i", $badge_primary_text_color, $output );
					if ( count( $output[0] ) < 1 ) {
						ts_yts_admin_notice_html( 'Primary text color must be in html format', 'error' );
						return;
					}
				}

				$badge_primary_text_break_distance = ts_yts_sanitize_or_default( $_POST['badge_primary_text_break_distance'] );
				if ( isset( $badge_primary_text_break_distance ) ) {
					if ( ! is_numeric( $badge_primary_text_break_distance ) ) {
						ts_yts_admin_notice_html( 'Primary break text distance must be a number', 'error' );
						return;
					}
				}

				$badge_secondary_test_text = ts_yts_sanitize_or_default( $_POST['badge_secondary_test_text'] );

				$horizontal_locations = array( 'left', 'center', 'right' );
				$badge_secondary_text_horizontal_location = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_horizontal_location'] );
				if ( isset( $badge_secondary_text_horizontal_location ) ) {
					if ( ! in_array( $badge_secondary_text_horizontal_location, $horizontal_locations ) ) {
						ts_yts_admin_notice_html( 'Primary text horizontal location value must be either left, center or right', 'error' );
						return;
					}
				}

				$badge_secondary_text_horizontal_offset = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_horizontal_offset'] );
				if ( isset( $badge_secondary_text_horizontal_offset ) ) {
					if ( ! is_numeric( $badge_secondary_text_horizontal_offset ) ) {
						ts_yts_admin_notice_html( 'Primary text horizontal offset must be a number', 'error' );
						return;
					}
				}

				$vertical_locations = array( 'top', 'center', 'bottom' );
				$badge_secondary_text_vertical_location = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_vertical_location'] );
				if ( isset( $badge_secondary_text_vertical_location ) ) {
					if ( ! in_array( $badge_secondary_text_vertical_location, $vertical_locations ) ) {
						ts_yts_admin_notice_html( 'Primary text vertical location value must be either top, center or bottom', 'error' );
						return;
					}
				}

				$badge_secondary_text_vertical_offset = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_vertical_offset'] );
				if ( isset( $badge_secondary_text_vertical_offset ) ) {
					if ( ! is_numeric( $badge_secondary_text_vertical_offset ) ) {
						ts_yts_admin_notice_html( 'Primary text vertical offset must be a number', 'error' );
						return;
					}
				}

				$badge_secondary_text_fontsize = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_fontsize'] );
				if ( isset( $badge_secondary_text_fontsize ) ) {
					if ( ! is_numeric( $badge_secondary_text_fontsize ) ) {
						ts_yts_admin_notice_html( 'Primary text font size must be a number', 'error' );
						return;
					}
				}

				$badge_secondary_text_color = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_color'] );
				if ( isset( $badge_secondary_text_color ) ) {
					$output = null;
					preg_match_all( "/#[0-9a-f]{6}/i", $badge_secondary_text_color, $output );
					preg_match_all( "/#[0-9a-f]{3}/i", $badge_secondary_text_color, $output );
					if ( count( $output[0] ) < 1 ) {
						ts_yts_admin_notice_html( 'Primary text color must be in html format', 'error' );
						return;
					}
				}

				$badge_secondary_text_break_distance = ts_yts_sanitize_or_default( $_POST['badge_secondary_text_break_distance'] );
				if ( isset( $badge_secondary_text_break_distance ) ) {
					if ( ! is_numeric( $badge_secondary_text_break_distance ) ) {
						ts_yts_admin_notice_html( 'Secondary break text distance must be a number', 'error' );
						return;
					}
				}

				$uploaded_badge_file_path = null;
				if ( isset( $_FILES['badge_file'] ) ) {
					$result = ts_yts_upload_custom_badge_background();
					$uploaded_badge_file_path = $result['file'];
				}

				$result = ts_yts_request_create_new_event(
					$url,
					$email,
					$key,
					$event_title,
					$event_secondary_text_pl,
					$event_date,
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
				);

				if ( 'success' === $result['status'] ) {
					ts_yts_admin_notice_html( 'Status: success. Event ID: ' . $result['event_id'] . ' successfully sent for processing. You will receive an email when it is processed.', 'updated' );
				} else {
					$json = json_decode( $result['message'] );
					ts_yts_admin_notice_html( 'Failed to request new event: ' . $json->message, 'error' );
				}

				break;

			case 'create-ticket':
				$url = ts_yts_get_option( 'new_ticket_endpoint', 'https://www.ticketsonic.com:9507/v1/ticket/new' );
				if ( empty( $url ) ) {
					ts_yts_admin_notice_html( 'New Ticket Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = ts_yts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					ts_yts_admin_notice_html( 'API E-mail have to set in Settings', 'error' );
					return;
				}

				$key = ts_yts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					ts_yts_admin_notice_html( 'API Key have to set in Settings', 'error' );
					return;
				}

				$ticket_eventid = ts_yts_sanitize_or_default( $_POST['ticket_eventid'] );
				if ( empty( $ticket_eventid ) ) {
					ts_yts_admin_notice_html( 'Ticket event id field have to set', 'error' );
					return;
				}

				$ticket_title = ts_yts_sanitize_or_default( $_POST['primary_text_pl'] );
				if ( empty( $ticket_title ) ) {
					ts_yts_admin_notice_html( 'Ticket title field have to set', 'error' );
					return;
				}

				$ticket_description = ts_yts_sanitize_or_default( $_POST['secondary_text_pl'] );

				$ticket_price = ts_yts_sanitize_or_default( $_POST['ticket_price'] );
				if ( ! is_numeric( $ticket_price ) ) {
					ts_yts_admin_notice_html( 'Ticket price must be a valid number', 'error' );
					return;
				}

				$ticket_currency = ts_yts_sanitize_or_default( $_POST['ticket_currency'] );
				if ( empty( $ticket_currency ) ) {
					ts_yts_admin_notice_html( 'Ticket currency field have to set', 'error' );
					return;
				}

				$ticket_stock = ts_yts_sanitize_or_default( $_POST['ticket_stock'] );
				if ( ! is_numeric( $ticket_stock ) ) {
					ts_yts_admin_notice_html( 'Ticket stock must be a valid number', 'error' );
					return;
				}

				$result = ts_yts_request_create_new_ticket( $url, $email, $key, $ticket_eventid, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock );

				if ( 'success' === $result['status'] ) {
					ts_yts_admin_notice_html( 'Status: success. Ticket for event ID: ' . $ticket_eventid . ' successfully sent for processing. You will receive an email when it is processed.', 'updated' );
				} else {
					$json = json_decode( $result['message'] );
					ts_yts_admin_notice_html( 'Failed to request new event: ' . $json->message, 'error' );
				}

				break;
		}
	}

	/** Add plugin ticket term. */
	function ts_yts_structure_init() {
		wp_insert_term(
			'TicketSonic Tickets',
			'product_cat',
			array(
				'description' => 'TicketSonic Tickets imported tickets.',
				'slug'        => 'ticketsonic',
			)
		);

		// TODO: Add catch handler
		wp_mkdir_p( TS_YTS_UPLOADPATH );
	}
}

/**
 * Add a custom action to order actions select box on edit order page
 * Ability to manually request tickets from TicketSonic
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
add_action( 'woocommerce_order_actions', 'ts_yts_force_get_new_tickets_order_action' );
function ts_yts_force_get_new_tickets_order_action( $actions ) {
	$actions['wc_ts_yts_force_get_new_tickets_order_action'] = __( 'Get tickets from TS', 'woo-ts' );
	return $actions;
}

add_action( 'woocommerce_order_action_wc_ts_yts_force_get_new_tickets_order_action', 'ts_yts_force_get_new_tickets_order' );
function ts_yts_force_get_new_tickets_order( $order ) {
	$order_id = $order->id;

	$url   = ts_yts_get_option( 'external_order_endpoint', 'https://www.ticketsonic.com:9507/v1/order/new' );
	$email = ts_yts_get_option( 'api_userid', '' );
	$key   = ts_yts_get_option( 'api_key', '' );

	$response = ts_yts_request_create_tickets_order_in_remote( $order_id, $url, $email, $key );

	if ( ! empty( $response['request_hash'] ) )
		$order->add_order_note( 'Request ID: ' . $response['request_hash'] );

	if ( 'success' !== $response['status'] ) {
		$order->add_order_note( 'Error getting new tickets for order ' . $order_id . ': ' . $response['message'] );
		return;
	}

	$order->update_meta_data( 'ts_response', $response['tickets'] );
	$order->add_order_note( 'Tickets data obtained successfully.' );

	$order->save();
}

/**
 * Add a custom action to order actions select box on edit order page
 * Ability to resend html based tickets via e-mail to the order owner
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
add_action( 'woocommerce_order_actions', 'ts_yts_resend_html_tickets_order_action' );
function ts_yts_resend_html_tickets_order_action( $actions ) {
	$actions['wc_ts_yts_resend_html_tickets_order_action'] = __( 'Send html based tickets via e-mail', 'woo-ts' );
	return $actions;
}

add_action( 'woocommerce_order_action_wc_ts_yts_resend_html_tickets_order_action', 'ts_yts_resend_html_tickets_order' );
function ts_yts_resend_html_tickets_order( $order ) {
	if ( ! $order->meta_exists( 'ts_response' ) ) {
		$order->add_order_note( 'No ticket data found to generate and send html tickets.' );

		return;
	}

	$ts_response = $order->get_meta( 'ts_response' );

	$decoded_tickets_data = ts_yts_decode_tickets( $ts_response );
	if ( 'success' !== $decoded_tickets_data['status'] ) {
		$order->update_status( 'failed', $decoded_tickets_data['message'] );
		return;
	}

	$order->save();

	if ( ! empty( $decoded_tickets_data ) ) {
		$mail_sent = ts_yts_send_html_tickets_by_mail( $order->get_billing_email(), $decoded_tickets_data['payload'] );
		if ( ! $mail_sent ) {
			$order->update_status( 'failed', 'Unable to send email with tickets!' );

			return;
		}

		$order->add_order_note( 'Tickets sent by e-mail.' );
	}
}

/**
 * Add a custom action to order actions select box on edit order page
 * Ability to manually generate ticket files from existing ticket data
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
add_action( 'woocommerce_order_actions', 'ts_yts_generate_new_ticket_files_from_existing_ticket_data_order_action' );
function ts_yts_generate_new_ticket_files_from_existing_ticket_data_order_action( $actions ) {
	$actions['wc_ts_yts_generate_new_ticket_files_from_existing_ticket_data_order_action'] = __( 'Generate ticket files', 'generate-ticket-files' );
	return $actions;
}

add_action( 'woocommerce_order_action_wc_ts_yts_generate_new_ticket_files_from_existing_ticket_data_order_action', 'ts_yts_generate_new_ticket_files_from_existing_ticket_data' );
function ts_yts_generate_new_ticket_files_from_existing_ticket_data( $order ) {
	$order_id = $order->id;

	$order = wc_get_order( $order_id );
	if ( ! $order->meta_exists( 'ts_response' ) ) {
		$order->add_order_note( 'No ticket data found to generate and store ticket files.' );

		return;
	}

	$ts_response       = $order->get_meta( 'ts_response' );
	$generated_tickets = ts_yts_generate_file_tickets( $ts_response, $order_id );
	if ( 'success' !== $generated_tickets['status'] ) {
		$order->update_status( 'failed', $generated_tickets['message'] );
		return;
	}

	$order->update_meta_data( 'ts_paths', $generated_tickets['payload'] );
	$order->add_order_note( 'Tickets generated successfully.' );

	$order->save();
}

add_action( 'woocommerce_order_status_completed', 'ts_yts_send_html_tickets_to_customer_after_order_completed', 10, 1 );
function ts_yts_send_html_tickets_to_customer_after_order_completed( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order->meta_exists( 'tickets_data' ) ) {
		return;
	}

	$url   = ts_yts_get_option( 'external_order_endpoint', 'https://www.ticketsonic.com:9507/v1/order/new' );
	$email = ts_yts_get_option( 'api_userid', '' );
	$key   = ts_yts_get_option( 'api_key', '' );

	$response = ts_yts_request_create_tickets_order_in_remote( $order_id, $url, $email, $key );

	if ( ! empty( $response['request_hash'] ) )
		$order->add_order_note( 'Request ID: ' . $response['request_hash'] );

	if ( 'success' !== $response['status'] ) {
		$order->update_status( 'failed', 'Error fetching result for order ' . $order_id . ': ' . $response['message'] );
		return;
	}

	$decoded_tickets_data = ts_yts_decode_tickets( $response['tickets'] );
	if ( 'success' !== $decoded_tickets_data['status'] ) {
		$order->update_status( 'failed', $decoded_tickets_data['message'] );
		return;
	}

	$order->update_meta_data( 'ts_response', $response['tickets'] );

	$order->save();

	if ( ! empty( $decoded_tickets_data ) ) {
		$mail_sent = ts_yts_send_html_tickets_by_mail( $order->get_billing_email(), $decoded_tickets_data['payload'] );
		if ( ! $mail_sent ) {
			$order->update_status( 'failed', 'Unable to send email with tickets!' );

			return;
		}

		$order->add_order_note( 'Tickets sent by e-mail.' );
	}
}

add_action( 'woocommerce_admin_order_data_after_order_details', 'ts_yts_display_ticket_links_in_order_details' );
function ts_yts_display_ticket_links_in_order_details( $order ) {
	print '<br class="clear" />';
	print '<h4>Tickets</h4>';
	$ts_response = $order->get_meta( 'ts_response' );
	if ( ! empty( $ts_response ) ) {
		$decoded_tickets_data = ts_yts_decode_tickets( $ts_response );
		foreach ( $decoded_tickets_data['payload']['tickets_meta'] as $key => $ticket ) {
			print( '<div style="clear: both; margin-bottom: 15px;">' );
			print( '<div style="float: left; margin: 5px 5px 0 0;"><img src="' . esc_attr( TS_YTS_PLUGINPATH ) . '/templates/admin/example_qr.svg" /></div>' );
			print( '<div><span>' . esc_html( $ticket['title'] ) . '</span></div>' );
			print( '<div><span>' . esc_html( $ticket['formatted_price'] ) . '</span></div>' );
			print( '</div>' );
		}
		print '<br class="clear" />';
	} else {
		print( '<div>No ticket data found for this order</div>' );
	}

	$generated_tickets = $order->get_meta( 'ts_paths' );
	if ( ! empty( $generated_tickets ) && array_key_exists( 'ticket_file_url_path', $generated_tickets ) ) {
		print '<br class="clear" />';
		print '<h4>Ticket Files</h4>';

		$ticket_files_url_path = $generated_tickets['ticket_file_url_path'];

		if ( ! empty( $ticket_files_url_path ) ) {
			foreach ( $ticket_files_url_path as $key => $ticket_file_path ) {
				print( '<div><a href="' . esc_attr( $ticket_file_path ) . '">Tickets</a></div>' );
			}
			print '<br class="clear" />';
		} else {
			print( '<div>No ticket files are generated for this order</div>' );
		}
	}
}

function ts_yts_get_action( $prefer_get = false ) {
	if ( isset( $_GET['action'] ) && $prefer_get )
		return ts_yts_sanitize_or_default( $_GET['action'] );

	if ( isset( $_POST['action'] ) )
		return ts_yts_sanitize_or_default( $_POST['action'] );

	if ( isset( $_GET['action'] ) )
		return ts_yts_sanitize_or_default( $_GET['action'] );

	return false;
}

function ts_yts_get_option( $option = null, $default = false, $allow_empty = false ) {
	$output = '';
	if ( isset( $option ) ) {
		$separator  = '_';
		$output     = get_option( TS_YTS_PREFIX . $separator . $option, $default );
		if ( false === $allow_empty && 0 !== $output && ( false === $output || '' === $output ) )
			$output = $default;
	}
	return $output;
}

function ts_yts_update_option( $option = null, $value = null ) {
	$output = false;
	if ( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output    = update_option( TS_YTS_PREFIX . $separator . $option, $value );
	}
	return $output;
}

function wpse_141088_upload_dir( $dir ) {
	return array(
		'path'   => TS_YTS_UPLOADPATH,
		'url'    => TS_YTS_UPLOADPATH,
		'subdir' => '/' . TS_YTS_DIRNAME,
	) + $dir;
}

add_filter(
	'safe_style_css',
	function( $styles ) {
		$styles[] = 'display';
		$styles[] = 'stop-color';
		$styles[] = 'stop-opacity';
		$styles[] = 'opacity';
		$styles[] = 'fill-opacity';
		$styles[] = 'stroke';
		$styles[] = 'stroke-opacity';
		$styles[] = 'stroke-width';
		$styles[] = 'stroke-linejoin';
		$styles[] = 'stroke-miterlimit';
		$styles[] = 'stroke-dasharray';
		$styles[] = 'stroke-dashoffset';
		$styles[] = 'fill';
		$styles[] = 'stroke-linecap';
		$styles[] = 'border-top-left-radius';
		$styles[] = 'border-top-right-radius';
		$styles[] = 'border-bottom-left-radius';
		$styles[] = 'border-bottom-right-radius';
		return $styles;
	}
);
