<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Voucher API

 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/autoload.php';

// 1. Context Recovery
$data = json_decode(base64_decode($argv[3] ?? ""), true);
$get = $data['get'] ?? $_GET;
$method = $data['method'] ?? $_SERVER['REQUEST_METHOD'];
$rawBody = $data['input'] ?? file_get_contents('php://input');
$input = json_decode($rawBody, true);

$action = $get['action'] ?? $input['action'] ?? $input['post']['action'] ?? 'list';
$db = new Database();

if ($action === 'generate') {
    $params = $input['post'] ?? $input;
    $prefix = $params['prefix'] ?? '';
    $type = $params['type'] ?? 'mixed';
    $price = intval($params['price'] ?? 0);
    $total_mins = intval($params['mins'] ?? 0) + (intval($params['hrs'] ?? 0) * 60) + (intval($params['days'] ?? 0) * 1440);
    $data_limit = intval($params['data_limit'] ?? 0);
    $count = intval($params['count'] ?? 1);
    if ($count > 100) $count = 100;
    
    $generated = [];
    for ($i = 0; $i < $count; $i++) {
        $code = generate_code($prefix, $type);
        $stmt = $db->prepare("INSERT INTO vouchers (code, prefix, price, duration_min, data_limit_mb) VALUES (:code, :prefix, :price, :dur, :data)");
        $stmt->bindValue(':code', $code, SQLITE3_TEXT);
        $stmt->bindValue(':prefix', $prefix, SQLITE3_TEXT);
        $stmt->bindValue(':price', $price, SQLITE3_INTEGER);
        $stmt->bindValue(':dur', $total_mins, SQLITE3_INTEGER);
        $stmt->bindValue(':data', $data_limit, SQLITE3_INTEGER);
        if ($stmt->execute()) { $generated[] = $code; }
    }
    echo json_encode(['status' => 'SUCCESS', 'codes' => $generated]);
    exit;
}

if ($action === 'delete') {
    $id = intval($get['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM vouchers WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        if ($stmt->execute()) { echo json_encode(['status' => 'SUCCESS']); exit; }
    }
    echo json_encode(['status' => 'ERROR']);
    exit;
}

if ($action === 'clear_all') {
    if ($db->exec("DELETE FROM vouchers")) {
        echo json_encode(['status' => 'SUCCESS']);
    } else {
        echo json_encode(['status' => 'ERROR']);
    }
    exit;
}

if ($action === 'list') {
    $q = $db->query("SELECT * FROM vouchers ORDER BY id DESC LIMIT 200");
    $list = [];
    while($row = $q->fetchArray(SQLITE3_ASSOC)) { $list[] = $row; }
    echo json_encode($list);
    exit;
}

function generate_code($prefix, $type) {
    $chars = ($type === 'letters') ? 'ABCDEFGHJKLMNPQRSTUVWXYZ' : 
             (($type === 'numbers') ? '23456789' : 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789');
    $code = '';
    for ($i = 0; $i < 8; $i++) { $code .= $chars[rand(0, strlen($chars) - 1)]; }
    return $prefix . $code;
}
?>