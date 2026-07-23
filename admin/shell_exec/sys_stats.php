<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems System Resource API

 */

function get_cpu_stats() {
    $stats = [];
    if (file_exists('/proc/stat')) {
        $lines = file('/proc/stat');
        foreach ($lines as $line) {
            // Match 'cpu0', 'cpu1', etc.
            if (preg_match('/^cpu([0-3])\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $line, $m)) {
                $stats[$m[1]] = [
                    'total' => (float)$m[2] + (float)$m[3] + (float)$m[4] + (float)$m[5] + (float)$m[6] + (float)$m[7] + (float)$m[8],
                    'idle' => (float)$m[5] + (float)$m[6]
                ];
            }
        }
    }
    return $stats;
}

function get_multi_cpu_usage() {
    $s1 = get_cpu_stats();
    usleep(200000); // 200ms sample for more stability
    $s2 = get_cpu_stats();
    
    $results = [];
    foreach ($s2 as $core => $vals) {
        $totalDiff = $vals['total'] - $s1[$core]['total'];
        $idleDiff = $vals['idle'] - $s1[$core]['idle'];
        
        if ($totalDiff == 0) {
            $results[$core] = 0;
        } else {
            $usage = 100 * ($totalDiff - $idleDiff) / $totalDiff;
            $results[$core] = round(max(0, min(100, $usage)), 1);
        }
    }
    return $results;
}

function get_ram_usage() {
    $meminfo = file('/proc/meminfo', FILE_SKIP_EMPTY_LINES);
    $mem = [];
    foreach ($meminfo as $line) {
        $parts = explode(':', $line);
        if (count($parts) < 2) continue;
        $mem[trim($parts[0])] = (float)trim(str_replace('kB', '', $parts[1]));
    }
    $total = $mem['MemTotal'] / 1024;
    $available = $mem['MemAvailable'] / 1024;
    $used = $total - $available;
    return ['total' => round($total, 1), 'used' => round($used, 1), 'free' => round($available, 1)];
}

function get_disk_usage() {
    $total = disk_total_space("/");
    $free = disk_free_space("/");
    return ['total' => $total, 'used' => $total - $free, 'free' => $free];
}

$data = [
    'cpus' => get_multi_cpu_usage(),
    'ram' => get_ram_usage(),
    'disk' => get_disk_usage()
];

header('Content-Type: application/json');
echo json_encode($data);
?>