<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Device Blocker
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require '/home/pi/orange-wifi/lib/autoload.php';

$data = json_decode(base64_decode($argv[3] ?? ""), true);
$mac = $_GET['mac'] ?? $data['get']['mac'] ?? null;

if(!$mac) { http_response_code(403); exit; }

$db = new Database();
$db->set_mac($mac);

if( !$db->get_device_id() ) {
  http_response_code(403); exit;
}

[$mb_limit, $mb_used] = $db->get_data_usage();
$db->set_mb_used($mb_limit-$mb_used);
$db->update_mb_used();

// AUTHORITATIVE REGISTRATION: Mark as restricted in database
$db->exec("UPDATE devices SET topup_count = -1 WHERE mac_addr = '$mac'");

// NUCLEAR HARDENING: Physically isolate device from internet and portal
Iptables::block_mac($mac);

// Final cleanup: Kill active translation states
shell_exec("sudo /usr/bin/conntrack -D -s $(/usr/sbin/arp -n | grep $mac | awk '{print $1}') 2>/dev/null");
?>