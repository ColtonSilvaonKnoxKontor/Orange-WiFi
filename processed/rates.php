<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Logic Bridge
 */

// 1. Centralized Session & Restriction Check
ob_start();
// Find pass.php relative to this script
if (file_exists(dirname(__FILE__) . '/../pass.php')) {
    require_once dirname(__FILE__) . '/../pass.php';
} else {
    require_once dirname(__FILE__, 2) . '/pass.php';
}
$status = http_response_code();
ob_end_clean();

if ($status !== 200) {
    http_response_code($status);
    exit;
}

// 2. Execute Logic
$cmd = basename($_SERVER['SCRIPT_FILENAME'], '.php');
$data = file_get_contents('php://input');
$dataArg = !empty($data) ? " " . escapeshellarg(base64_encode($data)) : "";

passthru("/usr/bin/silvasystems " . escapeshellarg($cmd) . $dataArg);
?>