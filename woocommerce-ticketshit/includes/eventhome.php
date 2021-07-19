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

    public function get_remote($url, $email, $key, $event_id) {
        $this->http = new GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);
        $response = array();
        try {
            $response = $this->http->request('GET', $url, [
                'headers' => [
                    'x-api-userid' => $email,
                    'x-api-key' => $key,
                    'x-api-eventid' => $event_id
                ]
            ]);
        
            $response = json_decode($response->getBody(), true);
        } catch (Exception $ex) {
            $response['status'] = 'error';
            $response['message'] = $ex->getMessage();
        }

        return $response;
    }

    public function request_tickets_in_remote($url, $data) {
        $response = $this->post_remote($url, $data);

        if ($response['status'] !== 'success')
            return null;

        return $response;
    }

    public function request_new_event_in_remote($url, $headers, $body) {
        $response = $this->post_request_to_remote($url, $headers, $body);

        return $response;
    }

    private function post_remote($url, $data) {
        $this->http = new GuzzleHttp\Client(['base_uri' => $url, 'verify' => false]);
        $response = array();
        try {
            $response = $this->http->request('POST', $url, [
                'headers' => [
                    'x-api-userid' => $data['headers']['api_userid'],
                    'x-api-key' => $data['headers']['api_key']
                ],
                'body' => json_encode($data['payload'])
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