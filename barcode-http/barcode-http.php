<?php
error_reporting(E_ALL);

if (file_exists(dirname(__FILE__) . "/settings.local.php")) {
  require(dirname(__FILE__) . "/settings.local.php");
}
else {
  exit("Missing settings.local.php");
}

class Pool {
    public const MAX_CONNECTIONS = 5;//this is multiplied by the number of workers which is 12 cpu cores * 2 = 24 (so there are 24 * 5 = 120 connections)
    protected $available_connections = [];
    public function get_connection(string $connection_class) : ConnectionInterface {
        if (!array_key_exists($connection_class, $this->available_connections)) {
            $this->initialize_connections($connection_class);
        }
        
        $Connection = $this->available_connections[$connection_class]->pop();//blocks and waits until one is available if there are no available ones

        return $Connection;
    }

    public function free_connection(ConnectionInterface $Connection) : void {
        $connection_class = get_class($Connection);
        $this->available_connections[$connection_class]->push($Connection);
    }
    
    private function initialize_connections(string $connection_class) : void {
        $this->available_connections[$connection_class] = new \Swoole\Coroutine\Channel(self::MAX_CONNECTIONS);
        for ($aa = 0; $aa < self::MAX_CONNECTIONS ; $aa++) {
            $Connection = new $connection_class();
            $this->available_connections[$connection_class]->push($Connection);
        }
    }
    
}

interface ConnectionInterface { }

class MysqlConnection implements ConnectionInterface {
    private const CONNECTION_SETTINGS = [
        'host' => HOST,
        'port' => PORT,
        'user' => USER,
        'password' => PASSWORD,
        'database' => DB,
    ];
    
    private $SwooleMysql;
    
    public function __construct() {
        $this->SwooleMysql = new Swoole\Coroutine\MySQL();
        $this->SwooleMysql->connect(self::CONNECTION_SETTINGS);
    }
    
    public function __call(string $method, array $args) {
        return call_user_func_array([$this->SwooleMysql, $method], $args);
    }
    
    public function __get(string $property) {
        return $this->SwooleMysql->{$property};
    }
}

include_once 'helper.inc';
$http = new Swoole\HTTP\Server(HTTP_SERVER_IP, HTTP_SERVER_PORT, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$http->set([
    'worker_num' => swoole_cpu_num() * 2,
    //'log_file' => 'swoole.log',
    'ssl_cert_file' => dirname(__FILE__) . '/ssl.crt',
    'ssl_key_file' => dirname(__FILE__) . '/ssl.key',
  ]);

$http->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    static $Pool;
    if ($Pool === NULL) {
        $Pool = new Pool;
    }
    
    $dbConnection = $Pool->get_connection(MysqlConnection::class);

    $body = json_decode($request->rawcontent());

    // Validate order hash if not in debug mode
    if (DEBUG == FALSE) {
        $stmt = $dbConnection->prepare('SELECT * FROM order_unique_hash WHERE `hash` = ?');
        $rows = $stmt->execute([$body->order_hash]);
        if (!empty($rows)) {
            $response->header("Content-Type", "text/plain");
            $response->end("Duplicated order");
            return;
        }
    }

    // Validate promoter email
    $stmt = $dbConnection->prepare('SELECT u.uid FROM users u
                                    LEFT JOIN users_roles ur ON u.uid = ur.uid
                                    LEFT JOIN role r ON ur.rid = r.rid
                                    WHERE `mail` = ? AND r.name = "promoter"');
    $data = $stmt->execute([$body->promoter_email]);
    if (empty($data)) {
        $response->end("Unknown promoter");
        $Pool->free_connection($dbConnection);
        return;
    }
    $uid = '';
    foreach ($data as $row) {
        $uid = $row['uid'];
    }

    // Validate promoter api key
    $stmt = $dbConnection->prepare('SELECT field_promoter_api_key_value FROM field_data_field_promoter_api_key WHERE `field_promoter_api_key_value` = ?');
    $data = $stmt->execute([$body->promoter_api_key]);
    //$rows = $stmt->rowCount();
    if (empty($data)) {
        $response->end("Unknown promoter key");
        $Pool->free_connection($dbConnection);
        return;
    }
    // Sufficient stock
    $ticket_data = array();
    // Not using where in because there were problems with paramterizing where in and moved on
    foreach($body->tickets as $key => $ticket) {
        $stmt1 = $dbConnection->prepare('SELECT * FROM commerce_product cp LEFT JOIN field_data_commerce_stock fdcs ON cp.product_id = fdcs.entity_id WHERE cp.sku = ?');
        $data = $stmt1->execute([$ticket->sku]);
        //$rows = $stmt->rowCount();
        //$data = $stmt->fetch();
        if (empty($data)) {
            $response->end("Unknown promoter sku: " . $ticket->sku . "");
            $Pool->free_connection($dbConnection);
            return;
        }

        if ($data[0]['commerce_stock_value'] < $ticket->stock) {
            $response->end("Insuffiecint stock: " . $ticket->stock . " for sku: " . $ticket->sku);
            $Pool->free_connection($dbConnection);
            return;
        }
    }
    /// Checks end

    /// Get private key for signing
    $key = "";
    $stmt2 = $dbConnection->prepare('SELECT field_promoter_private_key_value FROM field_data_field_promoter_private_key WHERE `entity_id` = ?');
    $data = $stmt2->execute([$uid]);
    //$data = $stmt->fetch();
    $key = $data[0]['field_promoter_private_key_value'];

    $privKey = "-----BEGIN PRIVATE KEY-----\n";
    $privKey .= $key;
    $privKey .= "\n-----END PRIVATE KEY-----\n";

    // Get all relevant data
    $tickets_arr = array();
    foreach($body->tickets as $key => $ticket) {
        $stmt3 = $dbConnection->prepare('SELECT cp.sku, cp.product_id, fdcp.commerce_price_amount, fdfei.field_event_id_value
                                        FROM commerce_product cp 
                                        LEFT JOIN field_data_commerce_stock fdcs ON cp.product_id = fdcs.entity_id
                                        LEFT JOIN field_data_commerce_price fdcp ON cp.product_id = fdcp.entity_id
                                        LEFT JOIN field_data_field_event_id fdfei ON cp.product_id = fdfei.entity_id
                                        WHERE cp.sku = ?');
        $data = $stmt3->execute([$ticket->sku]);
        //$data = $stmt->fetch();
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
                $Pool->free_connection($dbConnection);
                return;
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

    // Insert order hash if not in debug
    if (DEBUG == FALSE) {
        $stmt = $dbConnection->prepare('INSERT INTO order_unique_hash (`hash`) VALUES (?)');
        $stmt->execute([$body->order_hash]);
    }

    $payload = array(array('request_body' => $body, 'tickets_arr' => $tickets_arr));
    $stmt4 = $dbConnection->prepare('INSERT INTO queue (`name`, `data`, `expire`, `created`) VALUES ("tickets_order_generator_queue", ?, 0, ?)');
    $stmt4->execute(
        [
            serialize($payload),
            time()
        ]);
    $Pool->free_connection($dbConnection);
});
$http->start();

?>