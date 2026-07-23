<?php // [!] By Colton Silva (chinawaterstealers).
require_once __DIR__ . '/../critical/gatekeeper.php';

$confFile = '/home/pi/orange-wifi/conf/site_blocker.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $active = ($_POST['porn_blocker'] ?? '0') === '1';
    $list = basename($_POST['selected_list'] ?? 'lite.txt');

    $config = [
        'porn_blocker' => $active,
        'selected_list' => $list
    ];

    file_put_contents($confFile, json_encode($config, JSON_PRETTY_PRINT));
    chmod($confFile, 0644);

    // Trigger system-level block application via silvasystems
    $context = base64_encode(json_encode(["action" => "apply"]));
    shell_exec("/usr/bin/silvasystems site_blocker_logic " . escapeshellarg($context));

    echo json_encode(['status' => 'OK']);
    exit;
}
?>