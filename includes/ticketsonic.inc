<?php

function generate_ticket_files($tickets_data, $order_id) {
    $dir_created = wp_mkdir_p(WOO_TS_UPLOADPATH . "/" . $order_id . "/");
    if (!$dir_created) {
        return array("status" => "failure", "message" => "Unable to create a folder for storing the ticket files");
    }

    $generated_files_paths = array();
    
    foreach ($tickets_data as $index => $ticket) {
        $decoded_barcode = decode_barcode($ticket["encrypted_data"]);
        if ($decoded_barcode["status"] == "failure")
            return $decoded_barcode;

        $woo_product_id = wc_get_product_id_by_sku($ticket["sku"]);
        $woo_product = new WC_Product_Simple($woo_product_id);

        $generated_file = generate_ticket_file($woo_product->get_name(), $woo_product->get_description(), $decoded_barcode["payload"]["formatted_price"], $decoded_barcode["payload"]["sensitive_decoded"], $order_id, $index);
        if ($generated_file["status"] == "failure")
            return $generated_file;

        $generated_files_paths["ticket_file_abs_path"][] = $generated_file["ticket_file_abs_path"];
        $generated_files_paths["ticket_file_url_path"][] = $generated_file["ticket_file_url_path"];
    }
    
    return array("status" => "success", "payload" => $generated_files_paths);
}

function decode_barcode($encrypted_data) {
    $public_key = openssl_pkey_get_public(woo_ts_get_option("user_public_key", ""));
    if (!$public_key) {
        write_log("Public key corrupted");
        return array("status" => "failure", "message" => "Public key corrupted");
    }

    $result = array();
    $result["sensitive_decoded"] = base64_decode($encrypted_data);
    $result["is_decrypted"] = openssl_public_decrypt($result["sensitive_decoded"], $sensitive_decrypted, $public_key);
    $result["decrypted_ticket"] = parse_raw_recrypted_ticket($sensitive_decrypted);
    $result["formatted_price"] = floatval($result["decrypted_ticket"]["price"]) / 100 . " " . currency_to_ascii(get_woocommerce_currency());

    return array("status" => "success", "payload" => $result);
}

function generate_ticket_file($name, $description, $price, $sensitive_decoded, $order_id, $index) {
    $file_generator = new MPDF_Generator();

    $ticket_file_abs_path = WOO_TS_UPLOADPATH . "/" . $order_id . "/" . $index . "." . $file_generator->extension; // file extension will be appended by the generator
    $ticket_file_url_path = WOO_TS_UPLOADURLPATH . "/" . $order_id . "/" . $index . "." . $file_generator->extension;; // file extension will be appended by the generator
    
    $result = $file_generator->generate_file($name, $description, $price, $sensitive_decoded, $ticket_file_abs_path);
    if ($result["status"] == "failure")
        return $result;
    
    $ticket_file_paths = array("ticket_file_url_path" => $ticket_file_url_path, "ticket_file_abs_path" => $ticket_file_abs_path);
    return $ticket_file_paths;
}

function send_tickets_by_mail($target_user_mail, $order_id, $sensitive_decoded_tickets) {
    if (!empty($sensitive_decoded_tickets)) {
        $headers = array("Content-Type: text/html; charset=UTF-8");

        $email_body = woo_ts_get_option("email_body", "");
        foreach ($sensitive_decoded_tickets as $sensitive_decoded_ticket) {

        }
        $mail_sent = wp_mail($target_user_mail, woo_ts_get_option("email_subject", ""), woo_ts_get_option("email_body", ""), $headers, $sensitive_decoded_tickets);

        return $mail_sent;
    }

    return false;
}

function get_customer_name($order) {
    return $order->get_billing_first_name() . " " . $order->get_billing_last_name();
}
    
function get_customer_company($order) {
    return $order->get_billing_company();;
}

function write_log($log) {
    if (true === WP_DEBUG) {
        if (is_array($log) || is_object($log)) {
            error_log(date("Y-m-d H:i:s") . ": " . print_r($log, true));
        } else {
            error_log(date("Y-m-d H:i:s") . ": " . $log);
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
    add_filter( "upload_dir", "wpse_141088_upload_dir" );
    include_once(ABSPATH . "wp-admin/includes/file.php" );
    include_once(ABSPATH . "wp-admin/includes/media.php");
    
    $allowed_file_types = array("jpg" =>"image/jpeg","jpeg" =>"image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $upload_overrides = array( "test_form" => false, "unique_filename_callback" => "filename_renamer", "mimes" => $allowed_file_types);
    $uploadedfile = $_FILES["fileToUpload"];

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    if (!$movefile || isset($movefile["error"]) ) {
        woo_ts_admin_notice($movefile["error"], "error");
    }

    remove_filter( "upload_dir", "wpse_141088_upload_dir" );
}

function upload_custom_badge_background() {
    add_filter( "upload_dir", "wpse_141088_upload_dir" );
    include_once(ABSPATH . "wp-admin/includes/file.php" );
    include_once(ABSPATH . "wp-admin/includes/media.php");
    
    $allowed_file_types = array("jpg" =>"image/jpeg","jpeg" =>"image/jpeg");
    $upload_overrides = array( "test_form" => false, "unique_filename_callback" => "badge_background_file_renamer", "mimes" => $allowed_file_types);
    $badge_file = $_FILES["badge_file"];

    $movefile = wp_handle_upload($badge_file, $upload_overrides);
    if (!$movefile || isset($movefile["error"]) ) {
        woo_ts_admin_notice($movefile["error"], "error");
    }

    remove_filter( "upload_dir", "wpse_141088_upload_dir" );
}

function filename_renamer($dir, $name, $ext){
    return "pdf_background" . $ext;
}

function badge_background_file_renamer($dir, $name, $ext){
    return "badge_background" . $ext;
}

function allowed_html() {
    return array (
        "html" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "body" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "style" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "table" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "tbody" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "th" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "tr" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "td" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "div" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "p" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "h1" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
    );
}

?>