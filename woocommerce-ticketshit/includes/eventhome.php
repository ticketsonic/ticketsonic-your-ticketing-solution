<?php
require( dirname( __FILE__ ) . '/../vendor/autoload.php');
use GuzzleHttp\Client;

class EventHome {
    private $http;

    public function get_request_from_remote($url, $headers, $body) {
        $this->http = new GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);
        $response = array();
        try {
            $response = $this->http->request('GET', $url, [
                'headers' => $headers,
                'body' => json_encode($body)
            ]);
        
            $response = json_decode($response->getBody(), true);
        } catch (Exception $ex) {
            $response['status'] = 'error';
            $response['message'] = $ex->getMessage();
        }

        return $response;
    }

    public function post_request_to_remote($url, $headers, $body) {
        $this->http = new GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);
        $response = array();
        try {
            $response = $this->http->request('POST', $url, [
                'headers' => $headers,
                'body' => json_encode($body)
            ]);
        
            $response = json_decode($response->getBody(), true);
        } catch (Exception $ex) {
            $response['status'] = 'error';
            $response['message'] = $ex->getMessage();
        }

        return $response;
    }

    public function currency_to_ascii($currency_code) {
        $currencies = array(
            'BGN' => 'BGN',
            'USD' => chr(36),
            'EUR' => chr(128),
            'GBP' => chr(163)
        );
    
        return $currencies[$currency_code];
    }
}