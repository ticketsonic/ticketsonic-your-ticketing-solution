<?php

function get_ticket_data_from_origin() {
	$url = woo_ts_get_option('ticket_info_endpoint', '');
	$response = wp_remote_get($url, array(
		'sslverify' => false,
		'timeout' => 10,
		'body' => array(
			'promoter_email' => woo_ts_get_option('promoter_email', ''),
			'promoter_api_key' => woo_ts_get_option('api_key', ''),
		)
	));

	return $response;
}

?>