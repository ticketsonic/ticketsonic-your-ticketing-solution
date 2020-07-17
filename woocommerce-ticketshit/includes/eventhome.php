<?php
require( dirname( __FILE__ ) . '/../vendor/autoload.php');
use GuzzleHttp\Client;

class EventHome {
    private static $http;

    public static function get_ticket_data($url, $email, $key) {
        $response = self::fetch_ticket_data($url, $email, $key);

        if ($response['status'] !== "success")
            return null;

        return $response;
    }

    private static function fetch_ticket_data($url, $email, $key) {
        self::$http = new GuzzleHttp\Client(['base_uri' => $url]);
        $response = self::$http->request('GET', '/v1', [
            'query' => [
                'promoter_email' => $email,
                'promoter_api_key' => $key
            ]
        ]);
    
        $response = json_decode($response->getBody(), true);
        return $response;
    }

    public static function currency_to_ascii($currency_code) {
        $currencies = array(
            'BGN' => 'BGN',
            'USD' => chr(36),
            'EUR' => chr(128),
            'GBP' => chr(163)
        );
    
        return $currencies[$currency_code];
    }
}