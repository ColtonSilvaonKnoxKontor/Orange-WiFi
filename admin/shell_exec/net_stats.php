<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Network Stats API
 * Handles midnight reset and manual offsets
 */

$offsetFile = '/home/pi/orange-wifi/conf/net_offsets.json';
$raw = [];

// 1. Read Raw Kernel Stats
if (file_exists('/proc/net/dev')) {
    $lines = file('/proc/net/dev');
    foreach ($lines as $line) {
        if (preg_match('/^\s*(eth0|eth1):\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $m)) {
            $raw[$m[1]] = [
                'rx' => (float)$m[2],
                'tx' => (float)$m[3]
            ];
        }
    }
}

// 2. Load Offsets
$data = ['last_reset' => date('Y-m-d'), 'offsets' => []];
if (file_exists($offsetFile)) {
    $data = json_decode(file_get_contents($offsetFile), true);
}

// 3. MIDNIGHT RESET: If the date has changed, update offsets to current raw values
if ($data['last_reset'] !== date('Y-m-d')) {
    $data = [
        'last_reset' => date('Y-m-d'),
        'offsets' => $raw
    ];
    file_put_contents($offsetFile, json_encode($data));
}

// 4. Calculate Visible Stats (Current - Offset)
$visible = [];
foreach ($raw as $iface => $vals) {
    $offRx = $data['offsets'][$iface]['rx'] ?? 0;
    $offTx = $data['offsets'][$iface]['tx'] ?? 0;

    // Handle Hardware Reboot: If Raw < Offset, kernel reset happened, so we reset our offset
    if ($vals['rx'] < $offRx || $vals['tx'] < $offTx) {
        $data['offsets'][$iface] = ['rx' => 0, 'tx' => 0];
        file_put_contents($offsetFile, json_encode($data));
        $offRx = 0; $offTx = 0;
    }

    $visible[$iface] = [
        'rx' => $vals['rx'] - $offRx,
        'tx' => $vals['tx'] - $offTx
    ];
}

header('Content-Type: application/json');
echo json_encode($visible);
?>