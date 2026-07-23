<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 */
error_reporting(0);
ini_set('display_errors', 0);

// --- 1. GLOBAL PUBLIC BYPASS ---
// Rates API must be 100% accessible to the portal without ANY security checks
$script = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($script === 'rates_api.php') {
    return;
}

if (PHP_SAPI === 'cli') {
    return; 
}

if (!defined('SILVA_PATH')) define('SILVA_PATH', '/home/pi/orange-wifi/admin/');

// --- 2. LOCKDOWN CHECK ---
$statusPayload = base64_encode(json_encode(['action' => 'status']));
$systemStatus = trim((string)shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($statusPayload)));

if ($systemStatus === 'LOCKED' && $script !== 'lockdown.php' && $script !== 'auth.php') {
    http_response_code(423);
    if (!headers_sent()) header('Location: /admin/lockdown.php');
    exit;
}

// --- 3. MAC RESTRICTION ---
$skipRestriction = ['lockdown.php', 'access_denied.php', 'auth.php'];
if (!in_array($script, $skipRestriction)) {
    $clientIP = $_SERVER['REMOTE_ADDR'];
    $clientMAC = '';
    if (file_exists('/proc/net/arp')) {
        $arp = file('/proc/net/arp');
        foreach ($arp as $line) {
            $cols = preg_split('/\s+/', $line);
            if (count($cols) > 3 && $cols[0] === $clientIP) {
                $clientMAC = strtoupper($cols[3]);
                break;
            }
        }
    }
    $restrictPayload = base64_encode(json_encode(['action' => 'check_access', 'mac' => $clientMAC]));
    $accessStatus = trim((string)shell_exec("/usr/bin/silvasystems admin_restrict " . escapeshellarg($restrictPayload)));

    if ($accessStatus === 'DENIED') {
        http_response_code(403);
        if (!headers_sent()) header('Location: /admin/access_denied.php');
        exit;
    }
}

// --- 4. SESSION AUTH ---
$skipAuth = ['lockdown.php', 'access_denied.php', 'auth.php', 'pass.php', 'chpwd.html'];
$isLoginPage = (strpos($_SERVER['SCRIPT_NAME'], '/Login/') !== false);
$isPublic = in_array($script, $skipAuth);

if (!$isLoginPage && !$isPublic) {
    if (!isset($_COOKIE['hash'])) {
        http_response_code(401);
        if (!headers_sent()) header('Location: /admin/Login/');
        exit;
    }

    $verifyPayload = base64_encode(json_encode(['action' => 'verify', 'token' => $_COOKIE['hash']]));
    $res = trim((string)shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($verifyPayload)));

    if (strpos($res, 'VALID:') !== 0) {
        setcookie('hash', '', -1, '/admin/');
        http_response_code(401);
        if (!headers_sent()) header('Location: /admin/Login/');
        exit;
    }
}
?>