<?php

function get_tickets_meta($tickets_data, $order_id) {
    $tickets_meta = array();

    foreach ($tickets_data as $index => $ticket) {
        $decoded_ticket = decode_barcode($ticket["encrypted_data"]);
        if ($decoded_ticket["status"] == "failure")
            return $decoded_ticket;

        $woo_product_id = wc_get_product_id_by_sku($ticket["sku"]);
        $woo_product = new WC_Product_Simple($woo_product_id);

        $ticket_meta = array(
            "title" => $woo_product->get_name(),
            "description" => $woo_product->get_description(),
            "formatted_price" => $decoded_ticket["payload"]["formatted_price"],
            "sensitive_decoded" => bin_to_int_array($decoded_ticket["payload"]["sensitive_decoded"])
        );
        
        $tickets_meta[] = $ticket_meta;
    }

    return array("status" => "success", "payload" => array("tickets_meta" => $tickets_meta));
}

function get_ticket_file_paths($tickets_data, $order_id) {
    $is_dir_created = wp_mkdir_p(WOO_TS_UPLOADPATH . "/" . $order_id . "/");
    if (!$is_dir_created) {
        return array("status" => "failure", "message" => "Unable to create a folder for storing the ticket files");
    }

    $ticket_files_paths = array();
    $ticket_url_paths = array();
    
    foreach ($tickets_data as $index => $ticket) {
        $decoded_barcode = decode_barcode($ticket["encrypted_data"]);
        if ($decoded_barcode["status"] == "failure")
            return $decoded_barcode;

        $woo_product_id = wc_get_product_id_by_sku($ticket["sku"]);
        $woo_product = new WC_Product_Simple($woo_product_id);

        $generated_file = get_ticket_file_path($woo_product->get_name(), $woo_product->get_description(), $decoded_barcode["payload"]["formatted_price"], $decoded_barcode["payload"]["sensitive_decoded"], $order_id, $index);
        if ($generated_file["status"] == "failure")
            return $generated_file;

        $ticket_files_paths[] = $generated_file["ticket_file_abs_path"];
        $ticket_url_paths[] = $generated_file["ticket_file_url_path"];
    }
    
    return array("status" => "success", "payload" => array("ticket_file_abs_path" => $ticket_files_paths, "ticket_file_url_path" => $ticket_url_paths));
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

function get_ticket_file_path($name, $description, $price, $sensitive_decoded, $order_id, $index) {
    $file_generator = new MPDF_Generator();

    $ticket_file_abs_path = WOO_TS_UPLOADPATH . "/" . $order_id . "/" . $index . "." . $file_generator->extension; // file extension will be appended by the generator
    $ticket_file_url_path = WOO_TS_UPLOADURLPATH . "/" . $order_id . "/" . $index . "." . $file_generator->extension;; // file extension will be appended by the generator
    
    $result = $file_generator->generate_file($name, $description, $price, $sensitive_decoded, $ticket_file_abs_path);
    if ($result["status"] == "failure")
        return $result;
    
    $ticket_file_paths = array("ticket_file_url_path" => $ticket_file_url_path, "ticket_file_abs_path" => $ticket_file_abs_path);
    return $ticket_file_paths;
}

function send_pdf_tickets_by_mail($target_user_mail, $order_id, $ticket_file_abs_paths) {
    if (!empty($ticket_file_abs_paths)) {
        $headers = array("Content-Type: text/html; charset=UTF-8");
        $mail_sent = wp_mail($target_user_mail, woo_ts_get_option("email_subject", ""), woo_ts_get_option("email_body", ""), $headers, $ticket_file_abs_paths);

        return $mail_sent;
    }

    return false;
}

function send_html_tickets_by_mail($target_user_mail, $tickets_data) {
    $tickets_meta = $tickets_data["tickets_meta"];
    if (!empty($tickets_meta)) {
        $headers = array("Content-Type: text/html; charset=UTF-8");
        
        foreach ($tickets_meta as $key => $ticket_meta) {
            $ticket_title = $ticket_meta["title"];
            $ticket_description = $ticket_meta["description"];
            $ticket_price = $ticket_meta["formatted_price"];

            $email_body = woo_ts_get_option("email_body", "");
            $email_subject = woo_ts_get_option("email_subject", "");
            $qr = null;

            $int_array = int_array_to_bin($ticket_meta["sensitive_decoded"]);
            $matrix = get_qr_matrix(base64_encode($int_array));
            $qr .= "<table><tbody>";
            
            foreach($matrix as $row) {
                $qr .= "<tr>";
                
                for($i = 0; $i < $row->count(); $i++)
                    if ($row[$i] == 1)
                        $qr .= "<td class=\"black-square\"></td>";
                    else
                        $qr .= "<td class=\"white-square\"></td>";
                
                $qr .= "</tr>";
            }

            $qr .= "</tbody></table>";

            $email_body = str_replace("[ticket_number]", $key + 1, $email_body);
            $email_body = str_replace("[ticket_qr]", $qr, $email_body);
            $email_body = str_replace("[ticket_title]", $ticket_title, $email_body);
            $email_body = str_replace("[ticket_description]", $ticket_description, $email_body);
            $email_body = str_replace("[ticket_price]", $ticket_price, $email_body);

            $email_subject = str_replace("[ticket_number]", $key + 1, $email_subject);
            $email_subject = str_replace("[ticket_title]", $ticket_title, $email_subject);
            $email_subject = str_replace("[ticket_description]", $ticket_description, $email_subject);
            $email_subject = str_replace("[ticket_price]", $ticket_price, $email_subject);

            $mail_sent = wp_mail($target_user_mail, $email_subject, $email_body, $headers, null);
        }

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

function get_qr_matrix($raw_input) {
    $qrCode = BaconQrCode\Encoder\Encoder::encode($raw_input, BaconQrCode\Common\ErrorCorrectionLevel::L(), BaconQrCode\Encoder\Encoder::DEFAULT_BYTE_MODE_ECODING);
    $matrix = $qrCode->getMatrix();
    $rows = $matrix->getArray()->toArray();
    return $rows;
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
        "head" => array (
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
            "width" => array(),
            "cellspacing" => array(),
            "cellpadding" => array(),
            "border" => array(),
            "role" => array(),
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
            "align" => array(),
            "width" => array(),
            "height" => array(),
            "rowspan" => array(),
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
        "a" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "h1" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "h2" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "h3" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "h4" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "svg" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
            "viewbox" => true,
            "width" => array(),
            "height" => array(),
            "xmlns" => array(),
            "xmlns:xlink" => array(),
            "xmlns:bx" => array()
        ),
        "defs" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
        ),
        "stop" => array (
            "offset" => array(),
            "class" => array(),
            "style" => array(),
            "id" => array(),
        ),
        "lineargradient" => array (
            "id" => array(),
            "gradientunits" => array(),
            "x1" => array(),
            "x2" => array(),
            "y1" => array(),
            "y2" => array(),
            "gradienttransform" => array(),
            "xlink:href" => array(),
            "bx:pinned" => array()
        ),
        "path" => array (
            "d" => array(),
            "class" => array(),
            "style" => array(),
            "id" => array(),
            "transform" => array(),
        ),
        "rect" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
            "x" => array(),
            "y" => array(),
            "height" => array(),
            "width" => array(),
            "transform" => array(),
            "id" => array(),
        ),
        "g" => array (
            "href" => array(),
            "class" => array(),
            "style" => array(),
            "transform" => array(),
            "id" => array()
        )
    );
}

?>