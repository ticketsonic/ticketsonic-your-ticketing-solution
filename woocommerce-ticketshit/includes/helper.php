<?php

class Helper {
    public static function sync_tickets($url, $email, $key) {
        $response = EventHome::get_ticket_data($url, $email, $key);
        if ($response == null) {
            woo_ts_admin_notice('Error syncing tickets' , 'error');
            return;
        }

        $imported_count = 0;
        $ignored_count = 0;
        foreach ($response['tickets'] as $key => $ticket) {
            try {
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

                $ticketshit_term = get_term_by("slug", "ticketshit", "product_cat");
                if ($ticketshit_term) {
                    $ticket_obj->set_category_ids(array($ticketshit_term->term_id));
                }

                $woo_ticket_id = $ticket_obj->save();

                $imported_count++;
            } catch (WC_Data_Exception $ex) {
                $ignored_count++;
            }
        }

        $result = array('imported_count' => $imported_count, 'api_public_key' => $response['api_public_key']);
        return $result;
    }

}
if (!function_exists('write_log')) {
    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(date("Y-m-d H:i:s") . ': ' . print_r($log, true));
            } else {
                error_log(date("Y-m-d H:i:s") . ': ' . $log);
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
