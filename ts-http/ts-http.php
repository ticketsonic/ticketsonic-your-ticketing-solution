<?php
require("connection.php");

include_once 'helper.inc';
$http = new Swoole\HTTP\Server("127.0.0.1", 9501);

function onRequest($request, $response) {
    require("connection.php");
    //$http->on('request', function ($request, $response) {
    $body = json_decode($request->rawcontent());

    // Order hash
    $stmt = $dbConnection->prepare('SELECT * FROM order_unique_hash WHERE `hash` = :hash1');
    $stmt->execute(['hash1' => $body->order_hash]);
    $rows = $stmt->rowCount();
    if ($rows > 0) {
        $response->header("Content-Type", "text/plain");
        //$response->end("<h1>Hash already used</h1>");
        //return;
    }

    // Promoter email
    $stmt = $dbConnection->prepare('SELECT u.uid FROM users u
                                    LEFT JOIN users_roles ur ON u.uid = ur.uid
                                    LEFT JOIN role r ON ur.rid = r.rid
                                    WHERE `mail` = :mail AND r.name = "promoter"');
    $stmt->execute(['mail' => $body->promoter_email]);
    $rows = $stmt->rowCount();
    if ($rows == 0) 
        $response->end("<h1>Unknown promoter</h1>");
    $uid = '';
    foreach ($stmt as $row) {
        $uid = $row['uid'];
    }

    // Valid key
    $stmt = $dbConnection->prepare('SELECT field_promoter_api_key_value FROM field_data_field_promoter_api_key WHERE `field_promoter_api_key_value` = :key');
    $stmt->execute(['key' => $body->promoter_api_key]);
    $rows = $stmt->rowCount();
    if ($rows == 0) 
        $response->end("<h1>Unknown promoter key</h1>");

    // Sufficient stock
    $ticket_data = array();
    // Not using where in because there were problems with paramterizing where in and moved on
    foreach($body->tickets as $key => $ticket) {
        $stmt = $dbConnection->prepare('SELECT * FROM commerce_product cp LEFT JOIN field_data_commerce_stock fdcs ON cp.product_id = fdcs.entity_id WHERE cp.sku = :sku');
        $stmt->execute(['sku' => $ticket->sku]);
        $rows = $stmt->rowCount();
        $data = $stmt->fetch();
        if ($rows == 0) 
            $response->end("<h1>Unknown promoter sku: " . $ticket->sku . "</h1>");

        if ($data['commerce_stock_value'] < $ticket->stock)
            $response->end("<h1>Insuffiecint stock: " . $ticket->stock . " for sku: " . $ticket->sku . "</h1>");
    }
    /// Checks end

    /// Get private key for signing
    $key = "";
    $stmt = $dbConnection->prepare('SELECT field_promoter_private_key_value FROM field_data_field_promoter_private_key WHERE `entity_id` = :uid');
    $stmt->execute(['uid' => $uid]);
    $data = $stmt->fetch();
    $key = $data['field_promoter_private_key_value'];

    $privKey = "-----BEGIN PRIVATE KEY-----\n";
    $privKey .= $key;
    $privKey .= "\n-----END PRIVATE KEY-----\n";

    // Get all relevant data
    $tickets_arr = array();
    foreach($body->tickets as $key => $ticket) {
        $stmt = $dbConnection->prepare('SELECT cp.sku, cp.product_id, fdcp.commerce_price_amount, fdfei.field_event_id_value
                                        FROM commerce_product cp 
                                        LEFT JOIN field_data_commerce_stock fdcs ON cp.product_id = fdcs.entity_id
                                        LEFT JOIN field_data_commerce_price fdcp ON cp.product_id = fdcp.entity_id
                                        LEFT JOIN field_data_field_event_id fdfei ON cp.product_id = fdfei.entity_id
                                        WHERE cp.sku = :sku');
        $stmt->execute(['sku' => $ticket->sku]);
        $data = $stmt->fetch();
        for ($i = 0; $i < $ticket->stock; $i++) {
            $temp = array(
                'hash' => base64_encode(openssl_random_pseudo_bytes(16)),
                'sku' => $data['sku'],
                'product_id' => $data['product_id'],
                'price' => $data['commerce_price_amount'],
                'event_id' => $data['field_event_id_value']
            );
            // TODO: segments

            $sensitive_ticket_data = get_ticket_contents_for_encryption($temp);
            $is_encrypted = openssl_private_encrypt($sensitive_ticket_data, $encrypted_sensitive_ticket_data, $privKey);

            if ($is_encrypted === false) {
                $response->end("encryption_failure");
            }
            $ticket_single = array(
                'encrypted_data' => base64_encode($encrypted_sensitive_ticket_data),
                'code' => 'rsa',
                'sku' => $ticket->sku
            );
            
            $tickets_arr[] = $ticket_single;
        }
    }

    $user_response = array('status' => 'success', 'tickets' => $tickets_arr);
    $response->end(json_encode($user_response));

    // Insert order hash
    $stmt = $dbConnection->prepare('INSERT INTO order_unique_hash (`hash`) VALUES (:hash1)');
    //stmt->execute(['hash1' => $body->order_hash]);

    // $payload = array('request_body' => $body, 'tickets_arr' => $tickets_arr);
    // $url = 'http://ticketshit.local/en/v1/enqueue_tickets';
    // $ch = curl_init($url);
    // curl_setopt_array($ch, array(
    //     CURLOPT_POST => TRUE,
    //     CURLOPT_RETURNTRANSFER => TRUE,
    //     CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    //     CURLOPT_POSTFIELDS => json_encode($payload)
    // ));

    // Send the request
    // $resp = curl_exec($ch);

    $payload = array(array('request_body' => $body, 'tickets_arr' => $tickets_arr));
    $stmt = $dbConnection->prepare('INSERT INTO queue (`name`, `data`, `expire`, `created`) VALUES ("tickets_order_generator_queue", :blob_data, 0, :created)');
    $stmt->execute(
        [
            'blob_data' => serialize($payload),
            'created' => time()
        ]);
    }

$http->on("request", "onRequest");
$http->start();

?>