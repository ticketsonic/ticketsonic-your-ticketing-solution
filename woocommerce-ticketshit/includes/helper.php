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

function currency_to_ascii($currency_code) {
    $currencies = array(
        'BGN' => 'BGN',
        'USD' => chr(36),
        'EUR' => chr(128),
        'GBP' => chr(163)
    );

    return $currencies[$currency_code];
}

?>
