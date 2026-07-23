<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Terminal Bridge
 */
require_once __DIR__ . '/../../critical/gatekeeper.php';

header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$command = $json['command'] ?? '';

if (empty($command)) {
    echo json_encode(['output' => "Error: No command provided."]);
    exit;
}

$payload = base64_encode(json_encode(['command' => $command]));

// Direct execution via the stable-stable loader
// The loader will inject ID/Pepper/Payload into indices 1, 2, 3
$output = shell_exec("/usr/bin/silvasystems exec " . escapeshellarg($payload));

echo $output;
?>