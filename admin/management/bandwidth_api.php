<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Bandwidth Management API
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';

$confFile = '/home/pi/orange-wifi/conf/bandwidth_settings.json';

// Handle GET: Retrieve current settings
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    if (file_exists($confFile)) {
        echo file_get_contents($confFile);
    } else {
        echo json_encode(['interface' => 'eth1', 'status' => 'disabled', 'down' => 0, 'up' => 0]);
    }
    exit;
}

// Handle POST: Apply and Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) die("[ERROR] Invalid payload.");

    $iface = $input['interface'] ?? 'eth1';
    $status = $input['status'] ?? 'disabled';
    $down = intval($input['down'] ?? 0);
    $up = intval($input['up'] ?? 0);

    // 1. Physically persist settings
    file_put_contents($confFile, json_encode([
        'interface' => $iface,
        'status' => $status,
        'down' => $down,
        'up' => $up
    ], JSON_PRETTY_PRINT));

    // 2. Authoritative Execution via Simple CLI Arguments
    // CMD: shaper [iface] [status] [down] [up]
    $command = sprintf("/usr/bin/silvasystems shaper %s %s %d %d", 
        escapeshellarg($iface), 
        escapeshellarg($status), 
        $down, 
        $up
    );
    
    $output = shell_exec($command);

    echo $output ?: "[SUCCESS] Bandwidth configuration physically applied.";
    exit;
}
?>