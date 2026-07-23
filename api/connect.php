<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Connection Handler
 */
if( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
  http_response_code(403);
  exit;
}

require '../lib/autoload.php';

$IP = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
$db = new Database();
$db->set_ip($IP);

if( !$db->get_device_id_by_ip() ) {
  http_response_code(401);
  exit;
}

list($mb_limit, $mb_used, $time_limit, $time_used) = $db->get_data_usage();

$has_data = ($mb_limit > 0 && ($mb_limit > $mb_used));
$has_time = ($time_limit > 0 && ($time_limit > $time_used));

if (!$has_data && !$has_time) {
    http_response_code(403);
    echo "NO_BALANCE";
    exit;
}

$ipt = new Iptables($IP);
$ipt->add_client();

echo "CONNECTED";
?>