<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Authoritative Update Bridge
 */
require_once __DIR__ . '/../critical/gatekeeper.php';

// 1. Context Extraction for Encrypted Logic
$encodedContext = $argv[3] ?? '';
if (!empty($encodedContext)) {
    $context = json_decode(base64_decode($encodedContext), true);
    if ($context) {
        $_POST = $context['post'] ?? [];
        $_FILES = $context['files'] ?? [];
        $_SERVER['REQUEST_METHOD'] = $context['method'] ?? 'POST';
    }
}

// Disable time limits for large updates
set_time_limit(0);
ob_end_clean();
header('X-Accel-Buffering: no');
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['package'])) {
    die("[ERROR] No update package received.");
}

$type = $_POST['type'] ?? '';
$package = $_FILES['package'];

// 2. SECURITY: Enforce 50MB Backend Limit
$maxSize = 50 * 1024 * 1024;
if ($package['size'] > $maxSize) {
    die("[CRITICAL] Security Block: Package exceeds the 50MB authorized limit.");
}

$tmpPath = "/tmp/update_" . time() . ".owp";

// 3. SECURITY: Dual-Mode Staging
// move_uploaded_file only works in standard HTTP context. 
// We use copy() for the encrypted CLI context.
$success = false;
if (is_uploaded_file($package['tmp_name'])) {
    $success = move_uploaded_file($package['tmp_name'], $tmpPath);
} else {
    $success = copy($package['tmp_name'], $tmpPath);
}

if (!$success) {
    die("[ERROR] Failed to stage update package to storage.");
}

// 4. EXECUTION: Call the SUID binary
$flag = ($type === 'official') ? '--official' : '--third-party';
$command = "/usr/bin/softup $flag " . escapeshellarg($tmpPath) . " 2>&1";

$handle = popen($command, 'r');
if (!$handle) {
    unlink($tmpPath);
    die("[ERROR] Could not initialize update engine.");
}

while (!feof($handle)) {
    echo fgets($handle);
    flush();
}

pclose($handle);
unlink($tmpPath);
?>