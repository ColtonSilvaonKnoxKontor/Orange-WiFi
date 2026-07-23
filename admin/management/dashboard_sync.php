<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Unified Dashboard Sync
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/autoload.php';

$db = new Database();

// 1. Get Earnings
$sum = [];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') = DATE('now','+8 hours')");
$sum['day'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') = DATE('now','+8 hours','-1 day');");
$sum['last_day'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','start of month')");
$sum['month'] = $q->fetchArray(SQLITE3_NUM)[0];
$q = $db->query("SELECT IFNULL(SUM(piso_count),0) FROM session WHERE DATE(created_at,'+8 hours') >= DATE('now','+8 hours','start of year')");
$sum['year'] = $q->fetchArray(SQLITE3_NUM)[0];

// 2. Get Active Users
$threshold = time() - 300;
$q = $db->query("SELECT COUNT(*) FROM session WHERE updated_at > $threshold");
$active_users = $q->fetchArray(SQLITE3_NUM)[0];

// 3. Get System Info (Internal call to avoid binary overhead)
$meminfo = file('/proc/meminfo', FILE_SKIP_EMPTY_LINES);
$mem = [];
foreach ($meminfo as $line) {
    $parts = explode(':', $line);
    if (count($parts) < 2) continue;
    $mem[trim($parts[0])] = (float)trim(str_replace('kB', '', $parts[1]));
}
$ram_total = $mem['MemTotal'] / 1024;
$ram_free = $mem['MemAvailable'] / 1024;

$disk_total = disk_total_space("/");
$disk_free = disk_free_space("/");

$cpu_load = sys_getloadavg();
$cpu_temp = @floatval(file_get_contents('/sys/class/thermal/thermal_zone0/temp'))/1000;
$uptime = @exec("uptime -p");

echo json_encode([
    'earnings' => $sum,
    'users' => $active_users,
    'ram' => ['total' => $ram_total, 'used' => $ram_total - $ram_free, 'free' => $ram_free],
    'disk' => ['total' => $disk_total, 'used' => $disk_total - $disk_free, 'free' => $disk_free],
    'cpu' => ['load' => $cpu_load[0], 'temp' => $cpu_temp],
    'uptime' => $uptime
]);
?>