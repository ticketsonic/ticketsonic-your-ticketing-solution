<?php

require('mpdf_generator.php');

class Helper {
    private $eventhome;

    public function __construct() {
        $this->eventhome = new EventHome();
    }

    public function sync_tickets_with_remote($url, $email, $key) {
        $response = $this->eventhome->get_sync_ticket_data($url, $email, $key);

        if ($response == null) {
            woo_ts_admin_notice('Error syncing tickets' , 'error');
            return;
        }

        $imported_count = 0;
        foreach ($response['tickets'] as $key => $ticket) {
            $woo_product_id = wc_get_product_id_by_sku($ticket['sku']);

            $ticket_obj = new WC_Product_Simple();

            // Ticket does not exist so we skip
            if ($woo_product_id != 0) {
                $ticket_obj = new WC_Product_Simple($woo_product_id);
            }

            $ticket_obj->set_sku($ticket['sku']);
            $ticket_obj->set_name($ticket['ticket_title_en'] . ' ' . $ticket['ticket_description_en']);
            $ticket_obj->set_status('publish');
            $ticket_obj->set_catalog_visibility('visible');
            $ticket_obj->set_description($ticket['ticket_description_en']);
            
            $price = (int)$ticket['price'] / 100;
            $ticket_obj->set_price($price);
            $ticket_obj->set_regular_price($price);
            $ticket_obj->set_manage_stock(true);
            $ticket_obj->set_stock_quantity($ticket['stock']);
            $ticket_obj->set_stock_status('instock');
            $ticket_obj->set_sold_individually(false);
            $ticket_obj->set_downloadable(true);
            $ticket_obj->set_virtual(true);

            $ticketshit_term = get_term_by('slug', 'ticketshit', 'product_cat');
            if ($ticketshit_term) {
                $ticket_obj->set_category_ids(array($ticketshit_term->term_id));
            }

            $woo_ticket_id = $ticket_obj->save();

            $imported_count++;
        }

        $result = array('imported_count' => $imported_count, 'user_public_key' => $response['user_public_key']);
        return $result;
    }

    public function order_tickets_in_remote($order_id, $url, $email, $key) {
        write_log('request_barcodes_from_ts for order ' . $order_id . ' is fired');
        write_log('sending req to TS');
        $data = $this->prepare_request_body($order_id, $email, $key);
        $response = $this->eventhome->order_tickets_in_remote($url, $data);
    
        write_log('result from the request to TS for ' . $order_id . ' is received');
    
        $order = wc_get_order($order_id);
        if ($response['status'] == 'failure') {
            write_log('Error fetching result for order ' . $order_id . ': '. $response['message']);
            $order->update_status('failed', 'Error fetching result for order ' . $order_id . ': '. $response['message']);
            return;
        }
        write_log('$json_response is: ' . print_r($response, 1));

        $ticket_file_paths = $this->generate_ticket_files($response, $order_id);
        write_log('File tickets generation for order ' . $order_id . ' is completed');
        
        $order->add_meta_data('ticket_file_paths', $ticket_file_paths);
        $order->save();
        write_log('Order meta for ticket files for order ' . $order_id . ' is saved');

        return $order;
    }

    public function display_ticket_links_in_order_details($order) {
        print '<br class="clear" />';
        print '<h4>Ticket Files</h4>';
        $ticket_file_paths = $order->get_meta('ticket_file_paths');
        $ticket_files_url_path = $ticket_file_paths['ticket_file_url_path'];
        if (!empty($ticket_files_url_path)) {
            foreach($ticket_files_url_path as $key => $ticket_file_path) {
                print('<div><a href="' . $ticket_file_path . '">Tickets</a></div>');
            }
            print '<br class="clear" />';
        } else {
            print('<div>No ticket files found for this order</div>');
        }
    }

    private function prepare_request_body($order_id, $email, $key) {
        $order = wc_get_order($order_id);
        $data = array(
            'headers' => array(
                'api_userid' => $email,
                'api_key' => $key,
            ),
            'payload' => array(
                'order_hash' => bin2hex(openssl_random_pseudo_bytes(16)),
                'order_details' => array(
                    'customer_billing_name' => $this->get_customer_name($order),
                    'customer_billing_company' => $this->get_customer_company($order)
                ),
                'tickets' => array()
            )
        );
    
        $items = $order->get_items();
        foreach($items as $item) {
            $ticket = new WC_Product_Simple($item['product_id']);
            $data['payload']['tickets'][] = array('sku' => $ticket->get_sku(), 'stock' => $item['quantity']);
        }

        return $data;
    }

