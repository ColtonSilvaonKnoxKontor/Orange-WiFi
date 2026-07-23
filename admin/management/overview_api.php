<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Unified Overview API
 * Merged stable logic for Android performance
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/autoload.php';

$db = new Database();

// 1. EARNINGS LOGIC (Mirroring earnings_summary.php)
$sum = [];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') = DATE('now','+8 hours')");
$sum['day'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') = DATE('now','+8 hours','-1 day');");
$sum['last_day'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','weekday 0','-7 days')");
$sum['week'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','start of month')");
$sum['month'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','start of year')");
$sum['year'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') BETWEEN DATE('now','+8 hours','start of year','-1 year') AND DATE('now','start of year','-1 day')");
$sum['last_year'] = $q->fetchArray(SQLITE3_NUM)[0];

// 2. ACTIVE USERS (Mirroring x.php?dev=active)
$active_count = count($db->get_active_devices());

// 3. SYSTEM STATS (Mirroring sys_stats.php V3 logic)
function get_cpu_stats() {
    $stats = [];
    if (file_exists('/proc/stat')) {
        $lines = file('/proc/stat');
        foreach ($lines as $line) {
            if (preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $line, $m)) {
                return [
                    'total' => (float)$m[1] + (float)$m[2] + (float)$m[3] + (float)$m[4] + (float)$m[5] + (float)$m[6] + (float)$m[7],
                    'idle' => (float)$m[4] + (float)$m[5]
                ];
            }
        }
    }
    return ['total' => 0, 'idle' => 0];
}

$s1 = get_cpu_stats();
usleep(200000); 
$s2 = get_cpu_stats();
$totalDiff = $s2['total'] - $s1['total'];
$idleDiff = $s2['idle'] - $s1['idle'];
$cpu_usage = ($totalDiff > 0) ? round(100 * ($totalDiff - $idleDiff) / $totalDiff, 1) : 0;

$meminfo = file('/proc/meminfo', FILE_SKIP_EMPTY_LINES);
$mem = [];
foreach ($meminfo as $line) {
    $parts = explode(':', $line);
    if (count($parts) < 2) continue;
    $mem[trim($parts[0])] = (float)trim(str_replace('kB', '', $parts[1]));
}
$ram_total = $mem['MemTotal'] / 1024;
$ram_used = $ram_total - ($mem['MemAvailable'] / 1024);

$disk_total = disk_total_space("/");
$disk_free = disk_free_space("/");

header('Content-Type: application/json');
echo json_encode([
    'earnings' => $sum,
    'users' => $active_count,
    'cpu' => ['usage' => $cpu_usage, 'temp' => System::cpu_temp()],
    'ram' => ['total' => $ram_total, 'used' => $ram_used],
    'disk' => ['total' => $disk_total, 'used' => $disk_total - $disk_free],
    'uptime' => System::uptime()
]);
?>