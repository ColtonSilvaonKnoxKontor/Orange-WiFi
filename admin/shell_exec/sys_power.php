<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Hardware Power API
 */
require_once __DIR__ . '/../critical/gatekeeper.php';

$action = $_GET['action'] ?? '';

if ($action === 'reboot') {
    // Call SUID binary directly without sudo
    echo shell_exec("/usr/bin/silvasystems reboot");
    exit;
}

if ($action === 'shutdown') {
    echo shell_exec("/usr/bin/silvasystems shutdown");
    exit;
}

echo "INVALID_ACTION";
?>