<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Voucher Redemption API
 */
require '../lib/autoload.php';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$code = strtoupper(trim($input['code'] ?? ''));

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Voucher code is required.']);
    exit;
}

$db = new Database();
$IP = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
$db->set_ip($IP);

$stmt = $db->prepare("SELECT * FROM vouchers WHERE UPPER(code) = :code AND status = 'UNUSED' LIMIT 1");
$stmt->bindValue(':code', $code, SQLITE3_TEXT);
$result = $stmt->execute();
$v = $result->fetchArray(SQLITE3_ASSOC);

if (!$v) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or already used voucher.']);
    exit;
}

$MAC = '';
if (file_exists('/proc/net/arp')) {
    $arp = file('/proc/net/arp');
    foreach ($arp as $line) {
        $cols = preg_split('/\s+/', $line);
        if (count($cols) > 3 && $cols[0] === $IP) {
            $MAC = strtoupper($cols[3]);
            break;
        }
    }
}

if (empty($MAC) || $MAC === '00:00:00:00:00:00') {
    http_response_code(500);
    echo json_encode(['error' => 'Could not resolve device identity. Please try again.']);
    exit;
}

$db->set_mac($MAC);
if (!$db->get_device_id()) {
    $db->set_host('VOUCHER-USER');
    $db->add_device(); 
}

$upd = $db->prepare("UPDATE vouchers SET status = 'USED', used_by_mac = :mac, used_at = CURRENT_TIMESTAMP WHERE id = :id");
$upd->bindValue(':mac', $MAC, SQLITE3_TEXT);
$upd->bindValue(':id', $v['id'], SQLITE3_INTEGER);
$upd->execute();

$now = date('Y-m-d H:i:s');
$piso = intval($v['price']);
$mb = intval($v['data_limit_mb']);
$min = intval($v['duration_min']);
$did = $db->get_did();

$db->exec("INSERT INTO session(device_id,piso_count,mb_limit,time_limit_min,created_at,updated_at) VALUES($did,$piso,$mb,$min,'$now','$now')");

$ipt = new Iptables($IP);
$ipt->add_client();

echo json_encode([
    'status' => 'SUCCESS',
    'msg' => 'Voucher activated successfully!',
    'data' => $mb,
    'mins' => $min
]);
?>