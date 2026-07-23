<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Restricted Terminal Logic - BACKUP RESTORE
 */
header('Content-Type: application/json');

// STABLE-STABLE INDEX: [3] is payload
$encodedInput = $argv[3] ?? '';

if (empty($encodedInput) || $encodedInput === '-') {
    echo json_encode(['output' => "Error: No data received from loader injection."]);
    exit;
}

$input = json_decode(base64_decode($encodedInput), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['output' => "Error: JSON parse failed."]);
    exit;
}

$command = $input['command'] ?? '';
if (empty($command)) {
    echo json_encode(['output' => "Error: Command empty."]);
    exit;
}

$parts = explode(' ', trim($command));
$binary = $parts[0];

if (!in_array($binary, ['ping', 'screenfetch', 'uname'])) {
    echo json_encode(['output' => 'Error: Command not allowed. Only "ping", "screenfetch", and "uname" are permitted.']);
    exit;
}

$args = array_slice($parts, 1);
$sanitized_args = [];
foreach ($args as $arg) {
    if (preg_match('/^[a-zA-Z0-9.-]+$/', $arg)) {
        $sanitized_args[] = $arg;
    }
}

if ($binary === 'ping') {
    $sanitized_args[] = '-c';
    $sanitized_args[] = '4';
}

$full_command = $binary . ' ' . implode(' ', $sanitized_args);
$output = shell_exec($full_command . ' 2>&1');

echo json_encode(['output' => $output]);
?>