<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * Deep Auth Debugger
 */
$password = $argv[1] ?? 'test1234';

echo "--- DEBUGGING AUTH FOR: $password ---\n";

// 1. Get Hardware ID from Loader directly
// We use a dummy script that just echoes the ID to verify it
$id_output = shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg(base64_encode(json_encode(['action' => 'get_id']))));
echo "LOADER ID OUTPUT: " . substr($id_output, 0, 10) . "...\n";

// 2. Read Stored Hash
$hashFile = '/home/pi/orange-wifi/conf/password.sha256';
if (file_exists($hashFile)) {
    echo "STORED HASH: " . substr(file_get_contents($hashFile), 0, 10) . "...\n";
} else {
    echo "STORED HASH: MISSING\n";
}

// 3. Attempt Login via Loader
$payload = base64_encode(json_encode(['action' => 'login', 'password' => $password]));
$login_output = shell_exec("/usr/bin/silvasystems auth_manager " . escapeshellarg($payload) . " 2>&1");
echo "LOGIN RESULT: $login_output\n";

// 4. Attempt Update via Loader (to see if saving works)
// Note: We use 'recovery' action to bypass session check if we have the ID, but we need the ID first.
// Let's just try to see if the loader crashes.
?>
