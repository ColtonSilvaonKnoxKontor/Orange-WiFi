<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems DoS Defense Authoritative API
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$confFile = '/home/pi/orange-wifi/conf/dos_settings.json';
$http_file = "/etc/nginx/conf.d/http_limit.conf";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'get') {
        // 1. Precision Physical Check
        $syn = trim(shell_exec("sysctl -n net.ipv4.tcp_syncookies"));
        
        // Only count as ACTIVE if the specific DROP rules exist
        $http_check = shell_exec("sudo iptables -L INPUT -n | grep 'DROP' | grep 'HTTP_FIXED'");
        $http = ($http_check) ? "1" : "0";
        
        $icmp_check = shell_exec("sudo iptables -L INPUT -n | grep 'DROP' | grep 'icmp'");
        $icmp = ($icmp_check) ? "1" : "0";
        
        $keepalive = "5";
        if (file_exists($http_file)) {
            $content = file_get_contents($http_file);
            if (preg_match('/keepalive_requests (\d+);/', $content, $m)) $keepalive = $m[1];
        }

        echo json_encode([
            'syn' => $syn,
            'http' => $http,
            'icmp' => $icmp,
            'keepalive' => $keepalive
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'toggle') {
        $type = $input['type'] ?? '';
        $state = ($input['state'] === '1') ? '1' : '0';
        $ka = $input['keepalive'] ?? '5';
        
        // Physical Force Execution
        $output = shell_exec(sprintf("/usr/bin/silvasystems %s %s %s", 
            escapeshellarg($type), 
            escapeshellarg($state),
            escapeshellarg($ka)
        ));

        // Registry Sync
        $settings = file_exists($confFile) ? json_decode(file_get_contents($confFile), true) : [];
        $settings[$type] = $state;
        if ($type === 'http_flood') $settings['keepalive'] = $ka;
        file_put_contents($confFile, json_encode($settings));

        echo json_encode(['status' => 'SUCCESS', 'msg' => trim($output)]);
        exit;
    }

    if ($action === 'update_keepalive') {
        $val = (int)($input['val'] ?? 5);
        if ($val < 1) $val = 1;
        shell_exec(sprintf("/usr/bin/silvasystems http_flood 1 %d", $val));
        
        $settings = file_exists($confFile) ? json_decode(file_get_contents($confFile), true) : [];
        $settings['keepalive'] = (string)$val;
        $settings['http'] = '1';
        file_put_contents($confFile, json_encode($settings));

        echo json_encode(['status' => 'SUCCESS', 'msg' => "Threshold physically updated to $val."]);
        exit;
    }
}

echo json_encode(['status' => 'ERROR', 'msg' => 'Invalid request.']);
?>