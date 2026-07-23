<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Logic Bridge
 */
ob_start();
if (file_exists('/home/pi/orange-wifi/admin/pass.php')) {
    require_once '/home/pi/orange-wifi/admin/pass.php';
}
$status = http_response_code();
ob_end_clean();

if ($status !== 200) {
    http_response_code($status);
    exit;
}

$cmd = basename($_SERVER['SCRIPT_FILENAME'], '.php');

// Package Context for Binary (Including Method and Files)
$context = [
    "method" => $_SERVER['REQUEST_METHOD'],
    "get" => $_GET,
    "post" => $_POST,
    "files" => $_FILES,
    "input" => file_get_contents('php://input')
];
$dataArg = " " . escapeshellarg(base64_encode(json_encode($context)));

passthru("/usr/bin/silvasystems " . escapeshellarg($cmd) . $dataArg);
?>