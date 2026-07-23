<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SSH Log Parser - Converts auth.log to JSON
 */

$logFile = '/var/log/auth.log';
$data = [];

if (!file_exists($logFile)) {
    echo json_encode([]);
    exit;
}

// Read last 1000 lines to save memory
$lines = array_reverse(file($logFile));
$count = 0;

foreach ($lines as $line) {
    if ($count >= 200) break; // Limit to 200 entries for dashboard speed

    // Detect SSHD entries only
    if (strpos($line, 'sshd') === false) continue;

    $entry = [
        'ts' => substr($line, 0, 19), // ISO 8601 (e.g. 2026-01-20T15:00:03)
        'status' => 'INFO',
        'user' => 'unknown',
        'ip' => '-',
        'msg' => $line
    ];

    // 1. Successful Login
    if (preg_match('/Accepted (password|publickey) for (.*?) from (.*?) port/', $line, $m)) {
        $entry['status'] = 'SUCCESS';
        $entry['user'] = $m[2];
        $entry['ip'] = $m[3];
        $entry['msg'] = 'Login Accepted (' . $m[1] . ')';
        $data[] = $entry;
        $count++;
    } 
    // 2. Failed Login
    elseif (preg_match('/Failed password for (invalid user )?(.*?) from (.*?) port/', $line, $m)) {
        $entry['status'] = 'FAILED';
        $entry['user'] = $m[2];
        $entry['ip'] = $m[3];
        $entry['msg'] = 'Wrong Password';
        $data[] = $entry;
        $count++;
    }
    // 3. Invalid User (Pre-auth)
    elseif (preg_match('/Invalid user (.*?) from (.*?) port/', $line, $m)) {
        $entry['status'] = 'INVALID';
        $entry['user'] = $m[1];
        $entry['ip'] = $m[2];
        $entry['msg'] = 'User not found';
        $data[] = $entry;
        $count++;
    }
    // 4. Disconnects
    elseif (preg_match('/Disconnected from (user )?(.*?) (.*?) port/', $line, $m)) {
        $entry['status'] = 'DISCONNECT';
        $entry['user'] = $m[2];
        $entry['ip'] = $m[3];
        $entry['msg'] = 'Session Ended';
        $data[] = $entry;
        $count++;
    }
}

echo json_encode($data);
?>