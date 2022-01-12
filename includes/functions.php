<?php

require 'ticketsonic.php';
require 'ticket_generator.php';

if ( is_admin() ) {
	include_once WOO_TS_PATH . 'includes/admin.php';

	function woo_ts_import_init() {
		global $wpdb;
		$wpdb->hide_errors();
		@ob_start();

		$action = woo_ts_get_action();
		switch ( $action ) {
			case 'ticket-change':
				$url = woo_ts_get_option( 'change_ticket_endpoint', '' );
				if ( empty( $url ) ) {
					woo_ts_admin_notice( 'Change Ticket Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = woo_ts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					woo_ts_admin_notice( 'Partner E-mail have to set in Settings', 'error' );
					return;
				}

				$key = woo_ts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					woo_ts_admin_notice( 'Partner API Key have to set in Settings', 'error' );
					return;
				}

				$ticket_sku = sanitize_or_default( $_POST['ticket_sku'] );
				if ( empty( $ticket_sku ) ) {
					woo_ts_admin_notice( 'Sku field have to be set', 'error' );
					return;
				}

				$ticket_title = sanitize_or_default( $_POST['ticket_primary_text_pl'] );
				if ( empty( $ticket_title ) ) {
					woo_ts_admin_notice( 'Ticket title field have to set', 'error' );
					return;
				}

				$ticket_description = sanitize_or_default( $_POST['ticket_secondary_text_pl'] );

				$ticket_price = sanitize_or_default( $_POST['ticket_price'] );

				if ( ! is_int( intval( sanitize_or_default( $ticket_price ) ) ) ) {
					woo_ts_admin_notice( 'Ticket price must be an integer number', 'error' );

					return;
				}

				$ticket_currency = sanitize_or_default( $_POST['ticket_currency'] );

				$ticket_stock = sanitize_or_default( $_POST['ticket_stock'] );
				if ( ! is_int( intval( sanitize_or_default( $ticket_stock ) ) ) ) {
					woo_ts_admin_notice( 'Ticket stock must be an integer number', 'error' );

					return;
				}

				$result = request_change_ticket( $url, $email, $key, $ticket_sku, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock );

				if ( 'success' === $result['status'] ) {
					woo_ts_admin_notice( 'Status: success<br>Ticket with SKU: ' . $ticket_sku . ' successfully sent for processing. You will receive an email when it is processed.', 'notice' );
				} else {
					woo_ts_admin_notice( 'Failed to request new event: ' . $result['message'], 'error' );
				}

				break;

			case 'event-change':
				$url = woo_ts_get_option( 'change_event_endpoint', '' );
				if ( empty( $url ) ) {
					woo_ts_admin_notice( 'Change Event Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = woo_ts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					woo_ts_admin_notice( 'Partner E-mail have to set in Settings', 'error' );
					return;
				}

				$key = woo_ts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					woo_ts_admin_notice( 'Partner API Key have to set in Settings', 'error' );
					return;
				}

				$event_id = sanitize_or_default( $_POST['event_id'] );
				if ( empty( $event_id ) ) {
					woo_ts_admin_notice( 'Event ID have to be set', 'error' );
					return;
				}

				$event_title = sanitize_or_default( $_POST['event_primary_text_pl'] );
				if ( empty( $event_title ) ) {
					woo_ts_admin_notice( 'Event title field have to set', 'error' );
					return;
				}

				$event_description    = sanitize_or_default( $_POST['event_secondary_text_pl'] );
				$event_location       = sanitize_or_default( $_POST['event_location'] );
				$event_start_datetime = sanitize_or_default( $_POST['event_start_datetime'] );

				$badge_text_horizontal_location = sanitize_or_default( $_POST['badge_text_horizontal_location'] );
				$badge_text_vertical_location   = sanitize_or_default( $_POST['badge_text_vertical_location'] );

				$badge_primary_text_fontsize   = sanitize_or_default( $_POST['badge_primary_text_fontsize'] );
				$badge_secondary_text_fontsize = sanitize_or_default( $_POST['badge_secondary_text_fontsize'] );

				$badge_primary_text_color   = sanitize_or_default( $_POST['badge_primary_text_color'] );
				$badge_secondary_text_color = sanitize_or_default( $_POST['badge_secondary_text_color'] );

				$event_badge_data = array(
					'badge_text_horizontal_location' => $badge_text_horizontal_location,
					'badge_text_vertical_location'   => $badge_text_vertical_location,
					'badge_primary_text_fontsize'    => $badge_primary_text_fontsize,
					'badge_secondary_text_fontsize'  => $badge_secondary_text_fontsize,
					'badge_primary_text_color'       => $badge_primary_text_color,
					'badge_secondary_text_color'     => $badge_secondary_text_color,
				);

				$result = request_change_event( $url, $email, $key, $event_id, $event_title, $event_description, $event_location, $event_start_datetime, $event_badge_data );

				if ( 'success' === $result['status'] ) {
					woo_ts_admin_notice( 'Status: success<br>Event with ID: ' . $event_id . ' successfully sent for processing. You will receive an email when it is processed.', 'notice' );
				} else {
					woo_ts_admin_notice( 'Failed to request new event: ' . $result['message'], 'error' );
				}

				break;

			case 'save-settings':
				woo_ts_update_option( 'api_key', sanitize_or_default( $_POST['api_key'] ) );
				woo_ts_update_option( 'api_userid', sanitize_or_default( $_POST['api_userid'] ) );
				woo_ts_update_option( 'email_subject', sanitize_or_default( $_POST['email_subject'] ) );
				woo_ts_update_option( 'email_body', ( isset( $_POST['email_body'] ) ? wp_kses( $_POST['email_body'], allowed_html() ) : '' ) );
				woo_ts_update_option( 'ticket_info_endpoint', sanitize_or_default( $_POST['ticket_info_endpoint'] ) );
				woo_ts_update_option( 'event_info_endpoint', sanitize_or_default( $_POST['event_info_endpoint'] ) );
				woo_ts_update_option( 'new_event_endpoint', sanitize_or_default( $_POST['new_event_endpoint'] ) );
				woo_ts_update_option( 'change_event_endpoint', sanitize_or_default( $_POST['change_event_endpoint'] ) );
				woo_ts_update_option( 'new_ticket_endpoint', sanitize_or_default( $_POST['new_ticket_endpoint'] ) );
				woo_ts_update_option( 'change_ticket_endpoint', sanitize_or_default( $_POST['change_ticket_endpoint'] ) );
				woo_ts_update_option( 'external_order_endpoint', sanitize_or_default( $_POST['external_order_endpoint'] ) );
				woo_ts_update_option( 'event_id', sanitize_or_default( $_POST['event_id'] ) );

				upload_custom_ticket_background();

				$message = __( 'Settings saved.', 'woo-ts' );
				woo_ts_admin_notice( $message );
				break;

			case 'sync_with_ts':
				$url = woo_ts_get_option( 'ticket_info_endpoint', '' );
				if ( empty( $url ) ) {
					woo_ts_admin_notice( 'Ticket Info Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = woo_ts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					woo_ts_admin_notice( 'Partner E-mail have to set in Settings', 'error' );
					return;
				}

				$key = woo_ts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					woo_ts_admin_notice( 'Partner API Key have to set in Settings', 'error' );
					return;
				}

				$event_id = woo_ts_get_option( 'event_id', '' );

				$response = get_tickets_with_remote( $url, $email, $key, $event_id );
				if ( 'error' === $response['status'] ) {
					woo_ts_admin_notice( 'Error syncing tickets: ' . $response['message'], 'error' );
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

					$price = (int) $ticket['price'] / 100;
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
					woo_ts_admin_notice( $result['message'], 'notice' );
					woo_ts_admin_notice( 'Public Key' . $result['user_public_key'], 'notice' );
					woo_ts_update_option( 'user_public_key', '-----BEGIN PUBLIC KEY-----\n' . $result['user_public_key'] . '\n-----END PUBLIC KEY-----' );
				}

				break;

			case 'create-event':
				$url = woo_ts_get_option( 'new_event_endpoint', '' );
				if ( empty( $url ) ) {
					woo_ts_admin_notice( 'New Event Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = woo_ts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					woo_ts_admin_notice( 'Partner E-mail have to set in Settings', 'error' );
					return;
				}

				$key = woo_ts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					woo_ts_admin_notice( 'Partner API Key have to set in Settings', 'error' );
					return;
				}

				$event_title = sanitize_or_default( $_POST['event_title'] );
				if ( empty( $event_title ) ) {
					woo_ts_admin_notice( 'Event title field have to set', 'error' );
					return;
				}

				$event_description = sanitize_or_default( $_POST['event_description'] );
				$event_datetime    = sanitize_or_default( $_POST['event_datetime'] );
				$event_location    = sanitize_or_default( $_POST['event_location'] );

				$tickets_data = $_POST['ticket'];
				foreach ( $tickets_data as $value ) {
					if ( empty( $value['primary_text_pl'] ) ) {
						$value['primary_text_pl'] = sanitize_or_default( $value['primary_text_pl'] );
						woo_ts_admin_notice( 'Ticket title must be set', 'error' );

						return;
					}

					if ( empty( $value['price'] ) ) {
						$value['price'] = sanitize_or_default( $value['price'] );
						woo_ts_admin_notice( 'Ticket price must be set', 'error' );

						return;
					}

					if ( ! is_int( intval( $value['price'] ) ) ) {
						$value['price'] = sanitize_or_default( $value['price'] );
						woo_ts_admin_notice( 'Ticket price must be an integer number', 'error' );

						return;
					}

					if ( empty( $value['stock'] ) ) {
						$value['stock'] = sanitize_or_default( $value['stock'] );
						woo_ts_admin_notice( 'Ticket stock must be set', 'error' );

						return;
					}

					if ( empty( $value['currency'] ) ) {
						$value['currency'] = sanitize_or_default( $value['currency'] );
						woo_ts_admin_notice( 'Ticket currency must be set', 'error' );

						return;
					}
				}

				$badge_text_horizontal_location = sanitize_or_default( $_POST['badge_text_horizontal_location'] );
				if ( empty( $badge_text_horizontal_location ) ) {
					woo_ts_admin_notice( 'Badge text horizontal location must be set', 'error' );
					return;
				}

				$badge_text_vertical_location = sanitize_or_default( $_POST['badge_text_vertical_location'] );
				if ( empty( $badge_text_vertical_location ) ) {
					woo_ts_admin_notice( 'Badge text vertical location must be set', 'error' );
					return;
				}

				$badge_primary_text_fontsize = sanitize_or_default( $_POST['badge_primary_text_fontsize'] );
				if ( empty( $badge_primary_text_fontsize ) ) {
					woo_ts_admin_notice( 'Primary text font size must be set', 'error' );
					return;
				}

				if ( ! is_int( intval( $badge_primary_text_fontsize ) ) ) {
					woo_ts_admin_notice( 'Primary text font size must be an integer number', 'error' );
					return;
				}

				$badge_secondary_text_fontsize = sanitize_or_default( $_POST['badge_secondary_text_fontsize'] );
				if ( empty( $badge_secondary_text_fontsize ) ) {
					woo_ts_admin_notice( 'Primary text font size must be set', 'error' );
					return;
				}

				if ( ! is_int( intval( $badge_secondary_text_fontsize ) ) ) {
					woo_ts_admin_notice( 'Secondary text font size must be an integer number', 'error' );
					return;
				}

				$badge_primary_text_color = sanitize_or_default( $_POST['badge_primary_text_color'] );
				if ( empty( $badge_primary_text_color ) ) {
					woo_ts_admin_notice( 'Primary text color must be set', 'error' );
					return;
				}

				$badge_secondary_text_color = sanitize_or_default( $_POST['badge_secondary_text_color'] );
				if ( empty( $badge_secondary_text_color ) ) {
					woo_ts_admin_notice( 'Secondary text color must be set', 'error' );
					return;
				}

				upload_custom_badge_background();

				$result = request_create_new_event(
					$url,
					$email,
					$key,
					$event_title,
					$event_description,
					$event_datetime,
					$event_location,
					$tickets_data,
					$badge_text_horizontal_location,
					$badge_text_vertical_location,
					$badge_primary_text_fontsize,
					$badge_secondary_text_fontsize,
					$badge_primary_text_color,
					$badge_secondary_text_color
				);

				if ( 'success' === $result['status'] ) {
					woo_ts_admin_notice( 'Status: success<br>Event ID: ' . $result['event_id'] . ' successfully sent for processing. You will receive an email when it is processed.', 'notice' );
				} else {
					woo_ts_admin_notice( 'Failed to request new event: ' . $result['message'], 'error' );
				}

				break;

			case 'create-ticket':
				$url = woo_ts_get_option( 'new_ticket_endpoint', '' );
				if ( empty( $url ) ) {
					woo_ts_admin_notice( 'New Ticket Endpoint have to set in Settings', 'error' );
					return;
				}

				$email = woo_ts_get_option( 'api_userid', '' );
				if ( empty( $email ) ) {
					woo_ts_admin_notice( 'Partner E-mail have to set in Settings', 'error' );
					return;
				}

				$key = woo_ts_get_option( 'api_key', '' );
				if ( empty( $key ) ) {
					woo_ts_admin_notice( 'Partner API Key have to set in Settings', 'error' );
					return;
				}

				$ticket_eventid = sanitize_or_default( $_POST['ticket_eventid'] );
				if ( empty( $ticket_eventid ) ) {
					woo_ts_admin_notice( 'Ticket event id field have to set', 'error' );
					return;
				}

				$ticket_title = sanitize_or_default( $_POST['primary_text_pl'] );
				if ( empty( $ticket_title ) ) {
					woo_ts_admin_notice( 'Ticket title field have to set', 'error' );
					return;
				}

				$ticket_description = sanitize_or_default( $_POST['secondary_text_pl'] );

				$ticket_price = sanitize_or_default( $_POST['ticket_price'] );
				if ( empty( $ticket_price ) ) {
					woo_ts_admin_notice( 'Ticket price field have to set', 'error' );
					return;
				}

				if ( ! is_int( intval( sanitize_or_default( $ticket_price ) ) ) ) {
					woo_ts_admin_notice( 'Ticket price must be an integer number', 'error' );

					return;
				}

				$ticket_currency = sanitize_or_default( $_POST['ticket_currency'] );
				if ( empty( $ticket_currency ) ) {
					woo_ts_admin_notice( 'Ticket currency field have to set', 'error' );
					return;
				}

				$ticket_stock = sanitize_or_default( $_POST['ticket_stock'] );
				if ( empty( $ticket_stock ) ) {
					woo_ts_admin_notice( 'Ticket stock field have to set', 'error' );
					return;
				}

				$result = request_create_new_ticket( $url, $email, $key, $ticket_eventid, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock );

				if ( 'success' === $result['status'] ) {
					woo_ts_admin_notice( 'Status: success<br>Ticket for event ID: ' . $ticket_eventid . ' successfully sent for processing. You will receive an email when it is processed.', 'notice' );
				} else {
					woo_ts_admin_notice( 'Failed to request new event: ' . $result['message'], 'error' );
				}

				break;
		}
	}

	/** Add plugin ticket term. */
	function woo_ts_structure_init() {
		wp_insert_term(
			'TicketSonic Tickets',
			'product_cat',
			array(
				'description' => 'TicketSonic Tickets imported tickets.',
				'slug'        => 'ticketsonic',
			)
		);

		// TODO: Add catch handler
		wp_mkdir_p( WOO_TS_UPLOADPATH );
	}
}

