<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Data & Time Sync API
 */
require '../lib/autoload.php';

$IP = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
$db = new Database();
$db->set_ip($IP);

if( !$db->get_device_id_by_ip() ) {
  http_response_code(401);
  exit;
}

$MAC = $db->get_device_mac();
$did = $db->get_did();
$ipt = new Iptables($IP);

// 1. Get Balance
list($mb_limit, $mb_used, $time_limit, $time_used) = $db->get_data_usage();
$data_rem = max(0, floatval($mb_limit) - floatval($mb_used));
$time_rem = max(0, intval($time_limit) - intval($time_used));

// 2. STRICT FRESH-SESSION LOGIC
// We use strftime to compare the DB date string with current Unix time
$threshold = time() - 120; // 2 minutes
$q = $db->query("SELECT id FROM session WHERE device_id=$did AND strftime('%s', created_at) > $threshold LIMIT 1");
$is_fresh_session = ($q->fetchArray() !== false);

// Only Auto-Connect if the session is BRAND NEW and has balance
if (($data_rem > 0 || $time_rem > 0) && $is_fresh_session && !$ipt->connected()) {
    $ipt->add_client();
}

$status = ($ipt->connected()) ? 'Connected' : 'Disconnected';

echo json_encode([
    'ip' => $IP, 
    'mac' => $MAC, 
    'status' => $status,
    'data_remaining' => $data_rem,
    'time_remaining' => $time_rem,
    'mb_limit' => $mb_limit,
    'time_limit' => $time_limit
]);