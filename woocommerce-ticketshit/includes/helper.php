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

function upload_custom_ticket_logo() {
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
    return 'pdf_logo' . $ext;
}
?>
