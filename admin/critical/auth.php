<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Critical Entry Bridge (No Auth Check)
 */

// We deliberately DO NOT check for password.sha256 here because
// this script is the mechanism to RESTORE it when it's missing.

$data = file_get_contents('php://input');
$dataArg = !empty($data) ? " " . escapeshellarg(base64_encode($data)) : "";

// Directly call the auth_manager logic
// We hardcode 'auth_manager' because this file is named 'auth.php'
passthru("/usr/bin/silvasystems auth_manager" . $dataArg);
?>