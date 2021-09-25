<?php

use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Common\ErrorCorrectionLevel;

function parse_raw_recrypted_ticket($raw_decrypted_ticket) {
    $result = array();
    $checksum = 0;
    $checksumpos = 0;
    $i = 0;
    try {
        while ($i < strlen($raw_decrypted_ticket)) {
            $label = $raw_decrypted_ticket[$i++];
            $len = ord($raw_decrypted_ticket[$i++]);
            switch ($label) {
                case "V":
                    $result["version"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "H":
                    $result["barcode"] = substr($raw_decrypted_ticket, $i, $len);
                    break;

                case "$":
                    $result["price"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "I":
                    $result["product_id"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "E":
                    $result["event_id"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "S":
                    $result["segment1"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "B":
                    $result["segment2"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "R":
                    $result["segment3"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "P":
                    $result["segment4"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "T":
                    $result["start_time"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "F":
                    $result["end_time"] = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;

                case "X":
                    $temp = substr($raw_decrypted_ticket, $i, $len);
                    $delimiter_pos = strpos($temp, "=");

                    $result["extension." . substr($temp, 0, $delimiter_pos)] = substr($temp, $delimiter_pos + 1);
                    break;

                case "C":
                    $checksumpos = $i - 2;
                    $checksum = bin_to_int_data(substr($raw_decrypted_ticket, $i, $len));
                    break;
                
                default:
                    $result[$label] = substr($raw_decrypted_ticket, $i, $len);
                    $result["warning"] = "Unrecognized label";
                    break;
            }
            $i += $len;
        }
    } catch (Exception $e) {
        $result["error"] = 1;
    }

    if ($checksumpos > 0) {
        $c = 0;
        for ($i = 0; $i < $checksumpos; $i++) {
            $c = ($c + ord($raw_decrypted_ticket[$i])) & 0xFFFF;
        }
        
        if ($c != $checksum) {
            $result["error"] = 1;
        }
    }

    return $result;
}

function bin_to_int_data($str) {
    $result = 0;
    for ($i = strlen($str) - 1; $i >= 0; $i--) {
        $result = ($result << 8) | ord($str[$i]);
    }

    return $result;
}

function qr_binary_to_binary($raw_input) {
    $qrCode = Encoder::encode($raw_input, ErrorCorrectionLevel::L(), Encoder::DEFAULT_BYTE_MODE_ECODING);
    $matrix = $qrCode->getMatrix();
    $rows = $matrix->getArray()->toArray();
    return $rows;
}

function bin_to_int_array($str) {
    $result = array();
    for ($i = strlen($str) - 1; $i >= 0; $i--) {
        $result[] = ord($str[$i]);
    }

    return $result;
}

function int_array_to_bin($str) {
    $result = array();
    for ($i = count($str) - 1; $i >= 0; $i--) {
        $result[] = chr($str[$i]);
    }

    return implode("", $result);
}

?>