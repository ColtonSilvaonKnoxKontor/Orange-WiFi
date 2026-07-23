<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Admin Index
 * Clean Version - Logic moved to Gatekeeper
 */
require_once 'critical/gatekeeper.php';

// If we reached here, Gatekeeper passed (Session Valid, Not Locked, Not Restricted)
header('Location: /admin/frontend/');
exit;
?>