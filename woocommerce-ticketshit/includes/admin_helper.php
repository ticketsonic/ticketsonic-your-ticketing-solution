<?php
// Increase memory for AJAX importer process and Product Importer screens
function woo_ts_init_memory() {

    $page = $_SERVER['SCRIPT_NAME'];
    if( isset( $_POST['action'] ) )
        $action = $_POST['action'];
    elseif( isset( $_GET['action'] ) )
        $action = $_GET['action'];
    else
        $action = '';

    $allowed_actions = array( 'product_importer', 'finish_import', 'upload_image' );

    if( $page == '/wp-admin/admin-ajax.php' && in_array( $action, $allowed_actions ) )
        @ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

}
?>