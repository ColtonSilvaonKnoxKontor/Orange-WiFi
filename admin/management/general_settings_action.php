<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems General Settings Handler
 */
require_once __DIR__ . '/../critical/gatekeeper.php';

$confFile = '/home/pi/orange-wifi/conf/general_settings.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendoName = trim($_POST['vendo_name'] ?? 'SILVASYSTEMS');
    $customMsg = trim($_POST['custom_msg'] ?? '');
    
    if (empty($vendoName)) {
        $vendoName = "SILVASYSTEMS";
    }

    // Authoritative Data Structure
    $data = [
        'vendo_name' => $vendoName,
        'custom_msg' => $customMsg
    ];

    // Physically persist to system configuration
    file_put_contents($confFile, json_encode($data, JSON_PRETTY_PRINT));

    // Restore finalized permissions
    chmod($confFile, 0644);
    
    header('Location: general_settings.php?success=1');
    exit;
}

header('Location: general_settings.php');
?>