<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Logic Bridge
 */
require_once 'critical/gatekeeper.php';

if (!isset($_COOKIE['hash'])) { http_response_code(401); exit; }

// Use the new session router verification
$payload = base64_encode(json_encode([
    'action' => 'verify',
    'token' => $_COOKIE['hash'],
    'last_seen' => $_COOKIE['last_seen'] ?? 0
]));

$res = trim(shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload)));

// Check for VALID response
if (strpos($res, 'VALID:') !== 0) {
    if ($res === 'LOCKED') http_response_code(423);
    else http_response_code(401);
    exit;
}

// Logic execution
$file = preg_replace('/\.php$/', '.titemongmaliit', $_SERVER['SCRIPT_FILENAME']);
$data = file_get_contents('php://input');
$dataArg = !empty($data) ? " " . escapeshellarg(base64_encode($data)) : "";

passthru("/usr/bin/silvasystems " . escapeshellarg(basename($file, '.titemongmaliit')) . $dataArg);
?>