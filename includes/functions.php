<?php

require("ticketsonic.php");

if (is_admin()) {
    include_once( WOO_TS_PATH . "includes/admin.php" );

    function woo_ts_import_init() {
        global $wpdb;
        $wpdb->hide_errors();
        @ob_start();

        $action = woo_ts_get_action();
        switch( $action ) {
            case "save-settings":
                woo_ts_update_option( "api_key", ( isset( $_POST["api_key"] ) ? sanitize_text_field( $_POST["api_key"] ) : "" ) );
                woo_ts_update_option( "api_userid", ( isset( $_POST["api_userid"] ) ? sanitize_text_field( $_POST["api_userid"] ) : "" ) );
                woo_ts_update_option( "email_subject", ( isset( $_POST["email_subject"] ) ? sanitize_text_field( $_POST["email_subject"] ) : "" ) );
                woo_ts_update_option( "email_body", ( isset( $_POST["email_body"] ) ? wp_kses($_POST["email_body"], allowed_html()) : "" ) );
                woo_ts_update_option( "ticket_info_endpoint", ( isset( $_POST["ticket_info_endpoint"] ) ? sanitize_text_field( $_POST["ticket_info_endpoint"] ) : "" ) );
                woo_ts_update_option( "event_info_endpoint", ( isset( $_POST["event_info_endpoint"] ) ? sanitize_text_field( $_POST["event_info_endpoint"] ) : "" ) );
                woo_ts_update_option( "new_event_endpoint", ( isset( $_POST["new_event_endpoint"] ) ? sanitize_text_field( $_POST["new_event_endpoint"] ) : "" ) );
                woo_ts_update_option( "new_ticket_endpoint", ( isset( $_POST["new_ticket_endpoint"] ) ? sanitize_text_field( $_POST["new_ticket_endpoint"] ) : "" ) );
                woo_ts_update_option( "external_order_endpoint", ( isset( $_POST["external_order_endpoint"] ) ? sanitize_text_field( $_POST["external_order_endpoint"] ) : "" ) );
                woo_ts_update_option( "event_id", ( isset( $_POST["event_id"] ) ? sanitize_text_field( $_POST["event_id"] ) : "" ) );

                upload_custom_ticket_background();

                $message = __( "Settings saved.", "woo_ts" );
                woo_ts_admin_notice( $message );
                break;

            case "sync_with_ts":
                $url = woo_ts_get_option("ticket_info_endpoint", "");
                if (empty($url)) {
                    woo_ts_admin_notice("Ticket Info Endpoint have to set in Settings", "error");
                    return;
                }
                
                $email = woo_ts_get_option("api_userid", "");
                if (empty($email)) {
                    woo_ts_admin_notice("Partner E-mail have to set in Settings", "error");
                    return;
                }

                $key = woo_ts_get_option("api_key", "");
                if (empty($key)) {
                    woo_ts_admin_notice("Partner API Key have to set in Settings", "error");
                    return;
                }

                $event_id = woo_ts_get_option("event_id", "");
                
                $result = sync_tickets_with_remote($url, $email, $key, $event_id);

                if ($result["status"] == "success") {
                    woo_ts_admin_notice($result["message"], "notice");
                    woo_ts_admin_notice("Public Key" . $result["user_public_key"], "notice");
                    woo_ts_update_option("user_public_key", "-----BEGIN PUBLIC KEY-----\n" . $result["user_public_key"] . "\n-----END PUBLIC KEY-----");
                }

                break;

            case "create-event":
                $url = woo_ts_get_option("new_event_endpoint", "");
                if (empty($url)) {
                    woo_ts_admin_notice("New Event Endpoint have to set in Settings", "error");
                    return;
                }
                
                $email = woo_ts_get_option("api_userid", "");
                if (empty($email)) {
                    woo_ts_admin_notice("Partner E-mail have to set in Settings", "error");
                    return;
                }

                $key = woo_ts_get_option("api_key", "");
                if (empty($key)) {
                    woo_ts_admin_notice("Partner API Key have to set in Settings", "error");
                    return;
                }

                $event_title = sanitize_text_field( $_POST["event_title"] );
                if (empty($event_title)) {
                    woo_ts_admin_notice("Event title field have to set", "error");
                    return;
                }

                $event_description = sanitize_text_field( $_POST["event_description"] );
                $event_datetime = sanitize_text_field( $_POST["event_datetime"] );
                $event_location = sanitize_text_field( $_POST["event_location"] );
                
                $tickets_data = $_POST["ticket"];
                foreach ($tickets_data as $value) {
                    if (empty($value["primary_text_pl"])) {
                        $value["primary_text_pl"] = sanitize_text_field( $value["primary_text_pl"] );
                        woo_ts_admin_notice("Ticket title must be set", "error");

                        return;
                    }

                    if (empty($value["price"])) {
                        $value["price"] = sanitize_text_field( $value["price"] );
                        woo_ts_admin_notice("Ticket price must be set", "error");

                        return;
                    }

                    if (!is_int(intval($value["price"]))) {
                        $value["price"] = sanitize_text_field( $value["price"] );
                        woo_ts_admin_notice("Ticket price must be an integer number", "error");

                        return;
                    }

                    if (empty($value["stock"])) {
                        $value["stock"] = sanitize_text_field( $value["stock"] );
                        woo_ts_admin_notice("Ticket stock must be set", "error");

                        return;
                    }

                    if (empty($value["currency"])) {
                        $value["currency"] = sanitize_text_field( $value["currency"] );
                        woo_ts_admin_notice("Ticket currency must be set", "error");

                        return;
                    }
                }

                $badge_text_horizontal_location = sanitize_text_field( $_POST["badge_text_horizontal_location"] );
                if (empty($badge_text_horizontal_location)) {
                    woo_ts_admin_notice("Badge text horizontal location must be set", "error");
                    return;
                }

                $badge_text_vertical_location = sanitize_text_field( $_POST["badge_text_vertical_location"] );
                if (empty($badge_text_vertical_location)) {
                    woo_ts_admin_notice("Badge text vertical location must be set", "error");
                    return;
                }
                
                $badge_primary_text_fontsize = sanitize_text_field( $_POST["badge_primary_text_fontsize"] );
                if (empty($badge_primary_text_fontsize)) {
                    woo_ts_admin_notice("Primary text font size must be set", "error");
                    return;
                }

                if (!is_int(intval($badge_primary_text_fontsize))) {
                    woo_ts_admin_notice("Primary text font size must be an integer number", "error");
                    return;
                }

                $badge_secondary_text_fontsize = sanitize_text_field( $_POST["badge_secondary_text_fontsize"] );
                if (empty($badge_secondary_text_fontsize)) {
                    woo_ts_admin_notice("Primary text font size must be set", "error");
                    return;
                }

                if (!is_int(intval($badge_secondary_text_fontsize))) {
                    woo_ts_admin_notice("Secondary text font size must be an integer number", "error");
                    return;
                }

                $badge_primary_text_color = sanitize_text_field( $_POST["badge_primary_text_color"] );
                if (empty($badge_primary_text_color)) {
                    woo_ts_admin_notice("Primary text color must be set", "error");
                    return;
                }

                $badge_secondary_text_color = sanitize_text_field( $_POST["badge_secondary_text_color"] );
                if (empty($badge_secondary_text_color)) {
                    woo_ts_admin_notice("Secondary text color must be set", "error");
                    return;
                }

                upload_custom_badge_background();
                
                $result = request_create_new_event($url, $email, $key, $event_title, $event_description, $event_datetime,
                                                   $event_location, $tickets_data, $badge_text_horizontal_location,
                                                   $badge_text_vertical_location, $badge_primary_text_fontsize,
                                                   $badge_secondary_text_fontsize, $badge_primary_text_color, $badge_secondary_text_color);

                if ($result["status"] == "success") {
                    woo_ts_admin_notice("Status: success<br>Event ID: " . $result["event_id"] . " successfully sent for processing. You will receive an email when it is processed.", "notice");
                } else {
                    woo_ts_admin_notice("Failed to request new event: " . $result["message"], "error");
                }

                break;

            case "create-ticket":
                $url = woo_ts_get_option("new_ticket_endpoint", "");
                if (empty($url)) {
                    woo_ts_admin_notice("New Ticket Endpoint have to set in Settings", "error");
                    return;
                }
                
                $email = woo_ts_get_option("api_userid", "");
                if (empty($email)) {
                    woo_ts_admin_notice("Partner E-mail have to set in Settings", "error");
                    return;
                }

                $key = woo_ts_get_option("api_key", "");
                if (empty($key)) {
                    woo_ts_admin_notice("Partner API Key have to set in Settings", "error");
                    return;
                }

                $ticket_eventid = sanitize_text_field( $_POST["ticket_eventid"] );
                if (empty($ticket_eventid)) {
                    woo_ts_admin_notice("Ticket event id field have to set", "error");
                    return;
                }

                $ticket_title = sanitize_text_field( $_POST["primary_text_pl"] );
                if (empty($ticket_title)) {
                    woo_ts_admin_notice("Ticket title field have to set", "error");
                    return;
                }

                $ticket_description = sanitize_text_field( $_POST["secondary_text_pl"]);

                $ticket_price = sanitize_text_field(  $_POST["ticket_price"] );
                if (empty($ticket_price)) {
                    woo_ts_admin_notice("Ticket price field have to set", "error");
                    return;
                }

                if (!is_int(intval(sanitize_text_field( $ticket_price )))) {
                    woo_ts_admin_notice("Ticket price must be an integer number", "error");

                    return;
                }

                $ticket_currency = sanitize_text_field( $_POST["ticket_currency"] );
                if (empty($ticket_currency)) {
                    woo_ts_admin_notice("Ticket currency field have to set", "error");
                    return;
                }

                $ticket_stock = sanitize_text_field( $_POST["ticket_stock"] );
                if (empty($ticket_stock)) {
                    woo_ts_admin_notice("Ticket stock field have to set", "error");
                    return;
                }

                
                $result = request_create_new_ticket($url, $email, $key, $ticket_eventid, $ticket_title, $ticket_description, $ticket_price, $ticket_currency, $ticket_stock);

                if ($result["status"] == "success") {
                    woo_ts_admin_notice("Status: success<br>Ticket for event ID: " . $ticket_eventid . " successfully sent for processing. You will receive an email when it is processed.", "notice");
                } else {
                    woo_ts_admin_notice("Failed to request new event: " . $result["message"], "error");
                }

                break;
        }
    }

    // Add plugin ticket term
    function woo_ts_structure_init() {
        wp_insert_term("TicketSonic Tickets","product_cat",
            array(
            "description"=> "TicketSonic Tickets imported tickets.",
            "slug" => "ticketsonic"
            )
        );

        // TODO: Add catch handler
        wp_mkdir_p(WOO_TS_TICKETSDIR);
        wp_mkdir_p(WOO_TS_UPLOADPATH);
    }
}

