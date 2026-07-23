<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Device Restoration (Unblock)
 */
require_once '/home/pi/orange-wifi/admin/critical/gatekeeper.php';
require_once '/home/pi/orange-wifi/lib/database.php';
require_once '/home/pi/orange-wifi/lib/iptables.php';

$mac = strtoupper($_GET['mac'] ?? '');

if (!filter_var($mac, FILTER_VALIDATE_MAC)) {
    http_response_code(403); exit;
}

$db = new Database();
$db->set_mac($mac);

if (!$db->get_device_id()) {
    http_response_code(403); exit;
}

// 1. RESTORE STATE: Mark as active (0) in database
$db->exec("UPDATE devices SET topup_count = 0 WHERE mac_addr = '$mac'");

// 2. KERNEL RESTORATION: Physically remove DROP rules
// Use authoritative shell_exec to ensure removal
shell_exec("sudo /sbin/iptables -D FORWARD -m mac --mac-source $mac -j DROP 2>/dev/null");
shell_exec("sudo /sbin/iptables -D INPUT -m mac --mac-source $mac -j DROP 2>/dev/null");

echo "SUCCESS: Device $mac has been restored to the network.";
header("Location: device.php?mac=$mac");
?>