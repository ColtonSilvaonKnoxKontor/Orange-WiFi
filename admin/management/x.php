<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Dashboard API
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/autoload.php';

// 1. Context Recovery
$data = json_decode(base64_decode($argv[3] ?? ""), true);
$txn = $_GET['txn'] ?? $data['get']['txn'] ?? null;
$dev = $_GET['dev'] ?? $data['get']['dev'] ?? null;

if (!$dev && !$txn) {
    echo "[]"; exit;
}

try {
    $db = new Database(); 
} catch (Exception $e) {
    echo "[]"; exit;
}

// 2. Command Routing
if ($dev === 'all') {
    echo json_encode($db->get_devices(), JSON_PRETTY_PRINT);
    exit;
}

if ($dev === 'active') {
    echo json_encode($db->get_active_devices(), JSON_PRETTY_PRINT);
    exit;
}

if ($dev === 'restricted') {
    $res = $db->query("SELECT mac_addr AS mac, IFNULL(ip_addr,'-NA-') AS ip, IFNULL(hostname,'-NA-') AS host, updated_at FROM devices WHERE topup_count = -1 ORDER BY updated_at DESC");
    $list = [];
    while($row = $res->fetchArray(SQLITE3_ASSOC)) {
        // Convert timestamp using parse_ts logic from lib/database.php
        $ts = is_numeric($row['updated_at']) ? (int)$row['updated_at'] : strtotime($row['updated_at']);
        $row['active_at'] = $ts * 1000;
        $list[] = $row;
    }
    echo json_encode($list, JSON_PRETTY_PRINT);
    exit;
}

if ($dev === 'clear_all_devices') {
    $db->query("DELETE FROM devices");
    $db->query("DELETE FROM session");
    echo json_encode(['status' => 'OK']);
    exit;
}

if ($dev === 'add_session') {
    $limit = intval($_GET['limit'] ?? $data['get']['limit'] ?? 0);
    $mins = intval($_GET['mins'] ?? $data['get']['mins'] ?? 0);
    $mac = $_GET['mac'] ?? $data['get']['mac'] ?? '';
    if ((!$limit && !$mins) || empty($mac)) { echo "[]"; exit; }
    
    $db->set_mac($mac); 
    $db->set_mb_limit($limit);
    $db->set_time_limit($mins);
    $db->set_amount(0);

    if (!$db->get_device_id()) { http_response_code(403); exit; }
    $db->add_session();
    
    $ipt = new Iptables($db->get_device_ip()); 
    $ipt->add_client();
    
    echo json_encode(['status' => 'OK']);
    exit;
}

if ($dev === 'get_session') {
    $mac = $_GET['mac'] ?? $data['get']['mac'] ?? '';
    $db->set_mac($mac);
    if (!$db->get_device_id()) { http_response_code(403); exit; }
    $device = $db->get_device_info();
    
    // UPDATED: Capture all 4 elements from database logic
    list($mb_limit, $mb_used, $time_limit, $time_used) = $db->get_data_usage();
    
    $ipt = new Iptables($device['ip'] ?? '0.0.0.0');
    
    echo json_encode([
        'mac' => $device['mac'], 
        'ip' => $device['ip'], 
        'host' => $device['host'], 
        'manufacturer' => System::get_manufacturer($device['mac']),
        'restricted' => (intval($db->query("SELECT topup_count FROM devices WHERE mac_addr='{$device['mac']}'")->fetchArray(SQLITE3_NUM)[0] ?? 0) === -1),
        'mb_limit' => $mb_limit, 
        'mb_used' => $mb_used,
        'time_limit' => $time_limit,
        'time_used' => $time_used,
        'time_remaining' => max(0, $time_limit - $time_used),
        'active_at' => $db->get_active_at(), 
        'connected' => $ipt->connected()
    ]);
    exit;
}

if ($dev === 'clear_mb') {
    $mac = $_GET['mac'] ?? $data['get']['mac'] ?? '';
    $db->set_mac($mac);
    if (!$db->get_device_id()) { http_response_code(403); exit; }
    
    // Physical Disconnection: Drop firewall rules before clearing database
    $ipt = new Iptables($db->get_device_ip());
    $ipt->rem_client();
    
    $db->clear_mb();
    echo json_encode(['status' => 'OK']);
    exit;
}

if ($dev === 'del_txn') {
    $sid = intval($_GET['sid'] ?? $data['get']['sid'] ?? 0);
    $db->set_sid($sid); $db->rem_session();
    echo json_encode(['status' => 'OK']);
    exit;
}

if ($dev === 'get_txn') {
    $mac = $_GET['mac'] ?? $data['get']['mac'] ?? '';
    $db->set_mac($mac);
    if (!$db->get_device_id()) { http_response_code(403); exit; }
    echo json_encode($db->get_device_sessions(), JSON_PRETTY_PRINT);
    exit;
}

if ($txn === 'get_all') {
    $o = intval($_GET['offset'] ?? $data['get']['offset'] ?? 0);
    $l = intval($_GET['limit'] ?? $data['get']['limit'] ?? 25);
    $db->set_offset($o); $db->set_limit($l);
    echo json_encode($db->get_all_txn(), JSON_PRETTY_PRINT);
    exit;
}

echo "[]";
?>