<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Network Stats Reset
 * Sets the reference point to 'zero' out the counters
 */

$offsetFile = '/home/pi/orange-wifi/conf/net_offsets.json';
$stats = [];

if (file_exists('/proc/net/dev')) {
    $lines = file('/proc/net/dev');
    foreach ($lines as $line) {
        if (preg_match('/^\s*(eth0|eth1):\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $m)) {
            $stats[$m[1]] = [
                'rx' => (float)$m[2],
                'tx' => (float)$m[3]
            ];
        }
    }
}

$data = [
    'last_reset' => date('Y-m-d'),
    'offsets' => $stats
];

if (file_put_contents($offsetFile, json_encode($data))) {
    echo "SUCCESS";
} else {
    echo "ERROR:WRITE_FAILED";
}
?>