    private function generate_ticket_files($response, $order_id) {
        // TODO: Add a check if is writable
        wp_mkdir_p(WOO_TS_UPLOADPATH . '/' . $order_id . '/');
    
        $ticket_file_paths = array();
        
        $starttime = microtime(true);
        foreach ($response['tickets'] as $key => $ticket) {
            write_log('start generation of ticket file');
            $decoded = $this->decode_barcode($ticket['encrypted_data']);
            if ($decoded == null)
                return null;
    
            $woo_product_id = wc_get_product_id_by_sku($ticket['sku']);
            $woo_product = new WC_Product_Simple($woo_product_id);
    
            // Create separate ticket files
            $temp = $this->generate_file($woo_product->get_name(), $woo_product->get_description(), $decoded['formatted_price'], $decoded['sensitive_decoded'], $order_id, $key);
            $ticket_file_paths['ticket_file_abs_path'][] = $temp['ticket_file_abs_path'];
            $ticket_file_paths['ticket_file_url_path'][] = $temp['ticket_file_url_path'];
            write_log('end of generation of ticket file at: ' . date('Y-m-d H:i:s'));
        }
    
        $endtime = microtime(true);
        $temp = $endtime - $starttime;
        write_log('start: ' . $starttime);
        write_log('end: ' . $endtime);
        write_log('time diff: ' . $temp);
        
        return $ticket_file_paths;
    }

    public function generate_file($name, $description, $price, $sensitive_decoded, $order_id, $key) {
        $file_generator = new MPDF_Generator();

        $ticket_file_abs_path = WOO_TS_UPLOADPATH . '/' . $order_id . '/' . $key . '.' . $file_generator->extension; // file extension will be appended by the generator
        $ticket_file_url_path = WOO_TS_UPLOADURLPATH . '/' . $order_id . '/' . $key . '.' . $file_generator->extension;; // file extension will be appended by the generator
        $file_generator->generate_ticket($name, $description, $price, $sensitive_decoded, $ticket_file_abs_path);
        
        $ticket_file_paths = array('ticket_file_url_path' => $ticket_file_url_path, 'ticket_file_abs_path' => $ticket_file_abs_path);
        return $ticket_file_paths;
    }

    public function decode_barcode($encrypted_data) {
        $public_key = openssl_pkey_get_public(woo_ts_get_option('user_public_key', ''));
        if (!$public_key) {
            write_log('Public key corrupted');
            return null;
        }

        $result = array();
        $result['sensitive_decoded'] = base64_decode($encrypted_data);
        $result['is_decrypted'] = openssl_public_decrypt($result['sensitive_decoded'], $sensitive_decrypted, $public_key);
        $result['decrypted_ticket'] = parse_raw_recrypted_ticket($sensitive_decrypted);
        $result['formatted_price'] = floatval($result['decrypted_ticket']['price']) / 100 . ' ' . currency_to_ascii(get_woocommerce_currency());

        return $result;
    }

    public function send_tickets_to_customer_after_order_completed($order_id, $url, $email, $key) {
        $order = wc_get_order($order_id);

        // Checking if there is a order meta data with the generated tickets
        // Online payment methods skip the woocommerce_order_status_processing event
        // We need to make sure we got the meta data containing ticket locations in fs for them too.
        if (empty($order->get_meta('ticket_file_paths')))
            $order = order_tickets_in_remote($order_id, $url, $email, $key);

        write_log('woocommerce_order_status_completed');
        write_log('send_tickets_to_email_after_order_completed for order ' . $order_id . ' is fired');

        $ticket_files = $order->get_meta('ticket_file_paths');
        
        if (!empty($ticket_files)) {
            $ticket_file_abs_paths = $ticket_files['ticket_file_abs_path'];
            
            // TODO: Check if there are files generated
            $mail_sent = $this->send_tickets_by_mail($order->get_billing_email(), $order_id, $ticket_file_abs_paths);
            write_log('mail status: ' . $mail_sent);
            write_log('mail attachments: ' . print_r($ticket_file_abs_paths));
            if (!$mail_sent)
                write_log('Could not send mail with tickets');
        }

        write_log('Tickets files for order ' . $order_id . ' are sent via mail to ' . $order->get_billing_email());
    }

    private function send_tickets_by_mail($target_user_mail, $order_id, $ticket_file_abs_paths) {
        write_log('send_tickets_to_email_after_payment_confirmed fired');
        if (!empty($ticket_file_abs_paths)) {
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $mail_sent = wp_mail($target_user_mail, woo_ts_get_option('email_subject', ''), woo_ts_get_option('email_body', ''), $headers, $ticket_file_abs_paths);
    
            return $mail_sent;
        }
    
        return false;
    }

    private function get_customer_name($order) {
        return $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    }
    
    private function get_customer_company($order) {
        return $order->get_billing_company();;
    }
}

if (!function_exists('write_log')) {
    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(date('Y-m-d H:i:s') . ': ' . print_r($log, true));
            } else {
                error_log(date('Y-m-d H:i:s') . ': ' . $log);
            }
        }
    }
}

function currency_to_ascii($currency_code) {
    $currencies = array(
        'BGN' => 'BGN',
        'USD' => chr(36),
        'EUR' => chr(128),
        'GBP' => chr(163)
    );

    return $currencies[$currency_code];
}

?>
