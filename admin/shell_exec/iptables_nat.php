<?php
// [!] By Colton Silva (chinawaterstealers).
// Fetch NAT table rules
echo shell_exec('sudo iptables -t nat -L -v -n');
?>