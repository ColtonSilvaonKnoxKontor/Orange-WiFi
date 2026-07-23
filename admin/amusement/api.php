<?php
/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Radio API Bridge
 */
header('Content-Type: application/json');

// Call the binary to execute the encrypted logic
// We assume 'radio_logic' command maps to 'logic/radio_logic.titemongmaliit'
$output = shell_exec("/usr/bin/silvasystems radio_logic");
echo $output;
?>