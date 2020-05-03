<?php

include_once 'helper.inc';
$http = new Swoole\HTTP\Server("127.0.0.1", 9507);

function onRequest($request, $response) {
    $dbConnection = new Swoole\Coroutine\MySQL();
    $dbConnection->connect([
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'ticketshitlocal',
        'password' => 'CbLhsa7AcxQJhuSU',
        'database' => 'ticketshitlocal',
    ]);
    $body = json_decode($request->rawcontent());

    // Order hash
    $stmt = $dbConnection->prepare('SELECT * FROM order_unique_hash WHERE `hash` = ?');
    $data = $stmt->execute([$body->order_hash]);

    // if (empty($data)) {
    //     $response->header("Content-Type", "text/plain");
    //     $response->end("<h1>Hash already used</h1>");
    //     return;
    // }

    // Promoter email
    $stmt = $dbConnection->prepare('SELECT u.uid FROM users u
                                    LEFT JOIN users_roles ur ON u.uid = ur.uid
                                    LEFT JOIN role r ON ur.rid = r.rid
                                    WHERE `mail` = ? AND r.name = "promoter"');
    $data = $stmt->execute([$body->promoter_email]);
    //$rows = $stmt->rowCount();
    if (empty($data)) 
        $response->end("<h1>Unknown promoter</h1>");
    $uid = '';
    foreach ($data as $row) {
        $uid = $row['uid'];
    }

    // Valid key
    $stmt = $dbConnection->prepare('SELECT field_promoter_api_key_value FROM field_data_field_promoter_api_key WHERE `field_promoter_api_key_value` = ?');
    $data = $stmt->execute([$body->promoter_api_key]);
    if (empty($data)) 
        $response->end("<h1>Unknown promoter key</h1>");

    // Sufficient stock
    $ticket_data = array();
    // Not using where in because there were problems with paramterizing where in and moved on
    foreach($body->tickets as $key => $ticket) {
        $stmt = $dbConnection->prepare('SELECT * FROM commerce_product cp LEFT JOIN field_data_commerce_stock fdcs ON cp.product_id = fdcs.entity_id WHERE cp.sku = ?');
        $data = $stmt->execute([$ticket->sku]);
        if (empty($data)) 
            $response->end("<h1>Unknown promoter sku: " . $ticket->sku . "</h1>");

        if ($data[0]['commerce_stock_value'] < $ticket->stock)
            $response->end("<h1>Insuffiecint stock: " . $ticket->stock . " for sku: " . $ticket->sku . "</h1>");
    }
    /// Checks end

    /// Get private key for signing
    $key = "";
    $stmt = $dbConnection->prepare('SELECT field_promoter_private_key_value FROM field_data_field_promoter_private_key WHERE `entity_id` = ?');
    $data = $stmt->execute([$uid]);
    $key = $data[0]['field_promoter_private_key_value'];

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
                                        WHERE cp.sku = ?');
        $data = $stmt->execute([$ticket->sku]);
        for ($i = 0; $i < $ticket->stock; $i++) {
            $temp = array(
                'hash' => base64_encode(openssl_random_pseudo_bytes(16)),
                'sku' => $data[0]['sku'],
                'product_id' => $data[0]['product_id'],
                'price' => $data[0]['commerce_price_amount'],
                'event_id' => $data[0]['field_event_id_value']
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
    $stmt = $dbConnection->prepare('INSERT INTO order_unique_hash (`hash`) VALUES (?)');
    //$data = $stmt->execute([$body->order_hash]);

    $payload = array(array('request_body' => $body, 'tickets_arr' => $tickets_arr));
    $stmt = $dbConnection->prepare('INSERT INTO queue (`name`, `data`, `expire`, `created`) VALUES ("tickets_order_generator_queue", ?, 0, ?)');
    $stmt->execute([serialize($payload), time()]);
}

$http->on("request", "onRequest");
$http->start();

?>