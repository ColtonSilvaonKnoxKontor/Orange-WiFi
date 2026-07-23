<?php
header('Content-Type: application/json');
require '../lib/autoload.php';

$ip = $_SERVER['REMOTE_ADDR'];
$mac = 'Unknown';

// Parse /proc/net/arp for better reliability
$arp = file_get_contents('/proc/net/arp');
$lines = explode("\n", $arp);
foreach ($lines as $line) {
    $cols = preg_split('/\s+/', $line);
    // Col 0 = IP, Col 3 = MAC
    if (count($cols) > 3 && $cols[0] === $ip) {
        $mac = $cols[3];
        break;
    }
}

echo json_encode(['ip' => $ip, 'mac' => $mac]);
?>