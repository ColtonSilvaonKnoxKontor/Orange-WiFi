<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Rates API
 */
ob_start();
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/database.php';
ob_end_clean();

$context = json_decode(base64_decode($argv[3] ?? ""), true);
$method = $context['method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = $context['input'] ?? file_get_contents('php://input');

$db = new Database();
$publicFile = '/home/pi/orange-wifi/rates_list.json';
$settingsFile = '/home/pi/orange-wifi/conf/rates_settings.json';

if ($method === 'GET') {
    header('Content-Type: application/json');
    $settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : ['display' => 'data'];
    echo json_encode([
        'settings' => $settings,
        'rates' => $db->get_rates()
    ]);
    exit;
}

if ($method === 'PUT') {
    $data = json_decode($input, true);
    if (!is_array($data)) { http_response_code(400); echo "ERROR"; exit; }

    $new_rates = [];
    $settings = ['display' => 'data'];

    // If payload contains settings and rates (new format)
    if (isset($data['rates']) && isset($data['settings'])) {
        $settings = $data['settings'];
        foreach ($data['rates'] as $entry) {
            $parts = explode(':', $entry);
            if (count($parts) >= 3) {
                $amt = intval($parts[0]);
                $mb  = intval($parts[1]);
                $time = trim($parts[2]);
                if ($amt > 0) $new_rates[$amt] = ['mb' => $mb, 'time' => $time];
            }
        }
    } else {
        // Fallback for old format
        foreach ($data as $entry) {
            if (strpos($entry, ':') !== false) {
                list($amt, $mb) = explode(':', $entry);
                if (intval($amt) > 0 && intval($mb) > 0) $new_rates[intval($amt)] = ['mb' => intval($mb), 'time' => '0m'];
            }
        }
    }

    if (count($new_rates) > 0) {
        // 1. Save Settings
        file_put_contents($settingsFile, json_encode($settings));
        
        // 2. Save to Secure Database
        $db->set_rates($new_rates);
        
        // 3. Create Public Mirror for Portal
        $publicData = ['settings' => $settings, 'rates' => $db->get_rates()];
        file_put_contents($publicFile, json_encode($publicData));
        chmod($publicFile, 0644);
        
        echo "SUCCESS";
    } else {
        http_response_code(400); echo "ERROR";
    }
    exit;
}
?>