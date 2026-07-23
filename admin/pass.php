<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Session Bridge
 */
require_once __DIR__ . '/critical/gatekeeper.php';

// If being included by another script (like the bridges), we act as a verification library
if (basename($_SERVER['SCRIPT_FILENAME']) !== 'pass.php') {
    if (!isset($_COOKIE['hash'])) return http_response_code(401);

    $payload = base64_encode(json_encode([
        'action' => 'verify',
        'token' => $_COOKIE['hash']
    ]));

    $res = trim((string)shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload)));

    if (strpos($res, 'VALID:') === 0) {
        return http_response_code(200);
    } else {
        return http_response_code(401);
    }
}

// --- Standard API Logic ---
$cookieParams = ['lifetime' => 86400, 'path' => '/admin/', 'domain' => '', 'secure' => isset($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Strict'];

// LOGOUT
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_COOKIE['hash'])) {
        $payload = base64_encode(json_encode(['action' => 'logout', 'token' => $_COOKIE['hash']]));
        shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload));
    }
    setcookie('hash', '', -1, $cookieParams['path']);
    exit;
}

// LOGIN / RECOVERY / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $rawInput = file_get_contents('php://input');
    
    $json = json_decode($rawInput, true); 
    if ($json && isset($json['action']) && ($json['action'] === 'recovery' || $json['action'] === 'update_password')) {
        $payload = base64_encode($rawInput); 
    } else {
        $password = base64_decode($rawInput);
        $action = ($_SERVER['REQUEST_METHOD'] === 'PUT') ? 'login' : 'update';
        $payload = base64_encode(json_encode(['action' => $action, 'password' => $password]));
    }
    
    $output = trim((string)shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload)));

    // Handle Login Success
    if (strpos($output, 'SUCCESS:') === 0) {
        $parts = explode(':', $output);
        setcookie('hash', $parts[1], time() + 86400, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
        http_response_code(200);
        exit;
    }
    
    // Handle Recovery Success
    if (strpos($output, 'RECOVERED:') === 0) {
        http_response_code(200);
        echo $output;
        exit;
    }
    
    // FIXED: Return 401 for password failures so the UI doesn't say "Accepted"
    http_response_code(401); 
    echo $output; 
    exit;
}

// Default check (Heartbeat)
if (!isset($_COOKIE['hash'])) { http_response_code(401); exit; }
$payload = base64_encode(json_encode(['action' => 'verify', 'token' => $_COOKIE['hash']]));
$res = trim((string)shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload)));

if (strpos($res, 'VALID:') === 0) {
    http_response_code(200);
} else {
    setcookie('hash', '', -1, $cookieParams['path']);
    http_response_code(401);
    echo $res;
}
?>