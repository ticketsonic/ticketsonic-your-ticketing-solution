<?php

require("mpdf_generator.php");
require("cryptography.php");
require("ticketsonic.inc");
require( dirname( __FILE__ ) . "/../vendor/autoload.php");

function request_create_new_event($url, $email, $key, $event_title, $event_description, $event_datetime, $event_location, $tickets_data, $badge_text_horizontal_location, $badge_text_vartical_location, $badge_primary_text_fontsize, $badge_secondary_text_fontsize, $badge_primary_text_color, $badge_secondary_text_color) {
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key,
    );

    foreach ($tickets_data as $k => $value) {
        $tickets_data[$k]["price"] = intval($value["price"]) * 100;
    }

    $badge_background = WOO_TS_UPLOADURLPATH . "/badge_background.jpg";

    $body = array(
        "primary_text_pl" => $event_title,
        "secondary_text_pl" => $event_description,
        "datetime" => $event_datetime,
        "location" => $event_location,
        "tickets" => $tickets_data,
        "request_hash" => bin2hex(openssl_random_pseudo_bytes(16)),
        "badge_background" => base64_encode(file_get_contents($badge_background)),
        "badge_text_horizontal_location" => $badge_text_horizontal_location,
        "badge_text_vertical_location" => $badge_text_vartical_location,
        "badge_primary_text_fontsize" => $badge_primary_text_fontsize,
        "badge_secondary_text_fontsize" => $badge_secondary_text_fontsize,
        "badge_primary_text_color" => $badge_primary_text_color,
        "badge_secondary_text_color" => $badge_secondary_text_color
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
        "primary_text_pl" => $ticket_title,
        "secondary_text_pl" => $ticket_description,
        "price" => $ticket_price,
        "currency" => $ticket_currency,
        "stock" => $ticket_stock
    );
    $response = post_request_to_remote($url, $headers, $body);

    if ($response["status"] == "error") {
        woo_ts_admin_notice("Error sending new ticket request: " . $response["message"] , "error");
        return;
    }

    return $response;
}

function request_change_ticket($url, $email, $key, $ticket_sku, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock) {
    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key,
        "x-api-sku" => $ticket_sku
    );

    $ticket_price = intval($ticket_price) * 100;
    $body = array(
        "primary_text_pl" => $ticket_title,
        "secondary_text_pl" => $ticket_description,
        "price" => $ticket_price,
        "currency" => $ticket_currency,
        "stock" => $ticket_stock
    );
    $response = post_request_to_remote($url, $headers, $body);

    if ($response["status"] == "error") {
        woo_ts_admin_notice("Error sending new ticket request: " . $response["message"] , "error");
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
    foreach ($response["tickets"] as $key => $ticket) {
        $woo_product_id = wc_get_product_id_by_sku($ticket["sku"]);

        $ticket_obj = new WC_Product_Simple();

        // Ticket does not exist so we skip
        if ($woo_product_id != 0) {
            $ticket_obj = new WC_Product_Simple($woo_product_id);
        }

        $ticket_obj->set_sku($ticket["sku"]);
        $ticket_obj->set_name($ticket["primary_text_pl"]);
        $ticket_obj->set_description($ticket["secondary_text_pl"]);
        $ticket_obj->set_status("publish");
        $ticket_obj->set_catalog_visibility("visible");
        
        $price = (int)$ticket["price"] / 100;
        $ticket_obj->set_price($price);
        $ticket_obj->set_regular_price($price);
        $ticket_obj->set_manage_stock(true);
        $ticket_obj->set_stock_quantity($ticket["stock"]);
        $ticket_obj->set_stock_status("instock");
        $ticket_obj->set_sold_individually(false);
        $ticket_obj->set_downloadable(true);
        $ticket_obj->set_virtual(true);

        $ticketsonic_term = get_term_by("slug", "ticketsonic", "product_cat");
        if ($ticketsonic_term) {
            $ticket_obj->set_category_ids(array($ticketsonic_term->term_id));
        }

        $woo_ticket_id = $ticket_obj->save();

        $imported_count++;
    }

    $result = array("status" => "success", "message" => "Number of imported tickets: " . $imported_count, "user_public_key" => $response["user_public_key"]);
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

function request_create_tickets_order_in_remote($order_id, $url, $email, $key) {
    $order = wc_get_order($order_id);

    $data = array(
        "start_time" => null,
        "end_time" => null,
        "group" => null
    );

    if (class_exists("Booked_WC_Appointment")) {
        $appointment_id = $order->get_meta("_booked_wc_order_appointments");
        $appointment = Booked_WC_Appointment::get($appointment_id[0]);
        $from_to_arr = explode("-", $appointment->timeslot);
        $from_date = date_create_from_format("Hi", $from_to_arr[0]);
        $to_date = date_create_from_format("Hi", $from_to_arr[1]);
        $interval = date_diff($to_date, $from_date);
        $minutes_diff = $interval->d * 24 * 60;
        $minutes_diff += $interval->h * 60;
        $minutes_diff += $interval->i;

        $data["start_time"] = strval(intval($appointment->timestamp));
        $data["end_time"] = strval(intval($appointment->timestamp) + $minutes_diff * 60);
    }

    $headers = array(
        "x-api-userid" => $email,
        "x-api-key" => $key
    );
    $body = prepare_order_tickets_request_body($order_id, $email, $key, $data);

    $response = post_request_to_remote($url, $headers, $body);

    $order = wc_get_order($order_id);
    if ($response["status"] != "success") {
        $order->update_status("failed", "Error fetching result for order " . $order_id . ": ". $response["message"]);
        return;
    }

    $ticket_file_paths = generate_ticket_files($response["tickets"], $order_id);
    
    $order->add_meta_data("ticket_file_paths", $ticket_file_paths);
    $order->save();

    return $order;
}

function prepare_order_tickets_request_body($order_id, $email, $key, $data) {
    $order = wc_get_order($order_id);
    $body = array(
        "order_hash" => bin2hex(openssl_random_pseudo_bytes(16)),
        "order_details" => array(
            "customer_billing_name" => get_customer_name($order),
            "customer_billing_company" => get_customer_company($order)
        ),
        "tickets" => array()
    );

    $items = $order->get_items();
    foreach($items as $item) {
        $ticket = new WC_Product_Simple($item["product_id"]);
        $body["tickets"][] = array(
            "sku" => $ticket->get_sku(),
            "stock" => $item["quantity"],
            "start_time" => $data["start_time"],
            "end_time" => $data["end_time"]
        );
    }

    return $body;
}

function get_request_from_remote($url, $headers, $body) {
    $http = new GuzzleHttp\Client(["base_uri" => $url, "verify" => false]);
    $response = array();
    try {
        $response = $http->request("GET", $url, [
            "headers" => $headers,
            "body" => json_encode($body)
        ]);
    
        $response = json_decode($response->getBody(), true);
    } catch (Exception $ex) {
        $response["status"] = "error";
        $response["message"] = $ex->getMessage();
    }

    return $response;
}

function post_request_to_remote($url, $headers, $body) {
    $http = new GuzzleHttp\Client(["base_uri" => $url, "verify" => false]);
    $response = array();
    try {
        $response = $http->request("POST", $url, [
            "headers" => $headers,
            "body" => json_encode($body)
        ]);
    
        $response = json_decode($response->getBody(), true);
    } catch (Exception $ex) {
        $response["status"] = "error";
        $response["message"] = $ex->getMessage();
    }

    return $response;
}
?>
