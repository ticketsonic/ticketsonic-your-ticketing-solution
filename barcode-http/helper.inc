<?php
function is_null_or_empty_string($str){
  return (!isset($str) || trim($str) === '');
}

function get_or_false(&$arr1, &$result1) {
  if (isset($arr1)) {
    $result1 = $arr1;
    return true;
  }
  return false;
}

function get_ticket_contents_for_encryption($ticket) {  
  $sensitive_ticket_data_arr = array();
  if (isset($ticket['hash']))
    $sensitive_ticket_data_arr['barcode'] = base64_decode($ticket['hash']);

  if (isset($ticket['event_id']))
    $sensitive_ticket_data_arr['event_id'] = $ticket['event_id'];

  if (isset($ticket['price']))
    $sensitive_ticket_data_arr['price'] = $ticket['price'];

  if (isset($ticket['segment1']))
    $sensitive_ticket_data_arr['segment1'] = $ticket['segment1'];

  if (isset($ticket['segment2']))
    $sensitive_ticket_data_arr['segment2'] = $ticket['segment2'];

  if (isset($ticket['segment3']))
    $sensitive_ticket_data_arr['segment3'] = $ticket['segment3'];

  if (isset($ticket['segment4']))
    $sensitive_ticket_data_arr['segment4'] = $ticket['segment4'];

  // Extensions go here
  // $arr['extension.isvip'] = "1";

  return format_ticket_contents_for_encryption($sensitive_ticket_data_arr);
}

function format_ticket_contents_for_encryption($sensitive_ticket_data_arr) {
  /*
    V LL 00, версия на формата LL байта (задължително първо поле), LL=1
    H LL 00000000000000000000000000000000, Уникален номер, LL = 16
    $ LL 0000, цена, LL=var (variable size)
    E LL 0000, събитие, LL=var (variable size)
    S LL 0000, сектор, LL=var (variable size)
    B LL 0000, блок, LL=var (variable size)
    R LL 0000, ред, LL=var (variable size)
    P LL 0000, колона/място, LL=var (variable size)
    T LL 000000000000, валиден до, LL=6
    X LL 00...00, разширение (key=value текст)
    C LL 0000, контролна сума само за вътрешните данни, LL=2
  */

  $result_bytes = "";

  // !!! V - version must be first
  $result_bytes .= "V" . chr(1) . chr(1);

  if (get_or_false($sensitive_ticket_data_arr['barcode'], $v))
    $result_bytes .= "H" . chr(strlen($v)) . $v;

  if (get_or_false($sensitive_ticket_data_arr['price'], $v))
    $result_bytes .= "$" . get_field_int_data($v);

  if (get_or_false($sensitive_ticket_data_arr['event_id'], $v))
    $result_bytes .= "E" . get_field_int_data($v);

  if (get_or_false($sensitive_ticket_data_arr['segment1'], $v))
    $result_bytes .= "S" . get_field_int_data($v);
  
  if (get_or_false($sensitive_ticket_data_arr['segment2'], $v))
    $result_bytes .= "B" . get_field_int_data($v);

  if (get_or_false($sensitive_ticket_data_arr['segment3'], $v))
    $result_bytes .= "R" . get_field_int_data($v);

  if (get_or_false($sensitive_ticket_data_arr['segment4'], $v))
    $result_bytes .= "P" . get_field_int_data($v);

  foreach ($sensitive_ticket_data_arr as $key => $value) {
    if (strpos($key, 'extension.') === 0) {
      $v = $value;
      if (!is_null_or_empty_string($v)) {
        $extension = substr($key, 10) . "=" . $v;
        $result_bytes .= "X" . chr(strlen($extension)) . $extension;
      }      
    }
  }
  
  // !!! C - checksum must be last
  $c = 0;
  for ($i = 0; $i < strlen($result_bytes); $i++) {
    $c = ($c + ord($result_bytes[$i])) & 0xFFFF;
  }
  $result_bytes .= "C" . get_field_int_data($c);
  
  return $result_bytes;
}

function get_field_int_data($val) {
  $v = intval($val);
  $res = "";
  do {
    $res .= chr($v & 0xFF);
    $v >>= 8;
  } while ($v != 0);

  return chr(strlen($res)) . $res;
}

?>