add_action("woocommerce_payment_complete", "mysite_woocommerce_payment_complete");
function mysite_woocommerce_payment_complete($order_id) {
    // write_log("mysite_woocommerce_payment_complete for order " . $order_id . " is fired");
}

add_action("woocommerce_order_status_processing", "create_tickets_order_in_remote", 10, 1);
function create_tickets_order_in_remote($order_id) {
    $url = woo_ts_get_option("external_order_endpoint", "");
    $email = woo_ts_get_option("api_userid", "");
    $key = woo_ts_get_option("api_key", "");

    request_create_tickets_order_in_remote($order_id, $url, $email, $key);
}

/**
 * Add a custom action to order actions select box on edit order page
 * Ability to manually generate ticket files
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
add_action( "woocommerce_order_actions", "manual_ticket_generation_order_action" );
function manual_ticket_generation_order_action($actions) {
    global $theorder;

    $order_id = $theorder->id;
    $endpoint_url = woo_ts_get_option("external_order_endpoint", "");
    $api_userid = woo_ts_get_option("api_userid", "");
    $promoter_api_key = woo_ts_get_option("api_key", "");

    //FIXME: check if arguments are not. Causes failure in WP if $endpoint_url, $api_userid, $promoter_api_key are null
    // request_create_tickets_order_in_remote($order_id, $endpoint_url, $api_userid, $promoter_api_key, $data);

    $actions["wc_manual_ticket_generation_order_action"] = __( "Generate tickets", "generate-tickets" );
    return $actions;
}

add_action("woocommerce_order_status_completed", "send_tickets_to_customer_after_order_completed", 10, 1);
function send_tickets_to_customer_after_order_completed($order_id) {
    $url = woo_ts_get_option("external_order_endpoint", "");
    $email = woo_ts_get_option("api_userid", "");
    $key = woo_ts_get_option("api_key", "");

    $order = request_create_tickets_order_in_remote($order_id, $url, $email, $key);

    if ($order == null) {
        return;
    }

    write_log("woocommerce_order_status_completed");
    write_log("send_tickets_to_email_after_order_completed for order " . $order_id . " is fired");

    $ticket_files = $order->get_meta("ticket_file_paths");
    
    if (!empty($ticket_files)) {
        $ticket_file_abs_paths = $ticket_files["ticket_file_abs_path"];
        
        // TODO: Check if there are files generated
        $mail_sent = send_tickets_by_mail($order->get_billing_email(), $order_id, $ticket_file_abs_paths);
        write_log("mail status: " . $mail_sent);
        write_log("mail attachments: " . print_r($ticket_file_abs_paths));
        if (!$mail_sent)
            write_log("Could not send mail with tickets");
    }

    write_log("Tickets files for order " . $order_id . " are sent via mail to " . $order->get_billing_email());
}

add_action( "woocommerce_admin_order_data_after_order_details", "display_ticket_links_in_order_details" );
function display_ticket_links_in_order_details($order) {
    print "<br class=\"clear\" />";
    print "<h4>Ticket Files</h4>";
    $ticket_file_paths = $order->get_meta("ticket_file_paths");
    $ticket_files_url_path = $ticket_file_paths["ticket_file_url_path"];
    if (!empty($ticket_files_url_path)) {
        foreach($ticket_files_url_path as $key => $ticket_file_path) {
            print("<div><a href=\"" . $ticket_file_path . "\">Tickets</a></div>");
        }
        print "<br class=\"clear\" />";
    } else {
        print("<div>No ticket files found for this order</div>");
    }
}

add_action( "admin_notices", "ticketsdir_writable_error_message" );
function ticketsdir_writable_error_message() {
    if (!is_writable(WOO_TS_TICKETSDIR)) {
        print "<div class=\"error notice\">";
        print    "<p>Ensure " . WOO_TS_TICKETSDIR . " is writable</p>";
        print "</div>";
    } else {
        print "<div class=\"notice notice-success\">";
        print    "<p>" . WOO_TS_TICKETSDIR . " is writable</p>";
        print "</div>";
    }
}

add_action( "admin_notices", "uploadpath_writable_error_message" );
function uploadpath_writable_error_message() {
    if (!is_writable(WOO_TS_UPLOADPATH)) {
        print "<div class=\"error notice\">";
        print    "<p>Ensure " . WOO_TS_UPLOADPATH . " is writable</p>";
        print "</div>";
    } else {
        print "<div class=\"notice notice-success\">";
        print    "<p>" . WOO_TS_UPLOADPATH . " is writable</p>";
        print "</div>";
    }
}


function woo_ts_get_action( $prefer_get = false ) {
    if ( isset( $_GET["action"] ) && $prefer_get )
        return sanitize_text_field( $_GET["action"] );

    if ( isset( $_POST["action"] ) )
        return sanitize_text_field( $_POST["action"] );

    if ( isset( $_GET["action"] ) )
        return sanitize_text_field( $_GET["action"] );

    return false;
}

function woo_ts_get_option( $option = null, $default = false, $allow_empty = false ) {
    $output = "";
    if( isset( $option ) ) {
        $separator = "_";
        $output = get_option( WOO_TS_PREFIX . $separator . $option, $default );
        if( $allow_empty == false && $output != 0 && ( $output == false || $output == "" ) )
            $output = $default;
    }
    return $output;
}

function woo_ts_update_option( $option = null, $value = null ) {
    $output = false;
    if( isset( $option ) && isset( $value ) ) {
        $separator = "_";
        $output = update_option( WOO_TS_PREFIX . $separator . $option, $value );
    }
    return $output;
}

function wpse_141088_upload_dir( $dir ) {
    return array(
        "path"   => WOO_TS_UPLOADPATH,
        "url"    => WOO_TS_UPLOADPATH,
        "subdir" => "/" . WOO_TS_DIRNAME,
    ) + $dir;
}

?>