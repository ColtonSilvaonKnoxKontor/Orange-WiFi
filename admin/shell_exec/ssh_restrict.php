<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure SSH Restriction Logic
 */

$configFile = '/home/pi/orange-wifi/conf/ssh_config.json';
$logFile = '/home/pi/orange-wifi/conf/ssh_restrict_debug.log';

if (!file_exists($configFile)) {
    @mkdir(dirname($configFile), 0755, true);
    file_put_contents($configFile, json_encode(['status' => 'ENABLED', 'whitelist' => []]));
}

// 1. Robust Payload Extraction
$encodedContext = $argv[3] ?? $argv[4] ?? '';
$action = 'get';
$input = [];

if (!empty($encodedContext)) {
    $context = json_decode(base64_decode($encodedContext), true);
    if ($context) {
        $input = json_decode($context['input'] ?? '{}', true);
        $action = $input['action'] ?? $context['get']['action'] ?? 'get';
    }
}

$config = json_decode(file_get_contents($configFile), true);

if ($action === 'get') {
    echo json_encode($config);
    exit;
}

if ($action === 'set_status') {
    $config['status'] = $input['status'] ?? 'ENABLED';
    saveAndApply($config, $configFile, $logFile);
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
        saveAndApply($config, $configFile, $logFile);
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
        saveAndApply($config, $configFile, $logFile);
    }
    echo json_encode(['status' => 'SUCCESS', 'whitelist' => $config['whitelist']]);
    exit;
}

function saveAndApply($config, $file, $debugLog) {
    file_put_contents($file, json_encode($config));
    
    // Authoritative iptables manipulation (Real Root UID 0)
    shell_exec("/sbin/iptables -N SSH_GATE 2>/dev/null");
    shell_exec("/sbin/iptables -F SSH_GATE");
    shell_exec("/sbin/iptables -C INPUT -p tcp --dport 22 -j SSH_GATE 2>/dev/null || /sbin/iptables -A INPUT -p tcp --dport 22 -j SSH_GATE");
    shell_exec("/sbin/iptables -A SSH_GATE -m state --state RELATED,ESTABLISHED -j ACCEPT");

    if ($config['status'] === 'ENABLED') {
        shell_exec("/sbin/iptables -A SSH_GATE -j ACCEPT");
    } else {
        foreach ($config['whitelist'] as $m) {
            $m = trim($m);
            if (!empty($m)) {
                shell_exec("/sbin/iptables -A SSH_GATE -m mac --mac-source " . escapeshellarg($m) . " -j ACCEPT");
            }
        }
        shell_exec("/sbin/iptables -A SSH_GATE -j DROP");
    }
    shell_exec("/sbin/iptables-save > /home/pi/orange-wifi/conf/iptables.txt");
    file_put_contents($debugLog, "Applied Rules: " . $config['status'] . " at " . date('Y-m-d H:i:s'));
}
?>