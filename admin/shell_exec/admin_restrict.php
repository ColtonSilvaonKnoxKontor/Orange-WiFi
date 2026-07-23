<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Admin Restriction Manager
 */

$configFile = '/home/pi/orange-wifi/conf/admin_config.json';

if (!file_exists($configFile)) {
    @mkdir(dirname($configFile), 0755, true);
    file_put_contents($configFile, json_encode(['status' => 'ENABLED', 'whitelist' => []]));
}

// 1. Robust Payload Extraction
// Supports both positional shifts in silvasystems loader
$encodedContext = $argv[3] ?? $argv[4] ?? '';
$action = 'get';
$input = [];

if (!empty($encodedContext)) {
    $context = json_decode(base64_decode($encodedContext), true);
    if ($context) {
        // Parse the JSON body sent by the frontend fetch()
        $input = json_decode($context['input'] ?? '{}', true);
        $action = $input['action'] ?? $context['get']['action'] ?? 'get';
    }
}

$config = json_decode(file_get_contents($configFile), true);

// --- CHECK ACCESS ---
if ($action === 'check_access') {
    $mac = strtoupper($input['mac'] ?? $context['get']['mac'] ?? '');
    if (($config['status'] ?? 'ENABLED') === 'ENABLED') {
        echo "ALLOWED"; exit;
    }
    if (empty($mac) || $mac === '00:00:00:00:00:00') {
        echo "DENIED"; exit;
    }
    if (in_array($mac, $config['whitelist'])) echo "ALLOWED";
    else echo "DENIED";
    exit;
}

// --- CONFIG MANAGEMENT ---
if ($action === 'get') {
    echo json_encode($config);
    exit;
}

if ($action === 'set_status') {
    $config['status'] = $input['status'] ?? 'ENABLED';
    file_put_contents($configFile, json_encode($config));
    echo json_encode(['status' => 'SUCCESS']);
    exit;
}

if ($action === 'add_mac') {
    $mac = strtoupper(trim($input['mac'] ?? ''));
    if (!filter_var($mac, FILTER_VALIDATE_MAC)) {
        echo json_encode(['status' => 'INVALID_MAC']); exit;
    }
    if (!in_array($mac, $config['whitelist'])) {
        $config['whitelist'][] = $mac;
        file_put_contents($configFile, json_encode($config));
    }
    echo json_encode(['status' => 'SUCCESS', 'whitelist' => $config['whitelist']]);
    exit;
}

if ($action === 'del_mac') {
    $mac = strtoupper(trim($input['mac'] ?? ''));
    $key = array_search($mac, $config['whitelist']);
    if ($key !== false) {
        unset($config['whitelist'][$key]);
        $config['whitelist'] = array_values($config['whitelist']);
        file_put_contents($configFile, json_encode($config));
    }
    echo json_encode(['status' => 'SUCCESS', 'whitelist' => $config['whitelist']]);
    exit;
}
?>