/**
 * Add a custom action to order actions select box on edit order page
 * Ability to manually request tickets from TicketSonic
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
add_action( 'woocommerce_order_actions', 'force_get_new_tickets_order_action' );
function force_get_new_tickets_order_action( $actions ) {
	$actions['wc_force_get_new_tickets_order_action'] = __( 'Get tickets from TS', 'woo-ts' );
	return $actions;
}

add_action( 'woocommerce_order_action_wc_force_get_new_tickets_order_action', 'force_get_new_tickets_order' );
function force_get_new_tickets_order( $order ) {
	$order_id = $order->id;

	$url   = woo_ts_get_option( 'external_order_endpoint', '' );
	$email = woo_ts_get_option( 'api_userid', '' );
	$key   = woo_ts_get_option( 'api_key', '' );

	$response = request_create_tickets_order_in_remote( $order_id, $url, $email, $key );

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
add_action( 'woocommerce_order_actions', 'resend_html_tickets_order_action' );
function resend_html_tickets_order_action( $actions ) {
	$actions['wc_resend_html_tickets_order_action'] = __( 'Send html based tickets via e-mail', 'woo-ts' );
	return $actions;
}

add_action( 'woocommerce_order_action_wc_resend_html_tickets_order_action', 'resend_html_tickets_order' );
function resend_html_tickets_order( $order ) {
	if ( ! $order->meta_exists( 'ts_response' ) ) {
		$order->add_order_note( 'No ticket data found to generate and send html tickets.' );

		return;
	}

	$ts_response = $order->get_meta( 'ts_response' );

	$decoded_tickets_data = decode_tickets( $ts_response );
	if ( 'success' !== $decoded_tickets_data['status'] ) {
		$order->update_status( 'failed', $decoded_tickets_data['message'] );
		return;
	}

	$order->save();

	if ( ! empty( $decoded_tickets_data ) ) {
		$mail_sent = send_html_tickets_by_mail( $order->get_billing_email(), $decoded_tickets_data['payload'] );
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
add_action( 'woocommerce_order_actions', 'generate_new_ticket_files_from_existing_ticket_data_order_action' );
function generate_new_ticket_files_from_existing_ticket_data_order_action( $actions ) {
	$actions['wc_generate_new_ticket_files_from_existing_ticket_data_order_action'] = __( 'Generate ticket files', 'generate-ticket-files' );
	return $actions;
}

add_action( 'woocommerce_order_action_wc_generate_new_ticket_files_from_existing_ticket_data_order_action', 'generate_new_ticket_files_from_existing_ticket_data' );
function generate_new_ticket_files_from_existing_ticket_data( $order ) {
	$order_id = $order->id;

	$order = wc_get_order( $order_id );
	if ( ! $order->meta_exists( 'ts_response' ) ) {
		$order->add_order_note( 'No ticket files generated because there is no data.' );

		return;
	}

	$ts_response       = $order->get_meta( 'ts_response' );
	$generated_tickets = generate_file_tickets( $ts_response, $order_id );
	if ( 'success' !== $generated_tickets['status'] ) {
		$order->update_status( 'failed', $generated_tickets['message'] );
		return;
	}

	$order->update_meta_data( 'ts_paths', $generated_tickets['payload'] );
	$order->add_order_note( 'Tickets generated successfully.' );

	$order->save();
}

add_action( 'woocommerce_order_status_completed', 'send_html_tickets_to_customer_after_order_completed', 10, 1 );
function send_html_tickets_to_customer_after_order_completed( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order->meta_exists( 'tickets_data' ) ) {
		return;
	}

	$url   = woo_ts_get_option( 'external_order_endpoint', '' );
	$email = woo_ts_get_option( 'api_userid', '' );
	$key   = woo_ts_get_option( 'api_key', '' );

	$response = request_create_tickets_order_in_remote( $order_id, $url, $email, $key );

	if ( 'success' !== $response['status'] ) {
		$order->update_status( 'failed', 'Error fetching result for order ' . $order_id . ': ' . $response['message'] );
		return;
	}

	$decoded_tickets_data = decode_tickets( $response['tickets'] );
	if ( 'success' !== $decoded_tickets_data['status'] ) {
		$order->update_status( 'failed', $decoded_tickets_data['message'] );
		return;
	}

	$order->update_meta_data( 'ts_response', $response['tickets'] );

	$order->save();

	if ( ! empty( $decoded_tickets_data ) ) {
		$mail_sent = send_html_tickets_by_mail( $order->get_billing_email(), $decoded_tickets_data['payload'] );
		if ( ! $mail_sent ) {
			$order->update_status( 'failed', 'Unable to send email with tickets!' );

			return;
		}

		$order->add_order_note( 'Tickets sent by e-mail.' );
	}
}

add_action( 'woocommerce_admin_order_data_after_order_details', 'display_ticket_links_in_order_details' );
function display_ticket_links_in_order_details( $order ) {
	print '<br class="clear" />';
	print '<h4>Tickets</h4>';
	$ts_response = $order->get_meta( 'ts_response' );
	if ( ! empty( $ts_response ) ) {
		$decoded_tickets_data = decode_tickets( $ts_response );
		foreach ( $decoded_tickets_data['payload']['tickets_meta'] as $key => $ticket ) {
			print( '<div style="clear: both; margin-bottom: 15px;">' );
			print( '<div style="float: left; margin: 5px 5px 0 0;"><img src="' . esc_attr( WOO_TS_PLUGINPATH ) . '/templates/admin/example_qr.svg" /></div>' );
			print( '<div><span>' . esc_html( $ticket['title'] ) . '</span></div>' );
			print( '<div><span>' . esc_html( $ticket['formatted_price'] ) . '</span></div>' );
			print( '</div>' );
		}
		print '<br class="clear" />';
	} else {
		print( '<div>No ticket data found for this order</div>' );
	}

	print '<br class="clear" />';
	print '<h4>Ticket Files</h4>';

	$generated_tickets     = $order->get_meta( 'ts_paths' );
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

function woo_ts_get_action( $prefer_get = false ) {
	if ( isset( $_GET['action'] ) && $prefer_get )
		return sanitize_or_default( $_GET['action'] );

	if ( isset( $_POST['action'] ) )
		return sanitize_or_default( $_POST['action'] );

	if ( isset( $_GET['action'] ) )
		return sanitize_or_default( $_GET['action'] );

	return false;
}

function woo_ts_get_option( $option = null, $default = false, $allow_empty = false ) {
	$output = '';
	if ( isset( $option ) ) {
		$separator  = '_';
		$output     = get_option( WOO_TS_PREFIX . $separator . $option, $default );
		if ( false === $allow_empty && 0 !== $output && ( false === $output || '' === $output ) )
			$output = $default;
	}
	return $output;
}

function woo_ts_update_option( $option = null, $value = null ) {
	$output = false;
	if ( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output    = update_option( WOO_TS_PREFIX . $separator . $option, $value );
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

add_filter(
	'safe_style_css',
	function( $styles ) {
		$styles[] = 'display';
		$styles[] = 'stop-color';
		$styles[] = 'stop-opacity';
		$styles[] = 'opacity';
		$styles[] = 'fill-opacity';
		$styles[] = 'stroke';
		$styles[] = 'stroke-width';
		$styles[] = 'stroke-linejoin';
		$styles[] = 'stroke-miterlimit';
		$styles[] = 'stroke-dasharray';
		$styles[] = 'fill';
		$styles[] = 'stroke-linecap';
		return $styles;
	}
);
