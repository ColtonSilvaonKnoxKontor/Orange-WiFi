<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Amount Bridge
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require '/home/pi/orange-wifi/lib/autoload.php';

$data = json_decode(base64_decode($argv[3] ?? ""), true);
$n = intval($_GET['n'] ?? $data['get']['n'] ?? 0);

if(!$n) exit;

$db = new Database();
$rates = $db->get_rates();

echo $rates[$n] ?? 0;
?>