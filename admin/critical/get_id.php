<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * Fetch Orange Star ID (Secure)
 */

if (!isset($_COOKIE['hash'])) { http_response_code(401); exit; }

$payload = base64_encode(json_encode([
    'action' => 'verify',
    'token' => $_COOKIE['hash'],
    'last_seen' => $_COOKIE['last_seen'] ?? 0
]));

$res = trim(shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload)));

if (strpos($res, 'VALID:') === 0) {
    // Session Valid, fetch ID
    $idPayload = base64_encode(json_encode(['action' => 'get_id']));
    passthru("/usr/bin/silvasystems auth_manager " . escapeshellarg($idPayload));
} else {
    http_response_code(401);
    exit('Unauthorized');
}
?>