<?php

function get_ticket_data_from_origin() {
	$url = woo_ts_get_option('ticket_info_endpoint', '');
	$response = wp_remote_post($url, array(
		'sslverify' => false,
		'method' => 'POST',
		'timeout' => 10,
		'body' => array(
			'email' => woo_ts_get_option('promoter_email', ''),
			'api_key' => woo_ts_get_option('api_key', ''),
		)
	));

	return $response;
}

?>