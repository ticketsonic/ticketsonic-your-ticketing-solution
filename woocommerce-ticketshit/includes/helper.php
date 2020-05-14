<?php
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

function woo_ts_get_option( $option = null, $default = false, $allow_empty = false ) {
	$output = '';
	if( isset( $option ) ) {
		$separator = '_';
		$output = get_option( WOO_TS_PREFIX . $separator . $option, $default );
		if( $allow_empty == false && $output != 0 && ( $output == false || $output == '' ) )
			$output = $default;
	}
	return $output;
}

function woo_ts_update_option( $option = null, $value = null ) {
	$output = false;
	if( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output = update_option( WOO_TS_PREFIX . $separator . $option, $value );
	}
	return $output;
}

/**
 * Override the default upload path.
 * 
 * @param   array   $dir
 * @return  array
 */
function wpse_141088_upload_dir( $dir ) {
    return array(
        'path'   => WOO_TS_UPLOADPATH,
        'url'    => WOO_TS_UPLOADPATH,
        'subdir' => '/' . WOO_TS_DIRNAME,
    ) + $dir;
}

function upload_custom_ticket_background() {
    add_filter( 'upload_dir', 'wpse_141088_upload_dir' );
    include_once( ABSPATH . 'wp-admin/includes/file.php' );
    include_once(ABSPATH . 'wp-admin/includes/media.php');
    
    $allowed_file_types = array('jpg' =>'image/jpeg','jpeg' =>'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
    $upload_overrides = array( 'test_form' => false, 'unique_filename_callback' => 'filename_renamer', 'mimes' => $allowed_file_types);
    $uploadedfile = $_FILES['fileToUpload'];

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    if (!$movefile || isset($movefile['error']) ) {
        woo_ts_admin_notice($movefile['error'], 'error');
    }

    remove_filter( 'upload_dir', 'wpse_141088_upload_dir' );
}

function filename_renamer($dir, $name, $ext){
    return 'pdf_background' . $ext;
}

function allowed_html() {
	return array (
		'html' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'body' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'style' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'table' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'tbody' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'th' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'tr' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'td' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'div' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'p' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
		'h1' => array (
			'href' => array(),
			'class' => array(),
			'style' => array(),
		),
	);
}

function get_customer_name($order) {
	return $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
}
?>
